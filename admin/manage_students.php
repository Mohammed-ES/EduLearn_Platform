<?php
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

require_once '../config/connectiondb.php';

$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_student':
                $fullname = trim($_POST['fullname']);
                $email = trim($_POST['email']);
                $username = trim($_POST['username']);
                $password = $_POST['password'];
                
                // Validation
                if (empty($fullname) || empty($email) || empty($username) || empty($password)) {
                    $error = 'All fields are required.';
                } else {
                    // Check if username or email already exists
                    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
                    $stmt->execute([$username, $email]);
                    
                    if ($stmt->fetchColumn()) {
                        $error = 'Username or email already exists.';
                    } else {
                        // Hash password and insert new student
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                        $stmt = $conn->prepare("INSERT INTO users (fullname, email, username, password, user_role, created_at) VALUES (?, ?, ?, ?, 'student', NOW())");
                        
                        if ($stmt->execute([$fullname, $email, $username, $hashed_password])) {
                            $message = 'Student added successfully!';
                        } else {
                            $error = 'Failed to add student. Please try again.';
                        }
                    }
                }
                break;
            
            case 'edit_student':
                $student_id = $_POST['student_id'];
                $fullname = trim($_POST['fullname']);
                $email = trim($_POST['email']);
                $username = trim($_POST['username']);
                $password = $_POST['password'] ?? '';
                
                // Validation for required fields
                if (empty($fullname) || empty($email) || empty($username)) {
                    $error = 'Full name, email, and username are required.';
                } else {
                    // Check if username or email already exists for other students
                    $stmt = $conn->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ? AND user_role = 'student'");
                    $stmt->execute([$username, $email, $student_id]);
                    
                    if ($stmt->fetchColumn()) {
                        $error = 'Username or email already exists for another student.';
                    } else {
                        // Update student info
                        if (!empty($password)) {
                            // If password is provided, update it as well
                            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                            $stmt = $conn->prepare("UPDATE users SET fullname = ?, email = ?, username = ?, password = ? WHERE id = ? AND user_role = 'student'");
                            $result = $stmt->execute([$fullname, $email, $username, $hashed_password, $student_id]);
                        } else {
                            // If no password provided, don't update it
                            $stmt = $conn->prepare("UPDATE users SET fullname = ?, email = ?, username = ? WHERE id = ? AND user_role = 'student'");
                            $result = $stmt->execute([$fullname, $email, $username, $student_id]);
                        }
                        
                        if ($result) {
                            $message = 'Student updated successfully!';
                        } else {
                            $error = 'Failed to update student. Please try again.';
                        }
                    }
                }
                break;
                  case 'delete_student':
                $student_id = $_POST['student_id'];
                
                try {
                    // Start transaction
                    $conn->beginTransaction();
                    
                    // Move student to deleted_students table first
                    $stmt = $conn->prepare("INSERT INTO deleted_students (original_id, fullname, email, username, deleted_at, deleted_by) 
                                          SELECT id, fullname, email, username, NOW(), ? FROM users WHERE id = ?");
                    $stmt->execute([$_SESSION['user_id'], $student_id]);
                    
                    // Then delete from users table
                    $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND user_role = 'student'");
                    $stmt->execute([$student_id]);
                    
                    // Commit transaction
                    $conn->commit();
                    $message = 'Student deleted successfully!';
                } catch (PDOException $e) {
                    // Rollback transaction on error
                    $conn->rollBack();
                    $error = 'Failed to delete student: ' . $e->getMessage();
                }
                break;
        }
    }
}

// Get all students from database
try {
    $stmt = $conn->prepare("SELECT id, fullname, email, username, created_at FROM users WHERE user_role = 'student' ORDER BY created_at DESC");
    $stmt->execute();
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $students = [];
    $error = "Erreur de base de données: " . $e->getMessage();
}

