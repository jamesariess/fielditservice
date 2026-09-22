<?php

class TicketFieldMemory
{
    private static bool $ready = false;

    public static function ensure(): void
    {
        if (self::$ready || (defined('DEMO_MODE') && DEMO_MODE)) { return; }

        $locationColumns = [];
        foreach (Database::fetchAll("SHOW COLUMNS FROM locations") as $column) {
            $locationColumns[$column['Field']] = true;
        }
        $adds = [];
        if (!isset($locationColumns['latitude'])) { $adds[] = "ADD COLUMN latitude DECIMAL(10,7) NULL AFTER address"; }
        if (!isset($locationColumns['longitude'])) { $adds[] = "ADD COLUMN longitude DECIMAL(10,7) NULL AFTER latitude"; }
        if ($adds) { Database::query("ALTER TABLE locations " . implode(', ', $adds)); }

        Database::query(
            "CREATE TABLE IF NOT EXISTS ticket_field_memory (
                id INT AUTO_INCREMENT PRIMARY KEY,
                memory_type ENUM('result','recommendation','confirmed_by') NOT NULL,
                issue_id INT NULL,
                company_key VARCHAR(255) NULL,
                value VARCHAR(500) NOT NULL,
                normalized_value VARCHAR(500) NOT NULL,
                created_by INT NULL,
                use_count INT NOT NULL DEFAULT 1,
                last_used_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uq_ticket_field_memory (memory_type, issue_id, company_key, normalized_value),
                KEY idx_ticket_field_issue (memory_type, issue_id),
                KEY idx_ticket_field_company (memory_type, company_key)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
        $columns = [];
        foreach (Database::fetchAll("SHOW COLUMNS FROM ticket_field_memory") as $column) {
            $columns[$column['Field']] = true;
        }
        $memoryAdds = [];
        if (!isset($columns['status'])) { $memoryAdds[] = "ADD COLUMN status ENUM('pending','approved') NOT NULL DEFAULT 'approved' AFTER normalized_value"; }
        if (!isset($columns['approved_by'])) { $memoryAdds[] = "ADD COLUMN approved_by INT NULL AFTER created_by"; }
        if (!isset($columns['approved_at'])) { $memoryAdds[] = "ADD COLUMN approved_at TIMESTAMP NULL DEFAULT NULL AFTER last_used_at"; }
        if (!isset($columns['deleted_at'])) { $memoryAdds[] = "ADD COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL AFTER approved_at"; }
        if ($memoryAdds) { Database::query("ALTER TABLE ticket_field_memory " . implode(', ', $memoryAdds)); }
        // Values collected before the approval workflow existed have no approver.
        // Put them into the queue instead of silently treating them as shared choices.
        Database::query(
            "UPDATE ticket_field_memory SET status = 'pending'
             WHERE status = 'approved' AND approved_by IS NULL AND approved_at IS NULL AND deleted_at IS NULL"
        );
        $memoryCount = Database::fetch("SELECT COUNT(*) AS total FROM ticket_field_memory");
        if ((int)($memoryCount['total'] ?? 0) === 0) { self::importHistoricalSessions(); }
        self::$ready = true;
    }

    private static function importHistoricalSessions(): void
    {
        $rows = Database::fetchAll(
            "SELECT issue_id, company_name, result_of_checking, recommendation, confirmed_by, user_id
             FROM troubleshooting_sessions
             WHERE (result_of_checking IS NOT NULL AND result_of_checking <> '')
                OR (recommendation IS NOT NULL AND recommendation <> '')
                OR (confirmed_by IS NOT NULL AND confirmed_by <> '')"
        ) ?: [];
        foreach ($rows as $row) {
            $issueId = (int)($row['issue_id'] ?? 0);
            $userId = !empty($row['user_id']) ? (int)$row['user_id'] : null;
            foreach (['result' => 'result_of_checking', 'recommendation' => 'recommendation'] as $type => $column) {
                $value = trim(preg_replace('/\s+/', ' ', (string)($row[$column] ?? '')));
                if ($issueId < 1 || $value === '') continue;
                $normalized = self::normalize($value);
                $exists = Database::fetch(
                    "SELECT id FROM ticket_field_memory WHERE memory_type = ? AND issue_id = ? AND normalized_value = ? AND deleted_at IS NULL LIMIT 1",
                    [$type, $issueId, $normalized]
                );
                if (!$exists) {
                    Database::insert('ticket_field_memory', [
                        'memory_type' => $type, 'issue_id' => $issueId, 'company_key' => null,
                        'value' => $value, 'normalized_value' => $normalized, 'status' => 'pending', 'created_by' => $userId,
                    ]);
                }
            }
            $companyKey = self::normalize((string)($row['company_name'] ?? ''));
            $confirmed = trim(preg_replace('/\s+/', ' ', (string)($row['confirmed_by'] ?? '')));
            if ($companyKey === '' || $confirmed === '') continue;
            $normalized = self::normalize($confirmed);
            $exists = Database::fetch(
                "SELECT id FROM ticket_field_memory WHERE memory_type = 'confirmed_by' AND company_key = ? AND normalized_value = ? AND deleted_at IS NULL LIMIT 1",
                [$companyKey, $normalized]
            );
            if (!$exists) {
                Database::insert('ticket_field_memory', [
                    'memory_type' => 'confirmed_by', 'issue_id' => null, 'company_key' => $companyKey,
                    'value' => $confirmed, 'normalized_value' => $normalized, 'status' => 'pending', 'created_by' => $userId,
                ]);
            }
        }
    }

    public static function normalize(string $value): string
    {
        return mb_strtolower(trim(preg_replace('/\s+/', ' ', $value)));
    }

    public static function rememberIssue(int $issueId, string $type, string $value, ?int $userId): void
    {
        self::ensure();
        $value = trim(preg_replace('/\s+/', ' ', $value));
        if ($issueId < 1 || $value === '' || !in_array($type, ['result', 'recommendation'], true)) { return; }
        $norm = self::normalize($value);
        $row = Database::fetch(
            "SELECT id, status FROM ticket_field_memory WHERE memory_type = ? AND issue_id = ? AND normalized_value = ? AND deleted_at IS NULL LIMIT 1",
            [$type, $issueId, $norm]
        );
        if ($row) {
            Database::query("UPDATE ticket_field_memory SET value = ?, use_count = use_count + 1, last_used_at = NOW() WHERE id = ?", [$value, $row['id']]);
            return;
        }
        Database::insert('ticket_field_memory', [
            'memory_type' => $type, 'issue_id' => $issueId, 'company_key' => null,
            'value' => $value, 'normalized_value' => $norm, 'status' => 'pending', 'created_by' => $userId,
        ]);
    }

    public static function rememberConfirmedBy(string $company, string $value, ?int $userId): void
    {
        self::ensure();
        $companyKey = self::normalize($company);
        $value = trim(preg_replace('/\s+/', ' ', $value));
        if ($companyKey === '' || $value === '') { return; }
        $norm = self::normalize($value);
        $row = Database::fetch(
            "SELECT id, status FROM ticket_field_memory WHERE memory_type = 'confirmed_by' AND company_key = ? AND normalized_value = ? AND deleted_at IS NULL LIMIT 1",
            [$companyKey, $norm]
        );
        if ($row) {
            Database::query("UPDATE ticket_field_memory SET value = ?, use_count = use_count + 1, last_used_at = NOW() WHERE id = ?", [$value, $row['id']]);
            return;
        }
        Database::insert('ticket_field_memory', [
            'memory_type' => 'confirmed_by', 'issue_id' => null, 'company_key' => $companyKey,
            'value' => $value, 'normalized_value' => $norm, 'status' => 'pending', 'created_by' => $userId,
        ]);
    }

    public static function issueOptions(): array
    {
        self::ensure();
        $out = [];
        $rows = Database::fetchAll(
            "SELECT issue_id, memory_type, value FROM ticket_field_memory
             WHERE memory_type IN ('result','recommendation') AND issue_id IS NOT NULL
               AND status = 'approved' AND deleted_at IS NULL
             ORDER BY use_count DESC, last_used_at DESC"
        ) ?: [];
        foreach ($rows as $row) {
            $issue = (string)(int)$row['issue_id'];
            if (!isset($out[$issue])) { $out[$issue] = ['result' => [], 'recommendation' => []]; }
            $out[$issue][$row['memory_type']][] = $row['value'];
        }
        return $out;
    }

    public static function companyContacts(): array
    {
        self::ensure();
        $out = [];
        $rows = Database::fetchAll(
            "SELECT company_key, value FROM ticket_field_memory WHERE memory_type = 'confirmed_by'
               AND status = 'approved' AND deleted_at IS NULL
             ORDER BY use_count DESC, last_used_at DESC"
        ) ?: [];
        foreach ($rows as $row) {
            if (!isset($out[$row['company_key']])) { $out[$row['company_key']] = []; }
            $out[$row['company_key']][] = $row['value'];
        }
        return $out;
    }

    public static function pending(): array
    {
        self::ensure();
        return Database::fetchAll(
            "SELECT m.*, u.full_name AS created_by_name, i.title AS issue_title
             FROM ticket_field_memory m
             LEFT JOIN users u ON m.created_by = u.id
             LEFT JOIN troubleshooting_issues i ON m.issue_id = i.id
             WHERE m.status = 'pending' AND m.deleted_at IS NULL
             ORDER BY m.memory_type, m.created_at DESC"
        ) ?: [];
    }

    public static function approve(array $ids, ?int $adminId): int
    {
        self::ensure();
        $count = 0;
        foreach ($ids as $id) {
            $stmt = Database::query(
                "UPDATE ticket_field_memory SET status = 'approved', approved_by = ?, approved_at = NOW()
                 WHERE id = ? AND status = 'pending' AND deleted_at IS NULL",
                [$adminId, (int)$id]
            );
            $count += $stmt->rowCount();
        }
        return $count;
    }

    public static function deletePending(array $ids): int
    {
        self::ensure();
        $count = 0;
        foreach ($ids as $id) {
            $stmt = Database::query(
                "UPDATE ticket_field_memory SET deleted_at = NOW() WHERE id = ? AND status = 'pending'",
                [(int)$id]
            );
            $count += $stmt->rowCount();
        }
        return $count;
    }
}
