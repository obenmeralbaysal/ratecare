<?php

namespace Core;

/**
 * Error Handler and Logger
 */
class ErrorHandler
{
    private static $logPath;
    
    public static function register($logPath = null)
    {
        self::$logPath = $logPath ?: __DIR__ . '/../storage/logs/';
        
        // Debug: Echo the actual path being used
        if (!file_exists(self::$logPath)) {
            echo "<!-- DEBUG: Log path does not exist: " . self::$logPath . " -->";
        }
        
        // Create logs directory if it doesn't exist
        if (!is_dir(self::$logPath)) {
            mkdir(self::$logPath, 0755, true);
        }
        
        // Set error handlers
        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
        register_shutdown_function([self::class, 'handleShutdown']);
    }
    
    public static function handleError($severity, $message, $file, $line)
    {
        if (!(error_reporting() & $severity)) {
            return false;
        }
        
        $errorType = self::getErrorType($severity);
        $logMessage = "[{$errorType}] {$message} in {$file} on line {$line}";
        
        self::log('error', $logMessage);
        
        if (Config::get('app.debug', false)) {
            echo "<div style='background: #ffebee; border: 1px solid #f44336; padding: 10px; margin: 10px; border-radius: 4px;'>";
            echo "<strong>{$errorType}:</strong> {$message}<br>";
            echo "<strong>File:</strong> {$file}<br>";
            echo "<strong>Line:</strong> {$line}";
            echo "</div>";
        }
        
        return true;
    }
    
    public static function handleException($exception)
    {
        $message = $exception->getMessage();
        $file = $exception->getFile();
        $line = $exception->getLine();
        $trace = $exception->getTraceAsString();
        
        $logMessage = "[EXCEPTION] {$message} in {$file} on line {$line}\nStack trace:\n{$trace}";
        
        self::log('error', $logMessage);
        
        if (Config::get('app.debug', false)) {
            echo "<div style='background: #ffebee; border: 1px solid #f44336; padding: 10px; margin: 10px; border-radius: 4px;'>";
            echo "<strong>Exception:</strong> {$message}<br>";
            echo "<strong>File:</strong> {$file}<br>";
            echo "<strong>Line:</strong> {$line}<br>";
            echo "<strong>Trace:</strong><pre>{$trace}</pre>";
            echo "</div>";
        } else {
            echo "<h1>500 - Internal Server Error</h1>";
            echo "<p>Something went wrong. Please try again later.</p>";
        }
    }
    
    public static function handleShutdown()
    {
        $error = error_get_last();
        
        if ($error && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE])) {
            self::handleError($error['type'], $error['message'], $error['file'], $error['line']);
        }
    }
    
    public static function log($level, $message, $context = [])
    {
        try {
            $timestamp = date('Y-m-d H:i:s');
            $logFile = self::$logPath . date('Y-m-d') . '.log';
            
            $contextString = !empty($context) ? ' ' . json_encode($context) : '';
            $logEntry = "[{$timestamp}] {$level}: {$message}{$contextString}" . PHP_EOL;
            
            file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
        } catch (\Exception $e) {
            // If logging fails, silently continue to avoid infinite loops
        }
    }
    
    private static function getErrorType($severity)
    {
        switch ($severity) {
            case E_ERROR:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
            case E_PARSE:
                return 'FATAL ERROR';
            case E_WARNING:
            case E_CORE_WARNING:
            case E_COMPILE_WARNING:
                return 'WARNING';
            case E_NOTICE:
            case E_USER_NOTICE:
                return 'NOTICE';
            case E_STRICT:
                return 'STRICT';
            case E_DEPRECATED:
            case E_USER_DEPRECATED:
                return 'DEPRECATED';
            default:
                return 'ERROR';
        }
    }
}
