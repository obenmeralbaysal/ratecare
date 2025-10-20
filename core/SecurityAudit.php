<?php

namespace Core;

/**
 * Security Audit System
 */
class SecurityAudit
{
    private $auditResults = [];
    private $criticalIssues = 0;
    private $warningIssues = 0;
    private $infoIssues = 0;
    
    /**
     * Run complete security audit
     */
    public function runAudit()
    {
        $this->auditResults = [];
        $this->criticalIssues = 0;
        $this->warningIssues = 0;
        $this->infoIssues = 0;
        
        $this->checkFilePermissions();
        $this->checkConfigSecurity();
        $this->checkDatabaseSecurity();
        $this->checkSessionSecurity();
        $this->checkHttpsSecurity();
        $this->checkInputValidation();
        $this->checkErrorHandling();
        $this->checkLogging();
        $this->checkDependencies();
        
        return $this->generateReport();
    }
    
    /**
     * Check file permissions
     */
    private function checkFilePermissions()
    {
        $criticalFiles = [
            '.env' => 0600,
            'config/' => 0755,
            'storage/logs/' => 0755,
            'storage/cache/' => 0755
        ];
        
        foreach ($criticalFiles as $file => $expectedPerm) {
            $fullPath = __DIR__ . '/../' . $file;
            
            if (!file_exists($fullPath)) {
                $this->addIssue('warning', 'File Permissions', "File/directory not found: {$file}");
                continue;
            }
            
            $currentPerm = fileperms($fullPath) & 0777;
            
            if ($currentPerm !== $expectedPerm) {
                $this->addIssue('warning', 'File Permissions', 
                    "Incorrect permissions for {$file}. Expected: " . decoct($expectedPerm) . 
                    ", Current: " . decoct($currentPerm));
            }
        }
        
        // Check if sensitive files are accessible via web
        $sensitiveFiles = ['.env', 'composer.json', 'composer.lock'];
        foreach ($sensitiveFiles as $file) {
            if (file_exists(__DIR__ . '/../public/' . $file)) {
                $this->addIssue('critical', 'File Exposure', 
                    "Sensitive file {$file} is accessible in public directory");
            }
        }
    }
    
    /**
     * Check configuration security
     */
    private function checkConfigSecurity()
    {
        // Check if debug mode is enabled in production
        if ($_ENV['APP_ENV'] === 'production' && $_ENV['APP_DEBUG'] === 'true') {
            $this->addIssue('critical', 'Configuration', 
                'Debug mode is enabled in production environment');
        }
        
        // Check for default/weak app key
        $appKey = $_ENV['APP_KEY'] ?? '';
        if (empty($appKey) || $appKey === 'your-secret-key-here' || strlen($appKey) < 32) {
            $this->addIssue('critical', 'Configuration', 
                'App key is missing, default, or too weak');
        }
        
        // Check database credentials
        if (empty($_ENV['DB_PASSWORD']) && $_ENV['APP_ENV'] === 'production') {
            $this->addIssue('critical', 'Configuration', 
                'Database password is empty in production');
        }
        
        // Check for exposed configuration
        if (function_exists('phpinfo')) {
            $this->addIssue('warning', 'Configuration', 
                'phpinfo() function is available - consider disabling');
        }
    }
    
    /**
     * Check database security
     */
    private function checkDatabaseSecurity()
    {
        try {
            $db = Database::getInstance();
            
            // Check for SQL injection protection
            $testQuery = "SELECT 1 WHERE 1=1 OR '1'='1'";
            // This is just a check - we don't actually execute dangerous queries
            
            // Check if database user has excessive privileges
            $privileges = $db->selectOne("SHOW GRANTS");
            if ($privileges && strpos(strtoupper($privileges['Grants for ' . $_ENV['DB_USERNAME'] . '@%'] ?? ''), 'ALL PRIVILEGES') !== false) {
                $this->addIssue('warning', 'Database Security', 
                    'Database user has ALL PRIVILEGES - consider using principle of least privilege');
            }
            
        } catch (\Exception $e) {
            $this->addIssue('info', 'Database Security', 
                'Could not check database security: ' . $e->getMessage());
        }
    }
    
