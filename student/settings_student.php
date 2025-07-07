<?php 
session_start();

// Include required classes
require_once 'classes/SecurityManager.php';
require_once 'classes/FormValidator.php';
require_once 'classes/UserSettings.php';
require_once 'classes/UIRenderer.php';

// Security check
SecurityManager::checkStudentAuth();
if (!SecurityManager::validateSession()) {
    header('Location: ../auth/login.php');
    exit;
}

include('../config/connectiondb.php'); 

// Initialize variables
$user_name = $_SESSION['user_fullname'] ?? 'Student';
$user_initials = SecurityManager::generateUserInitials($user_name);
$user_id = $_SESSION['user_id'];
$csrf_token = SecurityManager::generateCSRFToken();

// Initialize classes
$userSettings = new UserSettings($conn, $user_id);
$message = '';
$messageType = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Rate limiting check
    if (!SecurityManager::checkRateLimit($user_id)) {
        $message = 'Too many requests. Please wait before trying again.';
        $messageType = 'error';
    } elseif (!SecurityManager::validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $message = 'Invalid security token. Please refresh the page and try again.';
        $messageType = 'error';
        SecurityManager::logSecurityEvent($user_id, 'CSRF_TOKEN_INVALID');
    } else {
        // Sanitize input data
        $input_data = SecurityManager::sanitizeInput($_POST);
        
        // Check for suspicious activity
        if (SecurityManager::detectSuspiciousActivity($user_id, $input_data)) {
            $message = 'Suspicious activity detected. Request blocked.';
            $messageType = 'error';
        } else {
            $fullname = $input_data['fullname'] ?? '';
            $username = $input_data['username'] ?? '';
            $current_password = $_POST['current_password'] ?? ''; // Don't sanitize passwords
            $new_password = $_POST['new_password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';

            // Validate profile data
            $profileErrors = FormValidator::validateProfile([
                'fullname' => $fullname,
                'username' => $username
            ]);
            
            // Validate password data if password change is requested
            $passwordErrors = [];
            if (!empty($new_password)) {
                $passwordErrors = FormValidator::validatePassword([
                    'current_password' => $current_password,
                    'new_password' => $new_password,
                    'confirm_password' => $confirm_password
                ]);
            }
            
            $allErrors = array_merge($profileErrors, $passwordErrors);
            
            if (!empty($allErrors)) {
                $message = implode(' ', $allErrors);
                $messageType = 'error';
            } else {
                // Update profile
                $profileResult = $userSettings->updateProfile($fullname, $username);
                
                if ($profileResult['success']) {
                    $message = $profileResult['message'];
                    $messageType = 'success';
                    
                    // Handle password change if requested
                    if (!empty($new_password)) {
                        $passwordResult = $userSettings->changePassword($current_password, $new_password, $confirm_password);
                        
                        if ($passwordResult['success']) {
                            $message = 'Profile and password updated successfully!';
                            SecurityManager::logSecurityEvent($user_id, 'PASSWORD_CHANGED');
                        } else {
                            $message = $passwordResult['message'];
                            $messageType = 'error';
                        }
                    }
                    
                    SecurityManager::logSecurityEvent($user_id, 'PROFILE_UPDATED');
                } else {
                    $message = $profileResult['message'];
                    $messageType = 'error';
                }
            }
        }
    }
}

