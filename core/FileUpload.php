<?php

namespace Core;

/**
 * File Upload Handler
 */
class FileUpload
{
    private $uploadPath;
    private $allowedTypes = [];
    private $maxSize = 10485760; // 10MB default
    private $allowedExtensions = [];
    private $generateUniqueNames = true;
    
    public function __construct($uploadPath = null)
    {
        $this->uploadPath = $uploadPath ?: __DIR__ . '/../storage/uploads/';
        
        // Create upload directory if it doesn't exist
        if (!is_dir($this->uploadPath)) {
            mkdir($this->uploadPath, 0755, true);
        }
    }
    
    /**
     * Set upload path
     */
    public function setUploadPath($path)
    {
        $this->uploadPath = rtrim($path, '/') . '/';
        
        if (!is_dir($this->uploadPath)) {
            mkdir($this->uploadPath, 0755, true);
        }
        
        return $this;
    }
    
    /**
     * Set allowed MIME types
     */
    public function setAllowedTypes($types)
    {
        $this->allowedTypes = is_array($types) ? $types : [$types];
        return $this;
    }
    
    /**
     * Set allowed file extensions
     */
    public function setAllowedExtensions($extensions)
    {
        $this->allowedExtensions = is_array($extensions) ? $extensions : [$extensions];
        return $this;
    }
    
    /**
     * Set maximum file size
     */
    public function setMaxSize($size)
    {
        $this->maxSize = $size;
        return $this;
    }
    
    /**
     * Enable/disable unique name generation
     */
    public function setGenerateUniqueNames($generate)
    {
        $this->generateUniqueNames = $generate;
        return $this;
    }
    
    /**
     * Upload single file
     */
    public function upload($file, $subfolder = '')
    {
        if (!$this->isValidUpload($file)) {
            throw new \Exception('Invalid file upload');
        }
        
        $this->validateFile($file);
        
        $uploadPath = $this->uploadPath . ltrim($subfolder, '/');
        if ($subfolder && !is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }
        
        $filename = $this->generateFilename($file);
        $fullPath = rtrim($uploadPath, '/') . '/' . $filename;
        
        if (!move_uploaded_file($file['tmp_name'], $fullPath)) {
            throw new \Exception('Failed to move uploaded file');
        }
        
        return [
            'filename' => $filename,
            'original_name' => $file['name'],
            'path' => $fullPath,
            'relative_path' => str_replace($this->uploadPath, '', $fullPath),
            'size' => $file['size'],
            'type' => $file['type'],
            'extension' => $this->getFileExtension($file['name'])
        ];
    }
    
    /**
     * Upload multiple files
     */
    public function uploadMultiple($files, $subfolder = '')
    {
        $results = [];
        
        // Handle array of files or single file with multiple entries
        if (isset($files['name']) && is_array($files['name'])) {
            // Multiple files in single input
            for ($i = 0; $i < count($files['name']); $i++) {
                $file = [
                    'name' => $files['name'][$i],
                    'type' => $files['type'][$i],
                    'tmp_name' => $files['tmp_name'][$i],
                    'error' => $files['error'][$i],
                    'size' => $files['size'][$i]
                ];
                
                if ($file['error'] === UPLOAD_ERR_OK) {
                    try {
                        $results[] = $this->upload($file, $subfolder);
                    } catch (\Exception $e) {
                        $results[] = ['error' => $e->getMessage(), 'file' => $file['name']];
                    }
                }
            }
        } else {
            // Array of separate file inputs
            foreach ($files as $file) {
                if ($file['error'] === UPLOAD_ERR_OK) {
                    try {
                        $results[] = $this->upload($file, $subfolder);
                    } catch (\Exception $e) {
                        $results[] = ['error' => $e->getMessage(), 'file' => $file['name']];
                    }
                }
            }
        }
        
        return $results;
    }
    
    /**
     * Validate uploaded file
     */
    private function validateFile($file)
    {
        // Check file size
        if ($file['size'] > $this->maxSize) {
            throw new \Exception('File size exceeds maximum allowed size (' . $this->formatBytes($this->maxSize) . ')');
        }
        
        // Check MIME type
        if (!empty($this->allowedTypes)) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            
            if (!in_array($mimeType, $this->allowedTypes)) {
                throw new \Exception('File type not allowed. Allowed types: ' . implode(', ', $this->allowedTypes));
            }
        }
        
