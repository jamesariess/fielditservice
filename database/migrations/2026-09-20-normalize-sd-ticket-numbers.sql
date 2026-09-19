UPDATE `troubleshooting_sessions`
SET `ticket_number` = CONCAT('SD', `ticket_number`)
WHERE `ticket_number` REGEXP '^[0-9]+$';

UPDATE `troubleshooting_sessions`
SET `ticket_number` = CONCAT('SD', SUBSTRING(`ticket_number`, 4))
WHERE `ticket_number` REGEXP '^TK-[0-9]+$';