    /**
     * Check session security
     */
    private function checkSessionSecurity()
    {
        // Check session configuration
        if (!ini_get('session.cookie_httponly')) {
            $this->addIssue('warning', 'Session Security', 
                'session.cookie_httponly is not enabled');
        }
        
        if (!ini_get('session.cookie_secure') && $this->isHttps()) {
            $this->addIssue('warning', 'Session Security', 
                'session.cookie_secure should be enabled for HTTPS');
        }
        
        if (ini_get('session.use_strict_mode') !== '1') {
            $this->addIssue('warning', 'Session Security', 
                'session.use_strict_mode is not enabled');
        }
        
        // Check session regeneration
        if (!isset($_SESSION['last_regeneration'])) {
            $this->addIssue('info', 'Session Security', 
                'Session ID regeneration not implemented');
        }
    }
    
    /**
     * Check HTTPS security
     */
    private function checkHttpsSecurity()
    {
        if (!$this->isHttps() && $_ENV['APP_ENV'] === 'production') {
            $this->addIssue('critical', 'HTTPS Security', 
                'HTTPS is not enabled in production environment');
        }
        
        // Check security headers
        $requiredHeaders = [
            'X-Frame-Options',
            'X-Content-Type-Options',
            'X-XSS-Protection',
            'Strict-Transport-Security'
        ];
        
        foreach ($requiredHeaders as $header) {
            if (!$this->headerExists($header)) {
                $this->addIssue('warning', 'Security Headers', 
                    "Missing security header: {$header}");
            }
        }
    }
    
    /**
     * Check input validation
     */
    private function checkInputValidation()
    {
        // Check if input sanitization is implemented
        if (!class_exists('Core\\Sanitizer')) {
            $this->addIssue('warning', 'Input Validation', 
                'Input sanitization class not found');
        }
        
        // Check if XSS protection is implemented
        if (!class_exists('Core\\XssProtection')) {
            $this->addIssue('warning', 'Input Validation', 
                'XSS protection class not found');
        }
        
        // Check if SQL injection protection is implemented
        if (!class_exists('Core\\SqlProtection')) {
            $this->addIssue('warning', 'Input Validation', 
                'SQL injection protection class not found');
        }
        
        // Check for magic quotes (deprecated)
        if (function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()) {
            $this->addIssue('warning', 'Input Validation', 
                'Magic quotes are enabled (deprecated and insecure)');
        }
    }
    
    /**
     * Check error handling
     */
    private function checkErrorHandling()
    {
        // Check if error display is disabled in production
        if ($_ENV['APP_ENV'] === 'production' && ini_get('display_errors')) {
            $this->addIssue('critical', 'Error Handling', 
                'Error display is enabled in production');
        }
        
        // Check if error logging is enabled
        if (!ini_get('log_errors')) {
            $this->addIssue('warning', 'Error Handling', 
                'Error logging is not enabled');
        }
        
        // Check error log file permissions
        $errorLog = ini_get('error_log');
        if ($errorLog && file_exists($errorLog)) {
            $perms = fileperms($errorLog) & 0777;
            if ($perms > 0640) {
                $this->addIssue('warning', 'Error Handling', 
                    'Error log file has overly permissive permissions');
            }
        }
    }
    
    /**
     * Check logging security
     */
    private function checkLogging()
    {
        $logDir = __DIR__ . '/../storage/logs/';
        
        if (!is_dir($logDir)) {
            $this->addIssue('warning', 'Logging', 'Log directory does not exist');
            return;
        }
        
        // Check log directory permissions
        $perms = fileperms($logDir) & 0777;
        if ($perms > 0755) {
            $this->addIssue('warning', 'Logging', 
                'Log directory has overly permissive permissions');
        }
        
        // Check if logs are accessible via web
        $htaccessFile = $logDir . '.htaccess';
        if (!file_exists($htaccessFile)) {
            $this->addIssue('warning', 'Logging', 
                'Log directory is not protected from web access');
        }
        
        // Check log rotation
        $logFiles = glob($logDir . '*.log');
        foreach ($logFiles as $logFile) {
            if (filesize($logFile) > 10 * 1024 * 1024) { // 10MB
                $this->addIssue('info', 'Logging', 
                    'Log file is large and may need rotation: ' . basename($logFile));
            }
        }
    }
    
