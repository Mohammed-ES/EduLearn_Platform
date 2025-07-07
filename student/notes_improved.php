<?php 
session_start();

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
    header('Location: ../auth/login.php');
    exit;
}

include('../config/connectiondb.php'); 
require_once('classes/NotesManager.php');

$user_name = $_SESSION['user_fullname'] ?? 'Student';
$user_initials = strtoupper(substr($user_name, 0, 2));

// Auto-setup database tables if they don't exist
try {
    // Check if note_modules table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'note_modules'");
    if ($stmt->rowCount() == 0) {
        // Create tables automatically
        $sql_modules = "CREATE TABLE IF NOT EXISTS `note_modules` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `student_id` int(11) NOT NULL,
            `name` varchar(255) NOT NULL,
            `description` text DEFAULT NULL,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_student_id` (`student_id`),
            KEY `idx_name` (`name`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $sql_notes = "CREATE TABLE IF NOT EXISTS `notes` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `module_id` int(11) NOT NULL,
            `student_id` int(11) NOT NULL,
            `title` varchar(500) NOT NULL,
            `content` longtext NOT NULL,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_module_id` (`module_id`),
            KEY `idx_student_id` (`student_id`),
            KEY `idx_title` (`title`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $pdo->exec($sql_modules);
        $pdo->exec($sql_notes);
    }
} catch (Exception $e) {
    error_log("Notes table setup error: " . $e->getMessage());
}

// Initialize NotesManager
$notesManager = new NotesManager($pdo, $_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Personal Notes | EduLearn</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>  <style>        :root {
            --primary-blue: #007BFF;
            --gold-accent: #D4AF37;
            --dark-blue: #0F4C75;
            --blue-gray: #6C757D;
            --light-bg: #F8FAFC;
            --white: #FFFFFF;
            --text-dark: #1A202C;
            --text-light: #718096;
            --text-secondary: #A0AEC0;
            --border-light: rgba(0, 123, 255, 0.1);
            --success: #28A745;
            --error: #DC3545;
            --warning: #FFC107;
            --shadow-light: 0 4px 6px rgba(0, 0, 0, 0.05);
            --shadow-medium: 0 10px 25px rgba(0, 0, 0, 0.1);
            --shadow-heavy: 0 20px 40px rgba(0, 0, 0, 0.1);
            --gradient-primary: linear-gradient(135deg, var(--primary-blue) 0%, var(--dark-blue) 100%);
            --gradient-accent: linear-gradient(135deg, var(--gold-accent) 0%, #B8941F 100%);
            --border-radius: 20px;
            --border-radius-small: 12px;
            --transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }* {
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
            background-image: 
                radial-gradient(circle at 20% 80%, rgba(0, 123, 255, 0.03) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(212, 175, 55, 0.03) 0%, transparent 50%);
        }

        /* Animated Background - EduLearn Style */
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
            animation: float 15s infinite ease-in-out;
        }

        .shape-1 {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary-blue), rgba(0, 123, 255, 0.3));
            top: 20%;
            left: 10%;
            animation-delay: 0s;
        }

        .shape-2 {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--gold-accent), rgba(212, 175, 55, 0.3));
            top: 60%;
            right: 15%;
            animation-delay: -5s;
        }

        .shape-3 {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--primary-blue), rgba(0, 123, 255, 0.2));
            bottom: 30%;
            left: 60%;
            animation-delay: -10s;
        }        @keyframes float {
            0%, 100% {
                transform: translateY(0px) rotate(0deg);
                opacity: 0.7;
            }
            33% {
                transform: translateY(-30px) rotate(120deg);
                opacity: 1;
            }
            66% {
                transform: translateY(30px) rotate(240deg);
                opacity: 0.4;
            }
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

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 80px;
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 24px;
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
        }        .back-btn {
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

        .back-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-medium);
            color: white;
        }        /* Main Content */
        .main-content {
            padding: 32px 0;
        }

        /* Main Container */
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 32px 24px;
        }        /* Hero Section */
        .hero-section {
            background: linear-gradient(135deg, 
                rgba(255, 255, 255, 0.95) 0%, 
                rgba(248, 250, 252, 0.98) 50%, 
                rgba(245, 248, 250, 0.95) 100%);
            border-radius: var(--border-radius);
            padding: 80px 40px;
            text-align: center;
            box-shadow: 
                var(--shadow-light),
                inset 0 1px 0 rgba(255, 255, 255, 0.8);
            margin-bottom: 40px;
            position: relative;
            overflow: hidden;
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 30% 20%, rgba(0, 123, 255, 0.08) 0%, transparent 50%),
                radial-gradient(circle at 70% 80%, rgba(212, 175, 55, 0.08) 0%, transparent 50%);
            z-index: -1;
        }

        .hero-section::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, 
                var(--primary-blue) 0%, 
                var(--gold-accent) 50%, 
                var(--primary-blue) 100%);
            background-size: 200% 100%;
            animation: gradientShift 3s ease-in-out infinite;
        }

        @keyframes gradientShift {
            0%, 100% {
                background-position: 0% 0%;
            }
            50% {
                background-position: 100% 0%;
            }
        }.hero-title {
            font-family: 'Poppins', sans-serif;
            font-size: 3.5rem;
            font-weight: 700;
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--dark-blue) 50%, var(--gold-accent) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 24px;
            line-height: 1.2;
            position: relative;
            text-align: center;
            animation: titleGlow 3s ease-in-out infinite alternate;
        }

        .hero-title::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, var(--primary-blue), var(--gold-accent));
            border-radius: 12px;
            opacity: 0.1;
            z-index: -1;
            transform: scale(1.05);
            filter: blur(20px);
        }

        .hero-title::after {
            content: '📝';
            position: absolute;
            top: -10px;
            left: -60px;
            font-size: 2.5rem;
            animation: bounce 2s ease-in-out infinite;
        }

        @keyframes titleGlow {
            0% {
                filter: brightness(1) drop-shadow(0 0 10px rgba(0, 123, 255, 0.3));
            }
            100% {
                filter: brightness(1.1) drop-shadow(0 0 20px rgba(212, 175, 55, 0.4));
            }
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-10px);
            }
            60% {
                transform: translateY(-5px);
            }
        }        .hero-subtitle {
            font-size: 1.25rem;
            color: var(--text-light);
            line-height: 1.6;
            max-width: 650px;
            margin: 0 auto;
            font-weight: 400;
            text-shadow: 0 1px 2px rgba(255, 255, 255, 0.8);
            opacity: 0.9;
        }

        .hero-section {
            animation: heroFadeIn 1s ease-out;
        }

        @keyframes heroFadeIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Controls Section */
        .controls {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border-radius: var(--border-radius);
            padding: 32px;
            box-shadow: var(--shadow-light);
            margin-bottom: 32px;
            display: flex;
            gap: 24px;
            flex-wrap: wrap;
            align-items: center;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .btn {
            padding: 14px 28px;
            border: none;
            border-radius: var(--border-radius-small);
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.5s ease;
        }

        .btn:hover::before {
            left: 100%;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-blue), var(--dark-blue));
            color: white;
            box-shadow: var(--shadow-light);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-medium);
        }

        .btn-gold {
            background: linear-gradient(135deg, var(--gold-accent), var(--gold-accent-dark));
            color: white;
            box-shadow: var(--shadow-light);
        }

        .btn-gold:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-medium);
        }

        .search-container {
            position: relative;
            flex: 1;
            max-width: 400px;
        }

        .search-input {
            width: 100%;
            padding: 14px 50px 14px 20px;
            border: 2px solid var(--border-light);
            border-radius: var(--border-radius-small);
            font-size: 14px;
            transition: var(--transition);
            background: white;
        }        .search-input:focus {
            outline: none;
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
        }        .search-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
        }/* Content */
        .content {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            border-radius: var(--border-radius);
            padding: 40px;
            box-shadow: var(--shadow-light);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .btn-primary {
            background: var(--gradient-primary);
            color: white;
            border: none;
            padding: 16px 32px;
            border-radius: var(--border-radius-small);
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: var(--shadow-light);
            position: relative;
            overflow: hidden;
            text-decoration: none;
        }        

        .btn-secondary {
            background: var(--gradient-accent);
            color: white;
            border: none;
            padding: 16px 32px;
            border-radius: var(--border-radius-small);
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: var(--shadow-light);
            position: relative;
            overflow: hidden;
            text-decoration: none;
        }

        .btn-primary::before, .btn-secondary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: var(--transition);
        }

        .btn-primary:hover::before, .btn-secondary:hover::before {
            left: 100%;
        }

    .btn-primary:hover, .btn-secondary:hover {
      transform: translateY(-3px);
      box-shadow: var(--shadow-heavy);
    }

    .btn-primary:active, .btn-secondary:active {
      transform: translateY(-1px);
    }

    .search-container {
      flex: 1;
      position: relative;
      max-width: 400px;
    }    .search-input {
      width: 100%;
      padding: 16px 20px 16px 50px;
      border: 2px solid rgba(0, 123, 255, 0.1);
      border-radius: var(--border-radius-small);
      font-size: 14px;
      background: var(--white);
      transition: var(--transition);
      outline: none;
    }

    .search-input:focus {
      border-color: var(--primary-blue);
      box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
    }

    .search-icon {
      position: absolute;
      left: 18px;
      top: 50%;
      transform: translateY(-50%);
      color: var(--text-light);
      font-size: 18px;
    }        /* Modules Grid */
        .modules-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
            gap: 30px;
            margin-top: 30px;
        }

        .module-card {
            border: 1px solid var(--border-light);
            border-radius: var(--border-radius);
            overflow: hidden;
            transition: var(--transition);
            background: white;
            position: relative;
        }

        .module-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-heavy);
        }

        .module-header {
            background: linear-gradient(135deg, var(--primary-blue), var(--dark-blue));
            color: white;
            padding: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
        }        .module-header::before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--gold-accent);
        }

        .module-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient-primary);
            transform: scaleX(0);
            transition: var(--transition);
        }

    .module-card:hover {
      transform: translateY(-8px);
      box-shadow: var(--shadow-heavy);
    }

    .module-card:hover::before {
      transform: scaleX(1);
    }

    .module-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
    }

    .module-title {
      font-family: 'Poppins', sans-serif;
      font-size: 1.4rem;
      font-weight: 600;
      color: var(--text-dark);
    }

    .module-actions {
      display: flex;
      gap: 8px;
    }

    .btn-icon {
      width: 40px;
      height: 40px;
      border: none;
      border-radius: 50%;
      cursor: pointer;
      transition: var(--transition);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 16px;
    }

    .btn-add {
      background: rgba(0, 123, 255, 0.1);
      color: var(--primary-blue);
    }

    .btn-add:hover {
      background: var(--primary-blue);
      color: white;
      transform: scale(1.1);
    }

    .btn-export {
      background: rgba(212, 175, 55, 0.1);
      color: var(--gold-accent);
    }

    .btn-export:hover {
      background: var(--gold-accent);
      color: white;
      transform: scale(1.1);
    }

    .btn-delete {
      background: rgba(220, 53, 69, 0.1);
      color: var(--error);
    }

    .btn-delete:hover {
      background: var(--error);
      color: white;
      transform: scale(1.1);
    }

    .notes-list {
      max-height: 300px;
      overflow-y: auto;
      margin-top: 16px;
    }

    .note-item {
      background: rgba(0, 123, 255, 0.05);
      border-radius: 12px;
      padding: 16px;
      margin-bottom: 12px;
      border-left: 4px solid var(--primary-blue);
      transition: var(--transition);
      cursor: pointer;
    }

    .note-item:hover {
      background: rgba(0, 123, 255, 0.1);
      transform: translateX(4px);
    }

    .note-title {
      font-weight: 600;
      color: var(--text-dark);
      margin-bottom: 8px;
    }        .note-preview {
            color: var(--text-light);
            font-size: 14px;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

    .note-actions {
      display: flex;
      gap: 8px;
      margin-top: 12px;
    }

    .btn-small {
      padding: 6px 12px;
      border: none;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 500;
      cursor: pointer;
      transition: var(--transition);
    }

    .btn-edit {
      background: rgba(212, 175, 55, 0.1);
      color: var(--gold-accent);
    }

    .btn-edit:hover {
      background: var(--gold-accent);
      color: white;
    }

    .btn-small.btn-export {
      background: rgba(40, 167, 69, 0.1);
      color: var(--success);
    }

    .btn-small.btn-export:hover {
      background: var(--success);
      color: white;
    }

    .btn-remove {
      background: rgba(220, 53, 69, 0.1);
      color: var(--error);
    }

    .btn-remove:hover {
      background: var(--error);
      color: white;
    }

    /* Modal Styles */
    .modal {
      display: none;
      position: fixed;
      z-index: 2000;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.5);
      backdrop-filter: blur(5px);
      animation: fadeIn 0.3s ease;
    }

    .modal-content {
      background: var(--white);
      margin: 5% auto;
      padding: 32px;
      border-radius: var(--border-radius);
      width: 90%;
      max-width: 600px;
      box-shadow: var(--shadow-heavy);
      position: relative;
      animation: slideInUp 0.4s ease;
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

    .close-btn {
      position: absolute;
      top: 16px;
      right: 20px;
      font-size: 24px;
      cursor: pointer;
      color: var(--text-light);
      transition: var(--transition);
    }

    .close-btn:hover {
      color: var(--error);
      transform: scale(1.1);
    }

    .modal h2 {
      font-family: 'Poppins', sans-serif;
      font-size: 1.8rem;
      font-weight: 600;
      color: var(--text-dark);
      margin-bottom: 24px;
    }

    .form-group {
      margin-bottom: 20px;
    }

    .form-input, .form-textarea {
      width: 100%;
      padding: 16px 20px;
      border: 2px solid rgba(0, 123, 255, 0.1);
      border-radius: 12px;
      font-size: 16px;
      transition: var(--transition);
      outline: none;
      font-family: 'Inter', sans-serif;
    }

    .form-textarea {
      min-height: 120px;
      resize: vertical;
    }

    .form-input:focus, .form-textarea:focus {
      border-color: var(--primary-blue);
      box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
    }

    /* Export Options */
    .export-options-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 20px;
      margin-top: 20px;
    }

    .export-option {
      background: rgba(0, 123, 255, 0.05);
      border: 2px solid rgba(0, 123, 255, 0.1);
      border-radius: 12px;
      padding: 24px;
      text-align: center;
      cursor: pointer;
      transition: var(--transition);
    }

    .export-option:hover {
      background: rgba(0, 123, 255, 0.1);
      border-color: var(--primary-blue);
      transform: translateY(-2px);
      box-shadow: var(--shadow-medium);
    }

    .export-option i {
      font-size: 2.5rem;
      color: var(--primary-blue);
      margin-bottom: 12px;
    }

    .export-option h3 {
      font-size: 1.2rem;
      font-weight: 600;
      color: var(--text-dark);
      margin-bottom: 8px;
    }

    .export-option p {
      color: var(--text-light);
      font-size: 14px;
      line-height: 1.4;
    }

    /* Loading State */
    .loading {
      opacity: 0.6;
      pointer-events: none;
    }

    .loading::after {
      content: '';
      position: absolute;
      top: 50%;
      left: 50%;
      width: 20px;
      height: 20px;
      margin: -10px 0 0 -10px;
      border: 2px solid #f3f3f3;
      border-top: 2px solid var(--primary-blue);
      border-radius: 50%;
      animation: spin 1s linear infinite;
    }

    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }

    /* Animations */
    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    @keyframes slideInDown {
      from {
        opacity: 0;
        transform: translateY(-30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }

    /* Responsive Design */
    @media (max-width: 768px) {
      .container {
        padding: 0 16px;
      }

      .page-title {
        font-size: 2.2rem;
      }

      .notes-toolbar {
        flex-direction: column;
        align-items: stretch;
      }

      .search-container {
        max-width: none;
      }

      .modules-grid {
        grid-template-columns: 1fr;
      }

      .module-header {
        flex-direction: column;
        gap: 12px;
        align-items: flex-start;
      }
    }

    /* Empty State */
    .empty-state {
      text-align: center;
      padding: 60px 20px;
      color: var(--text-light);
    }

    .empty-state i {
      font-size: 4rem;
      margin-bottom: 20px;
      color: var(--blue-gray);
    }

    .empty-state h3 {
      font-size: 1.5rem;
      margin-bottom: 12px;
      color: var(--text-dark);
    }
  </style>
</head>
<body>
  <div class="animated-background">
    <div class="floating-shape shape-1"></div>
    <div class="floating-shape shape-2"></div>
    <div class="floating-shape shape-3"></div>
  </div>  <!-- Header -->
  <header class="header">
    <div class="header-content">
      <a href="student_dashboard.php" class="logo">
        <i class="fas fa-graduation-cap"></i>
        EduLearn
      </a>
      
      <a href="student_dashboard.php" class="back-btn">
        <i class="fas fa-arrow-left"></i>
        Back to Dashboard
      </a>
    </div>
  </header>

  <!-- Main Content -->
  <main class="main-content">
    <div class="container">    <!-- Hero Section -->
    <div class="hero-section">
      <h1 class="hero-title">My Personal Notes</h1>
      <p class="hero-subtitle">
        Organize your thoughts, ideas, and study materials with our advanced note-taking system.
      </p>
    </div>    <!-- Controls -->
    <div class="controls">
      <button class="btn btn-primary" onclick="openAddModuleModal()">
        <i class="fas fa-plus"></i>
        Add Module
      </button>
      <div class="search-container">
        <input type="text" class="search-input" placeholder="Search files and modules..." id="searchInput">
        <i class="fas fa-search search-icon"></i>
      </div>      
      <button class="btn btn-secondary" onclick="showExportOptions()">
        <i class="fas fa-download"></i>
        Export Notes
      </button>
    </div><!-- Content -->
    <div class="content">
      <div id="modulesList" class="modules-grid"></div>
    </div>
    </div>
  </main>

  <!-- Modal for Adding Module -->
  <div id="addModuleModal" class="modal">
    <div class="modal-content">
      <span class="close-btn" onclick="closeAddModuleModal()">&times;</span>
      <h2><i class="fas fa-folder-plus" style="color: var(--primary-blue); margin-right: 12px;"></i>Add New Module</h2>
      <div class="form-group">
        <input type="text" class="form-input" id="moduleName" placeholder="Enter module name..." />
      </div>
      <button class="btn-primary" onclick="addModule()">
        <i class="fas fa-plus"></i>
        Create Module
      </button>
    </div>
  </div>

  <!-- Modal for Adding/Editing Note -->
  <div id="noteModal" class="modal">
    <div class="modal-content">
      <span class="close-btn" onclick="closeNoteModal()">&times;</span>
      <h2 id="noteModalTitle">
        <i class="fas fa-sticky-note" style="color: var(--primary-blue); margin-right: 12px;"></i>
        Add Note
      </h2>
      <div class="form-group">
        <input type="text" class="form-input" id="noteTitle" placeholder="Note title...">
      </div>
      <div class="form-group">
        <textarea class="form-textarea" id="noteContent" placeholder="Write your note content here..."></textarea>
      </div>
      <button class="btn-primary" onclick="saveNote()">
        <i class="fas fa-save"></i>
        Save Note
      </button>
    </div>
  </div>

  <!-- Export Options Modal -->
  <div id="exportModal" class="modal">
    <div class="modal-content">
      <span class="close-btn" onclick="closeExportModal()">&times;</span>
      <h2><i class="fas fa-download" style="color: var(--gold-accent); margin-right: 12px;"></i>Export Options</h2>
      <div class="export-options-grid">
        <div class="export-option" onclick="exportAllNotes('txt')">
          <i class="fas fa-file-alt"></i>
          <h3>All Notes (TXT)</h3>
          <p>Export all your notes as a text file</p>
        </div>
      </div>
    </div>
  </div>

  <script>
    // Global variables
    let currentModule = null;
    let currentNote = null;
    let modules = [];
    let isLoading = false;

    // Initialize the application
    document.addEventListener('DOMContentLoaded', function() {
      loadModules();
      setupSearchFunctionality();
      
      // Add smooth loading animation
      setTimeout(() => {
        document.body.style.opacity = '1';
      }, 100);
    });

    // API Helper Functions
    async function apiRequest(url, options = {}) {
      if (isLoading) return;
      
      try {
        isLoading = true;
        const response = await fetch(url, {
          ...options,
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            ...options.headers
          }
        });
        
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        return data;
      } catch (error) {
        console.error('API Error:', error);
        Swal.fire({
          icon: 'error',
          title: 'Connection Error',
          text: 'Failed to connect to server. Please try again.',
          confirmButtonColor: '#007BFF'
        });
        return { success: false, message: error.message };
      } finally {
        isLoading = false;
      }
    }    // Load modules from server
    async function loadModules() {
      console.log('Loading modules...');
      
      const formData = new FormData();
      formData.append('action', 'get_modules');
      
      const result = await apiRequest('notes_api.php', {
        method: 'POST',
        body: formData
      });
      
      console.log('API Result:', result);
      console.log('Result type:', typeof result);
      console.log('Result success:', result?.success);
      console.log('Result modules:', result?.modules);
      
      if (result && result.success) {
        modules = result.modules || [];
        console.log('Loaded modules:', modules);
        console.log('Modules count:', modules.length);
        renderModules();
      } else {
        console.error('Failed to load modules:', result);
        console.log('Showing error dialog...');
        
        // Show user-friendly error message
        Swal.fire({
          icon: 'warning',
          title: 'No Modules Found',
          html: `
            <p>You don't have any modules yet, or there was an error loading them.</p>
            <br>
            <p>Would you like to create your first module?</p>
            <div style="display: flex; gap: 10px; justify-content: center; margin-top: 15px;">
              <button onclick="createFirstModule()" style="background: #28A745; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer;">
                Create First Module
              </button>
            </div>
          `,
          showConfirmButton: false,
          allowOutsideClick: true
        });
        
        // Show empty state
        modules = [];
        renderModules();
      }
    }

    // Render modules in the grid
    function renderModules() {
      const modulesList = document.getElementById('modulesList');
      
      if (modules.length === 0) {
        modulesList.innerHTML = `
          <div class="empty-state">
            <i class="fas fa-folder-open"></i>
            <h3>No modules yet</h3>
            <p>Create your first module to start organizing your notes</p>
          </div>
        `;
        return;
      }

      modulesList.innerHTML = modules.map(module => `
        <div class="module-card" data-module-id="${module.id}">
          <div class="module-header">
            <h3 class="module-title">
              <i class="fas fa-folder" style="color: var(--primary-blue); margin-right: 8px;"></i>
              ${escapeHtml(module.name)}
            </h3>
            <div class="module-actions">
              <button class="btn-icon btn-add" onclick="openAddNoteModal(${module.id})" title="Add Note">
                <i class="fas fa-plus"></i>
              </button>
              <button class="btn-icon btn-export" onclick="exportModule(${module.id})" title="Export Module">
                <i class="fas fa-download"></i>
              </button>
              <button class="btn-icon btn-delete" onclick="deleteModule(${module.id})" title="Delete Module">
                <i class="fas fa-trash"></i>
              </button>
            </div>
          </div>
          <div class="notes-count">
            <i class="fas fa-sticky-note" style="color: var(--text-light); margin-right: 4px;"></i>
            ${module.notes.length} note${module.notes.length !== 1 ? 's' : ''}
          </div>
          <div class="notes-list">
            ${module.notes.map(note => `
              <div class="note-item" onclick="viewNote(${module.id}, ${note.id})">
                <div class="note-title">${escapeHtml(note.title)}</div>
                <div class="note-preview">${escapeHtml(note.content.substring(0, 100))}${note.content.length > 100 ? '...' : ''}</div>
                <div class="note-actions" onclick="event.stopPropagation()">
                  <button class="btn-small btn-edit" onclick="editNote(${module.id}, ${note.id})">
                    <i class="fas fa-edit"></i> Edit
                  </button>
                  <button class="btn-small btn-export" onclick="exportNote(${note.id})">
                    <i class="fas fa-download"></i> Export
                  </button>
                  <button class="btn-small btn-remove" onclick="deleteNote(${module.id}, ${note.id})">
                    <i class="fas fa-trash"></i> Delete
                  </button>
                </div>
              </div>
            `).join('')}
          </div>
        </div>
      `).join('');
    }

    // Helper function to escape HTML
    function escapeHtml(text) {
      const div = document.createElement('div');
      div.textContent = text;
      return div.innerHTML;
    }

    // Modal functions
    function openAddModuleModal() {
      document.getElementById('addModuleModal').style.display = 'block';
      document.getElementById('moduleName').focus();
    }

    function closeAddModuleModal() {
      document.getElementById('addModuleModal').style.display = 'none';
      document.getElementById('moduleName').value = '';
    }

    function openAddNoteModal(moduleId) {
      currentModule = moduleId;
      currentNote = null;
      document.getElementById('noteModalTitle').innerHTML = '<i class="fas fa-sticky-note" style="color: var(--primary-blue); margin-right: 12px;"></i>Add Note';
      document.getElementById('noteTitle').value = '';
      document.getElementById('noteContent').value = '';
      document.getElementById('noteModal').style.display = 'block';
      document.getElementById('noteTitle').focus();
    }

    function closeNoteModal() {
      document.getElementById('noteModal').style.display = 'none';
      currentModule = null;
      currentNote = null;
    }

    function showExportOptions() {
      document.getElementById('exportModal').style.display = 'block';
    }

    function closeExportModal() {
      document.getElementById('exportModal').style.display = 'none';
    }

    // CRUD operations
    async function addModule() {
      const moduleName = document.getElementById('moduleName').value.trim();
      if (!moduleName) {
        Swal.fire({
          icon: 'warning',
          title: 'Missing Information',
          text: 'Please enter a module name',
          confirmButtonColor: '#007BFF'
        });
        return;
      }

      const formData = new FormData();
      formData.append('action', 'create_module');
      formData.append('name', moduleName);
      
      const result = await apiRequest('notes_api.php', {
        method: 'POST',
        body: formData
      });

      if (result.success) {
        closeAddModuleModal();
        loadModules(); // Reload modules
        
        Swal.fire({
          icon: 'success',
          title: 'Module Created!',
          text: `Module "${moduleName}" has been created successfully`,
          confirmButtonColor: '#007BFF',
          timer: 2000
        });
      } else {
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: result.message || 'Failed to create module',
          confirmButtonColor: '#007BFF'
        });
      }
    }

    async function deleteModule(moduleId) {
      const module = modules.find(m => m.id === moduleId);
      
      Swal.fire({
        title: 'Delete Module?',
        text: `Are you sure you want to delete "${module.name}" and all its notes?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#DC3545',
        cancelButtonColor: '#6C757D',
        confirmButtonText: 'Yes, delete it!'
      }).then(async (result) => {
        if (result.isConfirmed) {
          const formData = new FormData();
          formData.append('action', 'delete_module');
          formData.append('module_id', moduleId);
          
          const apiResult = await apiRequest('notes_api.php', {
            method: 'POST',
            body: formData
          });
          
          if (apiResult.success) {
            loadModules(); // Reload modules
            
            Swal.fire({
              icon: 'success',
              title: 'Deleted!',
              text: 'Module has been deleted successfully',
              confirmButtonColor: '#007BFF',
              timer: 2000
            });
          } else {
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: apiResult.message || 'Failed to delete module',
              confirmButtonColor: '#007BFF'
            });
          }
        }
      });
    }

    async function saveNote() {
      const title = document.getElementById('noteTitle').value.trim();
      const content = document.getElementById('noteContent').value.trim();

      if (!title || !content) {
        Swal.fire({
          icon: 'warning',
          title: 'Missing Information',
          text: 'Please fill in both title and content',
          confirmButtonColor: '#007BFF'
        });
        return;
      }

      const formData = new FormData();
      
      if (currentNote) {
        // Edit existing note
        formData.append('action', 'update_note');
        formData.append('note_id', currentNote);
      } else {
        // Add new note
        formData.append('action', 'create_note');
        formData.append('module_id', currentModule);
      }
      
      formData.append('title', title);
      formData.append('content', content);
      
      const result = await apiRequest('notes_api.php', {
        method: 'POST',
        body: formData
      });

      if (result.success) {
        closeNoteModal();
        loadModules(); // Reload modules

        Swal.fire({
          icon: 'success',
          title: currentNote ? 'Note Updated!' : 'Note Saved!',
          text: `Note "${title}" has been ${currentNote ? 'updated' : 'saved'} successfully`,
          confirmButtonColor: '#007BFF',
          timer: 2000
        });
      } else {
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: result.message || 'Failed to save note',
          confirmButtonColor: '#007BFF'
        });
      }
    }

    function editNote(moduleId, noteId) {
      const module = modules.find(m => m.id === moduleId);
      const note = module.notes.find(n => n.id === noteId);

      currentModule = moduleId;
      currentNote = noteId;

      document.getElementById('noteModalTitle').innerHTML = '<i class="fas fa-edit" style="color: var(--primary-blue); margin-right: 12px;"></i>Edit Note';
      document.getElementById('noteTitle').value = note.title;
      document.getElementById('noteContent').value = note.content;
      document.getElementById('noteModal').style.display = 'block';
      document.getElementById('noteTitle').focus();
    }

    async function deleteNote(moduleId, noteId) {
      const module = modules.find(m => m.id === moduleId);
      const note = module.notes.find(n => n.id === noteId);
      
      Swal.fire({
        title: 'Delete Note?',
        text: `Are you sure you want to delete "${note.title}"?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#DC3545',
        cancelButtonColor: '#6C757D',
        confirmButtonText: 'Yes, delete it!'
      }).then(async (result) => {
        if (result.isConfirmed) {
          const formData = new FormData();
          formData.append('action', 'delete_note');
          formData.append('note_id', noteId);
          
          const apiResult = await apiRequest('notes_api.php', {
            method: 'POST',
            body: formData
          });
          
          if (apiResult.success) {
            loadModules(); // Reload modules
            
            Swal.fire({
              icon: 'success',
              title: 'Deleted!',
              text: 'Note has been deleted successfully',
              confirmButtonColor: '#007BFF',
              timer: 2000
            });
          } else {
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: apiResult.message || 'Failed to delete note',
              confirmButtonColor: '#007BFF'
            });
          }
        }
      });
    }

    function viewNote(moduleId, noteId) {
      const module = modules.find(m => m.id === moduleId);
      const note = module.notes.find(n => n.id === noteId);

      Swal.fire({
        title: note.title,
        html: `<div style="text-align: left; max-height: 400px; overflow-y: auto; padding: 10px; background: #f8f9fa; border-radius: 8px; white-space: pre-wrap;">${escapeHtml(note.content)}</div>`,
        width: '600px',
        confirmButtonColor: '#007BFF',
        confirmButtonText: 'Close'
      });
    }

    // Export functions - TXT only
    function exportNote(noteId) {
      window.open(`notes_api.php?action=export&type=note&id=${noteId}&format=txt`, '_blank');
    }

    function exportModule(moduleId) {
      window.open(`notes_api.php?action=export&type=module&id=${moduleId}&format=txt`, '_blank');
    }

    function exportAllNotes(format) {
      closeExportModal();
      
      Swal.fire({
        title: 'Exporting...',
        text: `Preparing your notes for ${format.toUpperCase()} export`,
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => {
          Swal.showLoading();
          // Start download after a short delay to show loading
          setTimeout(() => {
            window.open(`notes_api.php?action=export&type=all&format=${format}`, '_blank');
            Swal.close();
          }, 1000);
        }
      });
    }

    // Search functionality
    function setupSearchFunctionality() {
      const searchInput = document.getElementById('searchInput');
      let searchTimeout;

      searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
          const searchTerm = this.value.toLowerCase().trim();
          filterModules(searchTerm);
        }, 300);
      });
    }

    function filterModules(searchTerm) {
      const moduleCards = document.querySelectorAll('.module-card');
      
      if (!searchTerm) {
        moduleCards.forEach(card => {
          card.style.display = 'block';
          card.style.animation = 'fadeIn 0.3s ease';
        });
        return;
      }

      moduleCards.forEach(card => {
        const moduleText = card.textContent.toLowerCase();
        if (moduleText.includes(searchTerm)) {
          card.style.display = 'block';
          card.style.animation = 'fadeIn 0.3s ease';
        } else {
          card.style.display = 'none';
        }
      });
    }

    // Close modals when clicking outside
    window.onclick = function(event) {
      const addModuleModal = document.getElementById('addModuleModal');
      const noteModal = document.getElementById('noteModal');
      const exportModal = document.getElementById('exportModal');
      
      if (event.target === addModuleModal) {
        closeAddModuleModal();
      }
      if (event.target === noteModal) {
        closeNoteModal();
      }
      if (event.target === exportModal) {
        closeExportModal();
      }
    }

    // Keyboard shortcuts
    document.addEventListener('keydown', function(event) {
      if (event.ctrlKey && event.key === 'n') {
        event.preventDefault();
        openAddModuleModal();
      }
      if (event.key === 'Escape') {
        closeAddModuleModal();
        closeNoteModal();
        closeExportModal();
      }
    });

    // Helper function to create first module easily
    function createFirstModule() {
      Swal.fire({
        title: 'Create Your First Module',
        input: 'text',
        inputLabel: 'Module Name',
        inputPlaceholder: 'e.g., Mathematics, Computer Science, etc.',
        showCancelButton: true,
        confirmButtonColor: '#007BFF',
        cancelButtonColor: '#6C757D',
        confirmButtonText: 'Create Module',
        inputValidator: (value) => {
          if (!value || !value.trim()) {
            return 'Please enter a module name';
          }
        }
      }).then(async (result) => {
        if (result.isConfirmed) {
          const formData = new FormData();
          formData.append('action', 'create_module');
          formData.append('name', result.value.trim());
          
          const apiResult = await apiRequest('notes_api.php', {
            method: 'POST',
            body: formData
          });
          
          if (apiResult && apiResult.success) {
            Swal.fire({
              icon: 'success',
              title: 'Module Created!',
              text: `Module "${result.value}" has been created successfully`,
              confirmButtonColor: '#007BFF',
              timer: 2000
            });
            loadModules(); // Reload the interface
          } else {
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: apiResult?.message || 'Failed to create module',
              confirmButtonColor: '#007BFF'
            });
          }
        }
      });
    }
  </script>
</body>
</html>
