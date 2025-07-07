<?php
/**
 * EduLearn Platform - Registration Processing
 * Only allows admin users to create new accounts
 */

require_once '../config/connectiondb.php';

// Create alias for compatibility
$pdo = $conn;

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if user is logged in and is an admin
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
        $_SESSION['error_message'] = 'Only administrators can create new accounts. Please contact your administrator.';
        header('Location: register.php');
        exit;
    }
    
    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $user_role = $_POST['user_role'] ?? 'student';
    $cohort = trim($_POST['cohort'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    
    // Validate input
    if (empty($fullname) || empty($email) || empty($password)) {
        $_SESSION['error_message'] = 'Please fill in all required fields';
        header('Location: register.php');
        exit;
    }
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error_message'] = 'Please enter a valid email address';
        header('Location: register.php');
        exit;
    }
    
    // Validate password confirmation
    if ($password !== $confirm_password) {
        $_SESSION['error_message'] = 'Passwords do not match';
        header('Location: register.php');
        exit;
    }
    
    // Validate password strength
    if (strlen($password) < 6) {
        $_SESSION['error_message'] = 'Password must be at least 6 characters long';
        header('Location: register.php');
        exit;
    }
    
    // Validate user role
    if (!in_array($user_role, ['admin', 'student'])) {
        $_SESSION['error_message'] = 'Invalid user role selected';
        header('Location: register.php');
        exit;
    }
    
    try {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND deleted_at IS NULL");
        $stmt->execute([$email]);
        
        if ($stmt->fetch()) {
            $_SESSION['error_message'] = 'An account with this email already exists';
            header('Location: register.php');
            exit;
        }
        
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert new user
        $stmt = $pdo->prepare("
            INSERT INTO users (fullname, email, password, user_role, cohort, phone, status, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, ?, 'active', NOW(), NOW())
        ");
        
        $stmt->execute([
            $fullname,
            $email,
            $hashed_password,
            $user_role,
            $cohort ?: null,
            $phone ?: null
        ]);
        
        $_SESSION['success_message'] = 'Account created successfully for ' . $fullname;
        header('Location: register.php');
        exit;
        
    } catch (PDOException $e) {
        error_log("Registration error: " . $e->getMessage());
        $_SESSION['error_message'] = 'A database error occurred. Please try again later.';
        header('Location: register.php');
        exit;
    }
    
} else {
    // Not a POST request
    header('Location: register.php');
    exit;
}
?>
