<?php

namespace App\Helpers;

use Core\Database;
use PDO;

/**
 * Circuit Breaker Pattern Implementation
 * Prevents cascading failures by stopping requests to failing platforms
 */
class CircuitBreaker
{
    private $db;
    private $pdo;
    private $enabled;
    private $failureThreshold;
    private $timeoutSeconds;
    private $halfOpenAttempts;
    
    const STATE_CLOSED = 'closed';       // Normal operation
    const STATE_OPEN = 'open';           // Circuit is open, skip requests
    const STATE_HALF_OPEN = 'half_open'; // Testing if service recovered
    
    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->pdo = $this->db->getConnection();
        
        // Load settings
        $this->enabled = (bool) $this->getSetting('circuit-breaker-enabled', 1);
        $this->failureThreshold = (int) $this->getSetting('circuit-breaker-failure-threshold', 5);
        $this->timeoutSeconds = (int) $this->getSetting('circuit-breaker-timeout-seconds', 600);
        $this->halfOpenAttempts = (int) $this->getSetting('circuit-breaker-half-open-requests', 3);
    }
    
    /**
     * Check if platform is available for requests
     */
    public function isAvailable(string $platform): bool
    {
        if (!$this->enabled) {
            return true;
        }
        
        $state = $this->getState($platform);
        
        // Closed state: always available
        if ($state['state'] === self::STATE_CLOSED) {
            return true;
        }
        
        // Open state: check if timeout passed
        if ($state['state'] === self::STATE_OPEN) {
            $openedAt = strtotime($state['opened_at']);
            $now = time();
            
            // If timeout passed, transition to half-open
            if ($now - $openedAt >= $this->timeoutSeconds) {
                $this->transitionToHalfOpen($platform);
                return true; // Allow test request
            }
            
            return false; // Still open, skip request
        }
        
        // Half-open state: allow limited test requests
        if ($state['state'] === self::STATE_HALF_OPEN) {
            return $state['half_open_attempts'] < $this->halfOpenAttempts;
        }
        
        return false;
    }
    
    /**
     * Record successful request
     */
    public function recordSuccess(string $platform): void
    {
        if (!$this->enabled) {
            return;
        }
        
        $state = $this->getState($platform);
        
        // If half-open, check if we can close the circuit
        if ($state['state'] === self::STATE_HALF_OPEN) {
            $this->transitionToClosed($platform);
            $this->log('info', "Circuit breaker CLOSED for platform: {$platform} (service recovered)");
        } else {
            // Reset failure count on success
            $this->updateState($platform, [
                'failure_count' => 0,
                'last_success_time' => date('Y-m-d H:i:s'),
                'total_requests' => $state['total_requests'] + 1
            ]);
        }
    }
    
    /**
     * Record failed request
     */
    public function recordFailure(string $platform): void
    {
        if (!$this->enabled) {
            return;
        }
        
        $state = $this->getState($platform);
        $newFailureCount = $state['failure_count'] + 1;
        $totalFailures = $state['total_failures'] + 1;
        
        $updateData = [
            'failure_count' => $newFailureCount,
            'last_failure_time' => date('Y-m-d H:i:s'),
            'total_requests' => $state['total_requests'] + 1,
            'total_failures' => $totalFailures
        ];
        
        // Check if we should open the circuit
        if ($state['state'] === self::STATE_CLOSED && $newFailureCount >= $this->failureThreshold) {
            $this->transitionToOpen($platform);
            $this->log('warning', "Circuit breaker OPENED for platform: {$platform} ({$newFailureCount} consecutive failures)");
        } 
        // If half-open and still failing, reopen
        elseif ($state['state'] === self::STATE_HALF_OPEN) {
            $this->transitionToOpen($platform);
            $this->log('warning', "Circuit breaker REOPENED for platform: {$platform} (test requests failed)");
        } 
        else {
            $this->updateState($platform, $updateData);
        }
    }
    
    /**
     * Get current state of platform
     */
    public function getState(string $platform): array
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM circuit_breaker_state 
            WHERE platform = ?
        ");
        $stmt->execute([$platform]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // If not found, create initial state
        if (!$result) {
            $this->initializePlatform($platform);
            return $this->getState($platform);
        }
        
        return $result;
    }
    
    /**
     * Get all platform states
     */
    public function getAllStates(): array
    {
        $stmt = $this->pdo->query("
            SELECT * FROM circuit_breaker_state 
            ORDER BY platform
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Manually reset circuit for platform
     */
    public function reset(string $platform): void
    {
        $this->transitionToClosed($platform);
        $this->log('info', "Circuit breaker manually RESET for platform: {$platform}");
    }
    
    /**
     * Reset all platforms
     */
    public function resetAll(): void
    {
        $this->pdo->exec("
            UPDATE circuit_breaker_state 
            SET state = 'closed', 
                failure_count = 0, 
                half_open_attempts = 0
        ");
        $this->log('info', "All circuit breakers RESET");
    }
    
    /**
     * Get statistics
     */
    public function getStatistics(): array
    {
        $states = $this->getAllStates();
        
        $stats = [
            'total_platforms' => count($states),
            'closed' => 0,
            'open' => 0,
            'half_open' => 0,
            'total_requests' => 0,
            'total_failures' => 0,
            'platforms' => []
        ];
        
        foreach ($states as $state) {
            $stats[$state['state']]++;
            $stats['total_requests'] += $state['total_requests'];
            $stats['total_failures'] += $state['total_failures'];
            
            $stats['platforms'][] = [
                'platform' => $state['platform'],
                'state' => $state['state'],
                'failure_count' => $state['failure_count'],
                'success_rate' => $state['total_requests'] > 0 
                    ? round((($state['total_requests'] - $state['total_failures']) / $state['total_requests']) * 100, 2)
                    : 100
            ];
        }
        
        return $stats;
    }
    
    /**
     * Transition to OPEN state
     */
    private function transitionToOpen(string $platform): void
    {
        $this->updateState($platform, [
            'state' => self::STATE_OPEN,
            'opened_at' => date('Y-m-d H:i:s'),
            'half_open_attempts' => 0
        ]);
    }
    
    /**
     * Transition to HALF_OPEN state
     */
    private function transitionToHalfOpen(string $platform): void
    {
        $this->updateState($platform, [
            'state' => self::STATE_HALF_OPEN,
            'half_open_attempts' => 0
        ]);
        $this->log('info', "Circuit breaker HALF-OPEN for platform: {$platform} (testing recovery)");
    }
    
    /**
     * Transition to CLOSED state
     */
    private function transitionToClosed(string $platform): void
    {
        $this->updateState($platform, [
            'state' => self::STATE_CLOSED,
            'failure_count' => 0,
            'half_open_attempts' => 0,
            'last_success_time' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Update platform state
     */
    private function updateState(string $platform, array $data): void
    {
        $fields = [];
        $values = [];
        
        foreach ($data as $key => $value) {
            $fields[] = "`{$key}` = ?";
            $values[] = $value;
        }
        
        $values[] = $platform;
        
        $sql = "UPDATE circuit_breaker_state SET " . implode(', ', $fields) . " WHERE platform = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($values);
    }
    
    /**
     * Initialize platform state
     */
    private function initializePlatform(string $platform): void
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO circuit_breaker_state (platform, state) 
            VALUES (?, 'closed')
            ON DUPLICATE KEY UPDATE state = state
        ");
        $stmt->execute([$platform]);
    }
    
    /**
     * Get setting value
     */
    private function getSetting(string $key, $default = null)
    {
        $stmt = $this->pdo->prepare("SELECT value FROM settings WHERE `key` = ?");
        $stmt->execute([$key]);
        $result = $stmt->fetch();
        return $result ? $result['value'] : $default;
    }
    
    /**
     * Log message
     */
    private function log(string $level, string $message): void
    {
        $logFile = APP_ROOT . '/storage/logs/circuit-breaker.log';
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] [{$level}] {$message}\n";
        file_put_contents($logFile, $logMessage, FILE_APPEND);
    }
}
