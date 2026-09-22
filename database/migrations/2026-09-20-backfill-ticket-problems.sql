UPDATE `troubleshooting_sessions` SET `issue_id` = 24 WHERE `issue_id` IS NULL AND `problem_description` LIKE 'Battery life at 64%';
UPDATE `troubleshooting_sessions` SET `issue_id` = 13 WHERE `issue_id` IS NULL AND `problem_description` LIKE 'Printer offline%';
UPDATE `troubleshooting_sessions` SET `issue_id` = 6 WHERE `issue_id` IS NULL AND (`problem_description` LIKE '%microphone%' OR `ticket_number` = 'SD6326262');
UPDATE `troubleshooting_sessions` SET `issue_id` = 8 WHERE `issue_id` IS NULL AND `problem_description` LIKE '%WiFi%';
UPDATE `troubleshooting_sessions` SET `issue_id` = 1 WHERE `issue_id` IS NULL AND `problem_description` LIKE '%black screen%';
