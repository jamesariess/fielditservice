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

// The chart needs a round "nice" ceiling so the axis labels and gridlines mean something.
$chartCount = count($daily);
// Few days means wide columns: let the pair of bars grow instead of leaving two
// hairlines stranded in the middle of a broad column.
$barCap = $chartCount <= 10 ? 22 : ($chartCount <= 20 ? 15 : 11);
$chartPeak = (int)max(array_column($daily, 'total'));
$chartAvg = round(array_sum(array_column($daily, 'total')) / max(1, $chartDays), 1);
$maxDaily = chartCeiling($chartPeak);
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

/**
 * Session issue titles often hold several lines of free text. Collapse the whitespace
 * so a row reads as one sentence instead of a wall of joined fragments, and clip the
 * total length while keeping the full text for the hover title.
 */
function statLabel(string $text, int $limit = 96): string {
    $text = trim(preg_replace('/\s+/u', ' ', $text) ?? '');
    if ($text === '') return 'Unspecified';
    $length = function_exists('mb_strlen') ? mb_strlen($text) : strlen($text);
    if ($length <= $limit) return $text;
    $cut = function_exists('mb_substr') ? mb_substr($text, 0, $limit - 1) : substr($text, 0, $limit - 1);
    return rtrim($cut, " .,;:\t\n\r\0\x0B") . '…';
}

/** Solved-rate traffic light used by the problem and device lists. */
function statRateBand(int $rate): string { return $rate >= 70 ? 'good' : ($rate >= 40 ? 'mid' : 'low'); }

/**
 * Bars are drawn as a percentage of the axis ceiling, so the ceiling has to be a
 * round number that comfortably clears the busiest day. It is also kept even so
 * the mid gridline lands on a whole number and the axis reads 10 / 5 / 0 rather
 * than 15 / 8 / 0.
 */
