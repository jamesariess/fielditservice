<?php
final class Activity {
    public static function log(string $action, string $resourceType, ?int $resourceId, array $details = []): void {
        try {
            Database::insert('audit_logs', ['user_id' => Auth::userId(), 'action' => $action, 'resource_type' => $resourceType, 'resource_id' => $resourceId, 'details' => json_encode($details), 'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown', 'created_at' => date('Y-m-d H:i:s')]);
        } catch (Throwable $e) { error_log('Activity audit: ' . $e->getMessage()); }
    }
    public static function notifyUsers(array $userIds, string $type, string $title, string $message, string $url): void {
        foreach (array_unique(array_filter(array_map('intval', $userIds))) as $userId) {
            try { Database::insert('notifications', ['user_id' => $userId, 'type' => $type, 'title' => $title, 'message' => $message, 'url' => $url, 'is_read' => 0, 'created_at' => date('Y-m-d H:i:s')]); } catch (Throwable $e) { error_log('Activity notification: ' . $e->getMessage()); }
        }
    }
    public static function managers(): array {
        try { return array_map('intval', array_column(Database::fetchAll("SELECT u.id FROM users u JOIN roles r ON r.id=u.role_id WHERE u.status='active' AND LOWER(r.name) IN ('admin','super admin','manager','supervisor')"), 'id')); } catch (Throwable $e) { return []; }
    }
}
