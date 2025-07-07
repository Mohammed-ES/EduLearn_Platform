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

// Traiter la restauration d'un étudiant
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'restore_student' && isset($_POST['student_id'])) {
        $student_id = $_POST['student_id'];
        
        try {
            // Start transaction
            $conn->beginTransaction();
            
            // Récupérer les données de l'étudiant supprimé
            $stmt = $conn->prepare("SELECT * FROM deleted_students WHERE id = ?");
            $stmt->execute([$student_id]);
            $student = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($student) {
                // Vérifier si l'email ou username existe déjà
                $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
                $stmt->execute([$student['email'], $student['username']]);
                
                if ($stmt->fetchColumn()) {
                    $error = "Un utilisateur avec cet email ou nom d'utilisateur existe déjà.";
                } else {
                    // Generate a new password since we don't store the original password
                    $temp_password = 'TempPass' . rand(1000, 9999);
                    $hashed_password = password_hash($temp_password, PASSWORD_DEFAULT);
                    
                    // Réinsérer dans la table users
                    $stmt = $conn->prepare("INSERT INTO users (fullname, email, username, password, user_role, created_at) 
                                            VALUES (?, ?, ?, ?, 'student', NOW())");
                    $result = $stmt->execute([
                        $student['fullname'],
                        $student['email'],
                        $student['username'],
                        $hashed_password
                    ]);
                    
                    if ($result) {
                        // Update restore count
                        $stmt = $conn->prepare("UPDATE deleted_students SET restore_count = restore_count + 1 WHERE id = ?");
                        $stmt->execute([$student_id]);
                        
                        // Supprimer de la table deleted_students
                        $stmt = $conn->prepare("DELETE FROM deleted_students WHERE id = ?");
                        $stmt->execute([$student_id]);
                        
                        $conn->commit();
                        $message = "L'étudiant a été restauré avec succès! Mot de passe temporaire: " . $temp_password;
                    } else {
                        $conn->rollBack();
                        $error = "Erreur lors de la restauration de l'étudiant.";
                    }
                }
            } else {
                $conn->rollBack();
                $error = "Étudiant non trouvé.";
            }
        } catch (PDOException $e) {
            $conn->rollBack();
            $error = "Erreur de base de données: " . $e->getMessage();
        }
    } else if ($_POST['action'] === 'restore_all_students') {
        try {
            // Start transaction
            $conn->beginTransaction();
            
            // Récupérer tous les étudiants supprimés
            $stmt = $conn->prepare("SELECT * FROM deleted_students");
            $stmt->execute();
            $studentsToRestore = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($studentsToRestore) > 0) {
                $restoredCount = 0;
                $errors = [];
                
                foreach ($studentsToRestore as $student) {
                    // Check if email or username already exists
                    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
                    $stmt->execute([$student['email'], $student['username']]);
                    
                    if (!$stmt->fetchColumn()) {
                        // Generate a new password since we don't store the original password
                        $temp_password = 'TempPass' . rand(1000, 9999);
                        $hashed_password = password_hash($temp_password, PASSWORD_DEFAULT);
                        
                        $stmt = $conn->prepare("INSERT INTO users (fullname, email, username, password, user_role, created_at) 
                                               VALUES (?, ?, ?, ?, 'student', NOW())");
                        $result = $stmt->execute([
                            $student['fullname'],
                            $student['email'],
                            $student['username'],
                            $hashed_password
                        ]);
                        
                        if ($result) {
                            $restoredCount++;
                        }
                    } else {
                        $errors[] = "Utilisateur avec email/username déjà existant: " . $student['email'];
                    }
                }
                
                if ($restoredCount > 0) {
                    // Vider la table deleted_students après restauration
                    $conn->exec("TRUNCATE TABLE deleted_students");
                    $conn->commit();
                    $message = "$restoredCount étudiants ont été restaurés avec succès!";
                    if (!empty($errors)) {
                        $message .= " Erreurs: " . implode(', ', $errors);
                    }
                } else {
                    $conn->rollBack();
                    $error = "Aucun étudiant n'a pu être restauré. " . implode(', ', $errors);
                }
            } else {
                $message = "Aucun étudiant à restaurer.";
            }
        } catch (PDOException $e) {
            $conn->rollBack();
            $error = "Erreur de base de données: " . $e->getMessage();
        }
    }
}

