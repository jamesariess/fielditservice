<?php
$page_title = 'My Tickets';
$active_menu = 'tickets';
require APP_ROOT . '/includes/layout_header.php';

$demo = !defined('DEMO_MODE') || DEMO_MODE;
$tickets = [];

if (!$demo) {
    try {
        $tickets = Database::fetchAll(
            "SELECT ts.*, i.title as issue_title, i.slug as issue_slug, c.name as category_name
             FROM troubleshooting_sessions ts
             LEFT JOIN troubleshooting_issues i ON ts.issue_id = i.id
             LEFT JOIN troubleshooting_categories c ON i.category_id = c.id
             WHERE ts.user_id = ?
             ORDER BY ts.created_at DESC",
            [Auth::userId()]
        );
    } catch (Exception $e) {}
}

// Build issue slug lookup for fallback
if (empty($tickets)) {
    $tickets = [
        ['id'=>1,'ticket_number'=>'TK-1001','problem_description'=>'User reports black screen on Dell OptiPlex 7090','status'=>'solved','priority'=>'high','category_name'=>'Display','model'=>'Dell OptiPlex 7090','department'=>'Finance','location'=>'Floor 3, Desk 42','customer_name'=>'Juan D.','created_at'=>'2026-08-22 09:15:00','issue_slug'=>'no-display','issue_title'=>'No Display'],
        ['id'=>2,'ticket_number'=>'TK-1002','problem_description'=>'Intermittent slow network on Floor 3','status'=>'in_progress','priority'=>'medium','category_name'=>'Network','model'=>'HP ProBook 450','department'=>'HR','location'=>'Floor 3, Room 301','customer_name'=>'Maria S.','created_at'=>'2026-08-22 11:00:00','issue_slug'=>'network-slow','issue_title'=>'Network Slow'],
        ['id'=>3,'ticket_number'=>'TK-1003','problem_description'=>'HP LaserJet Pro M404 not responding','status'=>'escalated','priority'=>'high','category_name'=>'Printer','model'=>'HP LaserJet Pro M404','department'=>'Reception','location'=>'Ground Floor, Reception','customer_name'=>'You','created_at'=>'2026-08-22 08:30:00','issue_slug'=>'printer-offline','issue_title'=>'Printer Offline'],
        ['id'=>4,'ticket_number'=>'TK-1004','problem_description'=>'No audio output from Lenovo ThinkCentre','status'=>'solved','priority'=>'medium','category_name'=>'Sound','model'=>'Lenovo ThinkCentre M70s','department'=>'Operations','location'=>'Floor 2, Meeting Room A','customer_name'=>'Carlos R.','created_at'=>'2026-08-22 10:45:00','issue_slug'=>'no-sound','issue_title'=>'No Sound'],
        ['id'=>5,'ticket_number'=>'TK-1005','problem_description'=>'Cannot connect to WiFi on Dell Latitude','status'=>'in_progress','priority'=>'low','category_name'=>'Network','model'=>'Dell Latitude 5520','department'=>'Sales','location'=>'Floor 1, Sales Area','customer_name'=>'Ana T.','created_at'=>'2026-08-22 11:30:00','issue_slug'=>'wifi-not-connecting','issue_title'=>'WiFi Not Connecting'],
    ];
}

$total = count($tickets);
$solved = 0; $inProgress = 0; $escalated = 0;
foreach ($tickets as $t) {
    if ($t['status'] === 'solved') $solved++;
    elseif ($t['status'] === 'in_progress') $inProgress++;
    elseif ($t['status'] === 'escalated') $escalated++;
}

$categories = [['id'=>1,'name'=>'Display'],['id'=>2,'name'=>'Power'],['id'=>3,'name'=>'Sound'],['id'=>4,'name'=>'Network'],['id'=>5,'name'=>'Printer'],['id'=>6,'name'=>'Software'],['id'=>7,'name'=>'Hardware'],['id'=>8,'name'=>'CCTV']];
$departments = ['IT','Finance','HR','Operations','Sales','Reception','Marketing','Security'];
?>

