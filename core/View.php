<?php

namespace Core;

class View
{
    private static $instance = null;
    private $viewPath;
    private $compiledPath;
    private $data = [];
    private $sections = [];
    private $currentSection = null;
    private $layout = null;
    
    private function __construct()
    {
        $this->viewPath = __DIR__ . '/../resources/views/';
        $this->compiledPath = __DIR__ . '/../storage/views/';
        
        if (!is_dir($this->compiledPath)) {
            mkdir($this->compiledPath, 0755, true);
        }
    }
    
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function render($template, $data = [])
    {
        $this->data = array_merge($this->data, $data);
        $this->layout = null; // Reset layout
        $this->sections = []; // Reset sections
        
        $templateFile = $this->viewPath . str_replace('.', '/', $template) . '.php';
        
        if (!file_exists($templateFile)) {
            throw new \Exception("View template not found: {$template}");
        }
        
        $compiledFile = $this->compile($templateFile, $template);
        
        extract($this->data);
        
        ob_start();
        include $compiledFile;
        $output = ob_get_clean();
        
        // If view extends a layout, render the layout
        if ($this->layout) {
            $layoutFile = $this->viewPath . str_replace('.', '/', $this->layout) . '.php';
            
            if (!file_exists($layoutFile)) {
                throw new \Exception("Layout not found: {$this->layout}");
            }
            
            $compiledLayout = $this->compile($layoutFile, $this->layout);
            
            extract($this->data);
            
            ob_start();
            include $compiledLayout;
            $output = ob_get_clean();
        }
        
        return $output;
    }
    
    private function compile($templateFile, $template)
    {
        $compiledFile = $this->compiledPath . str_replace(['/', '.'], '_', $template) . '_' . md5_file($templateFile) . '.php';
        
        if (file_exists($compiledFile) && filemtime($compiledFile) >= filemtime($templateFile)) {
            return $compiledFile;
        }
        
        $content = file_get_contents($templateFile);
        $content = $this->compileDirectives($content);
        file_put_contents($compiledFile, $content);
        
        return $compiledFile;
    }
    
    private function compileDirectives($content)
    {
        $content = preg_replace_callback('/\{\{\s*(.+?)\s*\}\}/', function($matches) {
            return '<?php echo htmlspecialchars(' . $matches[1] . ' ?? ""); ?>';
        }, $content);
        
        $content = preg_replace_callback('/\{!!\s*(.+?)\s*!!\}/', function($matches) {
            return '<?php echo ' . $matches[1] . '; ?>';
        }, $content);
        
        $content = preg_replace_callback('/@if\s*\((.+?)\)/', function($matches) {
            return '<?php if(' . $matches[1] . '): ?>';
        }, $content);
        
        $content = preg_replace_callback('/@elseif\s*\((.+?)\)/', function($matches) {
            return '<?php elseif(' . $matches[1] . '): ?>';
        }, $content);
        
        $content = str_replace('@else', '<?php else: ?>', $content);
        $content = str_replace('@endif', '<?php endif; ?>', $content);
        
        $content = preg_replace_callback('/@foreach\s*\((.+?)\)/', function($matches) {
            return '<?php foreach(' . $matches[1] . '): ?>';
        }, $content);
        
        $content = str_replace('@endforeach', '<?php endforeach; ?>', $content);
        
        $content = preg_replace_callback('/@for\s*\((.+?)\)/', function($matches) {
            return '<?php for(' . $matches[1] . '): ?>';
        }, $content);
        
        $content = str_replace('@endfor', '<?php endfor; ?>', $content);
        
        $content = preg_replace_callback('/@while\s*\((.+?)\)/', function($matches) {
            return '<?php while(' . $matches[1] . '): ?>';
        }, $content);
        
        $content = str_replace('@endwhile', '<?php endwhile; ?>', $content);
        
        $content = preg_replace_callback('/@include\s*\([\'"](.+?)[\'"]\)/', function($matches) {
            return '<?php echo $this->renderPartial("' . $matches[1] . '", get_defined_vars()); ?>';
        }, $content);
        
        $content = preg_replace_callback('/@extends\s*\([\'"](.+?)[\'"]\)/', function($matches) {
            return '<?php $this->layout = "' . $matches[1] . '"; ?>';
        }, $content);
        
        $content = preg_replace_callback('/@section\s*\([\'"](.+?)[\'"]\)/', function($matches) {
            return '<?php $this->startSection("' . $matches[1] . '"); ?>';
        }, $content);
        
        $content = str_replace('@endsection', '<?php $this->endSection(); ?>', $content);
        
        $content = preg_replace_callback('/@yield\s*\([\'"](.+?)[\'"]\)/', function($matches) {
            return '<?php echo $this->yieldSection("' . $matches[1] . '"); ?>';
        }, $content);
        
        return $content;
    }
    
    public function share($key, $value = null)
    {
        if (is_array($key)) {
            $this->data = array_merge($this->data, $key);
        } else {
            $this->data[$key] = $value;
        }
        
        return $this;
    }
    
    public function startSection($name)
    {
        $this->currentSection = $name;
        ob_start();
    }
    
    public function endSection()
    {
        if ($this->currentSection) {
            $this->sections[$this->currentSection] = ob_get_clean();
            $this->currentSection = null;
        }
    }
    
    public function yieldSection($name, $default = '')
    {
        return $this->sections[$name] ?? $default;
    }
    
    public function renderPartial($template, $data = [])
    {
        $templateFile = $this->viewPath . str_replace('.', '/', $template) . '.php';
        
        if (!file_exists($templateFile)) {
            return "<!-- Partial not found: {$template} -->";
        }
        
        $compiledFile = $this->compile($templateFile, $template);
        
        extract(array_merge($this->data, $data));
        
        ob_start();
        include $compiledFile;
        
        return ob_get_clean();
    }
    
    public function clearCompiled()
    {
        $files = glob($this->compiledPath . '*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
}
