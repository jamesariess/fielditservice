-- Unify the AI subsystem with fieldit_hub.
-- Run this against the same single database used by config/app.php.

CREATE TABLE IF NOT EXISTS `ai_conversation_logs` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `session_id` varchar(150) NOT NULL,
  `user_id` int NOT NULL,
  `message` text NOT NULL,
  `response` longtext NOT NULL,
  `sources_used` varchar(1000) DEFAULT NULL,
  `confidence` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_ai_logs_session` (`session_id`),
  KEY `idx_ai_logs_user` (`user_id`),
  KEY `idx_ai_logs_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `ai_personality` (
  `id` int NOT NULL AUTO_INCREMENT,
  `bot_name` varchar(100) NOT NULL DEFAULT 'IT Bot',
  `greeting` text DEFAULT NULL,
  `personality` varchar(50) DEFAULT 'professional',
  `system_prompt` longtext DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_ai_personality_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `ai_training_files` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `file_type` varchar(30) DEFAULT 'text',
  `content` longtext NOT NULL,
  `category` varchar(100) DEFAULT 'general',
  `tags` varchar(1000) DEFAULT NULL,
  `uploaded_by` int DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_ai_training_active` (`is_active`),
  KEY `idx_ai_training_category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `ai_conversation_ratings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `session_id` varchar(150) NOT NULL,
  `user_id` int NOT NULL,
  `rating` tinyint NOT NULL,
  `comment` text DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_ai_rating_session` (`session_id`),
  KEY `idx_ai_rating_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `ai_response_feedback` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `legacy_message_id` bigint DEFAULT NULL,
  `session_id` varchar(150) DEFAULT NULL,
  `user_id` int NOT NULL,
  `rating` varchar(20) NOT NULL,
  `solved` varchar(20) DEFAULT NULL,
  `feedback` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_ai_response_feedback_user` (`user_id`),
  KEY `idx_ai_response_feedback_session` (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
