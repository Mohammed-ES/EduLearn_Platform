# EduLearn Platform Database Schema

## Password Recovery System

### `password_resets` Table
| Field | Type | Description |
|-------|------|-------------|
| `id` | int(11) AUTO_INCREMENT | Primary key |
| `user_id` | int(11) | Foreign key to users table |
| `token` | varchar(255) | Random token for password reset |
| `expires_at` | datetime | When the token expires (typically 1 hour) |
| `created_at` | datetime | When the reset request was created |
| `used` | tinyint(1) | Whether the token has been used |
| `used_at` | datetime | When the token was used |

## Core User Management

### `users` Table
| Field | Type | Description |
|-------|------|-------------|
| `id` | int(11) AUTO_INCREMENT | Primary key |
| `fullname` | varchar(255) | User's full name |
| `username` | varchar(100) UNIQUE | Unique username |
| `email` | varchar(255) UNIQUE | Email address |
| `password` | varchar(255) | Hashed password |
| `user_role` | enum('admin','student') | User role |
| `status` | enum('active','inactive') | Account status |
| `phone` | varchar(20) | Phone number |
| `profile_image` | varchar(255) | Profile image path |
| `bio` | text | User biography |
| `last_login` | datetime | Last login timestamp |
| `password_changed_at` | datetime | Password change timestamp |
| `deleted_at` | datetime | Soft delete timestamp |
| `created_at` | datetime | Creation timestamp |
| `updated_at` | datetime | Update timestamp |

### `student_profiles` Table
| Field | Type | Description |
|-------|------|-------------|
| `id` | int(11) AUTO_INCREMENT | Primary key |
| `user_id` | int(11) | Foreign key to users table |
| `student_id` | varchar(50) | Student ID number |
| `date_of_birth` | date | Date of birth |
| `academic_level` | varchar(50) | Academic level |
| `enrollment_date` | date | Date of enrollment |
| `department` | varchar(100) | Department name |
| `major` | varchar(100) | Major subject |
| `created_at` | datetime | Creation timestamp |
| `updated_at` | datetime | Update timestamp |

### Settings Tables

#### `admin_settings` Table
| Field | Type | Description |
|-------|------|-------------|
| `id` | int(11) AUTO_INCREMENT | Primary key |
| `user_id` | int(11) | Foreign key to users table |
| `email_notifications` | tinyint(1) | Email notification preference |
| `system_notifications` | tinyint(1) | System notification preference |
| `created_at` | datetime | Creation timestamp |
| `updated_at` | datetime | Update timestamp |

#### `student_settings` Table
| Field | Type | Description |
|-------|------|-------------|
| `id` | int(11) AUTO_INCREMENT | Primary key |
| `user_id` | int(11) | Foreign key to users table |
| `email_notifications` | tinyint(1) | Email notification preference |
| `system_notifications` | tinyint(1) | System notification preference |
| `reminder_notifications` | tinyint(1) | Reminder notification preference |
| `theme_preference` | varchar(20) | UI theme preference |
| `created_at` | datetime | Creation timestamp |
| `updated_at` | datetime | Update timestamp |

### `sessions` Table
| Field | Type | Description |
|-------|------|-------------|
| `id` | varchar(128) | Session ID (primary key) |
| `user_id` | int(11) | Foreign key to users table |
| `ip_address` | varchar(45) | IP address |
| `user_agent` | varchar(255) | Browser user agent |
| `payload` | text | Session data |
| `last_activity` | int(11) | Last activity timestamp |

## Announcement System

### `announcements` Table
| Field | Type | Description |
|-------|------|-------------|
| `id` | int(11) AUTO_INCREMENT | Primary key |
| `title` | varchar(255) | Announcement title |
| `content` | text | Announcement content |
| `status` | enum('draft','published') | Publication status |
| `author_id` | int(11) | Foreign key to users table |
| `importance` | enum('low','medium','high') | Importance level |
| `published_at` | datetime | Publication timestamp |
| `deleted_at` | datetime | Soft delete timestamp |
| `created_at` | datetime | Creation timestamp |
| `updated_at` | datetime | Update timestamp |

### `announcement_views` Table
| Field | Type | Description |
|-------|------|-------------|
| `id` | int(11) AUTO_INCREMENT | Primary key |
| `announcement_id` | int(11) | Foreign key to announcements table |
| `user_id` | int(11) | Foreign key to users table |
| `viewed_at` | datetime | View timestamp |

