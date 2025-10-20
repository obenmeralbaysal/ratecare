<?php

namespace Core;

/**
 * Advanced Validation System
 */
class Validator
{
    private $data = [];
    private $rules = [];
    private $messages = [];
    private $errors = [];
    private $customMessages = [];
    
    public function __construct($data = [], $rules = [], $messages = [])
    {
        $this->data = $data;
        $this->rules = $rules;
        $this->customMessages = $messages;
        $this->setDefaultMessages();
    }
    
    /**
     * Create validator instance
     */
    public static function make($data, $rules, $messages = [])
    {
        return new self($data, $rules, $messages);
    }
    
    /**
     * Validate data
     */
    public function validate()
    {
        $this->errors = [];
        
        foreach ($this->rules as $field => $ruleSet) {
            $this->validateField($field, $ruleSet);
        }
        
        return empty($this->errors);
    }
    
    /**
     * Validate single field
     */
    private function validateField($field, $ruleSet)
    {
        $rules = is_string($ruleSet) ? explode('|', $ruleSet) : $ruleSet;
        $value = $this->getValue($field);
        
        foreach ($rules as $rule) {
            $this->applyRule($field, $rule, $value);
        }
    }
    
    /**
     * Apply validation rule
     */
    private function applyRule($field, $rule, $value)
    {
        $ruleName = $rule;
        $parameters = [];
        
        if (strpos($rule, ':') !== false) {
            list($ruleName, $paramString) = explode(':', $rule, 2);
            $parameters = explode(',', $paramString);
        }
        
        $methodName = 'validate' . ucfirst($ruleName);
        
        if (method_exists($this, $methodName)) {
            $passes = $this->$methodName($field, $value, $parameters);
            
            if (!$passes) {
                $this->addError($field, $ruleName, $parameters);
            }
        }
    }
    
    /**
     * Get value from data
     */
    private function getValue($field)
    {
        return array_get($this->data, $field);
    }
    
    /**
     * Add validation error
     */
    private function addError($field, $rule, $parameters = [])
    {
        $message = $this->getMessage($field, $rule, $parameters);
        
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        
        $this->errors[$field][] = $message;
    }
    
    /**
     * Get error message
     */
    private function getMessage($field, $rule, $parameters = [])
    {
        $key = "{$field}.{$rule}";
        
        if (isset($this->customMessages[$key])) {
            return $this->customMessages[$key];
        }
        
        if (isset($this->customMessages[$rule])) {
            return $this->customMessages[$rule];
        }
        
        $message = $this->messages[$rule] ?? "The {$field} field is invalid.";
        
        // Replace placeholders
        $message = str_replace(':attribute', $field, $message);
        
        foreach ($parameters as $index => $parameter) {
            $message = str_replace(':' . $index, $parameter, $message);
        }
        
        return $message;
    }
    
    /**
     * Get validation errors
     */
    public function errors()
    {
        return $this->errors;
    }
    
    /**
     * Check if validation failed
     */
    public function fails()
    {
        return !$this->validate();
    }
    
    /**
     * Get first error for field
     */
    public function first($field = null)
    {
        if ($field) {
            return $this->errors[$field][0] ?? null;
        }
        
        foreach ($this->errors as $fieldErrors) {
            return $fieldErrors[0] ?? null;
        }
        
        return null;
    }
    
    /**
     * Validation Rules
     */
    
    protected function validateRequired($field, $value, $parameters)
    {
        return !empty($value) || $value === '0' || $value === 0;
    }
    