// Get current user data
$user_data = $userSettings->getUserData();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Account Settings | EduLearn</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
    .shape-4 { width: 180px; height: 180px; top: 30%; right: 30%; animation-delay: 3s; }

    @keyframes float-gentle {
      0%, 100% { transform: translateY(0px) rotate(0deg); }
      50% { transform: translateY(-20px) rotate(180deg); }
    }

    /* Header */
    .header {
      background: var(--white);
      backdrop-filter: blur(20px);
      box-shadow: var(--shadow-light);
      position: sticky;
      top: 0;
      z-index: 1000;
      border-bottom: 1px solid rgba(0, 123, 255, 0.1);
    }

    .container {
      max-width: 1400px;
      margin: 0 auto;
      padding: 0 24px;
    }

    .header-content {
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

    .back-btn {
      display: flex;
      align-items: center;
      gap: 8px;
      background: rgba(0, 123, 255, 0.1);
      color: var(--primary-blue);
      padding: 12px 20px;
      border-radius: 50px;
      text-decoration: none;
      font-weight: 500;
      font-size: 14px;
      transition: var(--transition);
      border: 2px solid transparent;
    }

    .back-btn:hover {
      background: var(--primary-blue);
      color: white;
      transform: translateY(-2px);
      box-shadow: var(--shadow-medium);
    }    /* Main Content */
    .main-content {
      padding: 40px 0;
      min-height: calc(100vh - 80px);
    }

    .settings-layout {
      display: grid;
      grid-template-columns: 300px 1fr;
      gap: 32px;
      max-width: 1200px;
      margin: 0 auto;
      animation: slideInUp 0.8s ease-out;
    }

    /* Sidebar */
    .settings-sidebar {
      background: var(--white);
      border-radius: var(--border-radius);
      box-shadow: var(--shadow-light);
      padding: 32px 0;
      height: fit-content;
      position: sticky;
      top: 120px;
    }

    .user-profile {
      text-align: center;
      padding: 0 32px 32px;
      border-bottom: 1px solid rgba(0, 123, 255, 0.1);
      margin-bottom: 24px;
    }

    .profile-avatar-sidebar {
      width: 80px;
      height: 80px;
      background: var(--gradient-primary);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 2rem;
      font-weight: 700;
      margin: 0 auto 16px;
      color: var(--white);
      animation: profilePulse 3s ease-in-out infinite;
    }

    .user-name {
      font-family: 'Poppins', sans-serif;
      font-size: 1.2rem;
      font-weight: 600;
      color: var(--text-dark);
      margin-bottom: 4px;
    }

    .user-email {
      font-size: 0.9rem;
      color: var(--text-light);
    }

    .sidebar-nav {
      list-style: none;
      padding: 0;
    }

    .nav-item {
      margin-bottom: 8px;
    }

    .nav-link {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 16px 32px;
      color: var(--text-light);
      text-decoration: none;
      font-weight: 500;
      font-size: 14px;
      transition: var(--transition);
      border-left: 3px solid transparent;
    }

    .nav-link:hover {
      background: rgba(0, 123, 255, 0.05);
      color: var(--primary-blue);
      border-left-color: var(--primary-blue);
    }

    .nav-link.active {
      background: rgba(0, 123, 255, 0.1);
      color: var(--primary-blue);
      border-left-color: var(--primary-blue);
    }

    .nav-link i {
      font-size: 16px;
      width: 20px;
      text-align: center;
    }

    /* Settings Content */
    .settings-container {
      background: var(--white);
      border-radius: var(--border-radius);
      box-shadow: var(--shadow-light);
      overflow: hidden;
    }

    .settings-header-main {
      padding: 32px;
      border-bottom: 1px solid rgba(0, 123, 255, 0.1);
    }

    .settings-title-main {
      font-family: 'Poppins', sans-serif;
      font-size: 1.8rem;
      font-weight: 700;
      color: var(--text-dark);
      margin-bottom: 8px;
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .settings-title-main i {
      color: var(--primary-blue);
      font-size: 1.5rem;
    }

    .settings-subtitle-main {
      color: var(--text-light);
      font-size: 1rem;
    }

    @keyframes slideInUp {
      from {
        opacity: 0;
        transform: translateY(50px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .settings-header {
      background: var(--gradient-primary);
      color: var(--white);
      padding: 32px;
      text-align: center;
      position: relative;
      overflow: hidden;
    }

    .settings-header::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="1" fill="rgba(255,255,255,0.1)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>') repeat;
      opacity: 0.1;
    }

    .profile-avatar {
      width: 80px;
      height: 80px;
      background: var(--gradient-accent);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 2rem;
      font-weight: 700;
      margin: 0 auto 16px;
      border: 4px solid rgba(255, 255, 255, 0.2);
      position: relative;
      z-index: 1;
      animation: profilePulse 3s ease-in-out infinite;
    }

    @keyframes profilePulse {
      0%, 100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.3); }
      50% { transform: scale(1.05); box-shadow: 0 0 0 10px rgba(255, 255, 255, 0); }
    }

    .settings-title {
      font-family: 'Poppins', sans-serif;
      font-size: 2rem;
      font-weight: 700;
      margin-bottom: 8px;
      position: relative;
      z-index: 1;
    }

    .settings-subtitle {
      font-size: 1rem;
      opacity: 0.9;
      position: relative;
      z-index: 1;
    }    .settings-body {
      padding: 32px;
    }

    /* Form Styles with cleaner layout */
    .form-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 24px;
    }

    .form-group.full-width {
      grid-column: 1 / -1;
    }

    .form-input-wrapper {
      position: relative;
    }

    .form-input {
      width: 100%;
      padding: 16px 20px;
      padding-right: 50px;
      border: 2px solid rgba(0, 123, 255, 0.1);
      border-radius: 12px;
      font-size: 14px;
      transition: var(--transition);
      background: var(--white);
      color: var(--text-dark);
      font-family: 'Inter', sans-serif;
    }

    .form-input-icon {
      position: absolute;
      right: 16px;
      top: 50%;
      transform: translateY(-50%);
      color: var(--text-light);
      font-size: 16px;
      pointer-events: none;
    }

    .section-divider {
      margin: 40px 0;
      padding: 0;
      border: none;
      border-top: 1px solid rgba(0, 123, 255, 0.1);
    }

    /* Action Buttons */
    .action-buttons {
      display: flex;
      gap: 16px;
      justify-content: flex-end;
      padding: 32px;
      border-top: 1px solid rgba(0, 123, 255, 0.1);
      background: rgba(0, 123, 255, 0.02);
    }

    .btn-secondary {
      padding: 12px 24px;
      background: transparent;
      color: var(--text-light);
      border: 2px solid rgba(0, 123, 255, 0.2);
      border-radius: 8px;
      font-size: 14px;
      font-weight: 500;
      cursor: pointer;
      transition: var(--transition);
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .btn-secondary:hover {
      background: rgba(0, 123, 255, 0.05);
      border-color: var(--primary-blue);
      color: var(--primary-blue);
    }

    .btn-primary {
      padding: 12px 24px;
      background: var(--gradient-primary);
      color: var(--white);
      border: none;
      border-radius: 8px;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      transition: var(--transition);
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: var(--shadow-medium);
    }

    /* Notifications */
    .notification {
      padding: 16px 24px;
      border-radius: 12px;
      margin-bottom: 24px;
      display: flex;
      align-items: center;
      gap: 12px;
      font-weight: 500;
      animation: slideInDown 0.5s ease-out;
    }

    @keyframes slideInDown {
      from {
        opacity: 0;
        transform: translateY(-20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .notification.success {
      background: rgba(40, 167, 69, 0.1);
      color: var(--success);
      border-left: 4px solid var(--success);
    }

    .notification.error {
      background: rgba(220, 53, 69, 0.1);
      color: var(--error);
      border-left: 4px solid var(--error);
    }

    /* Form Styles */
    .form-section {
      margin-bottom: 32px;
    }

    .section-title {
      font-family: 'Poppins', sans-serif;
      font-size: 1.2rem;
      font-weight: 600;
      color: var(--text-dark);
      margin-bottom: 20px;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .section-title i {
      color: var(--primary-blue);
    }

    .form-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
    }

    .form-group {
      margin-bottom: 24px;
    }

    .form-group.full-width {
      grid-column: 1 / -1;
    }

    .form-label {
      display: block;
      margin-bottom: 8px;
      font-weight: 500;
      color: var(--text-dark);
      font-size: 14px;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .form-label i {
      color: var(--primary-blue);
      font-size: 16px;
    }

    .form-input {
      width: 100%;
      padding: 16px 20px;
      border: 2px solid rgba(0, 123, 255, 0.1);
      border-radius: 12px;
      font-size: 14px;
      transition: var(--transition);
      background: var(--white);
      color: var(--text-dark);
      font-family: 'Inter', sans-serif;
    }

    .form-input:focus {
      outline: none;
      border-color: var(--primary-blue);
      box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
      transform: translateY(-2px);
    }

    .form-input:disabled {
      background: rgba(0, 123, 255, 0.05);
      color: var(--text-light);
      cursor: not-allowed;
    }

    .password-input-wrapper {
      position: relative;
    }

    .password-toggle {
      position: absolute;
      right: 16px;
      top: 50%;
      transform: translateY(-50%);
      background: none;
      border: none;
      color: var(--text-light);
      cursor: pointer;
      font-size: 16px;
      transition: var(--transition);
    }

    .password-toggle:hover {
      color: var(--primary-blue);
    }

    .password-strength {
      margin-top: 8px;
      height: 4px;
      background: #f0f0f0;
      border-radius: 2px;
      overflow: hidden;
      transition: var(--transition);
    }

    .password-strength.weak { background: linear-gradient(90deg, #dc3545 30%, #f0f0f0 30%); }
    .password-strength.medium { background: linear-gradient(90deg, #ffc107 60%, #f0f0f0 60%); }
    .password-strength.strong { background: linear-gradient(90deg, #28a745 90%, #f0f0f0 90%); }

    .btn-primary {
      width: 100%;
      padding: 16px 24px;
      background: var(--gradient-primary);
      color: var(--white);
      border: none;
      border-radius: 12px;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      transition: var(--transition);
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      font-family: 'Inter', sans-serif;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .btn-primary:hover {
      transform: translateY(-3px);
      box-shadow: var(--shadow-heavy);
    }

    .btn-primary:active {
      transform: translateY(-1px);
    }

    .btn-primary:disabled {
      opacity: 0.6;
      cursor: not-allowed;
      transform: none !important;
    }

    .divider {
      height: 1px;
      background: linear-gradient(90deg, transparent, rgba(0, 123, 255, 0.2), transparent);
      margin: 32px 0;
    }

    /* Security Section */
    .security-info {
      background: rgba(0, 123, 255, 0.05);
      border: 1px solid rgba(0, 123, 255, 0.1);
      border-radius: 12px;
      padding: 16px;
      margin-bottom: 20px;
    }

    .security-info h4 {
      color: var(--primary-blue);
      margin-bottom: 8px;
      font-size: 14px;
      font-weight: 600;
    }

    .security-info p {
      color: var(--text-light);
      font-size: 13px;
      line-height: 1.5;
    }

    /* Loading Animation */
    .loading {
      position: relative;
      overflow: hidden;
    }

    .loading::after {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
      animation: loading-sweep 1.5s infinite;
    }

    @keyframes loading-sweep {
      0% { left: -100%; }
      100% { left: 100%; }
    }    /* Responsive Design */
    @media (max-width: 768px) {
      .container {
        padding: 0 16px;
      }

      .settings-layout {
        grid-template-columns: 1fr;
        gap: 20px;
      }

      .settings-sidebar {
        position: relative;
        top: auto;
      }

      .user-profile {
        padding: 16px 24px 24px;
      }

      .profile-avatar-sidebar {
        width: 60px;
        height: 60px;
        font-size: 1.5rem;
      }

      .nav-link {
        padding: 12px 24px;
      }

      .settings-header-main {
        padding: 24px;
      }

      .settings-title-main {
        font-size: 1.5rem;
      }

      .settings-body {
        padding: 24px;
      }

      .form-grid {
        grid-template-columns: 1fr;
        gap: 16px;
      }

      .action-buttons {
        padding: 24px;
        flex-direction: column;
      }
    }

    @media (max-width: 480px) {
      .main-content {
        padding: 20px 0;
      }

      .settings-header-main {
        padding: 20px;
      }

      .settings-body {
        padding: 20px;
      }

      .action-buttons {
        padding: 20px;
      }
    }
  </style>
</head>
<body>
  <?php
  // Initialize UI Renderer
  $uiRenderer = new UIRenderer($user_data, $user_initials, $csrf_token);
  
  // Render animated background
  echo $uiRenderer->renderAnimatedBackground();
  
  // Render header
  echo $uiRenderer->renderHeader();
  ?>
  <!-- Main Content -->
  <main class="main-content">
    <div class="container">
      <?php
      // Initialize UI Renderer
      $uiRenderer = new UIRenderer($user_data, $user_initials, $csrf_token);
      
      // Render new layout
      echo $uiRenderer->renderNewLayout($message, $messageType);
      ?>
    </div>
  </main>  <script>
    // Enhanced JavaScript with tab navigation
    document.addEventListener('DOMContentLoaded', function() {
      const form = document.getElementById('settingsForm');
      const submitBtn = document.getElementById('submitBtn');
      const newPasswordInput = document.getElementById('new_password');
      const strengthIndicator = document.getElementById('passwordStrength');
      
      // Tab navigation
      const profileTab = document.getElementById('profileTab');
      const securityTab = document.getElementById('securityTab');
      const profileSection = document.getElementById('profileSection');
      const securitySection = document.getElementById('securitySection');
      
      // Tab switching
      if (profileTab && securityTab) {
        profileTab.addEventListener('click', function(e) {
          e.preventDefault();
          switchTab('profile');
        });
        
        securityTab.addEventListener('click', function(e) {
          e.preventDefault();
          switchTab('security');
        });
      }
      
      function switchTab(tab) {
        // Remove active class from all tabs
        document.querySelectorAll('.nav-link').forEach(link => {
          link.classList.remove('active');
        });
        
        // Hide all sections
        if (profileSection) profileSection.style.display = 'none';
        if (securitySection) securitySection.style.display = 'none';
        
        // Show selected tab and section
        if (tab === 'profile') {
          profileTab.classList.add('active');
          if (profileSection) profileSection.style.display = 'block';
          document.querySelector('.settings-title-main').innerHTML = '<i class="fas fa-user"></i> Profile Settings';
          document.querySelector('.settings-subtitle-main').textContent = 'Manage your personal information and account settings';
        } else if (tab === 'security') {
          securityTab.classList.add('active');
          if (securitySection) securitySection.style.display = 'block';
          document.querySelector('.settings-title-main').innerHTML = '<i class="fas fa-shield-alt"></i> Security Settings';
          document.querySelector('.settings-subtitle-main').textContent = 'Manage your password and security preferences';
        }
      }
      
      // Initialize with profile tab
      switchTab('profile');

      // Form validation with enhanced security
      form.addEventListener('submit', function(e) {
        const newPassword = newPasswordInput.value;
        const confirmPassword = document.getElementById('confirm_password').value;
        const currentPassword = document.getElementById('current_password').value;

        // Basic validation
        if (newPassword && !currentPassword) {
          e.preventDefault();
          Swal.fire({
            icon: 'warning',
            title: 'Current Password Required',
            text: 'Please enter your current password to change it.',
            confirmButtonColor: '#007BFF'
          });
          return;
        }

        if (newPassword && newPassword !== confirmPassword) {
          e.preventDefault();
          Swal.fire({
            icon: 'error',
            title: 'Password Mismatch',
            text: 'New passwords do not match.',
            confirmButtonColor: '#007BFF'
          });
          return;
        }

        // Enhanced password validation
        if (newPassword) {
          const passwordStrength = checkPasswordStrength(newPassword);
          if (passwordStrength < 3) {
            e.preventDefault();
            Swal.fire({
              icon: 'warning',
              title: 'Weak Password',
              text: 'Password must be stronger. Include uppercase, lowercase, numbers, and special characters.',
              confirmButtonColor: '#007BFF'
            });
            return;
          }
        }

        // Rate limiting check (client-side)
        const lastSubmit = localStorage.getItem('lastSettingsSubmit');
        const now = Date.now();
        if (lastSubmit && (now - parseInt(lastSubmit)) < 5000) {
          e.preventDefault();
          Swal.fire({
            icon: 'warning',
            title: 'Please Wait',
            text: 'Please wait a moment before submitting again.',
            confirmButtonColor: '#007BFF'
          });
          return;
        }

        // Store submission time
        localStorage.setItem('lastSettingsSubmit', now.toString());

        // Show loading state
        submitBtn.disabled = true;
        submitBtn.classList.add('loading');
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
      });

      // Enhanced username validation
      const usernameInput = document.getElementById('username');
      if (usernameInput) {
        usernameInput.addEventListener('input', function() {
          let value = this.value.toLowerCase();
          // Remove invalid characters
          value = value.replace(/[^a-z0-9_]/g, '');
          // Limit length
          if (value.length > 50) {
            value = value.substring(0, 50);
          }
          this.value = value;
          
          // Real-time validation feedback
          if (value.length < 3 && value.length > 0) {
            this.style.borderColor = '#DC3545';
          } else {
            this.style.borderColor = 'rgba(0, 123, 255, 0.1)';
          }
        });
      }

      // Password strength indicator
      if (newPasswordInput && strengthIndicator) {
        newPasswordInput.addEventListener('input', function() {
          const password = this.value;
          const strength = checkPasswordStrength(password);
          updatePasswordStrength(strength);
        });
      }

      // Auto-hide notifications with animation
      const notifications = document.querySelectorAll('.notification');
      notifications.forEach(notification => {
        setTimeout(() => {
          notification.style.animation = 'slideOutUp 0.5s ease-in forwards';
          setTimeout(() => {
            notification.remove();
          }, 500);
        }, 5000);
      });

      // Prevent form resubmission
      if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
      }
    });
    
    // Reset form function
    function resetForm() {
      Swal.fire({
        title: 'Reset Changes?',
        text: 'This will reset all unsaved changes.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#007BFF',
        cancelButtonColor: '#6C757D',
        confirmButtonText: 'Yes, reset'
      }).then((result) => {
        if (result.isConfirmed) {
          document.getElementById('settingsForm').reset();
          Swal.fire({
            title: 'Reset!',
            text: 'Your changes have been reset.',
            icon: 'success',
            confirmButtonColor: '#007BFF',
            timer: 1500,
            showConfirmButton: false
          });
        }
      });
    }

    // Enhanced password strength checker
    function checkPasswordStrength(password) {
      let strength = 0;
      const checks = [
        password.length >= 6,
        /[a-z]/.test(password),
        /[A-Z]/.test(password),
        /[0-9]/.test(password),
        /[^a-zA-Z0-9]/.test(password),
        password.length >= 10
      ];
      
      strength = checks.filter(Boolean).length;
      return strength;
    }

    // Update password strength visual indicator
    function updatePasswordStrength(strength) {
      const indicator = document.getElementById('passwordStrength');
      if (!indicator) return;

      indicator.className = 'password-strength';
      
      if (strength < 3) {
        indicator.classList.add('weak');
      } else if (strength < 5) {
        indicator.classList.add('medium');
      } else {
        indicator.classList.add('strong');
      }
    }

    // Enhanced password visibility toggle
    function togglePassword(fieldId) {
      const field = document.getElementById(fieldId);
      const toggle = field.nextElementSibling.querySelector('i');
      
      if (field.type === 'password') {
        field.type = 'text';
        toggle.classList.remove('fa-eye');
        toggle.classList.add('fa-eye-slash');
      } else {
        field.type = 'password';
        toggle.classList.remove('fa-eye-slash');
        toggle.classList.add('fa-eye');
      }
    }

    // Security: Clear sensitive data on page unload
    window.addEventListener('beforeunload', function() {
      const passwordFields = document.querySelectorAll('input[type="password"]');
      passwordFields.forEach(field => {
        field.value = '';
      });
    });

    // Add slideOutUp animation
    const style = document.createElement('style');
    style.textContent = `
      @keyframes slideOutUp {
        to {
          opacity: 0;
          transform: translateY(-20px);
        }
      }
    `;
    document.head.appendChild(style);

    // Enhanced security: Detect developer tools
    let devtools = {open: false, orientation: null};
    setInterval(function() {
      if (window.outerHeight - window.innerHeight > 160 || 
          window.outerWidth - window.innerWidth > 160) {
        if (!devtools.open) {
          devtools.open = true;
          console.warn('Security Notice: Developer tools detected. Please note that this session is monitored for security purposes.');
        }
      } else {
        devtools.open = false;
      }
    }, 500);
  </script>
</body>
</html>
