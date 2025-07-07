<?php
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

require_once '../config/connectiondb.php';

// Get admin data
$admin_id = $_SESSION['user_id'];
try {
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ? AND user_role = 'admin'");
    $stmt->execute([$admin_id]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}

// Get system settings
try {
    $stmt = $conn->prepare("SELECT * FROM system_settings");
    $stmt->execute();
    $system_settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
} catch (PDOException $e) {
    $system_settings = [];
}

// Process form submission
$message = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'update_profile':
            $fullname = trim($_POST['fullname']);
            $username = trim($_POST['username']);
            $email = trim($_POST['email']);
            $password = $_POST['password'];
            $phone = trim($_POST['phone']);
            
            // Basic validation
            if (empty($fullname) || empty($username) || empty($email)) {
                $error = 'Full name, username and email are required fields.';
            } else {
                try {
                    // Check if username or email already exists for other admins
                    $stmt = $conn->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ? AND user_role = 'admin'");
                    $stmt->execute([$username, $email, $admin_id]);
                    
                    if ($stmt->fetchColumn()) {
                        $error = 'Username or email already exists for another admin.';
                    } else {
                        // Update admin info
                        if (!empty($password)) {
                            // If password is provided, update it as well
                            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                            $stmt = $conn->prepare("UPDATE users SET fullname = ?, username = ?, email = ?, password = ?, phone = ? WHERE id = ?");
                            $result = $stmt->execute([$fullname, $username, $email, $hashed_password, $phone, $admin_id]);
                        } else {
                            // If no password provided, don't update it
                            $stmt = $conn->prepare("UPDATE users SET fullname = ?, username = ?, email = ?, phone = ? WHERE id = ?");
                            $result = $stmt->execute([$fullname, $username, $email, $phone, $admin_id]);
                        }
                        
                        if ($result) {
                            $message = 'Profile updated successfully!';
                            // Update session data
                            $_SESSION['user_fullname'] = $fullname;
                            // Refresh admin data
                            $stmt = $conn->prepare("SELECT * FROM users WHERE id = ? AND user_role = 'admin'");
                            $stmt->execute([$admin_id]);
                            $admin = $stmt->fetch(PDO::FETCH_ASSOC);
                        } else {
                            $error = 'Failed to update profile. Please try again.';
                        }
                    }
                } catch (PDOException $e) {
                    $error = "Database error: " . $e->getMessage();
                }
            }
            break;
            
        case 'update_system_settings':
            try {
                $settings_updated = 0;
                foreach ($_POST as $key => $value) {
                    if ($key !== 'action' && strpos($key, 'setting_') === 0) {
                        $setting_key = substr($key, 8); // Remove 'setting_' prefix
                        $stmt = $conn->prepare("UPDATE system_settings SET setting_value = ? WHERE setting_key = ?");
                        if ($stmt->execute([$value, $setting_key])) {
                            $settings_updated++;
                        }
                    }
                }
                
                if ($settings_updated > 0) {
                    $message = 'System settings updated successfully!';
                    // Refresh system settings
                    $stmt = $conn->prepare("SELECT * FROM system_settings");
                    $stmt->execute();
                    $system_settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
                } else {
                    $error = 'No settings were updated.';
                }
            } catch (PDOException $e) {
                $error = "Database error: " . $e->getMessage();
            }
            break;
    }
}

