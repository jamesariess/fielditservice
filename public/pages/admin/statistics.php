<?php
if (!defined('APP_ROOT')) { @header('Location: /fielditservice/'); exit; }

$page_title = 'Statistics';
$active_menu = 'admin-statistics';
$required_permission = 'audit.view';
require APP_ROOT . '/includes/admin_guard.php';

$allowedRanges = [7, 30, 90, 0];
$days = (int)($_GET['days'] ?? 30);
if (!in_array($days, $allowedRanges, true)) $days = 30;
$rangeLabel = $days === 0 ? 'All time' : "Last $days days";
$sessionFilter = $days ? "WHERE s.created_at >= DATE_SUB(NOW(), INTERVAL $days DAY)" : '';
$aiFilter = $days ? "WHERE created_at >= DATE_SUB(NOW(), INTERVAL $days DAY)" : '';

$summary = Database::fetch(
    "SELECT COUNT(*) total,
            SUM(status = 'solved') solved,
            SUM(status = 'escalated') escalated,
            SUM(status = 'in_progress') in_progress,
            ROUND(AVG(CASE WHEN status = 'solved' AND time_spent_minutes BETWEEN 1 AND 480 THEN time_spent_minutes END)) avg_minutes
     FROM troubleshooting_sessions s $sessionFilter"
) ?: [];
$totalSessions = (int)($summary['total'] ?? 0);
$solvedCount = (int)($summary['solved'] ?? 0);
$solvedRate = $totalSessions ? round(($solvedCount / $totalSessions) * 100) : 0;
$escalationRate = $totalSessions ? round(((int)($summary['escalated'] ?? 0) / $totalSessions) * 100) : 0;
$activeUsers = (int)(Database::fetch("SELECT COUNT(*) total FROM users WHERE status = 'active'")['total'] ?? 0);
$aiSummary = Database::fetch("SELECT COUNT(*) messages, COUNT(DISTINCT session_id) conversations FROM ai_conversation_logs $aiFilter") ?: [];

$topIssues = Database::fetchAll(
    "SELECT COALESCE(NULLIF(ti.title,''), NULLIF(s.problem_description,''), NULLIF(s.task,''), 'Unspecified') label,
            COUNT(*) total, SUM(s.status = 'solved') solved
     FROM troubleshooting_sessions s LEFT JOIN troubleshooting_issues ti ON ti.id = s.issue_id
     $sessionFilter GROUP BY label ORDER BY total DESC, label LIMIT 8"
);
$topDevices = Database::fetchAll(
    "SELECT COALESCE(NULLIF(s.model,''), NULLIF(s.device_type,''), 'Unspecified device') label,
            COALESCE(NULLIF(s.device_type,''), 'Other') device_type, COUNT(*) total
     FROM troubleshooting_sessions s $sessionFilter
     GROUP BY label, device_type ORDER BY total DESC, label LIMIT 8"
);
$statusRows = Database::fetchAll(
    "SELECT status, COUNT(*) total FROM troubleshooting_sessions s $sessionFilter GROUP BY status ORDER BY total DESC"
);
$technicians = Database::fetchAll(
    "SELECT u.full_name, COUNT(s.id) total, SUM(s.status = 'solved') solved,
            ROUND(AVG(CASE WHEN s.status = 'solved' AND s.time_spent_minutes BETWEEN 1 AND 480 THEN s.time_spent_minutes END)) avg_minutes
     FROM troubleshooting_sessions s INNER JOIN users u ON u.id = s.user_id
     $sessionFilter GROUP BY u.id, u.full_name ORDER BY total DESC, solved DESC LIMIT 8"
);

$chartDays = $days === 0 ? 30 : min($days, 30);
$chartLookback = $chartDays - 1;
$dailyRows = Database::fetchAll(
    "SELECT DATE(created_at) day, COUNT(*) total, SUM(status = 'solved') solved
     FROM troubleshooting_sessions
     WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL $chartLookback DAY)
     GROUP BY DATE(created_at) ORDER BY day"
);
$dailyMap = [];
foreach ($dailyRows as $row) $dailyMap[$row['day']] = $row;
$daily = [];
for ($i = $chartDays - 1; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $daily[] = ['day' => $date, 'total' => (int)($dailyMap[$date]['total'] ?? 0), 'solved' => (int)($dailyMap[$date]['solved'] ?? 0)];
}
$maxDaily = max(1, ...array_column($daily, 'total'));
$maxIssue = max(1, ...array_map(fn($row) => (int)$row['total'], $topIssues ?: [['total' => 1]]));
$maxDevice = max(1, ...array_map(fn($row) => (int)$row['total'], $topDevices ?: [['total' => 1]]));

