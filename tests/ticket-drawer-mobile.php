<?php
/**
 * Ticket detail sheet ("View Ticket") phone-layout check.
 *
 * The sheet lives in public/pages/tickets.php and is styled by the inline
 * <style> block in that file plus public/assets/css/app.css. Both are needed to
 * see the real phone layout, and one of them is PHP, so this check reads the
 * styles out of tickets.php at run time instead of duplicating them (a copy
 * would drift and stop proving anything).
 *
 * Run it with the built-in server from the project root:
 *     php -S 127.0.0.1:8123 -t .
 *     open http://127.0.0.1:8123/tests/ticket-drawer-mobile.php
 * Then use the browser's device toolbar (or just narrow the window) to about
 * 390x844. Every line under CHECKS must start with PASS.
 */

$ticketsFile = dirname(__DIR__) . '/public/pages/tickets.php';
$source = @file_get_contents($ticketsFile);
$styles = [];
if ($source !== false && preg_match_all('#<style>(.*?)</style>#s', $source, $m)) {
    $styles = $m[1];
}
$inlineCss = implode("\n", $styles);

$longAddress = '% Ya Nike Shoe Corporation s Lot 5 Purok 5D1c T 046-4710343 / 0998-5361871 Brgy.Baraytay PI er 4110 Cavitted City hase';
$longProblem = 'i Unable to power on ih Upon checking unit has no any sign ii For Onsite Checking of power';

// openTicketDrawer() chooses the sheet's display inline. That single value is
// what decides whether the sheet's inner body can scroll at all, so take the
// real expression out of app.js instead of guessing at it here.
$appJs = @file_get_contents(dirname(__DIR__) . '/public/assets/js/app.js');
$drawerDisplayExpr = '';
if ($appJs !== false && preg_match('/var drawer = document\.getElementById\(.ticket-drawer.\);.*?drawer\.style\.display = ([^;]+);/s', $appJs, $m)) {
    $drawerDisplayExpr = trim($m[1]);
} elseif ($appJs !== false && preg_match('/el\.style\.display\s*=\s*([^;
]+);/', $appJs, $m2)) {
    // openTicketDrawer() calls the shared ftShowModal() helper; its display
    // expression is the one that matters.
    $drawerDisplayExpr = trim($m2[1]);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Ticket detail sheet - phone layout check</title>
