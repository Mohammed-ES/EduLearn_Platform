<?php
session_start();

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
    header('Location: ../auth/login.php');
    exit;
}

$user_name = $_SESSION['user_fullname'] ?? 'Student';
$user_initials = strtoupper(substr($user_name, 0, 2));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - EduLearn</title>
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
        }        /* Header */
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
        }.logo {
            display: flex;
            align-items: center;
            gap: 12px;
            font-family: 'Poppins', sans-serif;
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--dark-blue);
            text-decoration: none;
            transition: var(--transition);
        }.logo i {
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
        }        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px 16px;
            background: rgba(0, 123, 255, 0.05);
            border-radius: 50px;
            transition: var(--transition);
        }

        .user-avatar {
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

        .user-details {
            display: flex;
            flex-direction: column;
        }

        .user-details .user-name {
            font-size: 14px;
            font-weight: 600;
            color: var(--text-dark);
        }

        .user-details .user-role {
            font-size: 12px;
            color: var(--text-light);
        }        .logout-btn {
            background: var(--gradient-primary);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: var(--shadow-light);
        }

        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-medium);
            color: white;
        }

        /* Main Content */
        .main-content {
            padding: 32px 0;
        }

        /* Success Message */
        .success-banner {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            color: var(--success);
            padding: 16px 20px;
            border-radius: var(--border-radius);
            margin-bottom: 32px;
            border-left: 4px solid var(--success);
            display: flex;
            align-items: center;
            gap: 12px;
            animation: slideIn 0.6s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }        /* Quick Actions Section */
        .section {
            margin-bottom: 40px;
        }

        .quick-actions {
            background: var(--white);
            border-radius: var(--border-radius);
            padding: 32px;
            box-shadow: var(--shadow-medium);
            margin-bottom: 40px;
        }.section-title {
            font-family: 'Poppins', sans-serif;
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .section-title i {
            color: var(--primary-blue);
        }.actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
        }.action-card {
            background: var(--white);
            border: 2px solid rgba(0, 123, 255, 0.1);
            border-radius: var(--border-radius);
            padding: 24px;
            text-decoration: none;
            color: var(--text-dark);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
            group: hover;
        }

        .action-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
            transition: var(--transition);
        }

        .action-card:hover {
            border-color: var(--primary-blue);
            transform: translateY(-4px);
            box-shadow: var(--shadow-medium);
            color: var(--text-dark);
        }

        .action-card:hover::before {
            left: 100%;
        }

        .action-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            background: var(--gradient-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
            margin-bottom: 16px;
            transition: var(--transition);
        }

        .action-card:hover .action-icon {
            transform: scale(1.1) rotate(5deg);
        }        .action-title {
            font-weight: 600;
            font-size: 16px;
            margin-bottom: 8px;
        }

        .action-description {
            font-size: 14px;
            color: var(--text-light);
            line-height: 1.4;
        }

        /* Welcome Section */
        .welcome-section {
            background: var(--white);
            border-radius: var(--border-radius);
            padding: 40px;
            margin-bottom: 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
            box-shadow: var(--shadow-light);
        }

        .welcome-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(212, 175, 55, 0.1), transparent);
            animation: shimmer 3s ease-in-out infinite;
        }

        @keyframes shimmer {
            0% { left: -100%; }
            100% { left: 100%; }
        }        .welcome-title {
            font-family: 'Poppins', sans-serif;
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-blue);
            margin-bottom: 16px;
            position: relative;
            z-index: 1;
        }

        .welcome-subtitle {
            color: var(--text-light);
            font-size: 18px;
            max-width: 600px;
            margin: 0 auto 24px;
            position: relative;
            z-index: 1;
        }

        .current-time {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(0, 123, 255, 0.1);
            color: var(--primary-blue);
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
            position: relative;
            z-index: 1;
        }

        /* Stats Section */        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 24px;
            margin-bottom: 40px;
        }.stat-card {
            background: var(--white);
            border-radius: var(--border-radius);
            padding: 32px;
            box-shadow: var(--shadow-medium);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(0, 123, 255, 0.1);
        }.stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background: var(--gradient-primary);
            transform: scaleX(0);
            transition: var(--transition);
        }        .stat-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-heavy);
        }

        .stat-card:hover::before {
            transform: scaleX(1);
        }        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
        }.stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            background: var(--gradient-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            box-shadow: var(--shadow-light);
        }

        .stat-trend {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 12px;
            font-weight: 500;
            color: var(--success);
        }        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 8px;
            font-family: 'Poppins', sans-serif;
        }

        .stat-label {
            color: var(--text-light);
            font-weight: 500;
            font-size: 14px;
        }

        /* Recent Activity */        .recent-activity {
            background: var(--white);
            border-radius: var(--border-radius);
            padding: 32px;
            box-shadow: var(--shadow-medium);
        }        .activity-item {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 16px 0;
            border-bottom: 1px solid rgba(0, 123, 255, 0.1);
            transition: var(--transition);
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-item:hover {
            background: rgba(0, 123, 255, 0.03);
            border-radius: 8px;
            margin: 0 -16px;
            padding: 16px 16px;
        }        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            background: var(--gradient-accent);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 16px;
        }

        .activity-content {
            flex: 1;
        }        .activity-title {
            font-weight: 500;
            margin-bottom: 4px;
        }

        .activity-time {
            font-size: 12px;
            color: var(--text-light);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                padding: 0 16px;
            }

            .header-content {
                flex-direction: column;
                gap: 16px;
                text-align: center;
            }

            .user-info {
                flex-direction: column;
                gap: 12px;
            }

            .actions-grid {
                grid-template-columns: 1fr;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .welcome-title {
                font-size: 28px;
            }

            .welcome-subtitle {
                font-size: 16px;
            }
        }        /* Loading Animation */
        .loading {
            opacity: 0;
            animation: fadeIn 0.6s ease-out forwards;
        }

        @keyframes fadeIn {
            to {
                opacity: 1;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="header-content">
                <a href="#" class="logo">
                    <i class="fas fa-graduation-cap"></i>
                    EduLearn
                </a>
                <div class="user-info">
                    <div class="user-avatar">
                        <?php echo $user_initials; ?>
                    </div>
                    <div class="user-details">
                        <div class="user-name"><?php echo htmlspecialchars($user_name); ?></div>
                        <div class="user-role">Student</div>
                    </div>
                    <a href="../auth/logout.php" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i>
                        Logout
                    </a>
                </div>
            </div>
        </div>
    </header>

    <main class="main-content">
        <div class="container">
            <?php if (isset($_SESSION['success_message'])): ?>
            <div class="success-banner">
                <i class="fas fa-check-circle"></i>
                <span><?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?></span>
            </div>
            <?php endif; ?>            <!-- Welcome Section -->
            <div class="welcome-section loading">
                <h1 class="welcome-title">Welcome Back, Student!</h1>
                <p class="welcome-subtitle">Manage your learning journey with advanced educational tools and track your academic progress</p>
                <div class="current-time">
                    <i class="fas fa-clock"></i>
                    <span id="current-time"></span>
                </div>
            </div>

            <!-- Statistics -->
            <div class="stats-grid">                <div class="stat-card loading">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="fas fa-book-open"></i>
                        </div>
                        <div class="stat-trend">
                            <i class="fas fa-arrow-up"></i>
                            +12%
                        </div>
                    </div>
                    <div class="stat-number">12</div>
                    <div class="stat-label">Active Courses</div>
                </div>
                  <div class="stat-card loading">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="fas fa-tasks"></i>
                        </div>
                        <div class="stat-trend">
                            <i class="fas fa-arrow-up"></i>
                            +8%
                        </div>
                    </div>
                    <div class="stat-number">5</div>
                    <div class="stat-label">Pending Tasks</div>
                </div>
                  <div class="stat-card loading">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="fas fa-bullhorn"></i>
                        </div>
                        <div class="stat-trend">
                            <i class="fas fa-arrow-up"></i>
                            +5%
                        </div>
                    </div>
                    <div class="stat-number">23</div>
                    <div class="stat-label">Announcements</div>
                </div>
                  <div class="stat-card loading">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="stat-trend">
                            <i class="fas fa-arrow-up"></i>
                            +15%
                        </div>
                    </div>
                    <div class="stat-number">75%</div>
                    <div class="stat-label">Progress</div>
                </div>
            </div>            <!-- Quick Actions -->
            <section class="quick-actions">
                <h2 class="section-title">
                    <i class="fas fa-bolt"></i>
                    Quick Actions
                </h2>
                <div class="actions-grid">
                    <div class="action-card featured loading" onclick="location.href='notes_improved.php';">
                        <div class="action-icon">
                            <i class="fas fa-sticky-note"></i>
                        </div>
                        <h3 class="action-title">Personal Notes</h3>
                        <p class="action-description">Create and manage your study notes with advanced organization tools</p>
                    </div>
                    
                    <div class="action-card secondary loading" onclick="location.href='announcements_student.php';">
                        <div class="action-icon">
                            <i class="fas fa-bullhorn"></i>
                        </div>
                        <h3 class="action-title">Announcements</h3>
                        <p class="action-description">Stay updated with the latest announcements and important notifications</p>
                    </div>
                    
                    <div class="action-card tertiary loading" onclick="location.href='quiz.php';">
                        <div class="action-icon">
                            <i class="fas fa-question-circle"></i>
                        </div>
                        <h3 class="action-title">Interactive Quizzes</h3>
                        <p class="action-description">Test your knowledge with engaging quizzes and track your progress</p>
                    </div>
                      <div class="action-card quaternary loading" onclick="location.href='ai_assistant.php';">
                        <div class="action-icon">
                            <i class="fas fa-robot"></i>
                        </div>
                        <h3 class="action-title">AI Study Assistant</h3>
                        <p class="action-description">Get personalized help and study guidance from our intelligent AI</p>
                    </div>
                    
                    <div class="action-card settings loading" onclick="location.href='settings_student.php';">
                        <div class="action-icon">
                            <i class="fas fa-cog"></i>
                        </div>
                        <h3 class="action-title">Settings</h3>
                        <p class="action-description">Customize your account preferences and system configurations</p>
                    </div>
                    
                    <div class="action-card plannings loading" onclick="location.href='planning.php';">
                        <div class="action-icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <h3 class="action-title">Plannings</h3>
                        <p class="action-description">Manage your study schedule and view upcoming events</p>
                    </div>
                </div>
            </section><!-- Recent Activity -->
            <section class="recent-activity">
                <h2 class="section-title">
                    <i class="fas fa-history"></i>
                    Recent Activity
                </h2>
                <div class="activity-item">
                        <div class="activity-icon">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <div class="activity-content">
                            <div class="activity-title">New study session started</div>
                            <div class="activity-time">2 hours ago</div>
                        </div>
                    </div>
                    
                    <div class="activity-item">
                        <div class="activity-icon">
                            <i class="fas fa-bullhorn"></i>
                        </div>
                        <div class="activity-content">
                            <div class="activity-title">System announcement published</div>
                            <div class="activity-time">5 hours ago</div>
                        </div>
                    </div>
                      <div class="activity-item">
                        <div class="activity-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="activity-content">
                            <div class="activity-title">Quiz completed successfully</div>
                            <div class="activity-time">1 day ago</div>
                        </div>
                    </div>
            </section>
        </div>
    </main>

    <script>
        // Update current time
        function updateTime() {
            const now = new Date();
            const options = { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            };
            document.getElementById('current-time').textContent = now.toLocaleDateString('en-US', options);
        }

        // Initialize time and update every minute
        updateTime();
        setInterval(updateTime, 60000);

        // Add click animations to cards
        document.querySelectorAll('.action-card, .stat-card').forEach(card => {
            card.addEventListener('mousedown', function() {
                this.style.transform = 'scale(0.95)';
            });
            
            card.addEventListener('mouseup', function() {
                this.style.transform = '';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = '';
            });
        });

        // Smooth loading animation
        window.addEventListener('load', function() {
            document.querySelectorAll('.loading').forEach((element, index) => {
                setTimeout(() => {
                    element.style.opacity = '1';
                }, index * 100);
            });
        });

        // Add hover sound effect (optional)
        document.querySelectorAll('.action-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                // Optional: Add subtle sound effect
                // new Audio('hover-sound.mp3').play().catch(() => {});
            });
        });
    </script>
</body>
</html>
