<?php
/**
 * Enhanced NotesExporter Class
 * Handles export functionality for notes in TXT and PDF formats
 */

class NotesExporter {
    private $db;
    private $userId;
    private $userName;
    private $exportsDir;
    
    public function __construct($database, $userId, $userName = 'Student') {
        $this->db = $database;
        $this->userId = $userId;
        $this->userName = $userName;
        
        // Create exports directory if it doesn't exist
        $this->exportsDir = __DIR__ . '/../exports/';
        if (!is_dir($this->exportsDir)) {
            mkdir($this->exportsDir, 0755, true);
        }
    }
    
    /**
     * Export single note as TXT
     */
    public function exportNoteAsTxt($noteId) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM notes WHERE id = ? AND student_id = ?");
            $stmt->execute([$noteId, $this->userId]);
            $note = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$note) {
                return ['success' => false, 'message' => 'Note not found'];
            }
            
            $content = $this->formatNoteForTxt($note);
            $filename = $this->sanitizeFilename($note['title']) . '_' . date('Y-m-d_H-i-s') . '.txt';
            
            return $this->saveFile($content, $filename);
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error exporting note: ' . $e->getMessage()];
        }
    }
    
    /**
     * Export module as TXT
     */
    public function exportModuleAsTxt($moduleId) {
        try {
            // Get module info
            $stmt = $this->db->prepare("SELECT * FROM note_modules WHERE id = ? AND student_id = ?");
            $stmt->execute([$moduleId, $this->userId]);
            $module = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$module) {
                return ['success' => false, 'message' => 'Module not found'];
            }
            
            // Get all notes in this module
            $stmt = $this->db->prepare("SELECT * FROM notes WHERE module_id = ? AND student_id = ? ORDER BY created_at DESC");
            $stmt->execute([$moduleId, $this->userId]);
            $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $content = $this->formatModuleForTxt($module, $notes);
            $filename = $this->sanitizeFilename($module['name']) . '_module_' . date('Y-m-d_H-i-s') . '.txt';
            
            return $this->saveFile($content, $filename);
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error exporting module: ' . $e->getMessage()];
        }
    }
    
    /**
     * Export all notes as TXT
     */
    public function exportAllNotesAsTxt() {
        try {
            // Get all modules for this user
            $stmt = $this->db->prepare("
                SELECT m.*, COUNT(n.id) as note_count 
                FROM note_modules m 
                LEFT JOIN notes n ON m.id = n.module_id AND n.student_id = m.student_id
                WHERE m.student_id = ? 
                GROUP BY m.id 
                ORDER BY m.created_at DESC
            ");
            $stmt->execute([$this->userId]);
            $modules = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($modules)) {
                return ['success' => false, 'message' => 'No modules found'];
            }
            
            $content = $this->formatAllNotesForTxt($modules);
            $filename = 'all_notes_' . $this->sanitizeFilename($this->userName) . '_' . date('Y-m-d_H-i-s') . '.txt';
            
            return $this->saveFile($content, $filename);
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error exporting all notes: ' . $e->getMessage()];
        }
    }
    
    /**
     * Export single note as PDF (simplified HTML version)
     */
    public function exportNoteAsPdf($noteId) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM notes WHERE id = ? AND student_id = ?");
            $stmt->execute([$noteId, $this->userId]);
            $note = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$note) {
                return ['success' => false, 'message' => 'Note not found'];
            }
            
            $html = $this->formatNoteForPdf($note);
            $filename = $this->sanitizeFilename($note['title']) . '_' . date('Y-m-d_H-i-s') . '.html';
            
            return $this->saveFile($html, $filename);
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error exporting note as PDF: ' . $e->getMessage()];
        }
    }
    
    /**
     * Export module as PDF (simplified HTML version)
     */
    public function exportModuleAsPdf($moduleId) {
        try {
            // Get module info
            $stmt = $this->db->prepare("SELECT * FROM note_modules WHERE id = ? AND student_id = ?");
            $stmt->execute([$moduleId, $this->userId]);
            $module = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$module) {
                return ['success' => false, 'message' => 'Module not found'];
            }
            
            // Get all notes in this module
            $stmt = $this->db->prepare("SELECT * FROM notes WHERE module_id = ? AND student_id = ? ORDER BY created_at DESC");
            $stmt->execute([$moduleId, $this->userId]);
            $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $html = $this->formatModuleForPdf($module, $notes);
            $filename = $this->sanitizeFilename($module['name']) . '_module_' . date('Y-m-d_H-i-s') . '.html';
            
            return $this->saveFile($html, $filename);
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error exporting module as PDF: ' . $e->getMessage()];
        }
    }
    
    /**
     * Export all notes as PDF (simplified HTML version)
     */
    public function exportAllNotesAsPdf() {
        try {
            // Get all modules for this user
            $stmt = $this->db->prepare("
                SELECT m.*, COUNT(n.id) as note_count 
                FROM note_modules m 
                LEFT JOIN notes n ON m.id = n.module_id AND n.student_id = m.student_id
                WHERE m.student_id = ? 
                GROUP BY m.id 
                ORDER BY m.created_at DESC
            ");
            $stmt->execute([$this->userId]);
            $modules = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($modules)) {
                return ['success' => false, 'message' => 'No modules found'];
            }
            
            $html = $this->formatAllNotesForPdf($modules);
            $filename = 'all_notes_' . $this->sanitizeFilename($this->userName) . '_' . date('Y-m-d_H-i-s') . '.html';
            
            return $this->saveFile($html, $filename);
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error exporting all notes as PDF: ' . $e->getMessage()];
        }
    }
    
    /**
     * Save file to exports directory and return download info
     */
    private function saveFile($content, $filename) {
        try {
            $filePath = $this->exportsDir . $filename;
            
            if (file_put_contents($filePath, $content) === false) {
                return ['success' => false, 'message' => 'Failed to save file'];
            }
            
            // Create web-accessible path
            $webPath = 'exports/' . $filename;
            
            return [
                'success' => true,
                'message' => 'File exported successfully',
                'file_path' => $webPath,
                'filename' => $filename,
                'full_path' => $filePath
            ];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error saving file: ' . $e->getMessage()];
        }
    }
    
    /**
     * Format single note for TXT export
     */
    private function formatNoteForTxt($note) {
        $content = "=====================================\n";
        $content .= "EDULEARN - NOTE EXPORT\n";
        $content .= "=====================================\n\n";
        $content .= "Student: " . $this->userName . "\n";
        $content .= "Title: " . $note['title'] . "\n";
        $content .= "Created: " . date('F j, Y g:i A', strtotime($note['created_at'])) . "\n";
        $content .= "Last Modified: " . date('F j, Y g:i A', strtotime($note['updated_at'])) . "\n\n";
        $content .= "-------------------------------------\n";
        $content .= "CONTENT:\n";
        $content .= "-------------------------------------\n\n";
        $content .= $note['content'] . "\n\n";
        $content .= "=====================================\n";
        $content .= "End of Note Export\n";
        $content .= "Exported on: " . date('F j, Y g:i A') . "\n";
        $content .= "=====================================\n";
        
        return $content;
    }
    
    /**
     * Format module for TXT export
     */
    private function formatModuleForTxt($module, $notes) {
        $content = "=====================================\n";
        $content .= "EDULEARN - MODULE EXPORT\n";
        $content .= "=====================================\n\n";
        $content .= "Student: " . $this->userName . "\n";
        $content .= "Module: " . $module['name'] . "\n";
        if (!empty($module['description'])) {
            $content .= "Description: " . $module['description'] . "\n";
        }
        $content .= "Created: " . date('F j, Y g:i A', strtotime($module['created_at'])) . "\n";
        $content .= "Total Notes: " . count($notes) . "\n\n";
        
        if (empty($notes)) {
            $content .= "No notes found in this module.\n\n";
        } else {
            foreach ($notes as $index => $note) {
                $content .= "=====================================\n";
                $content .= "NOTE " . ($index + 1) . ": " . $note['title'] . "\n";
                $content .= "=====================================\n";
                $content .= "Created: " . date('F j, Y g:i A', strtotime($note['created_at'])) . "\n";
                $content .= "Last Modified: " . date('F j, Y g:i A', strtotime($note['updated_at'])) . "\n\n";
                $content .= $note['content'] . "\n\n";
            }
        }
        
        $content .= "=====================================\n";
        $content .= "End of Module Export\n";
        $content .= "Exported on: " . date('F j, Y g:i A') . "\n";
        $content .= "=====================================\n";
        
        return $content;
    }
    
    /**
     * Format all notes for TXT export
     */
    private function formatAllNotesForTxt($modules) {
        $content = "=====================================\n";
        $content .= "EDULEARN - ALL NOTES EXPORT\n";
        $content .= "=====================================\n\n";
        $content .= "Student: " . $this->userName . "\n";
        $content .= "Total Modules: " . count($modules) . "\n";
        $content .= "Exported on: " . date('F j, Y g:i A') . "\n\n";
        
        foreach ($modules as $moduleIndex => $module) {
            $content .= "\n" . str_repeat("=", 60) . "\n";
            $content .= "MODULE " . ($moduleIndex + 1) . ": " . strtoupper($module['name']) . "\n";
            $content .= str_repeat("=", 60) . "\n";
            
            if (!empty($module['description'])) {
                $content .= "Description: " . $module['description'] . "\n";
            }
            $content .= "Created: " . date('F j, Y g:i A', strtotime($module['created_at'])) . "\n";
            $content .= "Notes in this module: " . $module['note_count'] . "\n\n";
            
            // Get notes for this module
            $stmt = $this->db->prepare("SELECT * FROM notes WHERE module_id = ? AND student_id = ? ORDER BY created_at DESC");
            $stmt->execute([$module['id'], $this->userId]);
            $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($notes)) {
                $content .= "No notes in this module.\n\n";
            } else {
                foreach ($notes as $noteIndex => $note) {
                    $content .= "-------------------------------------\n";
                    $content .= "NOTE " . ($noteIndex + 1) . ": " . $note['title'] . "\n";
                    $content .= "-------------------------------------\n";
                    $content .= "Created: " . date('F j, Y g:i A', strtotime($note['created_at'])) . "\n";
                    $content .= "Last Modified: " . date('F j, Y g:i A', strtotime($note['updated_at'])) . "\n\n";
                    $content .= $note['content'] . "\n\n";
                }
            }
        }
        
        $content .= str_repeat("=", 60) . "\n";
        $content .= "End of All Notes Export\n";
        $content .= str_repeat("=", 60) . "\n";
        
        return $content;
    }
    
    /**
     * Format single note for PDF export (HTML with print-friendly CSS)
     */
    private function formatNoteForPdf($note) {
        $html = "<!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>EduLearn - Note Export</title>
            <style>
                @media print {
                    body { margin: 0; font-family: Arial, sans-serif; }
                    .no-print { display: none; }
                }
                body {
                    font-family: Arial, sans-serif;
                    line-height: 1.6;
                    color: #333;
                    max-width: 800px;
                    margin: 0 auto;
                    padding: 20px;
                    background: #fff;
                }
                .header {
                    border-bottom: 3px solid #007BFF;
                    padding-bottom: 20px;
                    margin-bottom: 30px;
                }
                .title {
                    color: #007BFF;
                    font-size: 28px;
                    font-weight: bold;
                    margin: 0;
                }
                .meta {
                    color: #666;
                    font-size: 14px;
                    margin-top: 15px;
                }
                .content-section {
                    margin: 30px 0;
                    padding: 20px;
                    border: 1px solid #ddd;
                    border-radius: 8px;
                    background: #fafafa;
                }
                .content-title {
                    color: #007BFF;
                    font-size: 20px;
                    font-weight: bold;
                    margin-bottom: 15px;
                }
                .content-text {
                    white-space: pre-wrap;
                    font-size: 14px;
                    line-height: 1.8;
                }
                .footer {
                    margin-top: 40px;
                    border-top: 1px solid #ccc;
                    padding-top: 20px;
                    text-align: center;
                    color: #666;
                    font-size: 12px;
                }
                .print-btn {
                    background: #007BFF;
                    color: white;
                    border: none;
                    padding: 10px 20px;
                    border-radius: 5px;
                    cursor: pointer;
                    margin: 20px 0;
                }
                .print-btn:hover {
                    background: #0056b3;
                }
            </style>
        </head>
        <body>
            <button class='print-btn no-print' onclick='window.print()'>📄 Print/Save as PDF</button>
            
            <div class='header'>
                <h1 class='title'>EduLearn - Note Export</h1>
                <div class='meta'>
                    <strong>Student:</strong> " . htmlspecialchars($this->userName) . "<br>
                    <strong>Created:</strong> " . date('F j, Y g:i A', strtotime($note['created_at'])) . "<br>
                    <strong>Last Modified:</strong> " . date('F j, Y g:i A', strtotime($note['updated_at'])) . "<br>
                    <strong>Exported on:</strong> " . date('F j, Y g:i A') . "
                </div>
            </div>
            
            <div class='content-section'>
                <div class='content-title'>" . htmlspecialchars($note['title']) . "</div>
                <div class='content-text'>" . nl2br(htmlspecialchars($note['content'])) . "</div>
            </div>
            
            <div class='footer'>
                <p>Generated by EduLearn Notes System</p>
                <p>This document contains academic content and should be used for educational purposes only.</p>
            </div>
        </body>
        </html>";
        
        return $html;
    }
    
    /**
     * Format module for PDF export (HTML with print-friendly CSS)
     */
    private function formatModuleForPdf($module, $notes) {
        $html = "<!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>EduLearn - Module Export</title>
            <style>
                @media print {
                    body { margin: 0; font-family: Arial, sans-serif; }
                    .no-print { display: none; }
                    .note { page-break-inside: avoid; }
                }
                body {
                    font-family: Arial, sans-serif;
                    line-height: 1.6;
                    color: #333;
                    max-width: 800px;
                    margin: 0 auto;
                    padding: 20px;
                    background: #fff;
                }
                .header {
                    border-bottom: 3px solid #007BFF;
                    padding-bottom: 20px;
                    margin-bottom: 30px;
                }
                .title {
                    color: #007BFF;
                    font-size: 28px;
                    font-weight: bold;
                    margin: 0;
                }
                .meta {
                    color: #666;
                    font-size: 14px;
                    margin-top: 15px;
                }
                .note {
                    margin: 30px 0;
                    border: 1px solid #ddd;
                    padding: 20px;
                    border-radius: 8px;
                    background: #fafafa;
                }
                .note-title {
                    color: #007BFF;
                    font-size: 18px;
                    font-weight: bold;
                    margin-bottom: 10px;
                }
                .note-meta {
                    color: #666;
                    font-size: 12px;
                    margin-bottom: 15px;
                }
                .note-content {
                    white-space: pre-wrap;
                    font-size: 14px;
                }
                .empty-module {
                    text-align: center;
                    color: #999;
                    font-style: italic;
                    padding: 40px;
                }
                .footer {
                    margin-top: 40px;
                    border-top: 1px solid #ccc;
                    padding-top: 20px;
                    text-align: center;
                    color: #666;
                    font-size: 12px;
                }
                .print-btn {
                    background: #007BFF;
                    color: white;
                    border: none;
                    padding: 10px 20px;
                    border-radius: 5px;
                    cursor: pointer;
                    margin: 20px 0;
                }
                .print-btn:hover {
                    background: #0056b3;
                }
            </style>
        </head>
        <body>
            <button class='print-btn no-print' onclick='window.print()'>📄 Print/Save as PDF</button>
            
            <div class='header'>
                <h1 class='title'>EduLearn - Module Export</h1>
                <div class='meta'>
                    <strong>Student:</strong> " . htmlspecialchars($this->userName) . "<br>
                    <strong>Module:</strong> " . htmlspecialchars($module['name']) . "<br>";
        
        if (!empty($module['description'])) {
            $html .= "<strong>Description:</strong> " . htmlspecialchars($module['description']) . "<br>";
        }
        
        $html .= "
                    <strong>Created:</strong> " . date('F j, Y g:i A', strtotime($module['created_at'])) . "<br>
                    <strong>Total Notes:</strong> " . count($notes) . "<br>
                    <strong>Exported on:</strong> " . date('F j, Y g:i A') . "
                </div>
            </div>";
        
        if (empty($notes)) {
            $html .= "<div class='empty-module'>This module contains no notes yet.</div>";
        } else {
            foreach ($notes as $index => $note) {
                $html .= "
                <div class='note'>
                    <div class='note-title'>Note " . ($index + 1) . ": " . htmlspecialchars($note['title']) . "</div>
                    <div class='note-meta'>
                        Created: " . date('F j, Y g:i A', strtotime($note['created_at'])) . " | 
                        Modified: " . date('F j, Y g:i A', strtotime($note['updated_at'])) . "
                    </div>
                    <div class='note-content'>" . nl2br(htmlspecialchars($note['content'])) . "</div>
                </div>";
            }
        }
        
        $html .= "
            <div class='footer'>
                <p>Generated by EduLearn Notes System</p>
                <p>Module: " . htmlspecialchars($module['name']) . " | Total Notes: " . count($notes) . "</p>
            </div>
        </body>
        </html>";
        
        return $html;
    }
    
    /**
     * Format all notes for PDF export (HTML with print-friendly CSS)
     */
    private function formatAllNotesForPdf($modules) {
        $totalNotes = 0;
        foreach ($modules as $module) {
            $totalNotes += $module['note_count'];
        }
        
        $html = "<!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>EduLearn - All Notes Export</title>
            <style>
                @media print {
                    body { margin: 0; font-family: Arial, sans-serif; }
                    .no-print { display: none; }
                    .module-section { page-break-before: always; }
                    .note { page-break-inside: avoid; }
                }
                body {
                    font-family: Arial, sans-serif;
                    line-height: 1.6;
                    color: #333;
                    max-width: 800px;
                    margin: 0 auto;
                    padding: 20px;
                    background: #fff;
                }
                .header {
                    border-bottom: 3px solid #007BFF;
                    padding-bottom: 20px;
                    margin-bottom: 40px;
                }
                .title {
                    color: #007BFF;
                    font-size: 28px;
                    font-weight: bold;
                    margin: 0;
                }
                .meta {
                    color: #666;
                    font-size: 14px;
                    margin-top: 15px;
                }
                .module-section {
                    margin: 40px 0;
                }
                .module-title {
                    color: #007BFF;
                    font-size: 22px;
                    font-weight: bold;
                    border-bottom: 2px solid #007BFF;
                    padding-bottom: 10px;
                    margin-bottom: 20px;
                }
                .note {
                    margin: 20px 0;
                    border: 1px solid #ddd;
                    padding: 15px;
                    border-radius: 5px;
                    background: #f9f9f9;
                }
                .note-title {
                    color: #333;
                    font-size: 16px;
                    font-weight: bold;
                    margin-bottom: 8px;
                }
                .note-meta {
                    color: #666;
                    font-size: 12px;
                    margin-bottom: 12px;
                }
                .note-content {
                    white-space: pre-wrap;
                    font-size: 14px;
                }
                .empty-module {
                    color: #999;
                    font-style: italic;
                    text-align: center;
                    padding: 20px;
                }
                .footer {
                    margin-top: 60px;
                    border-top: 1px solid #ccc;
                    padding-top: 20px;
                    text-align: center;
                    color: #666;
                    font-size: 12px;
                }
                .print-btn {
                    background: #007BFF;
                    color: white;
                    border: none;
                    padding: 10px 20px;
                    border-radius: 5px;
                    cursor: pointer;
                    margin: 20px 0;
                }
                .print-btn:hover {
                    background: #0056b3;
                }
            </style>
        </head>
        <body>
            <button class='print-btn no-print' onclick='window.print()'>📄 Print/Save as PDF</button>
            
            <div class='header'>
                <h1 class='title'>EduLearn - Complete Notes Export</h1>
                <div class='meta'>
                    <strong>Student:</strong> " . htmlspecialchars($this->userName) . "<br>
                    <strong>Total Modules:</strong> " . count($modules) . "<br>
                    <strong>Total Notes:</strong> {$totalNotes}<br>
                    <strong>Exported on:</strong> " . date('F j, Y g:i A') . "
                </div>
            </div>";
        
        foreach ($modules as $moduleIndex => $module) {
            $html .= "<div class='module-section'>
                     <div class='module-title'>Module " . ($moduleIndex + 1) . ": " . htmlspecialchars($module['name']) . "</div>";
            
            if (!empty($module['description'])) {
                $html .= "<p><strong>Description:</strong> " . htmlspecialchars($module['description']) . "</p>";
            }
            
            // Get notes for this module
            $stmt = $this->db->prepare("SELECT * FROM notes WHERE module_id = ? AND student_id = ? ORDER BY created_at DESC");
            $stmt->execute([$module['id'], $this->userId]);
            $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($notes)) {
                $html .= "<div class='empty-module'>This module contains no notes.</div>";
            } else {
                foreach ($notes as $noteIndex => $note) {
                    $html .= "
                    <div class='note'>
                        <div class='note-title'>Note " . ($noteIndex + 1) . ": " . htmlspecialchars($note['title']) . "</div>
                        <div class='note-meta'>
                            Created: " . date('F j, Y g:i A', strtotime($note['created_at'])) . " | 
                            Modified: " . date('F j, Y g:i A', strtotime($note['updated_at'])) . "
                        </div>
                        <div class='note-content'>" . nl2br(htmlspecialchars($note['content'])) . "</div>
                    </div>";
                }
            }
            
            $html .= "</div>";
        }
        
        $html .= "
            <div class='footer'>
                <p>Generated by EduLearn Notes System</p>
                <p>Complete academic portfolio for " . htmlspecialchars($this->userName) . "</p>
                <p>Total content: " . count($modules) . " modules, {$totalNotes} notes</p>
            </div>
        </body>
        </html>";
        
        return $html;
    }
    
    /**
     * Sanitize filename for safe file creation
     */
    private function sanitizeFilename($filename) {
        // Remove or replace invalid characters
        $filename = preg_replace('/[^a-zA-Z0-9_\-\s]/', '', $filename);
        $filename = preg_replace('/\s+/', '_', $filename);
        $filename = trim($filename, '_');
        
        // Limit length
        if (strlen($filename) > 50) {
            $filename = substr($filename, 0, 50);
        }
        
        // Ensure we have something
        if (empty($filename)) {
            $filename = 'note_export';
        }
        
        return $filename;
    }
}
?>
