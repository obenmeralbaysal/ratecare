<?php

namespace Core;

/**
 * Pagination System
 */
class Paginator
{
    private $currentPage;
    private $perPage;
    private $total;
    private $data;
    private $path;
    private $query = [];
    
    public function __construct($data, $total, $perPage, $currentPage = 1, $path = null)
    {
        $this->data = $data;
        $this->total = $total;
        $this->perPage = $perPage;
        $this->currentPage = max(1, $currentPage);
        $this->path = $path ?: $this->getCurrentPath();
    }
    
    /**
     * Create paginator from query builder
     */
    public static function fromQueryBuilder($queryBuilder, $perPage = 15, $page = null)
    {
        $page = $page ?: self::getCurrentPage();
        
        // Get total count
        $total = $queryBuilder->count();
        
        // Get paginated data
        $offset = ($page - 1) * $perPage;
        $data = $queryBuilder->offset($offset)->limit($perPage)->get();
        
        return new self($data, $total, $perPage, $page);
    }
    
    /**
     * Create paginator from array
     */
    public static function fromArray($data, $perPage = 15, $page = null)
    {
        $page = $page ?: self::getCurrentPage();
        $total = count($data);
        
        $offset = ($page - 1) * $perPage;
        $paginatedData = array_slice($data, $offset, $perPage);
        
        return new self($paginatedData, $total, $perPage, $page);
    }
    
    /**
     * Create paginator from SQL query
     */
    public static function fromSql($sql, $params = [], $perPage = 15, $page = null)
    {
        $page = $page ?: self::getCurrentPage();
        $db = Database::getInstance();
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM ({$sql}) as count_table";
        $totalResult = $db->selectOne($countSql, $params);
        $total = $totalResult['total'] ?? 0;
        
        // Get paginated data
        $offset = ($page - 1) * $perPage;
        $paginatedSql = $sql . " LIMIT {$perPage} OFFSET {$offset}";
        $data = $db->select($paginatedSql, $params);
        
        return new self($data, $total, $perPage, $page);
    }
    
    /**
     * Get current page from request
     */
    private static function getCurrentPage()
    {
        return max(1, (int) ($_GET['page'] ?? 1));
    }
    
    /**
     * Get current path
     */
    private function getCurrentPath()
    {
        return strtok($_SERVER['REQUEST_URI'], '?');
    }
    
    /**
     * Get paginated data
     */
    public function data()
    {
        return $this->data;
    }
    
    /**
     * Get items (alias for data)
     */
    public function items()
    {
        return $this->data();
    }
    
    /**
     * Get current page
     */
    public function currentPage()
    {
        return $this->currentPage;
    }
    
    /**
     * Get per page count
     */
    public function perPage()
    {
        return $this->perPage;
    }
    
    /**
     * Get total items
     */
    public function total()
    {
        return $this->total;
    }
    
    /**
     * Get last page
     */
    public function lastPage()
    {
        return (int) ceil($this->total / $this->perPage);
    }
    
    /**
     * Check if there are more pages
     */
    public function hasPages()
    {
        return $this->lastPage() > 1;
    }
    
    /**
     * Check if on first page
     */
    public function onFirstPage()
    {
        return $this->currentPage <= 1;
    }
    
    /**
     * Check if on last page
     */
    public function onLastPage()
    {
        return $this->currentPage >= $this->lastPage();
    }
    
    /**
     * Get previous page number
     */
    public function previousPage()
    {
        return $this->currentPage > 1 ? $this->currentPage - 1 : null;
    }
    
    /**
     * Get next page number
     */
    public function nextPage()
    {
        return $this->currentPage < $this->lastPage() ? $this->currentPage + 1 : null;
    }
    
    /**
     * Get first item number
     */
    public function firstItem()
    {
        return $this->total > 0 ? (($this->currentPage - 1) * $this->perPage) + 1 : 0;
    }
    
    /**
     * Get last item number
     */
    public function lastItem()
    {
        return $this->firstItem() + count($this->data) - 1;
    }
    
    /**
     * Set query parameters
     */
    public function appends($query)
    {
        $this->query = array_merge($this->query, $query);
        return $this;
    }
    
    /**
     * Get URL for page
     */
    public function url($page)
    {
        $query = array_merge($this->query, ['page' => $page]);
        return $this->path . '?' . http_build_query($query);
    }
    
    /**
     * Get previous page URL
     */
    public function previousPageUrl()
    {
        $previousPage = $this->previousPage();
        return $previousPage ? $this->url($previousPage) : null;
    }
    
    /**
     * Get next page URL
     */
    public function nextPageUrl()
    {
        $nextPage = $this->nextPage();
        return $nextPage ? $this->url($nextPage) : null;
    }
    
