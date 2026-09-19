-- ============================================================
-- New Ticket form: extra capture fields
-- Adds company name, task, equipment link and a free-form note to
-- troubleshooting_sessions. Run once against the fieldit_hub database.
-- ============================================================

ALTER TABLE troubleshooting_sessions
  ADD COLUMN company_name VARCHAR(150) NULL AFTER customer_name,
  ADD COLUMN task         VARCHAR(255) NULL AFTER problem_description,
  ADD COLUMN equipment_id INT(11)      NULL AFTER issue_id,
  ADD COLUMN notes        TEXT         NULL AFTER serial_number;