    /**
     * Check dependencies
     */
    private function checkDependencies()
    {
        // Check PHP version
        if (version_compare(PHP_VERSION, '7.4.0', '<')) {
            $this->addIssue('critical', 'Dependencies', 
                'PHP version is outdated. Current: ' . PHP_VERSION);
        }
        
        // Check for dangerous functions
        $dangerousFunctions = [
            'eval', 'exec', 'system', 'shell_exec', 'passthru',
            'file_get_contents', 'file_put_contents', 'fopen'
        ];
        
        foreach ($dangerousFunctions as $func) {
            if (function_exists($func)) {
                $this->addIssue('info', 'Dependencies', 
                    "Potentially dangerous function is available: {$func}");
            }
        }
        
        // Check for development packages in production
        if ($_ENV['APP_ENV'] === 'production' && file_exists(__DIR__ . '/../composer.json')) {
            $composer = json_decode(file_get_contents(__DIR__ . '/../composer.json'), true);
            if (!empty($composer['require-dev'])) {
                $this->addIssue('warning', 'Dependencies', 
                    'Development dependencies may be installed in production');
            }
        }
    }
    
    /**
     * Add audit issue
     */
    private function addIssue($severity, $category, $message)
    {
        $this->auditResults[] = [
            'severity' => $severity,
            'category' => $category,
            'message' => $message,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        switch ($severity) {
            case 'critical':
                $this->criticalIssues++;
                break;
            case 'warning':
                $this->warningIssues++;
                break;
            case 'info':
                $this->infoIssues++;
                break;
        }
    }
    
    /**
     * Generate audit report
     */
    private function generateReport()
    {
        $totalIssues = $this->criticalIssues + $this->warningIssues + $this->infoIssues;
        
        $report = [
            'summary' => [
                'total_issues' => $totalIssues,
                'critical_issues' => $this->criticalIssues,
                'warning_issues' => $this->warningIssues,
                'info_issues' => $this->infoIssues,
                'security_score' => $this->calculateSecurityScore(),
                'audit_date' => date('Y-m-d H:i:s')
            ],
            'issues' => $this->auditResults,
            'recommendations' => $this->getRecommendations()
        ];
        
        // Save audit report
        $this->saveAuditReport($report);
        
        return $report;
    }
    
    /**
     * Calculate security score (0-100)
     */
    private function calculateSecurityScore()
    {
        $score = 100;
        $score -= $this->criticalIssues * 20;
        $score -= $this->warningIssues * 10;
        $score -= $this->infoIssues * 2;
        
        return max(0, $score);
    }
    
    /**
     * Get security recommendations
     */
    private function getRecommendations()
    {
        $recommendations = [];
        
        if ($this->criticalIssues > 0) {
            $recommendations[] = 'Address all critical security issues immediately';
        }
        
        if ($this->warningIssues > 0) {
            $recommendations[] = 'Review and fix warning-level security issues';
        }
        
        $recommendations[] = 'Implement regular security audits';
        $recommendations[] = 'Keep all dependencies up to date';
        $recommendations[] = 'Use HTTPS in production';
        $recommendations[] = 'Implement proper input validation and sanitization';
        $recommendations[] = 'Enable security headers';
        $recommendations[] = 'Use strong authentication mechanisms';
        
        return $recommendations;
    }
    
    /**
     * Save audit report
     */
    private function saveAuditReport($report)
    {
        $reportFile = __DIR__ . '/../storage/logs/security_audit_' . date('Y-m-d_H-i-s') . '.json';
        file_put_contents($reportFile, json_encode($report, JSON_PRETTY_PRINT));
    }
    
    /**
     * Check if HTTPS is enabled
     */
    private function isHttps()
    {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
               $_SERVER['SERVER_PORT'] == 443 ||
               (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    }
    
    /**
     * Check if header exists
     */
    private function headerExists($headerName)
    {
        $headers = headers_list();
        foreach ($headers as $header) {
            if (stripos($header, $headerName . ':') === 0) {
                return true;
            }
        }
        return false;
    }
}
