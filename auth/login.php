<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduLearn - Sign In</title>
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
        }        .back-home {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--blue-gray);
            text-decoration: none;
            font-size: 14px;
            margin-bottom: 30px;
            transition: var(--transition);
            font-weight: 500;
            position: relative;
            overflow: hidden;
        }

        .back-home:hover {
            color: var(--primary-blue);
            transform: translateX(-3px);
            text-shadow: 0 2px 8px rgba(0, 123, 255, 0.2);
        }

        .back-home:hover i {
            transform: translateX(-2px) scale(1.2);
            color: var(--primary-blue);
        }

        .back-home::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: radial-gradient(circle, rgba(0, 123, 255, 0.1) 0%, transparent 70%);
            border-radius: 50%;
            transition: width 0.4s ease, height 0.4s ease;
            transform: translate(-50%, -50%);
            z-index: -1;
        }

        .back-home:hover::before {
            width: 120px;
            height: 120px;
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

        .input-group input {
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

        .input-group input:focus {
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

        /* Password Strength Meter */
        .password-strength {
            margin-top: 10px;
        }

        .strength-meter {
            display: flex;
            gap: 4px;
            margin-bottom: 5px;
        }

        .strength-segment {
            height: 4px;
            flex: 1;
            background: #E8F4FD;
            border-radius: 2px;
            transition: var(--transition);
        }

        .strength-segment.active:nth-child(1) { background: var(--error); }
        .strength-segment.active:nth-child(2) { background: var(--warning); }
        .strength-segment.active:nth-child(3) { background: var(--primary-blue); }
        .strength-segment.active:nth-child(4) { background: var(--success); }

        .strength-text {
            font-size: 12px;
            color: var(--text-light);
            font-weight: 500;
        }

        /* Form Options */
        .form-options {
            margin: 20px 0 30px;
        }

        .remember-me, .terms-check {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .remember-me input, .terms-check input {
            width: 18px;
            height: 18px;
            accent-color: var(--primary-blue);
        }

        .remember-me label, .terms-check label {
            font-size: 14px;
            color: var(--text-light);
            margin: 0;
        }

        .terms-check a {
            color: var(--primary-blue);
            text-decoration: none;
            font-weight: 500;
        }

        .terms-check a:hover {
            text-decoration: underline;
        }

        /* Buttons */
        .btn-login, .btn-register {
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
        }

        .btn-login:hover, .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-medium);
        }

        .btn-login:active, .btn-register:active {
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

        .login-link, .register-link {
            color: var(--primary-blue);
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
        }

        .login-link:hover, .register-link:hover {
            color: var(--dark-blue);
            text-decoration: underline;
        }        /* Side Image */
        .auth-side-image {
            background: var(--gradient-primary);
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
            overflow: hidden;
        }        /* Graduation Cap Animation - Simple Modern Effect */
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
        }        /* Logo Text Animations */
        .logo-text {
            font-family: 'Poppins', sans-serif;
            margin-bottom: 10px;
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
            z-index: 3;
        }

        .logo-text:hover {
            text-shadow: 0 0 15px rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }        .logo-subtitle {
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
            z-index: 3;
        }

        .logo-subtitle:hover {
            text-shadow: 0 0 10px rgba(255, 255, 255, 0.2);
        }

        .auth-side-image img {
            max-width: 100%;
            height: auto;
            z-index: 2;
            position: relative;
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

        /* Focus States for Accessibility */
        .input-group input:focus,
        .btn-login:focus,
        .btn-register:focus,
        .back-home:focus {
            outline: 2px solid var(--primary-blue);
            outline-offset: 2px;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: var(--light-bg);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--blue-gray);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--primary-blue);
        }
    </style>
</head>
<body>    <!-- Login page -->
    <div class="auth-page login-section">
        <!-- Animated Background -->
        <div class="animated-background">
            <div class="shape shape-1"></div>
            <div class="shape shape-2"></div>
            <div class="shape shape-3"></div>
            <div class="shape shape-4"></div>
        </div>

        <div class="container">
            <div class="auth-wrapper">
                <div class="auth-side-image" data-aos="fade-right" data-aos-duration="1000">
                    <div style="text-align: center; color: white;">
                        <a href="../index.php" class="graduation-cap">
                            <i class="fas fa-graduation-cap"></i>
                        </a>
                        <h2 class="logo-text" onclick="window.location.href='../index.php'">EduLearn</h2>
                        <p class="logo-subtitle" onclick="window.location.href='../index.php'">Your modern learning platform</p>
                    </div>
                    <div class="image-overlay"></div>
                </div>
                
                <div class="auth-form-container" data-aos="fade-left" data-aos-duration="1000">                    <div class="auth-header">
                        <h1>Welcome Back</h1>
                        <p>Sign in to continue your learning journey</p>
                    </div>                      <!-- Error/Success messages -->
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
                    
                    <form class="auth-form" method="POST" action="process_login.php">                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <div class="input-group">
                                <i class="fas fa-envelope"></i>
                                <input type="email" id="email" name="email" placeholder="Enter your email" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="password">Password</label>
                            <div class="input-group">
                                <i class="fas fa-lock"></i>
                                <input type="password" id="password" name="password" placeholder="Enter your password" required>
                                <i class="fas fa-eye toggle-password"></i>
                            </div>
                        </div>
                          <div class="form-options">
                            <div class="remember-me">
                                <input type="checkbox" id="remember" name="remember">
                                <label for="remember">Remember me</label>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn-login">
                            <span>Sign In</span>
                            <i class="fas fa-arrow-right"></i>
                        </button>                        <div class="auth-footer">
                            <p>Don't have an account? <a href="register.php" class="register-link">Sign Up</a></p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        // Initialize AOS animations
        AOS.init({
            once: true
        });
        
        // Toggle password visibility
        document.querySelector('.toggle-password').addEventListener('click', function() {
            const passwordInput = document.querySelector('#password');
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
        
        // Add floating label effect
        document.querySelectorAll('.auth-form input').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('focused');
            });
            
            input.addEventListener('blur', function() {
                if (this.value === '') {
                    this.parentElement.classList.remove('focused');
                }
            });
            
            // Check on load if input has value
            if (input.value !== '') {
                input.parentElement.classList.add('focused');
            }
        });        // Form submission handling
        document.querySelector('.auth-form').addEventListener('submit', function(e) {
            const button = document.querySelector('.btn-login');
            button.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Signing in...';
            button.disabled = true;
        });// Graduation cap now uses natural link navigation without JavaScript interference
    </script>
    </script>
</body>
</html>