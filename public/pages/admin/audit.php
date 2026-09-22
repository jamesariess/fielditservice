<?php
if (!defined('APP_ROOT')) { @header('Location: /fielditservice/'); exit; }

$page_title = 'Audit Logs';
$active_menu = 'audit';
$required_permission = 'audit.view';
require APP_ROOT . '/includes/admin_guard.php';

$search = trim((string)($_GET['q'] ?? ''));
$actionFilter = trim((string)($_GET['action'] ?? ''));
$userFilter = (int)($_GET['user'] ?? 0);
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 40;
$where = [];
$params = [];

if ($search !== '') {
    $like = '%' . $search . '%';
    $where[] = "(u.full_name LIKE ? OR u.email LIKE ? OR a.action LIKE ? OR a.resource_type LIKE ? OR CAST(a.resource_id AS CHAR) LIKE ? OR a.ip_address LIKE ? OR a.details LIKE ?)";
    array_push($params, $like, $like, $like, $like, $like, $like, $like);
}
if ($actionFilter !== '') { $where[] = 'a.action = ?'; $params[] = $actionFilter; }
if ($userFilter > 0) { $where[] = 'a.user_id = ?'; $params[] = $userFilter; }
$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$totalRow = Database::fetch("SELECT COUNT(*) AS total FROM audit_logs a LEFT JOIN users u ON u.id = a.user_id $whereSql", $params);
$total = (int)($totalRow['total'] ?? 0);
$totalPages = max(1, (int)ceil($total / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;
$logs = Database::fetchAll(
    "SELECT a.id, a.user_id, a.action, a.resource_type, a.resource_id, a.details,
            a.ip_address, a.user_agent, a.created_at, u.full_name, u.email
     FROM audit_logs a LEFT JOIN users u ON u.id = a.user_id
     $whereSql ORDER BY a.created_at DESC, a.id DESC LIMIT $perPage OFFSET $offset",
    $params
);
$actions = Database::fetchAll("SELECT DISTINCT action FROM audit_logs WHERE action IS NOT NULL AND action <> '' ORDER BY action");
$auditUsers = Database::fetchAll("SELECT DISTINCT u.id, u.full_name FROM audit_logs a INNER JOIN users u ON u.id = a.user_id ORDER BY u.full_name");

function auditBadgeClass(string $action): string {
    $action = strtoupper($action);
    if (str_contains($action, 'FAIL') || str_contains($action, 'DELETE') || str_contains($action, 'REJECT')) return 'danger';
    if (str_contains($action, 'LOGIN') || str_contains($action, 'APPROVE') || str_contains($action, 'SUCCESS')) return 'success';
    if (str_contains($action, 'UPDATE') || str_contains($action, 'EDIT') || str_contains($action, 'PUBLISH')) return 'info';
    if (str_contains($action, 'CREATE') || str_contains($action, 'ADD') || str_contains($action, 'INVITE')) return 'violet';
    return 'neutral';
}

function auditPageUrl(int $targetPage): string {
    $query = $_GET;
    $query['page'] = $targetPage;
    return app_base() . 'admin/audit?' . http_build_query($query);
}

require APP_ROOT . '/includes/layout_header.php';
?>
<div class="max-w-7xl mx-auto">
    <div class="page-hero fx-reveal">
        <div style="display:flex;align-items:center;gap:14px;">
            <div class="page-hero-ico amber"><i data-lucide="scroll-text"></i></div>
            <div><h1 class="page-hero-title">Audit Logs</h1><p class="page-hero-sub">Live system activity and security audit trail</p></div>
        </div>
        <div class="audit-total"><strong><?= number_format($total) ?></strong><span>matching events</span></div>
    </div>

    <form method="get" action="<?= e(app_base() . 'admin/audit') ?>" class="audit-filters">
        <div class="audit-search"><i data-lucide="search"></i><input name="q" value="<?= e($search) ?>" type="search" placeholder="Search user, action, resource, IP, or details..."></div>
        <select name="action" aria-label="Filter by action" onchange="this.form.submit()">
            <option value="">All actions</option>
            <?php foreach ($actions as $action): ?><option value="<?= e($action['action']) ?>" <?= $actionFilter === $action['action'] ? 'selected' : '' ?>><?= e($action['action']) ?></option><?php endforeach; ?>
        </select>
        <select name="user" aria-label="Filter by user" onchange="this.form.submit()">
            <option value="0">All users</option>
            <?php foreach ($auditUsers as $auditUser): ?><option value="<?= (int)$auditUser['id'] ?>" <?= $userFilter === (int)$auditUser['id'] ? 'selected' : '' ?>><?= e($auditUser['full_name']) ?></option><?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-primary"><i data-lucide="search"></i> Search</button>
        <?php if ($search !== '' || $actionFilter !== '' || $userFilter > 0): ?><a href="<?= e(app_base() . 'admin/audit') ?>" class="btn btn-secondary" data-tooltip="Clear filters"><i data-lucide="x"></i></a><?php endif; ?>
    </form>

    <div class="audit-table-wrap">
        <div class="audit-table-scroll">
            <table class="audit-table">
                <thead><tr><th>Timestamp</th><th>User</th><th>Action</th><th>Resource</th><th>IP Address</th><th>Details</th></tr></thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                        <?php
                        $details = json_decode((string)($log['details'] ?? ''), true);
                        $detailsText = is_array($details) ? implode(' · ', array_map(function ($key, $value) {
                            if (is_array($value)) $value = json_encode($value, JSON_UNESCAPED_SLASHES);
                            return ucwords(str_replace('_', ' ', (string)$key)) . ': ' . (string)$value;
                        }, array_keys($details), array_values($details))) : trim((string)($log['details'] ?? ''));
                        $resource = trim((string)($log['resource_type'] ?? '')) ?: 'system';
                        if ($log['resource_id'] !== null) $resource .= ' #' . $log['resource_id'];
                        ?>
                        <tr>
                            <td class="audit-time"><?= e($log['created_at']) ?></td>
                            <td><div class="audit-user-name"><?= e($log['full_name'] ?: 'System / Unknown') ?></div><?php if (!empty($log['email'])): ?><div class="audit-user-email"><?= e($log['email']) ?></div><?php endif; ?></td>
                            <td><span class="audit-badge <?= auditBadgeClass($log['action']) ?>"><?= e($log['action']) ?></span></td>
                            <td class="audit-resource"><?= e($resource) ?></td>
                            <td class="audit-ip"><?= e($log['ip_address'] ?: '—') ?></td>
                            <td class="audit-details"><?php if ($detailsText !== ''): ?><details><summary>View</summary><div><?= e($detailsText) ?></div></details><?php else: ?>—<?php endif; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php if (!$logs): ?><div class="audit-empty"><i data-lucide="search-x"></i><strong>No audit events found</strong><span>Try clearing or changing the current filters.</span></div><?php endif; ?>
        <?php if ($totalPages > 1): ?>
            <div class="audit-pagination"><span>Page <?= $page ?> of <?= $totalPages ?></span><div>
                <a class="btn btn-secondary btn-sm <?= $page <= 1 ? 'disabled' : '' ?>" href="<?= $page > 1 ? e(auditPageUrl($page - 1)) : '#' ?>"><i data-lucide="chevron-left"></i> Previous</a>
                <a class="btn btn-secondary btn-sm <?= $page >= $totalPages ? 'disabled' : '' ?>" href="<?= $page < $totalPages ? e(auditPageUrl($page + 1)) : '#' ?>">Next <i data-lucide="chevron-right"></i></a>
            </div></div>
        <?php endif; ?>
    </div>
</div>

<style>
.audit-total{display:flex;align-items:baseline;gap:7px;color:#64748b;font-size:12px}.audit-total strong{color:#0f172a;font-size:20px}.audit-filters{display:grid;grid-template-columns:minmax(260px,1fr) 180px 190px auto auto;gap:10px;margin-bottom:16px}.audit-search{position:relative}.audit-search i{position:absolute;left:12px;top:50%;width:16px;height:16px;color:#94a3b8;transform:translateY(-50%)}.audit-search input,.audit-filters select{width:100%;height:42px;border:1px solid #cbd5e1;border-radius:7px;background:#fff;padding:0 12px;font-size:13px;color:#334155}.audit-search input{padding-left:38px}.audit-table-wrap{overflow:hidden;border:1px solid #dbe3ef;border-radius:8px;background:#fff}.audit-table-scroll{overflow-x:auto}.audit-table{width:100%;min-width:900px;border-collapse:collapse}.audit-table th{padding:12px 16px;border-bottom:1px solid #dbe3ef;background:#f8fafc;color:#64748b;font-size:11px;font-weight:700;text-align:left;text-transform:uppercase}.audit-table td{padding:12px 16px;border-bottom:1px solid #edf1f6;color:#334155;font-size:13px;vertical-align:top}.audit-table tbody tr:hover{background:#f8fafc}.audit-time,.audit-ip{color:#64748b!important;font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:11.5px!important;white-space:nowrap}.audit-user-name{color:#172033;font-weight:650}.audit-user-email{margin-top:2px;color:#94a3b8;font-size:11px}.audit-resource{text-transform:capitalize;white-space:nowrap}.audit-badge{display:inline-flex;padding:4px 8px;border-radius:999px;font-size:10.5px;font-weight:750;white-space:nowrap}.audit-badge.success{background:#dcfce7;color:#15803d}.audit-badge.danger{background:#fee2e2;color:#dc2626}.audit-badge.info{background:#dbeafe;color:#1d4ed8}.audit-badge.violet{background:#ede9fe;color:#7c3aed}.audit-badge.neutral{background:#e2e8f0;color:#475569}.audit-details{max-width:280px}.audit-details summary{color:#2563eb;font-size:12px;font-weight:650;cursor:pointer}.audit-details details div{margin-top:6px;padding:8px;border-radius:6px;background:#f1f5f9;color:#475569;font-size:11.5px;line-height:1.45;overflow-wrap:anywhere}.audit-empty{display:flex;min-height:220px;align-items:center;justify-content:center;flex-direction:column;gap:7px;color:#94a3b8;text-align:center}.audit-empty i{width:28px;height:28px}.audit-empty strong{color:#475569;font-size:14px}.audit-empty span{font-size:12px}.audit-pagination{display:flex;align-items:center;justify-content:space-between;padding:12px 16px;color:#64748b;font-size:12px}.audit-pagination div{display:flex;gap:8px}.audit-pagination .disabled{pointer-events:none;opacity:.45}.dark .audit-table-wrap,.dark .audit-search input,.dark .audit-filters select{background:#111827;border-color:#334155;color:#e2e8f0}.dark .audit-table th{background:#1e293b;border-color:#334155}.dark .audit-table td{border-color:#253247;color:#cbd5e1}.dark .audit-table tbody tr:hover{background:#172033}.dark .audit-user-name,.dark .audit-total strong{color:#f1f5f9}.dark .audit-details details div{background:#1e293b;color:#cbd5e1}@media(max-width:900px){.audit-filters{grid-template-columns:1fr 1fr}.audit-search{grid-column:1/-1}}@media(max-width:560px){.audit-filters{grid-template-columns:1fr}.audit-search{grid-column:auto}.audit-total{display:none}}
</style>
<?php require APP_ROOT . '/includes/layout_footer.php'; ?>
