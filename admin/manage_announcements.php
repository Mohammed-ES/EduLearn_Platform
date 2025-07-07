<?php
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

require_once '../config/connectiondb.php';

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
      switch ($_POST['action']) {
        case 'delete':
            if (isset($_POST['id'])) {
                try {
                    // Utiliser soft delete en mettant à jour deleted_at au lieu de supprimer définitivement
                    $stmt = $conn->prepare("UPDATE announcements SET deleted_at = NOW() WHERE id = ?");
                    $stmt->execute([$_POST['id']]);
                    
                    if ($stmt->rowCount() > 0) {
                        echo json_encode(['success' => true, 'message' => 'Announcement deleted successfully']);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Announcement not found']);
                    }
                } catch (PDOException $e) {
                    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid request']);
            }
            exit;
            
        case 'restore':
            if (isset($_POST['id'])) {
                try {
                    // Restaurer l'annonce en mettant deleted_at à NULL
                    $stmt = $conn->prepare("UPDATE announcements SET deleted_at = NULL WHERE id = ?");
                    $stmt->execute([$_POST['id']]);
                    
                    if ($stmt->rowCount() > 0) {
                        echo json_encode(['success' => true, 'message' => 'Announcement restored successfully']);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Announcement not found']);
                    }
                } catch (PDOException $e) {
                    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid request']);
            }
            exit;
            
        case 'publish':
            if (isset($_POST['id'])) {
                try {
                    // Publier l'annonce
                    $stmt = $conn->prepare("UPDATE announcements SET status = 'published', published_at = NOW() WHERE id = ?");
                    $stmt->execute([$_POST['id']]);
                    
                    if ($stmt->rowCount() > 0) {
                        echo json_encode(['success' => true, 'message' => 'Announcement published successfully']);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Announcement not found']);
                    }
                } catch (PDOException $e) {
                    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid request']);
            }
            exit;
              case 'save':
            try {
                $title = $_POST['title'] ?? '';
                $content = $_POST['content'] ?? '';
                $importance = $_POST['priority'] ?? 'medium'; // Utilise priority du formulaire mais le stocke comme importance
                $status = $_POST['status'] ?? 'draft';
                $id = $_POST['id'] ?? null;
                $author_id = $_SESSION['user_id'] ?? 1;
                
                if (empty($title) || empty($content)) {
                    echo json_encode(['success' => false, 'message' => 'Title and content are required']);
                    exit;
                }
                
                if ($id) {
                    // Update existing announcement
                    $stmt = $conn->prepare("UPDATE announcements SET title = ?, content = ?, importance = ?, status = ?, updated_at = NOW() WHERE id = ?");
                    $stmt->execute([$title, $content, $importance, $status, $id]);
                    $message = 'Announcement updated successfully';
                } else {
                    // Create new announcement
                    $stmt = $conn->prepare("INSERT INTO announcements (title, content, importance, status, author_id, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())");
                    $stmt->execute([$title, $content, $importance, $status, $author_id]);
                    $message = 'Announcement created successfully';
                }
                
                echo json_encode(['success' => true, 'message' => $message]);
            } catch (PDOException $e) {
                echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
            }
            exit;
            
        case 'get':
            if (isset($_POST['id'])) {
                try {
                    $stmt = $conn->prepare("SELECT * FROM announcements WHERE id = ?");
                    $stmt->execute([$_POST['id']]);
                    $announcement = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($announcement) {
                        echo json_encode(['success' => true, 'data' => $announcement]);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Announcement not found']);
                    }
                } catch (PDOException $e) {
                    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid request']);
            }
            exit;
    }
}

// Get all announcements from database (including deleted and drafts)
try {
    $stmt = $conn->prepare("SELECT * FROM announcements ORDER BY created_at DESC");
    $stmt->execute();
    $announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $announcements = [];
}

$user_name = $_SESSION['user_fullname'] ?? 'Administrator';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Announcements Management | EduLearn</title>
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
            --info: #17A2B8;
            --shadow-light: 0 2px 10px rgba(0, 123, 255, 0.1);
            --shadow-medium: 0 5px 25px rgba(0, 123, 255, 0.15);
            --shadow-heavy: 0 10px 40px rgba(15, 76, 117, 0.2);
            --gradient-primary: linear-gradient(135deg, var(--primary-blue) 0%, var(--dark-blue) 100%);
            --gradient-accent: linear-gradient(135deg, var(--gold-accent) 0%, #B8941F 100%);
            --gradient-info: linear-gradient(135deg, var(--info) 0%, #138496 100%);
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
        .shape-4 { width: 180px; height: 180px; top: 40%; right: 40%; animation-delay: 3s; background: var(--gradient-info); }

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
            background: var(--gradient-info);
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
            color: var(--info);
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
            background: rgba(23, 162, 184, 0.05);
            border-radius: 12px;
            border: 1px solid rgba(23, 162, 184, 0.1);
        }

        .stat-number {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--info);
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

        .btn-info {
            background: var(--gradient-info);
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

        /* Announcements Grid */
        .announcements-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
            gap: 24px;
            margin-bottom: 32px;
        }

        .announcement-card {
            background: var(--white);
            border-radius: var(--border-radius);
            padding: 24px;
            box-shadow: var(--shadow-medium);
            transition: var(--transition);
            border: 1px solid rgba(0, 123, 255, 0.1);
            position: relative;
            overflow: hidden;
        }

        .announcement-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-heavy);
        }

        .announcement-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--gradient-info);
        }

        .announcement-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 16px;
        }

        .announcement-priority {
            padding: 4px 12px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
        }

        .priority-high {
            background: rgba(220, 53, 69, 0.1);
            color: var(--error);
        }

        .priority-medium {
            background: rgba(255, 193, 7, 0.1);
            color: var(--warning);
        }

        .priority-low {
            background: rgba(40, 167, 69, 0.1);
            color: var(--success);
        }

        .announcement-actions {
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
        }        .action-btn.delete {
            background: rgba(220, 53, 69, 0.1);
            color: var(--error);
        }

        .action-btn.delete:hover {
            background: var(--error);
            color: white;
        }

        .action-btn.restore {
            background: rgba(40, 167, 69, 0.1);
            color: var(--success);
        }

        .action-btn.restore:hover {
            background: var(--success);
            color: white;
        }

        .action-btn.publish {
            background: rgba(212, 175, 55, 0.1);
            color: var(--gold-accent);
        }

        .action-btn.publish:hover {
            background: var(--gold-accent);
            color: white;
        }

        /* Styles pour les annonces supprimées */
        .announcement-card.deleted {
            opacity: 0.7;
            border-left: 4px solid var(--error);
            background: rgba(220, 53, 69, 0.02);
        }

        .announcement-card.deleted .announcement-title {
            text-decoration: line-through;
            color: var(--text-light);
        }

        .announcement-card.deleted .announcement-content {
            color: var(--blue-gray);
        }.announcement-title {
            font-family: 'Poppins', sans-serif;
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 12px;
            color: var(--text-dark);
            line-height: 1.4;
            transition: var(--transition);
        }

        .announcement-title:hover {
            color: var(--primary-blue);
        }

        .announcement-content {
            color: var(--text-light);
            margin-bottom: 16px;
            line-height: 1.6;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            transition: var(--transition);
        }

        .announcement-content:hover {
            color: var(--text-dark);
        }

        .announcement-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 16px;
            border-top: 1px solid rgba(0, 123, 255, 0.1);
            font-size: 12px;
            color: var(--text-light);
        }

        .announcement-date {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .announcement-status {
            display: flex;
            align-items: center;
            gap: 6px;
        }        .status-published {
            color: var(--success);
        }

        .status-draft {
            color: var(--warning);
        }

        .status-deleted {
            color: var(--error);
            font-weight: 600;
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
            max-width: 600px;
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

        .form-input,
        .form-textarea,
        .form-select {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid rgba(0, 123, 255, 0.1);
            border-radius: 8px;
            font-size: 14px;
            transition: var(--transition);
            font-family: inherit;
        }

        .form-textarea {
            min-height: 120px;
            resize: vertical;
        }

        .form-input:focus,
        .form-textarea:focus,
        .form-select:focus {
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

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 24px;
            color: var(--text-light);
            background: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-light);
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 16px;
            opacity: 0.3;
        }        .empty-state h3 {
            font-size: 1.2rem;
            margin-bottom: 8px;
            color: var(--text-dark);
        }

        /* View Announcement Modal Styles */
        .view-announcement-content {
            max-height: 70vh;
            overflow-y: auto;
        }

        .view-announcement-meta {
            background: rgba(0, 123, 255, 0.05);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 24px;
            border: 1px solid rgba(0, 123, 255, 0.1);
        }

        .meta-row {
            display: flex;
            align-items: center;
            margin-bottom: 12px;
            gap: 12px;
        }

        .meta-row:last-child {
            margin-bottom: 0;
        }

        .meta-label {
            font-weight: 600;
            color: var(--text-dark);
            min-width: 100px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .meta-label i {
            color: var(--primary-blue);
            width: 16px;
        }

        .meta-value {
            color: var(--text-light);
            flex: 1;
        }

        .meta-value.status-published {
            color: var(--success);
            font-weight: 500;
        }

        .meta-value.status-draft {
            color: var(--warning);
            font-weight: 500;
        }

        .meta-value.priority-high {
            color: var(--error);
            font-weight: 500;
            text-transform: uppercase;
        }

        .meta-value.priority-medium {
            color: var(--warning);
            font-weight: 500;
            text-transform: uppercase;
        }

        .meta-value.priority-low {
            color: var(--success);
            font-weight: 500;
            text-transform: uppercase;
        }

        .view-announcement-body {
            margin-bottom: 24px;
        }

        .view-title {
            font-family: 'Poppins', sans-serif;
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 20px;
            line-height: 1.3;
            border-bottom: 2px solid rgba(0, 123, 255, 0.1);
            padding-bottom: 16px;
        }

        .view-content {
            font-size: 16px;
            line-height: 1.8;
            color: var(--text-dark);
            white-space: pre-wrap;
            background: rgba(248, 249, 250, 0.5);
            padding: 24px;
            border-radius: 12px;
            border: 1px solid rgba(0, 123, 255, 0.1);
        }

        .view-announcement-actions {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            padding-top: 20px;
            border-top: 1px solid rgba(0, 123, 255, 0.1);
        }

        .btn-danger {
            background: var(--error);
            color: white;
        }

        .btn-danger:hover {
            background: #c82333;
            transform: translateY(-2px);
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

            .toolbar {
                flex-direction: column;
                align-items: stretch;
            }

            .search-container {
                min-width: auto;
            }

            .announcements-grid {
                grid-template-columns: 1fr;
            }

            .modal-content {
                padding: 24px;
                margin: 20px;
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
                <a href="dashboard.php" class="back-btn">
                    <i class="fas fa-arrow-left"></i>
                    Back to Dashboard
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Page Header -->
        <section class="page-header" data-aos="fade-up">
            <div class="header-content">
                <div class="header-info">
                    <h1>
                        <i class="fas fa-bullhorn"></i>
                        Announcements Management
                    </h1>
                    <p>Create, manage and publish announcements for students and faculty</p>
                </div>
                <div class="header-stats">
                    <div class="stat-item">
                        <div class="stat-number"><?php echo count($announcements); ?></div>
                        <div class="stat-label">Total Announcements</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number"><?php echo count(array_filter($announcements, function($a) { return $a['status'] === 'published'; })); ?></div>
                        <div class="stat-label">Published</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Toolbar -->
        <section class="toolbar" data-aos="fade-up" data-aos-delay="100">
            <div class="toolbar-left">
                <button class="btn btn-primary" onclick="openAddModal()">
                    <i class="fas fa-plus"></i>
                    New Announcement
                </button>
                <button class="btn btn-info">
                    <i class="fas fa-filter"></i>
                    Filter
                </button>
            </div>
            <div class="search-container">
                <i class="fas fa-search search-icon"></i>
                <input type="text" class="search-input" id="searchInput" placeholder="Search announcements...">
            </div>
        </section>

        <!-- Announcements Grid -->
        <?php if (empty($announcements)): ?>
        <section class="empty-state" data-aos="fade-up" data-aos-delay="200">
            <i class="fas fa-bullhorn"></i>
            <h3>No Announcements Yet</h3>
            <p>Create your first announcement to communicate with students and faculty.</p>
        </section>
        <?php else: ?>
        <section class="announcements-grid" data-aos="fade-up" data-aos-delay="200">            <?php foreach ($announcements as $announcement): ?>
            <div class="announcement-card <?php echo $announcement['deleted_at'] ? 'deleted' : ''; ?>">
                <div class="announcement-header">
                    <span class="announcement-priority priority-<?php echo strtolower($announcement['importance'] ?? 'medium'); ?>">
                        <?php echo ucfirst($announcement['importance'] ?? 'Medium'); ?> Priority
                    </span>
                    <div class="announcement-actions">
                        <?php if ($announcement['deleted_at']): ?>
                            <!-- Actions pour annonces supprimées -->
                            <button class="action-btn restore" onclick="restoreAnnouncement(<?php echo $announcement['id']; ?>)" title="Restore Announcement">
                                <i class="fas fa-undo"></i>
                            </button>
                        <?php else: ?>
                            <!-- Actions pour annonces actives -->
                            <?php if ($announcement['status'] === 'draft'): ?>
                                <button class="action-btn publish" onclick="publishAnnouncement(<?php echo $announcement['id']; ?>)" title="Publish Announcement">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                            <?php endif; ?>
                            <button class="action-btn edit" onclick="editAnnouncement(<?php echo $announcement['id']; ?>)" title="Edit Announcement">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="action-btn delete" onclick="deleteAnnouncement(<?php echo $announcement['id']; ?>)" title="Delete Announcement">
                                <i class="fas fa-trash"></i>
                            </button>
                        <?php endif; ?>
                    </div>
                </div><h3 class="announcement-title" onclick="viewAnnouncement(<?php echo $announcement['id']; ?>)" style="cursor: pointer;" title="Click to view full announcement">
                    <?php echo htmlspecialchars($announcement['title']); ?>
                    <i class="fas fa-external-link-alt" style="font-size: 0.8em; margin-left: 8px; opacity: 0.6;"></i>
                </h3>
                <p class="announcement-content" onclick="viewAnnouncement(<?php echo $announcement['id']; ?>)" style="cursor: pointer;" title="Click to view full announcement">
                    <?php echo htmlspecialchars($announcement['content']); ?>
                    <?php if (strlen($announcement['content']) > 150): ?>
                        <span style="color: var(--primary-blue); font-weight: 500;"> ...Read more</span>
                    <?php endif; ?>
                </p>
                  <div class="announcement-meta">
                    <div class="announcement-date">
                        <i class="fas fa-calendar"></i>
                        <?php echo date('M d, Y', strtotime($announcement['created_at'])); ?>
                    </div>
                    <?php if ($announcement['deleted_at']): ?>
                        <div class="announcement-status status-deleted">
                            <i class="fas fa-trash" style="font-size: 10px;"></i>
                            Deleted
                        </div>
                    <?php else: ?>
                        <div class="announcement-status status-<?php echo $announcement['status'] ?? 'draft'; ?>">
                            <i class="fas fa-circle" style="font-size: 8px;"></i>
                            <?php echo ucfirst($announcement['status'] ?? 'Draft'); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </section>
        <?php endif; ?>
    </main>

    <!-- Announcement Modal -->
    <div id="announcementModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="modalTitle">Create New Announcement</h2>
                <button class="close-btn" onclick="closeModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>            <form id="announcementForm">
                <input type="hidden" id="announcementId" name="id">
                <div class="form-group">
                    <label class="form-label">Title</label>
                    <input type="text" class="form-input" id="announcementTitle" name="title" placeholder="Enter announcement title" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Content</label>
                    <textarea class="form-textarea" id="announcementContent" name="content" placeholder="Enter announcement content" required></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Priority</label>
                    <select class="form-select" id="announcementPriority" name="priority">
                        <option value="low">Low Priority</option>
                        <option value="medium" selected>Medium Priority</option>
                        <option value="high">High Priority</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select class="form-select" id="announcementStatus" name="status">
                        <option value="draft">Draft</option>
                        <option value="published">Published</option>
                    </select>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-cancel" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        Save Announcement
                    </button>
                </div>
            </form>
        </div>    </div>

    <!-- View Announcement Modal -->
    <div id="viewAnnouncementModal" class="modal">
        <div class="modal-content" style="max-width: 800px;">
            <div class="modal-header">
                <h2 class="modal-title" id="viewModalTitle">Announcement Details</h2>
                <button class="close-btn" onclick="closeViewModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="view-announcement-content">
                <div class="view-announcement-meta">
                    <div class="meta-row">
                        <span class="meta-label"><i class="fas fa-tag"></i> Priority:</span>
                        <span class="meta-value" id="viewPriority"></span>
                    </div>
                    <div class="meta-row">
                        <span class="meta-label"><i class="fas fa-circle"></i> Status:</span>
                        <span class="meta-value" id="viewStatus"></span>
                    </div>
                    <div class="meta-row">
                        <span class="meta-label"><i class="fas fa-calendar"></i> Created:</span>
                        <span class="meta-value" id="viewCreatedAt"></span>
                    </div>
                    <div class="meta-row" id="viewUpdatedRow" style="display: none;">
                        <span class="meta-label"><i class="fas fa-edit"></i> Updated:</span>
                        <span class="meta-value" id="viewUpdatedAt"></span>
                    </div>
                </div>
                
                <div class="view-announcement-body">
                    <h3 class="view-title" id="viewAnnouncementTitle"></h3>
                    <div class="view-content" id="viewAnnouncementContent"></div>
                </div>
                
                <div class="view-announcement-actions">
                    <button class="btn btn-info" onclick="editFromView()" id="editFromViewBtn">
                        <i class="fas fa-edit"></i>
                        Edit Announcement
                    </button>
                    <button class="btn btn-danger" onclick="deleteFromView()" id="deleteFromViewBtn">
                        <i class="fas fa-trash"></i>
                        Delete Announcement
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>    <script>
        // Initialize AOS animations
        AOS.init({
            duration: 800,
            easing: 'ease-out-cubic',
            once: true,
            offset: 50
        });        let currentAnnouncementId = null;
        let currentViewingAnnouncement = null;

        // Announcement management functions
        function openAddModal() {
            currentAnnouncementId = null;
            document.getElementById('modalTitle').textContent = 'Create New Announcement';
            document.getElementById('announcementForm').reset();
            document.getElementById('announcementId').value = '';
            document.getElementById('announcementModal').classList.add('show');
        }

        function closeModal() {
            document.getElementById('announcementModal').classList.remove('show');
            currentAnnouncementId = null;
        }

        function closeViewModal() {
            document.getElementById('viewAnnouncementModal').classList.remove('show');
            currentViewingAnnouncement = null;
        }

        function viewAnnouncement(id) {
            // Make AJAX request to get announcement data
            const formData = new FormData();
            formData.append('action', 'get');
            formData.append('id', id);
            
            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    currentViewingAnnouncement = data.data;
                    displayAnnouncementDetails(data.data);
                    document.getElementById('viewAnnouncementModal').classList.add('show');
                } else {
                    showAlert(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('Failed to load announcement details', 'error');
            });
        }

        function displayAnnouncementDetails(announcement) {
            // Set title
            document.getElementById('viewAnnouncementTitle').textContent = announcement.title;
            
            // Set content
            document.getElementById('viewAnnouncementContent').textContent = announcement.content;
            
            // Set priority with styling
            const priorityElement = document.getElementById('viewPriority');
            priorityElement.textContent = (announcement.importance || 'Medium') + ' Priority';
            priorityElement.className = 'meta-value priority-' + (announcement.importance || 'medium').toLowerCase();
            
            // Set status with styling
            const statusElement = document.getElementById('viewStatus');
            statusElement.textContent = (announcement.status || 'Draft').charAt(0).toUpperCase() + (announcement.status || 'draft').slice(1);
            statusElement.className = 'meta-value status-' + (announcement.status || 'draft');
            
            // Set dates
            if (announcement.created_at) {
                const createdDate = new Date(announcement.created_at);
                document.getElementById('viewCreatedAt').textContent = createdDate.toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
            }
            
            if (announcement.updated_at && announcement.updated_at !== announcement.created_at) {
                const updatedDate = new Date(announcement.updated_at);
                document.getElementById('viewUpdatedAt').textContent = updatedDate.toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
                document.getElementById('viewUpdatedRow').style.display = 'flex';
            } else {
                document.getElementById('viewUpdatedRow').style.display = 'none';
            }
            
            // Set action buttons data
            document.getElementById('editFromViewBtn').setAttribute('data-id', announcement.id);
            document.getElementById('deleteFromViewBtn').setAttribute('data-id', announcement.id);
        }

        function editFromView() {
            if (currentViewingAnnouncement) {
                closeViewModal();
                editAnnouncement(currentViewingAnnouncement.id);
            }
        }

        function deleteFromView() {
            if (currentViewingAnnouncement) {
                closeViewModal();
                deleteAnnouncement(currentViewingAnnouncement.id);
            }
        }

        function editAnnouncement(id) {
            currentAnnouncementId = id;
            document.getElementById('modalTitle').textContent = 'Edit Announcement';
            
            // Make AJAX request to get announcement data
            const formData = new FormData();
            formData.append('action', 'get');
            formData.append('id', id);
            
            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {                if (data.success) {
                    const announcement = data.data;
                    document.getElementById('announcementId').value = announcement.id;
                    document.getElementById('announcementTitle').value = announcement.title;
                    document.getElementById('announcementContent').value = announcement.content;
                    document.getElementById('announcementPriority').value = announcement.importance || 'medium';
                    document.getElementById('announcementStatus').value = announcement.status || 'draft';
                    document.getElementById('announcementModal').classList.add('show');
                } else {
                    showAlert(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('Failed to load announcement data', 'error');
            });
        }        function deleteAnnouncement(id) {
            if (confirm('Are you sure you want to delete this announcement? This action cannot be undone.')) {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('id', id);
                
                fetch('', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert(data.message, 'success');
                        // Remove the announcement card from the page
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    } else {
                        showAlert(data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('Failed to delete announcement', 'error');
                });
            }
        }

        function restoreAnnouncement(id) {
            if (confirm('Are you sure you want to restore this announcement? It will be visible to students again.')) {
                const formData = new FormData();
                formData.append('action', 'restore');
                formData.append('id', id);
                
                fetch('', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert(data.message, 'success');
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    } else {
                        showAlert(data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('Failed to restore announcement', 'error');
                });
            }
        }

        function publishAnnouncement(id) {
            if (confirm('Are you sure you want to publish this announcement? It will be immediately visible to all students.')) {
                const formData = new FormData();
                formData.append('action', 'publish');
                formData.append('id', id);
                
                fetch('', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert(data.message, 'success');
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    } else {
                        showAlert(data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('Failed to publish announcement', 'error');
                });
            }
        }

        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const cards = document.querySelectorAll('.announcement-card');
            
            cards.forEach(card => {
                const text = card.textContent.toLowerCase();
                card.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });

        // Form submission
        document.getElementById('announcementForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('action', 'save');
            
            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
            submitBtn.disabled = true;
            
            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
                
                if (data.success) {
                    showAlert(data.message, 'success');
                    closeModal();
                    // Reload page to show updated data
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    showAlert(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
                showAlert('Failed to save announcement', 'error');
            });
        });        // Close modal when clicking outside
        document.getElementById('announcementModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });

        // Close view modal when clicking outside
        document.getElementById('viewAnnouncementModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeViewModal();
            }
        });

        // Keyboard navigation
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                if (document.getElementById('viewAnnouncementModal').classList.contains('show')) {
                    closeViewModal();
                } else if (document.getElementById('announcementModal').classList.contains('show')) {
                    closeModal();
                }
            }
        });

        function showAlert(message, type) {
            // Remove existing alerts
            const existingAlerts = document.querySelectorAll('.alert');
            existingAlerts.forEach(alert => alert.remove());
            
            const alertClass = type === 'success' ? 'alert-success' : 'alert-error';
            const icon = type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle';
            
            const alertHtml = `
                <div class="alert ${alertClass}" style="
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    z-index: 9999;
                    background: ${type === 'success' ? '#d4edda' : '#f8d7da'};
                    color: ${type === 'success' ? '#155724' : '#721c24'};
                    border: 1px solid ${type === 'success' ? '#c3e6cb' : '#f5c6cb'};
                    padding: 16px 20px;
                    border-radius: 8px;
                    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                    display: flex;
                    align-items: center;
                    gap: 10px;
                    min-width: 300px;
                    animation: slideInRight 0.3s ease-out;
                ">
                    <i class="${icon}"></i>
                    ${message}
                </div>
            `;
            
            document.body.insertAdjacentHTML('beforeend', alertHtml);
            
            // Remove alert after 5 seconds
            setTimeout(() => {
                const alert = document.querySelector('.alert');
                if (alert) {
                    alert.style.animation = 'slideOutRight 0.3s ease-in';
                    setTimeout(() => alert.remove(), 300);
                }
            }, 5000);
        }

        // Add CSS for alert animations
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideInRight {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
            
            @keyframes slideOutRight {
                from {
                    transform: translateX(0);
                    opacity: 1;
                }
                to {
                    transform: translateX(100%);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);

        // Add smooth hover effects
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.announcement-card').forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-8px) scale(1.02)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0) scale(1)';
                });
            });
        });
    </script>
</body>
</html>