$knowledge = Database::fetch(
    "SELECT COUNT(*) total, SUM(status = 'published') published,
            SUM(status IN ('submitted','under_review')) pending,
            ROUND(AVG(NULLIF(quality_score, 0)),1) avg_quality
     FROM knowledge_articles WHERE deleted_at IS NULL"
) ?: [];
$equipment = Database::fetch("SELECT COUNT(*) total, SUM(status = 'active') active FROM equipment WHERE deleted_at IS NULL") ?: [];
$searchGaps = Database::fetchAll(
    "SELECT query, COUNT(*) searches FROM search_analytics WHERE results_count = 0
     GROUP BY query ORDER BY searches DESC, query LIMIT 6"
);

function statUrl(int $range): string { return app_base() . 'admin/statistics?days=' . $range; }
function statusLabel(string $status): string { return ucwords(str_replace('_', ' ', $status)); }

require APP_ROOT . '/includes/layout_header.php';
?>
<link rel="stylesheet" href="<?= $urlBase ?>assets/css/workspace-refresh.css?v=<?= filemtime(APP_ROOT . '/public/assets/css/workspace-refresh.css') ?>">
<div class="stats-page">
    <div class="page-hero fx-reveal stats-hero">
        <div style="display:flex;align-items:center;gap:14px;">
            <div class="page-hero-ico cyan"><i data-lucide="chart-no-axes-combined"></i></div>
            <div><h1 class="page-hero-title">Statistics</h1><p class="page-hero-sub">Live operational performance across field support</p></div>
        </div>
        <nav class="stats-range" aria-label="Statistics date range">
            <?php foreach ([7=>'7 days',30=>'30 days',90=>'90 days',0=>'All time'] as $range => $label): ?>
                <a href="<?= e(statUrl($range)) ?>" class="<?= $days === $range ? 'active' : '' ?>"><?= $label ?></a>
            <?php endforeach; ?>
        </nav>
    </div>

    <section class="stats-kpis stats-summary" aria-label="Operational summary">
        <article class="stats-kpi stats-kpi-group">
            <header class="stats-kpi-head">
                <span class="stats-kpi-icon tone-blue"><i data-lucide="activity"></i></span>
                <div><h2>Session performance</h2><p><?= e($rangeLabel) ?></p></div>
            </header>
            <div class="stats-kpi-pair">
                <div class="stats-metric"><span>Sessions</span><strong><?= number_format($totalSessions) ?></strong><small>Created in this period</small></div>
                <div class="stats-metric success"><span>Solved rate</span><strong><?= $solvedRate ?>%</strong><small><?= number_format($solvedCount) ?> solved</small></div>
            </div>
        </article>

        <article class="stats-kpi stats-kpi-single warning">
            <header class="stats-kpi-head"><span class="stats-kpi-icon tone-red"><i data-lucide="triangle-alert"></i></span><div><h2>Escalation</h2><p>Needs attention</p></div></header>
            <div class="stats-metric"><span>Escalation rate</span><strong><?= $escalationRate ?>%</strong><small><?= number_format((int)($summary['escalated'] ?? 0)) ?> escalated</small></div>
        </article>

        <article class="stats-kpi stats-kpi-single">
            <header class="stats-kpi-head"><span class="stats-kpi-icon tone-blue"><i data-lucide="timer"></i></span><div><h2>Resolution</h2><p>Completed work</p></div></header>
            <div class="stats-metric"><span>Average resolution</span><strong><?= $summary['avg_minutes'] ? (int)$summary['avg_minutes'] . ' min' : '—' ?></strong><small>Completed work up to 8 hours</small></div>
        </article>

        <article class="stats-kpi stats-kpi-single">
            <header class="stats-kpi-head"><span class="stats-kpi-icon tone-blue"><i data-lucide="users"></i></span><div><h2>Team availability</h2><p>Current workload</p></div></header>
            <div class="stats-metric"><span>Active users</span><strong><?= number_format($activeUsers) ?></strong><small><?= number_format((int)($summary['in_progress'] ?? 0)) ?> jobs in progress</small></div>
        </article>

        <article class="stats-kpi stats-kpi-single">
            <header class="stats-kpi-head"><span class="stats-kpi-icon tone-blue"><i data-lucide="sparkles"></i></span><div><h2>AI activity</h2><p>Support assistant</p></div></header>
            <div class="stats-metric"><span>Conversations</span><strong><?= number_format((int)($aiSummary['conversations'] ?? 0)) ?></strong><small><?= number_format((int)($aiSummary['messages'] ?? 0)) ?> messages</small></div>
        </article>
    </section>

    <div class="stats-main-grid">
        <section class="stats-panel stats-trend">
            <header><div><h2>Daily Workload</h2><p>Sessions created and solved over the last <?= $chartDays ?> days</p></div><div class="stats-legend"><span><i class="total"></i>Created</span><span><i class="solved"></i>Solved</span></div></header>
            <div class="trend-chart" role="img" aria-label="Daily created and solved sessions">
                <?php foreach ($daily as $index => $row): ?>
                    <?php $showLabel = $chartDays <= 14 || $index % 3 === 0 || $index === count($daily)-1; ?>
                    <div class="trend-day" title="<?= e(date('M j', strtotime($row['day']))) ?>: <?= $row['total'] ?> created, <?= $row['solved'] ?> solved">
                        <div class="trend-bars"><i class="created" style="height:<?= max(2, round($row['total'] / $maxDaily * 100)) ?>%"></i><i class="done" style="height:<?= max(2, round($row['solved'] / $maxDaily * 100)) ?>%"></i></div>
                        <span><?= $showLabel ? e(date('M j', strtotime($row['day']))) : '' ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="stats-panel">
            <header><div><h2>Work Status</h2><p>Current distribution for <?= strtolower($rangeLabel) ?></p></div></header>
            <div class="status-list">
                <?php foreach ($statusRows as $row): $pct = $totalSessions ? round($row['total'] / $totalSessions * 100) : 0; ?>
                    <div class="status-row"><div><span><?= e(statusLabel($row['status'])) ?></span><strong><?= (int)$row['total'] ?></strong></div><div class="meter"><i class="status-<?= e($row['status']) ?>" style="width:<?= $pct ?>%"></i></div><small><?= $pct ?>%</small></div>
                <?php endforeach; ?>
                <?php if (!$statusRows): ?><div class="stats-empty">No sessions in this date range.</div><?php endif; ?>
            </div>
        </section>
    </div>

    <div class="stats-list-grid">
        <section class="stats-panel">
            <header><div><h2>Most Common Problems</h2><p>Volume and solved rate by issue</p></div></header>
            <div class="rank-list">
                <?php foreach ($topIssues as $index => $row): $rate = $row['total'] ? round($row['solved'] / $row['total'] * 100) : 0; ?>
                    <div class="rank-row"><b><?= $index + 1 ?></b><div><span><?= e($row['label']) ?></span><div class="meter"><i style="width:<?= round($row['total'] / $maxIssue * 100) ?>%"></i></div></div><aside><strong><?= (int)$row['total'] ?></strong><small><?= $rate ?>% solved</small></aside></div>
                <?php endforeach; ?>
                <?php if (!$topIssues): ?><div class="stats-empty">No problem data yet.</div><?php endif; ?>
            </div>
        </section>

        <section class="stats-panel">
            <header><div><h2>Most Serviced Devices</h2><p>Models receiving the most field work</p></div></header>
            <div class="rank-list device-list">
                <?php foreach ($topDevices as $index => $row): ?>
                    <div class="rank-row"><b><?= $index + 1 ?></b><div><span><?= e($row['label']) ?> <em><?= e($row['device_type']) ?></em></span><div class="meter"><i style="width:<?= round($row['total'] / $maxDevice * 100) ?>%"></i></div></div><aside><strong><?= (int)$row['total'] ?></strong><small>sessions</small></aside></div>
                <?php endforeach; ?>
                <?php if (!$topDevices): ?><div class="stats-empty">No device data yet.</div><?php endif; ?>
            </div>
        </section>
    </div>

    <div class="stats-bottom-grid">
        <section class="stats-panel">
            <header><div><h2>Technician Activity</h2><p>Completed workload by assigned technician</p></div></header>
            <div class="tech-table">
                <div class="tech-head"><span>Technician</span><span>Sessions</span><span>Solved</span><span>Avg time</span></div>
                <?php foreach ($technicians as $tech): ?>
                    <div class="tech-row"><strong><?= e($tech['full_name']) ?></strong><span><?= (int)$tech['total'] ?></span><span><?= (int)$tech['solved'] ?></span><span><?= $tech['avg_minutes'] ? (int)$tech['avg_minutes'] . ' min' : '—' ?></span></div>
                <?php endforeach; ?>
                <?php if (!$technicians): ?><div class="stats-empty">No technician activity yet.</div><?php endif; ?>
            </div>
        </section>

        <section class="stats-panel health-panel">
            <header><div><h2>Content & Assets</h2><p>System readiness outside active tickets</p></div></header>
            <div class="health-grid">
                <div><i data-lucide="book-open"></i><strong><?= (int)($knowledge['total'] ?? 0) ?></strong><span>Knowledge articles</span></div>
                <div><i data-lucide="badge-check"></i><strong><?= (int)($knowledge['published'] ?? 0) ?></strong><span>Published</span></div>
                <div><i data-lucide="clock-3"></i><strong><?= (int)($knowledge['pending'] ?? 0) ?></strong><span>Awaiting review</span></div>
                <div><i data-lucide="package-check"></i><strong><?= (int)($equipment['active'] ?? 0) ?>/<?= (int)($equipment['total'] ?? 0) ?></strong><span>Active equipment</span></div>
            </div>
            <?php if ($searchGaps): ?><div class="gap-list"><h3>Searches with no result</h3><?php foreach ($searchGaps as $gap): ?><div><span><?= e($gap['query']) ?></span><b><?= (int)$gap['searches'] ?>x</b></div><?php endforeach; ?></div><?php endif; ?>
        </section>
    </div>