function chartCeiling(int $peak): int {
    $step = $peak <= 4 ? 1 : ($peak <= 10 ? 2 : ($peak <= 25 ? 5 : ($peak <= 60 ? 10 : 20)));
    $ceiling = max($step, (int)(ceil($peak / $step) * $step));
    return $ceiling > 2 && $ceiling % 2 === 1 ? $ceiling + 1 : $ceiling;
}

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
                <a href="<?= e(statUrl($range)) ?>" class="<?= $days === $range ? 'active' : '' ?>"<?= $days === $range ? ' aria-current="page"' : '' ?>><?= $label ?></a>
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
                <div class="stats-metric">
                    <span>Sessions</span>
                    <strong><?= number_format($totalSessions) ?></strong>
                    <small>Created in this period</small>
                </div>
                <div class="stats-metric success">
                    <span>Solved rate</span>
                    <strong><?= $solvedRate ?>%</strong>
                    <small><?= number_format($solvedCount) ?> of <?= number_format($totalSessions) ?> solved</small>
                    <div class="meter kpi-meter" role="img" aria-label="Solved rate <?= $solvedRate ?> percent"><i style="width:<?= max(0, min(100, $solvedRate)) ?>%"></i></div>
                </div>
            </div>
        </article>

        <article class="stats-kpi stats-kpi-single warning">
            <header class="stats-kpi-head"><span class="stats-kpi-icon tone-red"><i data-lucide="triangle-alert"></i></span><div><h2>Escalation</h2><p>Needs attention</p></div></header>
            <div class="stats-metric"><span>Escalation rate</span><strong><?= $escalationRate ?>%</strong><small><?= number_format((int)($summary['escalated'] ?? 0)) ?> escalated</small></div>
        </article>

        <article class="stats-kpi stats-kpi-single">
            <header class="stats-kpi-head"><span class="stats-kpi-icon tone-green"><i data-lucide="timer"></i></span><div><h2>Resolution</h2><p>Completed work</p></div></header>
            <div class="stats-metric"><span>Average resolution</span><strong><?= $summary['avg_minutes'] ? (int)$summary['avg_minutes'] . ' min' : '—' ?></strong><small>Based on solved jobs under 8 hours</small></div>
        </article>

        <article class="stats-kpi stats-kpi-single">
            <header class="stats-kpi-head"><span class="stats-kpi-icon tone-violet"><i data-lucide="users"></i></span><div><h2>Team availability</h2><p>Current workload</p></div></header>
            <div class="stats-metric"><span>Active users</span><strong><?= number_format($activeUsers) ?></strong><small><?= number_format((int)($summary['in_progress'] ?? 0)) ?> jobs in progress</small></div>
        </article>

        <article class="stats-kpi stats-kpi-single">
            <header class="stats-kpi-head"><span class="stats-kpi-icon tone-amber"><i data-lucide="sparkles"></i></span><div><h2>AI activity</h2><p>Support assistant</p></div></header>
            <div class="stats-metric"><span>Conversations</span><strong><?= number_format((int)($aiSummary['conversations'] ?? 0)) ?></strong><small><?= number_format((int)($aiSummary['messages'] ?? 0)) ?> messages</small></div>
        </article>
    </section>

    <div class="stats-main-grid">
        <section class="stats-panel stats-trend">
            <header>
                <div><h2>Daily Workload</h2><p>Sessions created and solved over the last <?= $chartDays ?> days</p></div>
                <div class="stats-trend-aside">
                    <div class="trend-stat"><b><?= $chartPeak ?></b><span>busiest day</span></div>
                    <div class="trend-stat"><b><?= number_format($chartAvg, 1) ?></b><span>avg / day</span></div>
                    <div class="stats-legend"><span><i class="total"></i>Created</span><span><i class="solved"></i>Solved</span></div>
                </div>
            </header>
            <?php if ($chartPeak === 0): ?>
                <div class="stats-empty"><i data-lucide="bar-chart-3"></i><span>No sessions were created in the last <?= $chartDays ?> days.</span></div>
            <?php else: ?>
            <div class="trend-wrap" style="--trend-days:<?= $chartCount ?>;--trend-bar:<?= $barCap ?>px">
                <div class="trend-axis" aria-hidden="true"><span><?= $maxDaily ?></span><span><?= (int)round($maxDaily / 2) ?></span><span>0</span></div>
                <div class="trend-scroll">
                <div class="trend-chart" role="img" aria-label="Sessions created and solved per day for the last <?= $chartDays ?> days">
                    <?php foreach ($daily as $index => $row): ?>
                        <?php $showLabel = $chartDays <= 14 || $index % 3 === 0 || $index === count($daily) - 1; ?>
                        <div class="trend-day<?= $row['total'] === 0 ? ' is-zero' : '' ?>" title="<?= e(date('M j', strtotime($row['day']))) ?>: <?= $row['total'] ?> created, <?= $row['solved'] ?> solved">
                            <div class="trend-bars">
                                <?php if ($row['total'] > 0): ?><i class="created" style="height:<?= max(3, round($row['total'] / $maxDaily * 100)) ?>%"></i><?php endif; ?>
                                <?php if ($row['solved'] > 0): ?><i class="done" style="height:<?= max(3, round($row['solved'] / $maxDaily * 100)) ?>%"></i><?php endif; ?>
                            </div>
                            <span class="trend-day-label"><?= $showLabel ? e(date('M j', strtotime($row['day']))) : '' ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
                </div>
            </div>
            <?php endif; ?>
        </section>

        <section class="stats-panel">
            <header><div><h2>Work Status</h2><p>Current distribution for <?= strtolower($rangeLabel) ?></p></div></header>
            <div class="status-list">
                <?php foreach ($statusRows as $row): $pct = $totalSessions ? round($row['total'] / $totalSessions * 100) : 0; ?>
                    <div class="status-row">
                        <div class="status-top"><span class="status-name"><i class="status-<?= e($row['status']) ?>"></i><?= e(statusLabel($row['status'])) ?></span><strong><?= (int)$row['total'] ?></strong></div>
                        <div class="status-meter"><div class="meter"><i class="status-<?= e($row['status']) ?>" style="width:<?= $pct ?>%"></i></div><small><?= $pct ?>%</small></div>
                    </div>
                <?php endforeach; ?>
                <?php if (!$statusRows): ?><div class="stats-empty"><i data-lucide="inbox"></i><span>No sessions in this date range.</span></div><?php else: ?>
                    <div class="status-total"><span>Total sessions</span><b><?= number_format($totalSessions) ?></b></div>
                <?php endif; ?>
            </div>
        </section>
    </div>

    <div class="stats-list-grid">
        <section class="stats-panel">
            <header><div><h2>Most Common Problems</h2><p>Bar length is volume, badge is the solved rate</p></div></header>
            <div class="rank-list">
                <?php foreach ($topIssues as $index => $row): $rate = $row['total'] ? round($row['solved'] / $row['total'] * 100) : 0; ?>
                    <div class="rank-row">
                        <b class="<?= $index < 3 ? 'is-top' : '' ?>"><?= $index + 1 ?></b>
                        <div class="rank-main">
                            <span class="rank-label" title="<?= e(statLabel((string)$row['label'], 400)) ?>"><?= e(statLabel((string)$row['label'])) ?></span>
                            <div class="meter"><i style="width:<?= round($row['total'] / $maxIssue * 100) ?>%"></i></div>
                        </div>
                        <aside>
                            <strong><?= (int)$row['total'] ?></strong>
                            <small class="rate <?= statRateBand($rate) ?>"><?= $rate ?>% solved</small>
                        </aside>
                    </div>
                <?php endforeach; ?>
                <?php if (!$topIssues): ?><div class="stats-empty"><i data-lucide="inbox"></i><span>No problem data yet.</span></div><?php endif; ?>
            </div>
        </section>

        <section class="stats-panel">
            <header><div><h2>Most Serviced Devices</h2><p>Models receiving the most field work</p></div></header>
            <div class="rank-list device-list">
                <?php foreach ($topDevices as $index => $row): $untyped = strcasecmp((string)$row['device_type'], 'Other') === 0; ?>
                    <div class="rank-row<?= $untyped ? ' is-untyped' : '' ?>">
                        <b class="<?= $index < 3 ? 'is-top' : '' ?>"><?= $index + 1 ?></b>
                        <div class="rank-main">
                            <span class="rank-label" title="<?= e(statLabel((string)$row['label'], 400)) ?>"><?= e(statLabel((string)$row['label'])) ?><?php if (!$untyped): ?> <em><?= e($row['device_type']) ?></em><?php endif; ?></span>
                            <div class="meter"><i style="width:<?= round($row['total'] / $maxDevice * 100) ?>%"></i></div>
                        </div>
                        <aside><strong><?= (int)$row['total'] ?></strong><small>sessions</small></aside>
                    </div>
                <?php endforeach; ?>
                <?php if (!$topDevices): ?><div class="stats-empty"><i data-lucide="inbox"></i><span>No device data yet.</span></div><?php endif; ?>
            </div>
        </section>
    </div>

    <div class="stats-bottom-grid">
        <section class="stats-panel">
            <header><div><h2>Technician Activity</h2><p>Completed workload by assigned technician</p></div></header>
            <div class="tech-table">
                <div class="tech-head"><span>Technician</span><span>Sessions</span><span>Solved</span><span>Avg time</span></div>
                <?php foreach ($technicians as $tech): ?>
                    <div class="tech-row">
                        <strong title="<?= e($tech['full_name']) ?>"><?= e($tech['full_name']) ?></strong>
                        <span><?= (int)$tech['total'] ?></span>
                        <span class="tech-solved"><?= (int)$tech['solved'] ?></span>
                        <span><?= $tech['avg_minutes'] ? (int)$tech['avg_minutes'] . ' min' : '—' ?></span>
                    </div>
                <?php endforeach; ?>
                <?php if (!$technicians): ?><div class="stats-empty"><i data-lucide="inbox"></i><span>No technician activity yet.</span></div><?php endif; ?>
            </div>
        </section>

        <section class="stats-panel health-panel">
            <header><div><h2>Content &amp; Assets</h2><p>System readiness outside active tickets</p></div></header>
            <div class="health-grid">
                <div><span class="health-ico"><i data-lucide="book-open"></i></span><strong><?= (int)($knowledge['total'] ?? 0) ?></strong><span>Knowledge articles</span></div>
                <div><span class="health-ico"><i data-lucide="badge-check"></i></span><strong><?= (int)($knowledge['published'] ?? 0) ?></strong><span>Published</span></div>
                <div><span class="health-ico"><i data-lucide="clock-3"></i></span><strong><?= (int)($knowledge['pending'] ?? 0) ?></strong><span>Awaiting review</span></div>
                <div><span class="health-ico"><i data-lucide="package-check"></i></span><strong><?= (int)($equipment['active'] ?? 0) ?><small>/<?= (int)($equipment['total'] ?? 0) ?></small></strong><span>Active equipment</span></div>
            </div>
            <?php if ($searchGaps): ?><div class="gap-list"><h3>Searches with no result</h3><?php foreach ($searchGaps as $gap): ?><div><span><?= e($gap['query']) ?></span><b><?= (int)$gap['searches'] ?>x</b></div><?php endforeach; ?></div><?php endif; ?>
        </section>
    </div>