### `announcement_files` Table
| Field | Type | Description |
|-------|------|-------------|
| `id` | int(11) AUTO_INCREMENT | Primary key |
| `announcement_id` | int(11) | Foreign key to announcements table |
| `file_path` | varchar(255) | Path to file |
| `file_name` | varchar(255) | Original file name |
| `file_type` | varchar(100) | File MIME type |
| `file_size` | int(11) | File size in bytes |
| `created_at` | datetime | Creation timestamp |

## Notes System

### `notes` Table
| Field | Type | Description |
|-------|------|-------------|
| `id` | int(11) AUTO_INCREMENT | Primary key |
| `user_id` | int(11) | Foreign key to users table |
| `title` | varchar(255) | Note title |
| `content` | text | Note content |
| `category` | varchar(50) | Note category |
| `color` | varchar(20) | Note color (hex) |
| `pinned` | tinyint(1) | Whether note is pinned |
| `deleted_at` | datetime | Soft delete timestamp |
| `created_at` | datetime | Creation timestamp |
| `updated_at` | datetime | Update timestamp |

### `note_categories` Table
| Field | Type | Description |
|-------|------|-------------|
| `id` | int(11) AUTO_INCREMENT | Primary key |
| `user_id` | int(11) | Foreign key to users table |
| `name` | varchar(50) | Category name |
| `color` | varchar(20) | Category color (hex) |
| `created_at` | datetime | Creation timestamp |
| `updated_at` | datetime | Update timestamp |

### `note_tags` Table
| Field | Type | Description |
|-------|------|-------------|
| `id` | int(11) AUTO_INCREMENT | Primary key |
| `name` | varchar(50) | Tag name |
| `user_id` | int(11) | Foreign key to users table |
| `created_at` | datetime | Creation timestamp |

### `note_tag_relations` Table
| Field | Type | Description |
|-------|------|-------------|
| `note_id` | int(11) | Foreign key to notes table |
| `tag_id` | int(11) | Foreign key to note_tags table |

## Planning System

### `events` Table
| Field | Type | Description |
|-------|------|-------------|
| `id` | int(11) AUTO_INCREMENT | Primary key |
| `user_id` | int(11) | Foreign key to users table |
| `title` | varchar(255) | Event title |
| `description` | text | Event description |
| `start_time` | datetime | Event start time |
| `end_time` | datetime | Event end time |
| `location` | varchar(255) | Event location |
| `color` | varchar(20) | Event color (hex) |
| `all_day` | tinyint(1) | Whether event lasts all day |
| `repeat_type` | enum('none','daily','weekly','monthly','yearly') | Recurrence type |
| `reminder` | int(11) | Minutes before event for reminder |
| `deleted_at` | datetime | Soft delete timestamp |
| `created_at` | datetime | Creation timestamp |
| `updated_at` | datetime | Update timestamp |

### `event_categories` Table
| Field | Type | Description |
|-------|------|-------------|
| `id` | int(11) AUTO_INCREMENT | Primary key |
| `user_id` | int(11) | Foreign key to users table |
| `name` | varchar(50) | Category name |
| `color` | varchar(20) | Category color (hex) |
| `created_at` | datetime | Creation timestamp |
| `updated_at` | datetime | Update timestamp |

## Quiz System

### `quizzes` Table
| Field | Type | Description |
|-------|------|-------------|
| `id` | int(11) AUTO_INCREMENT | Primary key |
| `title` | varchar(255) | Quiz title |
| `description` | text | Quiz description |
| `author_id` | int(11) | Foreign key to users table |
| `time_limit` | int(11) | Time limit in minutes |
| `pass_score` | int(11) | Passing score percentage |
| `status` | enum('draft','published','archived') | Publication status |
| `allow_retake` | tinyint(1) | Whether retakes are allowed |
| `max_attempts` | int(11) | Maximum number of attempts |
| `show_answers` | tinyint(1) | Whether to show answers |
| `randomize_questions` | tinyint(1) | Whether to randomize questions |
| `published_at` | datetime | Publication timestamp |
| `deleted_at` | datetime | Soft delete timestamp |
| `created_at` | datetime | Creation timestamp |
| `updated_at` | datetime | Update timestamp |

### `quiz_questions` Table
| Field | Type | Description |
|-------|------|-------------|
| `id` | int(11) AUTO_INCREMENT | Primary key |
| `quiz_id` | int(11) | Foreign key to quizzes table |
| `question` | text | Question text |
| `question_type` | enum('multiple_choice','true_false','short_answer','matching') | Question type |
| `points` | int(11) | Question point value |
| `explanation` | text | Explanation of answer |
| `position` | int(11) | Question position in quiz |
| `created_at` | datetime | Creation timestamp |
| `updated_at` | datetime | Update timestamp |

