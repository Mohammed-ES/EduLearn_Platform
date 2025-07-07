<?php
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

$user_name = $_SESSION['user_fullname'] ?? 'Administrator';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - EduLearn</title>
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
        .shape-4 { width: 250px; height: 250px; top: 30%; right: 30%; animation-delay: 1.5s; background: var(--gradient-accent); }

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

        .nav-user {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .user-info {
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

        .user-details h4 {
            font-size: 14px;
            font-weight: 600;
            color: var(--text-dark);
        }

        .user-details p {
            font-size: 12px;
            color: var(--text-light);
        }

        .logout-btn {
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
            max-width: 1400px;
            margin: 0 auto;
            padding: 40px 24px;
        }

        /* Welcome Section */
        .welcome-section {
            background: var(--white);
            border-radius: var(--border-radius);
            padding: 40px;
            margin-bottom: 40px;
            box-shadow: var(--shadow-medium);
            position: relative;
            overflow: hidden;
        }

        .welcome-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient-primary);
        }

        .welcome-content {
            text-align: center;
        }

        .welcome-title {
            font-family: 'Poppins', sans-serif;
            font-size: 2.5rem;
            font-weight: 700;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 12px;
        }

        .welcome-subtitle {
            font-size: 1.1rem;
            color: var(--text-light);
            margin-bottom: 24px;
        }

        .current-time {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(0, 123, 255, 0.1);
            padding: 8px 16px;
            border-radius: 50px;
            color: var(--primary-blue);
            font-weight: 500;
            font-size: 14px;
        }

        /* Success Message */
        .success-message {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            color: var(--success);
            padding: 16px 24px;
            border-radius: var(--border-radius);
            margin-bottom: 24px;
            border-left: 4px solid var(--success);
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 500;
            box-shadow: var(--shadow-light);
            animation: slideInDown 0.6s ease;
        }

        @keyframes slideInDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 24px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: var(--white);
            border-radius: var(--border-radius);
            padding: 32px;
            box-shadow: var(--shadow-medium);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(0, 123, 255, 0.1);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--gradient-primary);
            transform: scaleX(0);
            transition: var(--transition);
        }

        .stat-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-heavy);
        }

        .stat-card:hover::before {
            transform: scaleX(1);
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
        }

        .stat-icon {
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
            color: var(--success);
            font-weight: 500;
        }

        .stat-number {
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

        /* Quick Actions */
        .quick-actions {
            background: var(--white);
            border-radius: var(--border-radius);
            padding: 32px;
            box-shadow: var(--shadow-medium);
            margin-bottom: 40px;
        }

        .section-title {
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
        }

        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
        }

        .action-card {
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
        }

        .action-title {
            font-weight: 600;
            font-size: 16px;
            margin-bottom: 8px;
        }

        .action-description {
            font-size: 14px;
            color: var(--text-light);
            line-height: 1.4;
        }

        /* Recent Activity */
        .recent-activity {
            background: var(--white);
            border-radius: var(--border-radius);
            padding: 32px;
            box-shadow: var(--shadow-medium);
        }

        .activity-item {
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
        }

        .activity-icon {
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
        }

        .activity-title {
            font-weight: 500;
            margin-bottom: 4px;
        }

        .activity-time {
            font-size: 12px;
            color: var(--text-light);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .nav-container {
                padding: 0 16px;
                height: 70px;
            }

            .logo {
                font-size: 1.5rem;
            }

            .user-details {
                display: none;
            }

            .main-content {
                padding: 24px 16px;
            }

            .welcome-section {
                padding: 24px;
            }

            .welcome-title {
                font-size: 2rem;
            }

            .stats-grid,
            .actions-grid {
                grid-template-columns: 1fr;
                gap: 16px;
            }

            .stat-card,
            .quick-actions,
            .recent-activity {
                padding: 24px;
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
        <div class="floating-shape shape-4"></div>
    </div>

    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="../index.php" class="logo">
                <i class="fas fa-graduation-cap"></i>
                EduLearn
            </a>
            <div class="nav-user">
                <div class="user-info">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($user_name, 0, 2)); ?>
                    </div>
                    <div class="user-details">
                        <h4><?php echo htmlspecialchars($user_name); ?></h4>
                        <p>Administrator</p>
                    </div>
                </div>
                <a href="../auth/logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Success Message -->
        <?php if (isset($_SESSION['success_message'])): ?>
        <div class="success-message" data-aos="fade-down">
            <i class="fas fa-check-circle"></i>
            <?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?>
        </div>
        <?php endif; ?>

        <!-- Welcome Section -->
        <section class="welcome-section" data-aos="fade-up">
            <div class="welcome-content">
                <h1 class="welcome-title">Welcome Back, <?php echo htmlspecialchars(explode(' ', $user_name)[0]); ?>!</h1>
                <p class="welcome-subtitle">Manage your educational platform with advanced administrative tools</p>
                <div class="current-time">
                    <i class="fas fa-clock"></i>
                    <span id="current-time"></span>
                </div>
            </div>
        </section>

        <!-- Statistics Grid -->
        <section class="stats-grid">
            <div class="stat-card" data-aos="fade-up" data-aos-delay="100">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-trend">
                        <i class="fas fa-arrow-up"></i>
                        +12%
                    </div>
                </div>
                <div class="stat-number" id="total-users"><div class="loading"></div></div>
                <div class="stat-label">Total Users</div>
            </div>

            <div class="stat-card" data-aos="fade-up" data-aos-delay="200">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <div class="stat-trend">
                        <i class="fas fa-arrow-up"></i>
                        +8%
                    </div>
                </div>
                <div class="stat-number" id="active-students"><div class="loading"></div></div>
                <div class="stat-label">Active Students</div>
            </div>

            <div class="stat-card" data-aos="fade-up" data-aos-delay="300">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-bullhorn"></i>
                    </div>
                    <div class="stat-trend">
                        <i class="fas fa-arrow-up"></i>
                        +5%
                    </div>
                </div>
                <div class="stat-number" id="announcements"><div class="loading"></div></div>
                <div class="stat-label">Announcements</div>
            </div>

            <div class="stat-card" data-aos="fade-up" data-aos-delay="400">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stat-trend">
                        <i class="fas fa-arrow-up"></i>
                        +15%
                    </div>
                </div>
                <div class="stat-number" id="active-sessions"><div class="loading"></div></div>
                <div class="stat-label">Active Sessions</div>
            </div>
        </section>        <!-- Quick Actions -->
        <section class="quick-actions" data-aos="fade-up" data-aos-delay="500">
            <h2 class="section-title">
                <i class="fas fa-bolt"></i>
                Quick Actions
            </h2>            <div class="actions-grid">
                <a href="manage_students.php" class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <div class="action-title">Manage Students</div>
                    <div class="action-description">View and manage student accounts</div>
                </a>

                <a href="manage_announcements.php" class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-bullhorn"></i>
                    </div>
                    <div class="action-title">Announcements</div>
                    <div class="action-description">Create and manage platform announcements</div>
                </a>

                <a href="settings_admin.php" class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-cog"></i>
                    </div>
                    <div class="action-title">System Settings</div>
                    <div class="action-description">Configure platform settings and preferences</div>
                </a>

                <a href="backup.php" class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-database"></i>
                    </div>
                    <div class="action-title">Data Backup</div>
                    <div class="action-description">Backup and restore system data</div>
                </a>
            </div>
        </section>

        <!-- Recent Activity -->
        <section class="recent-activity" data-aos="fade-up" data-aos-delay="600">
            <h2 class="section-title">
                <i class="fas fa-history"></i>
                Recent Activity
            </h2>
            <div class="activity-item">
                <div class="activity-icon">
                    <i class="fas fa-user-plus"></i>
                </div>
                <div class="activity-content">
                    <div class="activity-title">New administrator account created</div>
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
                    <i class="fas fa-cog"></i>
                </div>
                <div class="activity-content">
                    <div class="activity-title">Platform settings updated</div>
                    <div class="activity-time">1 day ago</div>
                </div>
            </div>
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
        });

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

        // Load statistics (simulated)
        function loadStatistics() {
            setTimeout(() => {
                document.getElementById('total-users').textContent = '1,247';
                document.getElementById('active-students').textContent = '1,089';
                document.getElementById('announcements').textContent = '23';
                document.getElementById('active-sessions').textContent = '156';
            }, 1500);
        }

        // Initialize
        updateTime();
        setInterval(updateTime, 60000); // Update every minute
        loadStatistics();

        // Add smooth scroll behavior
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });

        // Add interactive hover effects
        document.querySelectorAll('.stat-card, .action-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-8px) scale(1.02)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });        });
    </script>
</body>
</html>
