-- Approval state for reusable ticket report values.
-- Existing values have no recorded approver, so they enter the pending queue.
ALTER TABLE `ticket_field_memory`
  ADD COLUMN IF NOT EXISTS `status` enum('pending','approved') NOT NULL DEFAULT 'approved' AFTER `normalized_value`,
  ADD COLUMN IF NOT EXISTS `approved_by` int(11) DEFAULT NULL AFTER `created_by`,
  ADD COLUMN IF NOT EXISTS `approved_at` timestamp NULL DEFAULT NULL AFTER `last_used_at`,
  ADD COLUMN IF NOT EXISTS `deleted_at` timestamp NULL DEFAULT NULL AFTER `approved_at`;

UPDATE `ticket_field_memory`
SET `status` = 'pending'
WHERE `status` = 'approved'
  AND `approved_by` IS NULL
  AND `approved_at` IS NULL
  AND `deleted_at` IS NULL;
