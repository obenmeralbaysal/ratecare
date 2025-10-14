<?php

namespace Core;

/**
 * Image Processing Utilities
 */
class Image
{
    private $image;
    private $width;
    private $height;
    private $type;
    private $quality = 90;
    
    public function __construct($imagePath = null)
    {
        if ($imagePath) {
            $this->load($imagePath);
        }
    }
    
    /**
     * Load image from file
     */
    public function load($imagePath)
    {
        if (!file_exists($imagePath)) {
            throw new \Exception("Image file not found: {$imagePath}");
        }
        
        $imageInfo = getimagesize($imagePath);
        if (!$imageInfo) {
            throw new \Exception("Invalid image file: {$imagePath}");
        }
        
        $this->width = $imageInfo[0];
        $this->height = $imageInfo[1];
        $this->type = $imageInfo[2];
        
        switch ($this->type) {
            case IMAGETYPE_JPEG:
                $this->image = imagecreatefromjpeg($imagePath);
                break;
            case IMAGETYPE_PNG:
                $this->image = imagecreatefrompng($imagePath);
                imagealphablending($this->image, false);
                imagesavealpha($this->image, true);
                break;
            case IMAGETYPE_GIF:
                $this->image = imagecreatefromgif($imagePath);
                break;
            case IMAGETYPE_WEBP:
                $this->image = imagecreatefromwebp($imagePath);
                break;
            default:
                throw new \Exception("Unsupported image type");
        }
        
        return $this;
    }
    
    /**
     * Create image from resource
     */
    public function fromResource($resource, $width, $height)
    {
        $this->image = $resource;
        $this->width = $width;
        $this->height = $height;
        $this->type = IMAGETYPE_PNG; // Default type
        
        return $this;
    }
    
    /**
     * Set JPEG quality
     */
    public function quality($quality)
    {
        $this->quality = max(0, min(100, $quality));
        return $this;
    }
    
    /**
     * Resize image
     */
    public function resize($newWidth, $newHeight, $maintainAspectRatio = true)
    {
        if ($maintainAspectRatio) {
            $ratio = min($newWidth / $this->width, $newHeight / $this->height);
            $newWidth = $this->width * $ratio;
            $newHeight = $this->height * $ratio;
        }
        
        $newImage = imagecreatetruecolor($newWidth, $newHeight);
        
        // Preserve transparency for PNG and GIF
        if ($this->type == IMAGETYPE_PNG || $this->type == IMAGETYPE_GIF) {
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);
            $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
            imagefilledrectangle($newImage, 0, 0, $newWidth, $newHeight, $transparent);
        }
        
        imagecopyresampled(
            $newImage, $this->image,
            0, 0, 0, 0,
            $newWidth, $newHeight,
            $this->width, $this->height
        );
        
        imagedestroy($this->image);
        $this->image = $newImage;
        $this->width = $newWidth;
        $this->height = $newHeight;
        
