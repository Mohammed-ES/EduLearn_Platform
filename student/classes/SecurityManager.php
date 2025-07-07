<?php
/**
 * SecurityManager Class
 * Handles security-related operations and authentication
 */
class SecurityManager {
    
    /**
     * Check if user is authenticated and has student role
     */
    public static function checkStudentAuth() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
            header('Location: ../auth/login.php');
            exit;
        }
        return true;
    }
    
    /**
     * Generate secure password hash
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }
    
    /**
     * Verify password against hash
     */
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    /**
     * Generate CSRF token
     */
    public static function generateCSRFToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Validate CSRF token
     */
    public static function validateCSRFToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Sanitize user input to prevent XSS
     */
    public static function sanitizeInput($input) {
        if (is_array($input)) {
            return array_map([self::class, 'sanitizeInput'], $input);
        }
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Rate limiting for form submissions
     */
    public static function checkRateLimit($user_id, $action = 'settings_update', $limit = 5, $timeWindow = 300) {
        $key = "rate_limit_{$action}_{$user_id}";
        
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = ['count' => 0, 'start_time' => time()];
        }
        
        $rateData = $_SESSION[$key];
        
        // Reset if time window has passed
        if (time() - $rateData['start_time'] > $timeWindow) {
            $_SESSION[$key] = ['count' => 1, 'start_time' => time()];
            return true;
        }
        
        // Check if limit exceeded
        if ($rateData['count'] >= $limit) {
            return false;
        }
        
        // Increment counter
        $_SESSION[$key]['count']++;
        return true;
    }
    
    /**
     * Log security events
     */
    public static function logSecurityEvent($user_id, $event, $details = '') {
        $log_entry = date('Y-m-d H:i:s') . " - User ID: {$user_id} - Event: {$event} - Details: {$details}" . PHP_EOL;
        
        $log_file = '../logs/security_' . date('Y-m') . '.log';
        
        // Create logs directory if it doesn't exist
        $log_dir = dirname($log_file);
        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0755, true);
        }
        
        file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Validate session integrity
     */
    public static function validateSession() {
        // Check if session has required data
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
            return false;
        }
        
        // Check session timeout (30 minutes of inactivity)
        if (isset($_SESSION['last_activity']) && 
            (time() - $_SESSION['last_activity'] > 1800)) {
            session_destroy();
            return false;
        }
        
        // Update last activity
        $_SESSION['last_activity'] = time();
        
        return true;
    }
    
    /**
     * Generate user initials safely
     */
    public static function generateUserInitials($fullname) {
        $fullname = trim($fullname);
        if (empty($fullname)) {
            return 'ST';
        }
        
        $parts = explode(' ', $fullname);
        $initials = '';
        
        // Get first letter of first name
        $initials .= strtoupper(substr($parts[0], 0, 1));
        
        // Get first letter of last name if exists
        if (count($parts) > 1) {
            $initials .= strtoupper(substr(end($parts), 0, 1));
        } else {
            // If only one name, get second letter
            $initials .= strtoupper(substr($parts[0], 1, 1)) ?: 'T';
        }
        
        return $initials;
    }
    
    /**
     * Detect suspicious activity
     */
    public static function detectSuspiciousActivity($user_id, $request_data) {
        $suspicious = false;
        $reasons = [];
        
        // Check for SQL injection patterns
        $sql_patterns = ['/union\s+select/i', '/drop\s+table/i', '/delete\s+from/i', '/insert\s+into/i'];
        foreach ($request_data as $value) {
            if (is_string($value)) {
                foreach ($sql_patterns as $pattern) {
                    if (preg_match($pattern, $value)) {
                        $suspicious = true;
                        $reasons[] = 'SQL injection attempt detected';
                        break 2;
                    }
                }
            }
        }
        
        // Check for XSS patterns
        $xss_patterns = ['/<script/i', '/javascript:/i', '/on\w+\s*=/i'];
        foreach ($request_data as $value) {
            if (is_string($value)) {
                foreach ($xss_patterns as $pattern) {
                    if (preg_match($pattern, $value)) {
                        $suspicious = true;
                        $reasons[] = 'XSS attempt detected';
                        break 2;
                    }
                }
            }
        }
        
        if ($suspicious) {
            self::logSecurityEvent($user_id, 'SUSPICIOUS_ACTIVITY', implode(', ', $reasons));
        }
        
        return $suspicious;
    }
}
?>
