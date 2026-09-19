<?php

class TicketSuggestions
{
    private static bool $ready = false;

    public static function ensure(): void
    {
        if (self::$ready || (defined('DEMO_MODE') && DEMO_MODE)) {
            return;
        }

        Database::query(
            "CREATE TABLE IF NOT EXISTS ticket_suggestions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                type ENUM('company','task') NOT NULL,
                value VARCHAR(255) NOT NULL,
                normalized_value VARCHAR(255) NOT NULL,
                status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
                created_by INT NULL,
                approved_by INT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                approved_at TIMESTAMP NULL DEFAULT NULL,
                deleted_at TIMESTAMP NULL DEFAULT NULL,
                KEY idx_ticket_suggestions_lookup (type, status, deleted_at),
                KEY idx_ticket_suggestions_norm (type, normalized_value, status, deleted_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        self::seedApproved();
        self::$ready = true;
    }

    public static function normalize(string $value): string
    {
        $value = trim(preg_replace('/\s+/', ' ', $value));
        return mb_strtolower($value);
    }

    public static function approved(string $type): array
    {
        self::ensure();
        if (defined('DEMO_MODE') && DEMO_MODE) {
            return [];
        }

        $rows = Database::fetchAll(
            "SELECT value FROM ticket_suggestions
             WHERE type = ? AND status = 'approved' AND deleted_at IS NULL
             ORDER BY value",
            [$type]
        );
        return array_map(fn($r) => $r['value'], $rows ?: []);
    }

    public static function pending(): array
    {
        self::ensure();
        if (defined('DEMO_MODE') && DEMO_MODE) {
            return [];
        }

        return Database::fetchAll(
            "SELECT ts.*, u.full_name AS created_by_name
             FROM ticket_suggestions ts
             LEFT JOIN users u ON ts.created_by = u.id
             WHERE ts.status = 'pending' AND ts.deleted_at IS NULL
             ORDER BY ts.type, ts.normalized_value, ts.created_at DESC"
        ) ?: [];
    }

    public static function submit(string $type, string $value, ?int $userId): array
    {
        self::ensure();
        $value = trim(preg_replace('/\s+/', ' ', $value));
        if ($value === '' || (defined('DEMO_MODE') && DEMO_MODE)) {
            return ['status' => 'ignored'];
        }

        $norm = self::normalize($value);
        $approved = Database::fetch(
            "SELECT id, value FROM ticket_suggestions
             WHERE type = ? AND normalized_value = ? AND status = 'approved' AND deleted_at IS NULL
             LIMIT 1",
            [$type, $norm]
        );
        if ($approved) {
            return ['status' => 'already_approved', 'value' => $approved['value']];
        }

        $pending = Database::fetch(
            "SELECT id FROM ticket_suggestions
             WHERE type = ? AND normalized_value = ? AND status = 'pending' AND deleted_at IS NULL
             LIMIT 1",
            [$type, $norm]
        );
        if ($pending) {
            return ['status' => 'already_pending'];
        }

        Database::insert('ticket_suggestions', [
            'type' => $type,
            'value' => $value,
            'normalized_value' => $norm,
            'status' => 'pending',
            'created_by' => $userId,
        ]);
        return ['status' => 'pending'];
    }

    public static function approve(array $ids, ?int $adminId): int
    {
        self::ensure();
        $count = 0;
        foreach ($ids as $id) {
            $row = Database::fetch(
                "SELECT * FROM ticket_suggestions WHERE id = ? AND status = 'pending' AND deleted_at IS NULL",
                [(int)$id]
            );
            if (!$row) {
                continue;
            }

            $existing = Database::fetch(
                "SELECT id FROM ticket_suggestions
                 WHERE type = ? AND normalized_value = ? AND status = 'approved' AND deleted_at IS NULL
                 LIMIT 1",
                [$row['type'], $row['normalized_value']]
            );
            if ($existing) {
                Database::query("UPDATE ticket_suggestions SET deleted_at = NOW() WHERE id = ?", [(int)$id]);
                self::deletePendingDuplicates($row['type'], $row['normalized_value']);
                continue;
            }

            Database::query(
                "UPDATE ticket_suggestions
                 SET status = 'approved', approved_by = ?, approved_at = NOW()
                 WHERE id = ?",
                [$adminId, (int)$id]
            );
            self::deletePendingDuplicates($row['type'], $row['normalized_value'], (int)$id);
            $count++;
        }
        return $count;
    }

    public static function delete(array $ids): int
    {
        self::ensure();
        if (!$ids) {
            return 0;
        }
        $count = 0;
        foreach ($ids as $id) {
            $stmt = Database::query(
                "UPDATE ticket_suggestions SET deleted_at = NOW() WHERE id = ? AND status = 'pending'",
                [(int)$id]
            );
            $count += $stmt->rowCount();
        }
        return $count;
    }

    private static function seedApproved(): void
    {
        $orgs = Database::fetchAll("SELECT name FROM organizations WHERE name IS NOT NULL AND name <> ''") ?: [];
        foreach ($orgs as $org) {
            self::insertApprovedIfMissing('company', $org['name']);
        }

        $tasks = Database::fetchAll(
            "SELECT DISTINCT task FROM troubleshooting_sessions WHERE task IS NOT NULL AND task <> '' LIMIT 500"
        ) ?: [];
        foreach ($tasks as $task) {
            self::insertApprovedIfMissing('task', $task['task']);
        }
    }

    private static function insertApprovedIfMissing(string $type, string $value): void
    {
        $value = trim(preg_replace('/\s+/', ' ', $value));
        if ($value === '') {
            return;
        }
        $norm = self::normalize($value);
        $exists = Database::fetch(
            "SELECT id FROM ticket_suggestions
             WHERE type = ? AND normalized_value = ? AND status = 'approved' AND deleted_at IS NULL
             LIMIT 1",
            [$type, $norm]
        );
        if (!$exists) {
            Database::insert('ticket_suggestions', [
                'type' => $type,
                'value' => $value,
                'normalized_value' => $norm,
                'status' => 'approved',
            ]);
        }
    }

    private static function deletePendingDuplicates(string $type, string $norm, int $exceptId = 0): void
    {
        $sql = "UPDATE ticket_suggestions
                SET deleted_at = NOW()
                WHERE type = ? AND normalized_value = ? AND status = 'pending' AND deleted_at IS NULL";
        $params = [$type, $norm];
        if ($exceptId > 0) {
            $sql .= " AND id <> ?";
            $params[] = $exceptId;
        }
        Database::query($sql, $params);
    }
}
