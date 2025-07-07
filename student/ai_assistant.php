<?php 
session_start();

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
    header('Location: ../auth/login.php');
    exit;
}

include('../config/connectiondb.php'); 
include('../config/api_config.php'); 

$user_name = $_SESSION['user_fullname'] ?? 'Student';
$user_initials = strtoupper(substr($user_name, 0, 2));
$user_id = $_SESSION['user_id'];

// Fetch modules with notes for the current user
$sql = "SELECT DISTINCT category 
        FROM notes 
        WHERE student_id = ? AND deleted_at IS NULL AND category IS NOT NULL
        ORDER BY category";

$stmt = $conn->prepare($sql);
$stmt->execute([$user_id]);
$modules_result = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Also get sample notes per module for AI processing
$notes_by_module = [];
foreach ($modules_result as $module) {
    $category = $module['category'];
    $notes_sql = "SELECT title, content 
                  FROM notes 
                  WHERE student_id = ? AND category = ? AND deleted_at IS NULL 
                  ORDER BY created_at DESC 
                  LIMIT 10";
    
    $notes_stmt = $conn->prepare($notes_sql);
    $notes_stmt->execute([$user_id, $category]);
    $notes_by_module[$category] = $notes_stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>AI Study Assistant | EduLearn</title>
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
    .shape-4 { width: 180px; height: 180px; top: 40%; right: 40%; animation-delay: 3s; }

    @keyframes float-gentle {
      0%, 100% { transform: translateY(0px) rotate(0deg); }
      50% { transform: translateY(-20px) rotate(180deg); }
    }

    /* Header Navigation */
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

    /* Main Container */
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

    /* API Status Indicator */
    .api-status {
      margin-top: 20px;
      padding: 15px;
      background: rgba(255, 255, 255, 0.1);
      border-radius: 10px;
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .api-status-indicator {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 10px;
    }

    .api-status-indicator i {
      font-size: 12px;
    }

    .api-status.configured .api-status-indicator i {
      color: #28a745;
    }

    .api-status.not-configured .api-status-indicator i {
      color: #dc3545;
    }

    .api-status.checking .api-status-indicator i {
      color: #ffc107;
      animation: pulse 1s infinite;
    }

    /* AI Assistant Container */
    .assistant-container {
      background: var(--white);
      border-radius: var(--border-radius);
      padding: 32px;
      box-shadow: var(--shadow-medium);
      margin-bottom: 32px;
      border: 1px solid rgba(0, 123, 255, 0.1);
      position: relative;
      overflow: hidden;
      animation: fadeInUp 0.8s ease-out 0.2s both;
    }

    .assistant-container::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 4px;
      background: var(--gradient-primary);
    }

    .assistant-header {
      display: flex;
      align-items: center;
      gap: 16px;
      margin-bottom: 32px;
    }

    .assistant-icon {
      width: 64px;
      height: 64px;
      background: var(--gradient-primary);
      border-radius: 16px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-size: 1.5rem;
      animation: float-icon 3s ease-in-out infinite;
    }

    @keyframes float-icon {
      0%, 100% { transform: translateY(0px); }
      50% { transform: translateY(-5px); }
    }

    .assistant-info h2 {
      font-family: 'Poppins', sans-serif;
      font-size: 1.8rem;
      font-weight: 600;
      color: var(--text-dark);
      margin-bottom: 8px;
    }

    .assistant-info p {
      color: var(--text-light);
      font-size: 14px;
    }

    /* Input Area */
    .input-section {
      margin-bottom: 32px;
    }

    .form-group {
      margin-bottom: 24px;
    }

    .form-label {
      display: block;
      margin-bottom: 8px;
      font-weight: 500;
      color: var(--text-dark);
      font-size: 14px;
    }

    .form-select, .form-textarea {
      width: 100%;
      padding: 16px 20px;
      border: 2px solid rgba(0, 123, 255, 0.1);
      border-radius: 12px;
      font-size: 16px;
      color: var(--text-dark);
      background: var(--white);
      transition: var(--transition);
      font-family: inherit;
      resize: vertical;
    }

    .form-select {
      appearance: none;
      background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
      background-position: right 12px center;
      background-repeat: no-repeat;
      background-size: 16px;
      cursor: pointer;
    }

    .form-select:focus, .form-textarea:focus {
      outline: none;
      border-color: var(--primary-blue);
      box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
    }

    .form-textarea {
      min-height: 120px;
      line-height: 1.6;
    }

    /* Action Buttons */
    .actions-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 16px;
      margin-bottom: 32px;
    }

    .action-btn {
      background: var(--gradient-primary);
      color: white;
      border: none;
      padding: 16px 24px;
      border-radius: 12px;
      font-size: 14px;
      font-weight: 500;
      cursor: pointer;
      transition: var(--transition);
      position: relative;
      overflow: hidden;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      text-decoration: none;
    }

    .action-btn:hover {
      transform: translateY(-3px);
      box-shadow: var(--shadow-heavy);
    }

    .action-btn:active {
      transform: translateY(-1px);
    }

    .action-btn.secondary {
      background: rgba(108, 117, 125, 0.1);
      color: var(--blue-gray);
    }

    .action-btn.secondary:hover {
      background: var(--blue-gray);
      color: white;
    }

    .action-btn.accent {
      background: var(--gradient-accent);
    }

    .action-btn.loading {
      pointer-events: none;
      opacity: 0.7;
    }

    .action-btn .spinner {
      width: 16px;
      height: 16px;
      border: 2px solid rgba(255, 255, 255, 0.3);
      border-top: 2px solid white;
      border-radius: 50%;
      animation: spin 1s linear infinite;
      display: none;
    }

    .action-btn.loading .spinner {
      display: block;
    }

    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }

    /* Output Area */
    .output-area {
      background: var(--light-bg);
      border-radius: 12px;
      padding: 32px;
      border: 2px solid rgba(0, 123, 255, 0.1);
      transition: var(--transition);
      min-height: 200px;
      display: flex;
      align-items: center;
      justify-content: center;
      text-align: center;
      color: var(--text-light);
    }

    .output-area.has-content {
      background: var(--white);
      border-color: var(--primary-blue);
      text-align: left;
      align-items: flex-start;
    }

    .output-content {
      width: 100%;
    }

    .output-header {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 20px;
      padding-bottom: 16px;
      border-bottom: 2px solid var(--light-bg);
    }

    .output-icon {
      width: 40px;
      height: 40px;
      background: var(--gradient-accent);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
    }

    .output-text {
      line-height: 1.8;
      color: var(--text-dark);
    }

    /* Loading Animation */
    .loading-container {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 20px;
    }

    .loading-spinner {
      width: 60px;
      height: 60px;
      border: 4px solid rgba(0, 123, 255, 0.1);
      border-top: 4px solid var(--primary-blue);
      border-radius: 50%;
      animation: spin 1s linear infinite;
    }

    .loading-text {
      color: var(--text-light);
      font-size: 16px;
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

    @keyframes pulse {
      0%, 100% { opacity: 1; }
      50% { opacity: 0.5; }
    }

    @keyframes slideInRight {
      from {
        opacity: 0;
        transform: translateX(30px);
      }
      to {
        opacity: 1;
        transform: translateX(0);
      }
    }

    /* Responsive Design */
    @media (max-width: 768px) {
      .container {
        padding: 0 16px;
      }

      .page-title {
        font-size: 2.2rem;
      }

      .assistant-container {
        padding: 24px;
      }

      .actions-grid {
        grid-template-columns: 1fr;
      }

      .header-content {
        flex-direction: column;
        gap: 16px;
        height: auto;
        padding: 16px 0;
      }
    }

    @media (max-width: 480px) {
      .page-title {
        font-size: 1.8rem;
      }

      .assistant-container {
        padding: 20px;
      }

      .assistant-icon {
        width: 48px;
        height: 48px;
        font-size: 1.2rem;
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

  <!-- Navigation Header -->
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
  <main class="container">
    <div class="main-content">
      <!-- Header Section -->
      <div class="page-header">
        <h1 class="page-title">AI Study Assistant</h1>
        <p class="page-subtitle">
          Harness the power of artificial intelligence to enhance your learning experience. 
          Get intelligent summaries, generate quizzes, and receive personalized study guidance.
        </p>
        
        <!-- API Status Indicator -->
        <div class="api-status" id="apiStatus">
          <div class="api-status-indicator">
            <i class="fas fa-circle" id="statusIcon"></i>
            <span id="statusText">Checking AI Assistant status...</span>
          </div>
        </div>
      </div>

      <!-- AI Assistant Container -->
      <div class="assistant-container">
        <div class="assistant-header">
          <div class="assistant-icon">
            <i class="fas fa-robot"></i>
          </div>
          <div class="assistant-info">
            <h2>Your Personal AI Tutor</h2>
            <p>Select a module and let AI help you study smarter</p>
          </div>
        </div>

        <!-- Input Section -->
        <div class="input-section">
          <div class="form-group">
            <label class="form-label" for="moduleSelect">
              <i class="fas fa-book-open" style="margin-right: 8px; color: var(--primary-blue);"></i>
              Choose Your Module
            </label>
            <select id="moduleSelect" class="form-select">
              <option value="" disabled selected>Select a module to get AI assistance...</option>
              <?php foreach ($modules_result as $module): ?>
                <option value="<?php echo htmlspecialchars($module['category']); ?>">
                  <?php echo htmlspecialchars($module['category']); ?>
                  (<?php echo count($notes_by_module[$module['category']]); ?> notes)
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group">
            <label class="form-label" for="userNotes">
              <i class="fas fa-edit" style="margin-right: 8px; color: var(--primary-blue);"></i>
              Your Notes (Optional - AI can use your saved notes)
            </label>
            <textarea 
              id="userNotes" 
              class="form-textarea"
              placeholder="Paste additional notes here, or leave empty to use your saved notes from the selected module..."
            ></textarea>
          </div>

          <!-- Action Buttons -->
          <div class="actions-grid">
            <button class="action-btn" onclick="getSummary()">
              <i class="fas fa-file-alt"></i>
              <span>Generate Summary</span>
              <div class="spinner"></div>
            </button>
            
            <button class="action-btn" onclick="generateQuiz('mcq')">
              <i class="fas fa-list-ul"></i>
              <span>Create MCQ Quiz</span>
              <div class="spinner"></div>
            </button>
            
            <button class="action-btn" onclick="generateQuiz('truefalse')">
              <i class="fas fa-check-double"></i>
              <span>True/False Quiz</span>
              <div class="spinner"></div>
            </button>
            
            <button class="action-btn accent" onclick="explainConcepts()">
              <i class="fas fa-lightbulb"></i>
              <span>Explain Concepts</span>
              <div class="spinner"></div>
            </button>
            
            <button class="action-btn secondary" onclick="exportContent()">
              <i class="fas fa-download"></i>
              <span>Export Results</span>
              <div class="spinner"></div>
            </button>
            
            <button class="action-btn secondary" onclick="clearOutput()">
              <i class="fas fa-broom"></i>
              <span>Clear Results</span>
            </button>
          </div>
        </div>

        <!-- Output Area -->
        <div id="outputArea" class="output-area">
          <div>
            <i class="fas fa-magic" style="font-size: 2rem; color: var(--primary-blue); margin-bottom: 16px;"></i>
            <p>Ready to assist you! Select a module and choose an AI action to get started.</p>
          </div>
        </div>
      </div>
    </div>
  </main>

  <script>
    // Configuration API Gemini
    <?php
    try {
        $api_key = getGeminiApiKey();
        $api_endpoint = getGeminiEndpoint();
        echo "const GEMINI_API_KEY = '$api_key';\n";
        echo "    const GEMINI_ENDPOINT = '$api_endpoint';\n";
        echo "    const API_CONFIGURED = true;\n";
    } catch (Exception $e) {
        echo "const GEMINI_API_KEY = null;\n";
        echo "    const GEMINI_ENDPOINT = null;\n";
        echo "    const API_CONFIGURED = false;\n";
        echo "    const API_ERROR = '" . addslashes($e->getMessage()) . "';\n";
    }
    ?>
    
    // Data des notes par module depuis PHP
    const notesByModule = <?php echo json_encode($notes_by_module); ?>;
    
    // Variables globales
    let currentContent = '';
    let currentModule = '';

    // Initialize page
    document.addEventListener('DOMContentLoaded', function() {
      updateAPIStatus();
      
      // Add smooth animations to elements
      const animatedElements = document.querySelectorAll('.action-btn, .form-group');
      const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            entry.target.style.animation = 'fadeInUp 0.6s ease-out';
          }
        });
      });

      animatedElements.forEach(el => observer.observe(el));
    });

    // Update API status
    function updateAPIStatus() {
      const statusElement = document.getElementById('apiStatus');
      const iconElement = document.getElementById('statusIcon');
      const textElement = document.getElementById('statusText');
      
      if (API_CONFIGURED) {
        statusElement.className = 'api-status configured';
        iconElement.className = 'fas fa-circle';
        textElement.textContent = 'AI Assistant: Ready and configured';
      } else {
        statusElement.className = 'api-status not-configured';
        iconElement.className = 'fas fa-circle';
        textElement.textContent = 'AI Assistant: Configuration required';
      }
    }

    // Get notes content for AI processing
    function getNotesContent() {
      const moduleSelect = document.getElementById('moduleSelect');
      const userNotes = document.getElementById('userNotes');
      const selectedModule = moduleSelect.value;
      
      if (!selectedModule) {
        Swal.fire({
          icon: 'warning',
          title: 'Module Required',
          text: 'Please select a module first',
          confirmButtonColor: '#007BFF'
        });
        return null;
      }

      currentModule = selectedModule;
      
      // Use manual notes if provided, otherwise use saved notes
      if (userNotes.value.trim()) {
        return userNotes.value.trim();
      } else {
        const moduleNotes = notesByModule[selectedModule];
        if (!moduleNotes || moduleNotes.length === 0) {
          Swal.fire({
            icon: 'info',
            title: 'No Notes Found',
            text: 'No saved notes found for this module. Please add some notes in the textarea or go to Notes page to save some notes.',
            confirmButtonColor: '#007BFF'
          });
          return null;
        }
        
        return moduleNotes.map(note => 
          `Title: ${note.title}\nContent: ${note.content}`
        ).join('\n\n');
      }
    }

    // Show loading state
    function showLoading(button) {
      button.classList.add('loading');
      const outputArea = document.getElementById('outputArea');
      outputArea.className = 'output-area has-content';
      outputArea.innerHTML = `
        <div class="loading-container">
          <div class="loading-spinner"></div>
          <div class="loading-text">AI is processing your request...</div>
        </div>
      `;
    }

    // Hide loading state
    function hideLoading(button) {
      button.classList.remove('loading');
    }

    // Display AI response
    function displayResponse(title, content, icon = 'fas fa-magic') {
      const outputArea = document.getElementById('outputArea');
      outputArea.className = 'output-area has-content';
      outputArea.innerHTML = `
        <div class="output-content" style="animation: slideInRight 0.6s ease-out;">
          <div class="output-header">
            <div class="output-icon">
              <i class="${icon}"></i>
            </div>
            <h3>${title}</h3>
          </div>
          <div class="output-text">${content.replace(/\n/g, '<br>')}</div>
        </div>
      `;
      currentContent = content;
    }

    // AI Functions
    async function getSummary() {
      if (!API_CONFIGURED) {
        Swal.fire({
          icon: 'error',
          title: 'AI Not Configured',
          text: 'Please configure the Gemini API first',
          confirmButtonColor: '#007BFF'
        });
        return;
      }

      const content = getNotesContent();
      if (!content) return;

      const button = event.target.closest('.action-btn');
      showLoading(button);

      try {
        const response = await callGeminiAPI(`Summarize and explain the following notes in a clear, structured way:\n\n${content}`);
        displayResponse('📄 AI Summary', response, 'fas fa-file-alt');
      } catch (error) {
        console.error('Error:', error);
        Swal.fire({
          icon: 'error',
          title: 'AI Error',
          text: 'Failed to generate summary. Please try again.',
          confirmButtonColor: '#007BFF'
        });
      } finally {
        hideLoading(button);
      }
    }

    async function generateQuiz(type) {
      if (!API_CONFIGURED) {
        Swal.fire({
          icon: 'error',
          title: 'AI Not Configured',
          text: 'Please configure the Gemini API first',
          confirmButtonColor: '#007BFF'
        });
        return;
      }

      const content = getNotesContent();
      if (!content) return;

      const button = event.target.closest('.action-btn');
      showLoading(button);

      const prompt = type === 'mcq' 
        ? `Create 5 multiple-choice questions from these notes:\n\n${content}`
        : `Create 5 true/false questions from these notes:\n\n${content}`;

      try {
        const response = await callGeminiAPI(prompt);
        const icon = type === 'mcq' ? 'fas fa-list-ul' : 'fas fa-check-double';
        const title = type === 'mcq' ? '📝 Multiple Choice Quiz' : '✅ True/False Quiz';
        displayResponse(title, response, icon);
      } catch (error) {
        console.error('Error:', error);
        Swal.fire({
          icon: 'error',
          title: 'AI Error',
          text: 'Failed to generate quiz. Please try again.',
          confirmButtonColor: '#007BFF'
        });
      } finally {
        hideLoading(button);
      }
    }

    async function explainConcepts() {
      if (!API_CONFIGURED) {
        Swal.fire({
          icon: 'error',
          title: 'AI Not Configured',
          text: 'Please configure the Gemini API first',
          confirmButtonColor: '#007BFF'
        });
        return;
      }

      const content = getNotesContent();
      if (!content) return;

      const button = event.target.closest('.action-btn');
      showLoading(button);

      try {
        const response = await callGeminiAPI(`Explain the key concepts and provide examples from these notes:\n\n${content}`);
        displayResponse('💡 Concept Explanations', response, 'fas fa-lightbulb');
      } catch (error) {
        console.error('Error:', error);
        Swal.fire({
          icon: 'error',
          title: 'AI Error',
          text: 'Failed to explain concepts. Please try again.',
          confirmButtonColor: '#007BFF'
        });
      } finally {
        hideLoading(button);
      }
    }

    // Call Gemini API
    async function callGeminiAPI(prompt) {
      const response = await fetch(GEMINI_ENDPOINT, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          contents: [{
            parts: [{
              text: prompt
            }]
          }]
        })
      });

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      const data = await response.json();
      const aiResponse = data.candidates?.[0]?.content?.parts?.[0]?.text;
      
      if (!aiResponse) {
        throw new Error('No response from AI');
      }

      return aiResponse;
    }

    // Export content
    function exportContent() {
      if (!currentContent) {
        Swal.fire({
          icon: 'info',
          title: 'Nothing to Export',
          text: 'Please generate some content first',
          confirmButtonColor: '#007BFF'
        });
        return;
      }

      const blob = new Blob([currentContent], { type: 'text/plain' });
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = `ai-assistant-${currentModule}-${new Date().toISOString().split('T')[0]}.txt`;
      document.body.appendChild(a);
      a.click();
      document.body.removeChild(a);
      URL.revokeObjectURL(url);

      Swal.fire({
        icon: 'success',
        title: 'Exported!',
        text: 'Content has been exported successfully',
        confirmButtonColor: '#007BFF',
        timer: 2000,
        showConfirmButton: false
      });
    }

    // Clear output
    function clearOutput() {
      const outputArea = document.getElementById('outputArea');
      outputArea.className = 'output-area';
      outputArea.innerHTML = `
        <div>
          <i class="fas fa-magic" style="font-size: 2rem; color: var(--primary-blue); margin-bottom: 16px;"></i>
          <p>Ready to assist you! Select a module and choose an AI action to get started.</p>
        </div>
      `;
      currentContent = '';
    }
  </script>
</body>
</html>