    /**
     * Get page range
     */
    public function getPageRange($onEachSide = 3)
    {
        $lastPage = $this->lastPage();
        
        if ($lastPage <= ($onEachSide * 2) + 1) {
            return range(1, $lastPage);
        }
        
        $start = max(1, $this->currentPage - $onEachSide);
        $end = min($lastPage, $this->currentPage + $onEachSide);
        
        // Adjust if we're near the beginning or end
        if ($start <= $onEachSide) {
            $end = min($lastPage, ($onEachSide * 2) + 1);
        }
        
        if ($end > $lastPage - $onEachSide) {
            $start = max(1, $lastPage - ($onEachSide * 2));
        }
        
        return range($start, $end);
    }
    
    /**
     * Render pagination links
     */
    public function links($view = null)
    {
        if (!$this->hasPages()) {
            return '';
        }
        
        if ($view) {
            $viewInstance = View::getInstance();
            return $viewInstance->render($view, ['paginator' => $this]);
        }
        
        return $this->renderDefaultLinks();
    }
    
    /**
     * Render simple pagination (Previous/Next only)
     */
    public function simplePaginate()
    {
        if (!$this->hasPages()) {
            return '';
        }
        
        $html = '<nav aria-label="Pagination">';
        $html .= '<ul class="pagination">';
        
        // Previous
        if ($this->previousPage()) {
            $html .= '<li class="page-item">';
            $html .= '<a class="page-link" href="' . $this->previousPageUrl() . '">Previous</a>';
            $html .= '</li>';
        } else {
            $html .= '<li class="page-item disabled">';
            $html .= '<span class="page-link">Previous</span>';
            $html .= '</li>';
        }
        
        // Next
        if ($this->nextPage()) {
            $html .= '<li class="page-item">';
            $html .= '<a class="page-link" href="' . $this->nextPageUrl() . '">Next</a>';
            $html .= '</li>';
        } else {
            $html .= '<li class="page-item disabled">';
            $html .= '<span class="page-link">Next</span>';
            $html .= '</li>';
        }
        
        $html .= '</ul>';
        $html .= '</nav>';
        
        return $html;
    }
    
    /**
     * Render default pagination links
     */
    private function renderDefaultLinks()
    {
        $html = '<nav aria-label="Pagination">';
        $html .= '<ul class="pagination justify-content-center">';
        
        // Previous
        if ($this->previousPage()) {
            $html .= '<li class="page-item">';
            $html .= '<a class="page-link" href="' . $this->previousPageUrl() . '">&laquo; Previous</a>';
            $html .= '</li>';
        }
        
        // Page numbers
        $pageRange = $this->getPageRange();
        
        // First page if not in range
        if (!in_array(1, $pageRange) && $this->lastPage() > 1) {
            $html .= '<li class="page-item">';
            $html .= '<a class="page-link" href="' . $this->url(1) . '">1</a>';
            $html .= '</li>';
            
            if ($pageRange[0] > 2) {
                $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
        }
        
        // Page range
        foreach ($pageRange as $page) {
            if ($page == $this->currentPage) {
                $html .= '<li class="page-item active">';
                $html .= '<span class="page-link">' . $page . '</span>';
                $html .= '</li>';
            } else {
                $html .= '<li class="page-item">';
                $html .= '<a class="page-link" href="' . $this->url($page) . '">' . $page . '</a>';
                $html .= '</li>';
            }
        }
        
        // Last page if not in range
        $lastPage = $this->lastPage();
        if (!in_array($lastPage, $pageRange) && $lastPage > 1) {
            if (end($pageRange) < $lastPage - 1) {
                $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
            
            $html .= '<li class="page-item">';
            $html .= '<a class="page-link" href="' . $this->url($lastPage) . '">' . $lastPage . '</a>';
            $html .= '</li>';
        }
        
        // Next
        if ($this->nextPage()) {
            $html .= '<li class="page-item">';
            $html .= '<a class="page-link" href="' . $this->nextPageUrl() . '">Next &raquo;</a>';
            $html .= '</li>';
        }
        
        $html .= '</ul>';
        $html .= '</nav>';
        
        return $html;
    }
    
    /**
     * Get pagination info
     */
    public function getInfo()
    {
        return [
            'current_page' => $this->currentPage,
            'per_page' => $this->perPage,
            'total' => $this->total,
            'last_page' => $this->lastPage(),
            'from' => $this->firstItem(),
            'to' => $this->lastItem(),
            'has_pages' => $this->hasPages(),
            'on_first_page' => $this->onFirstPage(),
            'on_last_page' => $this->onLastPage(),
            'previous_page_url' => $this->previousPageUrl(),
            'next_page_url' => $this->nextPageUrl()
        ];
    }
    
    /**
     * Convert to array
     */
    public function toArray()
    {
        return [
            'data' => $this->data,
            'pagination' => $this->getInfo()
        ];
    }
    
    /**
     * Convert to JSON
     */
    public function toJson()
    {
        return json_encode($this->toArray());
    }
    
    /**
     * Magic method for array access
     */
    public function __get($key)
    {
        switch ($key) {
            case 'data':
            case 'items':
                return $this->data();
            case 'total':
                return $this->total();
            case 'currentPage':
                return $this->currentPage();
            case 'lastPage':
                return $this->lastPage();
            case 'perPage':
                return $this->perPage();
            default:
                return null;
        }
    }
}
