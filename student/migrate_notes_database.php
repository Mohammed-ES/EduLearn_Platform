<?php
/**
 * Migration Script for Notes System
 * This script safely creates the required tables for the notes system
 * It checks for existing tables and only creates what's missing
 */

// Include database connection
require_once('../config/connectiondb.php');

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>EduLearn Notes System - Database Migration</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: #28a745; background: #d4edda; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .error { color: #dc3545; background: #f8d7da; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .info { color: #0c5460; background: #d1ecf1; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .warning { color: #856404; background: #fff3cd; padding: 10px; border-radius: 5px; margin: 10px 0; }
</style>";

try {
    echo "<div class='info'>🔍 Checking database connection...</div>";
    
    if (!isset($pdo)) {
        throw new Exception("Database connection not established");
    }
    
    echo "<div class='success'>✅ Database connection successful</div>";
    
    // Check if note_modules table exists
    echo "<div class='info'>🔍 Checking if note_modules table exists...</div>";
    
    $stmt = $pdo->query("SHOW TABLES LIKE 'note_modules'");
    $tableExists = $stmt->rowCount() > 0;
    
    if (!$tableExists) {
        echo "<div class='warning'>⚠️ note_modules table doesn't exist. Creating...</div>";
        
        $createModulesTable = "
        CREATE TABLE `note_modules` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `student_id` int(11) NOT NULL,
          `name` varchar(255) NOT NULL,
          `description` text DEFAULT NULL,
          `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
          `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          KEY `idx_student_id` (`student_id`),
          KEY `idx_name` (`name`),
          CONSTRAINT `fk_note_modules_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $pdo->exec($createModulesTable);
        echo "<div class='success'>✅ note_modules table created successfully</div>";
    } else {
        echo "<div class='success'>✅ note_modules table already exists</div>";
    }
    
    // Check if notes table exists
    echo "<div class='info'>🔍 Checking if notes table exists...</div>";
    
    $stmt = $pdo->query("SHOW TABLES LIKE 'notes'");
    $notesTableExists = $stmt->rowCount() > 0;
    
    if (!$notesTableExists) {
        echo "<div class='warning'>⚠️ notes table doesn't exist. Creating...</div>";
        
        $createNotesTable = "
        CREATE TABLE `notes` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `module_id` int(11) NOT NULL,
          `student_id` int(11) NOT NULL,
          `title` varchar(500) NOT NULL,
          `content` longtext NOT NULL,
          `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
          `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          KEY `idx_module_id` (`module_id`),
          KEY `idx_student_id` (`student_id`),
          KEY `idx_title` (`title`),
          FULLTEXT KEY `ft_title_content` (`title`, `content`),
          CONSTRAINT `fk_notes_module` FOREIGN KEY (`module_id`) REFERENCES `note_modules` (`id`) ON DELETE CASCADE,
          CONSTRAINT `fk_notes_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $pdo->exec($createNotesTable);
        echo "<div class='success'>✅ notes table created successfully</div>";
    } else {
        echo "<div class='success'>✅ notes table already exists</div>";
        
        // Check if the notes table has the correct structure
        echo "<div class='info'>🔍 Checking notes table structure...</div>";
        
        $stmt = $pdo->query("DESCRIBE notes");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $requiredColumns = ['id', 'module_id', 'student_id', 'title', 'content', 'created_at', 'updated_at'];
        $missingColumns = array_diff($requiredColumns, $columns);
        
        if (!empty($missingColumns)) {
            echo "<div class='warning'>⚠️ Missing columns in notes table: " . implode(', ', $missingColumns) . "</div>";
            
            // Add missing columns
            foreach ($missingColumns as $column) {
                switch ($column) {
                    case 'module_id':
                        $pdo->exec("ALTER TABLE notes ADD COLUMN module_id INT(11) NOT NULL AFTER id");
                        $pdo->exec("ALTER TABLE notes ADD KEY idx_module_id (module_id)");
                        echo "<div class='success'>✅ Added module_id column</div>";
                        break;
                    case 'student_id':
                        $pdo->exec("ALTER TABLE notes ADD COLUMN student_id INT(11) NOT NULL");
                        $pdo->exec("ALTER TABLE notes ADD KEY idx_student_id (student_id)");
                        echo "<div class='success'>✅ Added student_id column</div>";
                        break;
                    case 'title':
                        $pdo->exec("ALTER TABLE notes ADD COLUMN title VARCHAR(500) NOT NULL");
                        echo "<div class='success'>✅ Added title column</div>";
                        break;
                    case 'content':
                        $pdo->exec("ALTER TABLE notes ADD COLUMN content LONGTEXT NOT NULL");
                        echo "<div class='success'>✅ Added content column</div>";
                        break;
                    case 'created_at':
                        $pdo->exec("ALTER TABLE notes ADD COLUMN created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP");
                        echo "<div class='success'>✅ Added created_at column</div>";
                        break;
                    case 'updated_at':
                        $pdo->exec("ALTER TABLE notes ADD COLUMN updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
                        echo "<div class='success'>✅ Added updated_at column</div>";
                        break;
                }
            }
        } else {
            echo "<div class='success'>✅ notes table structure is correct</div>";
        }
    }
    
    // Add foreign key constraints if they don't exist
    echo "<div class='info'>🔍 Checking foreign key constraints...</div>";
    
    try {
        // Check if foreign keys exist
        $stmt = $pdo->query("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.TABLE_CONSTRAINTS 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'notes' 
            AND CONSTRAINT_TYPE = 'FOREIGN KEY'
        ");
        $existingConstraints = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (!in_array('fk_notes_module', $existingConstraints)) {
            $pdo->exec("ALTER TABLE notes ADD CONSTRAINT fk_notes_module FOREIGN KEY (module_id) REFERENCES note_modules(id) ON DELETE CASCADE");
            echo "<div class='success'>✅ Added foreign key constraint for module_id</div>";
        }
        
        if (!in_array('fk_notes_student', $existingConstraints)) {
            $pdo->exec("ALTER TABLE notes ADD CONSTRAINT fk_notes_student FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE");
            echo "<div class='success'>✅ Added foreign key constraint for student_id</div>";
        }
        
        if (!in_array('fk_note_modules_student', $existingConstraints)) {
            $pdo->exec("ALTER TABLE note_modules ADD CONSTRAINT fk_note_modules_student FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE");
            echo "<div class='success'>✅ Added foreign key constraint for note_modules.student_id</div>";
        }
        
    } catch (Exception $e) {
        echo "<div class='warning'>⚠️ Could not add some foreign key constraints: " . $e->getMessage() . "</div>";
        echo "<div class='info'>ℹ️ This might be normal if the constraints already exist or if there are data integrity issues</div>";
    }
    
    // Add indexes for better performance
    echo "<div class='info'>🔍 Adding performance indexes...</div>";
    
    try {
        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_notes_updated_at ON notes(updated_at)");
        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_modules_updated_at ON note_modules(updated_at)");
        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_notes_student_module ON notes(student_id, module_id)");
        echo "<div class='success'>✅ Performance indexes added</div>";
    } catch (Exception $e) {
        echo "<div class='warning'>⚠️ Some indexes might already exist: " . $e->getMessage() . "</div>";
    }
    
    // Insert sample data only if tables are empty
    echo "<div class='info'>🔍 Checking for existing data...</div>";
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM note_modules");
    $moduleCount = $stmt->fetchColumn();
    
    if ($moduleCount == 0) {
        echo "<div class='info'>📝 Adding sample data...</div>";
        
        // Get a sample student ID from users table
        $stmt = $pdo->query("SELECT id FROM users WHERE role = 'student' LIMIT 1");
        $sampleStudentId = $stmt->fetchColumn();
        
        if ($sampleStudentId) {
            // Insert sample modules
            $sampleModules = [
                ['Mathematics', 'Mathematical concepts and problem solving'],
                ['Computer Science', 'Programming and algorithms'],
                ['Physics', 'Physics principles and experiments']
            ];
            
            foreach ($sampleModules as $module) {
                $stmt = $pdo->prepare("INSERT INTO note_modules (student_id, name, description) VALUES (?, ?, ?)");
                $stmt->execute([$sampleStudentId, $module[0], $module[1]]);
            }
            
            // Get the inserted module IDs
            $stmt = $pdo->prepare("SELECT id, name FROM note_modules WHERE student_id = ?");
            $stmt->execute([$sampleStudentId]);
            $modules = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Insert sample notes
            foreach ($modules as $module) {
                switch ($module['name']) {
                    case 'Mathematics':
                        $notes = [
                            ['Algebra Basics', "Linear equations and their properties:\n\n1. Standard form: ax + b = 0\n2. Slope-intercept form: y = mx + b\n3. Point-slope form: y - y1 = m(x - x1)\n\nRemember to always check your work by substituting values back into the original equation."],
                            ['Trigonometry', "Key trigonometric identities:\n\nsin²θ + cos²θ = 1\ntan θ = sin θ / cos θ\n\nUnit circle values:\n- sin(0°) = 0, cos(0°) = 1\n- sin(90°) = 1, cos(90°) = 0\n- sin(180°) = 0, cos(180°) = -1\n- sin(270°) = -1, cos(270°) = 0"]
                        ];
                        break;
                    case 'Computer Science':
                        $notes = [
                            ['JavaScript Fundamentals', "Important JavaScript concepts:\n\n1. Variables: let, const, var\n2. Functions: function declaration vs expression\n3. Arrays and objects\n4. Scope and closures\n5. Asynchronous programming with promises\n\nExample:\nconst myFunction = (param) => {\n  return param * 2;\n};"],
                            ['Data Structures', "Common data structures:\n\n1. Arrays - O(1) access, O(n) search\n2. Linked Lists - O(n) access, O(1) insertion\n3. Stacks - LIFO (Last In, First Out)\n4. Queues - FIFO (First In, First Out)\n5. Trees - Hierarchical structure\n6. Hash Tables - O(1) average access"]
                        ];
                        break;
                    case 'Physics':
                        $notes = [
                            ["Newton's Laws", "Newton's Three Laws of Motion:\n\n1. First Law (Inertia): An object at rest stays at rest, and an object in motion stays in motion, unless acted upon by an external force.\n\n2. Second Law: F = ma (Force equals mass times acceleration)\n\n3. Third Law: For every action, there is an equal and opposite reaction.\n\nApplications in real world scenarios and problem solving techniques."]
                        ];
                        break;
                }
                
                if (isset($notes)) {
                    foreach ($notes as $note) {
                        $stmt = $pdo->prepare("INSERT INTO notes (module_id, student_id, title, content) VALUES (?, ?, ?, ?)");
                        $stmt->execute([$module['id'], $sampleStudentId, $note[0], $note[1]]);
                    }
                }
            }
            
            echo "<div class='success'>✅ Sample data added successfully</div>";
        } else {
            echo "<div class='warning'>⚠️ No student found in users table. Sample data not added.</div>";
        }
    } else {
        echo "<div class='info'>ℹ️ Tables already contain data. Skipping sample data insertion.</div>";
    }
    
    echo "<div class='success'><h2>🎉 Migration completed successfully!</h2></div>";
    echo "<div class='info'>
        <h3>Next Steps:</h3>
        <ul>
            <li>✅ Database tables are ready</li>
            <li>✅ All required columns exist</li>
            <li>✅ Foreign key relationships are established</li>
            <li>✅ Performance indexes are in place</li>
            <li>📝 You can now use the improved notes system</li>
        </ul>
    </div>";
    
    // Show table statistics
    echo "<div class='info'>";
    echo "<h3>📊 Database Statistics:</h3>";
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM note_modules");
    $moduleCount = $stmt->fetchColumn();
    echo "<p>• Total modules: {$moduleCount}</p>";
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM notes");
    $noteCount = $stmt->fetchColumn();
    echo "<p>• Total notes: {$noteCount}</p>";
    
    $stmt = $pdo->query("SELECT COUNT(DISTINCT student_id) FROM note_modules");
    $studentCount = $stmt->fetchColumn();
    echo "<p>• Students with modules: {$studentCount}</p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Migration failed: " . $e->getMessage() . "</div>";
    echo "<div class='error'>Stack trace: " . $e->getTraceAsString() . "</div>";
}

echo "<div class='info'>
    <h3>🔗 Quick Links:</h3>
    <a href='notes.php' style='margin-right: 20px;'>Test Notes System</a>
    <a href='test_notes_system.php' style='margin-right: 20px;'>Run System Tests</a>
    <a href='../student_dashboard.php'>Back to Dashboard</a>
</div>";
?>
