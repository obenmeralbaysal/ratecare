<?php

namespace Core;

/**
 * Password Hashing Utilities
 */
class Hash
{
    /**
     * Hash a password
     */
    public static function make($password, $options = [])
    {
        $algorithm = $options['algorithm'] ?? PASSWORD_DEFAULT;
        $cost = $options['cost'] ?? 12;
        
        $options = array_merge([
            'cost' => $cost
        ], $options);
        
        return password_hash($password, $algorithm, $options);
    }
    
    /**
     * Verify a password against a hash
     */
    public static function verify($password, $hash)
    {
        return password_verify($password, $hash);
    }
    
    /**
     * Check if hash needs rehashing
     */
    public static function needsRehash($hash, $options = [])
    {
        $algorithm = $options['algorithm'] ?? PASSWORD_DEFAULT;
        $cost = $options['cost'] ?? 12;
        
        $options = array_merge([
            'cost' => $cost
        ], $options);
        
        return password_needs_rehash($hash, $algorithm, $options);
    }
    
    /**
     * Generate random string
     */
    public static function random($length = 32)
    {
        return bin2hex(random_bytes($length / 2));
    }
    
    /**
     * Generate secure token
     */
    public static function token($length = 64)
    {
        return self::random($length);
    }
    
    /**
     * Generate password reset token
     */
    public static function resetToken()
    {
        return self::token(64);
    }
    
    /**
     * Generate invitation code
     */
    public static function inviteCode($length = 32)
    {
        return self::random($length);
    }
    
    /**
     * Generate API key
     */
    public static function apiKey()
    {
        return self::random(64);
    }
    
    /**
     * Hash string with salt
     */
    public static function hashWithSalt($string, $salt = null)
    {
        if ($salt === null) {
            $salt = self::random(16);
        }
        
        return [
            'hash' => hash('sha256', $string . $salt),
            'salt' => $salt
        ];
    }
    
    /**
     * Verify string with salt
     */
    public static function verifyWithSalt($string, $hash, $salt)
    {
        $computed = hash('sha256', $string . $salt);
        return hash_equals($hash, $computed);
    }
    
    /**
     * Generate widget code
     */
    public static function widgetCode($prefix = 'WDG')
    {
        return $prefix . '_' . strtoupper(self::random(16));
    }
    
    /**
     * Generate hotel code
     */
    public static function hotelCode($prefix = 'HTL')
    {
        return $prefix . '_' . strtoupper(self::random(12));
    }
    
    /**
     * Generate secure filename
     */
    public static function filename($extension = '')
    {
        $filename = self::random(32);
        return $extension ? $filename . '.' . $extension : $filename;
    }
    
    /**
     * Generate UUID v4
     */
    public static function uuid()
    {
        $data = random_bytes(16);
        
        // Set version to 0100
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        
        // Set bits 6-7 to 10
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
