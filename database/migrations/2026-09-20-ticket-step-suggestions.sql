CREATE TABLE IF NOT EXISTS `ticket_step_suggestions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `session_id` int(11) NOT NULL,
  `issue_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `normalized_title` varchar(200) NOT NULL,
  `status` enum('pending','approved','duplicate','rejected') NOT NULL DEFAULT 'pending',
  `reviewed_by` int(11) DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_ticket_step_session` (`session_id`,`normalized_title`),
  KEY `idx_ticket_step_queue` (`status`,`issue_id`),
  KEY `idx_ticket_step_session` (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