</div>

<script>
// On a phone a 30-day plot is wider than the screen, so open it on the most
// recent days rather than the oldest ones the user has to swipe past.
(function () {
    var scroller = document.querySelector('.stats-page .trend-scroll');
    if (!scroller) return;
    function showLatest() {
        if (scroller.scrollWidth > scroller.clientWidth + 1) scroller.scrollLeft = scroller.scrollWidth;
    }
    showLatest();
    window.addEventListener('load', showLatest);
    window.addEventListener('resize', showLatest);
})();
</script>

<style>
/* ===========================================================================
   Statistics workspace — the single source of truth for this page's skin.
   The two previous blocks (a 6-column KPI grid plus a later grouped-summary
   override) fought each other, which is why card sizes and type ran
   inconsistent. Everything now lives here, scoped to .stats-page.
   =========================================================================== */
.stats-page{
    --st-ink:#172033;
    --st-muted:#5d6c82;
    --st-faint:#8494a9;
    --st-line:#dfe7f1;
    --st-line-soft:#edf1f7;
    --st-card:#fff;
    --st-sunken:#f7fafd;
    --trend-h:230px;
    --trend-label:26px;
    /* Minimum column pitch (one day plus the gap between days). Phones set it so
       a 30-day plot stays legible and scrolls instead of crushing into hairlines. */
    --trend-col:0px;
    --trend-bar:11px;
    max-width:1500px;
    margin:0 auto;
    color:var(--st-ink);
}
.dark .stats-page{
    --st-ink:#f1f5f9;
    --st-muted:#a8b6c8;
    --st-faint:#7d8ca3;
    --st-line:#33415a;
    --st-line-soft:#27334a;
    --st-card:#111827;
    --st-sunken:#162234;
}

