<?php

namespace Core;

/**
 * Database Query Optimizer
 */
class QueryOptimizer
{
    private static $queryLog = [];
    private static $slowQueryThreshold = 1000; // milliseconds
    
    /**
     * Log query execution time
     */
    public static function logQuery($query, $params, $executionTime)
    {
        $logEntry = [
            'query' => $query,
            'params' => $params,
            'execution_time' => $executionTime,
            'timestamp' => microtime(true),
            'memory_usage' => memory_get_usage(true)
        ];
        
        self::$queryLog[] = $logEntry;
        
        // Log slow queries
        if ($executionTime > self::$slowQueryThreshold) {
            self::logSlowQuery($logEntry);
        }
    }
    
    /**
     * Log slow queries
     */
    private static function logSlowQuery($logEntry)
    {
        $logFile = __DIR__ . '/../storage/logs/slow_queries.log';
        $logData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'query' => $logEntry['query'],
            'params' => $logEntry['params'],
            'execution_time' => $logEntry['execution_time'] . 'ms',
            'memory_usage' => self::formatBytes($logEntry['memory_usage'])
        ];
        
        file_put_contents($logFile, json_encode($logData) . "\n", FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Get query statistics
     */
    public static function getStats()
    {
        if (empty(self::$queryLog)) {
            return [
                'total_queries' => 0,
                'total_time' => 0,
                'average_time' => 0,
                'slow_queries' => 0
            ];
        }
        
        $totalTime = array_sum(array_column(self::$queryLog, 'execution_time'));
        $slowQueries = count(array_filter(self::$queryLog, function($log) {
            return $log['execution_time'] > self::$slowQueryThreshold;
        }));
        
        return [
            'total_queries' => count(self::$queryLog),
            'total_time' => round($totalTime, 2),
            'average_time' => round($totalTime / count(self::$queryLog), 2),
            'slow_queries' => $slowQueries,
            'memory_peak' => self::formatBytes(memory_get_peak_usage(true))
        ];
    }
    
    /**
     * Optimize SELECT query
     */
    public static function optimizeSelect($table, $columns = ['*'], $conditions = [], $options = [])
    {
        $optimizations = [];
        
        // Suggest specific columns instead of *
        if (in_array('*', $columns)) {
            $optimizations[] = "Consider selecting specific columns instead of * for table '{$table}'";
        }
        
        // Check for missing WHERE clause
        if (empty($conditions)) {
            $optimizations[] = "Consider adding WHERE conditions to limit results for table '{$table}'";
        }
        
        // Check for LIMIT clause
        if (!isset($options['limit'])) {
            $optimizations[] = "Consider adding LIMIT clause for table '{$table}' to prevent large result sets";
        }
        
        // Check for ORDER BY without LIMIT
        if (isset($options['order_by']) && !isset($options['limit'])) {
            $optimizations[] = "ORDER BY without LIMIT can be expensive for table '{$table}'";
        }
        
        return $optimizations;
    }
    
    /**
     * Suggest indexes based on query patterns
     */
    public static function suggestIndexes()
    {
        $suggestions = [];
        $queryPatterns = [];
        
        foreach (self::$queryLog as $log) {
            $query = strtolower($log['query']);
            
            // Extract WHERE conditions
            if (preg_match('/where\s+(.+?)(?:\s+order\s+by|\s+group\s+by|\s+limit|$)/i', $query, $matches)) {
                $whereClause = $matches[1];
                
                // Find column references
                if (preg_match_all('/(\w+)\s*[=<>!]/i', $whereClause, $columnMatches)) {
                    foreach ($columnMatches[1] as $column) {
                        if (!isset($queryPatterns[$column])) {
                            $queryPatterns[$column] = 0;
                        }
                        $queryPatterns[$column]++;
                    }
                }
            }
            
            // Extract JOIN conditions
            if (preg_match_all('/join\s+\w+\s+on\s+(\w+)\s*=\s*(\w+)/i', $query, $joinMatches)) {
                foreach ($joinMatches[1] as $column) {
                    if (!isset($queryPatterns[$column])) {
                        $queryPatterns[$column] = 0;
                    }
                    $queryPatterns[$column] += 2; // JOINs are more important
                }
            }
        }
        
        // Suggest indexes for frequently used columns
        arsort($queryPatterns);
        foreach ($queryPatterns as $column => $frequency) {
            if ($frequency >= 3) {
                $suggestions[] = "Consider adding index on column '{$column}' (used {$frequency} times)";
            }
        }
        
        return $suggestions;
    }
    
    /**
     * Analyze query performance
     */
    public static function analyzeQuery($query, $params = [])
    {
        $analysis = [
            'query' => $query,
            'issues' => [],
            'suggestions' => []
        ];
        
        $query = strtolower($query);
        
        // Check for SELECT *
        if (strpos($query, 'select *') !== false) {
            $analysis['issues'][] = 'Using SELECT * - consider selecting specific columns';
        }
        
        // Check for missing WHERE clause in UPDATE/DELETE
        if ((strpos($query, 'update ') === 0 || strpos($query, 'delete ') === 0) && strpos($query, 'where') === false) {
            $analysis['issues'][] = 'UPDATE/DELETE without WHERE clause - this will affect all rows';
        }
        
        // Check for LIKE with leading wildcard
        if (preg_match('/like\s+[\'"]%/i', $query)) {
            $analysis['issues'][] = 'LIKE with leading wildcard cannot use indexes efficiently';
        }
        
        // Check for OR conditions
        if (preg_match('/\s+or\s+/i', $query)) {
            $analysis['suggestions'][] = 'OR conditions can prevent index usage - consider UNION if appropriate';
        }
        
        // Check for functions in WHERE clause
        if (preg_match('/where\s+\w+\s*\(/i', $query)) {
            $analysis['issues'][] = 'Functions in WHERE clause prevent index usage';
        }
        
        // Check for ORDER BY without LIMIT
        if (strpos($query, 'order by') !== false && strpos($query, 'limit') === false) {
            $analysis['suggestions'][] = 'ORDER BY without LIMIT can be expensive for large result sets';
        }
        
        return $analysis;
    }
    
    /**
     * Get N+1 query detection
     */
    public static function detectNPlusOne($threshold = 10)
    {
        $suspiciousPatterns = [];
        $queryGroups = [];
        
        foreach (self::$queryLog as $log) {
            $normalizedQuery = preg_replace('/\d+/', '?', $log['query']);
            $normalizedQuery = preg_replace('/[\'"][^\'\"]*[\'"]/', '?', $normalizedQuery);
            
            if (!isset($queryGroups[$normalizedQuery])) {
                $queryGroups[$normalizedQuery] = 0;
            }
            $queryGroups[$normalizedQuery]++;
        }
        
        foreach ($queryGroups as $query => $count) {
            if ($count >= $threshold) {
                $suspiciousPatterns[] = [
                    'query' => $query,
                    'count' => $count,
                    'likely_n_plus_one' => true
                ];
            }
        }
        
        return $suspiciousPatterns;
    }
    
    /**
     * Cache query results
     */
    public static function cacheQuery($query, $params, $result, $ttl = 3600)
    {
        $cacheKey = 'query_' . md5($query . serialize($params));
        
        $cache = Cache::getInstance();
        $cache->set($cacheKey, $result, $ttl);
        
        return $cacheKey;
    }
    
    /**
     * Get cached query result
     */
    public static function getCachedQuery($query, $params)
    {
        $cacheKey = 'query_' . md5($query . serialize($params));
        
        $cache = Cache::getInstance();
        return $cache->get($cacheKey);
    }
    
    /**
     * Format bytes
     */
    private static function formatBytes($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
    
    /**
     * Generate performance report
     */
    public static function generateReport()
    {
        $stats = self::getStats();
        $nPlusOne = self::detectNPlusOne();
        $indexSuggestions = self::suggestIndexes();
        
        return [
            'statistics' => $stats,
            'n_plus_one_queries' => $nPlusOne,
            'index_suggestions' => $indexSuggestions,
            'slow_queries' => array_filter(self::$queryLog, function($log) {
                return $log['execution_time'] > self::$slowQueryThreshold;
            })
        ];
    }
    
    /**
     * Clear query log
     */
    public static function clearLog()
    {
        self::$queryLog = [];
    }
    
    /**
     * Set slow query threshold
     */
    public static function setSlowQueryThreshold($milliseconds)
    {
        self::$slowQueryThreshold = $milliseconds;
    }
}
