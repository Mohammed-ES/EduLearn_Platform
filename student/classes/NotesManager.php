<?php
/**
 * NotesManager Class
 * Handles all note and module operations with proper database integration
 */
class NotesManager {
    private $db;
    private $userId;
    
    public function __construct($database, $userId) {
        $this->db = $database;
        $this->userId = $userId;
    }
      /**
     * Get all modules for the current user
     */
    public function getModules() {
        try {
            // Get modules
            $stmt = $this->db->prepare("
                SELECT m.*, COUNT(n.id) as note_count 
                FROM note_modules m 
                LEFT JOIN notes n ON m.id = n.module_id 
                WHERE m.student_id = ? 
                GROUP BY m.id 
                ORDER BY m.created_at DESC
            ");
            $stmt->execute([$this->userId]);
            $modules = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get notes for each module
            foreach ($modules as &$module) {
                $notesStmt = $this->db->prepare("
                    SELECT * FROM notes 
                    WHERE module_id = ? AND student_id = ? 
                    ORDER BY updated_at DESC
                ");
                $notesStmt->execute([$module['id'], $this->userId]);
                $module['notes'] = $notesStmt->fetchAll(PDO::FETCH_ASSOC);
            }
            
            return $modules;
        } catch (PDOException $e) {
            error_log("Error fetching modules: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get notes for a specific module
     */
    public function getModuleNotes($moduleId) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM notes 
                WHERE module_id = ? AND student_id = ? 
                ORDER BY updated_at DESC
            ");
            $stmt->execute([$moduleId, $this->userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching notes: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Create a new module
     */
    public function createModule($name, $description = '') {
        try {
            // Check if module already exists for this user
            $stmt = $this->db->prepare("SELECT id FROM note_modules WHERE name = ? AND student_id = ?");
            $stmt->execute([$name, $this->userId]);
            
            if ($stmt->fetch()) {
                return ['success' => false, 'message' => 'Module already exists'];
            }
            
            $stmt = $this->db->prepare("
                INSERT INTO note_modules (name, description, student_id, created_at, updated_at) 
                VALUES (?, ?, ?, NOW(), NOW())
            ");
            $stmt->execute([$name, $description, $this->userId]);
            
            return [
                'success' => true, 
                'message' => 'Module created successfully',
                'id' => $this->db->lastInsertId()
            ];
        } catch (PDOException $e) {
            error_log("Error creating module: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to create module'];
        }
    }
    
    /**
     * Update a module
     */
    public function updateModule($moduleId, $name, $description = '') {
        try {
            $stmt = $this->db->prepare("
                UPDATE note_modules 
                SET name = ?, description = ?, updated_at = NOW() 
                WHERE id = ? AND student_id = ?
            ");
            $stmt->execute([$name, $description, $moduleId, $this->userId]);
            
            return [
                'success' => true, 
                'message' => 'Module updated successfully'
            ];
        } catch (PDOException $e) {
            error_log("Error updating module: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to update module'];
        }
    }
    
    /**
     * Delete a module and all its notes
     */
    public function deleteModule($moduleId) {
        try {
            $this->db->beginTransaction();
            
            // First delete all notes in the module
            $stmt = $this->db->prepare("DELETE FROM notes WHERE module_id = ? AND student_id = ?");
            $stmt->execute([$moduleId, $this->userId]);
            
            // Then delete the module
            $stmt = $this->db->prepare("DELETE FROM note_modules WHERE id = ? AND student_id = ?");
            $stmt->execute([$moduleId, $this->userId]);
            
            $this->db->commit();
            
            return ['success' => true, 'message' => 'Module deleted successfully'];
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error deleting module: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to delete module'];
        }
    }
    
    /**
     * Create a new note
     */
    public function createNote($moduleId, $title, $content) {
        try {
            // Verify module belongs to user
            $stmt = $this->db->prepare("SELECT id FROM note_modules WHERE id = ? AND student_id = ?");
            $stmt->execute([$moduleId, $this->userId]);
            
            if (!$stmt->fetch()) {
                return ['success' => false, 'message' => 'Module not found'];
            }
            
            $stmt = $this->db->prepare("
                INSERT INTO notes (module_id, student_id, title, content, created_at, updated_at) 
                VALUES (?, ?, ?, ?, NOW(), NOW())
            ");
            $stmt->execute([$moduleId, $this->userId, $title, $content]);
            
            return [
                'success' => true, 
                'message' => 'Note created successfully',
                'id' => $this->db->lastInsertId()
            ];
        } catch (PDOException $e) {
            error_log("Error creating note: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to create note'];
        }
    }
    
    /**
     * Update a note
     */
    public function updateNote($noteId, $title, $content) {
        try {
            $stmt = $this->db->prepare("
                UPDATE notes 
                SET title = ?, content = ?, updated_at = NOW() 
                WHERE id = ? AND student_id = ?
            ");
            $stmt->execute([$title, $content, $noteId, $this->userId]);
            
            return ['success' => true, 'message' => 'Note updated successfully'];
        } catch (PDOException $e) {
            error_log("Error updating note: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to update note'];
        }
    }
    
    /**
     * Delete a note
     */
    public function deleteNote($noteId) {
        try {
            $stmt = $this->db->prepare("DELETE FROM notes WHERE id = ? AND student_id = ?");
            $stmt->execute([$noteId, $this->userId]);
            
            return ['success' => true, 'message' => 'Note deleted successfully'];
        } catch (PDOException $e) {
            error_log("Error deleting note: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to delete note'];
        }
    }
    
    /**
     * Search notes and modules
     */
    public function searchContent($query) {
        try {
            $searchTerm = "%{$query}%";
            
            // Search in modules
            $stmt = $this->db->prepare("
                SELECT 'module' as type, id, name as title, description as content, created_at
                FROM note_modules 
                WHERE student_id = ? AND (name LIKE ? OR description LIKE ?)
                
                UNION ALL
                
                SELECT 'note' as type, n.id, n.title, n.content, n.created_at
                FROM notes n
                JOIN note_modules m ON n.module_id = m.id
                WHERE n.student_id = ? AND (n.title LIKE ? OR n.content LIKE ?)
                
                ORDER BY created_at DESC
            ");
            
            $stmt->execute([$this->userId, $searchTerm, $searchTerm, $this->userId, $searchTerm, $searchTerm]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error searching content: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get module details with notes for export
     */
    public function getModuleForExport($moduleId) {
        try {
            // Get module info
            $stmt = $this->db->prepare("
                SELECT * FROM note_modules 
                WHERE id = ? AND student_id = ?
            ");
            $stmt->execute([$moduleId, $this->userId]);
            $module = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$module) {
                return null;
            }
            
            // Get all notes for this module
            $notes = $this->getModuleNotes($moduleId);
            $module['notes'] = $notes;
            
            return $module;
        } catch (PDOException $e) {
            error_log("Error getting module for export: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get single note for export
     */
    public function getNoteForExport($noteId) {
        try {
            $stmt = $this->db->prepare("
                SELECT n.*, m.name as module_name 
                FROM notes n
                JOIN note_modules m ON n.module_id = m.id
                WHERE n.id = ? AND n.student_id = ?
            ");
            $stmt->execute([$noteId, $this->userId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting note for export: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get all notes for bulk export
     */
    public function getAllNotesForExport() {
        try {
            $stmt = $this->db->prepare("
                SELECT n.*, m.name as module_name 
                FROM notes n
                JOIN note_modules m ON n.module_id = m.id
                WHERE n.student_id = ?
                ORDER BY m.name, n.title
            ");
            $stmt->execute([$this->userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting all notes for export: " . $e->getMessage());
            return [];
        }
    }
}
?>