$user_name = $_SESSION['user_fullname'] ?? 'Administrator';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Settings | EduLearn</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
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
            min-height: 100vh;
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
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 24px;
            display: flex;
            gap: 30px;
        }

        /* Settings Sidebar */
        .settings-sidebar {
            width: 280px;
            background: var(--white);
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow-medium);
            flex-shrink: 0;
            animation: slideInLeft 0.6s ease;
            height: fit-content;
        }

        @keyframes slideInLeft {
            from { opacity: 0; transform: translateX(-30px); }
            to { opacity: 1; transform: translateX(0); }
        }

        .admin-profile {
            padding: 30px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(0, 123, 255, 0.1);
            background: linear-gradient(135deg, rgba(0, 123, 255, 0.05) 0%, rgba(15, 76, 117, 0.05) 100%);
        }

        .admin-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            margin: 0 auto 15px;
            background: var(--gradient-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            color: white;
            font-weight: 600;
            position: relative;
            transition: var(--transition);
            box-shadow: var(--shadow-light);
        }

        .admin-avatar:hover {
            transform: scale(1.05);
            box-shadow: var(--shadow-medium);
        }

        .admin-info h3 {
            font-family: 'Poppins', sans-serif;
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 5px;
            color: var(--text-dark);
        }

        .admin-info p {
            font-size: 13px;
            color: var(--text-light);
            margin: 0;
        }

        .settings-menu {
            padding: 15px 0;
        }

        .menu-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 20px;
            color: var(--text-dark);
            text-decoration: none;
            transition: var(--transition);
            position: relative;
            font-weight: 500;
            font-size: 14px;
            cursor: pointer;
        }

        .menu-item:hover, .menu-item.active {
            background: rgba(0, 123, 255, 0.05);
            color: var(--primary-blue);
        }

        .menu-item.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background: var(--primary-blue);
        }

        .menu-item i {
            width: 20px;
            text-align: center;
            font-size: 16px;
        }

        /* Settings Content */
        .settings-content {
            flex-grow: 1;
            animation: fadeIn 0.6s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .content-section {
            display: none;
            background: var(--white);
            border-radius: var(--border-radius);
            padding: 30px;
            box-shadow: var(--shadow-light);
            margin-bottom: 20px;
        }

        .content-section.active {
            display: block;
        }

        .content-header {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid rgba(0, 123, 255, 0.1);
        }

        .content-title {
            font-family: 'Poppins', sans-serif;
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--text-dark);
        }

        .content-title i {
            color: var(--primary-blue);
        }

        .content-subtitle {
            color: var(--text-light);
            font-size: 14px;
        }

        /* Form Elements */
        .form-section {
            margin-bottom: 30px;
        }

        .section-title {
            font-family: 'Poppins', sans-serif;
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
            color: var(--text-dark);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .section-title i {
            color: var(--primary-blue);
            font-size: 16px;
        }

        .form-row {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            flex: 1;
            position: relative;
            margin-bottom: 20px;
        }

        .form-row .form-group {
            margin-bottom: 0;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            font-size: 14px;
            color: var(--text-dark);
        }

        .form-control, .form-input, .form-textarea, .form-select {
            width: 100%;
            padding: 14px 16px;
            font-size: 14px;
            border: 2px solid rgba(0, 123, 255, 0.1);
            border-radius: 8px;
            background-color: var(--white);
            transition: var(--transition);
            color: var(--text-dark);
            font-family: 'Inter', sans-serif;
        }

        .form-textarea {
            min-height: 100px;
            resize: vertical;
        }

        .form-control:focus, .form-input:focus, .form-textarea:focus, .form-select:focus {
            outline: none;
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
        }

        .form-control::placeholder {
            color: var(--text-light);
            opacity: 0.7;
        }

        .input-icon {
            position: absolute;
            right: 16px;
            top: 45px;
            color: var(--text-light);
            cursor: pointer;
        }

        /* Toggle Switch */
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 24px;
        }

        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: var(--blue-gray);
            transition: var(--transition);
            border-radius: 24px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: var(--transition);
            border-radius: 50%;
        }

        input:checked + .slider {
            background: var(--gradient-primary);
        }

        input:checked + .slider:before {
            transform: translateX(26px);
        }

        .toggle-group {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 0;
            border-bottom: 1px solid rgba(0, 123, 255, 0.1);
        }

        .toggle-group:last-child {
            border-bottom: none;
        }

        .toggle-info {
            flex: 1;
        }

        .toggle-title {
            font-weight: 500;
            margin-bottom: 4px;
        }

        .toggle-description {
            font-size: 12px;
            color: var(--text-light);
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 28px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: var(--transition);
            font-family: 'Inter', sans-serif;
            text-decoration: none;
            position: relative;
            overflow: hidden;
        }

        .btn-primary {
            background: var(--gradient-primary);
            color: white;
            box-shadow: var(--shadow-light);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-medium);
        }

        .btn-secondary {
            background: rgba(108, 117, 125, 0.1);
            color: var(--blue-gray);
        }

        .btn-secondary:hover {
            background: var(--blue-gray);
            color: white;
        }

        .btn-success {
            background: linear-gradient(135deg, var(--success) 0%, #20C997 100%);
            color: white;
            box-shadow: var(--shadow-light);
        }

        .btn-warning {
            background: linear-gradient(135deg, var(--warning) 0%, #FF8C00 100%);
            color: white;
            box-shadow: var(--shadow-light);
        }

        .btn:hover {
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

        .form-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid rgba(0, 123, 255, 0.1);
        }

        .btn-group {
            display: flex;
            gap: 12px;
            margin-top: 20px;
        }

        /* Color Picker */
        .color-picker {
            display: flex;
            gap: 12px;
            margin-top: 8px;
            flex-wrap: wrap;
        }

        .color-option {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            border: 3px solid transparent;
            cursor: pointer;
            transition: var(--transition);
        }

        .color-option.active {
            border-color: var(--primary-blue);
            transform: scale(1.1);
        }

        .color-option:hover {
            transform: scale(1.1);
        }

        /* Alert Messages */
        .alert {
            padding: 16px 20px;
            border-radius: 8px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 500;
            animation: slideInDown 0.6s ease;
        }

        @keyframes slideInDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .alert-success {
            background: rgba(40, 167, 69, 0.1);
            color: var(--success);
            border: 1px solid rgba(40, 167, 69, 0.2);
        }

        .alert-danger {
            background: rgba(220, 53, 69, 0.1);
            color: var(--error);
            border: 1px solid rgba(220, 53, 69, 0.2);
        }

        /* Password Strength Meter */
        .password-strength {
            margin-top: 10px;
            height: 5px;
            border-radius: 5px;
            background: #e9ecef;
            overflow: hidden;
            transition: var(--transition);
        }

        .password-strength-meter {
            height: 100%;
            width: 0;
            transition: var(--transition);
        }

        .weak { width: 25%; background-color: #dc3545; }
        .medium { width: 50%; background-color: #ffc107; }
        .strong { width: 75%; background-color: #17a2b8; }
        .very-strong { width: 100%; background-color: #28a745; }

        /* Responsive Styles */
        @media (max-width: 992px) {
            .main-content {
                flex-direction: column;
            }
            
            .settings-sidebar {
                width: 100%;
                margin-bottom: 24px;
            }
        }

        @media (max-width: 768px) {
            .form-row {
                flex-direction: column;
                gap: 0;
            }
            
            .form-footer {
                flex-direction: column;
                gap: 15px;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }

            .btn-group {
                flex-direction: column;
            }

            .color-picker {
                flex-wrap: wrap;
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

    <!-- Main Content -->
    <main class="main-content">
        <!-- Settings Sidebar -->
        <aside class="settings-sidebar">
            <div class="admin-profile">
                <div class="admin-avatar">
                    <?php echo strtoupper(substr($admin['fullname'] ?? 'A', 0, 1)); ?>
                </div>
                <div class="admin-info">
                    <h3><?php echo htmlspecialchars($admin['fullname'] ?? 'Administrator'); ?></h3>
                    <p><?php echo htmlspecialchars($admin['email'] ?? 'admin@edulearn.com'); ?></p>
                </div>
            </div>
            <div class="settings-menu">
                <div class="menu-item active" data-section="profile">
                    <i class="fas fa-user-cog"></i>
                    Profile Settings
                </div>
                <div class="menu-item" data-section="general">
                    <i class="fas fa-cog"></i>
                    General Settings
                </div>
                <div class="menu-item" data-section="notifications">
                    <i class="fas fa-bell"></i>
                    Notifications
                </div>
                <div class="menu-item" data-section="security">
                    <i class="fas fa-lock"></i>
                    Security
                </div>
                <div class="menu-item" data-section="appearance">
                    <i class="fas fa-palette"></i>
                    Appearance
                </div>
                <div class="menu-item" data-section="backup">
                    <i class="fas fa-database"></i>
                    Backup & Maintenance
                </div>
            </div>
        </aside>

        <!-- Settings Content -->
        <div class="settings-content">
            <!-- Alert Messages -->
            <?php if ($message): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo htmlspecialchars($message); ?>
            </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>

            <!-- Profile Settings Section -->
            <div class="content-section active" id="profile-section">
                <div class="content-header">
                    <h2 class="content-title">
                        <i class="fas fa-user-cog"></i>
                        Profile Settings
                    </h2>
                    <p class="content-subtitle">Manage your personal information and account settings</p>
                </div>

                <form id="profileForm" method="POST">
                    <input type="hidden" name="action" value="update_profile">
                    <div class="form-section">
                        <h3 class="section-title">
                            <i class="fas fa-user"></i>
                            Personal Information
                        </h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="fullname">Full Name</label>
                                <input type="text" class="form-control" id="fullname" name="fullname" value="<?php echo htmlspecialchars($admin['fullname'] ?? ''); ?>" required>
                                <span class="input-icon"><i class="fas fa-user"></i></span>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="username">Username</label>
                                <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($admin['username'] ?? ''); ?>" required>
                                <span class="input-icon"><i class="fas fa-at"></i></span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="email">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($admin['email'] ?? ''); ?>" required>
                            <span class="input-icon"><i class="fas fa-envelope"></i></span>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="phone">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($admin['phone'] ?? ''); ?>">
                            <span class="input-icon"><i class="fas fa-phone"></i></span>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3 class="section-title">
                            <i class="fas fa-lock"></i>
                            Security Settings
                        </h3>
                        <div class="form-group">
                            <label class="form-label" for="password">Change Password</label>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Leave empty to keep current password">
                            <span class="input-icon toggle-password" onclick="togglePassword()">
                                <i class="fas fa-eye"></i>
                            </span>
                        </div>
                        <div class="password-strength">
                            <div class="password-strength-meter" id="passwordStrengthMeter"></div>
                        </div>
                    </div>

                    <div class="form-footer">
                        <button type="button" class="btn btn-secondary" onclick="resetProfileForm()">
                            <i class="fas fa-undo"></i>
                            Reset Changes
                        </button>
                        <button type="submit" class="btn btn-primary" id="saveProfileBtn">
                            <i class="fas fa-save"></i>
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>

            <!-- General Settings Section -->
            <div class="content-section" id="general-section">
                <div class="content-header">
                    <h2 class="content-title">
                        <i class="fas fa-cog"></i>
                        General Settings
                    </h2>
                    <p class="content-subtitle">Configure platform settings and preferences</p>
                </div>

                <form id="generalSettingsForm" method="POST">
                    <input type="hidden" name="action" value="update_system_settings">
                    <div class="form-group">
                        <label class="form-label" for="site_name">Site Name</label>
                        <input type="text" class="form-input" id="site_name" name="setting_site_name" value="<?php echo htmlspecialchars($system_settings['site_name'] ?? 'EduLearn Platform'); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="site_description">Site Description</label>
                        <textarea class="form-textarea" id="site_description" name="setting_site_description" rows="3"><?php echo htmlspecialchars($system_settings['site_description'] ?? 'Modern Educational Experience'); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="admin_email">Admin Email</label>
                        <input type="email" class="form-input" id="admin_email" name="setting_admin_email" value="<?php echo htmlspecialchars($system_settings['admin_email'] ?? 'admin@edulearn.com'); ?>" required>
                    </div>
                    
                    <div class="btn-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i>
                            Save General Settings
                        </button>
                    </div>
                </form>
            </div>

            <!-- Notifications Section -->
            <div class="content-section" id="notifications-section">
                <div class="content-header">
                    <h2 class="content-title">
                        <i class="fas fa-bell"></i>
                        Notification Settings
                    </h2>
                    <p class="content-subtitle">Manage email and system notifications</p>
                </div>
                
                <div class="toggle-group">
                    <div class="toggle-info">
                        <div class="toggle-title">Email Notifications</div>
                        <div class="toggle-description">Receive email notifications for important events</div>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" checked>
                        <span class="slider"></span>
                    </label>
                </div>
                
                <div class="toggle-group">
                    <div class="toggle-info">
                        <div class="toggle-title">Student Registration Alerts</div>
                        <div class="toggle-description">Get notified when new students register</div>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" checked>
                        <span class="slider"></span>
                    </label>
                </div>
                
                <div class="toggle-group">
                    <div class="toggle-info">
                        <div class="toggle-title">System Maintenance Alerts</div>
                        <div class="toggle-description">Receive alerts about system maintenance and updates</div>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox">
                        <span class="slider"></span>
                    </label>
                </div>
                
                <div class="btn-group">
                    <button class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        Save Notification Settings
                    </button>
                </div>
            </div>

            <!-- Security Section -->
            <div class="content-section" id="security-section">
                <div class="content-header">
                    <h2 class="content-title">
                        <i class="fas fa-lock"></i>
                        Security Settings
                    </h2>
                    <p class="content-subtitle">Configure security and access policies</p>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Password Policy</label>
                    <select class="form-select">
                        <option value="basic">Basic (6+ characters)</option>
                        <option value="medium" selected>Medium (8+ characters, mixed case)</option>
                        <option value="strong">Strong (12+ characters, mixed case, numbers, symbols)</option>
                    </select>
                </div>
                
                <div class="toggle-group">
                    <div class="toggle-info">
                        <div class="toggle-title">Two-Factor Authentication</div>
                        <div class="toggle-description">Require 2FA for admin accounts</div>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox">
                        <span class="slider"></span>
                    </label>
                </div>
                
                <div class="toggle-group">
                    <div class="toggle-info">
                        <div class="toggle-title">Session Timeout</div>
                        <div class="toggle-description">Automatically log out inactive users</div>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" checked>
                        <span class="slider"></span>
                    </label>
                </div>
                
                <div class="btn-group">
                    <button class="btn btn-warning">
                        <i class="fas fa-shield-alt"></i>
                        Update Security Settings
                    </button>
                </div>
            </div>

            <!-- Appearance Section -->
            <div class="content-section" id="appearance-section">
                <div class="content-header">
                    <h2 class="content-title">
                        <i class="fas fa-palette"></i>
                        Appearance Settings
                    </h2>
                    <p class="content-subtitle">Customize the look and feel of the platform</p>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Theme Mode</label>
                    <select class="form-select">
                        <option value="light" selected>Light Theme</option>
                        <option value="dark">Dark Theme</option>
                        <option value="auto">Auto (System Preference)</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Primary Color</label>
                    <div class="color-picker">
                        <div class="color-option active" data-color="#007BFF" style="background-color: #007BFF;"></div>
                        <div class="color-option" data-color="#28A745" style="background-color: #28A745;"></div>
                        <div class="color-option" data-color="#DC3545" style="background-color: #DC3545;"></div>
                        <div class="color-option" data-color="#FFC107" style="background-color: #FFC107;"></div>
                        <div class="color-option" data-color="#6F42C1" style="background-color: #6F42C1;"></div>
                        <div class="color-option" data-color="#FD7E14" style="background-color: #FD7E14;"></div>
                    </div>
                </div>
                
                <div class="toggle-group">
                    <div class="toggle-info">
                        <div class="toggle-title">Animations</div>
                        <div class="toggle-description">Enable smooth animations and transitions</div>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" checked>
                        <span class="slider"></span>
                    </label>
                </div>
                
                <div class="btn-group">
                    <button class="btn btn-primary">
                        <i class="fas fa-paint-brush"></i>
                        Apply Theme Changes
                    </button>
                </div>
            </div>

            <!-- Backup & Maintenance Section -->
            <div class="content-section" id="backup-section">
                <div class="content-header">
                    <h2 class="content-title">
                        <i class="fas fa-database"></i>
                        Backup & Maintenance
                    </h2>
                    <p class="content-subtitle">Manage system backups and maintenance tasks</p>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Automatic Backups</label>
                    <select class="form-select">
                        <option value="disabled">Disabled</option>
                        <option value="daily">Daily</option>
                        <option value="weekly" selected>Weekly</option>
                        <option value="monthly">Monthly</option>
                    </select>
                </div>
                
                <div class="toggle-group">
                    <div class="toggle-info">
                        <div class="toggle-title">Maintenance Mode</div>
                        <div class="toggle-description">Put the site in maintenance mode for updates</div>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox">
                        <span class="slider"></span>
                    </label>
                </div>
                
                <div class="btn-group">
                    <button class="btn btn-success">
                        <i class="fas fa-download"></i>
                        Create Backup Now
                    </button>
                    <button class="btn btn-warning">
                        <i class="fas fa-tools"></i>
                        Run Maintenance
                    </button>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Menu navigation
        document.querySelectorAll('.menu-item').forEach(item => {
            item.addEventListener('click', function() {
                // Remove active class from all menu items and sections
                document.querySelectorAll('.menu-item').forEach(i => i.classList.remove('active'));
                document.querySelectorAll('.content-section').forEach(s => s.classList.remove('active'));
                
                // Add active class to clicked item
                this.classList.add('active');
                
                // Show corresponding section
                const sectionId = this.dataset.section + '-section';
                document.getElementById(sectionId).classList.add('active');
            });
        });

        // Toggle password visibility
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const icon = document.querySelector('.toggle-password i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.className = 'fas fa-eye-slash';
            } else {
                passwordInput.type = 'password';
                icon.className = 'fas fa-eye';
            }
        }

        // Password strength checker
        const passwordInput = document.getElementById('password');
        const strengthMeter = document.getElementById('passwordStrengthMeter');
        
        if (passwordInput && strengthMeter) {
            passwordInput.addEventListener('input', function() {
                const password = this.value;
                let strength = 0;
                
                if (password === '') {
                    strengthMeter.className = '';
                    strengthMeter.style.width = '0';
                    return;
                }
                
                // Length check
                if (password.length >= 8) strength += 1;
                
                // Complexity checks
                if (password.match(/[a-z]/)) strength += 1;
                if (password.match(/[A-Z]/)) strength += 1;
                if (password.match(/[0-9]/)) strength += 1;
                if (password.match(/[^a-zA-Z0-9]/)) strength += 1;
                
                // Update meter
                strengthMeter.className = '';
                if (strength <= 2) {
                    strengthMeter.classList.add('weak');
                } else if (strength === 3) {
                    strengthMeter.classList.add('medium');
                } else if (strength === 4) {
                    strengthMeter.classList.add('strong');
                } else {
                    strengthMeter.classList.add('very-strong');
                }
            });
        }

        // Reset profile form
        function resetProfileForm() {
            document.getElementById('profileForm').reset();
            if (strengthMeter) {
                strengthMeter.className = '';
                strengthMeter.style.width = '0';
            }
        }

        // Color picker functionality
        document.querySelectorAll('.color-option').forEach(option => {
            option.addEventListener('click', function() {
                document.querySelectorAll('.color-option').forEach(o => o.classList.remove('active'));
                this.classList.add('active');
                
                const color = this.dataset.color;
                document.documentElement.style.setProperty('--primary-blue', color);
                showAlert('Primary color updated successfully!', 'success');
            });
        });

        // Form submissions with loading states
        document.getElementById('profileForm').addEventListener('submit', function(e) {
            const saveBtn = document.getElementById('saveProfileBtn');
            const originalText = saveBtn.innerHTML;
            saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
            saveBtn.disabled = true;
        });

        document.getElementById('generalSettingsForm').addEventListener('submit', function(e) {
            const saveBtn = this.querySelector('button[type="submit"]');
            const originalText = saveBtn.innerHTML;
            saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
            saveBtn.disabled = true;
        });

        // Toggle switches
        document.querySelectorAll('.toggle-switch input').forEach(toggle => {
            toggle.addEventListener('change', function() {
                const settingName = this.closest('.toggle-group').querySelector('.toggle-title').textContent;
                const status = this.checked ? 'enabled' : 'disabled';
                showAlert(`${settingName} ${status}!`, 'success');
            });
        });

        // Button click handlers for backup section
        document.querySelector('#backup-section .btn-success')?.addEventListener('click', function(e) {
            e.preventDefault();
            if (confirm('Are you sure you want to create a manual backup?')) {
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating Backup...';
                this.disabled = true;
                
                // Simulate backup process
                setTimeout(() => {
                    this.innerHTML = '<i class="fas fa-download"></i> Create Backup Now';
                    this.disabled = false;
                    showAlert('Backup created successfully!', 'success');
                }, 3000);
            }
        });

        document.querySelector('#backup-section .btn-warning')?.addEventListener('click', function(e) {
            e.preventDefault();
            if (confirm('Are you sure you want to run system maintenance? This may temporarily affect site performance.')) {
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Running Maintenance...';
                this.disabled = true;
                
                // Simulate maintenance process
                setTimeout(() => {
                    this.innerHTML = '<i class="fas fa-tools"></i> Run Maintenance';
                    this.disabled = false;
                    showAlert('System maintenance completed successfully!', 'success');
                }, 5000);
            }
        });

        function showAlert(message, type) {
            const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
            const icon = type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle';
            
            const alertHtml = `
                <div class="alert ${alertClass}">
                    <i class="${icon}"></i>
                    ${message}
                </div>
            `;
            
            // Remove any existing temporary alerts
            const existingTempAlerts = document.querySelectorAll('.alert.temp-alert');
            existingTempAlerts.forEach(alert => alert.remove());
            
            // Insert alert at the top of settings content
            const settingsContent = document.querySelector('.settings-content');
            const alertElement = document.createElement('div');
            alertElement.innerHTML = alertHtml;
            alertElement.firstElementChild.classList.add('temp-alert');
            settingsContent.insertBefore(alertElement.firstElementChild, settingsContent.firstChild);
            
            // Remove alert after 5 seconds
            setTimeout(() => {
                const tempAlert = document.querySelector('.alert.temp-alert');
                if (tempAlert) {
                    tempAlert.style.opacity = '0';
                    tempAlert.style.transform = 'translateY(-20px)';
                    setTimeout(() => tempAlert.remove(), 300);
                }
            }, 5000);
        }

        // Auto hide existing alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert:not(.temp-alert)');
            alerts.forEach(alert => {
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-20px)';
                setTimeout(() => alert.remove(), 300);
            });
        }, 5000);

        // Add smooth hover effects for interactive elements
        document.querySelectorAll('.btn').forEach(btn => {
            btn.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-2px)';
            });
            
            btn.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });
    </script>
</body>
</html>
