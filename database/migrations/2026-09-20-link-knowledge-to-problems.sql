ALTER TABLE `knowledge_articles`
  ADD COLUMN IF NOT EXISTS `troubleshooting_issue_id` int(11) DEFAULT NULL AFTER `id`,
  ADD KEY IF NOT EXISTS `idx_knowledge_troubleshooting_issue` (`troubleshooting_issue_id`);

UPDATE `knowledge_articles` SET `troubleshooting_issue_id` = 1 WHERE `title` = 'No Display Troubleshooting';
UPDATE `knowledge_articles` SET `troubleshooting_issue_id` = 3 WHERE `title` = 'Power Issues Guide';
UPDATE `knowledge_articles` SET `troubleshooting_issue_id` = 8 WHERE `title` = 'WiFi Troubleshooting';
UPDATE `knowledge_articles` SET `troubleshooting_issue_id` = 11 WHERE `title` = 'Printer Fix Guide';
UPDATE `knowledge_articles` SET `troubleshooting_issue_id` = 16 WHERE `title` = 'BSOD Fix Guide';
UPDATE `knowledge_articles` SET `troubleshooting_issue_id` = 17 WHERE `title` = 'Slow PC Fix';
UPDATE `knowledge_articles` SET `troubleshooting_issue_id` = 14 WHERE `title` = 'CCTV Recording Issues';
UPDATE `knowledge_articles` SET `troubleshooting_issue_id` = 5 WHERE `title` = 'No Sound Fix';
