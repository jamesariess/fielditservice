ALTER TABLE locations
  ADD COLUMN IF NOT EXISTS latitude DECIMAL(10,7) NULL AFTER address,
  ADD COLUMN IF NOT EXISTS longitude DECIMAL(10,7) NULL AFTER latitude;

CREATE TABLE IF NOT EXISTS ticket_field_memory (
  id INT AUTO_INCREMENT PRIMARY KEY,
  memory_type ENUM('result','recommendation','confirmed_by') NOT NULL,
  issue_id INT NULL,
  company_key VARCHAR(255) NULL,
  value VARCHAR(500) NOT NULL,
  normalized_value VARCHAR(500) NOT NULL,
  created_by INT NULL,
  use_count INT NOT NULL DEFAULT 1,
  last_used_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_ticket_field_memory (memory_type, issue_id, company_key, normalized_value),
  KEY idx_ticket_field_issue (memory_type, issue_id),
  KEY idx_ticket_field_company (memory_type, company_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
