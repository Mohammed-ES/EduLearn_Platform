<?php
/**
 * EduLearn Platform - Login Processing
 * Handles user authentication and redirects to appropriate dashboard
 */

session_start();
require_once '../config/connectiondb.php';

// Create alias for compatibility
$pdo = $conn;

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    // Validate input
    if (empty($email) || empty($password)) {
        $_SESSION['error_message'] = 'Please fill in all fields';
        header('Location: login.php');
        exit;
    }
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error_message'] = 'Please enter a valid email address';
        header('Location: login.php');
        exit;
    }
    
    try {
        // Check if user exists and is active
        $stmt = $pdo->prepare("
            SELECT id, fullname, username, email, password, user_role, status 
            FROM users 
            WHERE email = ? AND deleted_at IS NULL
        ");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);        if ($user) {
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Check if account is active
                if ($user['status'] !== 'active') {
                    $_SESSION['error_message'] = 'Your account is not active. Please contact an administrator.';
                    header('Location: login.php');
                    exit;
                }
                
                // Login successful - set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_fullname'] = $user['fullname'];
                $_SESSION['user_role'] = $user['user_role'];
                $_SESSION['is_logged_in'] = true;
                
                // Update last login
                $updateStmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                $updateStmt->execute([$user['id']]);
                
                // Set remember me cookie if checked
                if ($remember) {
                    $token = bin2hex(random_bytes(32));
                    setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/', '', false, true); // 30 days
                    
                    // Store token in database (you might want to create a remember_tokens table)
                    // For now, we'll skip this advanced feature
                }
                
                // Redirect based on user role
                switch ($user['user_role']) {
                    case 'admin':
                        $_SESSION['success_message'] = 'Welcome back, ' . $user['fullname'] . '!';
                        header('Location: ../admin/dashboard.php');
                        break;
                    case 'student':
                        $_SESSION['success_message'] = 'Welcome back, ' . $user['fullname'] . '!';
                        header('Location: ../student/student_dashboard.php');
                        break;
                    default:
                        $_SESSION['error_message'] = 'Invalid user role. Please contact an administrator.';
                        header('Location: login.php');
                }
                exit;            } else {
                // Invalid password
                $_SESSION['error_message'] = 'Invalid email or password';
                header('Location: login.php');
                exit;
            }
        } else {
            // User not found
            $_SESSION['error_message'] = 'Invalid email or password';
            header('Location: login.php');
            exit;
        }
        
    } catch (PDOException $e) {
        // Database error
        error_log("Login error: " . $e->getMessage());
        $_SESSION['error_message'] = 'A database error occurred. Please try again later.';
        header('Location: login.php');
        exit;
    }
    
} else {
    // Not a POST request
    header('Location: login.php');
    exit;
}
?>