### `quiz_question_options` Table
| Field | Type | Description |
|-------|------|-------------|
| `id` | int(11) AUTO_INCREMENT | Primary key |
| `question_id` | int(11) | Foreign key to quiz_questions table |
| `option_text` | text | Option text |
| `is_correct` | tinyint(1) | Whether option is correct |
| `position` | int(11) | Option position |

### `quiz_attempts` Table
| Field | Type | Description |
|-------|------|-------------|
| `id` | int(11) AUTO_INCREMENT | Primary key |
| `quiz_id` | int(11) | Foreign key to quizzes table |
| `user_id` | int(11) | Foreign key to users table |
| `score` | int(11) | Score achieved |
| `time_spent` | int(11) | Time spent in seconds |
| `completed` | tinyint(1) | Whether attempt was completed |
| `started_at` | datetime | Start timestamp |
| `completed_at` | datetime | Completion timestamp |

### `quiz_attempt_answers` Table
| Field | Type | Description |
|-------|------|-------------|
| `id` | int(11) AUTO_INCREMENT | Primary key |
| `attempt_id` | int(11) | Foreign key to quiz_attempts table |
| `question_id` | int(11) | Foreign key to quiz_questions table |
| `answer_text` | text | Answer text for short answers |
| `selected_option_id` | int(11) | Foreign key to quiz_question_options table |
| `is_correct` | tinyint(1) | Whether answer is correct |
| `points_earned` | int(11) | Points earned for answer |
| `created_at` | datetime | Creation timestamp |

## AI Assistant System

### `ai_conversations` Table
| Field | Type | Description |
|-------|------|-------------|
| `id` | int(11) AUTO_INCREMENT | Primary key |
| `user_id` | int(11) | Foreign key to users table |
| `title` | varchar(255) | Conversation title |
| `created_at` | datetime | Creation timestamp |
| `updated_at` | datetime | Update timestamp |

### `ai_messages` Table
| Field | Type | Description |
|-------|------|-------------|
| `id` | int(11) AUTO_INCREMENT | Primary key |
| `conversation_id` | int(11) | Foreign key to ai_conversations table |
| `role` | enum('user','assistant') | Message sender role |
| `content` | text | Message content |
| `created_at` | datetime | Creation timestamp |

### `ai_usage` Table
| Field | Type | Description |
|-------|------|-------------|
| `id` | int(11) AUTO_INCREMENT | Primary key |
| `user_id` | int(11) | Foreign key to users table |
| `tokens_used` | int(11) | Number of tokens used |
| `date` | date | Usage date |

## Analytics & Reporting

### `user_activity_logs` Table
| Field | Type | Description |
|-------|------|-------------|
| `id` | int(11) AUTO_INCREMENT | Primary key |
| `user_id` | int(11) | Foreign key to users table |
| `action` | varchar(50) | Action performed |
| `entity_type` | varchar(50) | Type of entity acted upon |
| `entity_id` | int(11) | ID of entity acted upon |
| `description` | varchar(255) | Description of activity |
| `ip_address` | varchar(45) | IP address |
| `created_at` | datetime | Creation timestamp |

### `system_settings` Table
| Field | Type | Description |
|-------|------|-------------|
| `id` | int(11) AUTO_INCREMENT | Primary key |
| `setting_key` | varchar(100) UNIQUE | Setting key name |
| `setting_value` | text | Setting value |
| `setting_type` | varchar(20) | Value type (text, number, boolean) |
| `created_at` | datetime | Creation timestamp |
| `updated_at` | datetime | Update timestamp |

### `password_resets` Table
| Field | Type | Description |
|-------|------|-------------|
| `id` | int(11) AUTO_INCREMENT | Primary key |
| `email` | varchar(255) | User email |
| `token` | varchar(255) | Reset token |
| `created_at` | datetime | Creation timestamp |
| `expires_at` | datetime | Expiration timestamp |

## Default Data

### Default Admin User
- Full Name: Admin User
- Username: admin
- Email: admin@edulearn.com
- Password: admin123 (hashed)
- Role: admin
- Status: active

### Default System Settings
- site_name: EduLearn Platform
- site_description: Modern Educational Experience
- admin_email: admin@edulearn.com
- max_upload_size: 5
- default_language: en
- maintenance_mode: 0
- allow_registration: 1
- require_email_verification: 0
- announcement_per_page: 10
- notes_per_page: 20