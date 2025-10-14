<?php

use Core\Auth;
use Core\Authorization;
use Core\Config;
use Core\Session;

/**
 * Authentication Helper Functions
 */

/**
 * Get authenticated user
 */
function user()
{
    return Auth::getInstance()->user();
}

/**
 * Check if user is logged in
 */
function loggedIn()
{
    return Auth::getInstance()->check();
}

/**
 * Check if user is admin
 */
function isAdmin()
{
    return Auth::getInstance()->isAdmin();
}

/**
 * Check if user is reseller
 */
function isReseller()
{
    return Auth::getInstance()->isReseller();
}

/**
 * Check if user is customer
 */
function isCustomer()
{
    return Auth::getInstance()->isCustomer();
}

/**
 * Check if user has permission
 */
function can($permission, $resource = null)
{
    return Authorization::getInstance()->hasPermission($permission, $resource);
}

/**
 * Get configuration value
 */
function config($key, $default = null)
{
    return Config::get($key, $default);
}

/**
 * Get session value
 */
function session($key = null, $default = null)
{
    $session = Session::getInstance();
    
    if ($key === null) {
        return $session->all();
    }
    
    return $session->get($key, $default);
}

/**
 * Set session value
 */
function setSession($key, $value)
{
    return Session::getInstance()->set($key, $value);
}

/**
 * Get flash message
 */
function flash($key, $default = null)
{
    return Session::getInstance()->getFlash($key, $default);
}

/**
 * Set flash message
 */
function setFlash($key, $value)
{
    return Session::getInstance()->flash($key, $value);
}

/**
 * Generate URL
 */
function url($path = '')
{
    $baseUrl = rtrim(config('app.url', 'http://localhost'), '/');
    return $baseUrl . '/' . ltrim($path, '/');
}

/**
 * Generate asset URL
 */
function asset($path)
{
    return url('assets/' . ltrim($path, '/'));
}

/**
 * Redirect helper
 */
function redirect($url)
{
    header("Location: {$url}");
    exit;
}

/**
 * Old input helper (for form validation)
 */
function old($key, $default = '')
{
    return session("_old_input.{$key}", $default);
}

/**
 * CSRF token helper
 */
function csrfToken()
{
    return \Core\CSRF::getInstance()->getToken();
}

/**
 * CSRF field helper
 */
function csrfField()
{
    return \Core\CSRF::getInstance()->field();
}

/**
 * Escape HTML
 */
function e($value)
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Debug helper
 */
function dd(...$vars)
{
    foreach ($vars as $var) {
        echo '<pre>';
        var_dump($var);
        echo '</pre>';
    }
    exit;
}

/**
 * Format date
 */
function formatDate($date, $format = 'Y-m-d H:i:s')
{
    if (!$date) return '';
    
    if (is_string($date)) {
        $date = new DateTime($date);
    }
    
    return $date->format($format);
}

/**
 * Generate random string
 */
function str_random($length = 16)
{
    return \Core\Hash::random($length);
}

/**
 * Check if string starts with (PHP < 8.0 compatibility)
 */
if (!function_exists('str_starts_with')) {
    function str_starts_with($haystack, $needle)
    {
        return strpos($haystack, $needle) === 0;
    }
}

/**
 * Check if string ends with (PHP < 8.0 compatibility)
 */
if (!function_exists('str_ends_with')) {
    function str_ends_with($haystack, $needle)
    {
        return substr($haystack, -strlen($needle)) === $needle;
    }
}

/**
 * Convert array to JSON
 */
function to_json($data)
{
    return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}

/**
 * Parse JSON string
 */
function from_json($json, $assoc = true)
{
    return json_decode($json, $assoc);
}

/**
 * Array helper functions
 */

/**
 * Get array value with dot notation
 */
function array_get($array, $key, $default = null)
{
    if (is_null($key)) {
        return $array;
    }
    
    if (isset($array[$key])) {
        return $array[$key];
    }
    
    foreach (explode('.', $key) as $segment) {
        if (!is_array($array) || !array_key_exists($segment, $array)) {
            return $default;
        }
        $array = $array[$segment];
    }
    
    return $array;
}

/**
 * Set array value with dot notation
 */
function array_set(&$array, $key, $value)
{
    $keys = explode('.', $key);
    
    while (count($keys) > 1) {
        $key = array_shift($keys);
        
        if (!isset($array[$key]) || !is_array($array[$key])) {
            $array[$key] = [];
        }
        
        $array = &$array[$key];
    }
    
    $array[array_shift($keys)] = $value;
}

/**
 * Check if array has key with dot notation
 */
function array_has($array, $key)
{
    if (empty($array) || is_null($key)) {
        return false;
    }
    
    if (array_key_exists($key, $array)) {
        return true;
    }
    
    foreach (explode('.', $key) as $segment) {
        if (!is_array($array) || !array_key_exists($segment, $array)) {
            return false;
        }
        $array = $array[$segment];
    }
    
    return true;
}