        return $this;
    }
    
    /**
     * Crop image
     */
    public function crop($x, $y, $width, $height)
    {
        $newImage = imagecreatetruecolor($width, $height);
        
        // Preserve transparency
        if ($this->type == IMAGETYPE_PNG || $this->type == IMAGETYPE_GIF) {
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);
            $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
            imagefilledrectangle($newImage, 0, 0, $width, $height, $transparent);
        }
        
        imagecopyresampled(
            $newImage, $this->image,
            0, 0, $x, $y,
            $width, $height, $width, $height
        );
        
        imagedestroy($this->image);
        $this->image = $newImage;
        $this->width = $width;
        $this->height = $height;
        
        return $this;
    }
    
    /**
     * Create thumbnail
     */
    public function thumbnail($size, $crop = true)
    {
        if ($crop) {
            // Square crop from center
            $smallestSide = min($this->width, $this->height);
            $x = ($this->width - $smallestSide) / 2;
            $y = ($this->height - $smallestSide) / 2;
            
            $this->crop($x, $y, $smallestSide, $smallestSide);
            $this->resize($size, $size, false);
        } else {
            // Resize maintaining aspect ratio
            $this->resize($size, $size, true);
        }
        
        return $this;
    }
    
    /**
     * Add watermark
     */
    public function watermark($watermarkPath, $position = 'bottom-right', $opacity = 50)
    {
        $watermark = new self($watermarkPath);
        
        // Calculate position
        switch ($position) {
            case 'top-left':
                $x = 10;
                $y = 10;
                break;
            case 'top-right':
                $x = $this->width - $watermark->width - 10;
                $y = 10;
                break;
            case 'bottom-left':
                $x = 10;
                $y = $this->height - $watermark->height - 10;
                break;
            case 'bottom-right':
                $x = $this->width - $watermark->width - 10;
                $y = $this->height - $watermark->height - 10;
                break;
            case 'center':
                $x = ($this->width - $watermark->width) / 2;
                $y = ($this->height - $watermark->height) / 2;
                break;
            default:
                $x = 10;
                $y = 10;
        }
        
        // Apply watermark with opacity
        imagecopymerge(
            $this->image, $watermark->image,
            $x, $y, 0, 0,
            $watermark->width, $watermark->height,
            $opacity
        );
        
        return $this;
    }
    
    /**
     * Apply filter
     */
    public function filter($filter, $arg1 = null, $arg2 = null, $arg3 = null, $arg4 = null)
    {
        imagefilter($this->image, $filter, $arg1, $arg2, $arg3, $arg4);
        return $this;
    }
    
    /**
     * Convert to grayscale
     */
    public function grayscale()
    {
        return $this->filter(IMG_FILTER_GRAYSCALE);
    }
    
    /**
     * Adjust brightness
     */
    public function brightness($level)
    {
        return $this->filter(IMG_FILTER_BRIGHTNESS, $level);
    }
    
    /**
     * Adjust contrast
     */
    public function contrast($level)
    {
        return $this->filter(IMG_FILTER_CONTRAST, $level);
    }
    
    /**
     * Apply blur
     */
    public function blur($type = IMG_FILTER_GAUSSIAN_BLUR)
    {
        return $this->filter($type);
    }
    
    /**
     * Rotate image
     */
    public function rotate($angle, $backgroundColor = 0)
    {
        $this->image = imagerotate($this->image, $angle, $backgroundColor);
        
        // Update dimensions
        $this->width = imagesx($this->image);
        $this->height = imagesy($this->image);
        
        return $this;
    }
    
    /**
     * Flip image
     */
    public function flip($mode = IMG_FLIP_HORIZONTAL)
    {
        imageflip($this->image, $mode);
        return $this;
    }
    
    /**
     * Save image to file
     */
    public function save($path, $type = null)
    {
        $type = $type ?: $this->type;
        
        // Create directory if it doesn't exist
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        switch ($type) {
            case IMAGETYPE_JPEG:
                return imagejpeg($this->image, $path, $this->quality);
            case IMAGETYPE_PNG:
                return imagepng($this->image, $path);
            case IMAGETYPE_GIF:
                return imagegif($this->image, $path);
            case IMAGETYPE_WEBP:
                return imagewebp($this->image, $path, $this->quality);
            default:
                throw new \Exception("Unsupported image type for saving");
        }
    }
    
    /**
     * Output image to browser
     */
    public function output($type = null)
    {
        $type = $type ?: $this->type;
        
        switch ($type) {
            case IMAGETYPE_JPEG:
                header('Content-Type: image/jpeg');
                return imagejpeg($this->image, null, $this->quality);
            case IMAGETYPE_PNG:
                header('Content-Type: image/png');
                return imagepng($this->image);
            case IMAGETYPE_GIF:
                header('Content-Type: image/gif');
                return imagegif($this->image);
            case IMAGETYPE_WEBP:
                header('Content-Type: image/webp');
                return imagewebp($this->image, null, $this->quality);
            default:
                throw new \Exception("Unsupported image type for output");
        }
    }
    
    /**
     * Get image as base64 data URL
     */
    public function toDataUrl($type = null)
    {
        $type = $type ?: $this->type;
        
        ob_start();
        
        switch ($type) {
            case IMAGETYPE_JPEG:
                imagejpeg($this->image, null, $this->quality);
                $mimeType = 'image/jpeg';
                break;
            case IMAGETYPE_PNG:
                imagepng($this->image);
                $mimeType = 'image/png';
                break;
            case IMAGETYPE_GIF:
                imagegif($this->image);
                $mimeType = 'image/gif';
                break;
            case IMAGETYPE_WEBP:
                imagewebp($this->image, null, $this->quality);
                $mimeType = 'image/webp';
                break;
            default:
                throw new \Exception("Unsupported image type");
        }
        
        $imageData = ob_get_clean();
        return 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
    }
    
    /**
     * Get image dimensions
     */
    public function getDimensions()
    {
        return [
            'width' => $this->width,
            'height' => $this->height
        ];
    }
    
    /**
     * Get image width
     */
    public function getWidth()
    {
        return $this->width;
    }
    
    /**
     * Get image height
     */
    public function getHeight()
    {
        return $this->height;
    }
    
    /**
     * Destroy image resource
     */
    public function destroy()
    {
        if ($this->image) {
            imagedestroy($this->image);
            $this->image = null;
        }
    }
    
    /**
     * Destructor
     */
    public function __destruct()
    {
        $this->destroy();
    }
    
    /**
     * Create image from uploaded file and process
     */
    public static function fromUpload($file, $maxWidth = 1920, $maxHeight = 1080)
    {
        $image = new self($file['tmp_name']);
        
        // Resize if too large
        if ($image->width > $maxWidth || $image->height > $maxHeight) {
            $image->resize($maxWidth, $maxHeight, true);
        }
        
        return $image;
    }
    
    /**
     * Create avatar from uploaded image
     */
    public static function createAvatar($file, $size = 200)
    {
        $image = new self($file['tmp_name']);
        return $image->thumbnail($size, true);
    }
    
    /**
     * Create multiple sizes from single image
     */
    public static function createSizes($imagePath, $sizes = [])
    {
        $results = [];
        
        foreach ($sizes as $name => $size) {
            $image = new self($imagePath);
            
            if (isset($size['width']) && isset($size['height'])) {
                $image->resize($size['width'], $size['height'], $size['maintain_ratio'] ?? true);
            } elseif (isset($size['thumbnail'])) {
                $image->thumbnail($size['thumbnail'], $size['crop'] ?? true);
            }
            
            $pathInfo = pathinfo($imagePath);
            $newPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '_' . $name . '.' . $pathInfo['extension'];
            
            $image->save($newPath);
            $results[$name] = $newPath;
        }
        
        return $results;
    }
}