$user_name = $_SESSION['user_fullname'] ?? 'Administrator';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students | EduLearn</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        :root {
            --primary-blue: #007BFF;
            --gold-accent: #D4AF37;
            --dark-blue: #0F4C75;
            --blue-gray: #6C757D;
            --light-bg: #F5F8FA;
            --white: #FFFFFF;
            --text-dark: #2C3E50;
            --text-light: #7F8C8D;
            --success: #28A745;
            --error: #DC3545;
            --warning: #FFC107;
            --shadow-light: 0 2px 10px rgba(0, 123, 255, 0.1);
            --shadow-medium: 0 5px 25px rgba(0, 123, 255, 0.15);
            --shadow-heavy: 0 10px 40px rgba(15, 76, 117, 0.2);
            --gradient-primary: linear-gradient(135deg, var(--primary-blue) 0%, var(--dark-blue) 100%);
            --gradient-accent: linear-gradient(135deg, var(--gold-accent) 0%, #B8941F 100%);
            --border-radius: 16px;
            --transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--light-bg);
            color: var(--text-dark);
            line-height: 1.6;
            overflow-x: hidden;
        }

        /* Animated Background */
        .animated-background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            overflow: hidden;
        }

        .floating-shape {
            position: absolute;
            border-radius: 50%;
            background: var(--gradient-primary);
            opacity: 0.05;
            animation: float-gentle 8s ease-in-out infinite;
        }

        .shape-1 { width: 300px; height: 300px; top: 10%; left: 5%; animation-delay: 0s; }
        .shape-2 { width: 200px; height: 200px; top: 60%; right: 10%; animation-delay: 2s; background: var(--gradient-accent); }
        .shape-3 { width: 150px; height: 150px; bottom: 20%; left: 20%; animation-delay: 4s; }

        @keyframes float-gentle {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }

        /* Header Navigation */
        .navbar {
            background: var(--white);
            backdrop-filter: blur(20px);
            box-shadow: var(--shadow-light);
            position: sticky;
            top: 0;
            z-index: 1000;
            border-bottom: 1px solid rgba(0, 123, 255, 0.1);
        }

        .nav-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 80px;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            font-family: 'Poppins', sans-serif;
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--dark-blue);
            text-decoration: none;
            transition: var(--transition);
        }

        .logo i {
            font-size: 2rem;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: pulse-glow 2s ease-in-out infinite;
        }

        @keyframes pulse-glow {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .back-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(0, 123, 255, 0.1);
            color: var(--primary-blue);
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 50px;
            font-weight: 500;
            transition: var(--transition);
            font-size: 14px;
        }

        .back-btn:hover {
            background: var(--primary-blue);
            color: white;
            transform: translateX(-2px);
        }

        /* Main Content */
        .main-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 40px 24px;
        }

        /* Page Header */
        .page-header {
            background: var(--white);
            border-radius: var(--border-radius);
            padding: 32px;
            margin-bottom: 32px;
            box-shadow: var(--shadow-medium);
            position: relative;
            overflow: hidden;
        }

        .page-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient-primary);
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 24px;
        }

        .header-info h1 {
            font-family: 'Poppins', sans-serif;
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 8px;
        }

        .header-info p {
            color: var(--text-light);
            font-size: 16px;
        }

        .header-stats {
            display: flex;
            gap: 24px;
        }

        .stat-item {
            text-align: center;
            padding: 16px 24px;
            background: rgba(0, 123, 255, 0.05);
            border-radius: 12px;
            border: 1px solid rgba(0, 123, 255, 0.1);
        }

        .stat-number {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary-blue);
            font-family: 'Poppins', sans-serif;
        }

        .stat-label {
            font-size: 12px;
            color: var(--text-light);
            font-weight: 500;
        }

        /* Toolbar */
        .toolbar {
            background: var(--white);
            border-radius: var(--border-radius);
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: var(--shadow-light);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        .toolbar-left {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            border: none;
            border-radius: 50px;
            font-weight: 500;
            font-size: 14px;
            text-decoration: none;
            cursor: pointer;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .btn-primary {
            background: var(--gradient-primary);
            color: white;
            box-shadow: var(--shadow-light);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-medium);
        }

        .btn-secondary {
            background: var(--gradient-accent);
            color: white;
            box-shadow: var(--shadow-light);
        }

        .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-medium);
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: var(--transition);
        }

        .btn:hover::before {
            left: 100%;
        }

        .search-container {
            position: relative;
            min-width: 300px;
        }

        .search-input {
            width: 100%;
            padding: 12px 16px 12px 44px;
            border: 2px solid rgba(0, 123, 255, 0.1);
            border-radius: 50px;
            font-size: 14px;
            transition: var(--transition);
            background: var(--white);
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
        }

        .search-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
        }

        /* Students Table */
        .students-section {
            background: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-medium);
            overflow: hidden;
        }

        .table-container {
            overflow-x: auto;
        }

        .students-table {
            width: 100%;
            border-collapse: collapse;
        }

        .students-table th,
        .students-table td {
            padding: 16px 20px;
            text-align: left;
            border-bottom: 1px solid rgba(0, 123, 255, 0.1);
        }

        .students-table th {
            background: rgba(0, 123, 255, 0.05);
            font-weight: 600;
            color: var(--text-dark);
            font-size: 14px;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .students-table tbody tr {
            transition: var(--transition);
        }

        .students-table tbody tr:hover {
            background: rgba(0, 123, 255, 0.03);
        }

        .student-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .student-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--gradient-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 14px;
        }

        .student-details h4 {
            font-weight: 500;
            margin-bottom: 2px;
        }

        .student-details p {
            font-size: 12px;
            color: var(--text-light);
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-active {
            background: rgba(40, 167, 69, 0.1);
            color: var(--success);
        }

        .actions-group {
            display: flex;
            gap: 8px;
        }

        .action-btn {
            width: 32px;
            height: 32px;
            border: none;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition);
            font-size: 14px;
        }

        .action-btn.edit {
            background: rgba(0, 123, 255, 0.1);
            color: var(--primary-blue);
        }

        .action-btn.edit:hover {
            background: var(--primary-blue);
            color: white;
        }

        .action-btn.delete {
            background: rgba(220, 53, 69, 0.1);
            color: var(--error);
        }

        .action-btn.delete:hover {
            background: var(--error);
            color: white;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(8px);
            z-index: 2000;
            animation: fadeIn 0.3s ease;
        }

        .modal.show {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .modal-content {
            background: var(--white);
            border-radius: var(--border-radius);
            padding: 32px;
            max-width: 500px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: var(--shadow-heavy);
            position: relative;
            animation: slideUp 0.3s ease;
        }

        @keyframes slideUp {
            from { transform: translateY(50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .modal-title {
            font-family: 'Poppins', sans-serif;
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-dark);
        }

        .close-btn {
            width: 40px;
            height: 40px;
            border: none;
            background: rgba(220, 53, 69, 0.1);
            color: var(--error);
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
            font-size: 18px;
        }

        .close-btn:hover {
            background: var(--error);
            color: white;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-dark);
        }

        .form-input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid rgba(0, 123, 255, 0.1);
            border-radius: 8px;
            font-size: 14px;
            transition: var(--transition);
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
        }

        .modal-actions {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            margin-top: 24px;
        }

        .btn-cancel {
            background: rgba(108, 117, 125, 0.1);
            color: var(--blue-gray);
        }

        .btn-cancel:hover {
            background: var(--blue-gray);
            color: white;
        }

        /* Success/Error Messages */
        .alert {
            padding: 16px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 500;
        }

        .alert-success {
            background: rgba(40, 167, 69, 0.1);
            color: var(--success);
            border: 1px solid rgba(40, 167, 69, 0.2);
        }

        .alert-error {
            background: rgba(220, 53, 69, 0.1);
            color: var(--error);
            border: 1px solid rgba(220, 53, 69, 0.2);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                align-items: flex-start;
            }

            .header-stats {
                width: 100%;
                justify-content: space-between;
            }

            .toolbar {
                flex-direction: column;
                align-items: stretch;
            }

            .search-container {
                min-width: auto;
            }

            .students-table th,
            .students-table td {
                padding: 12px 16px;
            }

            .modal-content {
                padding: 24px;
                margin: 20px;
            }
        }

        /* Loading Animation */
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(0, 123, 255, 0.3);
            border-radius: 50%;
            border-top-color: var(--primary-blue);
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <!-- Animated Background -->
    <div class="animated-background">
        <div class="floating-shape shape-1"></div>
        <div class="floating-shape shape-2"></div>
        <div class="floating-shape shape-3"></div>
    </div>

    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="dashboard.php" class="logo">
                <i class="fas fa-graduation-cap"></i>
                EduLearn
            </a>
            <div class="nav-actions">
                <a href="dashboard.php" class="back-btn">
                    <i class="fas fa-arrow-left"></i>
                    Back to Dashboard
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->    <main class="main-content">        <!-- Success/Error Messages -->
        <?php if ($message): ?>
        <div class="alert alert-success" data-aos="fade-down">
            <i class="fas fa-check-circle"></i>
            <?php echo htmlspecialchars($message); ?>
        </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
        <div class="alert alert-error" data-aos="fade-down">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo htmlspecialchars($error); ?>        </div>
        <?php endif; ?>

        <!-- Page Header -->
        <section class="page-header" data-aos="fade-up">
            <div class="header-content">
                <div class="header-info">
                    <h1>Student Management</h1>
                    <p>Manage and oversee all student accounts in the system</p>
                </div>
                <div class="header-stats">
                    <div class="stat-item">
                        <div class="stat-number"><?php echo count($students); ?></div>
                        <div class="stat-label">Total Students</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number"><?php echo count($students); ?></div>
                        <div class="stat-label">Active Students</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Toolbar -->
        <section class="toolbar" data-aos="fade-up" data-aos-delay="100">
            <div class="toolbar-left">
                <button class="btn btn-primary" onclick="openAddModal()">
                    <i class="fas fa-plus"></i>
                    Add Student
                </button>
                <a href="backup.php" class="btn btn-secondary">
                    <i class="fas fa-trash-restore"></i>
                    Restore Students
                </a>
            </div>
            <div class="search-container">
                <i class="fas fa-search search-icon"></i>
                <input type="text" class="search-input" id="searchInput" placeholder="Search students by name or email...">
            </div>
        </section>

        <!-- Students Table -->        <section class="students-section" data-aos="fade-up" data-aos-delay="200">
            <div class="table-container">
                <table class="students-table">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Email</th>
                            <th style="background-color: rgba(0, 123, 255, 0.1);">Username</th>
                            <th>Join Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead><tbody id="studentsTableBody">
                        <?php if (count($students) > 0): ?>
                            <?php foreach ($students as $student): ?>
                            <tr>
                                <td>
                                    <div class="student-info">
                                        <div class="student-avatar">
                                            <?php echo strtoupper(substr($student['fullname'], 0, 2)); ?>
                                        </div>
                                        <div class="student-details">
                                            <h4><?php echo htmlspecialchars($student['fullname']); ?></h4>
                                            <p>ID: #<?php echo $student['id']; ?></p>
                                        </div>
                                    </div>
                                </td>                                <td><?php echo htmlspecialchars($student['email']); ?></td>
                                <td style="font-weight: bold; color: var(--primary-blue);"><?php echo !empty($student['username']) ? htmlspecialchars($student['username']) : '<span style="color: var(--text-light); font-style: italic;">Non défini</span>'; ?></td>
                                <td><?php echo date('M d, Y', strtotime($student['created_at'])); ?></td>
                                <td><span class="status-badge status-active">Active</span></td>
                                <td>
                                    <div class="actions-group">
                                        <button class="action-btn edit" onclick="editStudent(<?php echo $student['id']; ?>)" title="Edit Student">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="action-btn delete" onclick="deleteStudent(<?php echo $student['id']; ?>)" title="Delete Student">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 40px; color: var(--text-light);">
                                    <i class="fas fa-users" style="font-size: 48px; margin-bottom: 16px; opacity: 0.3;"></i>
                                    <h3 style="margin-bottom: 8px; font-weight: 500;">No Students Found</h3>
                                    <p style="margin-bottom: 0;">Add your first student to get started with the system.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <!-- Student Modal -->
    <div id="studentModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="modalTitle">Add New Student</h2>
                <button class="close-btn" onclick="closeModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>            <form id="studentForm" method="POST">
                <input type="hidden" name="action" value="add_student">
                <div class="form-group">
                    <label class="form-label">Full Name</label>
                    <input type="text" class="form-input" name="fullname" id="modalFullName" placeholder="Enter full name" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" class="form-input" name="email" id="modalEmail" placeholder="Enter email address" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Username</label>
                    <input type="text" class="form-input" name="username" id="modalUsername" placeholder="Enter username" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" class="form-input" name="password" id="modalPassword" placeholder="Enter password" required>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-cancel" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        Save Student
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        // Initialize AOS animations
        AOS.init({
            duration: 800,
            easing: 'ease-out-cubic',
            once: true,
            offset: 50
        });

        // Student management functions
        function openAddModal() {
            document.getElementById('modalTitle').textContent = 'Add New Student';
            document.getElementById('studentForm').reset();
            document.getElementById('studentModal').classList.add('show');
        }        function closeModal() {
            document.getElementById('studentModal').classList.remove('show');
        }        function editStudent(id) {
            // Fetch student data using AJAX
            const xhr = new XMLHttpRequest();
            xhr.onreadystatechange = function() {
                if (this.readyState === 4) {
                    if (this.status === 200) {
                        try {
                            const student = JSON.parse(this.responseText);
                            
                            // Update modal title and form action
                            document.getElementById('modalTitle').textContent = 'Edit Student';
                            const form = document.getElementById('studentForm');
                            form.querySelector('input[name="action"]').value = 'edit_student';
                            
                            // Add student ID as hidden field
                            if (!form.querySelector('input[name="student_id"]')) {
                                const idField = document.createElement('input');
                                idField.type = 'hidden';
                                idField.name = 'student_id';
                                form.appendChild(idField);
                            }
                            form.querySelector('input[name="student_id"]').value = student.id;
                            
                            // Fill form fields with student data
                            document.getElementById('modalFullName').value = student.fullname;
                            document.getElementById('modalEmail').value = student.email;
                            document.getElementById('modalUsername').value = student.username;
                            
                            // For security, don't fill password field, but make it optional for edits
                            const passwordField = document.getElementById('modalPassword');
                            passwordField.required = false;
                            passwordField.placeholder = 'Leave empty to keep current password';
                            
                            // Change submit button text
                            const submitBtn = form.querySelector('button[type="submit"]');
                            submitBtn.innerHTML = '<i class="fas fa-save"></i> Update Student';
                            
                            // Show the modal
                            document.getElementById('studentModal').classList.add('show');
                            
                        } catch (e) {
                            console.error('Error parsing JSON:', e);
                            alert('Failed to load student data. Please try again.');
                        }
                    } else {
                        console.error('Error fetching student data:', this.status);
                        alert('Failed to load student data. Please try again.');
                    }
                }
            };
            
            xhr.open('GET', 'get_student.php?id=' + id, true);
            xhr.send();
        }
        
        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('#studentsTableBody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });

        // Form validation
        document.getElementById('studentForm').addEventListener('submit', function(e) {
            const fullname = document.getElementById('modalFullName').value.trim();
            const email = document.getElementById('modalEmail').value.trim();
            const username = document.getElementById('modalUsername').value.trim();
            const password = document.getElementById('modalPassword').value;

            // Basic validation
            if (fullname.length < 2) {
                alert('Full name must be at least 2 characters long.');
                e.preventDefault();
                return false;
            }

            if (username.length < 3) {
                alert('Username must be at least 3 characters long.');
                e.preventDefault();
                return false;
            }

            if (password.length < 6) {
                alert('Password must be at least 6 characters long.');
                e.preventDefault();
                return false;
            }

            // Email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                alert('Please enter a valid email address.');
                e.preventDefault();
                return false;
            }

            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<div class="loading"></div> Adding Student...';
            submitBtn.disabled = true;

            return true;
        });
        
        function deleteStudent(id) {
            if (confirm('Are you sure you want to delete this student? This action can be undone from the backup page.')) {
                // Create a form to submit the delete request
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete_student">
                    <input type="hidden" name="student_id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }        // Close modal when clicking outside
        document.getElementById('studentModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });

        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-20px)';
                setTimeout(() => alert.remove(), 300);
            });
        }, 5000);
    </script>
</body>
</html>
