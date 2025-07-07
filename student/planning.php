<?php 
session_start();

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
    header('Location: ../auth/login.php');
    exit;
}

include('../config/connectiondb.php'); 

$user_name = $_SESSION['user_fullname'] ?? 'Student';
$user_initials = strtoupper(substr($user_name, 0, 2));
$user_id = $_SESSION['user_id'];

// Handle form submissions
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {            case 'add_event':
                $title = $_POST['title'] ?? '';
                $description = $_POST['description'] ?? '';
                $event_date = $_POST['event_date'] ?? '';
                $start_time = $_POST['start_time'] ?? '';
                $event_type = $_POST['event_type'] ?? '';
                $color = '#007BFF'; // Default color
                
                // Set color based on event type
                switch($event_type) {
                    case 'meeting':
                        $color = '#00c8c8';
                        break;
                    case 'exam':
                        $color = '#dc3545';
                        break;
                    case 'assignment':
                        $color = '#ff8c00';
                        break;
                    case 'personal':
                        $color = '#28a745';
                        break;
                    default:
                        $color = '#6c757d';
                }
                
                if (!empty($title) && !empty($event_date) && !empty($start_time)) {
                    // Combine date and time
                    $start_datetime = $event_date . ' ' . $start_time . ':00';
                    $end_datetime = date('Y-m-d H:i:s', strtotime($start_datetime . ' +1 hour'));
                    
                    try {
                        $stmt = $conn->prepare("INSERT INTO events (user_id, title, description, start_time, end_time, location, color, all_day) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                        $stmt->execute([$user_id, $title, $description, $start_datetime, $end_datetime, $event_type, $color, 0]);
                        $message = 'Event added successfully!';
                        $messageType = 'success';
                    } catch (PDOException $e) {
                        $message = 'Error adding event: ' . $e->getMessage();
                        $messageType = 'error';
                    }
                } else {
                    $message = 'Please fill in required fields (Title, Date and Time).';
                    $messageType = 'error';
                }
                break;
                
            case 'delete_event':
                $event_id = $_POST['event_id'] ?? '';
                if (!empty($event_id)) {
                    try {
                        $stmt = $conn->prepare("DELETE FROM events WHERE id = ? AND user_id = ?");
                        $stmt->execute([$event_id, $user_id]);
                        $message = 'Event deleted successfully!';
                        $messageType = 'success';
                    } catch (PDOException $e) {
                        $message = 'Error deleting event: ' . $e->getMessage();
                        $messageType = 'error';
                    }
                }
                break;
        }
    }
}

// Fetch user events
try {
    $stmt = $conn->prepare("SELECT * FROM events WHERE user_id = ? AND deleted_at IS NULL ORDER BY start_time ASC");
    $stmt->execute([$user_id]);
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $events = [];
    $message = 'Error fetching events: ' . $e->getMessage();
    $messageType = 'error';
}

// Group events by date for calendar display
$calendar_events = [];
foreach ($events as $event) {
    $date = date('Y-m-d', strtotime($event['start_time']));
    if (!isset($calendar_events[$date])) {
        $calendar_events[$date] = [];
    }
    $calendar_events[$date][] = $event;
}

