<?php
/**
 * Phone behaviour of every modal / side panel in the app.
 *
 * The app opens its panels two ways: some set display:flex, others set a plain
 * display:block. On a phone a sheet is a flex column whose inner body scrolls,
 * so a block panel grows to its whole content height and clips the rest - the
 * panel looks frozen and nothing can be scrolled. app.css now forces the column
 * layout for every panel while it is visible, and this checks that promise for
 * each family of panels the app actually has.
 *
 * The panels here are rebuilt from the real markup and the CSS is read out of
 * the pages at run time (a copy of either would drift and stop proving anything).
 * Each panel is opened the way a legacy opener did - inline display:block - so
 * the test covers browsers still running a cached script.
 *
 * Run: php -S 127.0.0.1:8123 -t .   then open
 *      http://127.0.0.1:8123/tests/mobile-modals.php
 * The checks re-run on resize and every line must start with PASS.
 * Window.__modalChecks holds the same list.
 */

$root = dirname(__DIR__);
$panelSources = [
    'admin/knowledge.php',
    'admin/equipment.php',
    'brand.php',
    'equipment.php',
    'tickets.php',
    'admin/users.php',
    'admin/troubleshoot.php',
];
$inlineCss = '';
foreach ($panelSources as $relative) {
    $file = $root . '/public/pages/' . $relative;
    $src = @file_get_contents($file);
    if ($src === false) continue;
    if (preg_match_all('#<style>(.*?)</style>#s', $src, $m)) {
        $inlineCss .= "\n/* ==== {$relative} ==== */\n" . implode("\n", $m[1]);
    }
}

