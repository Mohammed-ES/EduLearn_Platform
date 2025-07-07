<?php
session_start();

// Check if user is logged in and is an admin
$is_admin = isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduLearn - Sign Up</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
            --border-radius: 12px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
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

        /* Background Animation */
        .animated-background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            overflow: hidden;
        }

        .shape {
            position: absolute;
            border-radius: 50%;
            background: var(--gradient-primary);
            opacity: 0.1;
            animation: float 6s ease-in-out infinite;
        }

        .shape-1 {
            width: 200px;
            height: 200px;
            top: 10%;
            left: 10%;
            animation-delay: 0s;
        }

        .shape-2 {
            width: 300px;
            height: 300px;
            top: 60%;
            right: 10%;
            animation-delay: 2s;
            background: var(--gradient-accent);
        }

        .shape-3 {
            width: 150px;
            height: 150px;
            bottom: 20%;
            left: 20%;
            animation-delay: 4s;
        }

        .shape-4 {
            width: 100px;
            height: 100px;
            top: 30%;
            right: 30%;
            animation-delay: 1s;
            background: var(--gradient-accent);
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }

        /* Auth Page Layout */
        .auth-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            z-index: 1;
        }

        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
        }

        .auth-wrapper {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0;
            background: var(--white);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: var(--shadow-heavy);
            min-height: 600px;
        }

        /* Form Container */
        .auth-form-container {
            padding: 60px 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
        }

        .auth-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .auth-header h1 {
            font-family: 'Poppins', sans-serif;
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--dark-blue);
            margin-bottom: 10px;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .auth-header p {
            color: var(--text-light);
            font-size: 16px;
            font-weight: 400;
        }

        /* Form Styles */
        .auth-form {
            width: 100%;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--dark-blue);
            font-size: 14px;
        }

        .input-group {
            position: relative;
        }

        .input-group i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--blue-gray);
            font-size: 16px;
            z-index: 2;
        }

        .input-group input, .input-group select {
            width: 100%;
            padding: 15px 15px 15px 45px;
            border: 2px solid #E8F4FD;
            border-radius: var(--border-radius);
            font-size: 16px;
            font-family: 'Inter', sans-serif;
            background: #FAFBFC;
            transition: var(--transition);
            outline: none;
        }

        .input-group input:focus, .input-group select:focus {
            border-color: var(--primary-blue);
            background: var(--white);
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
        }

        .input-group input::placeholder {
            color: var(--text-light);
        }

        .toggle-password {
            position: absolute;
            right: 15px;
            left: auto !important;
            cursor: pointer;
            color: var(--blue-gray);
            transition: var(--transition);
        }

        .toggle-password:hover {
            color: var(--primary-blue);
        }

        /* Buttons */
        .btn-register, .btn-login {
            width: 100%;
            padding: 16px 24px;
            background: var(--gradient-primary);
            color: var(--white);
            border: none;
            border-radius: var(--border-radius);
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: var(--shadow-light);
            font-family: 'Inter', sans-serif;
            text-decoration: none;
        }

        .btn-register:hover, .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-medium);
            color: var(--white);
        }

        .btn-register:active, .btn-login:active {
            transform: translateY(0);
        }

        /* Auth Footer */
        .auth-footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #E8F4FD;
        }

        .auth-footer p {
            color: var(--text-light);
            font-size: 14px;
        }

        .login-link {
            color: var(--primary-blue);
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
        }

        .login-link:hover {
            color: var(--dark-blue);
            text-decoration: underline;
        }

        /* Side Image */
        .auth-side-image {
            background: var(--gradient-primary);
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
            overflow: hidden;
        }

        /* Graduation Cap Animation - Simple Modern Effect */
        .graduation-cap {
            font-size: 80px;
            margin-bottom: 20px;
            color: #D4AF37;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-block;
            text-decoration: none;
            animation: float-modern 3s ease-in-out infinite;
            position: relative;
            z-index: 3;
        }

        .graduation-cap:hover {
            color: #F4E4A6;
            transform: translateY(-8px) scale(1.1);
            text-shadow: 0 8px 25px rgba(212, 175, 55, 0.4);
            filter: drop-shadow(0 8px 15px rgba(212, 175, 55, 0.3));
        }

        .graduation-cap:active {
            transform: translateY(-4px) scale(1.05);
        }

        /* Simple modern floating animation */
        @keyframes float-modern {
            0%, 100% { 
                transform: translateY(0px);
            }
            50% { 
                transform: translateY(-10px);
            }
        }

        /* Logo Text Animations */
        .logo-text {
            font-family: 'Poppins', sans-serif;
            margin-bottom: 10px;
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
            z-index: 3;
            color: white;
        }

        .logo-text:hover {
            text-shadow: 0 0 15px rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }

        .logo-subtitle {
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
            z-index: 3;
            color: white;
        }

        .logo-subtitle:hover {
            text-shadow: 0 0 10px rgba(255, 255, 255, 0.2);
        }

        .image-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(15, 76, 117, 0.1);
            z-index: 1;
        }

        .feature-list {
            position: absolute;
            bottom: 40px;
            left: 40px;
            right: 40px;
            color: var(--white);
            z-index: 3;
        }

        .feature-list h3 {
            font-family: 'Poppins', sans-serif;
            font-size: 1.5rem;
            margin-bottom: 20px;
            font-weight: 600;
        }

        .feature-list ul {
            list-style: none;
        }

        .feature-list li {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
            font-size: 14px;
            font-weight: 500;
        }

        .feature-list i {
            color: var(--gold-accent);
            font-size: 16px;
        }

        /* Messages */
        .error-message, .success-message {
            padding: 15px 20px;
            border-radius: var(--border-radius);
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 500;
            animation: slideInDown 0.5s ease;
        }

        .error-message {
            background: #FFF5F5;
            color: var(--error);
            border: 1px solid #FED7D7;
        }

        .success-message {
            background: #F0FFF4;
            color: var(--success);
            border: 1px solid #C6F6D5;
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

        /* Responsive Design */
        @media (max-width: 768px) {
            .auth-wrapper {
                grid-template-columns: 1fr;
                margin: 10px;
            }

            .auth-form-container {
                padding: 40px 30px;
            }

            .auth-header h1 {
                font-size: 2rem;
            }

            .form-row {
                grid-template-columns: 1fr;
                gap: 0;
            }

            .auth-side-image {
                order: -1;
                min-height: 200px;
            }

            .feature-list {
                display: none;
            }

            .shape {
                display: none;
            }
        }

        @media (max-width: 480px) {
            .auth-page {
                padding: 10px;
            }

            .auth-form-container {
                padding: 30px 20px;
            }

            .auth-header h1 {
                font-size: 1.75rem;
            }
        }
    </style>
</head>
<body>
    <!-- Sign up page -->
    <div class="auth-page">
        <!-- Animated Background -->
        <div class="animated-background">
            <div class="shape shape-1"></div>
            <div class="shape shape-2"></div>
            <div class="shape shape-3"></div>
            <div class="shape shape-4"></div>
        </div>        <div class="container">
            <div class="auth-wrapper">
                <div class="auth-form-container" data-aos="fade-right" data-aos-duration="1000">                    <div class="auth-header">
                        <h1>Create New Account</h1>
                        <p>Add a new user to the learning platform</p>
                    </div>
                    
                    <!-- Error/Success messages -->
                    <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="error-message" data-aos="fade-in">
                        <i class="fas fa-exclamation-circle"></i>
                        <span><?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="success-message" data-aos="fade-in">
                        <i class="fas fa-check-circle"></i>
                        <span><?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <form class="auth-form" method="POST" action="process_register.php">
                        <div class="form-group">
                            <label for="fullname">Full Name</label>
                            <div class="input-group">
                                <i class="fas fa-user"></i>
                                <input type="text" id="fullname" name="fullname" placeholder="Enter full name" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <div class="input-group">
                                <i class="fas fa-envelope"></i>
                                <input type="email" id="email" name="email" placeholder="Enter email address" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="password">Password</label>
                                <div class="input-group">
                                    <i class="fas fa-lock"></i>
                                    <input type="password" id="password" name="password" placeholder="Enter password" required>
                                    <i class="fas fa-eye toggle-password"></i>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="confirm_password">Confirm Password</label>
                                <div class="input-group">
                                    <i class="fas fa-lock"></i>
                                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm password" required>
                                    <i class="fas fa-eye toggle-password"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-row">                            <div class="form-group">
                                <label for="user_role">User Role</label>
                                <div class="input-group">
                                    <i class="fas fa-user-tag"></i>
                                    <select id="user_role" name="user_role" required>
                                        <option value="">Select role</option>
                                        <option value="admin">Administrator</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="cohort">Cohort (Optional)</label>
                                <div class="input-group">
                                    <i class="fas fa-users"></i>
                                    <input type="text" id="cohort" name="cohort" placeholder="Enter cohort/class">
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">Phone Number (Optional)</label>
                            <div class="input-group">
                                <i class="fas fa-phone"></i>
                                <input type="tel" id="phone" name="phone" placeholder="Enter phone number">
                            </div>
                        </div>
                        
                        <button type="submit" class="btn-register">
                            <span>Create Account</span>
                            <i class="fas fa-user-plus"></i>
                        </button>
                          <div class="auth-footer">
                            <p><a href="login.php" class="login-link">Back to Login</a></p>
                        </div>
                    </form>
                </div>
                
                <div class="auth-side-image" data-aos="fade-left" data-aos-duration="1000">
                    <div style="text-align: center; color: white;">
                        <a href="../index.php" class="graduation-cap">
                            <i class="fas fa-graduation-cap"></i>
                        </a>
                        <h2 class="logo-text" onclick="window.location.href='../index.php'">EduLearn</h2>
                        <p class="logo-subtitle" onclick="window.location.href='../index.php'">Your modern learning platform</p>
                    </div>
                    <div class="image-overlay"></div>
                    
                    <div class="feature-list">
                        <h3>Why join us?</h3>
                        <ul>
                            <li><i class="fas fa-check-circle"></i> Personalized learning experience</li>
                            <li><i class="fas fa-check-circle"></i> Interactive courses and materials</li>
                            <li><i class="fas fa-check-circle"></i> Collaborate with other students</li>
                            <li><i class="fas fa-check-circle"></i> Track your progress easily</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        // Initialize AOS animations
        AOS.init({
            once: true
        });
        
        // Toggle password visibility for all password fields
        document.querySelectorAll('.toggle-password').forEach(toggle => {
            toggle.addEventListener('click', function() {
                const passwordInput = this.parentElement.querySelector('input');
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                this.classList.toggle('fa-eye');
                this.classList.toggle('fa-eye-slash');
            });
        });

        // Form submission handling
        document.querySelector('.auth-form')?.addEventListener('submit', function(e) {
            const button = document.querySelector('.btn-register');
            if (button) {
                button.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Creating Account...';
                button.disabled = true;
            }
        });

        // Graduation cap now uses natural link navigation without JavaScript interference
    </script>
</body>
</html>
