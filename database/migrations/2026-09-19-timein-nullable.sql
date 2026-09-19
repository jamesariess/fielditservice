-- Field IT Hub — make Time In nullable.
-- A newly created ticket must NOT have a Time In: the timestamp is only
-- written when the technician clicks "Start Time In" (action.php, action=timein).
-- Time In ("00" on screen) = started_at, Time Out ("00" on screen) = ended_at,
-- ticket creation date = created_at (already stamped by the DB default).
ALTER TABLE `troubleshooting_sessions`
  MODIFY `started_at` TIMESTAMP NULL DEFAULT NULL;