/**
 * Flatten array
 */
function array_flatten($array, $depth = INF)
{
    $result = [];
    
    foreach ($array as $item) {
        if (!is_array($item)) {
            $result[] = $item;
        } else {
            $values = $depth === 1 ? array_values($item) : array_flatten($item, $depth - 1);
            $result = array_merge($result, $values);
        }
    }
    
    return $result;
}

/**
 * String helper functions
 */

/**
 * Convert string to slug
 */
function str_slug($title, $separator = '-')
{
    $title = preg_replace('![^\\pL\\pN\\s]+!u', '', $title);
    $title = preg_replace('!\\s+!u', $separator, $title);
    $title = trim($title, $separator);
    
    return strtolower($title);
}

/**
 * Limit string length
 */
function str_limit($value, $limit = 100, $end = '...')
{
    if (mb_strwidth($value, 'UTF-8') <= $limit) {
        return $value;
    }
    
    return rtrim(mb_strimwidth($value, 0, $limit, '', 'UTF-8')) . $end;
}

/**
 * Convert string to camelCase
 */
function str_camel($value)
{
    return lcfirst(str_studly($value));
}

/**
 * Convert string to StudlyCase
 */
function str_studly($value)
{
    $value = ucwords(str_replace(['-', '_'], ' ', $value));
    return str_replace(' ', '', $value);
}

/**
 * Convert string to snake_case
 */
function str_snake($value, $delimiter = '_')
{
    if (!ctype_lower($value)) {
        $value = preg_replace('/\s+/u', '', ucwords($value));
        $value = strtolower(preg_replace('/(.)(?=[A-Z])/u', '$1' . $delimiter, $value));
    }
    
    return $value;
}

/**
 * Check if string contains substring (PHP < 8.0 compatibility)
 */
if (!function_exists('str_contains')) {
    function str_contains($haystack, $needle)
    {
        return strpos($haystack, $needle) !== false;
    }
}

/**
 * Validation helper functions
 */

/**
 * Validate email
 */
function is_email($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate URL
 */
function is_url($url)
{
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}

/**
 * Validate phone number (basic)
 */
function is_phone($phone)
{
    return preg_match('/^[\+]?[1-9][\d]{0,15}$/', $phone);
}

/**
 * Validate date
 */
function is_date($date, $format = 'Y-m-d')
{
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

/**
 * File helper functions
 */

/**
 * Get file size in human readable format
 */
function format_bytes($size, $precision = 2)
{
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    
    for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
        $size /= 1024;
    }
    
    return round($size, $precision) . ' ' . $units[$i];
}

/**
 * Get file extension
 */
function get_file_extension($filename)
{
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

/**
 * Check if file is image
 */
function is_image($filename)
{
    $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg'];
    return in_array(get_file_extension($filename), $imageExtensions);
}

/**
 * Number helper functions
 */

/**
 * Format number
 */
function number_format_short($number, $precision = 1)
{
    if ($number < 1000) {
        return $number;
    } elseif ($number < 1000000) {
        return round($number / 1000, $precision) . 'K';
    } elseif ($number < 1000000000) {
        return round($number / 1000000, $precision) . 'M';
    } else {
        return round($number / 1000000000, $precision) . 'B';
    }
}

/**
 * Generate random number
 */
function random_int_range($min, $max)
{
    return random_int($min, $max);
}

/**
 * Utility functions
 */

/**
 * Get client IP address
 */
function get_client_ip()
{
    $ipKeys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
    
    foreach ($ipKeys as $key) {
        if (array_key_exists($key, $_SERVER) === true) {
            foreach (explode(',', $_SERVER[$key]) as $ip) {
                $ip = trim($ip);
                
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                    return $ip;
                }
            }
        }
    }
    
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

/**
 * Get user agent
 */
function get_user_agent()
{
    return $_SERVER['HTTP_USER_AGENT'] ?? '';
}

/**
 * Check if request is mobile
 */
function is_mobile()
{
    $userAgent = get_user_agent();
    return preg_match('/Mobile|Android|iPhone|iPad/', $userAgent);
}

/**
 * Generate UUID v4
 */
function generate_uuid()
{
    return \Core\Hash::uuid();
}

/**
 * Sanitize input
 */
function sanitize($input, $type = 'string')
{
    switch ($type) {
        case 'email':
            return filter_var($input, FILTER_SANITIZE_EMAIL);
        case 'url':
            return filter_var($input, FILTER_SANITIZE_URL);
        case 'int':
            return filter_var($input, FILTER_SANITIZE_NUMBER_INT);
        case 'float':
            return filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        default:
            return htmlspecialchars(strip_tags($input), ENT_QUOTES, 'UTF-8');
    }
}