        // Check file extension
        if (!empty($this->allowedExtensions)) {
            $extension = strtolower($this->getFileExtension($file['name']));
            
            if (!in_array($extension, array_map('strtolower', $this->allowedExtensions))) {
                throw new \Exception('File extension not allowed. Allowed extensions: ' . implode(', ', $this->allowedExtensions));
            }
        }
        
        // Additional security checks
        $this->performSecurityChecks($file);
    }
    
    /**
     * Perform additional security checks
     */
    private function performSecurityChecks($file)
    {
        // Check for executable files
        $dangerousExtensions = ['php', 'phtml', 'php3', 'php4', 'php5', 'pl', 'py', 'jsp', 'asp', 'sh', 'cgi'];
        $extension = strtolower($this->getFileExtension($file['name']));
        
        if (in_array($extension, $dangerousExtensions)) {
            throw new \Exception('Executable files are not allowed');
        }
        
        // Check file content for PHP tags (basic check)
        $content = file_get_contents($file['tmp_name']);
        if (strpos($content, '<?php') !== false || strpos($content, '<?=') !== false) {
            throw new \Exception('Files containing PHP code are not allowed');
        }
    }
    
    /**
     * Check if upload is valid
     */
    private function isValidUpload($file)
    {
        return isset($file['error']) && $file['error'] === UPLOAD_ERR_OK;
    }
    
    /**
     * Generate filename
     */
    private function generateFilename($file)
    {
        $extension = $this->getFileExtension($file['name']);
        
        if ($this->generateUniqueNames) {
            return uniqid() . '_' . time() . '.' . $extension;
        } else {
            // Sanitize original filename
            $filename = pathinfo($file['name'], PATHINFO_FILENAME);
            $filename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $filename);
            return $filename . '.' . $extension;
        }
    }
    
    /**
     * Get file extension
     */
    private function getFileExtension($filename)
    {
        return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    }
    
    /**
     * Format bytes to human readable format
     */
    private function formatBytes($size, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }
        
        return round($size, $precision) . ' ' . $units[$i];
    }
    
    /**
     * Delete uploaded file
     */
    public function delete($filePath)
    {
        $fullPath = $this->uploadPath . ltrim($filePath, '/');
        
        if (file_exists($fullPath)) {
            return unlink($fullPath);
        }
        
        return false;
    }
    
    /**
     * Get file info
     */
    public function getFileInfo($filePath)
    {
        $fullPath = $this->uploadPath . ltrim($filePath, '/');
        
        if (!file_exists($fullPath)) {
            return null;
        }
        
        return [
            'filename' => basename($fullPath),
            'path' => $fullPath,
            'size' => filesize($fullPath),
            'type' => mime_content_type($fullPath),
            'extension' => $this->getFileExtension($fullPath),
            'created' => filectime($fullPath),
            'modified' => filemtime($fullPath)
        ];
    }
    
    /**
     * Create image upload handler
     */
    public static function images($uploadPath = null)
    {
        $uploader = new self($uploadPath);
        $uploader->setAllowedTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
        $uploader->setAllowedExtensions(['jpg', 'jpeg', 'png', 'gif', 'webp']);
        $uploader->setMaxSize(5242880); // 5MB
        
        return $uploader;
    }
    
    /**
     * Create document upload handler
     */
    public static function documents($uploadPath = null)
    {
        $uploader = new self($uploadPath);
        $uploader->setAllowedTypes([
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'text/plain',
            'text/csv'
        ]);
        $uploader->setAllowedExtensions(['pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt', 'csv']);
        $uploader->setMaxSize(10485760); // 10MB
        
        return $uploader;
    }
    
    /**
     * Create avatar upload handler
     */
    public static function avatars($uploadPath = null)
    {
        $uploader = new self($uploadPath ?: __DIR__ . '/../storage/uploads/avatars/');
        $uploader->setAllowedTypes(['image/jpeg', 'image/png']);
        $uploader->setAllowedExtensions(['jpg', 'jpeg', 'png']);
        $uploader->setMaxSize(2097152); // 2MB
        
        return $uploader;
    }
}
