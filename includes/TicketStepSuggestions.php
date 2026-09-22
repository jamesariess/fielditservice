<?php

class TicketStepSuggestions
{
    private static bool $ready = false;

    public static function ensure(): void
    {
        if (self::$ready || (defined('DEMO_MODE') && DEMO_MODE)) { return; }
        Database::query(
            "CREATE TABLE IF NOT EXISTS ticket_step_suggestions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                session_id INT NOT NULL,
                issue_id INT NOT NULL,
                title VARCHAR(200) NOT NULL,
                normalized_title VARCHAR(200) NOT NULL,
                status ENUM('pending','approved','duplicate','rejected') NOT NULL DEFAULT 'pending',
                reviewed_by INT NULL,
                reviewed_at TIMESTAMP NULL DEFAULT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uq_ticket_step_session (session_id, normalized_title),
                KEY idx_ticket_step_queue (status, issue_id),
                KEY idx_ticket_step_session (session_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
        self::$ready = true;
    }

    public static function normalize(string $value): string
    {
        return mb_strtolower(trim(preg_replace('/\s+/', ' ', $value)));
    }

    public static function sync(): void
    {
        self::ensure();
        Database::query(
            "UPDATE ticket_step_suggestions q
             JOIN troubleshooting_sessions s ON s.id = q.session_id
             JOIN troubleshooting_issues i ON i.id = s.issue_id
             SET q.issue_id = s.issue_id, q.status = 'pending'
             WHERE q.status IN ('pending','duplicate') AND q.issue_id <> s.issue_id"
        );
        $sessions = Database::fetchAll(
            "SELECT id, issue_id, steps_performed FROM troubleshooting_sessions
             WHERE ended_at IS NOT NULL AND COALESCE(steps_approved, 0) = 0
               AND steps_performed IS NOT NULL AND JSON_LENGTH(steps_performed) > 0"
        ) ?: [];
        foreach ($sessions as $session) {
            $steps = json_decode((string)$session['steps_performed'], true);
            if (!is_array($steps)) { continue; }
            foreach ($steps as $title) {
                $title = trim(preg_replace('/\s+/', ' ', (string)$title));
                if ($title === '') { continue; }
                $norm = self::normalize($title);
                $exists = Database::fetch(
                    "SELECT id FROM troubleshooting_steps WHERE issue_id = ? AND LOWER(TRIM(title)) = ? LIMIT 1",
                    [(int)$session['issue_id'], $norm]
                );
                try {
                    Database::insert('ticket_step_suggestions', [
                        'session_id' => (int)$session['id'],
                        'issue_id' => (int)$session['issue_id'],
                        'title' => $title,
                        'normalized_title' => $norm,
                        'status' => $exists ? 'duplicate' : 'pending',
                    ]);
                } catch (PDOException $e) {
                    if ((string)$e->getCode() !== '23000') { throw $e; }
                }
            }
        }
        Database::query(
            "UPDATE ticket_step_suggestions s
             JOIN troubleshooting_steps t ON t.issue_id = s.issue_id AND LOWER(TRIM(t.title)) = s.normalized_title
             SET s.status = 'duplicate'
             WHERE s.status = 'pending'"
        );
    }

    public static function queue(): array
    {
        self::sync();
        return Database::fetchAll(
            "SELECT q.*, s.ticket_number, s.company_name, i.title AS issue_title,
                    u.full_name AS created_by_name,
                    (SELECT t.title FROM troubleshooting_steps t
                     WHERE t.issue_id = q.issue_id AND LOWER(TRIM(t.title)) = q.normalized_title LIMIT 1) AS existing_title
             FROM ticket_step_suggestions q
             JOIN troubleshooting_sessions s ON s.id = q.session_id
             LEFT JOIN troubleshooting_issues i ON i.id = q.issue_id
             LEFT JOIN users u ON u.id = s.user_id
             WHERE q.status IN ('pending','duplicate') AND COALESCE(s.steps_approved, 0) = 0
             ORDER BY q.issue_id, q.normalized_title, q.created_at DESC"
        ) ?: [];
    }

    public static function review(int $id, string $action, ?int $adminId, string $adminName, int $selectedIssueId = 0): array
    {
        self::sync();
        $row = Database::fetch("SELECT * FROM ticket_step_suggestions WHERE id = ? AND status IN ('pending','duplicate')", [$id]);
        if (!$row) { return ['updated' => 0, 'status' => 'missing']; }

        if ($action === 'reject') {
            Database::query(
                "UPDATE ticket_step_suggestions SET status = 'rejected', reviewed_by = ?, reviewed_at = NOW() WHERE id = ?",
                [$adminId, $id]
            );
            self::finishSessionIfReviewed((int)$row['session_id'], $adminName);
            return ['updated' => 1, 'status' => 'rejected'];
        }

        $issueExists = Database::fetch("SELECT id FROM troubleshooting_issues WHERE id = ? LIMIT 1", [(int)$row['issue_id']]);
        if (!$issueExists && $selectedIssueId > 0) {
            $selected = Database::fetch("SELECT id FROM troubleshooting_issues WHERE id = ? LIMIT 1", [$selectedIssueId]);
            if (!$selected) { return ['updated' => 0, 'status' => 'problem_required']; }
            Database::query("UPDATE troubleshooting_sessions SET issue_id = ? WHERE id = ?", [$selectedIssueId, (int)$row['session_id']]);
            Database::query(
                "UPDATE ticket_step_suggestions SET issue_id = ?, status = 'pending'
                 WHERE session_id = ? AND status IN ('pending','duplicate')",
                [$selectedIssueId, (int)$row['session_id']]
            );
            Database::query(
                "UPDATE ticket_step_suggestions s
                 JOIN troubleshooting_steps t ON t.issue_id = s.issue_id AND LOWER(TRIM(t.title)) = s.normalized_title
                 SET s.status = 'duplicate'
                 WHERE s.session_id = ? AND s.status = 'pending'",
                [(int)$row['session_id']]
            );
            $row = Database::fetch("SELECT * FROM ticket_step_suggestions WHERE id = ?", [$id]);
            $issueExists = ['id' => $selectedIssueId];
        }
        if (!$issueExists) { return ['updated' => 0, 'status' => 'problem_required']; }

        if ($action === 'approve') {
            $existing = Database::fetch(
                "SELECT id FROM troubleshooting_steps WHERE issue_id = ? AND LOWER(TRIM(title)) = ? LIMIT 1",
                [(int)$row['issue_id'], $row['normalized_title']]
            );
            if ($existing) {
                Database::query("UPDATE ticket_step_suggestions SET status = 'duplicate' WHERE id = ?", [$id]);
                return ['updated' => 0, 'status' => 'duplicate'];
            }
            $max = Database::fetch("SELECT COALESCE(MAX(step_number), 0) AS n FROM troubleshooting_steps WHERE issue_id = ?", [(int)$row['issue_id']]);
            Database::insert('troubleshooting_steps', [
                'issue_id' => (int)$row['issue_id'], 'step_number' => (int)($max['n'] ?? 0) + 1,
                'title' => $row['title'], 'instruction' => $row['title'], 'risk_level' => 'safe', 'is_final' => 0,
            ]);
            $status = 'approved';
        } else {
            return ['updated' => 0, 'status' => 'invalid'];
        }

        Database::query(
            "UPDATE ticket_step_suggestions SET status = ?, reviewed_by = ?, reviewed_at = NOW() WHERE id = ?",
            [$status, $adminId, $id]
        );
        self::finishSessionIfReviewed((int)$row['session_id'], $adminName);
        return ['updated' => 1, 'status' => $status];
    }

    private static function finishSessionIfReviewed(int $sessionId, string $adminName): void
    {
        $left = Database::fetch(
            "SELECT COUNT(*) AS n FROM ticket_step_suggestions WHERE session_id = ? AND status IN ('pending','duplicate')",
            [$sessionId]
        );
        if ((int)($left['n'] ?? 0) === 0) {
            Database::query(
                "UPDATE troubleshooting_sessions SET steps_approved = 1, steps_approved_by = ? WHERE id = ?",
                [$adminName ?: 'Admin', $sessionId]
            );
        }
    }
}