</div>

<style>
.stats-page{max-width:1500px;margin:0 auto}.stats-hero{align-items:center}.stats-range{display:flex;padding:4px;border:1px solid #dbe3ef;border-radius:8px;background:#fff}.stats-range a{padding:7px 12px;border-radius:6px;color:#64748b;font-size:12px;font-weight:700;text-decoration:none;white-space:nowrap}.stats-range a.active{background:#2563eb;color:#fff}.stats-kpis{display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:10px;margin-bottom:18px}.stats-kpi{display:flex;min-width:0;align-items:flex-start;gap:10px;padding:15px;border:1px solid #dbe3ef;border-radius:8px;background:#fff}.stats-kpi-icon{display:flex;width:36px;height:36px;flex:0 0 36px;align-items:center;justify-content:center;border-radius:7px}.stats-kpi-icon i{width:18px;height:18px}.stats-kpi div:last-child{min-width:0}.stats-kpi span,.stats-kpi small{display:block;color:#64748b;font-size:10.5px;font-weight:650}.stats-kpi strong{display:block;margin:2px 0;color:#111827;font-size:21px;line-height:1.15}.stats-kpi.blue .stats-kpi-icon{background:#eff6ff;color:#2563eb}.stats-kpi.green .stats-kpi-icon{background:#ecfdf5;color:#059669}.stats-kpi.red .stats-kpi-icon{background:#fef2f2;color:#dc2626}.stats-kpi.amber .stats-kpi-icon{background:#fffbeb;color:#d97706}.stats-kpi.violet .stats-kpi-icon{background:#f5f3ff;color:#7c3aed}.stats-kpi.orange .stats-kpi-icon{background:#fff7ed;color:#ea580c}.stats-main-grid{display:grid;grid-template-columns:minmax(0,1.65fr) minmax(300px,.75fr);gap:14px;margin-bottom:14px}.stats-list-grid,.stats-bottom-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px}.stats-panel{min-width:0;border:1px solid #dbe3ef;border-radius:8px;background:#fff}.stats-panel>header{display:flex;min-height:64px;align-items:center;justify-content:space-between;padding:14px 16px;border-bottom:1px solid #e8edf4}.stats-panel h2{color:#172033;font-size:14px;font-weight:750}.stats-panel header p{margin-top:2px;color:#8492a6;font-size:11px}.stats-legend{display:flex;gap:12px;color:#64748b;font-size:10px}.stats-legend span{display:flex;align-items:center;gap:5px}.stats-legend i{width:8px;height:8px;border-radius:2px}.stats-legend .total{background:#2563eb}.stats-legend .solved{background:#22c55e}.trend-chart{display:flex;height:250px;align-items:stretch;gap:3px;padding:20px 14px 12px}.trend-day{display:flex;min-width:0;flex:1;flex-direction:column;justify-content:flex-end}.trend-bars{display:flex;height:190px;align-items:flex-end;justify-content:center;gap:2px;border-bottom:1px solid #dbe3ef}.trend-bars i{width:min(9px,42%);min-height:2px;border-radius:2px 2px 0 0}.trend-bars .created{background:#2563eb}.trend-bars .done{background:#22c55e}.trend-day>span{height:24px;padding-top:7px;color:#94a3b8;font-size:8px;text-align:center;white-space:nowrap}.status-list{padding:10px 16px}.status-row{display:grid;grid-template-columns:1fr 42px;gap:6px;padding:8px 0}.status-row>div:first-child{display:flex;grid-column:1/-1;justify-content:space-between}.status-row span{color:#475569;font-size:12px;font-weight:650}.status-row strong,.status-row small{color:#64748b;font-size:11px}.meter{height:5px;overflow:hidden;border-radius:99px;background:#e8edf3}.meter i{display:block;height:100%;border-radius:inherit;background:#2563eb}.status-row .meter i{background:#94a3b8}.status-row .meter .status-solved{background:#16a34a}.status-row .meter .status-in_progress{background:#2563eb}.status-row .meter .status-new{background:#d97706}.status-row .meter .status-escalated,.status-row .meter .status-unsolved{background:#dc2626}.rank-list{padding:8px 16px 12px}.rank-row{display:grid;grid-template-columns:24px minmax(0,1fr) 64px;gap:9px;align-items:center;padding:9px 0;border-bottom:1px solid #edf1f6}.rank-row:last-child{border-bottom:0}.rank-row>b{display:flex;width:22px;height:22px;align-items:center;justify-content:center;border-radius:5px;background:#f1f5f9;color:#64748b;font-size:10px}.rank-row>div span{display:block;margin-bottom:6px;overflow:hidden;color:#334155;font-size:12px;font-weight:650;text-overflow:ellipsis;white-space:nowrap}.rank-row em{padding:2px 5px;border-radius:4px;background:#eef2f7;color:#64748b;font-size:9px;font-style:normal}.rank-row aside{text-align:right}.rank-row aside strong,.rank-row aside small{display:block}.rank-row aside strong{color:#172033;font-size:13px}.rank-row aside small{color:#94a3b8;font-size:9px}.device-list .meter i{background:#0d9488}.tech-table{padding:6px 16px 12px}.tech-head,.tech-row{display:grid;grid-template-columns:minmax(150px,1fr) 70px 70px 80px;gap:8px;align-items:center;padding:9px 4px}.tech-head{color:#94a3b8;font-size:9.5px;font-weight:700;text-transform:uppercase}.tech-row{border-top:1px solid #edf1f6;color:#475569;font-size:11.5px}.tech-row strong{overflow:hidden;color:#172033;text-overflow:ellipsis;white-space:nowrap}.health-grid{display:grid;grid-template-columns:1fr 1fr;gap:8px;padding:12px 16px}.health-grid>div{display:grid;grid-template-columns:28px 1fr;column-gap:8px;align-items:center;padding:10px;border:1px solid #edf1f6;border-radius:7px}.health-grid i{grid-row:1/3;width:18px;height:18px;color:#2563eb}.health-grid strong{color:#172033;font-size:17px}.health-grid span{color:#64748b;font-size:9.5px}.gap-list{margin:0 16px 14px;padding-top:10px;border-top:1px solid #edf1f6}.gap-list h3{margin-bottom:5px;color:#64748b;font-size:10px;text-transform:uppercase}.gap-list div{display:flex;justify-content:space-between;padding:5px 0;color:#475569;font-size:11px}.gap-list b{color:#d97706}.stats-empty{padding:32px 16px;color:#94a3b8;font-size:12px;text-align:center}.dark .stats-range,.dark .stats-kpi,.dark .stats-panel{background:#111827;border-color:#334155}.dark .stats-range{background:#0f172a}.dark .stats-kpi strong,.dark .stats-panel h2,.dark .rank-row aside strong,.dark .tech-row strong,.dark .health-grid strong{color:#f1f5f9}.dark .stats-panel>header,.dark .rank-row,.dark .tech-row,.dark .gap-list{border-color:#253247}.dark .rank-row>div span,.dark .status-row span{color:#cbd5e1}.dark .health-grid>div{border-color:#253247}@media(max-width:1200px){.stats-kpis{grid-template-columns:repeat(3,1fr)}}@media(max-width:900px){.stats-main-grid,.stats-list-grid,.stats-bottom-grid{grid-template-columns:1fr}.stats-range{overflow-x:auto}.stats-hero{align-items:flex-start;gap:12px;flex-direction:column}}@media(max-width:620px){.stats-kpis{grid-template-columns:1fr 1fr}.stats-kpi{padding:12px}.stats-kpi strong{font-size:18px}.trend-chart{height:220px;padding-left:8px;padding-right:8px}.trend-bars{height:160px}.tech-head,.tech-row{grid-template-columns:minmax(120px,1fr) 55px 55px}.tech-head span:last-child,.tech-row span:last-child{display:none}}@media(max-width:400px){.stats-kpis{grid-template-columns:1fr}}
</style>
<style>
/* Summary cards: grouped for faster operational scanning. */
.stats-page .stats-summary {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 14px;
    margin: 20px 0;
}
.stats-page .stats-summary .stats-kpi {
    display: flex;
    min-width: 0;
    min-height: 164px;
    align-items: stretch;
    flex-direction: column;
    gap: 18px;
    padding: 18px 20px;
    border: 1px solid #dbe4ef;
    border-radius: 14px;
    background: #fff;
    box-shadow: 0 5px 18px rgba(30,64,120,.055);
}
.stats-page .stats-summary .stats-kpi-group { grid-column: span 2; }
.stats-page .stats-kpi-head {
    display: flex;
    width: 100%;
    align-items: center;
    gap: 11px;
    padding-bottom: 13px;
    border-bottom: 1px solid #edf1f6;
}
.stats-page .stats-kpi-head .stats-kpi-icon {
    display: grid;
    width: 38px;
    height: 38px;
    flex: 0 0 38px;
    place-items: center;
    border-radius: 10px;
}
.stats-page .stats-kpi-head .stats-kpi-icon svg { width: 18px; height: 18px; }
.stats-page .stats-kpi-head .tone-blue { color: #2563eb; background: #edf4ff; }
.stats-page .stats-kpi-head .tone-red { color: #dc2626; background: #fff0f0; }
.stats-page .stats-kpi-head h2 {
    margin: 0;
    color: #26344b;
    font-size: 13px;
    font-weight: 800;
    letter-spacing: 0;
}
.stats-page .stats-kpi-head p { margin: 3px 0 0; color: #8795a9; font-size: 10px; }
.stats-page .stats-kpi-pair { display: grid; width: 100%; grid-template-columns: 1fr 1fr; gap: 0; }
.stats-page .stats-kpi-pair .stats-metric + .stats-metric { padding-left: 24px; border-left: 1px solid #e5ebf3; }
.stats-page .stats-metric { min-width: 0; }
.stats-page .stats-metric > span {
    display: block;
    color: #6b7b91;
    font-size: 10px;
    font-weight: 750;
    text-transform: uppercase;
}
.stats-page .stats-metric > strong {
    display: block;
    min-height: 34px;
    margin: 5px 0 2px;
    color: #172033;
    font-size: 28px;
    font-weight: 800;
    line-height: 1.1;
}
.stats-page .stats-metric > small { display: block; color: #8090a5; font-size: 10.5px; font-weight: 650; }
.stats-page .stats-metric.success > strong { color: #168447; }
.stats-page .stats-kpi.warning .stats-metric > strong { color: #c43b3b; }
.dark .stats-page .stats-summary .stats-kpi { background: #111827; border-color: #334155; }
.dark .stats-page .stats-kpi-head { border-color: #253247; }
.dark .stats-page .stats-kpi-head h2,.dark .stats-page .stats-metric > strong { color: #f1f5f9; }
.dark .stats-page .stats-kpi-pair .stats-metric + .stats-metric { border-color: #334155; }
.dark .stats-page .stats-kpi-head .tone-blue { color: #8fb7ff; background: #192a45; }
.dark .stats-page .stats-kpi-head .tone-red { color: #fca5a5; background: #351c24; }
.dark .stats-page .stats-metric.success > strong { color: #4ade80; }
.dark .stats-page .stats-kpi.warning .stats-metric > strong { color: #f87171; }
@media (max-width: 1050px) {
    .stats-page .stats-summary { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .stats-page .stats-summary .stats-kpi-group { grid-column: span 2; }
}
@media (max-width: 620px) {
    .stats-page .stats-summary { grid-template-columns: 1fr; }
    .stats-page .stats-summary .stats-kpi-group { grid-column: auto; }
    .stats-page .stats-summary .stats-kpi { min-height: 0; padding: 16px; }
    .stats-page .stats-kpi-pair .stats-metric + .stats-metric { padding-left: 16px; }
    .stats-page .stats-metric > strong { font-size: 24px; }
}
</style>
<?php require APP_ROOT . '/includes/layout_footer.php'; ?>
