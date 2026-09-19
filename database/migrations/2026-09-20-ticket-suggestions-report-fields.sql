-- Shared Company / Task suggestions with approval, plus saved report fields.
-- New "Other" company/task values are saved as pending suggestions. Managers/admins
-- approve one normalized spelling, and duplicate pending suggestions can be deleted.

CREATE TABLE IF NOT EXISTS ticket_suggestions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  type ENUM('company','task') NOT NULL,
  value VARCHAR(255) NOT NULL,
  normalized_value VARCHAR(255) NOT NULL,
  status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  created_by INT NULL,
  approved_by INT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  approved_at TIMESTAMP NULL DEFAULT NULL,
  deleted_at TIMESTAMP NULL DEFAULT NULL,
  KEY idx_ticket_suggestions_lookup (type, status, deleted_at),
  KEY idx_ticket_suggestions_norm (type, normalized_value, status, deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE troubleshooting_sessions
  ADD COLUMN IF NOT EXISTS result_of_checking TEXT NULL AFTER resolution,
  ADD COLUMN IF NOT EXISTS recommendation TEXT NULL AFTER result_of_checking,
  ADD COLUMN IF NOT EXISTS confirmed_by VARCHAR(150) NULL AFTER recommendation;