    protected function validateEmail($field, $value, $parameters)
    {
        return empty($value) || filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    protected function validateUrl($field, $value, $parameters)
    {
        return empty($value) || filter_var($value, FILTER_VALIDATE_URL) !== false;
    }
    
    protected function validateNumeric($field, $value, $parameters)
    {
        return empty($value) || is_numeric($value);
    }
    
    protected function validateInteger($field, $value, $parameters)
    {
        return empty($value) || filter_var($value, FILTER_VALIDATE_INT) !== false;
    }
    
    protected function validateMin($field, $value, $parameters)
    {
        if (empty($value)) return true;
        
        $min = $parameters[0] ?? 0;
        
        if (is_numeric($value)) {
            return $value >= $min;
        }
        
        return strlen($value) >= $min;
    }
    
    protected function validateMax($field, $value, $parameters)
    {
        if (empty($value)) return true;
        
        $max = $parameters[0] ?? 0;
        
        if (is_numeric($value)) {
            return $value <= $max;
        }
        
        return strlen($value) <= $max;
    }
    
    protected function validateBetween($field, $value, $parameters)
    {
        if (empty($value)) return true;
        
        $min = $parameters[0] ?? 0;
        $max = $parameters[1] ?? 0;
        
        if (is_numeric($value)) {
            return $value >= $min && $value <= $max;
        }
        
        $length = strlen($value);
        return $length >= $min && $length <= $max;
    }
    
    protected function validateIn($field, $value, $parameters)
    {
        return empty($value) || in_array($value, $parameters);
    }
    
    protected function validateNotIn($field, $value, $parameters)
    {
        return empty($value) || !in_array($value, $parameters);
    }
    
    protected function validateRegex($field, $value, $parameters)
    {
        if (empty($value)) return true;
        
        $pattern = $parameters[0] ?? '';
        return preg_match($pattern, $value);
    }
    
    protected function validateAlpha($field, $value, $parameters)
    {
        return empty($value) || ctype_alpha($value);
    }
    
    protected function validateAlphaNum($field, $value, $parameters)
    {
        return empty($value) || ctype_alnum($value);
    }
    
    protected function validateAlphaDash($field, $value, $parameters)
    {
        return empty($value) || preg_match('/^[a-zA-Z0-9_-]+$/', $value);
    }
    
    protected function validateDate($field, $value, $parameters)
    {
        if (empty($value)) return true;
        
        $format = $parameters[0] ?? 'Y-m-d';
        $date = \DateTime::createFromFormat($format, $value);
        
        return $date && $date->format($format) === $value;
    }
    
    protected function validateDateAfter($field, $value, $parameters)
    {
        if (empty($value)) return true;
        
        $afterDate = $parameters[0] ?? '';
        $date = strtotime($value);
        $after = strtotime($afterDate);
        
        return $date && $after && $date > $after;
    }
    
    protected function validateDateBefore($field, $value, $parameters)
    {
        if (empty($value)) return true;
        
        $beforeDate = $parameters[0] ?? '';
        $date = strtotime($value);
        $before = strtotime($beforeDate);
        
        return $date && $before && $date < $before;
    }
    
    protected function validateSame($field, $value, $parameters)
    {
        $otherField = $parameters[0] ?? '';
        $otherValue = $this->getValue($otherField);
        
        return $value === $otherValue;
    }
    
    protected function validateDifferent($field, $value, $parameters)
    {
        $otherField = $parameters[0] ?? '';
        $otherValue = $this->getValue($otherField);
        
        return $value !== $otherValue;
    }
    
    protected function validateConfirmed($field, $value, $parameters)
    {
        $confirmationField = $field . '_confirmation';
        $confirmationValue = $this->getValue($confirmationField);
        
        return $value === $confirmationValue;
    }
    
    protected function validateUnique($field, $value, $parameters)
    {
        if (empty($value)) return true;
        
        $table = $parameters[0] ?? '';
        $column = $parameters[1] ?? $field;
        $except = $parameters[2] ?? null;
        
        if (empty($table)) return true;
        
        $db = Database::getInstance();
        $sql = "SELECT COUNT(*) as count FROM {$table} WHERE {$column} = ?";
        $params = [$value];
        
        if ($except) {
            $sql .= " AND id != ?";
            $params[] = $except;
        }
        
        $result = $db->selectOne($sql, $params);
        return ($result['count'] ?? 0) == 0;
    }
    
    protected function validateExists($field, $value, $parameters)
    {
        if (empty($value)) return true;
        
        $table = $parameters[0] ?? '';
        $column = $parameters[1] ?? $field;
        
        if (empty($table)) return true;
        
        $db = Database::getInstance();
        $sql = "SELECT COUNT(*) as count FROM {$table} WHERE {$column} = ?";
        $result = $db->selectOne($sql, [$value]);
        
        return ($result['count'] ?? 0) > 0;
    }
    
    protected function validateImage($field, $value, $parameters)
    {
        if (empty($value)) return true;
        
        if (is_array($value) && isset($value['tmp_name'])) {
            // File upload
            $imageInfo = getimagesize($value['tmp_name']);
            return $imageInfo !== false;
        }
        
        if (is_string($value)) {
            // File path or base64
            if (file_exists($value)) {
                $imageInfo = getimagesize($value);
                return $imageInfo !== false;
            }
        }
        
        return false;
    }
    
    protected function validateMimes($field, $value, $parameters)
    {
        if (empty($value) || !is_array($value) || !isset($value['type'])) {
            return true;
        }
        
        return in_array($value['type'], $parameters);
    }
    
    protected function validateSize($field, $value, $parameters)
    {
        if (empty($value)) return true;
        
        $size = $parameters[0] ?? 0;
        
        if (is_array($value) && isset($value['size'])) {
            // File upload - size in KB
            return ($value['size'] / 1024) <= $size;
        }
        
        return strlen($value) == $size;
    }
    
    /**
     * Set default error messages
     */
    private function setDefaultMessages()
    {
        $this->messages = [
            'required' => 'The :attribute field is required.',
            'email' => 'The :attribute must be a valid email address.',
            'url' => 'The :attribute must be a valid URL.',
            'numeric' => 'The :attribute must be a number.',
            'integer' => 'The :attribute must be an integer.',
            'min' => 'The :attribute must be at least :0.',
            'max' => 'The :attribute may not be greater than :0.',
            'between' => 'The :attribute must be between :0 and :1.',
            'in' => 'The selected :attribute is invalid.',
            'not_in' => 'The selected :attribute is invalid.',
            'regex' => 'The :attribute format is invalid.',
            'alpha' => 'The :attribute may only contain letters.',
            'alpha_num' => 'The :attribute may only contain letters and numbers.',
            'alpha_dash' => 'The :attribute may only contain letters, numbers, dashes and underscores.',
            'date' => 'The :attribute is not a valid date.',
            'date_after' => 'The :attribute must be a date after :0.',
            'date_before' => 'The :attribute must be a date before :0.',
            'same' => 'The :attribute and :0 must match.',
            'different' => 'The :attribute and :0 must be different.',
            'confirmed' => 'The :attribute confirmation does not match.',
            'unique' => 'The :attribute has already been taken.',
            'exists' => 'The selected :attribute is invalid.',
            'image' => 'The :attribute must be an image.',
            'mimes' => 'The :attribute must be a file of type: :0.',
            'size' => 'The :attribute must be :0 kilobytes.'
        ];
    }
    
    /**
     * Static validation methods
     */
    public static function validateData($data, $rules, $messages = [])
    {
        $validator = new self($data, $rules, $messages);
        
        if ($validator->fails()) {
            throw new ValidationException($validator->errors());
        }
        
        return true;
    }
    
    public static function quick($data, $rules, $messages = [])
    {
        return self::make($data, $rules, $messages)->validate();
    }
}

/**
 * Validation Exception
 */
class ValidationException extends \Exception
{
    private $errors;
    
    public function __construct($errors)
    {
        $this->errors = $errors;
        parent::__construct('Validation failed');
    }
    
    public function getErrors()
    {
        return $this->errors;
    }
    
    public function getFirstError()
    {
        foreach ($this->errors as $fieldErrors) {
            return $fieldErrors[0] ?? null;
        }
        return null;
    }
}
