<?php
/**
 * FormValidator Class
 * Handles form validation and security checks
 */
class FormValidator {
    
    /**
     * Validate profile form data
     */
    public static function validateProfile($data) {
        $errors = [];
        
        // Validate fullname
        if (empty($data['fullname'])) {
            $errors[] = 'Full name is required.';
        } elseif (strlen($data['fullname']) < 2) {
            $errors[] = 'Full name must be at least 2 characters long.';
        } elseif (strlen($data['fullname']) > 100) {
            $errors[] = 'Full name cannot exceed 100 characters.';
        }
        
        // Validate username
        if (empty($data['username'])) {
            $errors[] = 'Username is required.';
        } elseif (strlen($data['username']) < 3) {
            $errors[] = 'Username must be at least 3 characters long.';
        } elseif (strlen($data['username']) > 50) {
            $errors[] = 'Username cannot exceed 50 characters.';
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $data['username'])) {
            $errors[] = 'Username can only contain letters, numbers, and underscores.';
        }
        
        return $errors;
    }
    
    /**
     * Validate password change data
     */
    public static function validatePassword($data) {
        $errors = [];
        
        if (!empty($data['new_password'])) {
            // Current password is required when changing password
            if (empty($data['current_password'])) {
                $errors[] = 'Current password is required to change password.';
            }
            
            // Validate new password
            if (strlen($data['new_password']) < 6) {
                $errors[] = 'New password must be at least 6 characters long.';
            }
            
            if (strlen($data['new_password']) > 255) {
                $errors[] = 'Password cannot exceed 255 characters.';
            }
            
            // Check password confirmation
            if ($data['new_password'] !== $data['confirm_password']) {
                $errors[] = 'New passwords do not match.';
            }
            
            // Password strength check
            if (!self::isPasswordStrong($data['new_password'])) {
                $errors[] = 'Password must contain at least one uppercase letter, one lowercase letter, and one number.';
            }
        }
        
        return $errors;
    }
    
    /**
     * Check password strength
     */
    public static function isPasswordStrong($password) {
        // At least 6 characters, one uppercase, one lowercase, one number
        return strlen($password) >= 6 && 
               preg_match('/[a-z]/', $password) && 
               preg_match('/[A-Z]/', $password) && 
               preg_match('/[0-9]/', $password);
    }
    
    /**
     * Sanitize input data
     */
    public static function sanitizeInput($data) {
        $sanitized = [];
        
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $sanitized[$key] = trim(htmlspecialchars($value, ENT_QUOTES, 'UTF-8'));
            } else {
                $sanitized[$key] = $value;
            }
        }
        
        return $sanitized;
    }
    
    /**
     * Validate CSRF token
     */
    public static function validateCSRF($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Generate CSRF token
     */
    public static function generateCSRF() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}
?>
