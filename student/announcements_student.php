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

// Fetch announcements from database
$sql = "SELECT a.*, u.fullname as author_name 
        FROM announcements a 
        LEFT JOIN users u ON a.author_id = u.id 
        WHERE a.status = 'published' 
        AND (a.expiry_date IS NULL OR a.expiry_date > NOW())
        AND a.deleted_at IS NULL
        ORDER BY a.importance DESC, a.published_at DESC";

$stmt = $conn->prepare($sql);
$stmt->execute();
$announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch viewed announcements for current user
$viewed_sql = "SELECT announcement_id FROM announcement_views WHERE user_id = ?";
$viewed_stmt = $conn->prepare($viewed_sql);
$viewed_stmt->execute([$user_id]);
$viewed_announcements = [];
while ($row = $viewed_stmt->fetch(PDO::FETCH_ASSOC)) {
    $viewed_announcements[] = $row['announcement_id'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Announcements | EduLearn</title>
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
    }

    /* Main Content */
    .main-content {
      padding: 40px 0;
    }

    .page-header {
      text-align: center;
      margin-bottom: 40px;
      animation: fadeInUp 0.8s ease-out;
    }

    .page-title {
      font-family: 'Poppins', sans-serif;
      font-size: 3rem;
      font-weight: 700;
      background: var(--gradient-primary);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      margin-bottom: 16px;
      position: relative;
    }

    .page-subtitle {
      color: var(--text-light);
      font-size: 1.2rem;
      max-width: 600px;
      margin: 0 auto;
    }

    /* Toolbar */
    .announcements-toolbar {
      background: var(--white);
      border-radius: var(--border-radius);
      padding: 24px;
      margin-bottom: 32px;
      box-shadow: var(--shadow-medium);
      display: flex;
      gap: 16px;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      animation: slideInDown 0.6s ease-out;
    }

    .toolbar-left {
      display: flex;
      align-items: center;
      gap: 16px;
    }

    .stats-badge {
      background: rgba(0, 123, 255, 0.1);
      color: var(--primary-blue);
      padding: 8px 16px;
      border-radius: 50px;
      font-size: 14px;
      font-weight: 500;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .unread-count {
      background: var(--error);
      color: white;
      font-size: 12px;
      font-weight: 600;
      padding: 4px 8px;
      border-radius: 50px;
      min-width: 20px;
      text-align: center;
    }

    .btn-primary {
      background: var(--gradient-primary);
      color: white;
      border: none;
      padding: 12px 24px;
      border-radius: 50px;
      font-weight: 600;
      font-size: 14px;
      cursor: pointer;
      transition: var(--transition);
      display: flex;
      align-items: center;
      gap: 8px;
      box-shadow: var(--shadow-light);
      position: relative;
      overflow: hidden;
    }

    .btn-primary::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
      transition: var(--transition);
    }

    .btn-primary:hover::before {
      left: 100%;
    }

    .btn-primary:hover {
      transform: translateY(-3px);
      box-shadow: var(--shadow-heavy);
    }

    .btn-primary:active {
      transform: translateY(-1px);
    }

    /* Filter Tabs */
    .filter-tabs {
      display: flex;
      gap: 8px;
      background: rgba(0, 123, 255, 0.05);
      padding: 6px;
      border-radius: 50px;
      margin-bottom: 24px;
      animation: slideInDown 0.7s ease-out;
    }

    .filter-tab {
      padding: 10px 20px;
      border: none;
      background: transparent;
      color: var(--text-light);
      font-weight: 500;
      border-radius: 50px;
      cursor: pointer;
      transition: var(--transition);
    }

    .filter-tab.active {
      background: var(--primary-blue);
      color: white;
      box-shadow: var(--shadow-light);
    }

    .filter-tab:hover:not(.active) {
      background: rgba(0, 123, 255, 0.1);
      color: var(--primary-blue);
    }

    /* Announcements List */
    .announcements-list {
      display: flex;
      flex-direction: column;
      gap: 24px;
      animation: fadeIn 1s ease-out;
    }

    .announcement-card {
      background: var(--white);
      border-radius: var(--border-radius);
      padding: 32px;
      box-shadow: var(--shadow-medium);
      transition: var(--transition);
      position: relative;
      overflow: hidden;
      border: 1px solid rgba(0, 123, 255, 0.1);
      cursor: pointer;
    }

    .announcement-card::before {
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

    .announcement-card:hover {
      transform: translateY(-8px);
      box-shadow: var(--shadow-heavy);
    }

    .announcement-card:hover::before {
      transform: scaleX(1);
    }

    .announcement-card.unread {
      border-left: 6px solid var(--primary-blue);
      background: linear-gradient(135deg, rgba(0, 123, 255, 0.02), var(--white));
    }

    .announcement-card.unread::before {
      background: var(--gradient-accent);
    }

    .announcement-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      margin-bottom: 20px;
      gap: 16px;
    }

    .announcement-title {
      font-family: 'Poppins', sans-serif;
      font-size: 1.5rem;
      font-weight: 600;
      color: var(--text-dark);
      margin-bottom: 8px;
      flex: 1;
    }

    .announcement-meta {
      display: flex;
      flex-direction: column;
      align-items: flex-end;
      gap: 8px;
    }

    .importance-badge {
      padding: 6px 12px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .importance-badge.high {
      background: rgba(220, 53, 69, 0.1);
      color: var(--error);
    }

    .importance-badge.medium {
      background: rgba(255, 193, 7, 0.1);
      color: var(--warning);
    }

    .importance-badge.low {
      background: rgba(40, 167, 69, 0.1);
      color: var(--success);
    }

    .announcement-date {
      color: var(--text-light);
      font-size: 14px;
      display: flex;
      align-items: center;
      gap: 4px;
    }

    .announcement-content {
      color: var(--text-dark);
      line-height: 1.6;
      margin-bottom: 20px;
    }

    .announcement-content.preview {
      display: -webkit-box;
      -webkit-line-clamp: 3;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }

    .announcement-content.expanded {
      display: block;
    }

    .announcement-footer {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding-top: 16px;
      border-top: 1px solid rgba(0, 123, 255, 0.1);
    }

    .announcement-author {
      color: var(--text-light);
      font-size: 14px;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .read-status {
      display: flex;
      align-items: center;
      gap: 8px;
      color: var(--text-light);
      font-size: 14px;
    }

    .read-indicator {
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background: var(--success);
    }

    .unread-indicator {
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background: var(--primary-blue);
      animation: pulse 2s ease-in-out infinite;
    }

    @keyframes pulse {
      0%, 100% { opacity: 1; }
      50% { opacity: 0.5; }
    }

    .expand-btn {
      background: none;
      border: none;
      color: var(--primary-blue);
      font-weight: 500;
      cursor: pointer;
      transition: var(--transition);
      padding: 4px 8px;
      border-radius: 4px;
    }

    .expand-btn:hover {
      background: rgba(0, 123, 255, 0.1);
    }

    /* Empty State */
    .empty-state {
      text-align: center;
      padding: 80px 20px;
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

    /* Loading State */
    .loading-skeleton {
      background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
      background-size: 200% 100%;
      animation: loading 1.5s infinite;
      border-radius: 8px;
    }

    @keyframes loading {
      0% { background-position: 200% 0; }
      100% { background-position: -200% 0; }
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

      .announcements-toolbar {
        flex-direction: column;
        align-items: stretch;
      }

      .toolbar-left {
        justify-content: center;
      }

      .announcement-header {
        flex-direction: column;
        align-items: flex-start;
      }

      .announcement-meta {
        align-items: flex-start;
        flex-direction: row;
        gap: 12px;
      }

      .filter-tabs {
        flex-wrap: wrap;
        justify-content: center;
      }
    }
  </style>
</head>
<body>
  <div class="animated-background">
    <div class="floating-shape shape-1"></div>
    <div class="floating-shape shape-2"></div>
    <div class="floating-shape shape-3"></div>
  </div>

  <header class="header">
    <div class="container">
      <div class="header-content">
        <a href="#" class="logo">
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

  <main class="main-content">
    <div class="container">
      <div class="page-header">
        <h1 class="page-title">Announcements</h1>
        <p class="page-subtitle">Stay updated with the latest news, updates, and important information from your institution</p>
      </div>

      <div class="announcements-toolbar">
        <div class="toolbar-left">
          <div class="stats-badge">
            <i class="fas fa-bullhorn"></i>
            <span id="totalCount"><?php echo count($announcements); ?> Total</span>
          </div>
          <div class="stats-badge">
            <i class="fas fa-envelope"></i>
            <span id="unreadCount">
              <?php 
              $unread = 0;
              foreach ($announcements as $announcement) {
                if (!in_array($announcement['id'], $viewed_announcements)) {
                  $unread++;
                }
              }
              echo $unread;
              ?> Unread
            </span>
            <?php if ($unread > 0): ?>
            <span class="unread-count"><?php echo $unread; ?></span>
            <?php endif; ?>
          </div>
        </div>
        <button class="btn-primary" onclick="markAllAsRead()">
          <i class="fas fa-check-double"></i>
          Mark All as Read
        </button>
      </div>

      <div class="filter-tabs">
        <button class="filter-tab active" data-filter="all">
          <i class="fas fa-list"></i> All
        </button>
        <button class="filter-tab" data-filter="unread">
          <i class="fas fa-envelope"></i> Unread
        </button>
        <button class="filter-tab" data-filter="read">
          <i class="fas fa-envelope-open"></i> Read
        </button>
        <button class="filter-tab" data-filter="important">
          <i class="fas fa-exclamation-triangle"></i> Important
        </button>
      </div>

      <div class="announcements-list" id="announcementsList">
        <?php if (empty($announcements)): ?>
        <div class="empty-state">
          <i class="fas fa-bullhorn"></i>
          <h3>No announcements yet</h3>
          <p>Check back later for updates and important information</p>
        </div>
        <?php else: ?>
        <?php foreach ($announcements as $announcement): ?>
        <div class="announcement-card <?php echo in_array($announcement['id'], $viewed_announcements) ? 'read' : 'unread'; ?>" 
             data-id="<?php echo $announcement['id']; ?>" 
             data-importance="<?php echo $announcement['importance']; ?>"
             onclick="markAsRead(<?php echo $announcement['id']; ?>)">
          <div class="announcement-header">
            <div>
              <h2 class="announcement-title"><?php echo htmlspecialchars($announcement['title']); ?></h2>
            </div>
            <div class="announcement-meta">
              <span class="importance-badge <?php echo $announcement['importance']; ?>">
                <?php echo ucfirst($announcement['importance']); ?>
              </span>
              <div class="announcement-date">
                <i class="fas fa-calendar"></i>
                <?php echo date('M d, Y', strtotime($announcement['published_at'])); ?>
              </div>
            </div>
          </div>
          
          <div class="announcement-content preview" id="content-<?php echo $announcement['id']; ?>">
            <?php echo nl2br(htmlspecialchars($announcement['content'])); ?>
          </div>
          
          <?php if (strlen($announcement['content']) > 300): ?>
          <button class="expand-btn" onclick="event.stopPropagation(); toggleContent(<?php echo $announcement['id']; ?>)">
            <span id="expand-text-<?php echo $announcement['id']; ?>">Read more</span>
            <i class="fas fa-chevron-down" id="expand-icon-<?php echo $announcement['id']; ?>"></i>
          </button>
          <?php endif; ?>
          
          <div class="announcement-footer">
            <div class="announcement-author">
              <i class="fas fa-user"></i>
              By <?php echo htmlspecialchars($announcement['author_name'] ?? 'Administrator'); ?>
            </div>
            <div class="read-status">
              <?php if (in_array($announcement['id'], $viewed_announcements)): ?>
              <div class="read-indicator"></div>
              <span>Read</span>
              <?php else: ?>
              <div class="unread-indicator"></div>
              <span>Unread</span>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </main>

  <script>
    // Global variables
    let announcements = <?php echo json_encode($announcements); ?>;
    let viewedAnnouncements = <?php echo json_encode($viewed_announcements); ?>;
    const userId = <?php echo $user_id; ?>;

    // Initialize the application
    document.addEventListener('DOMContentLoaded', function() {
      setupFilterTabs();
      setupAnimations();
      updateStats();
      
      // Add smooth loading animation
      setTimeout(() => {
        document.body.style.opacity = '1';
      }, 100);
    });

    // Setup filter tabs functionality
    function setupFilterTabs() {
      const filterTabs = document.querySelectorAll('.filter-tab');
      
      filterTabs.forEach(tab => {
        tab.addEventListener('click', function() {
          // Remove active class from all tabs
          filterTabs.forEach(t => t.classList.remove('active'));
          // Add active class to clicked tab
          this.classList.add('active');
          
          // Filter announcements
          const filter = this.getAttribute('data-filter');
          filterAnnouncements(filter);
        });
      });
    }

    // Filter announcements based on selected tab
    function filterAnnouncements(filter) {
      const announcementCards = document.querySelectorAll('.announcement-card');
      let visibleCount = 0;
      
      announcementCards.forEach(card => {
        const announcementId = parseInt(card.getAttribute('data-id'));
        const importance = card.getAttribute('data-importance');
        const isRead = viewedAnnouncements.includes(announcementId);
        
        let shouldShow = false;
        
        switch(filter) {
          case 'all':
            shouldShow = true;
            break;
          case 'unread':
            shouldShow = !isRead;
            break;
          case 'read':
            shouldShow = isRead;
            break;
          case 'important':
            shouldShow = importance === 'high';
            break;
        }
        
        if (shouldShow) {
          card.style.display = 'block';
          card.style.animation = 'fadeInUp 0.4s ease-out';
          visibleCount++;
        } else {
          card.style.display = 'none';
        }
      });

      // Show empty state if no announcements match filter
      showEmptyState(visibleCount === 0, filter);
    }

    // Show/hide empty state
    function showEmptyState(show, filter) {
      const announcementsList = document.getElementById('announcementsList');
      let emptyState = document.querySelector('.empty-state-filter');
      
      if (show && !emptyState) {
        let message = '';
        let icon = '';
        
        switch(filter) {
          case 'unread':
            message = 'No unread announcements';
            icon = 'fas fa-envelope-open';
            break;
          case 'read':
            message = 'No read announcements';
            icon = 'fas fa-envelope';
            break;
          case 'important':
            message = 'No important announcements';
            icon = 'fas fa-exclamation-triangle';
            break;
          default:
            message = 'No announcements found';
            icon = 'fas fa-bullhorn';
        }
        
        emptyState = document.createElement('div');
        emptyState.className = 'empty-state empty-state-filter';
        emptyState.innerHTML = `
          <i class="${icon}"></i>
          <h3>${message}</h3>
          <p>Try switching to a different filter</p>
        `;
        announcementsList.appendChild(emptyState);
      } else if (!show && emptyState) {
        emptyState.remove();
      }
    }

    // Toggle content expand/collapse
    function toggleContent(announcementId) {
      const content = document.getElementById(`content-${announcementId}`);
      const expandText = document.getElementById(`expand-text-${announcementId}`);
      const expandIcon = document.getElementById(`expand-icon-${announcementId}`);
      
      if (content.classList.contains('preview')) {
        content.classList.remove('preview');
        content.classList.add('expanded');
        expandText.textContent = 'Read less';
        expandIcon.style.transform = 'rotate(180deg)';
      } else {
        content.classList.remove('expanded');
        content.classList.add('preview');
        expandText.textContent = 'Read more';
        expandIcon.style.transform = 'rotate(0deg)';
      }
    }

    // Mark announcement as read
    function markAsRead(announcementId) {
      if (viewedAnnouncements.includes(announcementId)) {
        return; // Already read
      }

      // Update local state immediately for better UX
      viewedAnnouncements.push(announcementId);
      
      // Update UI
      const card = document.querySelector(`[data-id="${announcementId}"]`);
      card.classList.remove('unread');
      card.classList.add('read');
      
      // Update read status indicator
      const readStatus = card.querySelector('.read-status');
      readStatus.innerHTML = `
        <div class="read-indicator"></div>
        <span>Read</span>
      `;
      
      // Update stats
      updateStats();
      
      // Show success animation
      showReadAnimation(card);

      // Send AJAX request to server (simulate API call)
      setTimeout(() => {
        console.log(`Marked announcement ${announcementId} as read for user ${userId}`);
      }, 100);
    }

    // Mark all announcements as read
    function markAllAsRead() {
      const unreadAnnouncements = announcements.filter(ann => 
        !viewedAnnouncements.includes(ann.id)
      );
      
      if (unreadAnnouncements.length === 0) {
        Swal.fire({
          icon: 'info',
          title: 'All Caught Up!',
          text: 'All announcements are already marked as read',
          confirmButtonColor: '#007BFF'
        });
        return;
      }

      Swal.fire({
        title: 'Mark All as Read?',
        text: `This will mark ${unreadAnnouncements.length} announcements as read`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#007BFF',
        cancelButtonColor: '#6C757D',
        confirmButtonText: 'Yes, mark all!'
      }).then((result) => {
        if (result.isConfirmed) {
          // Update local state
          unreadAnnouncements.forEach(ann => {
            viewedAnnouncements.push(ann.id);
          });
          
          // Update all cards
          document.querySelectorAll('.announcement-card.unread').forEach(card => {
            card.classList.remove('unread');
            card.classList.add('read');
            
            const readStatus = card.querySelector('.read-status');
            readStatus.innerHTML = `
              <div class="read-indicator"></div>
              <span>Read</span>
            `;
            
            showReadAnimation(card);
          });
          
          // Update stats
          updateStats();
          
          Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: 'All announcements marked as read',
            confirmButtonColor: '#007BFF',
            timer: 2000
          });
        }
      });
    }

    // Show read animation
    function showReadAnimation(card) {
      card.style.transform = 'scale(1.02)';
      setTimeout(() => {
        card.style.transform = '';
      }, 200);
    }

    // Update statistics
    function updateStats() {
      const totalCount = announcements.length;
      const unreadCount = announcements.filter(ann => 
        !viewedAnnouncements.includes(ann.id)
      ).length;
      
      document.getElementById('totalCount').textContent = `${totalCount} Total`;
      document.getElementById('unreadCount').innerHTML = `${unreadCount} Unread`;
      
      // Update unread badge
      const unreadBadge = document.querySelector('.unread-count');
      if (unreadCount > 0) {
        if (!unreadBadge) {
          const badge = document.createElement('span');
          badge.className = 'unread-count';
          badge.textContent = unreadCount;
          document.getElementById('unreadCount').appendChild(badge);
        } else {
          unreadBadge.textContent = unreadCount;
        }
      } else if (unreadBadge) {
        unreadBadge.remove();
      }
    }

    // Setup animations
    function setupAnimations() {
      // Stagger animation for announcement cards
      const cards = document.querySelectorAll('.announcement-card');
      cards.forEach((card, index) => {
        card.style.animationDelay = `${index * 0.1}s`;
      });

      // Intersection Observer for scroll animations
      const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            entry.target.style.animation = 'fadeInUp 0.6s ease-out forwards';
          }
        });
      }, { threshold: 0.1 });

      cards.forEach(card => {
        observer.observe(card);
      });
    }

    // Keyboard shortcuts
    document.addEventListener('keydown', function(event) {
      if (event.ctrlKey && event.key === 'a') {
        event.preventDefault();
        markAllAsRead();
      }
    });

    // Add smooth scroll behavior
    document.documentElement.style.scrollBehavior = 'smooth';
  </script>
</body>
</html>
