<?php
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied']);
    exit;
}

require_once '../config/connectiondb.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid student ID']);
    exit;
}

$student_id = $_GET['id'];

try {
    $stmt = $conn->prepare("SELECT id, fullname, email, username FROM users WHERE id = ? AND user_role = 'student'");
    $stmt->execute([$student_id]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($student) {
        header('Content-Type: application/json');
        echo json_encode($student);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Student not found']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
