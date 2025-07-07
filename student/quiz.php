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

// Also get sample notes per module for quiz generation
$notes_by_module = [];
foreach ($modules_result as $module) {
    $category = $module['category'];
    $notes_sql = "SELECT title, content 
                  FROM notes 
                  WHERE student_id = ? AND category = ? AND deleted_at IS NULL 
                  ORDER BY created_at DESC 
                  LIMIT 5";
    
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
  <title>AI Quiz Generator | EduLearn</title>
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
    }    /* Header Navigation */
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
    }    /* Main Container */
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

    /* Quiz Generator Section */
    .quiz-generator {
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

    .quiz-generator::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 4px;
      background: var(--gradient-primary);
    }

    .generator-header {
      display: flex;
      align-items: center;
      gap: 16px;
      margin-bottom: 24px;
    }

    .generator-icon {
      width: 48px;
      height: 48px;
      background: var(--gradient-primary);
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-size: 1.2rem;
    }

    .generator-title {
      font-family: 'Poppins', sans-serif;
      font-size: 1.5rem;
      font-weight: 600;
      color: var(--text-dark);
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

    .form-select {
      width: 100%;
      padding: 16px 20px;
      border: 2px solid rgba(0, 123, 255, 0.1);
      border-radius: 12px;
      font-size: 16px;
      color: var(--text-dark);
      background: var(--white);
      transition: var(--transition);
      appearance: none;
      background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
      background-position: right 12px center;
      background-repeat: no-repeat;
      background-size: 16px;
    }

    .form-select:focus {
      outline: none;
      border-color: var(--primary-blue);
      box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
    }

    .quiz-options {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 16px;
      margin-bottom: 24px;
    }

    .option-card {
      background: var(--light-bg);
      border: 2px solid transparent;
      border-radius: 12px;
      padding: 20px;
      text-align: center;
      cursor: pointer;
      transition: var(--transition);
      position: relative;
      overflow: hidden;
    }

    .option-card:hover {
      border-color: var(--primary-blue);
      transform: translateY(-4px);
      box-shadow: var(--shadow-medium);
    }

    .option-card.active {
      border-color: var(--primary-blue);
      background: rgba(0, 123, 255, 0.05);
    }

    .option-icon {
      width: 48px;
      height: 48px;
      background: var(--gradient-primary);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-size: 1.2rem;
      margin: 0 auto 12px;
    }

    .option-title {
      font-weight: 600;
      color: var(--text-dark);
      margin-bottom: 4px;
    }

    .option-desc {
      font-size: 12px;
      color: var(--text-light);
    }

    .generate-btn {
      width: 100%;
      padding: 18px 32px;
      background: var(--gradient-primary);
      color: white;
      border: none;
      border-radius: 12px;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      transition: var(--transition);
      position: relative;
      overflow: hidden;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 12px;
    }

    .generate-btn:hover {
      transform: translateY(-2px);
      box-shadow: var(--shadow-heavy);
    }

    .generate-btn:active {
      transform: translateY(0);
    }

    .generate-btn.loading {
      pointer-events: none;
    }

    .btn-spinner {
      width: 20px;
      height: 20px;
      border: 2px solid rgba(255, 255, 255, 0.3);
      border-top: 2px solid white;
      border-radius: 50%;
      animation: spin 1s linear infinite;
      display: none;
    }

    .generate-btn.loading .btn-spinner {
      display: block;
    }

    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }

    /* Quiz Section */
    .quiz-section {
      background: var(--white);
      border-radius: var(--border-radius);
      box-shadow: var(--shadow-medium);
      border: 1px solid rgba(0, 123, 255, 0.1);
      position: relative;
      overflow: hidden;
      display: none;
      animation: fadeInUp 0.8s ease-out;
    }

    .quiz-section.show {
      display: block;
    }

    .quiz-section::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 4px;
      background: var(--gradient-accent);
    }

    .quiz-content {
      padding: 32px;
    }

    .quiz-progress {
      background: var(--light-bg);
      border-radius: 8px;
      padding: 16px;
      margin-bottom: 32px;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .progress-info {
      display: flex;
      align-items: center;
      gap: 16px;
    }

    .progress-circle {
      width: 48px;
      height: 48px;
      border-radius: 50%;
      background: var(--gradient-primary);
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-weight: 600;
    }

    .progress-text {
      font-weight: 500;
      color: var(--text-dark);
    }

    .quiz-question {
      margin-bottom: 32px;
      padding: 24px;
      background: var(--light-bg);
      border-radius: 12px;
      border-left: 4px solid var(--primary-blue);
    }

    .question-number {
      font-size: 14px;
      color: var(--primary-blue);
      font-weight: 600;
      margin-bottom: 8px;
    }

    .question-text {
      font-size: 18px;
      font-weight: 500;
      color: var(--text-dark);
      line-height: 1.6;
      margin-bottom: 20px;
    }

    .answer-options {
      display: flex;
      flex-direction: column;
      gap: 12px;
    }

    .answer-option {
      padding: 16px 20px;
      background: var(--white);
      border: 2px solid rgba(0, 123, 255, 0.1);
      border-radius: 12px;
      cursor: pointer;
      transition: var(--transition);
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .answer-option:hover {
      border-color: var(--primary-blue);
      background: rgba(0, 123, 255, 0.05);
    }

    .answer-option.selected {
      border-color: var(--primary-blue);
      background: rgba(0, 123, 255, 0.1);
    }

    .option-letter {
      width: 28px;
      height: 28px;
      background: var(--light-bg);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 600;
      color: var(--text-dark);
      transition: var(--transition);
    }

    .answer-option.selected .option-letter {
      background: var(--primary-blue);
      color: white;
    }

    .quiz-actions {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 24px 32px;
      background: var(--light-bg);
      border-top: 1px solid rgba(0, 123, 255, 0.1);
    }

    .action-btn {
      padding: 12px 24px;
      border-radius: 12px;
      font-weight: 500;
      cursor: pointer;
      transition: var(--transition);
      border: none;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .btn-secondary {
      background: rgba(108, 117, 125, 0.1);
      color: var(--blue-gray);
    }

    .btn-secondary:hover {
      background: var(--blue-gray);
      color: white;
    }

    .btn-primary {
      background: var(--gradient-primary);
      color: white;
    }

    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: var(--shadow-medium);
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

    @keyframes slideInLeft {
      from {
        opacity: 0;
        transform: translateX(-30px);
      }
      to {
        opacity: 1;
        transform: translateX(0);
      }
    }    .question-appear {
      animation: slideInLeft 0.5s ease-out;
    }

    /* API Status Indicator Styles */
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

    .api-actions {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
    }

    .api-link {
      padding: 8px 12px;
      background: rgba(255, 255, 255, 0.9);
      color: var(--primary-blue);
      text-decoration: none;
      border-radius: 6px;
      font-size: 14px;
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      gap: 5px;
    }

    .api-link:hover {
      background: white;
      transform: translateY(-1px);
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .setup-link {
      background: var(--gradient-primary);
      color: white;
    }

    .setup-link:hover {
      background: var(--primary-blue);
      color: white;
    }

    @keyframes pulse {
      0%, 100% { opacity: 1; }
      50% { opacity: 0.5; }
    }

    /* Loading States */
    .loading-quiz {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 60px 32px;
      text-align: center;
    }

    .loading-spinner {
      width: 60px;
      height: 60px;
      border: 4px solid rgba(0, 123, 255, 0.1);
      border-top: 4px solid var(--primary-blue);
      border-radius: 50%;
      animation: spin 1s linear infinite;
      margin-bottom: 24px;
    }

    .loading-text {
      font-size: 16px;
      color: var(--text-light);
      margin-bottom: 8px;
    }

    .loading-subtext {
      font-size: 14px;
      color: var(--blue-gray);
    }

    /* Responsive Design */
    @media (max-width: 768px) {
      .nav-container {
        padding: 0 16px;
      }

      .quiz-container {
        padding: 24px 16px;
      }

      .quiz-title {
        font-size: 2.2rem;
      }

      .quiz-generator,
      .quiz-content {
        padding: 24px;
      }

      .quiz-options {
        grid-template-columns: 1fr;
      }

      .quiz-actions {
        flex-direction: column;
        gap: 12px;
        padding: 20px;
      }

      .action-btn {
        width: 100%;
        justify-content: center;
      }
    }

    @media (max-width: 480px) {
      .quiz-title {
        font-size: 1.8rem;
      }

      .quiz-subtitle {
        font-size: 1rem;
      }

      .user-info {
        display: none;
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
    <div class="container">      <div class="header-content">
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
      <!-- Header Section -->      <div class="page-header">
        <h1 class="page-title">AI Quiz Generator</h1>
        <p class="page-subtitle">
          Transform your notes into interactive quizzes with the power of AI. 
          Test your knowledge and reinforce learning with personalized questions.
        </p>
        
        <!-- API Status Indicator -->
        <div class="api-status" id="apiStatus">
          <div class="api-status-indicator">
            <i class="fas fa-circle" id="statusIcon"></i>
            <span id="statusText">Checking API...</span>
          </div>
          <div class="api-actions" id="apiActions" style="display: none;">
            <a href="setup_api.php" class="api-link setup-link">
              <i class="fas fa-cogs"></i> Setup API
            </a>
            <a href="test_api.php" class="api-link test-link">
              <i class="fas fa-vial"></i> Test API
            </a>
          </div>
        </div>
      </div>

    <!-- Quiz Generator Section -->
    <section class="quiz-generator">
      <div class="generator-header">
        <div class="generator-icon">
          <i class="fas fa-magic"></i>
        </div>
        <h2 class="generator-title">Generate Your Quiz</h2>
      </div>

      <div class="form-group">
        <label class="form-label" for="moduleSelect">
          <i class="fas fa-book-open" style="margin-right: 8px; color: var(--primary-blue);"></i>
          Choose a Module
        </label>        <select id="moduleSelect" class="form-select">
          <option value="">Select a module to generate quiz...</option>
          <?php foreach ($modules_result as $module): ?>
            <option value="<?php echo htmlspecialchars($module['category']); ?>">
              <?php echo htmlspecialchars($module['category']); ?>
              (<?php echo count($notes_by_module[$module['category']]); ?> notes)
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label class="form-label">
          <i class="fas fa-cogs" style="margin-right: 8px; color: var(--primary-blue);"></i>
          Quiz Type
        </label>
        <div class="quiz-options">
          <div class="option-card active" data-type="mcq">
            <div class="option-icon">
              <i class="fas fa-list-ul"></i>
            </div>
            <div class="option-title">Multiple Choice</div>
            <div class="option-desc">4 options per question</div>
          </div>
          <div class="option-card" data-type="truefalse">
            <div class="option-icon">
              <i class="fas fa-check-double"></i>
            </div>
            <div class="option-title">True/False</div>
            <div class="option-desc">Binary choice questions</div>
          </div>
          <div class="option-card" data-type="mixed">
            <div class="option-icon">
              <i class="fas fa-random"></i>
            </div>
            <div class="option-title">Mixed</div>
            <div class="option-desc">Combination of both</div>
          </div>
        </div>
      </div>

      <button id="generateBtn" class="generate-btn" onclick="generateQuiz()">
        <div class="btn-spinner"></div>
        <i class="fas fa-sparkles"></i>
        <span class="btn-text">Generate AI Quiz</span>
      </button>
    </section>

    <!-- Quiz Section -->
    <section id="quizSection" class="quiz-section">
      <!-- Quiz content will be dynamically loaded here -->
    </section>
  </main>
  <script>    // Configuration API Gemini
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
    let currentQuiz = null;
    let currentQuestionIndex = 0;
    let userAnswers = [];
    let quizStartTime = null;    // Enhanced UI interactions
    document.addEventListener('DOMContentLoaded', function() {
      // Update API status indicator
      updateAPIStatus();
      
      // Quiz type selection
      const optionCards = document.querySelectorAll('.option-card');
      optionCards.forEach(card => {
        card.addEventListener('click', function() {
          optionCards.forEach(c => c.classList.remove('active'));
          this.classList.add('active');
        });
      });

      // Form animations
      const formElements = document.querySelectorAll('.form-select, .option-card, .generate-btn');
      const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            entry.target.style.animationDelay = `${entry.target.dataset.delay || 0}s`;
            entry.target.classList.add('fadeInUp');
          }
        });
      });

      formElements.forEach(el => observer.observe(el));
    });

    // Fonction principale pour générer le quiz
    async function generateQuiz() {
      const moduleSelect = document.getElementById('moduleSelect');
      const selectedModule = moduleSelect.value;
      const selectedQuizType = document.querySelector('.option-card.active').dataset.type;
        if (!selectedModule) {
        Swal.fire({
          icon: 'warning',
          title: 'Module Requis',
          text: 'Veuillez sélectionner un module pour générer le quiz',
          confirmButtonColor: '#007BFF'
        });
        return;
      }      // Check if API is configured
      if (!API_CONFIGURED) {
        Swal.fire({
          icon: 'error',
          title: 'Configuration API Requise',
          html: `<div style="text-align: left;">
            <p>L'API Gemini n'est pas configurée. Pour utiliser la génération de quiz IA :</p>
            <ol style="margin: 10px 0; padding-left: 20px;">
              <li>Obtenez une clé API sur <a href="https://aistudio.google.com/" target="_blank">Google AI Studio</a></li>
              <li>Utilisez notre assistant de configuration</li>
              <li>Testez votre configuration</li>
            </ol>
            <p><strong>Erreur:</strong> ${API_ERROR || 'Clé API manquante'}</p>
          </div>`,
          confirmButtonColor: '#007BFF',
          confirmButtonText: 'Configurer Maintenant',
          showCancelButton: true,
          cancelButtonText: 'Plus Tard',
          cancelButtonColor: '#6c757d'
        }).then((result) => {
          if (result.isConfirmed) {
            window.open('setup_api.php', '_blank');
          }
        });
        return;
      }

      const moduleNotes = notesByModule[selectedModule];
      if (!moduleNotes || moduleNotes.length === 0) {
        Swal.fire({
          icon: 'error',
          title: 'Aucune Note Trouvée',
          text: 'Aucune note trouvée pour ce module. Ajoutez des notes avant de générer un quiz.',
          confirmButtonColor: '#007BFF'
        });
        return;
      }

      // Show loading state
      const btn = document.getElementById('generateBtn');
      btn.classList.add('loading');
      btn.querySelector('.btn-text').textContent = 'Génération du quiz...';
      
      // Show quiz section with loading
      const quizSection = document.getElementById('quizSection');
      quizSection.classList.add('show');
      quizSection.innerHTML = createLoadingHTML();

      try {
        // Préparer le contenu des notes pour l'IA
        const notesContent = moduleNotes.map(note => 
          `Titre: ${note.title}\nContenu: ${note.content}`
        ).join('\n\n');

        // Générer le quiz avec Gemini AI
        const quizData = await generateQuizWithAI(notesContent, selectedQuizType, selectedModule);
        
        // Afficher le quiz généré
        displayQuiz(quizData, selectedModule);
        
        // Reset button state
        btn.classList.remove('loading');
        btn.querySelector('.btn-text').textContent = 'Générer Quiz IA';
        
        // Show success message
        Swal.fire({
          icon: 'success',
          title: 'Quiz Généré !',
          text: `Votre quiz ${selectedQuizType} a été créé avec succès`,
          confirmButtonColor: '#007BFF',
          timer: 2000,
          showConfirmButton: false
        });

      } catch (error) {
        console.error('Erreur génération quiz:', error);
        
        // Reset button state
        btn.classList.remove('loading');
        btn.querySelector('.btn-text').textContent = 'Générer Quiz IA';
        
        // Show error message
        Swal.fire({
          icon: 'error',
          title: 'Erreur de Génération',
          text: 'Impossible de générer le quiz. Veuillez réessayer.',
          confirmButtonColor: '#007BFF'
        });
        
        quizSection.classList.remove('show');
      }
    }    // Fonction pour générer le quiz avec l'API Gemini
    async function generateQuizWithAI(notesContent, quizType, module) {
      // Check if API is configured
      if (!API_CONFIGURED) {
        throw new Error(API_ERROR || 'API Gemini non configurée. Veuillez configurer votre clé API dans config/api_config.php');
      }
      
      let prompt = '';
      
      switch(quizType) {
        case 'mcq':
          prompt = `Générez 5 questions à choix multiples en français basées sur les notes suivantes du module "${module}". 
          
Notes:
${notesContent}

Format de réponse strictement en JSON:
{
  "questions": [
    {
      "question": "Question ici",
      "options": ["Option A", "Option B", "Option C", "Option D"],
      "correct": 0,
      "explanation": "Explication de la réponse correcte"
    }
  ]
}`;
          break;
          
        case 'truefalse':
          prompt = `Générez 7 questions vrai/faux en français basées sur les notes suivantes du module "${module}".
          
Notes:
${notesContent}

Format de réponse strictement en JSON:
{
  "questions": [
    {
      "question": "Question ici",
      "options": ["Vrai", "Faux"],
      "correct": 0,
      "explanation": "Explication de la réponse"
    }
  ]
}`;
          break;
          
        case 'mixed':
          prompt = `Générez 6 questions mixtes (3 QCM + 3 vrai/faux) en français basées sur les notes suivantes du module "${module}".
          
Notes:
${notesContent}

Format de réponse strictement en JSON:
{
  "questions": [
    {
      "question": "Question ici",
      "options": ["Option A", "Option B", "Option C", "Option D"] ou ["Vrai", "Faux"],
      "correct": 0,
      "explanation": "Explication de la réponse correcte"
    }
  ]
}`;
          break;
      }

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
        throw new Error('Pas de réponse de l\'IA');
      }

      try {
        // Nettoyer la réponse de l'IA pour extraire le JSON
        const jsonMatch = aiResponse.match(/\{[\s\S]*\}/);
        if (!jsonMatch) {
          throw new Error('Format JSON non trouvé dans la réponse IA');
        }
        
        const quizData = JSON.parse(jsonMatch[0]);
        quizData.module = module;
        quizData.type = quizType;
        
        return quizData;
      } catch (parseError) {
        console.error('Erreur parsing JSON:', parseError);
        throw new Error('Erreur dans le format de réponse de l\'IA');
      }
    }

    // Fonction pour créer le HTML de chargement
    function createLoadingHTML() {
      return `
        <div class="loading-quiz">
          <div class="loading-spinner"></div>
          <div class="loading-text">L'IA génère votre quiz personnalisé...</div>
          <div class="loading-subtext">Analyse de vos notes en cours</div>
        </div>
      `;
    }

    // Fonction pour afficher le quiz généré
    function displayQuiz(quizData, module) {
      currentQuiz = quizData;
      currentQuestionIndex = 0;
      userAnswers = [];
      quizStartTime = new Date();
      
      const quizSection = document.getElementById('quizSection');
      
      quizSection.innerHTML = `
        <div class="quiz-content">
          <div class="quiz-progress">
            <div class="progress-info">
              <div class="progress-circle">1/${quizData.questions.length}</div>
              <div class="progress-text">Question 1 sur ${quizData.questions.length}</div>
            </div>
            <div class="quiz-timer" id="quizTimer">00:00</div>
          </div>
          
          <div id="questionContainer">
            <!-- Questions will be loaded here -->
          </div>
        </div>
        
        <div class="quiz-actions">
          <button class="action-btn btn-secondary" onclick="previousQuestion()" id="prevBtn" disabled>
            <i class="fas fa-arrow-left"></i>
            Précédent
          </button>
          <button class="action-btn btn-primary" onclick="nextQuestion()" id="nextBtn">
            Suivant
            <i class="fas fa-arrow-right"></i>
          </button>
        </div>
      `;
      
      // Start timer
      startQuizTimer();
      
      // Load first question
      loadQuestion(0);
    }

    // Fonction pour charger une question
    function loadQuestion(index) {
      const question = currentQuiz.questions[index];
      const questionContainer = document.getElementById('questionContainer');
      
      questionContainer.innerHTML = `
        <div class="quiz-question question-appear">
          <div class="question-number">Question ${index + 1}</div>
          <div class="question-text">${question.question}</div>
          <div class="answer-options">
            ${question.options.map((option, i) => `
              <div class="answer-option" onclick="selectAnswer(${i})" data-option="${i}">
                <div class="option-letter">${String.fromCharCode(65 + i)}</div>
                <div>${option}</div>
              </div>
            `).join('')}
          </div>
        </div>
      `;
      
      // Update progress
      document.querySelector('.progress-circle').textContent = `${index + 1}/${currentQuiz.questions.length}`;
      document.querySelector('.progress-text').textContent = `Question ${index + 1} sur ${currentQuiz.questions.length}`;
      
      // Update navigation buttons
      updateNavigationButtons();
      
      // Restore previous answer if exists
      if (userAnswers[index] !== undefined) {
        const selectedOption = questionContainer.querySelector(`[data-option="${userAnswers[index]}"]`);
        if (selectedOption) {
          selectedOption.classList.add('selected');
        }
      }
    }

    // Fonction pour sélectionner une réponse
    function selectAnswer(optionIndex) {
      const questionContainer = document.getElementById('questionContainer');
      const allOptions = questionContainer.querySelectorAll('.answer-option');
      
      // Remove previous selection
      allOptions.forEach(opt => opt.classList.remove('selected'));
      
      // Add selection to clicked option
      const selectedOption = questionContainer.querySelector(`[data-option="${optionIndex}"]`);
      selectedOption.classList.add('selected');
      
      // Store answer
      userAnswers[currentQuestionIndex] = optionIndex;
      
      // Update next button
      updateNavigationButtons();
    }

    // Navigation functions
    function nextQuestion() {
      if (currentQuestionIndex < currentQuiz.questions.length - 1) {
        currentQuestionIndex++;
        loadQuestion(currentQuestionIndex);
      } else {
        finishQuiz();
      }
    }

    function previousQuestion() {
      if (currentQuestionIndex > 0) {
        currentQuestionIndex--;
        loadQuestion(currentQuestionIndex);
      }
    }

    function updateNavigationButtons() {
      const prevBtn = document.getElementById('prevBtn');
      const nextBtn = document.getElementById('nextBtn');
      
      // Previous button
      prevBtn.disabled = currentQuestionIndex === 0;
      
      // Next button
      if (currentQuestionIndex === currentQuiz.questions.length - 1) {
        nextBtn.innerHTML = '<i class="fas fa-check"></i> Terminer';
        nextBtn.disabled = userAnswers[currentQuestionIndex] === undefined;
      } else {
        nextBtn.innerHTML = 'Suivant <i class="fas fa-arrow-right"></i>';
        nextBtn.disabled = userAnswers[currentQuestionIndex] === undefined;
      }
    }

    // Timer functions
    function startQuizTimer() {
      setInterval(() => {
        if (quizStartTime) {
          const elapsed = Math.floor((new Date() - quizStartTime) / 1000);
          const minutes = Math.floor(elapsed / 60);
          const seconds = elapsed % 60;
          document.getElementById('quizTimer').textContent = 
            `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        }
      }, 1000);
    }

    // Fonction pour terminer le quiz
    async function finishQuiz() {
      const quizEndTime = new Date();
      const totalTime = Math.floor((quizEndTime - quizStartTime) / 1000);
      
      // Calculate score
      let correctAnswers = 0;
      currentQuiz.questions.forEach((question, index) => {
        if (userAnswers[index] === question.correct) {
          correctAnswers++;
        }
      });
      
      const score = Math.round((correctAnswers / currentQuiz.questions.length) * 100);
      
      // Show loading for AI feedback
      const quizSection = document.getElementById('quizSection');
      quizSection.innerHTML = createLoadingHTML();
      
      try {
        // Get AI feedback
        const feedback = await getAIFeedback(currentQuiz, userAnswers, score);
        
        // Display results
        displayQuizResults(score, correctAnswers, totalTime, feedback);
        
      } catch (error) {
        console.error('Erreur feedback IA:', error);
        // Display results without AI feedback
        displayQuizResults(score, correctAnswers, totalTime, null);
      }
    }

    // Fonction pour obtenir le feedback de l'IA
    async function getAIFeedback(quiz, answers, score) {
      const questionsAndAnswers = quiz.questions.map((q, index) => {
        const userAnswer = answers[index] !== undefined ? q.options[answers[index]] : 'Pas de réponse';
        const correctAnswer = q.options[q.correct];
        const isCorrect = answers[index] === q.correct;
        
        return `Question: ${q.question}
Votre réponse: ${userAnswer} ${isCorrect ? '✓' : '✗'}
Réponse correcte: ${correctAnswer}
Explication: ${q.explanation}`;
      }).join('\n\n');

      const feedbackPrompt = `Analysez les résultats de ce quiz et donnez des conseils personnalisés en français:

Module: ${quiz.module}
Score: ${score}%
Type de quiz: ${quiz.type}

Questions et réponses:
${questionsAndAnswers}

Donnez:
1. Une évaluation générale de la performance
2. Les points forts identifiés  
3. Les domaines à améliorer
4. 3 conseils d'étude spécifiques
5. Encouragements motivants

Réponse en format texte simple, max 300 mots.`;

      const response = await fetch(GEMINI_ENDPOINT, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          contents: [{
            parts: [{
              text: feedbackPrompt
            }]
          }]
        })
      });

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      const data = await response.json();
      return data.candidates?.[0]?.content?.parts?.[0]?.text || null;
    }

    // Fonction pour afficher les résultats
    function displayQuizResults(score, correctAnswers, totalTime, aiFeedback) {
      const minutes = Math.floor(totalTime / 60);
      const seconds = totalTime % 60;
      
      let scoreColor = score >= 80 ? '#28A745' : score >= 60 ? '#FFC107' : '#DC3545';
      let scoreIcon = score >= 80 ? 'fas fa-trophy' : score >= 60 ? 'fas fa-thumbs-up' : 'fas fa-redo';
      
      const quizSection = document.getElementById('quizSection');
      quizSection.innerHTML = `
        <div class="quiz-content">
          <div class="quiz-results">
            <div class="result-header">
              <div class="result-icon" style="color: ${scoreColor}">
                <i class="${scoreIcon}"></i>
              </div>
              <h2 class="result-title">Quiz Terminé !</h2>
              <div class="result-score" style="color: ${scoreColor}">${score}%</div>
            </div>
            
            <div class="result-stats">
              <div class="stat-item">
                <div class="stat-value">${correctAnswers}/${currentQuiz.questions.length}</div>
                <div class="stat-label">Réponses Correctes</div>
              </div>
              <div class="stat-item">
                <div class="stat-value">${minutes}:${seconds.toString().padStart(2, '0')}</div>
                <div class="stat-label">Temps Total</div>
              </div>
              <div class="stat-item">
                <div class="stat-value">${currentQuiz.type.toUpperCase()}</div>
                <div class="stat-label">Type de Quiz</div>
              </div>
            </div>
            
            ${aiFeedback ? `
              <div class="ai-feedback">
                <h3><i class="fas fa-robot"></i> Feedback IA Personnalisé</h3>
                <div class="feedback-content">${aiFeedback.replace(/\n/g, '<br>')}</div>
              </div>
            ` : ''}
            
            <div class="result-actions">
              <button class="action-btn btn-secondary" onclick="reviewQuiz()">
                <i class="fas fa-eye"></i>
                Réviser les Réponses
              </button>
              <button class="action-btn btn-primary" onclick="location.reload()">
                <i class="fas fa-redo"></i>
                Nouveau Quiz
              </button>
            </div>
          </div>
        </div>
      `;
    }

    // Fonction pour réviser le quiz
    function reviewQuiz() {
      const quizSection = document.getElementById('quizSection');
      
      const reviewHTML = currentQuiz.questions.map((question, index) => {
        const userAnswer = userAnswers[index];
        const isCorrect = userAnswer === question.correct;
        const userAnswerText = userAnswer !== undefined ? question.options[userAnswer] : 'Pas de réponse';
        const correctAnswerText = question.options[question.correct];
        
        return `
          <div class="review-question">
            <div class="question-header">
              <span class="question-number">Question ${index + 1}</span>
              <span class="question-result ${isCorrect ? 'correct' : 'incorrect'}">
                <i class="fas fa-${isCorrect ? 'check' : 'times'}"></i>
                ${isCorrect ? 'Correct' : 'Incorrect'}
              </span>
            </div>
            <div class="question-text">${question.question}</div>
            <div class="answer-review">
              <div class="user-answer ${isCorrect ? 'correct' : 'incorrect'}">
                <strong>Votre réponse:</strong> ${userAnswerText}
              </div>
              ${!isCorrect ? `
                <div class="correct-answer">
                  <strong>Réponse correcte:</strong> ${correctAnswerText}
                </div>
              ` : ''}
              <div class="explanation">
                <strong>Explication:</strong> ${question.explanation}
              </div>
            </div>
          </div>
        `;
      }).join('');
      
      quizSection.innerHTML = `
        <div class="quiz-content">
          <div class="review-header">
            <h2><i class="fas fa-eye"></i> Révision du Quiz</h2>
            <p>Examinez vos réponses et apprenez de vos erreurs</p>
          </div>
          <div class="review-questions">
            ${reviewHTML}
          </div>
        </div>
        
        <div class="quiz-actions">
          <button class="action-btn btn-secondary" onclick="displayQuizResults(${Math.round((userAnswers.filter((ans, i) => ans === currentQuiz.questions[i].correct).length / currentQuiz.questions.length) * 100)}, ${userAnswers.filter((ans, i) => ans === currentQuiz.questions[i].correct).length}, ${Math.floor((new Date() - quizStartTime) / 1000)}, null)">
            <i class="fas fa-arrow-left"></i>
            Retour aux Résultats
          </button>
          <button class="action-btn btn-primary" onclick="location.reload()">
            <i class="fas fa-redo"></i>
            Nouveau Quiz
          </button>
        </div>      `;
    }

    // Function to update API status indicator
    function updateAPIStatus() {
      const statusElement = document.getElementById('apiStatus');
      const iconElement = document.getElementById('statusIcon');
      const textElement = document.getElementById('statusText');
      const actionsElement = document.getElementById('apiActions');
      
      if (API_CONFIGURED) {
        statusElement.className = 'api-status configured';
        iconElement.className = 'fas fa-circle';
        textElement.textContent = 'API Gemini: Configurée';
        actionsElement.style.display = 'flex';
        actionsElement.innerHTML = `
          <a href="test_api.php" class="api-link test-link">
            <i class="fas fa-vial"></i> Tester API
          </a>
        `;
      } else {
        statusElement.className = 'api-status not-configured';
        iconElement.className = 'fas fa-circle';
        textElement.textContent = 'API Gemini: Non configurée';
        actionsElement.style.display = 'flex';
        actionsElement.innerHTML = `
          <a href="setup_api.php" class="api-link setup-link">
            <i class="fas fa-cogs"></i> Configurer API
          </a>
        `;
      }
    }
  </script>
</body>
</html>
