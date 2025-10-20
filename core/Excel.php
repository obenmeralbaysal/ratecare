<?php

namespace Core;

/**
 * Excel Export Utility
 * Simple Excel file generation without external dependencies
 */
class Excel
{
    private $data = [];
    private $headers = [];
    private $filename = 'export.xlsx';
    private $sheetName = 'Sheet1';
    
    /**
     * Set data for export
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }
    
    /**
     * Set headers
     */
    public function setHeaders($headers)
    {
        $this->headers = $headers;
        return $this;
    }
    
    /**
     * Set filename
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
        return $this;
    }
    
    /**
     * Set sheet name
     */
    public function setSheetName($sheetName)
    {
        $this->sheetName = $sheetName;
        return $this;
    }
    
    /**
     * Export as CSV (simple implementation)
     */
    public function exportCsv()
    {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . str_replace('.xlsx', '.csv', $this->filename) . '"');
        
        $output = fopen('php://output', 'w');
        
        // Write headers
        if (!empty($this->headers)) {
            fputcsv($output, $this->headers);
        }
        
        // Write data
        foreach ($this->data as $row) {
            if (is_object($row)) {
                $row = (array) $row;
            }
            fputcsv($output, $row);
        }
        
        fclose($output);
        exit;
    }
    
    /**
     * Export as Excel (XML format for compatibility)
     */
    public function exportExcel()
    {
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="' . $this->filename . '"');
        
        echo $this->generateExcelXml();
        exit;
    }
    
    /**
     * Generate Excel XML
     */
    private function generateExcelXml()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<?mso-application progid="Excel.Sheet"?>' . "\n";
        $xml .= '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"' . "\n";
        $xml .= ' xmlns:o="urn:schemas-microsoft-com:office:office"' . "\n";
        $xml .= ' xmlns:x="urn:schemas-microsoft-com:office:excel"' . "\n";
        $xml .= ' xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"' . "\n";
        $xml .= ' xmlns:html="http://www.w3.org/TR/REC-html40">' . "\n";
        
        // Styles
        $xml .= '<Styles>' . "\n";
        $xml .= '<Style ss:ID="Header">' . "\n";
        $xml .= '<Font ss:Bold="1"/>' . "\n";
        $xml .= '<Interior ss:Color="#CCCCCC" ss:Pattern="Solid"/>' . "\n";
        $xml .= '</Style>' . "\n";
        $xml .= '</Styles>' . "\n";
        
        // Worksheet
        $xml .= '<Worksheet ss:Name="' . htmlspecialchars($this->sheetName) . '">' . "\n";
        $xml .= '<Table>' . "\n";
        
        // Headers
        if (!empty($this->headers)) {
            $xml .= '<Row>' . "\n";
            foreach ($this->headers as $header) {
                $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">' . htmlspecialchars($header) . '</Data></Cell>' . "\n";
            }
            $xml .= '</Row>' . "\n";
        }
        
        // Data rows
        foreach ($this->data as $row) {
            if (is_object($row)) {
                $row = (array) $row;
            }
            
            $xml .= '<Row>' . "\n";
            foreach ($row as $cell) {
                $type = is_numeric($cell) ? 'Number' : 'String';
                $xml .= '<Cell><Data ss:Type="' . $type . '">' . htmlspecialchars($cell) . '</Data></Cell>' . "\n";
            }
            $xml .= '</Row>' . "\n";
        }
        
        $xml .= '</Table>' . "\n";
        $xml .= '</Worksheet>' . "\n";
        $xml .= '</Workbook>';
        
        return $xml;
    }
    
    /**
     * Create Excel export from array data
     */
    public static function fromArray($data, $headers = null, $filename = 'export.xlsx')
    {
        $excel = new self();
        $excel->setData($data);
        
        if ($headers) {
            $excel->setHeaders($headers);
        } elseif (!empty($data)) {
            // Auto-generate headers from first row
            $firstRow = reset($data);
            if (is_array($firstRow) || is_object($firstRow)) {
                $headers = array_keys((array) $firstRow);
                $excel->setHeaders($headers);
            }
        }
        
        $excel->setFilename($filename);
        
        return $excel;
    }
    
    /**
     * Create Excel export from database query
     */
    public static function fromQuery($sql, $params = [], $headers = null, $filename = 'export.xlsx')
    {
        $db = Database::getInstance();
        $data = $db->select($sql, $params);
        
        return self::fromArray($data, $headers, $filename);
    }
    
    /**
     * Export statistics data
     */
    public static function exportStatistics($hotelId = null, $dateFrom = null, $dateTo = null)
    {
        $db = Database::getInstance();
        
        $sql = "SELECT 
                    s.date,
                    s.metric_type,
                    s.metric_name,
                    s.value,
                    h.name as hotel_name,
                    w.name as widget_name
                FROM statistics s
                JOIN hotels h ON s.hotel_id = h.id
                LEFT JOIN widgets w ON s.widget_id = w.id
                WHERE 1=1";
        
        $params = [];
        
        if ($hotelId) {
            $sql .= " AND s.hotel_id = ?";
            $params[] = $hotelId;
        }
        
        if ($dateFrom) {
            $sql .= " AND s.date >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $sql .= " AND s.date <= ?";
            $params[] = $dateTo;
        }
        
        $sql .= " ORDER BY s.date DESC, h.name";
        
        $headers = ['Date', 'Hotel', 'Widget', 'Metric Type', 'Metric Name', 'Value'];
        $filename = 'statistics_' . date('Y-m-d') . '.xlsx';
        
        return self::fromQuery($sql, $params, $headers, $filename);
    }
    
    /**
     * Export users data
     */
    public static function exportUsers($resellerId = null)
    {
        $db = Database::getInstance();
        
        $sql = "SELECT 
                    u.namesurname,
                    u.email,
                    CASE WHEN u.is_admin = 1 THEN 'Admin' 
                         WHEN u.reseller_id = 0 THEN 'Reseller' 
                         ELSE 'Customer' END as role,
                    u.created_at,
                    u.last_login,
                    CASE WHEN u.is_active = 1 THEN 'Active' ELSE 'Inactive' END as status
                FROM users u
                WHERE 1=1";
        
        $params = [];
        
        if ($resellerId !== null) {
            $sql .= " AND u.reseller_id = ?";
            $params[] = $resellerId;
        }
        
        $sql .= " ORDER BY u.created_at DESC";
        
        $headers = ['Name', 'Email', 'Role', 'Created At', 'Last Login', 'Status'];
        $filename = 'users_' . date('Y-m-d') . '.xlsx';
        
        return self::fromQuery($sql, $params, $headers, $filename);
    }
    
    /**
     * Export hotels data
     */
    public static function exportHotels($userId = null)
    {
        $db = Database::getInstance();
        
        $sql = "SELECT 
                    h.name,
                    h.code,
                    h.city,
                    h.country,
                    h.star_rating,
                    h.currency,
                    h.language,
                    u.namesurname as owner,
                    h.created_at,
                    CASE WHEN h.is_active = 1 THEN 'Active' ELSE 'Inactive' END as status
                FROM hotels h
                JOIN users u ON h.user_id = u.id
                WHERE 1=1";
        
        $params = [];
        
        if ($userId) {
            $sql .= " AND h.user_id = ?";
            $params[] = $userId;
        }
        
        $sql .= " ORDER BY h.created_at DESC";
        
        $headers = ['Hotel Name', 'Code', 'City', 'Country', 'Star Rating', 'Currency', 'Language', 'Owner', 'Created At', 'Status'];
        $filename = 'hotels_' . date('Y-m-d') . '.xlsx';
        
        return self::fromQuery($sql, $params, $headers, $filename);
    }
}
