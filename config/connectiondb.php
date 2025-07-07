<?php
/**
 * EduLearn Platform - Database Connection
 * Modern Educational Experience
 */

// Start the session if it hasn't been started already
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Database Configuration
define('DB_HOST', 'localhost:3307'); // Using port 3307 instead of default 3306
define('DB_USER', 'root');
define('DB_PASS', ''); // If your MySQL root user has a password, enter it here
define('DB_NAME', 'edulearn_db');

// Create Connection
try {
    // Extract host and port from DB_HOST
    $hostParts = explode(':', DB_HOST);
    $host = $hostParts[0];
    $port = isset($hostParts[1]) ? $hostParts[1] : '3306';
    
    $conn = new PDO("mysql:host=$host;port=$port;dbname=" . DB_NAME, DB_USER, DB_PASS);
    // Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Set default fetch mode to associative array
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    // Set UTF-8 encoding
    $conn->exec("SET NAMES utf8");
    
    // Create an alias for $pdo for compatibility with notes system
    $pdo = $conn;
    
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

/**
 * Helper function to safely execute queries with prepared statements
 */
function executeQuery($sql, $params = []) {
    global $conn;
    try {
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch(PDOException $e) {
        die("Query failed: " . $e->getMessage());
    }
}

/**
 * Get a single row from a query
 */
function fetchRow($sql, $params = []) {
    $stmt = executeQuery($sql, $params);
    return $stmt->fetch();
}

/**
 * Get all rows from a query
 */
function fetchAll($sql, $params = []) {
    $stmt = executeQuery($sql, $params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Insert data and return the last insert ID
 */
function insert($sql, $params = []) {
    global $conn;
    $stmt = executeQuery($sql, $params);
    return $conn->lastInsertId();
}

/**
 * Session initialization
 */
if (!isset($_SESSION)) {
    session_start();
}

/**
 * Function to check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Function to check if user is an administrator
 */
function isAdmin() {
    return isLoggedIn() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Function to check if user is a student
 */
function isStudent() {
    return isLoggedIn() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'student';
}

/**
 * Function to redirect to a specified URL
 * @param string $url URL to redirect to
 */
function redirect($url) {
    header("Location: $url");
    exit();
}
