<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

/**
 * Log Viewer Controller
 */
class LogViewerController extends BaseController
{
    private $logPath;
    
    public function __construct()
    {
        parent::__construct();
        $this->logPath = __DIR__ . '/../../../storage/logs/';
    }
    
    /**
     * Show log files list and viewer
     */
    public function index()
    {
        $selectedFile = $this->input('file', '');
        $logFiles = $this->getLogFiles();
        $logContent = '';
        $totalLines = 0;
        
        if ($selectedFile && file_exists($this->logPath . $selectedFile)) {
            $logContent = $this->getLogContent($selectedFile);
            $totalLines = substr_count($logContent, "\n");
        } elseif (!empty($logFiles)) {
            // Default to latest log file
            $selectedFile = $logFiles[0];
            $logContent = $this->getLogContent($selectedFile);
            $totalLines = substr_count($logContent, "\n");
        }
        
        echo $this->view('admin.logs.index-new', [
            'title' => 'Log Viewer',
            'logFiles' => $logFiles,
            'selectedFile' => $selectedFile,
            'logContent' => $logContent,
            'totalLines' => $totalLines
        ]);
    }
    
    /**
     * Download log file
     */
    public function download()
    {
        $file = $this->input('file', '');
        
        if (!$file || !file_exists($this->logPath . $file)) {
            return $this->redirect('/admin/logs')->with('error', 'Log file not found');
        }
        
        $filePath = $this->logPath . $file;
        
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $file . '"');
        header('Content-Length: ' . filesize($filePath));
        
        readfile($filePath);
        exit;
    }
    
    /**
     * Clear log file
     */
    public function clear()
    {
        $file = $this->input('file', '');
        
        if (!$file || !file_exists($this->logPath . $file)) {
            return $this->redirect('/admin/logs')->with('error', 'Log file not found');
        }
        
        try {
            file_put_contents($this->logPath . $file, '');
            return $this->redirect('/admin/logs?file=' . $file)->with('success', 'Log file cleared successfully');
        } catch (\Exception $e) {
            return $this->redirect('/admin/logs')->with('error', 'Failed to clear log file: ' . $e->getMessage());
        }
    }
    
    /**
     * Get list of log files
     */
    private function getLogFiles()
    {
        $files = [];
        
        if (!is_dir($this->logPath)) {
            return $files;
        }
        
        $logFiles = glob($this->logPath . '*.log');
        
        foreach ($logFiles as $file) {
            $filename = basename($file);
            $files[] = $filename;
        }
        
        // Sort by modification time (newest first)
        usort($files, function($a, $b) {
            $timeA = filemtime($this->logPath . $a);
            $timeB = filemtime($this->logPath . $b);
            return $timeB - $timeA;
        });
        
        return $files;
    }
    
    /**
     * Get log file content
     */
    private function getLogContent($filename)
    {
        $filePath = $this->logPath . $filename;
        
        if (!file_exists($filePath)) {
            return 'Log file not found.';
        }
        
        $content = file_get_contents($filePath);
        
        if ($content === false) {
            return 'Unable to read log file.';
        }
        
        // Limit content size for performance (last 50KB)
        if (strlen($content) > 51200) {
            $content = '... (showing last 50KB of log file) ...' . "\n\n" . substr($content, -51200);
        }
        
        return $content;
    }
    
    /**
     * Get log file info
     */
    private function getLogFileInfo($filename)
    {
        $filePath = $this->logPath . $filename;
        
        if (!file_exists($filePath)) {
            return null;
        }
        
        return [
            'name' => $filename,
            'size' => filesize($filePath),
            'modified' => filemtime($filePath),
            'readable' => is_readable($filePath),
            'writable' => is_writable($filePath)
        ];
    }
}
