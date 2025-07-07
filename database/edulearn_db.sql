-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `edulearn_db`
--

-- --------------------------------------------------------

--
-- Structure de la table `admin_settings`
--

CREATE TABLE `admin_settings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `email_notifications` tinyint(1) NOT NULL DEFAULT 1,
  `system_notifications` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `ai_conversations`
--

CREATE TABLE `ai_conversations` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT 'New Conversation',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `ai_messages`
--

CREATE TABLE `ai_messages` (
  `id` int(11) NOT NULL,
  `conversation_id` int(11) NOT NULL,
  `role` enum('user','assistant') NOT NULL,
  `content` text NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `ai_usage`
--

CREATE TABLE `ai_usage` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `tokens_used` int(11) NOT NULL DEFAULT 0,
  `date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `status` enum('draft','published','expired') NOT NULL DEFAULT 'draft',
  `author_id` int(11) NOT NULL,
  `importance` enum('low','medium','high') NOT NULL DEFAULT 'medium',
  `published_at` datetime DEFAULT NULL,
  `expiry_date` datetime DEFAULT NULL COMMENT 'Date when the announcement will be automatically archived',
  `deleted_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `announcements`
--

INSERT INTO `announcements` (`id`, `title`, `content`, `status`, `author_id`, `importance`, `published_at`, `expiry_date`, `deleted_at`, `created_at`, `updated_at`) VALUES
(4, 'Announcement: Eid Al-Adha 2025', 'Eid Al-Adha will be celebrated on June 7, 2025. May this occasion bring joy, peace, and unity. Wishing you all a wonderful celebration with family and friends!', 'published', 1, 'medium', '2025-06-13 12:04:07', NULL, NULL, '2025-06-12 23:45:45', '2025-06-12 23:45:45'),
(5, 'Attention Students – EduLearn Updates', 'New educational resources and modules have been updated on the EduLearn platform. Check them out to enhance your learning experience. Stay curious and keep learning!', 'published', 1, 'low', NULL, NULL, NULL, '2025-06-12 23:46:48', '2025-06-12 23:46:48'),
(6, 'Important Reminder: Student Feedback', 'We value your thoughts! Please take a moment to share your feedback on EduLearn. Your input helps us improve and create a better learning experience for everyone.', 'published', 1, 'high', '2025-06-13 12:21:27', NULL, NULL, '2025-06-12 23:47:22', '2025-06-13 12:21:20');

-- --------------------------------------------------------

--
-- Structure de la table `announcement_files`
--

CREATE TABLE `announcement_files` (
  `id` int(11) NOT NULL,
  `announcement_id` int(11) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_type` varchar(100) NOT NULL,
  `file_size` int(11) NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `announcement_views`
--