<div id="new-ticket-modal" class="modal-overlay" style="display:none;">
    <div class="backdrop" onclick="closeModal('new-ticket-modal')"></div>
    <div class="modal-panel" style="max-width:560px;margin-top:5vh;max-height:90vh;overflow-y:auto;">
        <div style="padding:20px 24px;border-bottom:1px solid #e5e7eb;display:flex;justify-content:space-between;align-items:center;">
            <h2 style="font-size:18px;font-weight:700;color:#111827;">New Ticket</h2>
            <button onclick="closeModal('new-ticket-modal')" style="background:none;border:none;cursor:pointer;color:#94a3b8;font-size:20px;">&#10005;</button>
        </div>
        <form onsubmit="createTicket(event)" style="padding:20px 24px;">
            <div style="margin-bottom:14px;">
                <label style="display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:4px;">Title *</label>
                <input name="title" required placeholder="Brief description" class="form-input dark-input" style="width:100%;padding:10px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;">
            </div>
            <div style="margin-bottom:14px;">
                <label style="display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:4px;">Description *</label>
                <textarea name="description" required rows="3" placeholder="Detailed description" class="form-input dark-input" style="width:100%;padding:10px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;resize:vertical;"></textarea>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px;">
                <div>
                    <label style="display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:4px;">Category *</label>
                    <select name="category" required class="form-input dark-input" style="width:100%;padding:10px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;">
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= e($cat['name']) ?>"><?= e($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label style="display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:4px;">Priority *</label>
                    <select name="priority" required class="form-input dark-input" style="width:100%;padding:10px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;">
                        <option value="low">Low</option><option value="medium" selected>Medium</option><option value="high">High</option><option value="critical">Critical</option>
                    </select>
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px;">
                <div>
                    <label style="display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:4px;">Department</label>
                    <select name="department" class="form-input dark-input" style="width:100%;padding:10px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;">
                        <?php foreach ($departments as $d): ?>
                            <option value="<?= e($d) ?>"><?= e($d) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label style="display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:4px;">Location</label>
                    <input name="location" placeholder="Floor, Room, Desk" class="form-input dark-input" style="width:100%;padding:10px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;">
                </div>
            </div>
            <div style="margin-bottom:16px;">
                <label style="display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:4px;">Device / Equipment</label>
                <input name="device" placeholder="e.g., Dell OptiPlex 7090" class="form-input dark-input" style="width:100%;padding:10px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;">
            </div>
            <div style="display:flex;gap:8px;justify-content:flex-end;">
                <button type="button" onclick="closeModal('new-ticket-modal')" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-primary"><i data-lucide="plus" style="width:15px;height:15px;"></i> Create Ticket</button>
            </div>
        </form>
    </div>
</div>