<link rel="stylesheet" href="../public/assets/css/app.css">
<style>
<?= $inlineCss ?>
body { background: #eef2f7; margin: 0; }
/* Minimal stand-ins for the real app chrome, so the sheet is measured inside
   the same header / tab bar it lives between on a phone. */
.harness-header { height: 64px; background: #fff; border-bottom: 1px solid #e5e7eb; display: flex; align-items: center; padding: 0 14px; }
.harness-main { padding: 16px 14px 120px; }
.harness-nav { position: fixed; left: 0; right: 0; bottom: 0; min-height: 76px; background: #fff; border-top: 1px solid #e5e7eb; display: flex; align-items: center; justify-content: space-around; z-index: 10060; font: 600 11px/1 Inter, sans-serif; color: #94a3b8; }
#checks { position: fixed; left: 8px; top: 8px; z-index: 99999; background: #0f172a; color: #cbd5e1; font: 11px/1.5 ui-monospace, monospace; padding: 8px 10px; border-radius: 8px; max-width: 320px; white-space: pre-wrap; }
</style>
</head>
<body>

<div class="harness-header"><span style="font-weight:700;color:#0f172a;">My Tickets</span></div>
<div class="harness-main">
    <!-- Hero tile, copied from tickets.php: the Mine / All tickets switch plus the
         New Ticket button have to share one phone row. -->
    <div class="tickets-page">
        <div class="page-hero tickets-hero">
            <div>
                <div style="display:flex;align-items:center;gap:14px;">
                    <div class="page-hero-ico blue"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2"><path d="M4 6h16v12H4z"/></svg></div>
                    <div>
                        <h1 class="page-hero-title">Team Tickets</h1>
                        <p class="page-hero-sub">Review the complete field queue; each technician keeps control of their own workflow.</p>
                    </div>
                </div>
            </div>
            <div class="page-hero-actions">
                <div class="tickets-scope" role="tablist">
                    <a role="tab" class="tickets-scope-btn" href="?scope=mine">Mine</a>
                    <a role="tab" class="tickets-scope-btn is-active" href="?scope=all">All tickets</a>
                </div>
                <button class="btn btn-primary"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg> New Ticket</button>
            </div>
        </div>
    </div>
    <div class="card" style="padding:16px;">Page content behind the sheet.</div>
</div>
<nav class="harness-nav"><span>Home</span><span>Fix</span><span>AI</span><span>KB</span><span style="color:#2563eb;">Tickets</span></nav>

<!-- Same structure openTicketDrawer() writes into #ticket-drawer-body -->
<div id="ticket-drawer-overlay" class="ftd-modal-overlay"></div>
<aside id="ticket-drawer" class="ftd-modal" role="dialog" aria-modal="true" aria-labelledby="ticket-drawer-title">
    <div id="ticket-drawer-body" class="ftd-modal-body">
        <div class="ftd-modal-head" style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;border-bottom:1px solid #e5e7eb;">
            <div style="display:flex;gap:12px;min-width:0;">
                <div class="ft-co-ico"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2"><rect x="3" y="4" width="18" height="16" rx="2"/></svg></div>
                <div style="min-width:0;">
                    <div id="ticket-drawer-title" style="font-size:17px;font-weight:800;color:#111827;word-break:break-word;">Malayan</div>
                    <div class="ftd-head-sub" style="font-size:12px;color:#64748b;font-weight:600;margin-top:3px;">Ticket #SD123465<span style="margin-left:10px;">SN: LCUTLNO-EXTRA-LONG-SERIAL-NUMBER</span></div>
                    <div class="ftd-owner-line">Assigned to sodaicaries</div>
                </div>
            </div>
            <div style="display:flex;align-items:center;gap:8px;flex-shrink:0;">
                <span class="badge" style="background:#fffbeb;color:#d97706;">New</span>
                <button class="btn btn-sm btn-ghost ftd-close-btn" aria-label="Close ticket details" style="color:#64748b;">&#10005;</button>
            </div>
        </div>

        <div class="ftd-grid">
            <div>
                <div class="ftd-section">
                    <div class="ftd-title">Ticket</div>
                    <div class="ftd-row"><div class="ftd-lbl">Ticket #</div><div class="ftd-val">SD123465</div></div>
                    <div class="ftd-row"><div class="ftd-lbl">Status</div><div class="ftd-val">New</div></div>
                    <div class="ftd-row"><div class="ftd-lbl">Created</div><div class="ftd-val">09/26/2026</div></div>
                    <div class="ftd-row"><div class="ftd-lbl">Priority</div><div class="ftd-val">medium</div></div>
                    <div class="ftd-row"><div class="ftd-lbl">Address</div><div class="ftd-val"><?= htmlspecialchars($longAddress) ?></div></div>
                </div>

                <!-- What ttInitTicketLocationMap() writes when no map can be drawn -->
                <div class="ftd-section">
                    <div class="ftd-title">Ticket Location</div>
                    <div id="ticket-location-map-1" class="ftd-ticket-map is-map-fallback">
                        <div class="ftd-map-note">Could not locate this address.</div>
                        <div class="ftd-map-address"><?= htmlspecialchars($longAddress) ?></div>
                        <div class="ftd-map-actions">
                            <a class="btn btn-sm btn-primary" href="https://www.google.com/maps/search/?api=1&amp;query=test" target="_blank" rel="noopener"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4.5 8-11a8 8 0 0 0-16 0c0 6.5 8 11 8 11z"/></svg> Open in Maps</a>
                            <button type="button" class="btn btn-sm btn-secondary" data-tt-copy="x"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2"/></svg> Copy address</button>
                        </div>
                    </div>
                </div>

                <div class="ftd-section">
                    <div class="ftd-title">Device</div>
                    <div class="ftd-row"><div class="ftd-lbl">Device</div><div class="ftd-val">Dell Latitude 5520</div></div>
                    <div class="ftd-row"><div class="ftd-lbl">Serial Number</div><div class="ftd-val">LCUTLNO-EXTRA-LONG-SERIAL-NUMBER</div></div>
                </div>

                <div class="ftd-section">
                    <div class="ftd-title">Issue</div>
                    <div class="ftd-steps"><?= htmlspecialchars($longProblem) ?></div>
                </div>

                <div class="ftd-section">
                    <div class="ftd-title">Work Time</div>
                    <div class="ftd-row"><div class="ftd-lbl">Time In</div><div class="ftd-val">00:00</div></div>
                    <div class="ftd-row"><div class="ftd-lbl">Time Out</div><div class="ftd-val">00:00</div></div>
                </div>
            </div>

            <div>
                <div class="ftd-section" id="guide-1" style="background:#f8fafc;border:1px solid #e5e7eb;border-radius:12px;padding:14px;">
                    <div class="ftd-title">Field Guide</div>
                    <div style="font-size:12px;color:#94a3b8;">Loading symptoms, cause, tools &amp; tips&hellip;</div>
                </div>

                <div style="display:flex;justify-content:space-between;align-items:center;margin:16px 0 8px;">
                    <div class="ftd-title" style="margin:0;color:#1e40af;">Report</div>
                    <button class="btn btn-sm btn-primary">Copy Report</button>
                </div>
                <div id="report-1" style="background:#f8fafc;border:1px solid #e5e7eb;border-radius:10px;padding:6px 14px 4px;margin-bottom:12px;">
                    <div style="display:grid;grid-template-columns:150px minmax(0,1fr);gap:12px;padding:6px 0;border-bottom:1px solid #eef2f7;align-items:start;">
                        <span style="font-size:10.5px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.4px;padding-top:2px;">Action Taken</span>
                        <span style="font-size:12.5px;font-weight:600;color:#111827;text-align:left;word-break:break-word;white-space:pre-wrap;line-height:1.45;">Replaced the power adapter and reseated the memory modules; machine now powers on and completes POST normally.</span>
                    </div>
                </div>

                <div class="ftd-field">
                    <label class="ftd-lbl" for="rep-1-result">Result of Checking</label>
                    <textarea id="rep-1-result" class="form-input tt-memory-manual" rows="3" style="width:100%;font-size:12.5px;padding:8px 10px;resize:vertical;">i Unable to power on ih Upon checking unit has no any sign ii For Onsite Checking of power</textarea>
                </div>
                <div class="ftd-field">
                    <label class="ftd-lbl" for="rep-1-reco">Recommendation</label>
                    <textarea id="rep-1-reco" class="form-input tt-memory-manual" rows="2" style="width:100%;font-size:12.5px;padding:8px 10px;resize:vertical;"></textarea>
                </div>
                <div class="ftd-field">
                    <label class="ftd-lbl" for="rep-1-confirm">Confirmed By</label>
                    <input id="rep-1-confirm" class="form-input tt-memory-manual" placeholder="Type another contact name..." style="width:100%;font-size:12.5px;padding:8px 10px;">
                </div>

                <div class="ftd-section"><div class="ftd-title">Steps Done</div><div id="logged-1" class="ftd-steps">1. Confirmed no power LED with a known-good outlet
2. Swapped power adapter, unit powered on
3. Ran Lenovo diagnostics, all passed</div></div>

                <div class="ftd-section">
                    <div class="ftd-title">Parts &amp; Tools</div>
                    <div class="ftd-row"><div class="ftd-lbl">Parts Replaced</div><div class="ftd-val">65W USB-C adapter</div></div>
                    <div class="ftd-row"><div class="ftd-lbl">Tools Used</div><div class="ftd-val">Multimeter, Precision screwdriver</div></div>
                </div>
            </div>
        </div>
    </div>
</aside>

<pre id="checks">running&hellip;</pre>
<script>
(function () {
    var out = document.getElementById('checks');
    function run() {
    var lines = [];
    function check(name, ok, detail) {
        lines.push((ok ? 'PASS ' : 'FAIL ') + name + (detail ? ' (' + detail + ')' : ''));
    }

    // This harness measures the phone sheet only: above the phone breakpoint the
    // app has no tab bar and the sheet scrolls itself, so the expectations here
    // do not apply. Say so instead of reporting a screenful of false failures.
    var phoneSheet = window.matchMedia('(max-width: 767px)').matches;
    if (!phoneSheet) {
        window.__drawerChecks = ['SKIP - open this page at phone width (currently ' + window.innerWidth + 'px)'];
        out.textContent = 'CHECKS\nSKIP - this checks the phone sheet.\nCurrent width: ' + window.innerWidth + 'px.\nNarrow the window to about 390px and it re-runs.';
        return;
    }

    var drawer = document.getElementById('ticket-drawer');
    var body   = document.getElementById('ticket-drawer-body');
    var nav    = document.querySelector('.harness-nav');

    // Open the sheet the way openTicketDrawer() opens it: the same inline display
    // value (read out of app.js above) and the same page scroll lock.
    var drawerDisplayExpr = <?= json_encode($drawerDisplayExpr) ?>;
    function openLikeApp() {
        var value = 'block';
        try { value = String(eval(drawerDisplayExpr)); } catch (e) { /* keep the plain value */ }
        drawer.style.display = value;
        document.body.style.overflow = 'hidden';
        return value;
    }
    function closeLikeApp() { drawer.style.display = 'none'; document.body.style.overflow = ''; }
    var openedWith = openLikeApp();

    var rect   = drawer.getBoundingClientRect();
    var navTop = nav.getBoundingClientRect().top;
    var vw     = document.documentElement.clientWidth;

    check('page has no horizontal overflow', document.documentElement.scrollWidth <= vw + 1,
        'scrollWidth=' + document.documentElement.scrollWidth + ' viewport=' + vw);
    check('sheet stays inside the viewport', rect.left >= -1 && rect.right <= vw + 1,
        'left=' + Math.round(rect.left) + ' right=' + Math.round(rect.right) + ' viewport=' + vw);
    check('sheet does not run under the tab bar', rect.bottom <= navTop + 1,
        'bottom=' + Math.round(rect.bottom) + ' navTop=' + Math.round(navTop));
    check('sheet has a scrollable body', body.scrollHeight > body.clientHeight,
        'content=' + body.scrollHeight + ' visible=' + body.clientHeight);
    var canScroll = getComputedStyle(body).overflowY;
    check('body scrolls vertically', canScroll === 'auto' || canScroll === 'scroll', 'overflow-y=' + canScroll);

    // The phone sheet only scrolls inside #ticket-drawer-body, and that only
    // works while the sheet itself is a flex column. An inline display:block from
    // openTicketDrawer() used to defeat the flex rule, sizing the body to its
    // entire content: scrollHeight === clientHeight, nothing scrolled, and the
    // rest of the ticket was clipped and unreachable.
    check('app.js opens the sheet as a flex column on a phone',
        openedWith === 'flex', 'app.js asked for display:' + openedWith);
    check('the sheet renders as a flex column (what its scroller needs)',
        getComputedStyle(drawer).display === 'flex',
        'display=' + getComputedStyle(drawer).display + ' at ' + vw + 'px wide');
    var scroller = body;
    var scrollerTravel = 0;
    function reachBottom() {
        var before = scroller.scrollTop;
        scroller.scrollTop = scroller.scrollHeight;
        scrollerTravel = scroller.scrollTop;
        var reached = scroller.scrollTop + scroller.clientHeight >= scroller.scrollHeight - 2;
        scroller.scrollTop = before;
        return reached;
    }
    var scrollable = scroller.scrollHeight > scroller.clientHeight + 1;
    check('the whole ticket is reachable by scrolling', scrollable && reachBottom(),
        'travel=' + Math.round(scrollerTravel) + 'px, content=' + scroller.scrollHeight + ', visible=' + scroller.clientHeight);
    // Backstop: a browser still running the previous app.js opens the sheet with
    // display:block inline. The CSS in tickets.php has to keep it working.
    function backstop() {
        var saved = drawer.style.display;
        drawer.style.display = 'block';
        var forced = getComputedStyle(drawer).display;
        var reaches = body.scrollHeight > body.clientHeight + 1 && reachBottom();
        drawer.style.display = saved;
        return forced === 'flex' && reaches;
    }
    check('a cached app.js that opens with display:block still scrolls', backstop(),
        'forced display=' + (function () { drawer.style.display = 'block'; var d = getComputedStyle(drawer).display; drawer.style.display = 'flex'; return d; })());
    check('long address wraps instead of widening the sheet',
        drawer.scrollWidth <= drawer.clientWidth + 1,
        'drawer scrollWidth=' + drawer.scrollWidth + ' clientWidth=' + drawer.clientWidth);

    // The sheet header turns blue on a phone; app.js writes dark screen colours
    // inline, so the CSS must win or the title is grey-on-blue.
    var headTitle = document.getElementById('ticket-drawer-title');
    var headSub   = document.querySelector('#ticket-drawer .ftd-head-sub');
    var ownerLine = document.querySelector('#ticket-drawer .ftd-owner-line');
    function isWhitish(el) {
        if (!el) return false;
        var parts = getComputedStyle(el).color.match(/\d+/g) || [];
        return parts.length >= 3 && Number(parts[0]) > 200 && Number(parts[1]) > 200 && Number(parts[2]) > 200;
    }
    check('drawer title is readable on the blue header', isWhitish(headTitle), headTitle ? getComputedStyle(headTitle).color : 'missing');
    check('drawer sub-line + owner line are readable', isWhitish(headSub) && isWhitish(ownerLine),
        (headSub ? getComputedStyle(headSub).color : 'missing') + ' / ' + (ownerLine ? getComputedStyle(ownerLine).color : 'missing'));
    // Deliberately clipped with an ellipsis (the full serial is listed under
    // Device): it must not wrap into extra lines, and the badge must stay clear.
    var headSubStyle = headSub ? getComputedStyle(headSub) : null;
    var headBadge = document.querySelector('#ticket-drawer .ftd-modal-head .badge');
    check('header sub-line is clipped to one line', !!headSubStyle && headSubStyle.whiteSpace === 'nowrap' && headSubStyle.textOverflow === 'ellipsis',
        headSubStyle ? 'white-space=' + headSubStyle.whiteSpace + ' text-overflow=' + headSubStyle.textOverflow : 'missing');
    check('header sub-line does not collide with the status badge',
        headSub && headBadge ? headSub.getBoundingClientRect().right <= headBadge.getBoundingClientRect().left + 1 : false,
        headSub ? 'subRight=' + Math.round(headSub.getBoundingClientRect().right) + ' badgeLeft=' + Math.round(headBadge.getBoundingClientRect().left) : 'missing');

    // Report rows are written with an inline "150px value" grid; on a phone the
    // label column collapses and letter-breaks ("Acti / on Tak / en").
    var reportRow = document.querySelector('#report-1 > div');
    var reportLabel = reportRow ? reportRow.firstElementChild : null;
    var tracks = reportRow ? getComputedStyle(reportRow).gridTemplateColumns.trim().split(/\s+/) : [];
    check('report rows stack to a single column', tracks.length === 1, 'grid-template-columns=' + (reportRow ? getComputedStyle(reportRow).gridTemplateColumns : 'missing'));
    check('report label is not letter-broken', reportLabel ? reportLabel.scrollWidth <= reportLabel.clientWidth + 1 : false,
        reportLabel ? 'label scrollWidth=' + reportLabel.scrollWidth + ' clientWidth=' + reportLabel.clientWidth : 'missing');

    // A dead "Map is unavailable" box ate 190px of the sheet with no way to reach
    // the site; the fallback must be compact and offer a Maps link.
    var mapBox = document.getElementById('ticket-location-map-1');
    var mapRect = mapBox ? mapBox.getBoundingClientRect() : { height: 0 };
    // It must size itself to the address + buttons (a 320px phone wraps the two
    // buttons onto two rows, which is fine) instead of holding a fixed 190px of
    // empty grey, and it must never clip what it does show.
    check('map fallback sizes to its content, not a fixed empty box', mapRect.height > 0 && mapRect.height < 240,
        'height=' + Math.round(mapRect.height));
    check('map fallback is not clipped', mapBox ? mapBox.scrollHeight <= mapBox.clientHeight + 1 && mapBox.scrollWidth <= mapBox.clientWidth + 1 : false,
        mapBox ? 'scroll=' + mapBox.scrollWidth + 'x' + mapBox.scrollHeight + ' client=' + mapBox.clientWidth + 'x' + mapBox.clientHeight : 'missing');
    check('map fallback offers a Maps link and the address',
        !!(mapBox && mapBox.querySelector('a[href*="google.com/maps"]') && mapBox.querySelector('.ftd-map-address')),
        mapBox ? mapBox.className : 'missing');
    var mapAddr = mapBox ? mapBox.querySelector('.ftd-map-address') : null;
    check('map fallback address wraps', mapAddr ? mapAddr.scrollWidth <= mapAddr.clientWidth + 1 : false,
        mapAddr ? 'scrollWidth=' + mapAddr.scrollWidth + ' clientWidth=' + mapAddr.clientWidth : 'missing');

    // Hero tile: the Mine / All tickets switch and the New Ticket button share
    // one row on a phone; neither may push the page wider than the screen.
    var hero = document.querySelector('.tickets-page .tickets-hero');
    var scopeButtons = Array.prototype.slice.call(document.querySelectorAll('.tickets-scope-btn'));
    var heroBtn = document.querySelector('.tickets-page .page-hero-actions .btn-primary');
    check('hero tile fits the phone width', hero ? hero.scrollWidth <= hero.clientWidth + 1 : false,
        hero ? 'scrollWidth=' + hero.scrollWidth + ' clientWidth=' + hero.clientWidth : 'missing');
    check('scope switch has both options reachable', scopeButtons.length === 2, scopeButtons.length + ' options');
    check('scope switch is tappable', scopeButtons.every(function(b) { return b.getBoundingClientRect().height >= 32; }),
        scopeButtons.map(function(b) { return Math.round(b.getBoundingClientRect().height); }).join('/'));
    check('hero actions stay on screen',
        scopeButtons.length && heroBtn ? heroBtn.getBoundingClientRect().right <= vw + 1
            && scopeButtons[0].getBoundingClientRect().left >= -1 : false,
        heroBtn ? 'buttonRight=' + Math.round(heroBtn.getBoundingClientRect().right) + ' viewport=' + vw : 'missing');

    out.textContent = 'CHECKS\n' + lines.join('\n');
    window.__drawerChecks = lines;
}
// Re-measure after layout settles and whenever the window is resized, so the
// numbers are never read from a half-laid-out page.
window.__runDrawerChecks = run;
window.addEventListener('resize', run);
window.addEventListener('load', run);
requestAnimationFrame(run);
})();
</script>
</body>
</html>
