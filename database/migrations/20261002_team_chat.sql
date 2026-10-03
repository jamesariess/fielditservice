-- Import this file in your domain's phpMyAdmin. It preserves existing messages.
SET @chat_presence_sql = IF(
 (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='users' AND COLUMN_NAME='last_seen_at')=0,
 'ALTER TABLE users ADD COLUMN last_seen_at DATETIME NULL DEFAULT NULL',
 'SELECT 1');
PREPARE chat_presence_stmt FROM @chat_presence_sql;
EXECUTE chat_presence_stmt;
DEALLOCATE PREPARE chat_presence_stmt;
CREATE TABLE IF NOT EXISTS chat_department_requests (
 id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
 requester_id INT NOT NULL,
 target_user_id INT NOT NULL,
 reason TEXT NOT NULL,
 status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
 reviewed_by INT NULL,
 reviewed_at DATETIME NULL,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 UNIQUE KEY request_pair (requester_id,target_user_id),
 KEY request_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
