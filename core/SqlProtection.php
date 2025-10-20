<?php

namespace Core;

/**
 * SQL Injection Protection
 */
class SqlProtection
{
    /**
     * Dangerous SQL patterns
     */
    private static $dangerousPatterns = [
        // Union-based injection
        '/(\s|^)(union|select|insert|update|delete|drop|create|alter|exec|execute)(\s|$)/i',
        
        // Boolean-based blind injection
        '/(\s|^)(or|and)(\s|$)(\d+(\s|$)=(\s|$)\d+|\'\w*\'(\s|$)=(\s|$)\'\w*\')/i',
        
        // Time-based blind injection
        '/(sleep|benchmark|waitfor|delay)\s*\(/i',
        
        // Error-based injection
        '/(extractvalue|updatexml|exp|floor|rand)\s*\(/i',
        
        // Stacked queries
        '/;\s*(select|insert|update|delete|drop|create|alter)/i',
        
        // Comment injection
        '/\/\*.*?\*\/|--.*$|#.*$/m',
        
        // Quote escaping attempts
        '/\\\\[\'"]|[\'"].*?[\'"]/',
        
        // Hex encoding attempts
        '/0x[0-9a-f]+/i',
        
        // Function calls that shouldn't be in user input
        '/(concat|char|ascii|substring|mid|left|right|length|database|version|user|system_user)\s*\(/i'
    ];
    
    /**
     * Check if input contains SQL injection patterns
     */
    public static function detectInjection($input)
    {
        if (!is_string($input)) {
            return false;
        }
        
        $input = strtolower($input);
        
        foreach (self::$dangerousPatterns as $pattern) {
            if (preg_match($pattern, $input)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Clean SQL input
     */
    public static function cleanInput($input)
    {
        if (!is_string($input)) {
            return $input;
        }
        
        // Remove null bytes
        $input = str_replace("\0", '', $input);
        
        // Remove dangerous patterns
        foreach (self::$dangerousPatterns as $pattern) {
            $input = preg_replace($pattern, '', $input);
        }
        
        return trim($input);
    }
    
    /**
     * Escape string for SQL (additional layer)
     */
    public static function escapeString($string, $connection = null)
    {
        if ($connection && method_exists($connection, 'real_escape_string')) {
            return $connection->real_escape_string($string);
        }
        
        // Fallback escaping
        return addslashes($string);
    }
    
    /**
     * Validate table/column names
     */
    public static function validateIdentifier($identifier)
    {
        // Only allow alphanumeric characters and underscores
        return preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $identifier);
    }
    
    /**
     * Sanitize ORDER BY clause
     */
    public static function sanitizeOrderBy($orderBy, $allowedColumns = [])
    {
        if (!is_string($orderBy)) {
            return 'id ASC';
        }
        
        $parts = explode(' ', trim($orderBy));
        $column = $parts[0] ?? 'id';
        $direction = strtoupper($parts[1] ?? 'ASC');
        
        // Validate column name
        if (!self::validateIdentifier($column)) {
            $column = 'id';
        }
        
        // Check against allowed columns if provided
        if (!empty($allowedColumns) && !in_array($column, $allowedColumns)) {
            $column = $allowedColumns[0] ?? 'id';
        }
        
        // Validate direction
        if (!in_array($direction, ['ASC', 'DESC'])) {
            $direction = 'ASC';
        }
        
        return $column . ' ' . $direction;
    }
    
    /**
     * Sanitize LIMIT clause
     */
    public static function sanitizeLimit($limit, $maxLimit = 1000)
    {
        $limit = (int) $limit;
        
        if ($limit <= 0) {
            return 10;
        }
        
        if ($limit > $maxLimit) {
            return $maxLimit;
        }
        
        return $limit;
    }
    
    /**
     * Sanitize OFFSET clause
     */
    public static function sanitizeOffset($offset)
    {
        $offset = (int) $offset;
        return max(0, $offset);
    }
    
    /**
     * Build safe WHERE conditions
     */
    public static function buildWhereConditions($conditions, $allowedColumns = [])
    {
        if (!is_array($conditions) || empty($conditions)) {
            return ['1 = 1', []];
        }
        
        $whereParts = [];
        $params = [];
        
        foreach ($conditions as $column => $value) {
            // Validate column name
            if (!self::validateIdentifier($column)) {
                continue;
            }
            
            // Check against allowed columns
            if (!empty($allowedColumns) && !in_array($column, $allowedColumns)) {
                continue;
            }
            
            if (is_array($value)) {
                // IN clause
                $placeholders = str_repeat('?,', count($value) - 1) . '?';
                $whereParts[] = "`{$column}` IN ({$placeholders})";
                $params = array_merge($params, $value);
            } elseif (is_null($value)) {
                // NULL check
                $whereParts[] = "`{$column}` IS NULL";
            } else {
                // Equality check
                $whereParts[] = "`{$column}` = ?";
                $params[] = $value;
            }
        }
        
        $whereClause = empty($whereParts) ? '1 = 1' : implode(' AND ', $whereParts);
        
        return [$whereClause, $params];
    }
    
    /**
     * Log potential SQL injection attempts
     */
    public static function logInjectionAttempt($input, $userInfo = [])
    {
        $logData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'input' => $input,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'user_info' => $userInfo,
            'request_uri' => $_SERVER['REQUEST_URI'] ?? 'unknown'
        ];
        
        $logFile = __DIR__ . '/../storage/logs/sql_injection_attempts.log';
        $logEntry = json_encode($logData) . "\n";
        
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
        
        // Also log to main error log
        error_log("SQL Injection Attempt: " . $input . " from IP: " . ($logData['ip']));
    }
    
    /**
     * Middleware to check all inputs
     */
    public static function checkAllInputs()
    {
        $allInputs = array_merge($_GET, $_POST, $_COOKIE);
        
        foreach ($allInputs as $key => $value) {
            if (is_string($value) && self::detectInjection($value)) {
                self::logInjectionAttempt($value, [
                    'input_type' => 'form_data',
                    'field_name' => $key
                ]);
                
                // Block the request
                http_response_code(403);
                die('Forbidden: Potential SQL injection detected');
            }
        }
    }
    
    /**
     * Safe query builder helper
     */
    public static function buildSelectQuery($table, $columns = ['*'], $conditions = [], $orderBy = null, $limit = null, $offset = null)
    {
        // Validate table name
        if (!self::validateIdentifier($table)) {
            throw new \InvalidArgumentException('Invalid table name');
        }
        
        // Validate columns
        $safeColumns = [];
        foreach ((array) $columns as $column) {
            if ($column === '*' || self::validateIdentifier($column)) {
                $safeColumns[] = $column === '*' ? '*' : "`{$column}`";
            }
        }
        
        if (empty($safeColumns)) {
            $safeColumns = ['*'];
        }
        
        $sql = "SELECT " . implode(', ', $safeColumns) . " FROM `{$table}`";
        $params = [];
        
        // Add WHERE conditions
        if (!empty($conditions)) {
            list($whereClause, $whereParams) = self::buildWhereConditions($conditions);
            $sql .= " WHERE " . $whereClause;
            $params = array_merge($params, $whereParams);
        }
        
        // Add ORDER BY
        if ($orderBy) {
            $sql .= " ORDER BY " . self::sanitizeOrderBy($orderBy);
        }
        
        // Add LIMIT
        if ($limit) {
            $sql .= " LIMIT " . self::sanitizeLimit($limit);
            
            if ($offset) {
                $sql .= " OFFSET " . self::sanitizeOffset($offset);
            }
        }
        
        return [$sql, $params];
    }
}
