<?php
/**
 * Notes API - Clean TXT Export Only Version
 * Handles all CRUD operations and TXT export functionality
 */

session_start();

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

include('../config/connectiondb.php');
require_once('classes/NotesManager.php');
require_once('classes/NotesExporter.php');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_fullname'] ?? 'Student';

// Initialize managers
$notesManager = new NotesManager($pdo, $user_id);
$notesExporter = new NotesExporter($pdo, $user_id, $user_name);

// Handle both GET and POST requests
$method = $_SERVER['REQUEST_METHOD'];
$action = '';
$data = [];

if ($method === 'GET') {
    // Handle GET requests (mainly for exports)
    $action = $_GET['action'] ?? '';
    $data = $_GET;
} else if ($method === 'POST') {
    // Handle POST requests (CRUD operations)
    $input = file_get_contents('php://input');
    $jsonData = json_decode($input, true);
    
    if ($jsonData) {
        $action = $jsonData['action'] ?? '';
        $data = $jsonData;
    } else {
        // Fallback to $_POST if JSON parsing fails
        $action = $_POST['action'] ?? '';
        $data = $_POST;
    }
}

// Set content type for JSON responses (will be overridden for file downloads)
header('Content-Type: application/json');

try {
    switch ($action) {
        case 'test_session':
            // Debug endpoint to check session status
            echo json_encode([
                'success' => true,
                'session_data' => [
                    'user_id' => $_SESSION['user_id'] ?? 'not set',
                    'user_role' => $_SESSION['user_role'] ?? 'not set',
                    'user_fullname' => $_SESSION['user_fullname'] ?? 'not set'
                ],
                'php_session_id' => session_id(),
                'session_status' => session_status(),
                'cookies' => $_COOKIE
            ]);
            break;
            
        case 'get_modules':
            $modules = $notesManager->getModules();
            echo json_encode([
                'success' => true,
                'modules' => $modules
            ]);
            break;

        case 'get_notes':
            $moduleId = $data['module_id'] ?? null;
            if (!$moduleId) {
                throw new Exception('Module ID is required');
            }
            
            $notes = $notesManager->getModuleNotes($moduleId);
            echo json_encode([
                'success' => true,
                'notes' => $notes
            ]);
            break;

        case 'create_module':
            $name = $data['name'] ?? '';
            $description = $data['description'] ?? '';
            
            if (empty($name)) {
                throw new Exception('Module name is required');
            }
            
            $result = $notesManager->createModule($name, $description);
            echo json_encode($result);
            break;

        case 'update_module':
            $moduleId = $data['module_id'] ?? null;
            $name = $data['name'] ?? '';
            $description = $data['description'] ?? '';
            
            if (!$moduleId || empty($name)) {
                throw new Exception('Module ID and name are required');
            }
            
            $result = $notesManager->updateModule($moduleId, $name, $description);
            echo json_encode($result);
            break;

        case 'delete_module':
            $moduleId = $data['module_id'] ?? null;
            
            if (!$moduleId) {
                throw new Exception('Module ID is required');
            }
            
            $result = $notesManager->deleteModule($moduleId);
            echo json_encode($result);
            break;

        case 'create_note':
            $moduleId = $data['module_id'] ?? null;
            $title = $data['title'] ?? '';
            $content = $data['content'] ?? '';
            
            if (!$moduleId || empty($title) || empty($content)) {
                throw new Exception('Module ID, title, and content are required');
            }
            
            $result = $notesManager->createNote($moduleId, $title, $content);
            echo json_encode($result);
            break;

        case 'update_note':
            $noteId = $data['note_id'] ?? null;
            $title = $data['title'] ?? '';
            $content = $data['content'] ?? '';
            
            if (!$noteId || empty($title) || empty($content)) {
                throw new Exception('Note ID, title, and content are required');
            }
            
            $result = $notesManager->updateNote($noteId, $title, $content);
            echo json_encode($result);
            break;

        case 'delete_note':
            $noteId = $data['note_id'] ?? null;
            
            if (!$noteId) {
                throw new Exception('Note ID is required');
            }
            
            $result = $notesManager->deleteNote($noteId);
            echo json_encode($result);
            break;

        case 'search':
            $query = $data['query'] ?? '';
            
            if (empty($query)) {
                echo json_encode([
                    'success' => true,
                    'results' => []
                ]);
                break;
            }
            
            $results = $notesManager->searchContent($query);
            echo json_encode([
                'success' => true,
                'results' => $results
            ]);
            break;

        case 'export':
            handleExport($data, $notesExporter, $notesManager);
            break;

        default:
            throw new Exception('Unknown action: ' . $action);
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

/**
 * Handle export requests
 */
function handleExport($data, $notesExporter, $notesManager) {
    $scope = $data['scope'] ?? 'current';
    $format = $data['format'] ?? 'txt';
    $moduleId = $data['module_id'] ?? null;
    
    try {
        $result = null;
        
        // Only support TXT format
        if ($format !== 'txt') {
            throw new Exception('Only TXT export format is supported');
        }
        
        switch ($scope) {
            case 'current':
                if (!$moduleId) {
                    throw new Exception('Module ID is required for current module export');
                }
                $result = $notesExporter->exportModuleAsTxt($moduleId);
                break;
                
            case 'all':
                $result = $notesExporter->exportAllNotesAsTxt();
                break;
                
            default:
                throw new Exception('Invalid export scope');
        }
        
        if (!$result || !$result['success']) {
            throw new Exception($result['message'] ?? 'Export failed');
        }
        
        // For direct file download
        if (isset($result['full_path']) && file_exists($result['full_path'])) {
            // Set headers for file download
            header('Content-Type: text/plain');
            header('Content-Disposition: attachment; filename="' . $result['filename'] . '"');
            header('Content-Length: ' . filesize($result['full_path']));
            header('Cache-Control: no-cache, must-revalidate');
            header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
            
            // Output file content
            readfile($result['full_path']);
            
            // Clean up the temporary file
            unlink($result['full_path']);
            exit;
        } else {
            throw new Exception('Export file not found');
        }
        
    } catch (Exception $e) {
        // Reset headers for JSON response
        header('Content-Type: application/json');
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}
?>