/** Enough body content that the sheet must scroll to reveal the last field. */
function filler(string $label, int $count = 9): string
{
    $html = '';
    for ($i = 1; $i <= $count; $i++) {
        $html .= '<div style="margin-bottom:16px;">'
            . '<label style="display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:4px;">' . $label . ' ' . $i . '</label>'
            . '<input type="text" value="Sample value ' . $i . '" style="width:100%;padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;box-sizing:border-box;">'
            . '</div>';
    }
    return $html;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Modal + panel phone behaviour</title>
<link rel="stylesheet" href="../public/assets/css/app.css">
<link rel="stylesheet" href="../public/assets/css/ui-refresh.css">
<link rel="stylesheet" href="../public/assets/css/ticket-management.css">
<style>
<?= $inlineCss ?>
body { background: #eef2f7; margin: 0; }
/* Stand-ins for the real app chrome: the tab bar the sheets have to fit above. */
.harness-header { height: 64px; background: #fff; border-bottom: 1px solid #e5e7eb; display: flex; align-items: center; padding: 0 14px; font: 700 14px Inter, sans-serif; color: #0f172a; }
.harness-nav { position: fixed; left: 0; right: 0; bottom: 0; min-height: 76px; background: #fff; border-top: 1px solid #e5e7eb; display: flex; align-items: center; justify-content: space-around; z-index: 10060; font: 600 11px/1 Inter, sans-serif; color: #94a3b8; }
#checks { position: fixed; left: 8px; bottom: 8px; z-index: 99999; max-width: 420px; max-height: 50vh; overflow: auto; padding: 8px 10px; border-radius: 8px; background: #0f172a; color: #cbd5e1; font: 11px/1.5 ui-monospace, monospace; white-space: pre-wrap; }
</style>
</head>
<body>
<div class="harness-header">Field IT</div>
<div style="padding:16px 14px 120px;">
    <div class="card" style="padding:16px;">Dashboard content behind the panels.</div>
</div>
<nav class="harness-nav"><span>Home</span><span>Fix</span><span>AI</span><span>KB</span><span>Tickets</span></nav>

<!-- ===== Knowledge Base editor panel (admin/knowledge.php) ===== -->
<div id="kb-editor-panel" style="display:none;position:fixed;top:0;right:0;width:min(640px,95vw);height:100vh;height:100dvh;background:#fff;z-index:9999;box-shadow:-4px 0 24px rgba(0,0,0,0.15);overflow-y:auto;">
    <div style="position:sticky;top:0;background:#fff;border-bottom:1px solid #e5e7eb;padding:16px 24px;display:flex;align-items:center;justify-content:space-between;z-index:1;">
        <h2 style="font-size:16px;font-weight:800;color:#111827;">New Article</h2>
        <button type="button" style="background:none;border:none;padding:4px;">&#10005;</button>
    </div>
    <form id="kb-editor-form" style="padding:20px 24px 40px;">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px;">
            <div><label style="display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:4px;">Category *</label>
                <select style="width:100%;padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;"><option>Display</option><option>Power</option></select></div>
            <div><label style="display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:4px;">Difficulty</label>
                <select style="width:100%;padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;"><option>Easy</option><option>Hard</option></select></div>
        </div>
        <?= filler('Field') ?>
        <div style="display:flex;gap:8px;justify-content:flex-end;">
            <button type="button" class="btn btn-secondary">Cancel</button>
            <button type="button" class="btn btn-primary">Save Article</button>
        </div>
    </form>
</div>

<!-- ===== Knowledge Base viewer panel ===== -->
<div id="kb-viewer-panel" style="display:none;position:fixed;top:0;right:0;width:min(700px,95vw);height:100vh;height:100dvh;background:#fff;z-index:9999;box-shadow:-4px 0 24px rgba(0,0,0,0.15);overflow-y:auto;">
    <div style="position:sticky;top:0;background:#fff;border-bottom:1px solid #e5e7eb;padding:16px 24px;display:flex;align-items:center;justify-content:space-between;z-index:1;">
        <h2 style="font-size:16px;font-weight:800;color:#111827;">Article Preview</h2>
        <button type="button" style="background:none;border:none;padding:4px;">&#10005;</button>
    </div>
    <div id="kb-viewer-content" style="padding:24px;">
        <h3 style="margin:0 0 10px;font-size:16px;color:#111827;">No Display Troubleshooting</h3>
        <?= filler('Step', 8) ?>
    </div>
</div>

<!-- ===== Equipment editor panel (admin/equipment.php) ===== -->
<div id="eq-editor-panel" style="display:none;position:fixed;top:0;right:0;width:min(680px,95vw);height:100vh;height:100dvh;background:#fff;z-index:9999;box-shadow:-4px 0 24px rgba(0,0,0,0.15);overflow-y:auto;">
    <div style="position:sticky;top:0;background:#fff;border-bottom:1px solid #e5e7eb;padding:16px 24px;display:flex;align-items:center;justify-content:space-between;z-index:1;">
        <h2 style="font-size:16px;font-weight:800;color:#111827;">Add Equipment</h2>
        <button type="button" style="background:none;border:none;padding:4px;">&#10005;</button>
    </div>
    <form id="eq-editor-form" style="padding:20px 24px 40px;">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px;">
            <div><label style="display:block;font-size:12px;font-weight:700;">Manufacturer *</label><input type="text" style="width:100%;padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;"></div>
            <div><label style="display:block;font-size:12px;font-weight:700;">Model Name *</label><input type="text" style="width:100%;padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;"></div>
        </div>
        <?= filler('Spec') ?>
    </form>
</div>

<!-- ===== Brand modal (public/pages/equipment.php) ===== -->
<div id="brand-modal-overlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9998;"></div>
<div id="brand-modal" style="display:none;position:fixed;top:0;right:0;width:min(560px,95vw);height:100vh;height:100dvh;background:#fff;z-index:9999;overflow-y:auto;">
    <form id="brand-form" onsubmit="return false">
        <div style="padding:16px 24px;border-bottom:1px solid #e5e7eb;display:flex;align-items:center;justify-content:space-between;">
            <h2 style="font-size:16px;font-weight:800;color:#111827;">Add Brand</h2>
            <button type="button" style="background:none;border:none;padding:4px;">&#10005;</button>
        </div>
        <div style="padding:20px 24px 40px;">
            <?= filler('Brand field', 7) ?>
            <div style="display:flex;gap:8px;justify-content:flex-end;">
                <button type="button" class="btn btn-secondary">Cancel</button>
                <button type="button" class="btn btn-primary">Save Brand</button>
            </div>
        </div>
    </form>
</div>

<!-- ===== Device viewer panel (public/pages/brand.php) ===== -->
<div id="dv-overlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.6);z-index:10000;"></div>
<div id="dv-panel" style="display:none;position:fixed;top:0;right:0;width:min(700px,95vw);height:100vh;height:100dvh;background:#fff;z-index:10001;box-shadow:-8px 0 30px rgba(0,0,0,0.2);overflow-y:auto;">
    <div style="position:sticky;top:0;background:#fff;border-bottom:1px solid #e5e7eb;padding:16px 24px;display:flex;align-items:center;justify-content:space-between;z-index:1;">
        <h2 id="dv-title" style="font-size:16px;font-weight:800;color:#111827;">Device Details</h2>
        <button type="button" style="background:none;border:none;padding:6px;">&#10005;</button>
    </div>
    <div id="dv-content" style="padding:24px;"><?= filler('Detail', 16) ?></div>
</div>

<!-- ===== Generic overlay + modal-panel (public/pages/admin/users.php) ===== -->
<div id="invite-user-modal" class="modal-overlay" style="display:none;">
    <div class="modal-panel" style="position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);max-width:480px;background:#fff;border-radius:16px;z-index:10001;box-shadow:0 25px 60px rgba(0,0,0,0.3);max-height:90vh;overflow-y:auto;">
        <div style="padding:20px 24px;border-bottom:1px solid #e5e7eb;display:flex;justify-content:space-between;align-items:center;">
            <h2 style="font-size:16px;font-weight:800;color:#111827;">Invite User</h2>
            <button type="button" style="background:none;border:none;padding:4px;">&#10005;</button>
        </div>
        <form id="invite-form" onsubmit="return false">
            <div style="padding:20px 24px;">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px;">
                    <div><label style="display:block;font-size:12px;font-weight:600;">Password *</label><input type="password" style="width:100%;padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;"></div>
                    <div><label style="display:block;font-size:12px;font-weight:600;">Confirm *</label><input type="password" style="width:100%;padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;"></div>
                </div>
                <?= filler('User field', 6) ?>
            </div>
        </form>
    </div>
</div>

<!-- ===== New Ticket modal (public/pages/tickets.php) ===== -->
<div id="new-ticket-modal" class="modal-overlay" style="display:none;">
    <div id="new-ticket-panel" class="modal-panel" style="max-width:560px;background:#fff;border-radius:16px;z-index:10002;box-shadow:0 25px 60px rgba(0,0,0,0.3);max-height:90vh;overflow-y:auto;">
        <div class="modal-header" style="padding:18px 20px;border-bottom:1px solid #e5e7eb;"><h3 style="margin:0;font-size:15px;">New Ticket</h3></div>
        <div id="new-ticket-body" style="padding:18px 20px;"><?= filler('Ticket field', 8) ?></div>
    </div>
</div>

<!-- ===== Side panel from admin/troubleshoot.php (opens with a class) ===== -->
<div class="tm-panel-overlay" id="panel-overlay"></div>
<div class="tm-panel" id="ec-panel">
    <div class="tm-panel-head">
        <h3 id="panel-title">Add Error Code</h3>
        <button class="tm-panel-close" type="button"><span>&#10005;</span></button>
    </div>
    <div class="tm-panel-body">
        <div class="tm-fg"><label class="tm-fl">Error Code *</label><input class="tm-fi" placeholder="e.g., CRITICAL_PROCESS_DIED"></div>
        <?= filler('Error field', 8) ?>
    </div>
</div>

<pre id="checks">running&hellip;</pre>
<script>
(function () {
    var out = document.getElementById('checks');
    var header = document.querySelector('.harness-header');
    var nav = document.querySelector('.harness-nav');

    // Every panel, plus the element inside it that is supposed to scroll.
    // `scroller` is the element app.css hands the scrolling to; null asks the
    // harness to find whichever child actually overflows.
    var panels = [
        { name: 'KB editor panel',   el: 'kb-editor-panel',  scroller: '#kb-editor-form',  legacyBlock: true },
        { name: 'KB viewer panel',   el: 'kb-viewer-panel',  scroller: '#kb-viewer-content', legacyBlock: true },
        { name: 'Equipment editor',  el: 'eq-editor-panel',  scroller: '#eq-editor-form',  legacyBlock: true },
        { name: 'Brand modal',       el: 'brand-modal',      scroller: '#brand-form > div:nth-of-type(2)', legacyBlock: true },
        { name: 'Device viewer',     el: 'dv-panel',         scroller: '#dv-content',      legacyBlock: true },
        { name: 'Invite user modal', el: 'invite-user-modal', scroller: '#invite-form',    legacyBlock: false },
        { name: 'New ticket modal',  el: 'new-ticket-modal', scroller: '#new-ticket-body', legacyBlock: false },
        { name: 'Error-code panel',  el: 'ec-panel',         scroller: null,              legacyBlock: false }
    ];

    function run() {
        var lines = [];
        function check(name, ok, detail) {
            lines.push((ok ? 'PASS ' : 'FAIL ') + name + (detail ? ' (' + detail + ')' : ''));
        }
        var vw = document.documentElement.clientWidth;

        // Above the phone breakpoint these panels are right-hand drawers that
        // scroll themselves, so the expectations below do not apply. Say so
        // rather than reporting a screenful of false failures.
        if (!window.matchMedia('(max-width: 767px)').matches) {
            window.__modalChecks = ['SKIP - open this page at phone width (currently ' + vw + 'px)'];
            out.textContent = 'CHECKS\nSKIP - this checks the phone version of the modal panels.\nCurrent width: ' + vw + 'px.\nNarrow the window to about 390px and it re-runs.';
            return;
        }

        var headerBottom = header.getBoundingClientRect().bottom;
        var navTop = nav.getBoundingClientRect().top;

        check('page has no horizontal overflow', document.documentElement.scrollWidth <= vw + 1,
            'scrollWidth=' + document.documentElement.scrollWidth + ' viewport=' + vw);

        panels.forEach(function (spec) {
            var el = document.getElementById(spec.el);
            if (!el) { check(spec.name + ' exists', false, 'missing #' + spec.el); return; }

            // Open it the way the old code did - and the way openModal() does.
            if (spec.legacyBlock) {
                el.style.display = 'block';
            } else if (el.classList.contains('tm-panel')) {
                el.classList.add('open');
            } else {
                el.style.display = 'flex'; // openModal() sets the overlay, not the panel
            }

            var rect = el.getBoundingClientRect();
            var display = getComputedStyle(el).display;
            check(spec.name + ': opens as a scrollable column', display === 'flex',
                'display=' + display + ' at ' + vw + 'px');
            check(spec.name + ': fits between the top bar and the tab bar',
                rect.top >= headerBottom - 1 && rect.bottom <= navTop + 1,
                'top=' + Math.round(rect.top) + ' bottom=' + Math.round(rect.bottom) +
                ' header=' + Math.round(headerBottom) + ' nav=' + Math.round(navTop));
            check(spec.name + ': never wider than the screen',
                el.scrollWidth <= el.clientWidth + 1 && rect.left >= -1 && rect.right <= vw + 1,
                'scrollWidth=' + el.scrollWidth + ' clientWidth=' + el.clientWidth +
                ' left=' + Math.round(rect.left) + ' right=' + Math.round(rect.right));

            // The scrolling child is the one that overflows; find it if not named.
            var scroller = spec.scroller ? document.querySelector(spec.scroller) : null;
            if (!scroller) {
                var kids = Array.prototype.slice.call(el.querySelectorAll('*'));
                scroller = kids.filter(function (k) {
                    return k.scrollHeight > k.clientHeight + 1 && /auto|scroll/.test(getComputedStyle(k).overflowY);
                })[0] || null;
            }
            if (!scroller) {
                check(spec.name + ': has a scrollable body', false, 'no scroller found');
            } else {
                var travel = scroller.scrollHeight - scroller.clientHeight;
                var before = scroller.scrollTop;
                scroller.scrollTop = scroller.scrollHeight;
                var reached = scroller.scrollTop + scroller.clientHeight >= scroller.scrollHeight - 2;
                scroller.scrollTop = before;
                // Every panel here is built taller than the sheet, so a body that
                // cannot scroll means the rest of it is simply unreachable.
                check(spec.name + ': has a scrollable body', travel > 0 && reached,
                    'travel=' + Math.round(travel) + 'px in ' + (scroller.id ? '#' + scroller.id : scroller.className));
            }

            // Closing must still work: a panel hidden by its own inline display
            // has to stay hidden even though the layout rule forces flex.
            if (spec.legacyBlock) {
                el.style.display = 'none';
                check(spec.name + ': hides again when closed', getComputedStyle(el).display === 'none',
                    'display=' + getComputedStyle(el).display);
            } else if (el.classList.contains('tm-panel')) {
                el.classList.remove('open');
            } else {
                el.style.display = 'none';
            }
        });

        // Dark theme: these panels are built with inline white backgrounds, so
        // check the theme actually reaches them.
        var root = document.documentElement;
        var wasDark = root.classList.contains('dark');
        root.classList.add('dark');
        var kbPanel = document.getElementById('kb-editor-panel');
        kbPanel.style.display = 'block';
        var darkBg = getComputedStyle(kbPanel).backgroundColor;
        var darkParts = (darkBg.match(/\d+/g) || []).map(Number);
        var isDark = darkParts.length >= 3 && (darkParts[0] + darkParts[1] + darkParts[2]) / 3 < 90;
        kbPanel.style.display = 'none';
        root.classList.toggle('dark', wasDark);
        check('panels follow the dark theme instead of staying white', isDark, darkBg);

        out.textContent = 'CHECKS @ ' + vw + 'px\n' + lines.join('\n');
        window.__modalChecks = lines;
    }

    window.__runModalChecks = run;
    window.addEventListener('resize', run);
    window.addEventListener('load', run);
    requestAnimationFrame(run);
})();
</script>
</body>
</html>
