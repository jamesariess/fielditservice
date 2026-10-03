<?php
/**
 * Statistics page layout check.
 *
 * The stats page is styled by one inline <style> block in
 * public/pages/admin/statistics.php plus the shared stylesheets. The page itself
 * needs a login and a database, so this harness reads the styles and markup
 * pieces straight out of statistics.php at run time (a copy would drift and stop
 * proving anything) and measures the rendered result in a browser.
 *
 * Run it with the built-in server from the project root:
 *     php -S 127.0.0.1:8123 -t .
 *     open http://127.0.0.1:8123/tests/statistics-layout.php
 * The checks re-run on load and on resize, so narrowing the window (or using the
 * device toolbar) re-verifies the phone layout. Every line under CHECKS must
 * start with PASS. The same list is exposed as window.__statsChecks.
 */

$source = @file_get_contents(dirname(__DIR__) . '/public/pages/admin/statistics.php');
$inlineCss = '';
if ($source !== false && preg_match_all('#<style>(.*?)</style>#s', $source, $m)) {
    $inlineCss = implode("\n", $m[1]);
}

// Does the page still carry the duplicated style systems this redesign removed?
$styleBlocks = $source !== false ? preg_match_all('#<style>#', $source) : 0;
$legacyGrid = $source !== false && strpos($source, 'grid-template-columns:repeat(6,minmax(0,1fr))') !== false;

// Sample series with the exact shape of the real data: a busy couple of days and
// long stretches of nothing, which is what made the old chart look empty.
$created = [10, 0, 4, 0, 0, 0, 0, 2, 2, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 8, 6, 2, 0, 0, 0, 0, 0, 0, 1, 2];
$solved  = [1, 0, 0, 0, 0, 0, 0, 2, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 5, 4, 0, 0, 0, 0, 0, 0, 0, 0, 0];
$chartPeak = max($created);
// Reuse the page's own ceiling function so the sample chart cannot drift from
// the real one (tests/statistics-labels.php covers its behaviour).
if ($source !== false && preg_match('/function chartCeiling\(int \$peak\): int \{.*?\n\}/s', $source, $ceilingMatch)) {
    eval($ceilingMatch[0]);
}
$axisMax = function_exists('chartCeiling') ? chartCeiling($chartPeak) : $chartPeak;
$chartAvg = round(array_sum($created) / count($created), 1);
$daily = [];
foreach ($created as $i => $count) {
    $daily[] = [
        'day' => date('Y-m-d', strtotime('-' . (count($created) - 1 - $i) . ' days')),
        'total' => $count,
        'solved' => $solved[$i],
    ];
}

