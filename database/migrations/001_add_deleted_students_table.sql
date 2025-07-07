-- Migration: Add deleted_students table for soft deletes
-- Created: 2025-06-12

CREATE TABLE IF NOT EXISTS `deleted_students` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `original_id` int(11) NOT NULL,
  `fullname` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `username` varchar(100) NOT NULL,
  `deleted_at` datetime NOT NULL,
  `deleted_by` int(11) NOT NULL,
  `restore_count` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `deleted_by` (`deleted_by`),
  KEY `deleted_at` (`deleted_at`),
  KEY `original_id` (`original_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Add foreign key constraint
ALTER TABLE `deleted_students`
  ADD CONSTRAINT `fk_deleted_by_admin` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;
