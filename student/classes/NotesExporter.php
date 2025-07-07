<?php
/**
 * NotesExporter Class
 * Handles export functionality for notes in TXT and PDF formats
 */

// Try to load PDF library if available
$pdfLibraryAvailable = false;
if (file_exists(__DIR__ . '/../../vendor/autoload.php')) {
    require_once __DIR__ . '/../../vendor/autoload.php';
    $pdfLibraryAvailable = true;
}

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
     * Export single note as PDF
     */
    public function exportNoteAsPdf($noteId) {
        global $pdfLibraryAvailable;
        
        if (!$pdfLibraryAvailable) {
            return ['success' => false, 'message' => 'PDF export functionality requires additional libraries. Please install TCPDF or similar PDF library.'];
        }
        
        $note = $this->notesManager->getNoteForExport($noteId);
        
        if (!$note) {
            return ['success' => false, 'message' => 'Note not found'];
        }
        
        $pdf = $this->createPdfDocument();
        $this->addNoteToPage($pdf, $note);
        
        $filename = $this->sanitizeFilename($note['title']) . '.pdf';
        
        return $this->outputPdf($pdf, $filename);
    }
      /**
     * Export module as PDF
     */
    public function exportModuleAsPdf($moduleId) {
        global $pdfLibraryAvailable;
        
        if (!$pdfLibraryAvailable) {
            return ['success' => false, 'message' => 'PDF export functionality requires additional libraries. Please install TCPDF or similar PDF library.'];
        }
        
        $module = $this->notesManager->getModuleForExport($moduleId);
        
        if (!$module) {
            return ['success' => false, 'message' => 'Module not found'];
        }
        
        $pdf = $this->createPdfDocument();
        $this->addModuleToPdf($pdf, $module);
        
        $filename = $this->sanitizeFilename($module['name']) . '_module.pdf';
        
        return $this->outputPdf($pdf, $filename);
    }
      /**
     * Export all notes as PDF
     */
    public function exportAllNotesAsPdf() {
        global $pdfLibraryAvailable;
        
        if (!$pdfLibraryAvailable) {
            return ['success' => false, 'message' => 'PDF export functionality requires additional libraries. Please install TCPDF or similar PDF library.'];
        }
        
        $notes = $this->notesManager->getAllNotesForExport();
        
        if (empty($notes)) {
            return ['success' => false, 'message' => 'No notes found'];
        }
        
        $pdf = $this->createPdfDocument();
        $this->addAllNotesToPdf($pdf, $notes);
        
        $filename = 'all_notes_' . date('Y-m-d') . '.pdf';
        
        return $this->outputPdf($pdf, $filename);
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
     * Enhanced format single note for TXT export
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
     * Enhanced format module for TXT export
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
     * Enhanced format all notes for TXT export
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
     * Create PDF document with basic styling
     */
    private function createPdfDocument() {
        // Using a simple HTML to PDF approach since TCPDF might not be available
        // This can be enhanced with proper PDF libraries
        return new class {
            private $content = '';
            
            public function addContent($html) {
                $this->content .= $html;
            }
            
            public function getContent() {
                return $this->content;
            }
        };
    }
    
    /**
     * Add single note to PDF
     */
    private function addNoteToPage($pdf, $note) {
        $html = $this->generateNoteHtml($note);
        $pdf->addContent($html);
    }
    
    /**
     * Add module to PDF
     */
    private function addModuleToPdf($pdf, $module) {
        $html = $this->generateModuleHtml($module);
        $pdf->addContent($html);
    }
    
    /**
     * Add all notes to PDF
     */
    private function addAllNotesToPdf($pdf, $notes) {
        $html = $this->generateAllNotesHtml($notes);
        $pdf->addContent($html);
    }
    
    /**
     * Generate HTML for single note
     */
    private function generateNoteHtml($note) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .header { border-bottom: 3px solid #007BFF; padding-bottom: 20px; margin-bottom: 30px; }
                .title { color: #007BFF; font-size: 24px; font-weight: bold; }
                .meta { color: #666; font-size: 14px; margin-top: 10px; }
                .content { margin-top: 20px; white-space: pre-wrap; }
                .footer { margin-top: 40px; border-top: 1px solid #ccc; padding-top: 20px; text-align: center; color: #666; }
            </style>
        </head>
        <body>
            <div class='header'>
                <div class='title'>EduLearn - Personal Note</div>
                <div class='meta'>
                    <strong>Student:</strong> {$this->userName}<br>
                    <strong>Module:</strong> {$note['module_name']}<br>
                    <strong>Note:</strong> {$note['title']}<br>
                    <strong>Created:</strong> " . date('F j, Y g:i A', strtotime($note['created_at'])) . "<br>
                    <strong>Last Modified:</strong> " . date('F j, Y g:i A', strtotime($note['updated_at'])) . "
                </div>
            </div>
            <div class='content'>{$note['content']}</div>
            <div class='footer'>
                Exported on " . date('F j, Y g:i A') . " from EduLearn Platform
            </div>
        </body>
        </html>";
    }
    
    /**
     * Generate HTML for module
     */
    private function generateModuleHtml($module) {
        $html = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .header { border-bottom: 3px solid #007BFF; padding-bottom: 20px; margin-bottom: 30px; }
                .title { color: #007BFF; font-size: 24px; font-weight: bold; }
                .meta { color: #666; font-size: 14px; margin-top: 10px; }
                .note { margin: 30px 0; border: 1px solid #ddd; padding: 20px; border-radius: 5px; }
                .note-title { color: #007BFF; font-size: 18px; font-weight: bold; margin-bottom: 10px; }
                .note-meta { color: #666; font-size: 12px; margin-bottom: 15px; }
                .note-content { white-space: pre-wrap; }
                .footer { margin-top: 40px; border-top: 1px solid #ccc; padding-top: 20px; text-align: center; color: #666; }
            </style>
        </head>
        <body>
            <div class='header'>
                <div class='title'>EduLearn - Module Export</div>
                <div class='meta'>
                    <strong>Student:</strong> {$this->userName}<br>
                    <strong>Module:</strong> {$module['name']}<br>";
        
        if (!empty($module['description'])) {
            $html .= "<strong>Description:</strong> {$module['description']}<br>";
        }
        
        $html .= "
                    <strong>Created:</strong> " . date('F j, Y g:i A', strtotime($module['created_at'])) . "<br>
                    <strong>Total Notes:</strong> " . count($module['notes']) . "
                </div>
            </div>";
        
        foreach ($module['notes'] as $index => $note) {
            $html .= "
            <div class='note'>
                <div class='note-title'>Note " . ($index + 1) . ": {$note['title']}</div>
                <div class='note-meta'>
                    Created: " . date('F j, Y g:i A', strtotime($note['created_at'])) . " | 
                    Modified: " . date('F j, Y g:i A', strtotime($note['updated_at'])) . "
                </div>
                <div class='note-content'>{$note['content']}</div>
            </div>";
        }
        
        $html .= "
            <div class='footer'>
                Exported on " . date('F j, Y g:i A') . " from EduLearn Platform
            </div>
        </body>
        </html>";
        
        return $html;
    }
    
    /**
     * Generate HTML for all notes
     */
    private function generateAllNotesHtml($notes) {
        $html = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .header { border-bottom: 3px solid #007BFF; padding-bottom: 20px; margin-bottom: 30px; }
                .title { color: #007BFF; font-size: 24px; font-weight: bold; }
                .meta { color: #666; font-size: 14px; margin-top: 10px; }
                .module-section { margin: 40px 0; }
                .module-title { color: #007BFF; font-size: 20px; font-weight: bold; border-bottom: 2px solid #007BFF; padding-bottom: 10px; }
                .note { margin: 20px 0; border: 1px solid #ddd; padding: 15px; border-radius: 5px; }
                .note-title { color: #333; font-size: 16px; font-weight: bold; margin-bottom: 8px; }
                .note-meta { color: #666; font-size: 12px; margin-bottom: 12px; }
                .note-content { white-space: pre-wrap; font-size: 14px; }
                .footer { margin-top: 40px; border-top: 1px solid #ccc; padding-top: 20px; text-align: center; color: #666; }
            </style>
        </head>
        <body>
            <div class='header'>
                <div class='title'>EduLearn - All Notes Export</div>
                <div class='meta'>
                    <strong>Student:</strong> {$this->userName}<br>
                    <strong>Total Notes:</strong> " . count($notes) . "<br>
                    <strong>Exported on:</strong> " . date('F j, Y g:i A') . "
                </div>
            </div>";
        
        $currentModule = '';
        $noteIndex = 1;
        
        foreach ($notes as $note) {
            if ($currentModule !== $note['module_name']) {
                if ($currentModule !== '') {
                    $html .= "</div>";
                }
                $currentModule = $note['module_name'];
                $html .= "<div class='module-section'>
                         <div class='module-title'>Module: {$currentModule}</div>";
            }
            
            $html .= "
            <div class='note'>
                <div class='note-title'>Note {$noteIndex}: {$note['title']}</div>
                <div class='note-meta'>
                    Created: " . date('F j, Y g:i A', strtotime($note['created_at'])) . " | 
                    Modified: " . date('F j, Y g:i A', strtotime($note['updated_at'])) . "
                </div>
                <div class='note-content'>{$note['content']}</div>
            </div>";
            
            $noteIndex++;
        }
        
        if ($currentModule !== '') {
            $html .= "</div>";
        }
        
        $html .= "
            <div class='footer'>
                End of All Notes Export - EduLearn Platform
            </div>
        </body>
        </html>";
        
        return $html;
    }
    
    /**
     * Output PDF file
     */
    private function outputPdf($pdf, $filename) {
        $html = $pdf->getContent();
        
        // For now, we'll output as HTML file with PDF-like styling
        // In production, use proper HTML to PDF conversion library like wkhtmltopdf or TCPDF
        
        header('Content-Type: text/html; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '.html"');
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
        
        echo $html;
        exit;
    }
    
    /**
     * Download file helper
     */
    private function downloadFile($content, $filename, $mimeType) {
        header('Content-Type: ' . $mimeType . '; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($content));
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
        
        echo $content;
        exit;
    }
    
    /**
     * Sanitize filename for safe download
     */
    private function sanitizeFilename($filename) {
        $filename = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $filename);
        $filename = preg_replace('/_{2,}/', '_', $filename);
        $filename = trim($filename, '_');
        return substr($filename, 0, 50); // Limit length
    }
}
?>
