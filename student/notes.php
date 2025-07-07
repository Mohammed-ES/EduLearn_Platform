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
    .notes-toolbar {
      background: var(--white);
      border-radius: var(--border-radius);
      padding: 24px;
      margin-bottom: 32px;
      box-shadow: var(--shadow-medium);
      display: flex;
      gap: 16px;
      align-items: center;
      flex-wrap: wrap;
      animation: slideInDown 0.6s ease-out;
    }    .btn-primary {
      background: var(--gradient-primary);
      color: white;
      border: none;
      padding: 16px 32px;
      border-radius: 50px;
      font-weight: 600;
      font-size: 16px;
      cursor: pointer;
      transition: var(--transition);
      display: flex;
      align-items: center;
      gap: 12px;
      box-shadow: var(--shadow-light);
      position: relative;
      overflow: hidden;
    }

    .btn-secondary {
      background: var(--gradient-accent);
      color: white;
      border: none;
      padding: 16px 32px;
      border-radius: 50px;
      font-weight: 600;
      font-size: 16px;
      cursor: pointer;
      transition: var(--transition);
      display: flex;
      align-items: center;
      gap: 12px;
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
    }    .btn-primary:hover::before {
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
    }

    .search-input {
      width: 100%;
      padding: 16px 20px 16px 50px;
      border: 2px solid rgba(0, 123, 255, 0.1);
      border-radius: 50px;
      font-size: 16px;
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
    }

    /* Modules Grid */
    .modules-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
      gap: 24px;
      animation: fadeIn 1s ease-out;
    }

    .module-card {
      background: var(--white);
      border-radius: var(--border-radius);
      padding: 24px;
      box-shadow: var(--shadow-medium);
      transition: var(--transition);
      position: relative;
      overflow: hidden;
      border: 1px solid rgba(0, 123, 255, 0.1);
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
    }    .btn-add {
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
    }

    .note-preview {
      color: var(--text-light);
      font-size: 14px;
      line-height: 1.4;
      display: -webkit-box;
      -webkit-line-clamp: 2;
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
    }    .btn-edit {
      background: rgba(212, 175, 55, 0.1);
      color: var(--gold-accent);
    }

    .btn-edit:hover {
      background: var(--gold-accent);
      color: white;
    }

    .btn-export {
      background: rgba(40, 167, 69, 0.1);
      color: var(--success);
    }

    .btn-export:hover {
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
    }    .empty-state h3 {
      font-size: 1.5rem;
      margin-bottom: 12px;
      color: var(--text-dark);
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
        <h1 class="page-title">My Personal Notes</h1>
        <p class="page-subtitle">Organize your thoughts, ideas, and study materials with our advanced note-taking system</p>
      </div>      <div class="notes-toolbar">
        <button class="btn-primary" onclick="openAddModuleModal()">
          <i class="fas fa-plus"></i>
          Add Module
        </button>
        <div class="search-container">
          <i class="fas fa-search search-icon"></i>
          <input type="text" class="search-input" id="searchInput" placeholder="Search notes and modules...">
        </div>
        <div class="export-options">
          <button class="btn-secondary" onclick="showExportOptions()">
            <i class="fas fa-download"></i>
            Export Notes
          </button>
        </div>
      </div>

      <div id="modulesList" class="modules-grid"></div>
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
        <div class="export-option" onclick="exportAllNotes('pdf')">
          <i class="fas fa-file-pdf"></i>
          <h3>All Notes (PDF)</h3>
          <p>Export all your notes as a PDF document</p>
        </div>
      </div>
    </div>
  </div>

  <script>    // Global variables
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
    }

    // Load modules from server (replaced localStorage)
    async function loadModules() {
      console.log('Loading modules from database...');
      
      const formData = new FormData();
      formData.append('action', 'get_modules');
      
      const result = await apiRequest('notes_api.php', {
        method: 'POST',
        body: formData
      });
      
      if (result && result.success) {
        modules = result.modules || [];
        console.log('Loaded modules:', modules);
        renderModules();
      } else {
        console.error('Failed to load modules:', result);
        modules = [];
        renderModules();
      }
    }

    // Save modules removed - now handled by API

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
              ${module.name}
            </h3>
            <div class="module-actions">              <button class="btn-icon btn-add" onclick="openAddNoteModal(${module.id})" title="Add Note">
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
                <div class="note-title">${note.title}</div>
                <div class="note-preview">${note.content.substring(0, 100)}${note.content.length > 100 ? '...' : ''}</div>
                <div class="note-actions" onclick="event.stopPropagation()">                  <button class="btn-small btn-edit" onclick="editNote(${module.id}, ${note.id})">
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

    // CRUD operations
    function addModule() {
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

      const newModule = {
        id: Date.now(),
        name: moduleName,
        notes: []
      };

      modules.push(newModule);
      saveModules();
      renderModules();
      closeAddModuleModal();

      Swal.fire({
        icon: 'success',
        title: 'Module Created!',
        text: `Module "${moduleName}" has been created successfully`,
        confirmButtonColor: '#007BFF',
        timer: 2000
      });
    }

    function deleteModule(moduleId) {
      const module = modules.find(m => m.id === moduleId);
      
      Swal.fire({
        title: 'Delete Module?',
        text: `Are you sure you want to delete "${module.name}" and all its notes?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#DC3545',
        cancelButtonColor: '#6C757D',
        confirmButtonText: 'Yes, delete it!'
      }).then((result) => {
        if (result.isConfirmed) {
          modules = modules.filter(m => m.id !== moduleId);
          saveModules();
          renderModules();
          
          Swal.fire({
            icon: 'success',
            title: 'Deleted!',
            text: 'Module has been deleted successfully',
            confirmButtonColor: '#007BFF',
            timer: 2000
          });
        }
      });
    }

    function saveNote() {
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

      const module = modules.find(m => m.id === currentModule);
      
      if (currentNote) {
        // Edit existing note
        const noteIndex = module.notes.findIndex(n => n.id === currentNote);
        module.notes[noteIndex] = { id: currentNote, title, content };
      } else {
        // Add new note
        const newNote = {
          id: Date.now(),
          title,
          content
        };
        module.notes.push(newNote);
      }

      saveModules();
      renderModules();
      closeNoteModal();

      Swal.fire({
        icon: 'success',
        title: currentNote ? 'Note Updated!' : 'Note Saved!',
        text: `Note "${title}" has been ${currentNote ? 'updated' : 'saved'} successfully`,
        confirmButtonColor: '#007BFF',
        timer: 2000
      });
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

    function deleteNote(moduleId, noteId) {
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
      }).then((result) => {
        if (result.isConfirmed) {
          module.notes = module.notes.filter(n => n.id !== noteId);
          saveModules();
          renderModules();
          
          Swal.fire({
            icon: 'success',
            title: 'Deleted!',
            text: 'Note has been deleted successfully',
            confirmButtonColor: '#007BFF',
            timer: 2000
          });
        }
      });
    }

    function viewNote(moduleId, noteId) {
      const module = modules.find(m => m.id === moduleId);
      const note = module.notes.find(n => n.id === noteId);

      Swal.fire({
        title: note.title,
        html: `<div style="text-align: left; max-height: 400px; overflow-y: auto; padding: 10px; background: #f8f9fa; border-radius: 8px; white-space: pre-wrap;">${note.content}</div>`,
        width: '600px',
        confirmButtonColor: '#007BFF',
        confirmButtonText: 'Close'
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
      
      if (event.target === addModuleModal) {
        closeAddModuleModal();
      }
      if (event.target === noteModal) {
        closeNoteModal();
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
      }
    });
  </script>
</body>
</html>