CREATE TABLE `announcement_views` (
  `id` int(11) NOT NULL,
  `announcement_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `viewed_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `deleted_students`
--

CREATE TABLE `deleted_students` (
  `id` int(11) NOT NULL,
  `fullname` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','student') DEFAULT 'student',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `deleted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `deleted_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `color` varchar(20) DEFAULT '#4CAF50',
  `all_day` tinyint(1) NOT NULL DEFAULT 0,
  `repeat_type` enum('none','daily','weekly','monthly','yearly') NOT NULL DEFAULT 'none',
  `reminder` int(11) DEFAULT NULL COMMENT 'minutes before event',
  `deleted_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `events`
--

INSERT INTO `events` (`id`, `user_id`, `title`, `description`, `start_time`, `end_time`, `location`, `color`, `all_day`, `repeat_type`, `reminder`, `deleted_at`, `created_at`, `updated_at`) VALUES
(4, 8, 'Advanced Programming', ' Reviewing algorithms and data structures before the final exam.', '2025-06-18 10:30:00', '2025-06-18 11:30:00', 'exam', '#dc3545', 0, 'none', NULL, NULL, '0000-00-00 00:00:00', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Structure de la table `event_categories`
--

CREATE TABLE `event_categories` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `color` varchar(20) NOT NULL DEFAULT '#4CAF50',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `notes`
--

CREATE TABLE `notes` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `category` varchar(50) DEFAULT NULL,
  `color` varchar(20) DEFAULT '#ffffff',
  `pinned` tinyint(1) NOT NULL DEFAULT 0,
  `deleted_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `note_categories`
--

CREATE TABLE `note_categories` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `color` varchar(20) DEFAULT '#3498db',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `note_modules`
--

CREATE TABLE `note_modules` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `note_modules`
--

INSERT INTO `note_modules` (`id`, `student_id`, `name`, `description`, `created_at`, `updated_at`) VALUES
(1, 1, 'Mathematics', 'Mathematical concepts and problem solving', '2025-06-13 15:28:43', '2025-06-13 15:28:43'),
(2, 1, 'Computer Science', 'Programming and algorithms', '2025-06-13 15:28:43', '2025-06-13 15:28:43'),
(3, 1, 'Physics', 'Physics principles and experiments', '2025-06-13 15:28:43', '2025-06-13 15:28:43');

-- --------------------------------------------------------

--
-- Structure de la table `note_tags`
--

CREATE TABLE `note_tags` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `note_tag_relations`
--

CREATE TABLE `note_tag_relations` (
  `note_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL,
  `expires_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `quizzes`
--

CREATE TABLE `quizzes` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `author_id` int(11) NOT NULL,
  `time_limit` int(11) DEFAULT NULL COMMENT 'in minutes',
  `pass_score` int(11) DEFAULT 60 COMMENT 'percentage',
  `status` enum('draft','published','archived') NOT NULL DEFAULT 'draft',
  `allow_retake` tinyint(1) NOT NULL DEFAULT 1,
  `max_attempts` int(11) DEFAULT NULL,
  `show_answers` tinyint(1) NOT NULL DEFAULT 1,
  `randomize_questions` tinyint(1) NOT NULL DEFAULT 0,
  `published_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `quiz_attempts`
--

CREATE TABLE `quiz_attempts` (
  `id` int(11) NOT NULL,
  `quiz_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `score` int(11) DEFAULT NULL,
  `time_spent` int(11) DEFAULT NULL COMMENT 'in seconds',
  `completed` tinyint(1) NOT NULL DEFAULT 0,
  `started_at` datetime NOT NULL,
  `completed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `quiz_attempt_answers`
--

CREATE TABLE `quiz_attempt_answers` (
  `id` int(11) NOT NULL,
  `attempt_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `answer_text` text DEFAULT NULL,
  `selected_option_id` int(11) DEFAULT NULL,
  `is_correct` tinyint(1) NOT NULL DEFAULT 0,
  `points_earned` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `quiz_questions`
--

CREATE TABLE `quiz_questions` (
  `id` int(11) NOT NULL,
  `quiz_id` int(11) NOT NULL,
  `question` text NOT NULL,
  `question_type` enum('multiple_choice','true_false','short_answer','matching') NOT NULL DEFAULT 'multiple_choice',
  `points` int(11) NOT NULL DEFAULT 1,
  `explanation` text DEFAULT NULL,
  `position` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `quiz_question_options`
--

CREATE TABLE `quiz_question_options` (
  `id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `option_text` text NOT NULL,
  `is_correct` tinyint(1) NOT NULL DEFAULT 0,
  `position` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(128) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `payload` text NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `student_profiles`
--

CREATE TABLE `student_profiles` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `student_id` varchar(50) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `academic_level` varchar(50) DEFAULT NULL,
  `enrollment_date` date DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `major` varchar(100) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `student_settings`
--

CREATE TABLE `student_settings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `email_notifications` tinyint(1) NOT NULL DEFAULT 1,
  `system_notifications` tinyint(1) NOT NULL DEFAULT 1,
  `reminder_notifications` tinyint(1) NOT NULL DEFAULT 1,
  `theme_preference` varchar(20) DEFAULT 'light',
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `system_settings`
--

CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_type` varchar(20) NOT NULL DEFAULT 'text',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `system_settings`
--

INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `created_at`, `updated_at`) VALUES
(1, 'site_name', 'EduLearn Platform', 'text', '2025-06-04 20:53:33', '2025-06-04 20:53:33'),
(2, 'site_description', 'Modern Educational Experience', 'text', '2025-06-04 20:53:33', '2025-06-04 20:53:33'),
(3, 'admin_email', 'admin@edulearn.com', 'email', '2025-06-04 20:53:33', '2025-06-04 20:53:33'),
(4, 'max_upload_size', '5', 'number', '2025-06-04 20:53:33', '2025-06-04 20:53:33'),
(5, 'default_language', 'en', 'text', '2025-06-04 20:53:33', '2025-06-04 20:53:33'),
(6, 'maintenance_mode', '0', 'boolean', '2025-06-04 20:53:33', '2025-06-04 20:53:33'),
(7, 'allow_registration', '1', 'boolean', '2025-06-04 20:53:33', '2025-06-04 20:53:33'),
(8, 'require_email_verification', '0', 'boolean', '2025-06-04 20:53:33', '2025-06-04 20:53:33'),
(9, 'announcement_per_page', '10', 'number', '2025-06-04 20:53:33', '2025-06-04 20:53:33'),
(10, 'notes_per_page', '20', 'number', '2025-06-04 20:53:33', '2025-06-04 20:53:33');

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `fullname` varchar(255) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `user_role` enum('admin','student') NOT NULL DEFAULT 'student',
  `cohort` varchar(50) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `phone` varchar(20) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `password_changed_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `fullname`, `username`, `email`, `password`, `user_role`, `cohort`, `status`, `phone`, `profile_image`, `bio`, `last_login`, `password_changed_at`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'Admin User', 'admin', 'admin@edulearn.com', '$2y$10$F/7EEKf2sQthrkNk.GC3JOkIvNel8IRTtZPyDAJWVMZjc.xL5eyuu', 'admin', NULL, 'active', '', NULL, NULL, '2025-06-13 12:07:14', NULL, NULL, '2025-06-04 20:53:33', '2025-06-04 20:53:33'),
(6, 'Bilal El Azzam', 'bilal2', 'bilal@edulearn.com', '$2y$10$e/7VdXSFYvui1h8JTff8peo9pTI0E3TwIolFMx9KuZlc6QLIHXufS', 'student', NULL, 'active', NULL, NULL, NULL, '2025-06-13 00:39:32', NULL, NULL, '2025-06-12 19:37:32', '0000-00-00 00:00:00'),
(8, 'Mohammed El Ouazzani', 'mohammed1', 'mohammed@edulearn.com', '$2y$10$v4Ir6xsiFP1lj3d8s.VG9eJel.WQY.3EdXrX4uHEwZMqLj7NNOvn2', 'student', NULL, 'active', NULL, NULL, NULL, '2025-06-13 12:05:02', NULL, NULL, '2025-06-13 00:53:41', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Structure de la table `user_activity_logs`
--

CREATE TABLE `user_activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` varchar(50) NOT NULL,
  `entity_type` varchar(50) DEFAULT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `admin_settings`
--
ALTER TABLE `admin_settings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `ai_conversations`
--
ALTER TABLE `ai_conversations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `ai_messages`
--
ALTER TABLE `ai_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `conversation_id` (`conversation_id`);

--
-- Index pour la table `ai_usage`
--
ALTER TABLE `ai_usage`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_date` (`user_id`,`date`);

--
-- Index pour la table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `author_id` (`author_id`),
  ADD KEY `idx_announcement_expiry` (`expiry_date`);

--
-- Index pour la table `announcement_files`
--
ALTER TABLE `announcement_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `announcement_id` (`announcement_id`);

--
-- Index pour la table `announcement_views`
--
ALTER TABLE `announcement_views`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_announcement` (`announcement_id`,`user_id`),
  ADD KEY `announcement_id` (`announcement_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `deleted_students`
--
ALTER TABLE `deleted_students`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_deleted_by_admin` (`deleted_by`);

--
-- Index pour la table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `event_categories`
--
ALTER TABLE `event_categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `notes`
--
ALTER TABLE `notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `note_categories`
--
ALTER TABLE `note_categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `note_modules`
--
ALTER TABLE `note_modules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_student_id` (`student_id`),
  ADD KEY `idx_name` (`name`);

--
-- Index pour la table `note_tags`
--
ALTER TABLE `note_tags`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `note_tag_relations`
--
ALTER TABLE `note_tag_relations`
  ADD PRIMARY KEY (`note_id`,`tag_id`),
  ADD KEY `tag_id` (`tag_id`);

--
-- Index pour la table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `email` (`email`);

--
-- Index pour la table `quizzes`
--
ALTER TABLE `quizzes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `author_id` (`author_id`);

--
-- Index pour la table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quiz_id` (`quiz_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `quiz_attempt_answers`
--
ALTER TABLE `quiz_attempt_answers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `attempt_id` (`attempt_id`),
  ADD KEY `question_id` (`question_id`),
  ADD KEY `selected_option_id` (`selected_option_id`);

--
-- Index pour la table `quiz_questions`
--
ALTER TABLE `quiz_questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quiz_id` (`quiz_id`);

--
-- Index pour la table `quiz_question_options`
--
ALTER TABLE `quiz_question_options`
  ADD PRIMARY KEY (`id`),
  ADD KEY `question_id` (`question_id`);

--
-- Index pour la table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `last_activity` (`last_activity`);

--
-- Index pour la table `student_profiles`
--
ALTER TABLE `student_profiles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `student_settings`
--
ALTER TABLE `student_settings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Index pour la table `user_activity_logs`
--
ALTER TABLE `user_activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `action_index` (`action`),
  ADD KEY `entity_index` (`entity_type`,`entity_id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `admin_settings`
--
ALTER TABLE `admin_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `ai_conversations`
--
ALTER TABLE `ai_conversations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `ai_messages`
--
ALTER TABLE `ai_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `ai_usage`
--
ALTER TABLE `ai_usage`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `announcement_files`
--
ALTER TABLE `announcement_files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `announcement_views`
--
ALTER TABLE `announcement_views`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `event_categories`
--
ALTER TABLE `event_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `notes`
--
ALTER TABLE `notes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `note_categories`
--
ALTER TABLE `note_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `note_modules`
--
ALTER TABLE `note_modules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `note_tags`
--
ALTER TABLE `note_tags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `quizzes`
--
ALTER TABLE `quizzes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `quiz_attempt_answers`
--
ALTER TABLE `quiz_attempt_answers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `quiz_questions`
--
ALTER TABLE `quiz_questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `quiz_question_options`
--
ALTER TABLE `quiz_question_options`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `student_profiles`
--
ALTER TABLE `student_profiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `student_settings`
--
ALTER TABLE `student_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `user_activity_logs`
--
ALTER TABLE `user_activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `admin_settings`
--
ALTER TABLE `admin_settings`
  ADD CONSTRAINT `admin_settings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `ai_conversations`
--
ALTER TABLE `ai_conversations`
  ADD CONSTRAINT `ai_conversations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `ai_messages`
--
ALTER TABLE `ai_messages`
  ADD CONSTRAINT `ai_messages_ibfk_1` FOREIGN KEY (`conversation_id`) REFERENCES `ai_conversations` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `ai_usage`
--
ALTER TABLE `ai_usage`
  ADD CONSTRAINT `ai_usage_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `announcements`
--
ALTER TABLE `announcements`
  ADD CONSTRAINT `announcements_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `announcement_files`
--
ALTER TABLE `announcement_files`
  ADD CONSTRAINT `announcement_files_ibfk_1` FOREIGN KEY (`announcement_id`) REFERENCES `announcements` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `announcement_views`
--
ALTER TABLE `announcement_views`
  ADD CONSTRAINT `announcement_views_ibfk_1` FOREIGN KEY (`announcement_id`) REFERENCES `announcements` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `announcement_views_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `deleted_students`
--
ALTER TABLE `deleted_students`
  ADD CONSTRAINT `fk_deleted_by_admin` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `events_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `event_categories`
--
ALTER TABLE `event_categories`
  ADD CONSTRAINT `event_categories_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `notes`
--
ALTER TABLE `notes`
  ADD CONSTRAINT `notes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `note_categories`
--
ALTER TABLE `note_categories`
  ADD CONSTRAINT `note_categories_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `note_modules`
--
ALTER TABLE `note_modules`
  ADD CONSTRAINT `fk_note_modules_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `note_tags`
--
ALTER TABLE `note_tags`
  ADD CONSTRAINT `note_tags_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `note_tag_relations`
--
ALTER TABLE `note_tag_relations`
  ADD CONSTRAINT `note_tag_relations_ibfk_1` FOREIGN KEY (`note_id`) REFERENCES `notes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `note_tag_relations_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `note_tags` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `quizzes`
--
ALTER TABLE `quizzes`
  ADD CONSTRAINT `quizzes_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  ADD CONSTRAINT `quiz_attempts_ibfk_1` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `quiz_attempts_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `quiz_attempt_answers`
--
ALTER TABLE `quiz_attempt_answers`
  ADD CONSTRAINT `quiz_attempt_answers_ibfk_1` FOREIGN KEY (`attempt_id`) REFERENCES `quiz_attempts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `quiz_attempt_answers_ibfk_2` FOREIGN KEY (`question_id`) REFERENCES `quiz_questions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `quiz_attempt_answers_ibfk_3` FOREIGN KEY (`selected_option_id`) REFERENCES `quiz_question_options` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `quiz_questions`
--
ALTER TABLE `quiz_questions`
  ADD CONSTRAINT `quiz_questions_ibfk_1` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `quiz_question_options`
--
ALTER TABLE `quiz_question_options`
  ADD CONSTRAINT `quiz_question_options_ibfk_1` FOREIGN KEY (`question_id`) REFERENCES `quiz_questions` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `student_profiles`
--
ALTER TABLE `student_profiles`
  ADD CONSTRAINT `student_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `student_settings`
--
ALTER TABLE `student_settings`
  ADD CONSTRAINT `student_settings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `user_activity_logs`
--
ALTER TABLE `user_activity_logs`
  ADD CONSTRAINT `user_activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