$longIssue = 'i Unable to power on i Unable to power on ih Upon checking unit has no any sign if For Onsite Checking of power supply unit';
$topIssues = [
    ['label' => $longIssue, 'total' => 18, 'solved' => 6],
    ['label' => 'Microphone Not Working', 'total' => 2, 'solved' => 2],
    ['label' => 'Computer Overheating', 'total' => 1, 'solved' => 1],
    ['label' => 'Paper Jam', 'total' => 1, 'solved' => 0],
];
$topDevices = [
    ['label' => 'Unspecified device', 'device_type' => 'Other', 'total' => 17],
    ['label' => 'OptiPlex 7010 SFF', 'device_type' => 'Desktop', 'total' => 2],
    ['label' => 'Latitude 5520', 'device_type' => 'Laptop', 'total' => 1],
];
$statusRows = [
    ['status' => 'unsolved', 'total' => 14],
    ['status' => 'solved', 'total' => 12],
    ['status' => 'in_progress', 'total' => 3],
    ['status' => 'escalated', 'total' => 1],
];
$maxIssue = 18;
$maxDevice = 17;
$totalSessions = 30;
$solvedRate = 40;
$solvedCount = 12;
$escalationRate = 3;
$activeUsers = 4;
$chartDays = 30;
$rangeLabel = 'Last 30 days';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Statistics layout check</title>
<link rel="stylesheet" href="../public/assets/css/app.css">
<link rel="stylesheet" href="../public/assets/css/ui-refresh.css">
<!-- Real page order: stylesheets first, then the page's own inline block. -->
<link rel="stylesheet" href="../public/assets/css/workspace-refresh.css">
<style>
<?= $inlineCss ?>
body { background: #eef2f7; margin: 0; }
#checks { position: fixed; left: 8px; bottom: 8px; z-index: 99999; max-width: 380px; max-height: 46vh; overflow: auto; padding: 8px 10px; border-radius: 8px; background: #0f172a; color: #cbd5e1; font: 11px/1.5 ui-monospace, monospace; white-space: pre-wrap; }
</style>
</head>
<body>
<div class="stats-page">
    <div class="page-hero fx-reveal stats-hero">
        <div style="display:flex;align-items:center;gap:14px;">
            <div class="page-hero-ico cyan"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0891b2" stroke-width="2"><path d="M3 3v18h18"/></svg></div>
            <div><h1 class="page-hero-title">Statistics</h1><p class="page-hero-sub">Live operational performance across field support</p></div>
        </div>
        <nav class="stats-range" aria-label="Statistics date range">
            <a href="?days=7">7 days</a>
            <a href="?days=30" class="active" aria-current="page">30 days</a>
            <a href="?days=90">90 days</a>
            <a href="?days=0">All time</a>
        </nav>
    </div>

    <section class="stats-kpis stats-summary" aria-label="Operational summary">
        <article class="stats-kpi stats-kpi-group">
            <header class="stats-kpi-head">
                <span class="stats-kpi-icon tone-blue"><svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12h4l3 8 4-16 3 8h4"/></svg></span>
                <div><h2>Session performance</h2><p><?= $rangeLabel ?></p></div>
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
                    <div class="meter kpi-meter" role="img" aria-label="Solved rate <?= $solvedRate ?> percent"><i style="width:<?= $solvedRate ?>%"></i></div>
                </div>
            </div>
        </article>
        <article class="stats-kpi stats-kpi-single warning">
            <header class="stats-kpi-head"><span class="stats-kpi-icon tone-red"><svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3l9 16H3z"/></svg></span><div><h2>Escalation</h2><p>Needs attention</p></div></header>
            <div class="stats-metric"><span>Escalation rate</span><strong><?= $escalationRate ?>%</strong><small>1 escalated</small></div>
        </article>
        <article class="stats-kpi stats-kpi-single">
            <header class="stats-kpi-head"><span class="stats-kpi-icon tone-green"><svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/></svg></span><div><h2>Resolution</h2><p>Completed work</p></div></header>
            <div class="stats-metric"><span>Average resolution</span><strong>42 min</strong><small>Based on solved jobs under 8 hours</small></div>
        </article>
        <article class="stats-kpi stats-kpi-single">
            <header class="stats-kpi-head"><span class="stats-kpi-icon tone-violet"><svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 20v-6M12 20V6M20 20v-9"/></svg></span><div><h2>Team availability</h2><p>Current workload</p></div></header>
            <div class="stats-metric"><span>Active users</span><strong><?= $activeUsers ?></strong><small>3 jobs in progress</small></div>
        </article>
        <article class="stats-kpi stats-kpi-single">
            <header class="stats-kpi-head"><span class="stats-kpi-icon tone-amber"><svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3l2 5 5 2-5 2-2 5-2-5-5-2 5-2z"/></svg></span><div><h2>AI activity</h2><p>Support assistant</p></div></header>
            <div class="stats-metric"><span>Conversations</span><strong>11</strong><small>214 messages</small></div>
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
            <div class="trend-wrap" style="--trend-days:<?= count($daily) ?>;--trend-bar:11px">
                <div class="trend-axis" aria-hidden="true"><span><?= $axisMax ?></span><span><?= (int)round($axisMax / 2) ?></span><span>0</span></div>
                <div class="trend-scroll">
                <div class="trend-chart" role="img" aria-label="Sessions created and solved per day">
                    <?php foreach ($daily as $index => $row): ?>
                        <?php $showLabel = $chartDays <= 14 || $index % 3 === 0 || $index === count($daily) - 1; ?>
                        <div class="trend-day<?= $row['total'] === 0 ? ' is-zero' : '' ?>" title="<?= date('M j', strtotime($row['day'])) ?>: <?= $row['total'] ?> created, <?= $row['solved'] ?> solved">
                            <div class="trend-bars">
                                <?php if ($row['total'] > 0): ?><i class="created" style="height:<?= max(3, round($row['total'] / $axisMax * 100)) ?>%"></i><?php endif; ?>
                                <?php if ($row['solved'] > 0): ?><i class="done" style="height:<?= max(3, round($row['solved'] / $axisMax * 100)) ?>%"></i><?php endif; ?>
                            </div>
                            <span class="trend-day-label"><?= $showLabel ? date('M j', strtotime($row['day'])) : '' ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
                </div>
            </div>
        </section>

        <section class="stats-panel">
            <header><div><h2>Work Status</h2><p>Current distribution for last 30 days</p></div></header>
            <div class="status-list">
                <?php foreach ($statusRows as $row): $pct = round($row['total'] / $totalSessions * 100); ?>
                    <div class="status-row">
                        <div class="status-top"><span class="status-name"><i class="status-<?= $row['status'] ?>"></i><?= ucwords(str_replace('_', ' ', $row['status'])) ?></span><strong><?= $row['total'] ?></strong></div>
                        <div class="status-meter"><div class="meter"><i class="status-<?= $row['status'] ?>" style="width:<?= $pct ?>%"></i></div><small><?= $pct ?>%</small></div>
                    </div>
                <?php endforeach; ?>
                <div class="status-total"><span>Total sessions</span><b><?= number_format($totalSessions) ?></b></div>
            </div>
        </section>
    </div>

    <div class="stats-list-grid">
        <section class="stats-panel">
            <header><div><h2>Most Common Problems</h2><p>Bar length is volume, badge is the solved rate</p></div></header>
            <div class="rank-list">
                <?php foreach ($topIssues as $index => $row): $rate = round($row['solved'] / $row['total'] * 100); $band = $rate >= 70 ? 'good' : ($rate >= 40 ? 'mid' : 'low'); ?>
                    <div class="rank-row">
                        <b class="<?= $index < 3 ? 'is-top' : '' ?>"><?= $index + 1 ?></b>
                        <div class="rank-main">
                            <span class="rank-label" title="<?= htmlspecialchars($row['label']) ?>"><?= htmlspecialchars($row['label']) ?></span>
                            <div class="meter"><i style="width:<?= round($row['total'] / $maxIssue * 100) ?>%"></i></div>
                        </div>
                        <aside>
                            <strong><?= $row['total'] ?></strong>
                            <small class="rate <?= $band ?>"><?= $rate ?>% solved</small>
                        </aside>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="stats-panel">
            <header><div><h2>Most Serviced Devices</h2><p>Models receiving the most field work</p></div></header>
            <div class="rank-list device-list">
                <?php foreach ($topDevices as $index => $row): $untyped = strcasecmp($row['device_type'], 'Other') === 0; ?>
                    <div class="rank-row<?= $untyped ? ' is-untyped' : '' ?>">
                        <b class="<?= $index < 3 ? 'is-top' : '' ?>"><?= $index + 1 ?></b>
                        <div class="rank-main">
                            <span class="rank-label" title="<?= htmlspecialchars($row['label']) ?>"><?= htmlspecialchars($row['label']) ?><?php if (!$untyped): ?> <em><?= $row['device_type'] ?></em><?php endif; ?></span>
                            <div class="meter"><i style="width:<?= round($row['total'] / $maxDevice * 100) ?>%"></i></div>
                        </div>
                        <aside><strong><?= $row['total'] ?></strong><small>sessions</small></aside>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </div>

    <div class="stats-bottom-grid">
        <section class="stats-panel">
            <header><div><h2>Technician Activity</h2><p>Completed workload by assigned technician</p></div></header>
            <div class="tech-table">
                <div class="tech-head"><span>Technician</span><span>Sessions</span><span>Solved</span><span>Avg time</span></div>
                <div class="tech-row"><strong>Marco Villanueva Dela Cruz</strong><span>12</span><span class="tech-solved">9</span><span>38 min</span></div>
                <div class="tech-row"><strong>Ana Reyes</strong><span>7</span><span class="tech-solved">5</span><span>51 min</span></div>
            </div>
        </section>

        <section class="stats-panel health-panel">
            <header><div><h2>Content &amp; Assets</h2><p>System readiness outside active tickets</p></div></header>
            <div class="health-grid">
                <div><span class="health-ico"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 5h16v14H4z"/></svg></span><strong>42</strong><span>Knowledge articles</span></div>
                <div><span class="health-ico"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 13l4 4L19 7"/></svg></span><strong>31</strong><span>Published</span></div>
                <div><span class="health-ico"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/></svg></span><strong>6</strong><span>Awaiting review</span></div>
                <div><span class="health-ico"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 7h18v12H3z"/></svg></span><strong>58<small>/72</small></strong><span>Active equipment</span></div>
            </div>
            <div class="gap-list"><h3>Searches with no result</h3><div><span>printer offline fix</span><b>4x</b></div><div><span>how to reset bios password</span><b>2x</b></div></div>
        </section>
    </div>
</div>

<!-- Copied from statistics.php: the phone chart opens on the newest days. -->
<script>
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

<pre id="checks">CHECKS</pre>
<script>
(function () {
    var out = document.getElementById('checks');
    var lines = [];
    var styleBlocks = <?= (int)$styleBlocks ?>;
    var legacyGrid = <?= $legacyGrid ? 'true' : 'false' ?>;

    function check(name, ok, detail) {
        lines.push((ok ? 'PASS' : 'FAIL') + ' | ' + name + (detail ? '  [' + detail + ']' : ''));
    }
    function px(v) { return Math.round(v); }
    function num(value) { var m = String(value).match(/[\d.]+/); return m ? parseFloat(m[0]) : NaN; }
    function isDarkColor(color) { var p = String(color).match(/\d+/g) || []; return p.length >= 3 && (Number(p[0]) + Number(p[1]) + Number(p[2])) / 3 < 90; }
    function isLightColor(color) { var p = String(color).match(/\d+/g) || []; return p.length >= 3 && (Number(p[0]) + Number(p[1]) + Number(p[2])) / 3 > 170; }

    function run() {
        lines = [];
        var vw = window.innerWidth;

        // The redesign collapsed two competing style systems into one.
        check('statistics page carries a single style block', styleBlocks === 1, styleBlocks + ' <style> blocks');
        check('the old six-column KPI grid is gone', !legacyGrid, legacyGrid ? 'still present' : 'removed');

        check('page never scrolls sideways', document.documentElement.scrollWidth <= vw + 1,
            'scrollWidth=' + document.documentElement.scrollWidth + ' viewport=' + vw);

        var clipped = [];
        document.querySelectorAll('.stats-page .stats-panel, .stats-page .stats-kpi').forEach(function (el) {
            if (el.scrollWidth > el.clientWidth + 1) clipped.push(el.className);
        });
        check('no card clips its own content', clipped.length === 0, clipped.join(' | ') || 'all within bounds');

        // ---- summary cards ----
        var kpis = Array.prototype.slice.call(document.querySelectorAll('.stats-summary .stats-kpi'));
        var rowTop = Math.round(kpis[0].getBoundingClientRect().top);
        var rowMates = kpis.filter(function (k) { return Math.round(k.getBoundingClientRect().top) === rowTop; });
        check('cards in a summary row share one height',
            rowMates.every(function (k) { return Math.abs(k.getBoundingClientRect().height - rowMates[0].getBoundingClientRect().height) < 1.5; }),
            rowMates.map(function (k) { return px(k.getBoundingClientRect().height); }).join('/'));

        var fill = document.querySelector('.stats-summary .kpi-meter > i');
        var fillPct = Math.round(fill.getBoundingClientRect().width / fill.parentElement.clientWidth * 100);
        check('solved-rate bar matches the printed rate', Math.abs(fillPct - num(document.querySelector('.stats-summary .stats-metric.success > strong').textContent)) <= 2,
            'bar=' + fillPct + '% text=' + document.querySelector('.stats-summary .stats-metric.success > strong').textContent.trim());

        // A border-box bar with padding inside it collapses to nothing and the
        // track still paints, so the bar looks flat grey instead of filled.
        var fillHeight = fill.getBoundingClientRect().height;
        check('the solved-rate bar has a visible thickness (>= 6px)', fillHeight >= 6, fillHeight.toFixed(1) + 'px tall');
        var flat = [];
        document.querySelectorAll('.stats-page .meter > i').forEach(function (bar) {
            var h = bar.getBoundingClientRect().height;
            if (h < 6) flat.push((bar.className || 'bar') + '=' + h.toFixed(1) + 'px');
        });
        check('no progress bar renders flat', flat.length === 0, flat.join(' | ') || 'all bars visible');

        var kpiLabelSizes = Array.prototype.slice.call(document.querySelectorAll('.stats-metric > small'))
            .map(function (s) { return num(getComputedStyle(s).fontSize); });
        check('summary captions are legible (>= 11px)', Math.min.apply(null, kpiLabelSizes) >= 11, Math.min.apply(null, kpiLabelSizes) + 'px');

        // ---- chart ----
        var axis = Array.prototype.slice.call(document.querySelectorAll('.trend-axis > span')).map(function (s) { return s.textContent.trim(); });
        check('chart carries a labelled value axis', axis.length === 3 && Number(axis[0]) > 0 && Number(axis[2]) === 0, axis.join(' / '));
        var axisSize = num(getComputedStyle(document.querySelector('.trend-axis')).fontSize);
        check('axis labels are legible (>= 10px desktop, >= 9px phone)',
            axisSize >= (vw <= 700 ? 9 : 10), axisSize + 'px at ' + vw + 'px wide');

        var grid = getComputedStyle(document.querySelector('.trend-chart'), '::before').backgroundImage;
        check('chart draws horizontal gridlines', /repeating-linear-gradient/.test(grid), grid.slice(0, 48));

        var plot = document.querySelector('.trend-bars').getBoundingClientRect().height;
        var tallest = 0;
        document.querySelectorAll('.trend-bars > .created').forEach(function (b) { tallest = Math.max(tallest, b.getBoundingClientRect().height); });
        check('the busiest day fills the axis', tallest >= plot * 0.95, 'tallest=' + px(tallest) + ' plot=' + px(plot));

        var zeroDay = document.querySelector('.trend-day.is-zero');
        check('a zero day draws no bar', zeroDay && zeroDay.querySelectorAll('.trend-bars > i').length === 0,
            zeroDay ? zeroDay.querySelectorAll('.trend-bars > i').length + ' bars' : 'no zero day rendered');
        var tick = getComputedStyle(zeroDay.querySelector('.trend-bars'), '::after');
        check('a zero day still shows a baseline tick', tick.content && tick.content !== 'none' && num(tick.height) > 0,
            tick.content + ' h=' + num(tick.height) + ' w=' + num(tick.width));
        var labelSize = num(getComputedStyle(document.querySelector('.trend-day-label')).fontSize);
        check('day labels are legible (>= 9px)', labelSize >= 9, labelSize + 'px');

        // A 30-day plot cannot fit real bars into a phone width, so it keeps a
        // readable column floor and scrolls, opening on the newest days.
        var scroller = document.querySelector('.stats-page .trend-scroll');
        var narrow = getComputedStyle(document.querySelector('.stats-page .trend-wrap')).getPropertyValue('--trend-col').trim();
        var scrolls = scroller.scrollWidth > scroller.clientWidth + 1;
        var columnWidth = document.querySelector('.trend-day').getBoundingClientRect().width;
        check('day columns never collapse below a legible width, otherwise the plot scrolls',
            columnWidth >= 14 || scrolls,
            'column=' + columnWidth.toFixed(1) + 'px scrollWidth=' + scroller.scrollWidth + ' clientWidth=' + scroller.clientWidth);
        check('a wide viewport shows the whole period at once',
            num(narrow) > 0 || !scrolls,
            '--trend-col=' + narrow + ' scrollWidth=' + scroller.scrollWidth + ' clientWidth=' + scroller.clientWidth);
        check('the phone chart opens on the most recent days',
            !scrolls || scroller.scrollLeft >= scroller.scrollWidth - scroller.clientWidth - 2,
            'scrollLeft=' + px(scroller.scrollLeft) + ' max=' + px(scroller.scrollWidth - scroller.clientWidth));
        var busiest = document.querySelector('.trend-day .trend-bars > .created');
        check('bars stay wide enough to read (>= 4px)',
            !!busiest && busiest.getBoundingClientRect().width >= 4,
            busiest ? busiest.getBoundingClientRect().width.toFixed(1) + 'px at ' + vw + 'px wide' : 'missing');
        check('chart header summarises the period', document.querySelectorAll('.stats-trend-aside .trend-stat').length === 2,
            document.querySelectorAll('.stats-trend-aside .trend-stat').length + ' stats');

        // ---- ranked lists ----
        var longLabel = document.querySelector('.rank-list .rank-label');
        var clamp = getComputedStyle(longLabel).getPropertyValue('-webkit-line-clamp').trim() || getComputedStyle(longLabel).getPropertyValue('line-clamp').trim();
        check('long issue titles clamp to two lines', clamp === '2', 'line-clamp=' + clamp);
        check('clamped title still shows two lines of text',
            longLabel.getBoundingClientRect().height > num(getComputedStyle(longLabel).lineHeight) * 1.2
            && longLabel.getBoundingClientRect().height <= num(getComputedStyle(longLabel).lineHeight) * 2 + 3,
            'height=' + px(longLabel.getBoundingClientRect().height) + ' line-height=' + num(getComputedStyle(longLabel).lineHeight));
        check('clamped title keeps the full text on hover', (longLabel.getAttribute('title') || '').length >= 30,
            (longLabel.getAttribute('title') || '').slice(0, 46) + '...');
        check('title never widens its row', longLabel.scrollWidth <= longLabel.clientWidth + 1,
            'scrollWidth=' + longLabel.scrollWidth + ' clientWidth=' + longLabel.clientWidth);

        var badge = document.querySelector('.rank-row aside .rate');
        check('problem rows show a solved-rate badge', !!badge && /solved/.test(badge.textContent), badge ? badge.textContent.trim() : 'missing');
        var bandFills = {};
        document.querySelectorAll('.rank-row aside .rate').forEach(function (b) {
            bandFills[getComputedStyle(b).backgroundColor] = 1;
        });
        check('solved-rate badges are colour coded per band', !!badge && Object.keys(bandFills).length >= 2
            && getComputedStyle(badge).color !== getComputedStyle(document.querySelector('.rank-label')).color,
            Object.keys(bandFills).join(' | ') + ' (' + badge.className + ' = ' + getComputedStyle(badge).color + ')');

        var row = document.querySelector('.rank-row');
        var main = row.querySelector('.rank-main');
        var aside = row.querySelector('aside');
        check('count column stays clear of the label column',
            aside.getBoundingClientRect().left >= main.getBoundingClientRect().right - 1,
            'asideLeft=' + px(aside.getBoundingClientRect().left) + ' mainRight=' + px(main.getBoundingClientRect().right));
        // Each ranked list marks its own top three; the fourth row must read as plain.
        var firstList = document.querySelector('.rank-list');
        var rankMarks = firstList.querySelectorAll('.rank-row > b.is-top');
        var plainRank = firstList.querySelector('.rank-row:nth-child(4) > b');
        check('top-three ranks are visually marked, the rest stay plain',
            rankMarks.length === 3 && !!plainRank
            && getComputedStyle(rankMarks[0]).backgroundColor !== getComputedStyle(plainRank).backgroundColor,
            rankMarks.length + ' marked, plain=' + (plainRank ? getComputedStyle(plainRank).backgroundColor : 'missing'));
        check('untagged devices hide the useless "Other" chip', !document.querySelector('.rank-row.is-untyped .rank-label em'),
            document.querySelector('.rank-row.is-untyped') ? 'no chip on untyped row' : 'no untyped row');

        // ---- work status ----
        var statusRows = document.querySelectorAll('.status-row');
        check('work status lists every status with a meter and a share',
            statusRows.length === 4 && Array.prototype.every.call(statusRows, function (r) {
                return !!r.querySelector('.meter > i') && !!r.querySelector('.status-meter > small');
            }), statusRows.length + ' rows');
        var fills = {};
        document.querySelectorAll('.status-row .meter > i').forEach(function (m) { fills[getComputedStyle(m).backgroundImage] = 1; });
        check('status bars are distinguishable by colour', Object.keys(fills).length >= 3, Object.keys(fills).length + ' distinct fills');
        var totalRow = document.querySelector('.status-total');
        check('work status closes with a total', !!totalRow && /\d/.test(totalRow.textContent), totalRow ? totalRow.textContent.trim() : 'missing');
        var meterH = num(getComputedStyle(document.querySelector('.status-row .meter')).height);
        check('status meters are thick enough to read (>= 7px)', meterH >= 7, meterH + 'px');

        // ---- technician table ----
        var tracks = getComputedStyle(document.querySelector('.tech-row')).gridTemplateColumns.trim().split(/\s+/);
        var wantTracks = vw <= 620 ? 3 : 4;
        check('technician table keeps its columns usable', tracks.length === wantTracks,
            tracks.length + ' tracks at ' + vw + 'px (want ' + wantTracks + ')');
        var headSize = num(getComputedStyle(document.querySelector('.tech-head')).fontSize);
        check('table headings are legible (>= 10px)', headSize >= 10, headSize + 'px');

        // ---- content & assets ----
        var tile = document.querySelector('.health-grid > div');
        check('content tiles size themselves to their icon', tile.getBoundingClientRect().height > 50,
            'tile height=' + px(tile.getBoundingClientRect().height));
        var tileLabels = Array.prototype.slice.call(document.querySelectorAll('.health-grid > div > span:last-child'))
            .map(function (s) { return num(getComputedStyle(s).fontSize); });
        check('content tile labels are legible (>= 11px)', Math.min.apply(null, tileLabels) >= 11, Math.min.apply(null, tileLabels) + 'px');
        var gapItems = document.querySelectorAll('.gap-list > div');
        check('search gaps stay on one line each',
            gapItems.length > 0 && Array.prototype.every.call(gapItems, function (g) { return g.scrollWidth <= g.clientWidth + 1; }),
            gapItems.length + ' rows');

        // ---- responsive + theme ----
        var sumCols = getComputedStyle(document.querySelector('.stats-summary')).gridTemplateColumns.trim().split(/\s+/).length;
        var wantSum = vw <= 620 ? 1 : (vw <= 1180 ? 2 : 3);
        check('summary grid matches the viewport', sumCols === wantSum, sumCols + ' columns at ' + vw + 'px (want ' + wantSum + ')');

        var root = document.documentElement;
        var wasDark = root.classList.contains('dark');
        var panel = document.querySelector('.stats-panel');
        var lightCard = getComputedStyle(panel).backgroundColor;
        var lightInk = getComputedStyle(document.querySelector('.rank-label')).color;
        var lightAxis = getComputedStyle(document.querySelector('.trend-axis')).color;
        var lightMeter = getComputedStyle(document.querySelector('.status-row .meter')).backgroundColor;
        root.classList.add('dark');
        var darkCard = getComputedStyle(panel).backgroundColor;
        var darkInk = getComputedStyle(document.querySelector('.rank-label')).color;
        var darkAxis = getComputedStyle(document.querySelector('.trend-axis')).color;
        var darkMeter = getComputedStyle(document.querySelector('.status-row .meter')).backgroundColor;
        root.classList.toggle('dark', wasDark);
        check('light theme uses white cards and dark type', isLightColor(lightCard) && isDarkColor(lightInk),
            lightCard + ' / ' + lightInk);
        check('dark theme swaps to dark cards and light type', isDarkColor(darkCard) && isLightColor(darkInk),
            darkCard + ' / ' + darkInk);
        check('axis text and meter tracks follow the theme', lightAxis !== darkAxis && lightMeter !== darkMeter,
            lightAxis + ' -> ' + darkAxis);

        out.textContent = 'CHECKS @ ' + vw + 'px\n' + lines.join('\n');
        window.__statsChecks = lines;
    }

    window.__runStatsChecks = run;
    window.addEventListener('resize', run);
    window.addEventListener('load', run);
    requestAnimationFrame(run);
})();
</script>
</body>
</html>