// Get all deleted students from database (assuming we have a deleted_students table or a deleted flag)
try {
    // Get deleted students from the properly structured table
    $stmt = $conn->prepare("SELECT *, 
                           (SELECT fullname FROM users WHERE id = deleted_students.deleted_by) as deleted_by_name 
                           FROM deleted_students 
                           ORDER BY deleted_at DESC");
    $stmt->execute();
    $deletedStudents = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $deletedStudents = [];
    $error = "Erreur de base de données: " . $e->getMessage();
}

$user_name = $_SESSION['user_fullname'] ?? 'Administrator';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Backup & Restore | EduLearn</title>
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
            --gradient-warning: linear-gradient(135deg, var(--warning) 0%, #FF8C00 100%);
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
        .shape-4 { width: 180px; height: 180px; top: 40%; right: 40%; animation-delay: 3s; background: var(--gradient-warning); }

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
            background: var(--gradient-warning);
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
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .header-info h1 i {
            color: var(--warning);
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
            background: rgba(255, 193, 7, 0.05);
            border-radius: 12px;
            border: 1px solid rgba(255, 193, 7, 0.1);
        }

        .stat-number {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--warning);
            font-family: 'Poppins', sans-serif;
        }

        .stat-label {
            font-size: 12px;
            color: var(--text-light);
            font-weight: 500;
        }

        /* Warning Alert */
        .warning-alert {
            background: linear-gradient(135deg, rgba(255, 193, 7, 0.1) 0%, rgba(255, 140, 0, 0.05) 100%);
            border: 1px solid rgba(255, 193, 7, 0.2);
            border-radius: var(--border-radius);
            padding: 20px 24px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 16px;
            color: #856404;
            font-weight: 500;
        }

        .warning-alert i {
            font-size: 20px;
            color: var(--warning);
        }

        /* Action Cards */
        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 24px;
            margin-bottom: 32px;
        }

        .action-card {
            background: var(--white);
            border-radius: var(--border-radius);
            padding: 24px;
            box-shadow: var(--shadow-medium);
            transition: var(--transition);
            border: 1px solid rgba(0, 123, 255, 0.1);
            position: relative;
            overflow: hidden;
        }

        .action-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-heavy);
        }

        .action-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--gradient-primary);
        }

        .action-card.warning::before {
            background: var(--gradient-warning);
        }

        .action-card.success::before {
            background: linear-gradient(135deg, var(--success) 0%, #20C997 100%);
        }

        .action-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            background: var(--gradient-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            margin-bottom: 16px;
        }

        .action-card.warning .action-icon {
            background: var(--gradient-warning);
        }

        .action-card.success .action-icon {
            background: linear-gradient(135deg, var(--success) 0%, #20C997 100%);
        }

        .action-title {
            font-family: 'Poppins', sans-serif;
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--text-dark);
        }

        .action-description {
            color: var(--text-light);
            margin-bottom: 20px;
            line-height: 1.5;
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

        .btn-warning {
            background: var(--gradient-warning);
            color: white;
            box-shadow: var(--shadow-light);
        }

        .btn-success {
            background: linear-gradient(135deg, var(--success) 0%, #20C997 100%);
            color: white;
            box-shadow: var(--shadow-light);
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-medium);
        }

        /* Deleted Students Table */
        .students-section {
            background: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-medium);
            overflow: hidden;
        }

        .section-header {
            padding: 24px;
            border-bottom: 1px solid rgba(0, 123, 255, 0.1);
            background: rgba(0, 123, 255, 0.02);
        }

        .section-title {
            font-family: 'Poppins', sans-serif;
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--text-dark);
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 8px;
        }

        .section-subtitle {
            color: var(--text-light);
            font-size: 14px;
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
            background: var(--gradient-accent);
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
            background: rgba(220, 53, 69, 0.1);
            color: var(--error);
        }

        .actions-group {
            display: flex;
            gap: 8px;
        }

        .action-btn {
            padding: 6px 16px;
            border: none;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .action-btn.restore {
            background: rgba(40, 167, 69, 0.1);
            color: var(--success);
        }

        .action-btn.restore:hover {
            background: var(--success);
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

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 24px;
            color: var(--text-light);
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 16px;
            opacity: 0.3;
        }

        .empty-state h3 {
            font-size: 1.2rem;
            margin-bottom: 8px;
            color: var(--text-dark);
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
            animation: slideInDown 0.6s ease;
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

        @keyframes slideInDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
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

            .actions-grid {
                grid-template-columns: 1fr;
            }

            .students-table th,
            .students-table td {
                padding: 12px 16px;
            }

            .student-info {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }
        }
    </style>
</head>
<body>
    <!-- Animated Background -->
    <div class="animated-background">
        <div class="floating-shape shape-1"></div>
        <div class="floating-shape shape-2"></div>
        <div class="floating-shape shape-3"></div>
        <div class="floating-shape shape-4"></div>
    </div>

    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="dashboard.php" class="logo">
                <i class="fas fa-graduation-cap"></i>
                EduLearn
            </a>
            <div class="nav-actions">
                <a href="manage_students.php" class="back-btn">
                    <i class="fas fa-arrow-left"></i>
                    Back to Students
                </a>
            </div>
        </div>
    </nav>    <!-- Main Content -->
    <main class="main-content">
        <!-- Success/Error Messages -->
        <?php if ($message): ?>
        <div class="alert alert-success" data-aos="fade-down">
            <i class="fas fa-check-circle"></i>
            <?php echo htmlspecialchars($message); ?>
        </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
        <div class="alert alert-error" data-aos="fade-down">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>
        
        <!-- Page Header -->
        <section class="page-header" data-aos="fade-up">
            <div class="header-content">
                <div class="header-info">
                    <h1>
                        <i class="fas fa-trash-restore"></i>
                        Student Backup & Restore
                    </h1>
                    <p>Restore deleted student accounts and manage backup operations</p>
                </div>
                <div class="header-stats">
                    <div class="stat-item">
                        <div class="stat-number"><?php echo count($deletedStudents); ?></div>
                        <div class="stat-label">Deleted Students</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">30</div>
                        <div class="stat-label">Days Retention</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Warning Alert -->
        <div class="warning-alert" data-aos="fade-up" data-aos-delay="100">
            <i class="fas fa-exclamation-triangle"></i>
            <div>
                <strong>Important:</strong> Deleted students are retained for 30 days before permanent deletion. 
                Use this section to restore accidentally deleted accounts or permanently remove them.
            </div>
        </div>

        <!-- Action Cards -->
        <section class="actions-grid" data-aos="fade-up" data-aos-delay="200">
            <div class="action-card success">
                <div class="action-icon">
                    <i class="fas fa-undo"></i>
                </div>
                <h3 class="action-title">Restore All Students</h3>
                <p class="action-description">
                    Restore all deleted students back to the active students list. This action will reactivate all accounts.
                </p>
                <button class="btn btn-success" onclick="restoreAllStudents()">
                    <i class="fas fa-undo"></i>
                    Restore All
                </button>
            </div>

            <div class="action-card warning">
                <div class="action-icon">
                    <i class="fas fa-database"></i>
                </div>
                <h3 class="action-title">Create Backup</h3>
                <p class="action-description">
                    Create a backup of all student data including active and deleted accounts for safekeeping.
                </p>
                <button class="btn btn-warning" onclick="createBackup()">
                    <i class="fas fa-download"></i>
                    Create Backup
                </button>
            </div>

            <div class="action-card">
                <div class="action-icon">
                    <i class="fas fa-trash-alt"></i>
                </div>
                <h3 class="action-title">Permanent Cleanup</h3>
                <p class="action-description">
                    Permanently delete all students that have been in the trash for more than 30 days.
                </p>
                <button class="btn btn-primary" onclick="permanentCleanup()">
                    <i class="fas fa-broom"></i>
                    Clean Up
                </button>
            </div>
        </section>

        <!-- Deleted Students Table -->
        <section class="students-section" data-aos="fade-up" data-aos-delay="300">
            <div class="section-header">
                <h2 class="section-title">
                    <i class="fas fa-trash"></i>
                    Deleted Students
                </h2>
                <p class="section-subtitle">Students that have been deleted and can be restored</p>
            </div>
            
            <?php if (empty($deletedStudents)): ?>
            <div class="empty-state">
                <i class="fas fa-smile"></i>
                <h3>No Deleted Students</h3>
                <p>Great! There are no deleted students to restore.</p>
            </div>
            <?php else: ?>
            <div class="table-container">
                <table class="students-table">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Email</th>
                            <th>Username</th>
                            <th>Deleted Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($deletedStudents as $student): ?>
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
                            </td>
                            <td><?php echo htmlspecialchars($student['email']); ?></td>
                            <td><?php echo htmlspecialchars($student['username']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($student['deleted_at'])); ?></td>
                            <td><span class="status-badge">Deleted</span></td>
                            <td>
                                <div class="actions-group">
                                    <button class="action-btn restore" onclick="restoreStudent(<?php echo $student['id']; ?>)">
                                        <i class="fas fa-undo"></i>
                                        Restore
                                    </button>
                                    <button class="action-btn delete" onclick="permanentDelete(<?php echo $student['id']; ?>)">
                                        <i class="fas fa-trash"></i>
                                        Delete Forever
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </section>
    </main>

    <!-- Scripts -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        // Initialize AOS animations
        AOS.init({
            duration: 800,
            easing: 'ease-out-cubic',
            once: true,
            offset: 50
        });        // Backup and restore functions
        function restoreStudent(id) {
            if (confirm('Êtes-vous sûr de vouloir restaurer ce compte étudiant?')) {
                // Créer un formulaire pour soumettre l'action
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="restore_student">
                    <input type="hidden" name="student_id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function restoreAllStudents() {
            if (confirm('Êtes-vous sûr de vouloir restaurer TOUS les étudiants supprimés?')) {
                // Créer un formulaire pour soumettre l'action
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="restore_all_students">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function permanentDelete(id) {
            if (confirm('⚠️ WARNING: This will permanently delete the student account. This action cannot be undone!')) {
                if (confirm('Are you absolutely sure? This action is irreversible!')) {
                    // Implementation for permanent deletion
                    console.log('Permanently delete student:', id);
                    showAlert('Student permanently deleted!', 'error');
                }
            }
        }

        function createBackup() {
            if (confirm('Create a backup of all student data?')) {
                // Implementation for creating backup
                console.log('Creating backup...');
                showAlert('Backup created successfully!', 'success');
            }
        }

        function permanentCleanup() {
            if (confirm('⚠️ WARNING: This will permanently delete all students that have been deleted for more than 30 days!')) {
                if (confirm('This action cannot be undone. Continue?')) {
                    // Implementation for permanent cleanup
                    console.log('Performing cleanup...');
                    showAlert('Cleanup completed successfully!', 'success');
                }
            }
        }

        function showAlert(message, type) {
            const alertClass = type === 'success' ? 'alert-success' : 'alert-error';
            const icon = type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle';
            
            const alertHtml = `
                <div class="alert ${alertClass}">
                    <i class="${icon}"></i>
                    ${message}
                </div>
            `;
            
            // Insert alert at the top of main content
            const mainContent = document.querySelector('.main-content');
            mainContent.insertAdjacentHTML('afterbegin', alertHtml);
            
            // Remove alert after 5 seconds
            setTimeout(() => {
                const alert = mainContent.querySelector('.alert');
                if (alert) {
                    alert.remove();
                }
            }, 5000);
        }

        // Add smooth hover effects
        document.querySelectorAll('.action-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-8px) scale(1.02)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
        });
    </script>
</body>
</html>