/* ---- Hero + range switch ---- */
.stats-page .stats-hero{align-items:center;gap:16px}
.stats-page .stats-range{display:flex;gap:2px;padding:4px;border:1px solid var(--st-line);border-radius:13px;background:var(--st-card);box-shadow:0 2px 10px rgba(30,64,120,.05)}
.stats-page .stats-range a{padding:8px 14px;border-radius:9px;color:var(--st-muted);font-size:12.5px;font-weight:700;text-decoration:none;white-space:nowrap;transition:background .15s,color .15s}
.stats-page .stats-range a:hover{background:#eef4ff;color:#1d4ed8}
.stats-page .stats-range a.active{background:#2563eb;color:#fff;box-shadow:0 4px 12px rgba(37,99,235,.3)}
.dark .stats-page .stats-range a:hover{background:#1b2b44;color:#9fc0ff}

/* ---- Summary cards ---- */
.stats-page .stats-summary{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px;margin:22px 0}
.stats-page .stats-summary .stats-kpi{display:flex;min-width:0;flex-direction:column;gap:16px;padding:18px 20px;border:1px solid var(--st-line);border-radius:16px;background:var(--st-card);box-shadow:0 6px 20px rgba(30,64,120,.06)}
.stats-page .stats-kpi-group{grid-column:span 2}
.stats-page .stats-kpi-head{display:flex;align-items:center;gap:12px;padding-bottom:14px;border-bottom:1px solid var(--st-line-soft)}
.stats-page .stats-kpi-icon{display:grid;width:40px;height:40px;flex:0 0 40px;place-items:center;border-radius:12px}
.stats-page .stats-kpi-icon svg{width:19px;height:19px}
.stats-page .tone-blue{color:#2563eb;background:#eaf2ff}
.stats-page .tone-green{color:#0f8a4d;background:#e7f8ee}
.stats-page .tone-amber{color:#b4760c;background:#fff4e2}
.stats-page .tone-red{color:#d62828;background:#fdecec}
.stats-page .tone-violet{color:#7c3aed;background:#f2edff}
.stats-page .stats-kpi-head h2{margin:0;font-size:13.5px;font-weight:800;letter-spacing:-.01em}
.stats-page .stats-kpi-head p{margin:3px 0 0;color:var(--st-faint);font-size:11px;font-weight:650}
.stats-page .stats-kpi-pair{display:grid;grid-template-columns:1fr 1fr;align-items:stretch}
.stats-page .stats-kpi-pair .stats-metric+.stats-metric{padding-left:22px;border-left:1px solid var(--st-line-soft)}
.stats-page .stats-metric{display:flex;min-width:0;flex-direction:column}
.stats-page .stats-metric>span{display:block;color:var(--st-faint);font-size:10.5px;font-weight:800;letter-spacing:.05em;text-transform:uppercase}
.stats-page .stats-metric>strong{display:block;margin:6px 0 3px;font-size:30px;font-weight:800;line-height:1.05;letter-spacing:-.02em;font-variant-numeric:tabular-nums}
.stats-page .stats-metric>small{display:block;color:var(--st-muted);font-size:11.5px;font-weight:650}
.stats-page .stats-metric.success>strong{color:#0f8a4d}
.stats-page .stats-kpi.warning .stats-metric>strong{color:#cf3535}
/* Spacing lives in the margin: padding inside an 8px border-box would collapse
   the bar's own height to nothing. */
.stats-page .stats-metric>.kpi-meter{margin-top:12px}
.stats-page .meter{height:8px;overflow:hidden;border-radius:99px;background:#e7eef7}
.stats-page .meter>i{display:block;height:100%;border-radius:inherit;background:linear-gradient(90deg,#5b9bf8,#2563eb);transition:width .35s ease}
.stats-page .kpi-meter>i{background:linear-gradient(90deg,#34d399,#0f8a4d)}
.dark .stats-page .meter{background:#25334a}
.dark .stats-page .tone-blue{color:#8fb7ff;background:#1a2c48}
.dark .stats-page .tone-green{color:#5ed99b;background:#12301f}
.dark .stats-page .tone-amber{color:#f2c26b;background:#33260f}
.dark .stats-page .tone-red{color:#fca5a5;background:#351c24}
.dark .stats-page .tone-violet{color:#c4b5fd;background:#241d42}
.dark .stats-page .stats-metric.success>strong{color:#4ade80}
.dark .stats-page .stats-kpi.warning .stats-metric>strong{color:#f87171}

/* ---- Panels ---- */
.stats-page .stats-panel{min-width:0;overflow:hidden;border:1px solid var(--st-line);border-radius:16px;background:var(--st-card);box-shadow:0 6px 20px rgba(30,64,120,.055)}
.stats-page .stats-panel>header{display:flex;min-height:68px;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:12px;padding:16px 20px;border-bottom:1px solid var(--st-line-soft)}
.stats-page .stats-panel h2{margin:0;font-size:15px;font-weight:800;letter-spacing:-.01em}
.stats-page .stats-panel header p{margin:3px 0 0;color:var(--st-faint);font-size:11.5px;font-weight:650}
.stats-page .stats-main-grid{display:grid;grid-template-columns:minmax(0,1.7fr) minmax(320px,.85fr);gap:16px;margin-bottom:16px}
.stats-page .stats-list-grid,.stats-page .stats-bottom-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px;margin-bottom:16px}

/* ---- Trend chart ---- */
.stats-page .stats-trend-aside{display:flex;align-items:center;gap:18px;flex-wrap:wrap}
.stats-page .trend-stat{display:flex;flex-direction:column;line-height:1.15}
.stats-page .trend-stat b{font-size:15px;font-weight:800;font-variant-numeric:tabular-nums}
.stats-page .trend-stat span{color:var(--st-faint);font-size:9.5px;font-weight:800;letter-spacing:.05em;text-transform:uppercase}
.stats-page .stats-legend{display:flex;align-items:center;gap:14px;color:var(--st-muted);font-size:11px;font-weight:700}
.stats-page .stats-legend span{display:flex;align-items:center;gap:6px}
.stats-page .stats-legend i{width:9px;height:9px;border-radius:3px}
.stats-page .stats-legend .total{background:#2563eb}
.stats-page .stats-legend .solved{background:#22c55e}
.stats-page .trend-wrap{display:grid;grid-template-columns:auto minmax(0,1fr);gap:10px;padding:18px 20px 14px}
.stats-page .trend-axis{display:flex;height:calc(var(--trend-h) + var(--trend-label));flex-direction:column;justify-content:space-between;padding-bottom:var(--trend-label);color:var(--st-faint);font-size:10.5px;font-weight:700;font-variant-numeric:tabular-nums;text-align:right}
.stats-page .trend-scroll{min-width:0;overflow-x:auto;overflow-y:hidden;overscroll-behavior-x:contain;scrollbar-width:thin;scrollbar-color:#d3dded transparent}
.stats-page .trend-scroll::-webkit-scrollbar{height:6px}
.stats-page .trend-scroll::-webkit-scrollbar-thumb{border-radius:99px;background:#d3dded}
.dark .stats-page .trend-scroll{scrollbar-color:#38465e transparent}
.dark .stats-page .trend-scroll::-webkit-scrollbar-thumb{background:#38465e}
.stats-page .trend-chart{position:relative;display:flex;min-width:calc(var(--trend-days,30) * var(--trend-col));height:calc(var(--trend-h) + var(--trend-label));align-items:stretch;gap:3px}
.stats-page .trend-chart::before{content:"";position:absolute;left:0;right:0;top:0;bottom:var(--trend-label);background-image:repeating-linear-gradient(to bottom,var(--st-line-soft) 0 1px,transparent 1px 25%);pointer-events:none}
.stats-page .trend-day{position:relative;display:flex;min-width:0;flex:1;flex-direction:column;justify-content:flex-end;border-radius:9px 9px 0 0;transition:background .15s}
.stats-page .trend-day:hover{background:rgba(37,99,235,.06)}
.stats-page .trend-bars{display:flex;height:var(--trend-h);align-items:flex-end;justify-content:center;gap:3px;border-bottom:1px solid var(--st-line)}
.stats-page .trend-bars>i{width:min(var(--trend-bar,11px),44%);min-height:3px;border-radius:4px 4px 0 0}
.stats-page .trend-bars>.created{background:linear-gradient(180deg,#4c8dfb,#2563eb)}
.stats-page .trend-bars>.done{background:linear-gradient(180deg,#4ade80,#16a34a)}
.stats-page .trend-day.is-zero .trend-bars{border-bottom-color:transparent}
.stats-page .trend-day.is-zero .trend-bars::after{content:"";width:min(var(--trend-bar,11px),44%);height:2px;border-radius:2px;background:#dbe4f0}
.dark .stats-page .trend-day.is-zero .trend-bars::after{background:#2c3a52}
.stats-page .trend-day-label{height:var(--trend-label);padding-top:8px;color:var(--st-faint);font-size:10.5px;font-weight:650;font-variant-numeric:tabular-nums;text-align:center;white-space:nowrap}

/* ---- Work status ---- */
.stats-page .status-list{display:grid;gap:15px;padding:16px 20px 18px}
.stats-page .status-row{display:grid;gap:8px}
.stats-page .status-top{display:flex;align-items:baseline;justify-content:space-between;gap:10px}
.stats-page .status-name{display:flex;align-items:center;gap:8px;color:var(--st-ink);font-size:12.5px;font-weight:700}
.stats-page .status-name>i{width:8px;height:8px;flex:0 0 8px;border-radius:99px;background:#94a3b8}
.stats-page .status-top>strong{font-size:15px;font-weight:800;font-variant-numeric:tabular-nums}
.stats-page .status-meter{display:grid;grid-template-columns:minmax(0,1fr) 40px;gap:10px;align-items:center}
.stats-page .status-meter>small{color:var(--st-faint);font-size:11px;font-weight:700;font-variant-numeric:tabular-nums;text-align:right}
.stats-page .status-name>i.status-unsolved,.stats-page .meter>i.status-unsolved{background:#dc2626}
.stats-page .status-name>i.status-solved,.stats-page .meter>i.status-solved{background:#16a34a}
.stats-page .status-name>i.status-in_progress,.stats-page .meter>i.status-in_progress{background:#2563eb}
.stats-page .status-name>i.status-new,.stats-page .meter>i.status-new{background:#d97706}
.stats-page .status-name>i.status-escalated,.stats-page .meter>i.status-escalated{background:#e11d48}
.stats-page .status-name>i.status-cancelled,.stats-page .meter>i.status-cancelled{background:#64748b}
.stats-page .meter>i.status-solved{background:linear-gradient(90deg,#4ade80,#16a34a)}
.stats-page .meter>i.status-unsolved{background:linear-gradient(90deg,#f87171,#dc2626)}
.stats-page .meter>i.status-escalated{background:linear-gradient(90deg,#fb7185,#e11d48)}
.stats-page .meter>i.status-new{background:linear-gradient(90deg,#fbbf24,#d97706)}
.stats-page .status-total{display:flex;justify-content:space-between;padding-top:13px;border-top:1px solid var(--st-line-soft);color:var(--st-muted);font-size:12px;font-weight:700}
.stats-page .status-total b{color:var(--st-ink);font-variant-numeric:tabular-nums}

/* ---- Ranked lists ---- */
.stats-page .rank-list{display:grid;padding:10px 20px 16px}
.stats-page .rank-row{display:grid;grid-template-columns:26px minmax(0,1fr) auto;gap:12px;align-items:center;padding:11px 0;border-bottom:1px solid var(--st-line-soft)}
.stats-page .rank-row:last-child{border-bottom:0}
.stats-page .rank-row>b{display:grid;width:24px;height:24px;place-items:center;border-radius:8px;background:#f1f5f9;color:#64748b;font-size:11px;font-weight:800}
.stats-page .rank-row>b.is-top{background:#eaf2ff;color:#1d4ed8}
.dark .stats-page .rank-row>b{background:#1d2a40;color:#94a3b8}
.dark .stats-page .rank-row>b.is-top{background:#1a2c48;color:#8fb7ff}
.stats-page .rank-main{min-width:0}
.stats-page .rank-label{display:-webkit-box;overflow:hidden;overflow-wrap:anywhere;color:var(--st-ink);font-size:12.5px;font-weight:700;line-height:1.4;-webkit-box-orient:vertical;-webkit-line-clamp:2}
.stats-page .rank-label em{margin-left:4px;padding:2px 6px;border-radius:5px;background:#eef2f7;color:#5d6c82;font-size:9.5px;font-weight:800;font-style:normal;letter-spacing:.02em;white-space:nowrap}
.dark .stats-page .rank-label em{background:#1d2a40;color:#9fb0c6}
.stats-page .rank-main .meter{margin-top:8px}
.stats-page .device-list .meter>i{background:linear-gradient(90deg,#2dd4bf,#0d9488)}
.stats-page .rank-row.is-untyped .rank-label{color:var(--st-muted)}
.stats-page .rank-row aside{display:flex;min-width:74px;flex-direction:column;align-items:flex-end;gap:5px}
.stats-page .rank-row aside>strong{font-size:15px;font-weight:800;font-variant-numeric:tabular-nums}
.stats-page .rank-row aside>small{color:var(--st-faint);font-size:10.5px;font-weight:650;white-space:nowrap}
.stats-page .rank-row aside>.rate{padding:3px 8px;border-radius:99px;font-weight:800}
.stats-page .rate.good{background:#e7f8ee;color:#0f7a43}
.stats-page .rate.mid{background:#fff4e2;color:#a96a05}
.stats-page .rate.low{background:#fdecec;color:#c43b3b}
.dark .stats-page .rate.good{background:#12301f;color:#5ed99b}
.dark .stats-page .rate.mid{background:#33260f;color:#f2c26b}
.dark .stats-page .rate.low{background:#351c24;color:#fca5a5}

/* ---- Technician table ---- */
.stats-page .tech-table{padding:8px 20px 14px}
.stats-page .tech-head,.stats-page .tech-row{display:grid;grid-template-columns:minmax(0,1.4fr) 80px 80px 90px;gap:10px;align-items:center;padding:11px 6px}
.stats-page .tech-head{color:var(--st-faint);font-size:10.5px;font-weight:800;letter-spacing:.06em;text-transform:uppercase}
.stats-page .tech-head>span:not(:first-child),.stats-page .tech-row>span{text-align:right}
.stats-page .tech-row{border-top:1px solid var(--st-line-soft);color:var(--st-muted);font-size:12.5px}
.stats-page .tech-row:hover{background:var(--st-sunken)}
.stats-page .tech-row>strong{overflow:hidden;color:var(--st-ink);font-size:12.5px;font-weight:700;text-overflow:ellipsis;white-space:nowrap}
.stats-page .tech-row>span{font-variant-numeric:tabular-nums}
.stats-page .tech-row>.tech-solved{color:#0f7a43;font-weight:800}
.dark .stats-page .tech-row>.tech-solved{color:#5ed99b}

/* ---- Content & assets ---- */
.stats-page .health-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px;padding:16px 20px 6px}
.stats-page .health-grid>div{display:grid;grid-template-columns:36px minmax(0,1fr);align-items:center;column-gap:11px;padding:12px;border:1px solid var(--st-line-soft);border-radius:12px;background:var(--st-sunken)}
.stats-page .health-ico{display:grid;width:36px;height:36px;grid-row:1/3;place-items:center;border-radius:10px;background:#eaf2ff;color:#2563eb}
.stats-page .health-ico svg{width:18px;height:18px}
.stats-page .health-grid>div:nth-child(2) .health-ico{background:#e7f8ee;color:#0f8a4d}
.stats-page .health-grid>div:nth-child(3) .health-ico{background:#fff4e2;color:#b4760c}
.stats-page .health-grid>div:nth-child(4) .health-ico{background:#f2edff;color:#7c3aed}
.dark .stats-page .health-ico{background:#1a2c48;color:#8fb7ff}
.dark .stats-page .health-grid>div:nth-child(2) .health-ico{background:#12301f;color:#5ed99b}
.dark .stats-page .health-grid>div:nth-child(3) .health-ico{background:#33260f;color:#f2c26b}
.dark .stats-page .health-grid>div:nth-child(4) .health-ico{background:#241d42;color:#c4b5fd}
.stats-page .health-grid strong{color:var(--st-ink);font-size:17px;font-weight:800;letter-spacing:-.01em;font-variant-numeric:tabular-nums}
.stats-page .health-grid strong>small{color:var(--st-faint);font-size:12px;font-weight:700}
.stats-page .health-grid>div>span:last-child{color:var(--st-muted);font-size:11px;font-weight:650}
.stats-page .gap-list{margin:14px 20px 18px;padding-top:14px;border-top:1px solid var(--st-line-soft)}
.stats-page .gap-list h3{margin:0 0 8px;color:var(--st-faint);font-size:10.5px;font-weight:800;letter-spacing:.06em;text-transform:uppercase}
.stats-page .gap-list>div{display:flex;justify-content:space-between;gap:12px;padding:7px 0;color:var(--st-muted);font-size:12px}
.stats-page .gap-list>div>span{overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.stats-page .gap-list b{color:#b4760c;font-variant-numeric:tabular-nums}

/* ---- Empty states ---- */
.stats-page .stats-empty{display:grid;place-items:center;gap:8px;padding:40px 20px;color:var(--st-faint);font-size:12.5px;text-align:center}
.stats-page .stats-empty svg{width:22px;height:22px;color:#b9c6d8}
.dark .stats-page .stats-empty svg{color:#4c5c76}

/* ---- Responsive ---- */
@media(max-width:1180px){
    .stats-page .stats-summary{grid-template-columns:repeat(2,minmax(0,1fr))}
    .stats-page .stats-main-grid{grid-template-columns:1fr}
}
@media(max-width:900px){
    .stats-page .stats-list-grid,.stats-page .stats-bottom-grid{grid-template-columns:1fr}
    .stats-page .stats-hero{flex-direction:column;align-items:flex-start;gap:14px}
    .stats-page .stats-range{max-width:100%;overflow-x:auto;scrollbar-width:none}
    .stats-page .stats-range::-webkit-scrollbar{display:none}
}
@media(max-width:700px){
    /* 20px pitch leaves a 17px column: two ~7px bars, readable on a phone. */
    .stats-page{--trend-h:180px;--trend-label:24px;--trend-col:20px}
    .stats-page .stats-summary{gap:12px;margin:18px 0}
    .stats-page .stats-panel>header{padding:14px 16px}
    .stats-page .trend-wrap{gap:8px;padding:16px 14px 12px}
    /* Two bars share a narrow column: a tighter gap leaves them visibly fatter. */
    .stats-page .trend-bars{gap:2px}
    .stats-page .trend-day-label{font-size:9.5px}
    .stats-page .status-list,.stats-page .rank-list,.stats-page .tech-table,.stats-page .health-grid{padding-left:16px;padding-right:16px}
    .stats-page .gap-list{margin-left:16px;margin-right:16px}
}
@media(max-width:620px){
    .stats-page .stats-summary{grid-template-columns:1fr}
    .stats-page .stats-kpi-group{grid-column:auto}
    .stats-page .stats-summary .stats-kpi{gap:14px;padding:16px}
    .stats-page .stats-metric>strong{font-size:26px}
    .stats-page .stats-kpi-pair .stats-metric+.stats-metric{padding-left:16px}
    .stats-page .tech-head,.stats-page .tech-row{grid-template-columns:minmax(0,1fr) 56px 60px}
    .stats-page .tech-head>span:last-child,.stats-page .tech-row>span:last-child{display:none}
    .stats-page .stats-trend-aside{gap:14px}
}
@media(max-width:430px){
    .stats-page{--trend-label:22px}
    .stats-page .stats-range a{padding:7px 11px;font-size:12px}
    .stats-page .trend-axis{font-size:9.5px}
    .stats-page .trend-day-label{font-size:9px;padding-top:6px}
    .stats-page .rank-row{grid-template-columns:22px minmax(0,1fr) auto;gap:9px}
    .stats-page .rank-row aside{min-width:62px}
    .stats-page .stats-panel>header{min-height:0}
}
@media(max-width:379px){
    /* Only stack the content tiles once two of them stop having room to breathe. */
    .stats-page .health-grid{grid-template-columns:1fr}
}
</style>
<?php require APP_ROOT . '/includes/layout_footer.php'; ?>
