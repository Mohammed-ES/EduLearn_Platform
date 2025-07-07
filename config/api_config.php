<?php
/**
 * EduLearn Platform - API Configuration
 * Configure your AI API keys here
 */

// Load environment variables
function loadEnv($file = '.env') {
    $envFile = __DIR__ . '/../' . $file;
    if (!file_exists($envFile)) {
        return false;
    }
    
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue; // Skip comments
        }
        
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        
        if (!empty($key) && !empty($value)) {
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
    return true;
}

// Load .env file
loadEnv();

// Gemini API Configuration
// To get your API key:
// 1. Go to https://aistudio.google.com/
// 2. Create a new project or use existing one
// 3. Go to "Get API key" section
// 4. Generate new API key
// 5. Add it to your .env file

define('GEMINI_API_KEY', getenv('GEMINI_API_KEY') ?: 'YOUR_ACTUAL_GEMINI_API_KEY_HERE');
define('GEMINI_MODEL', getenv('GEMINI_MODEL') ?: 'gemini-2.0-flash');
define('GEMINI_ENDPOINT', 'https://generativelanguage.googleapis.com/v1beta/models/' . GEMINI_MODEL . ':generateContent?key=' . GEMINI_API_KEY);

// API Configuration Check
function isApiConfigured() {
    return GEMINI_API_KEY !== 'YOUR_ACTUAL_GEMINI_API_KEY_HERE' && !empty(GEMINI_API_KEY);
}

// Function to get API key safely
function getGeminiApiKey() {
    if (!isApiConfigured()) {
        throw new Exception('Gemini API key not configured. Please update config/api_config.php with your actual API key.');
    }
    return GEMINI_API_KEY;
}

// Function to get API endpoint
function getGeminiEndpoint() {
    if (!isApiConfigured()) {
        throw new Exception('Gemini API key not configured. Please update config/api_config.php with your actual API key.');
    }
    return GEMINI_ENDPOINT;
}
?>
