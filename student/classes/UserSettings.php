<?php
/**
 * UserSettings Class
 * Handles user profile and security settings management
 */
class UserSettings {
    private $conn;
    private $user_id;
    
    public function __construct($database_connection, $user_id) {
        $this->conn = $database_connection;
        $this->user_id = $user_id;
    }
    
    /**
     * Get current user data
     */
    public function getUserData() {
        try {
            $stmt = $this->conn->prepare("SELECT fullname, username, email FROM users WHERE id = ?");
            $stmt->execute([$this->user_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: ['fullname' => '', 'username' => '', 'email' => ''];
        } catch (PDOException $e) {
            return ['fullname' => '', 'username' => '', 'email' => ''];
        }
    }
    
    /**
     * Update user profile information
     */
    public function updateProfile($fullname, $username) {
        try {
            // Get current user data
            $stmt = $this->conn->prepare("SELECT username, email, password FROM users WHERE id = ?");
            $stmt->execute([$this->user_id]);
            $current_user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$current_user) {
                return ['success' => false, 'message' => 'User not found.'];
            }
            
            // Check if username is taken by another user
            if ($username !== $current_user['username']) {
                $stmt = $this->conn->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
                $stmt->execute([$username, $this->user_id]);
                if ($stmt->fetch()) {
                    return ['success' => false, 'message' => 'Username is already taken.'];
                }
                
                // Update username and fullname
                $stmt = $this->conn->prepare("UPDATE users SET fullname = ?, username = ? WHERE id = ?");
                $stmt->execute([$fullname, $username, $this->user_id]);
                $_SESSION['user_fullname'] = $fullname;
                return ['success' => true, 'message' => 'Profile updated successfully!'];
            } else {
                // Update only fullname
                $stmt = $this->conn->prepare("UPDATE users SET fullname = ? WHERE id = ?");
                $stmt->execute([$fullname, $this->user_id]);
                $_SESSION['user_fullname'] = $fullname;
                return ['success' => true, 'message' => 'Profile updated successfully!'];
            }
            
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error updating profile: ' . $e->getMessage()];
        }
    }
    
    /**
     * Change user password
     */
    public function changePassword($current_password, $new_password, $confirm_password) {
        if (empty($current_password)) {
            return ['success' => false, 'message' => 'Current password is required to change password.'];
        }
        
        if ($new_password !== $confirm_password) {
            return ['success' => false, 'message' => 'New passwords do not match.'];
        }
        
        if (strlen($new_password) < 6) {
            return ['success' => false, 'message' => 'New password must be at least 6 characters long.'];
        }
        
        try {
            // Get current user password
            $stmt = $this->conn->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$this->user_id]);
            $current_user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!password_verify($current_password, $current_user['password'])) {
                return ['success' => false, 'message' => 'Current password is incorrect.'];
            }
            
            // Update password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $this->conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashed_password, $this->user_id]);
            
            return ['success' => true, 'message' => 'Password updated successfully!'];
            
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error updating password: ' . $e->getMessage()];
        }
    }
    
    /**
     * Validate input data
     */
    public function validateInput($fullname, $username) {
        if (empty($fullname) || empty($username)) {
            return ['valid' => false, 'message' => 'Full name and username are required.'];
        }
        
        if (strlen($username) < 3) {
            return ['valid' => false, 'message' => 'Username must be at least 3 characters long.'];
        }
        
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            return ['valid' => false, 'message' => 'Username can only contain letters, numbers, and underscores.'];
        }
        
        return ['valid' => true];
    }
}
?>
