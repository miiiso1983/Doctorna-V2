<?php
/**
 * Validation Helper
 */

class Validator {
    private $errors = [];
    private $data = [];
    
    public function __construct($data) {
        $this->data = $data;
    }
    
    /**
     * Validate required fields
     */
    public function required($fields) {
        foreach ($fields as $field) {
            if (!isset($this->data[$field]) || empty(trim($this->data[$field]))) {
                $this->errors[$field] = "حقل $field مطلوب";
            }
        }
        return $this;
    }
    
    /**
     * Validate email
     */
    public function email($field) {
        if (isset($this->data[$field]) && !filter_var($this->data[$field], FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = ERROR_MESSAGES['invalid_email'];
        }
        return $this;
    }
    
    /**
     * Validate minimum length
     */
    public function minLength($field, $length) {
        if (isset($this->data[$field]) && strlen($this->data[$field]) < $length) {
            $this->errors[$field] = "يجب أن يكون $field على الأقل $length أحرف";
        }
        return $this;
    }
    
    /**
     * Validate maximum length
     */
    public function maxLength($field, $length) {
        if (isset($this->data[$field]) && strlen($this->data[$field]) > $length) {
            $this->errors[$field] = "يجب أن لا يتجاوز $field $length حرف";
        }
        return $this;
    }
    
    /**
     * Validate numeric
     */
    public function numeric($field) {
        if (isset($this->data[$field]) && !is_numeric($this->data[$field])) {
            $this->errors[$field] = "يجب أن يكون $field رقماً";
        }
        return $this;
    }
    
    /**
     * Validate in array
     */
    public function in($field, $values) {
        if (isset($this->data[$field]) && !in_array($this->data[$field], $values)) {
            $this->errors[$field] = "قيمة $field غير صالحة";
        }
        return $this;
    }
    
    /**
     * Validate date format
     */
    public function date($field, $format = 'Y-m-d') {
        if (isset($this->data[$field])) {
            $d = DateTime::createFromFormat($format, $this->data[$field]);
            if (!$d || $d->format($format) !== $this->data[$field]) {
                $this->errors[$field] = "تنسيق التاريخ غير صالح";
            }
        }
        return $this;
    }
    
    /**
     * Validate phone number (Iraqi format)
     */
    public function phone($field) {
        if (isset($this->data[$field])) {
            $phone = preg_replace('/[^0-9]/', '', $this->data[$field]);
            if (strlen($phone) < 10 || strlen($phone) > 15) {
                $this->errors[$field] = "رقم الهاتف غير صالح";
            }
        }
        return $this;
    }
    
    /**
     * Check if validation passed
     */
    public function passes() {
        return empty($this->errors);
    }
    
    /**
     * Check if validation failed
     */
    public function fails() {
        return !$this->passes();
    }
    
    /**
     * Get validation errors
     */
    public function errors() {
        return $this->errors;
    }
    
    /**
     * Get validated data
     */
    public function validated() {
        return $this->data;
    }
}