<div style="max-width:1000px;margin:0 auto;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
        <div>
            <h1 style="font-size:24px;font-weight:800;color:#111827;">My Tickets</h1>
            <p style="font-size:13px;color:#64748b;">Manage your troubleshooting sessions and view ticket history</p>
        </div>
        <button onclick="openNewTicketModal()" class="btn btn-primary"><i data-lucide="plus" style="width:16px;height:16px;"></i> New Ticket</button>
    </div>
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:20px;">
        <div class="card"><div class="card-body" style="text-align:center;"><div style="font-size:28px;font-weight:800;color:#2563eb;"><?= $total ?></div><div style="font-size:12px;color:#64748b;">Total Tickets</div></div></div>
        <div class="card"><div class="card-body" style="text-align:center;"><div style="font-size:28px;font-weight:800;color:#16a34a;"><?= $solved ?></div><div style="font-size:12px;color:#64748b;">Solved</div></div></div>
        <div class="card"><div class="card-body" style="text-align:center;"><div style="font-size:28px;font-weight:800;color:#d97706;"><?= $inProgress ?></div><div style="font-size:12px;color:#64748b;">In Progress</div></div></div>
        <div class="card"><div class="card-body" style="text-align:center;"><div style="font-size:28px;font-weight:800;color:#dc2626;"><?= $escalated ?></div><div style="font-size:12px;color:#64748b;">Escalated</div></div></div>
    </div>
    <div style="display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap;">
        <button onclick="ticketFilter('all')" class="btn btn-sm filter-btn active" data-filter="all">All (<?= $total ?>)</button>
        <button onclick="ticketFilter('in_progress')" class="btn btn-sm btn-secondary filter-btn" data-filter="in_progress">In Progress</button>
        <button onclick="ticketFilter('solved')" class="btn btn-sm btn-secondary filter-btn" data-filter="solved">Solved</button>
        <button onclick="ticketFilter('escalated')" class="btn btn-sm btn-secondary filter-btn" data-filter="escalated">Escalated</button>
    </div>
    <?php foreach ($tickets as $t):
        $ticketId = $t['id'];
        $ticketNum = $t['ticket_number'] ?? ('TK-' . $t['id']);
        $title = ($t['issue_title'] ?? $t['issue_slug'] ?? 'Issue') . ' — ' . ($t['model'] ?? $t['device_type'] ?? '');
        $statusColor = $t['status'] === 'solved' ? '#16a34a' : ($t['status'] === 'escalated' ? '#dc2626' : '#d97706');
        $statusBg = $t['status'] === 'solved' ? '#f0fdf4' : ($t['status'] === 'escalated' ? '#fef2f2' : '#fffbeb');
        $priorityColor = ($t['priority'] ?? 'medium') === 'high' ? '#dc2626' : (($t['priority'] ?? '') === 'low' ? '#16a34a' : '#d97706');
        $timeAgo = isset($t['created_at']) ? date('M d, g:i A', strtotime($t['created_at'])) : 'Aug 22';
        $assignee = $t['customer_name'] ?? $t['assignee_name'] ?? 'You';
        $device = $t['model'] ?? $t['device'] ?? $t['device_type'] ?? '';
    ?>
    <div class="card ticket-card" data-status="<?= e($t['status']) ?>" style="margin-bottom:12px;">
        <div class="card-body">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;">
                <div style="flex:1;">
                    <div style="display:flex;gap:8px;margin-bottom:6px;flex-wrap:wrap;">
                        <span style="font-size:12px;color:#64748b;font-weight:600;"><?= e($ticketNum) ?></span>
                        <span class="badge" style="background:<?= $statusBg ?>;color:<?= $statusColor ?>;"><?= e(ucwords(str_replace('_',' ',$t['status']))) ?></span>
                        <span class="badge" style="background:<?= $priorityColor ?>20;color:<?= $priorityColor ?>;"><?= e(ucfirst($t['priority'] ?? 'medium')) ?></span>
                    </div>
                    <h3 style="font-size:15px;font-weight:700;color:#111827;margin-bottom:4px;"><?= e($title) ?></h3>
                    <div style="display:flex;gap:12px;font-size:12px;color:#94a3b8;flex-wrap:wrap;">
                        <span>&#128187; <?= e($device) ?></span>
                        <span>&#127970; <?= e($t['department'] ?? '') ?></span>
                        <span>&#128205; <?= e($t['location'] ?? '') ?></span>
                        <span>&#128100; <?= e($assignee) ?></span>
                    </div>
                </div>
                <div style="text-align:right;flex-shrink:0;">
                    <div style="font-size:11px;color:#94a3b8;margin-bottom:8px;"><?= $timeAgo ?></div>
                    <?php if ($t['status'] === 'solved'): ?>
                        <span style="color:#16a34a;font-size:13px;font-weight:600;">&#10004; Resolved</span>
                    <?php elseif ($t['status'] !== 'escalated'): ?>
                        <button onclick="ticketTroubleshoot(<?= $ticketId ?>, '<?= e($t['issue_slug'] ?? 'no-display') ?>')" class="btn btn-primary btn-sm"><i data-lucide="stethoscope" style="width:14px;height:14px;"></i> Troubleshoot</button>
                    <?php else: ?>
                        <span style="color:#dc2626;font-size:12px;font-weight:600;">Escalated</span>
                    <?php endif; ?>
                </div>
            </div>
            <div id="troubleshoot-panel-<?= $ticketId ?>" style="display:none;margin-top:16px;border-top:1px solid #e5e7eb;padding-top:16px;"></div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php require APP_ROOT . '/includes/layout_footer.php'; ?>
