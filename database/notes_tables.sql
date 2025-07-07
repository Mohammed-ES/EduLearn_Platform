-- SQL script to create tables for the improved notes system
-- Run this script to add the necessary tables to your existing EduLearn database

-- Table for note modules
CREATE TABLE IF NOT EXISTS `note_modules` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table for individual notes
CREATE TABLE IF NOT EXISTS `notes` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert some sample data for testing (optional)
-- You can remove this section if you don't want sample data

-- Sample modules
INSERT IGNORE INTO `note_modules` (`id`, `student_id`, `name`, `description`, `created_at`, `updated_at`) VALUES
(1, 1, 'Mathematics', 'Mathematical concepts and problem solving', NOW(), NOW()),
(2, 1, 'Computer Science', 'Programming and algorithms', NOW(), NOW()),
(3, 1, 'Physics', 'Physics principles and experiments', NOW(), NOW());

-- Sample notes
INSERT IGNORE INTO `notes` (`id`, `module_id`, `student_id`, `title`, `content`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'Algebra Basics', 'Linear equations and their properties:\n\n1. Standard form: ax + b = 0\n2. Slope-intercept form: y = mx + b\n3. Point-slope form: y - y1 = m(x - x1)\n\nRemember to always check your work by substituting values back into the original equation.', NOW(), NOW()),
(2, 1, 1, 'Trigonometry', 'Key trigonometric identities:\n\nsin²θ + cos²θ = 1\ntan θ = sin θ / cos θ\n\nUnit circle values:\n- sin(0°) = 0, cos(0°) = 1\n- sin(90°) = 1, cos(90°) = 0\n- sin(180°) = 0, cos(180°) = -1\n- sin(270°) = -1, cos(270°) = 0', NOW(), NOW()),
(3, 2, 1, 'JavaScript Fundamentals', 'Important JavaScript concepts:\n\n1. Variables: let, const, var\n2. Functions: function declaration vs expression\n3. Arrays and objects\n4. Scope and closures\n5. Asynchronous programming with promises\n\nExample:\nconst myFunction = (param) => {\n  return param * 2;\n};', NOW(), NOW()),
(4, 2, 1, 'Data Structures', 'Common data structures:\n\n1. Arrays - O(1) access, O(n) search\n2. Linked Lists - O(n) access, O(1) insertion\n3. Stacks - LIFO (Last In, First Out)\n4. Queues - FIFO (First In, First Out)\n5. Trees - Hierarchical structure\n6. Hash Tables - O(1) average access', NOW(), NOW()),
(5, 3, 1, 'Newton\'s Laws', 'Newton\'s Three Laws of Motion:\n\n1. First Law (Inertia): An object at rest stays at rest, and an object in motion stays in motion, unless acted upon by an external force.\n\n2. Second Law: F = ma (Force equals mass times acceleration)\n\n3. Third Law: For every action, there is an equal and opposite reaction.\n\nApplications in real world scenarios and problem solving techniques.', NOW(), NOW());

-- Create indexes for better performance
CREATE INDEX idx_notes_updated_at ON notes(updated_at);
CREATE INDEX idx_modules_updated_at ON note_modules(updated_at);
CREATE INDEX idx_notes_student_module ON notes(student_id, module_id);
