<?php
final class Activity {
    public static function log(string $action, string $resourceType, ?int $resourceId, array $details = []): void {
        try {
            Database::insert('audit_logs', ['user_id' => Auth::userId(), 'action' => $action, 'resource_type' => $resourceType, 'resource_id' => $resourceId, 'details' => json_encode($details), 'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown', 'created_at' => date('Y-m-d H:i:s')]);
        } catch (Throwable $e) { error_log('Activity audit: ' . $e->getMessage()); }
    }
    public static function notifyUsers(array $userIds, string $type, string $title, string $message, string $url): void {
        foreach (array_unique(array_filter(array_map('intval', $userIds))) as $userId) {
            if ($userId === (int)Auth::userId()) continue;
            try {
                if (!Database::fetch("SELECT id FROM users WHERE id=? AND status='active' AND deleted_at IS NULL", [$userId])) continue;
                $permission = ['/admin/knowledge'=>'knowledge.manage','/admin/troubleshoot'=>'system.settings'][$url] ?? null;
                if ($permission && !Database::fetch("SELECT u.id FROM users u JOIN role_permissions rp ON rp.role_id=u.role_id JOIN permissions p ON p.id=rp.permission_id WHERE u.id=? AND p.permission_key IN (?, '*.*') LIMIT 1", [$userId,$permission])) continue;
            } catch (Throwable $e) { error_log('Notification access: '.$e->getMessage()); continue; }
            try { Database::insert('notifications', ['user_id' => $userId, 'type' => $type, 'title' => $title, 'message' => $message, 'url' => $url, 'is_read' => 0, 'created_at' => date('Y-m-d H:i:s')]); } catch (Throwable $e) { error_log('Activity notification: ' . $e->getMessage()); }
        }
    }
    public static function managers(?int $subjectUserId = null): array {
        try {
            $subject = Database::fetch('SELECT department_id FROM users WHERE id=?', [$subjectUserId ?? Auth::userId()]);
            if (empty($subject['department_id'])) return [];
            return array_map('intval', array_column(Database::fetchAll("SELECT u.id FROM users u JOIN roles r ON r.id=u.role_id WHERE u.status='active' AND u.deleted_at IS NULL AND u.department_id=? AND LOWER(r.name) IN ('admin','super admin','super_admin','manager','supervisor')", [$subject['department_id']]), 'id'));
        } catch (Throwable $e) { error_log('Notification recipients: '.$e->getMessage()); return []; }
    }
}