// Get current month for calendar
$current_month = date('Y-m');
$current_month_start = $current_month . '-01';
$current_month_end = date('Y-m-t', strtotime($current_month_start));
$today = date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Planning | EduLearn</title>
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
      opacity: 0.1;
      animation: float 15s infinite linear;
    }

    .floating-shape:nth-child(1) {
      width: 100px;
      height: 100px;
      top: 20%;
      left: 10%;
      animation-duration: 20s;
      animation-delay: 0s;
    }

    .floating-shape:nth-child(2) {
      width: 150px;
      height: 150px;
      top: 60%;
      right: 10%;
      animation-duration: 25s;
      animation-delay: 5s;
    }

    .floating-shape:nth-child(3) {
      width: 80px;
      height: 80px;
      bottom: 20%;
      left: 20%;
      animation-duration: 18s;
      animation-delay: 10s;
    }

    .floating-shape:nth-child(4) {
      width: 120px;
      height: 120px;
      top: 10%;
      right: 30%;
      animation-duration: 22s;
      animation-delay: 15s;
    }

    @keyframes float {
      0% {
        transform: translateY(0px) rotate(0deg);
      }
      33% {
        transform: translateY(-30px) rotate(120deg);
      }
      66% {
        transform: translateY(20px) rotate(240deg);
      }
      100% {
        transform: translateY(0px) rotate(360deg);
      }
    }    /* Header */
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
    }    /* Container */
    .main-content {
      padding: 40px 0;
    }

    /* Page Header */
    .page-header {
      text-align: center;
      margin-bottom: 40px;
      animation: fadeInUp 0.8s ease-out;
    }

    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(40px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .page-title {
      font-size: 3rem;
      font-weight: 700;
      color: var(--dark-blue);
      margin-bottom: 12px;
      background: var(--gradient-primary);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }

    .page-subtitle {
      font-size: 1.2rem;
      color: var(--text-light);
      max-width: 600px;
      margin: 0 auto;
    }

    /* Notifications */
    .notification {
      padding: 16px 24px;
      border-radius: var(--border-radius);
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

    /* Dashboard Grid */
    .dashboard-grid {
      display: grid;
      grid-template-columns: 1fr 2fr;
      gap: 40px;
      margin-bottom: 40px;
    }

    /* Event Form */
    .event-form {
      background: var(--white);
      border-radius: var(--border-radius);
      padding: 32px;
      box-shadow: var(--shadow-medium);
      height: fit-content;
      animation: fadeInLeft 0.8s ease-out;
    }

    @keyframes fadeInLeft {
      from {
        opacity: 0;
        transform: translateX(-40px);
      }
      to {
        opacity: 1;
        transform: translateX(0);
      }
    }

    .form-header {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 24px;
    }

    .form-icon {
      width: 48px;
      height: 48px;
      background: var(--gradient-accent);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--white);
      font-size: 1.2rem;
    }

    .form-title {
      font-size: 1.5rem;
      font-weight: 600;
      color: var(--text-dark);
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
      padding: 14px 16px;
      border: 2px solid rgba(0, 123, 255, 0.1);
      border-radius: 12px;
      font-size: 14px;
      transition: var(--transition);
      background: var(--white);
      color: var(--text-dark);
    }

    .form-input:focus,
    .form-textarea:focus,
    .form-select:focus {
      outline: none;
      border-color: var(--primary-blue);
      box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
    }

    .form-textarea {
      min-height: 100px;
      resize: vertical;
    }

    .form-checkbox {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-top: 16px;
    }

    .checkbox-input {
      width: 20px;
      height: 20px;
      accent-color: var(--primary-blue);
    }

    .color-picker-group {
      display: grid;
      grid-template-columns: repeat(6, 1fr);
      gap: 8px;
      margin-top: 8px;
    }

    .color-option {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      border: 3px solid transparent;
      cursor: pointer;
      transition: var(--transition);
    }

    .color-option:hover,
    .color-option.selected {
      transform: scale(1.1);
      border-color: var(--white);
      box-shadow: var(--shadow-medium);
    }

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
      margin-top: 20px;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
    }

    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: var(--shadow-heavy);
    }

    .btn-primary:active {
      transform: translateY(0);
    }

    /* Calendar */
    .calendar-container {
      background: var(--white);
      border-radius: var(--border-radius);
      padding: 32px;
      box-shadow: var(--shadow-medium);
      animation: fadeInRight 0.8s ease-out;
    }

    @keyframes fadeInRight {
      from {
        opacity: 0;
        transform: translateX(40px);
      }
      to {
        opacity: 1;
        transform: translateX(0);
      }
    }

    .calendar-header {
      display: flex;
      align-items: center;
      justify-content: between;
      margin-bottom: 24px;
    }

    .calendar-title {
      font-size: 1.5rem;
      font-weight: 600;
      color: var(--text-dark);
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .calendar-title i {
      color: var(--gold-accent);
    }

    .calendar-grid {
      display: grid;
      grid-template-columns: repeat(7, 1fr);
      gap: 1px;
      background: rgba(0, 123, 255, 0.1);
      border-radius: 12px;
      overflow: hidden;
    }

    .calendar-day-header {
      background: var(--gradient-primary);
      color: var(--white);
      padding: 16px 8px;
      text-align: center;
      font-weight: 600;
      font-size: 14px;
    }

    .calendar-day {
      background: var(--white);
      min-height: 100px;
      padding: 8px;
      position: relative;
      transition: var(--transition);
      cursor: pointer;
    }

    .calendar-day:hover {
      background: rgba(0, 123, 255, 0.05);
    }

    .calendar-day.today {
      background: rgba(0, 123, 255, 0.1);
      border: 2px solid var(--primary-blue);
    }

    .calendar-day.has-events {
      background: rgba(212, 175, 55, 0.1);
    }

    .calendar-day.empty {
      background: rgba(0, 0, 0, 0.02);
    }

    .calendar-date {
      font-weight: 600;
      margin-bottom: 4px;
      color: var(--text-dark);
    }

    .calendar-events {
      display: flex;
      flex-direction: column;
      gap: 2px;
    }

    .calendar-event {
      font-size: 10px;
      padding: 2px 4px;
      border-radius: 4px;
      color: var(--white);
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }    /* Events List */
    .events-section {
      background: var(--white);
      border-radius: var(--border-radius);
      padding: 32px;
      box-shadow: var(--shadow-medium);
      animation: fadeInUp 0.8s ease-out 0.2s both;
    }

    .section-header {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 24px;
    }

    .section-icon {
      width: 48px;
      height: 48px;
      background: var(--gradient-primary);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--white);
      font-size: 1.2rem;
    }

    .section-title {
      font-size: 1.5rem;
      font-weight: 600;
      color: var(--text-dark);
    }

    /* Events Table */
    .events-table {
      background: var(--white);
      border-radius: 12px;
      overflow: hidden;
      box-shadow: var(--shadow-light);
    }

    .events-table-header {
      background: var(--gradient-primary);
      color: var(--white);
      display: grid;
      grid-template-columns: 2fr 1.5fr 1fr 1fr 2fr 1fr;
      gap: 16px;
      padding: 16px 20px;
      font-weight: 600;
      font-size: 14px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .events-table-body {
      background: var(--white);
    }

    .event-row {
      display: grid;
      grid-template-columns: 2fr 1.5fr 1fr 1fr 2fr 1fr;
      gap: 16px;
      padding: 16px 20px;
      border-bottom: 1px solid rgba(0, 123, 255, 0.1);
      transition: var(--transition);
      align-items: center;
    }

    .event-row:hover {
      background: rgba(0, 123, 255, 0.05);
    }

    .event-row:last-child {
      border-bottom: none;
    }

    .table-col {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 14px;
      color: var(--text-dark);
    }

    .col-title {
      font-weight: 600;
    }

    .event-color-indicator {
      width: 12px;
      height: 12px;
      border-radius: 50%;
      flex-shrink: 0;
    }

    .col-date i, .col-time i {
      color: var(--text-light);
      font-size: 12px;
    }

    .type-badge {
      padding: 4px 12px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 500;
      text-transform: capitalize;
    }

    .type-meeting {
      background: rgba(0, 200, 200, 0.1);
      color: #00c8c8;
    }

    .type-exam {
      background: rgba(220, 53, 69, 0.1);
      color: var(--error);
    }

    .type-assignment {
      background: rgba(255, 140, 0, 0.1);
      color: #ff8c00;
    }

    .type-personal {
      background: rgba(40, 167, 69, 0.1);
      color: var(--success);
    }

    .type-general {
      background: rgba(108, 117, 125, 0.1);
      color: var(--blue-gray);
    }

    .col-actions {
      justify-content: center;
      gap: 8px;
    }

    .action-btn {
      width: 32px;
      height: 32px;
      border: none;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: var(--transition);
      font-size: 14px;
    }

    .edit-btn {
      background: rgba(0, 123, 255, 0.1);
      color: var(--primary-blue);
    }

    .edit-btn:hover {
      background: var(--primary-blue);
      color: var(--white);
      transform: scale(1.1);
    }

    .delete-btn {
      background: rgba(220, 53, 69, 0.1);
      color: var(--error);
    }

    .delete-btn:hover {
      background: var(--error);
      color: var(--white);
      transform: scale(1.1);
    }

    /* Calendar Month Display */
    .calendar-month-year {
      text-align: center;
      margin-bottom: 20px;
    }

    .calendar-month-year h3 {
      color: var(--primary-blue);
      font-size: 1.4rem;
      font-weight: 600;
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
    }    /* Responsive Design */
    @media (max-width: 968px) {
      .dashboard-grid {
        grid-template-columns: 1fr;
        gap: 24px;
      }

      .calendar-grid {
        font-size: 12px;
      }

      .calendar-day {
        min-height: 80px;
        padding: 4px;
      }

      .events-table-header,
      .event-row {
        grid-template-columns: 1fr;
        gap: 8px;
      }

      .table-col {
        justify-content: space-between;
      }

      .table-col::before {
        content: attr(data-label) ":";
        font-weight: 600;
        color: var(--text-light);
        margin-right: 8px;
      }

      .events-table-header {
        display: none;
      }

      .event-row {
        border: 1px solid rgba(0, 123, 255, 0.1);
        margin-bottom: 12px;
        border-radius: 8px;
        padding: 16px;
      }
    }

    @media (max-width: 768px) {
      .main-content .container {
        padding: 24px 16px;
      }

      .page-title {
        font-size: 2.2rem;
      }

      .event-form,
      .calendar-container,
      .events-section {
        padding: 24px;
      }

      .calendar-day {
        min-height: 60px;
      }

      .calendar-day-header {
        padding: 12px 4px;
        font-size: 12px;
      }
    }

    @media (max-width: 480px) {
      .page-title {
        font-size: 1.8rem;
      }

      .calendar-grid {
        gap: 0;
      }

      .calendar-day {
        min-height: 50px;
        padding: 2px;
      }

      .calendar-date {
        font-size: 12px;
      }
    }
  </style>
</head>
<body>
  <!-- Animated Background -->
  <div class="animated-background">
    <div class="floating-shape"></div>
    <div class="floating-shape"></div>
    <div class="floating-shape"></div>
    <div class="floating-shape"></div>
  </div>
  <!-- Header -->
  <header class="header">
    <div class="container">
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
    </div>
  </header>
  <!-- Main Container -->
  <main class="main-content">
    <div class="container">
    <!-- Page Header -->
    <div class="page-header">
      <h1 class="page-title">My Planning Calendar</h1>
      <p class="page-subtitle">Organize your schedule, manage events, and stay on top of your academic life with our intelligent planning system</p>
    </div>

    <!-- Notifications -->
    <?php if (!empty($message)): ?>
    <div class="notification <?php echo $messageType; ?>">
      <i class="fas <?php echo $messageType === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
      <?php echo htmlspecialchars($message); ?>
    </div>
    <?php endif; ?>

    <!-- Dashboard Grid -->
    <div class="dashboard-grid">
      <!-- Event Form -->
      <div class="event-form">
        <div class="form-header">
          <div class="form-icon">
            <i class="fas fa-plus"></i>
          </div>
          <h2 class="form-title">Add New Event</h2>
        </div>        <form method="POST" action="">
          <input type="hidden" name="action" value="add_event">
          
          <div class="form-group">
            <label class="form-label" for="title">
              <i class="fas fa-heading"></i> Title
            </label>
            <input type="text" id="title" name="title" class="form-input" placeholder="Event title" required>
          </div>

          <div class="form-group">
            <label class="form-label" for="start_time">
              <i class="fas fa-calendar"></i> Date
            </label>
            <input type="date" id="event_date" name="event_date" class="form-input" required>
          </div>

          <div class="form-group">
            <label class="form-label" for="start_time">
              <i class="fas fa-clock"></i> Time
            </label>
            <input type="time" id="start_time" name="start_time" class="form-input" placeholder="--:-- --" required>
          </div>

          <div class="form-group">
            <label class="form-label" for="event_type">
              <i class="fas fa-tag"></i> Type
            </label>
            <select id="event_type" name="event_type" class="form-select">
              <option value="" disabled selected>Select type</option>
              <option value="meeting">Meeting</option>
              <option value="exam">Exam</option>
              <option value="assignment">Assignment</option>
              <option value="personal">Personal</option>
              <option value="study">Study</option>
              <option value="other">Other</option>
            </select>
          </div>

          <div class="form-group">
            <label class="form-label" for="description">
              <i class="fas fa-align-left"></i> Description
            </label>
            <textarea id="description" name="description" class="form-textarea" placeholder="Event details" rows="4"></textarea>
          </div>

          <button type="submit" class="btn-primary">
            <i class="fas fa-plus"></i>
            ADD EVENT
          </button>
        </form>
      </div>      <!-- Calendar -->
      <div class="calendar-container">
        <div class="calendar-header">
          <h2 class="calendar-title">
            <i class="fas fa-calendar-alt"></i>
            Calendar View
          </h2>
        </div>
        
        <div class="calendar-month-year">
          <h3><?php echo date('F Y'); ?></h3>
        </div>

        <div class="calendar-grid">
          <div class="calendar-day-header">Mon</div>
          <div class="calendar-day-header">Tue</div>
          <div class="calendar-day-header">Wed</div>
          <div class="calendar-day-header">Thu</div>
          <div class="calendar-day-header">Fri</div>
          <div class="calendar-day-header">Sat</div>
          <div class="calendar-day-header">Sun</div>
          
          <?php
          $first_day_of_month = date('N', strtotime($current_month_start)) - 1;
          $days_in_month = date('t', strtotime($current_month_start));
          
          // Add empty cells for days before the start of the month
          for ($i = 0; $i < $first_day_of_month; $i++) {
              echo '<div class="calendar-day empty"></div>';
          }
          
          // Add cells for each day of the month
          for ($day = 1; $day <= $days_in_month; $day++) {
              $date = date('Y-m-d', strtotime("$current_month_start +".($day-1)." days"));
              $is_today = ($date == $today) ? 'today' : '';
              $has_events = isset($calendar_events[$date]) ? 'has-events' : '';
              
              echo '<div class="calendar-day ' . $is_today . ' ' . $has_events . '">';
              echo '<div class="calendar-date">' . $day . '</div>';
              
              if (isset($calendar_events[$date])) {
                  echo '<div class="calendar-events">';
                  foreach ($calendar_events[$date] as $event) {
                      echo '<div class="calendar-event" style="background-color: ' . htmlspecialchars($event['color']) . '">' . 
                           htmlspecialchars(substr($event['title'], 0, 15)) . (strlen($event['title']) > 15 ? '...' : '') . '</div>';
                  }
                  echo '</div>';
              }
              
              echo '</div>';
          }
          ?>
        </div>
      </div>
    </div>    <!-- Events List -->
    <div class="events-section">
      <div class="section-header">
        <div class="section-icon">
          <i class="fas fa-list"></i>
        </div>
        <h2 class="section-title">My Events</h2>
      </div>

      <?php if (empty($events)): ?>
      <div class="empty-state">
        <i class="fas fa-calendar-times"></i>
        <h3>No Events Found</h3>
        <p>You haven't created any events yet. Start by adding your first event above!</p>
      </div>
      <?php else: ?>
      <div class="events-table">
        <div class="events-table-header">
          <div class="table-col col-title">TITLE</div>
          <div class="table-col col-date">DATE</div>
          <div class="table-col col-time">TIME</div>
          <div class="table-col col-type">TYPE</div>
          <div class="table-col col-description">DESCRIPTION</div>
          <div class="table-col col-actions">ACTIONS</div>
        </div>
        
        <div class="events-table-body">
          <?php foreach ($events as $event): 
            $event_date = date('d M Y', strtotime($event['start_time']));
            $event_time = date('H:i', strtotime($event['start_time']));
            $event_type = ucfirst($event['location'] ?? 'General'); // Using location as type for now
          ?>
          <div class="event-row">
            <div class="table-col col-title">
              <div class="event-color-indicator" style="background-color: <?php echo htmlspecialchars($event['color']); ?>"></div>
              <?php echo htmlspecialchars($event['title']); ?>
            </div>
            <div class="table-col col-date">
              <i class="fas fa-calendar"></i>
              <?php echo $event_date; ?>
            </div>
            <div class="table-col col-time">
              <i class="fas fa-clock"></i>
              <?php echo $event_time; ?>
            </div>
            <div class="table-col col-type">
              <span class="type-badge type-<?php echo strtolower($event_type); ?>">
                <?php echo $event_type; ?>
              </span>
            </div>
            <div class="table-col col-description">
              <?php echo htmlspecialchars($event['description'] ?? 'No description'); ?>
            </div>
            <div class="table-col col-actions">
              <button class="action-btn edit-btn" onclick="editEvent(<?php echo $event['id']; ?>)">
                <i class="fas fa-edit"></i>
              </button>
              <form method="POST" style="display: inline;" onsubmit="return confirmDelete('<?php echo htmlspecialchars($event['title']); ?>')">
                <input type="hidden" name="action" value="delete_event">
                <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                <button type="submit" class="action-btn delete-btn">
                  <i class="fas fa-trash"></i>
                </button>
              </form>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </main>
  <script>
    // Initialize page
    document.addEventListener('DOMContentLoaded', function() {
      // Set minimum date to today for event date
      const eventDateInput = document.getElementById('event_date');
      const today = new Date().toISOString().split('T')[0];
      eventDateInput.min = today;

      // Form validation
      const form = document.querySelector('form');
      form.addEventListener('submit', function(e) {
        const title = document.getElementById('title').value.trim();
        const eventDate = document.getElementById('event_date').value;
        const startTime = document.getElementById('start_time').value;
        const eventType = document.getElementById('event_type').value;
        
        if (!title || !eventDate || !startTime) {
          e.preventDefault();
          Swal.fire({
            icon: 'warning',
            title: 'Missing Information',
            text: 'Please fill in all required fields (Title, Date, and Time).',
            confirmButtonColor: '#007BFF'
          });
          return;
        }

        // Check if event date is not in the past
        const selectedDate = new Date(eventDate + ' ' + startTime);
        const now = new Date();
        if (selectedDate < now) {
          e.preventDefault();
          Swal.fire({
            icon: 'warning',
            title: 'Invalid Date',
            text: 'Event date and time cannot be in the past.',
            confirmButtonColor: '#007BFF'
          });
          return;
        }
      });

      // Add responsive labels for mobile
      const tableRows = document.querySelectorAll('.event-row .table-col');
      const labels = ['Title', 'Date', 'Time', 'Type', 'Description', 'Actions'];
      
      tableRows.forEach((col, index) => {
        const labelIndex = index % 6;
        col.setAttribute('data-label', labels[labelIndex]);
      });

      // Add smooth animations to elements
      const animatedElements = document.querySelectorAll('.event-row, .calendar-day');
      const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            entry.target.style.animation = 'fadeInUp 0.6s ease-out';
          }
        });
      });

      animatedElements.forEach(el => observer.observe(el));
    });

    // Edit event function (placeholder)
    function editEvent(eventId) {
      Swal.fire({
        icon: 'info',
        title: 'Edit Event',
        text: 'Event editing feature will be available soon!',
        confirmButtonColor: '#007BFF'
      });
    }

    // Delete confirmation
    function confirmDelete(eventTitle) {
      return confirm(`Are you sure you want to delete the event "${eventTitle}"? This action cannot be undone.`);
    }

    // Auto-hide notifications
    const notifications = document.querySelectorAll('.notification');
    notifications.forEach(notification => {
      setTimeout(() => {
        notification.style.animation = 'slideOut 0.5s ease-in forwards';
        setTimeout(() => {
          notification.remove();
        }, 500);
      }, 5000);
    });

    // Add slideOut animation
    const style = document.createElement('style');
    style.textContent = `
      @keyframes slideOut {
        to {
          opacity: 0;
          transform: translateX(100%);
        }
      }
      
      @keyframes fadeInUp {
        from {
          opacity: 0;
          transform: translateY(20px);
        }
        to {
          opacity: 1;
          transform: translateY(0);
        }
      }
    `;
    document.head.appendChild(style);
  </script>
</body>
</html>
