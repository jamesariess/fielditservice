<?php
final class ChatAccess {
    public static function requestsAvailable(): bool {
        return (bool)Database::fetch("SHOW TABLES LIKE 'chat_department_requests'");
    }
    public static function canReview(): bool {
        return Auth::canViewAllTickets();
    }
    public static function allowed(int $a, int $b): bool {
        $users = Database::fetchAll('SELECT id, department_id FROM users WHERE id IN (?, ?) AND status = ? AND deleted_at IS NULL', [$a, $b, 'active']);
        if (count($users) !== 2) return false;
        if ($users[0]['department_id'] !== null && (int)$users[0]['department_id'] === (int)$users[1]['department_id']) return true;
        if (!self::requestsAvailable()) return false;
        return (bool)Database::fetch("SELECT q.id FROM chat_department_requests q JOIN users t ON t.id=q.target_user_id JOIN users other ON other.id=? WHERE q.status='approved' AND q.requester_id=? AND t.department_id=other.department_id UNION SELECT q.id FROM chat_department_requests q JOIN users t ON t.id=q.target_user_id JOIN users other ON other.id=? WHERE q.status='approved' AND q.requester_id=? AND t.department_id=other.department_id LIMIT 1", [$b,$a,$a,$b]);
    }
    public static function conversationAllowed(int $id, int $user): bool {
        if (!Database::fetch('SELECT id FROM chat_participants WHERE conversation_id=? AND user_id=?', [$id, $user])) return false;
        $others = Database::fetchAll('SELECT user_id FROM chat_participants WHERE conversation_id=? AND user_id<>?', [$id, $user]);
        foreach ($others as $other) if (!self::allowed($user, (int)$other['user_id'])) return false;
        return true;
    }
    public static function touch(): void {
        if (Database::fetch("SHOW COLUMNS FROM users LIKE 'last_seen_at'")) Database::query('UPDATE users SET last_seen_at=NOW() WHERE id=?', [Auth::userId()]);
    }
}
