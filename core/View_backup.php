<?php

namespace Core;

/**
 * Simple Template Engine (Blade-like)
 */
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
        
        // Create compiled views directory if it doesn't exist
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
    
    /**
     * Render a view template
     */
    public function render($template, $data = [])
    {
        $this->data = array_merge($this->data, $data);
        
        $templateFile = $this->viewPath . str_replace('.', '/', $template) . '.php';
        
        if (!file_exists($templateFile)) {
            throw new \Exception("View template not found: {$template}");
        }
        
        $compiledFile = $this->compile($templateFile, $template);
        
        // Extract data to variables
        extract($this->data);
        
        // Start output buffering
        ob_start();
        
        // Include the compiled template
        include $compiledFile;
        
        // Get the output and clean the buffer
        $output = ob_get_clean();
        
        return $output;
    }
    
    /**
     * Compile template with simple directives
     */
    private function compile($templateFile, $template)
    {
        $compiledFile = $this->compiledPath . str_replace(['/', '.'], '_', $template) . '_' . md5_file($templateFile) . '.php';
        
        // Check if compiled file exists and is newer than template
        if (file_exists($compiledFile) && filemtime($compiledFile) >= filemtime($templateFile)) {
            return $compiledFile;
        }
        
        $content = file_get_contents($templateFile);
        
        // Compile template directives
        $content = $this->compileDirectives($content);
        
        // Write compiled template
        file_put_contents($compiledFile, $content);
        
        return $compiledFile;
    }
    
    /**
     * Compile template directives
     */
    private function compileDirectives($content)
    {
        // {{ $variable }} -> <?php echo htmlspecialchars($variable); ?>
        $content = preg_replace_callback('/\{\{\s*(.+?)\s*\}\}/', function($matches) {
            return '<?php echo htmlspecialchars(' . $matches[1] . ' ?? ""); ?>';
        }, $content);
        
        // {!! $variable !!} -> <?php echo $variable; ?>
        $content = preg_replace_callback('/\{!!\s*(.+?)\s*!!\}/', function($matches) {
            return '<?php echo ' . $matches[1] . '; ?>';
        }, $content);
        
        // @if($condition) -> <?php if($condition): ?>
        $content = preg_replace_callback('/@if\s*\((.+?)\)/', function($matches) {
            return '<?php if(' . $matches[1] . '): ?>';
        }, $content);
        
        // @elseif($condition) -> <?php elseif($condition): ?>
        $content = preg_replace_callback('/@elseif\s*\((.+?)\)/', function($matches) {
            return '<?php elseif(' . $matches[1] . '): ?>';
        }, $content);
        
        // @else -> <?php else: ?>
        $content = preg_replace('/@else/', '<?php else: ?>', $content);
        
        // @endif -> <?php endif; ?>
        $content = preg_replace('/@endif/', '<?php endif; ?>', $content);
        
        // @foreach($items as $item) -> <?php foreach($items as $item): ?>
        $content = preg_replace_callback('/@foreach\s*\((.+?)\)/', function($matches) {
            return '<?php foreach(' . $matches[1] . '): ?>';
        }, $content);
        
        // @endforeach -> <?php endforeach; ?>
        $content = preg_replace('/@endforeach/', '<?php endforeach; ?>', $content);
        
        // @for($i = 0; $i < 10; $i++) -> <?php for($i = 0; $i < 10; $i++): ?>
        $content = preg_replace_callback('/@for\s*\((.+?)\)/', function($matches) {
            return '<?php for(' . $matches[1] . '): ?>';
        }, $content);
        
        // @endfor -> <?php endfor; ?>
        $content = preg_replace('/@endfor/', '<?php endfor; ?>', $content);
        
        // @while($condition) -> <?php while($condition): ?>
        $content = preg_replace_callback('/@while\s*\((.+?)\)/', function($matches) {
            return '<?php while(' . $matches[1] . '): ?>';
        }, $content);
        
        // @endwhile -> <?php endwhile; ?>
        $content = preg_replace('/@endwhile/', '<?php endwhile; ?>', $content);
        
        // @include('template') -> include partial template
        $content = preg_replace_callback('/@include\s*\([\'"](.+?)[\'"]\)/', function($matches) {
            return '<?php echo $this->renderPartial("' . $matches[1] . '", get_defined_vars()); ?>';
        }, $content);
        
        // @extends('layout') -> Layout inheritance
        $content = preg_replace_callback('/@extends\s*\([\'"](.+?)[\'"]\)/', function($matches) {
            return '<?php $this->layout = "' . $matches[1] . '"; ?>';
        }, $content);
        
        // @section('name') -> Start section
        $content = preg_replace_callback('/@section\s*\([\'"](.+?)[\'"]\)/', function($matches) {
            return '<?php $this->startSection("' . $matches[1] . '"); ?>';
        }, $content);
        
        // @endsection -> End section
        $content = preg_replace('/@endsection/', '<?php $this->endSection(); ?>', $content);
        
        // @yield('section') -> Yield section content
        $content = preg_replace_callback('/@yield\s*\([\'"](.+?)[\'"]\)/', function($matches) {
            return '<?php echo $this->yieldSection("' . $matches[1] . '"); ?>';
        }, $content);
        
        return $content;
    }
    
    /**
     * Set global view data
     */
    public function share($key, $value = null)
    {
        if (is_array($key)) {
            $this->data = array_merge($this->data, $key);
        } else {
            $this->data[$key] = $value;
        }
        
        return $this;
    }
    
    /**
     * Start a section
     */
    public function startSection($name)
    {
        $this->currentSection = $name;
        ob_start();
    }
    
    /**
     * End current section
     */
    public function endSection()
    {
        if ($this->currentSection) {
            $this->sections[$this->currentSection] = ob_get_clean();
            $this->currentSection = null;
        }
    }
    
    /**
     * Yield section content
     */
    public function yieldSection($name, $default = '')
    {
        return $this->sections[$name] ?? $default;
    }
    
    /**
     * Render partial template
     */
    public function renderPartial($template, $data = [])
    {
        $templateFile = $this->viewPath . str_replace('.', '/', $template) . '.php';
        
        if (!file_exists($templateFile)) {
            return "<!-- Partial not found: {$template} -->";
        }
        
        $compiledFile = $this->compile($templateFile, $template);
        
        // Extract data to variables
        extract(array_merge($this->data, $data));
        
        // Start output buffering
        ob_start();
        
        // Include the compiled template
        include $compiledFile;
        
        // Get the output and clean the buffer
        return ob_get_clean();
    }
    
    /**
     * Clear compiled views
     */
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
