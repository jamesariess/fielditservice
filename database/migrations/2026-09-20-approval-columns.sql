-- Troubleshooting checklist approval tracking
-- Adds columns to track who/what state the checklist approval is in.
-- Run once against the fieldit_hub database.
-- ============================================================

ALTER TABLE `troubleshooting_sessions`
  ADD COLUMN `steps_approved` tinyint(1) DEFAULT 0,
  ADD COLUMN `steps_approved_by` varchar(150) DEFAULT NULL;

-- Optional: index for quick lookup of pending-approval tickets
CREATE INDEX IF NOT EXISTS idx_sess_steps_approved ON `troubleshooting_sessions` (`steps_approved`);