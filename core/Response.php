<?php

namespace Core;

/**
 * HTTP Response Handler
 */
class Response
{
    private static $instance = null;
    private $headers = [];
    private $statusCode = 200;
    
    private function __construct()
    {
        // Singleton pattern
    }
    
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Set response status code
     */
    public function status($code)
    {
        $this->statusCode = $code;
        http_response_code($code);
        return $this;
    }
    
    /**
     * Set response header
     */
    public function header($key, $value)
    {
        $this->headers[$key] = $value;
        header("{$key}: {$value}");
        return $this;
    }
    
    /**
     * Set multiple headers
     */
    public function headers($headers)
    {
        foreach ($headers as $key => $value) {
            $this->header($key, $value);
        }
        return $this;
    }
    
    /**
     * Return JSON response
     */
    public function json($data, $status = 200)
    {
        $this->status($status);
        $this->header('Content-Type', 'application/json');
        
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        return $this;
    }
    
    /**
     * Return HTML response
     */
    public function html($content, $status = 200)
    {
        $this->status($status);
        $this->header('Content-Type', 'text/html; charset=UTF-8');
        
        echo $content;
        return $this;
    }
    
    /**
     * Return plain text response
     */
    public function text($content, $status = 200)
    {
        $this->status($status);
        $this->header('Content-Type', 'text/plain; charset=UTF-8');
        
        echo $content;
        return $this;
    }
    
    /**
     * Redirect to URL
     */
    public function redirect($url, $status = 302)
    {
        $this->status($status);
        $this->header('Location', $url);
        exit;
    }
    
    /**
     * Redirect back to previous page
     */
    public function back()
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        $this->redirect($referer);
    }
    
    /**
     * Download file
     */
    public function download($filePath, $fileName = null, $headers = [])
    {
        if (!file_exists($filePath)) {
            $this->status(404);
            echo "File not found";
            return $this;
        }
        
        $fileName = $fileName ?: basename($filePath);
        $fileSize = filesize($filePath);
        $mimeType = mime_content_type($filePath) ?: 'application/octet-stream';
        
        $this->header('Content-Type', $mimeType);
        $this->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
        $this->header('Content-Length', $fileSize);
        $this->header('Cache-Control', 'must-revalidate');
        $this->header('Pragma', 'public');
        
        // Set additional headers
        foreach ($headers as $key => $value) {
            $this->header($key, $value);
        }
        
        // Output file
        readfile($filePath);
        return $this;
    }
    
    /**
     * Stream file (for images, videos, etc.)
     */
    public function stream($filePath, $headers = [])
    {
        if (!file_exists($filePath)) {
            $this->status(404);
            echo "File not found";
            return $this;
        }
        
        $fileSize = filesize($filePath);
        $mimeType = mime_content_type($filePath) ?: 'application/octet-stream';
        
        $this->header('Content-Type', $mimeType);
        $this->header('Content-Length', $fileSize);
        $this->header('Accept-Ranges', 'bytes');
        
        // Set additional headers
        foreach ($headers as $key => $value) {
            $this->header($key, $value);
        }
        
        // Handle range requests (for video streaming)
        if (isset($_SERVER['HTTP_RANGE'])) {
            $this->handleRangeRequest($filePath, $fileSize);
        } else {
            readfile($filePath);
        }
        
        return $this;
    }
    
    /**
     * Handle HTTP range requests
     */
    private function handleRangeRequest($filePath, $fileSize)
    {
        $range = $_SERVER['HTTP_RANGE'];
        $ranges = explode('=', $range, 2);
        
        if (count($ranges) != 2 || $ranges[0] != 'bytes') {
            $this->status(416); // Range Not Satisfiable
            return;
        }
        
        $range = explode('-', $ranges[1], 2);
        $start = $range[0];
        $end = isset($range[1]) && $range[1] ? $range[1] : $fileSize - 1;
        
        $start = intval($start);
        $end = intval($end);
        
        if ($start > $end || $start >= $fileSize || $end >= $fileSize) {
            $this->status(416); // Range Not Satisfiable
            return;
        }
        
        $this->status(206); // Partial Content
        $this->header('Content-Range', "bytes {$start}-{$end}/{$fileSize}");
        $this->header('Content-Length', $end - $start + 1);
        
        $file = fopen($filePath, 'rb');
        fseek($file, $start);
        
        $bufferSize = 8192;
        $bytesRemaining = $end - $start + 1;
        
        while ($bytesRemaining > 0 && !feof($file)) {
            $bytesToRead = min($bufferSize, $bytesRemaining);
            echo fread($file, $bytesToRead);
            $bytesRemaining -= $bytesToRead;
            
            if (connection_aborted()) {
                break;
            }
        }
        
        fclose($file);
    }
    
    /**
     * Set cookie
     */
    public function cookie($name, $value, $expire = 0, $path = '/', $domain = '', $secure = false, $httpOnly = true)
    {
        setcookie($name, $value, $expire, $path, $domain, $secure, $httpOnly);
        return $this;
    }
    
    /**
     * Delete cookie
     */
    public function deleteCookie($name, $path = '/', $domain = '')
    {
        setcookie($name, '', time() - 3600, $path, $domain);
        return $this;
    }
    
    /**
     * Get current status code
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }
    
    /**
     * Get response headers
     */
    public function getHeaders()
    {
        return $this->headers;
    }
}
