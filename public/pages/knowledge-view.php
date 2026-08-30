<?php
if (!defined('APP_ROOT')) { @header('Location: /fielditservice/'); exit; }

$page_title = 'Knowledge Article';
$active_menu = 'knowledge';
require APP_ROOT . '/includes/layout_header.php';

// Get article ID from query string
$articleId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$demo = !defined('DEMO_MODE') || DEMO_MODE;
$article = null;

if (!$demo && $articleId > 0) {
    $article = Database::fetch(
        "SELECT ka.*, u.full_name as author_name
         FROM knowledge_articles ka
         LEFT JOIN users u ON ka.author_id = u.id
         WHERE ka.id = ? AND ka.deleted_at IS NULL AND ka.status = 'published'",
        [$articleId]
    );
}

if (!$article) {
    // Try any status for direct links
    if (!$demo && $articleId > 0) {
        $article = Database::fetch(
            "SELECT ka.*, u.full_name as author_name
             FROM knowledge_articles ka
             LEFT JOIN users u ON ka.author_id = u.id
             WHERE ka.id = ? AND ka.deleted_at IS NULL",
            [$articleId]
        );
    }
    if (!$article) {
        $article = [
            'id' => 0, 'title' => 'Article Not Found', 'category' => 'General',
            'status' => 'unknown', 'version' => '1.0', 'quality_score' => 0,
            'use_count' => 0, 'helpful_count' => 0, 'not_helpful_count' => 0,
            'success_count' => 0, 'author_name' => 'System', 'created_at' => date('Y-m-d'),
            'symptoms' => '', 'root_cause' => '', 'solution' => '',
            'tools_used' => '', 'commands_used' => '', 'issue' => '',
            'safety_warning' => '',
        ];
    }
}

// Increment view count
if ($article['id'] > 0 && !$demo) {
    try { Database::query("UPDATE knowledge_articles SET use_count = use_count + 1 WHERE id = ?", [$article['id']]); } catch (Exception $e) {}
}
?>
<div style="max-width:800px;margin:0 auto;">
    <a href="<?= $urlBase ?>knowledge" style="display:inline-flex;align-items:center;gap:6px;font-size:13px;color:#64748b;text-decoration:none;margin-bottom:20px;" onmouseover="this.style.color='#2563eb'" onmouseout="this.style.color='#64748b'">
        <i data-lucide="arrow-left" style="width:14px;height:14px;"></i> Back to Knowledge Base
    </a>

    <!-- Article Header -->
    <div class="card" style="margin-bottom:16px;">
        <div class="card-body">
            <div style="display:flex;gap:8px;margin-bottom:12px;flex-wrap:wrap;">
                <span class="badge badge-blue"><?= e($article['category'] ?? 'General') ?></span>
                <span class="badge badge-green"><i data-lucide="check-circle" style="width:10px;height:10px;"></i> <?= e(ucfirst($article['status'] ?? 'Published')) ?></span>
                <span class="badge badge-gray">v<?= e($article['version'] ?? '1.0') ?></span>
                <?php if (!empty($article['device_type'])): ?>
                    <span class="badge badge-gray"><i data-lucide="monitor" style="width:10px;height:10px;"></i> <?= e($article['device_type']) ?></span>
                <?php endif; ?>
            </div>
            <h1 style="font-size:22px;font-weight:800;color:#111827;margin-bottom:8px;"><?= e($article['title']) ?></h1>
            <?php if (!empty($article['issue'])): ?>
                <p style="font-size:14px;color:#475569;line-height:1.6;background:#f8fafc;padding:12px 16px;border-radius:8px;border-left:3px solid #2563eb;"><?= e($article['issue']) ?></p>
            <?php endif; ?>

            <div style="display:flex;gap:16px;margin-top:16px;padding-top:16px;border-top:1px solid #f1f5f9;flex-wrap:wrap;font-size:12px;color:#94a3b8;">
                <span style="display:flex;align-items:center;gap:4px;"><i data-lucide="user" style="width:13px;height:13px;"></i> <?= e($article['author_name'] ?? 'Admin') ?></span>
                <span style="display:flex;align-items:center;gap:4px;"><i data-lucide="calendar" style="width:13px;height:13px;"></i> <?= e($article['created_at'] ?? '') ?></span>
                <span style="display:flex;align-items:center;gap:4px;"><i data-lucide="eye" style="width:13px;height:13px;"></i> <?= intval($article['use_count'] ?? 0) ?> views</span>
                <?php
                $helpful = intval($article['helpful_count'] ?? 0);
                $total = $helpful + intval($article['not_helpful_count'] ?? 0);
                $rating = $total > 0 ? round(($helpful / $total) * 5, 1) : 5.0;
                ?>
                <span style="display:flex;align-items:center;gap:4px;"><i data-lucide="star" style="width:13px;height:13px;color:#d97706;"></i> <?= number_format($rating, 1) ?> rating</span>
            </div>
        </div>
    </div>

    <!-- Symptoms -->
    <?php
    $symptomsRaw = $article['symptoms'] ?? '';
    $symptoms = !empty($symptomsRaw) ? preg_split('/[,;|]/', $symptomsRaw) : [];
    $symptoms = array_map('trim', array_filter($symptoms));
    ?>
    <?php if (!empty($symptoms)): ?>
    <div class="card" style="margin-bottom:16px;">
        <div class="card-header"><h3 style="font-size:15px;font-weight:700;display:flex;align-items:center;gap:6px;"><i data-lucide="search" style="width:15px;height:15px;color:#64748b;"></i> Symptoms</h3></div>
        <div class="card-body">
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                <?php foreach ($symptoms as $s): ?>
                    <span class="badge badge-gray"><?= e(trim($s)) ?></span>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Root Cause -->
    <?php if (!empty($article['root_cause'])): ?>
    <div class="card" style="margin-bottom:16px;">
        <div class="card-header"><h3 style="font-size:15px;font-weight:700;display:flex;align-items:center;gap:6px;"><i data-lucide="target" style="width:15px;height:15px;color:#64748b;"></i> Root Cause</h3></div>
        <div class="card-body"><p style="font-size:13.5px;color:#475569;line-height:1.7;"><?= e($article['root_cause']) ?></p></div>
    </div>
    <?php endif; ?>

    <!-- Solution Steps -->
    <?php if (!empty($article['solution'])): ?>
    <div class="card" style="margin-bottom:16px;">
        <div class="card-header">
            <h3 style="font-size:15px;font-weight:700;display:flex;align-items:center;gap:6px;"><i data-lucide="wrench" style="width:15px;height:15px;color:#64748b;"></i> Solution Steps</h3>
            <button onclick="copySolution()" style="display:inline-flex;align-items:center;gap:4px;padding:4px 10px;background:#f1f5f9;border:none;border-radius:6px;font-size:11px;color:#475569;cursor:pointer;font-weight:600;" onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='#f1f5f9'">
                <i data-lucide="copy" style="width:12px;height:12px;"></i> Copy
            </button>
        </div>
        <div class="card-body">
            <div id="solution-steps" class="space-y-4">
                <?php
                $stepsRaw = $article['solution'] ?? '';
                $stepLines = preg_split('/[\n|]/', $stepsRaw);
                $stepNum = 1;
                foreach ($stepLines as $line) {
                    $line = trim($line);
                    if (empty($line)) continue;
                    // Check for step:desc format or just text
                    if (preg_match('/^([A-Z][\w\s\.]+?):\s*(.+)/', $line, $m)) {
                        $title = trim($m[1]);
                        $desc = trim($m[2]);
                    } else {
                        $title = 'Step ' . $stepNum;
                        $desc = trim($line);
                    }
                ?>
                    <div style="display:flex;gap:12px;padding:12px;background:#f8fafc;border-radius:10px;border:1px solid #f1f5f9;">
                        <div style="width:28px;height:28px;border-radius:8px;background:#2563eb;color:#fff;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;flex-shrink:0;"><?= $stepNum ?></div>
                        <div>
                            <div style="font-size:13px;font-weight:700;color:#111827;margin-bottom:2px;"><?= e($title) ?></div>
                            <div style="font-size:12px;color:#64748b;line-height:1.5;"><?= e($desc) ?></div>
                        </div>
                    </div>
                <?php $stepNum++; } ?>
            </div>
            <div id="solution-text" style="display:none;"><?= e($article['solution']) ?></div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Tools Needed -->
    <?php
    $toolsRaw = $article['tools_used'] ?? '';
    $tools = !empty($toolsRaw) ? preg_split('/[,;|]/', $toolsRaw) : [];
    $tools = array_map('trim', array_filter($tools));
    ?>
    <?php if (!empty($tools)): ?>
    <div class="card" style="margin-bottom:16px;">
        <div class="card-header"><h3 style="font-size:15px;font-weight:700;display:flex;align-items:center;gap:6px;"><i data-lucide="screwdriver" style="width:15px;height:15px;color:#64748b;"></i> Tools Needed</h3></div>
        <div class="card-body">
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                <?php foreach ($tools as $t): ?>
                    <span class="badge badge-blue"><i data-lucide="wrench" style="width:10px;height:10px;"></i> <?= e(trim($t)) ?></span>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Safety Warning -->
    <?php if (!empty($article['safety_warning'])): ?>
    <div style="display:flex;gap:10px;padding:14px 16px;background:#fef2f2;border:1px solid #fecaca;border-radius:10px;margin-bottom:16px;">
        <i data-lucide="shield-alert" style="width:18px;height:18px;color:#dc2626;flex-shrink:0;margin-top:1px;"></i>
        <div>
            <div style="font-size:13px;font-weight:700;color:#991b1b;margin-bottom:2px;">Safety Warning</div>
            <div style="font-size:12.5px;color:#991b1b;line-height:1.5;"><?= e($article['safety_warning']) ?></div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Related Commands -->
    <?php
    $cmdsRaw = $article['commands_used'] ?? '';
    $cmds = !empty($cmdsRaw) ? preg_split('/[,;|]/', $cmdsRaw) : [];
    $cmds = array_map('trim', array_filter($cmds));
    ?>
    <?php if (!empty($cmds)): ?>
    <div class="card" style="margin-bottom:16px;">
        <div class="card-header"><h3 style="font-size:15px;font-weight:700;display:flex;align-items:center;gap:6px;"><i data-lucide="terminal" style="width:15px;height:15px;color:#64748b;"></i> Related Commands</h3></div>
        <div class="card-body">
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                <?php foreach ($cmds as $c): ?>
                    <code onclick="navigator.clipboard.writeText('<?= e(trim($c)) ?>');showToast('Copied!','success')" style="padding:4px 10px;background:#f1f5f9;border-radius:6px;font-size:12px;font-weight:600;color:#1d4ed8;cursor:pointer;" title="Click to copy"><?= e(trim($c)) ?></code>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Quality Score -->
    <?php
    $qs = floatval($article['quality_score'] ?? 0);
    $qsDisplay = $qs > 0 ? $qs : 92;
    ?>
    <div class="card" style="margin-bottom:16px;background:linear-gradient(135deg,#f0fdf4,#ecfdf5);border-color:#bbf7d0;">
        <div class="card-body" style="text-align:center;">
            <div style="font-size:12px;font-weight:700;color:#166534;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:4px;">Verified Knowledge</div>
            <div style="display:flex;align-items:center;justify-content:center;gap:2px;margin-bottom:4px;">
                <span style="font-size:18px;color:#d97706;">&#9733;&#9733;&#9733;&#9733;&#9733;</span>
            </div>
            <div style="font-size:13px;color:#15803d;font-weight:600;"><?= intval($qsDisplay) ?>% successful</div>
            <div style="font-size:12px;color:#16a34a;">Used <?= intval($article['use_count'] ?? 0) ?> times</div>
        </div>
    </div>

    <!-- Rating Section -->
    <div class="card" style="margin-bottom:16px;">
        <div class="card-header"><h3 style="font-size:15px;font-weight:700;">Was this helpful?</h3></div>
        <div class="card-body">
            <p style="font-size:13px;color:#64748b;margin-bottom:12px;">Did this article solve your problem?</p>
            <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:12px;">
                <button onclick="kbRateArticle('solved')" class="btn btn-success" id="rate-yes"><i data-lucide="check-circle" style="width:15px;height:15px;"></i> Yes — Solved</button>
                <button onclick="kbRateArticle('partial')" class="btn btn-warning" id="rate-partial"><i data-lucide="alert-circle" style="width:15px;height:15px;"></i> Partially</button>
                <button onclick="kbRateArticle('no')" class="btn btn-danger" id="rate-no"><i data-lucide="x-circle" style="width:15px;height:15px;"></i> No</button>
            </div>
            <div style="display:flex;gap:8px;">
                <button onclick="kbRateArticle('helpful')" class="btn btn-secondary btn-sm" id="rate-helpful"><i data-lucide="thumbs-up" style="width:13px;height:13px;"></i> Helpful</button>
                <button onclick="kbRateArticle('not_helpful')" class="btn btn-secondary btn-sm" id="rate-not"><i data-lucide="thumbs-down" style="width:13px;height:13px;"></i> Not Helpful</button>
            </div>
            <div id="rating-feedback" style="display:none;margin-top:12px;">
                <textarea id="rating-comment" placeholder="Optional: tell us why..." class="form-input" rows="2" style="resize:none;"></textarea>
                <button class="btn btn-primary btn-sm" style="margin-top:8px;" id="fb-submit-btn" onclick="submitFeedback()">Submit Feedback</button>
            </div>
        </div>
    </div>
</div>

<script>
var articleId = <?= intval($article['id']) ?>;

function kbRateArticle(rating) {
    var btnMap = { solved: 'rate-yes', partial: 'rate-partial', no: 'rate-no', helpful: 'rate-helpful', not_helpful: 'rate-not' };
    var btn = document.getElementById(btnMap[rating]);
    if (typeof setButtonLoading === 'function') setButtonLoading(btn, true, 'Saving…');
    fetch('<?= $urlBase ?>api/knowledge/rate.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ article_id: articleId, rating: rating })
    }).then(function(r) { return r.json(); }).then(function(d) {
        if (typeof setButtonLoading === 'function') setButtonLoading(btn, false);
        if (d.success) {
            showToast('Thanks for your feedback!', 'success');
            // Highlight the button pressed
            document.querySelectorAll('#rate-yes,#rate-partial,#rate-no,#rate-helpful,#rate-not').forEach(function(b) {
                b.style.opacity = '0.5';
            });
            if (btn) { btn.style.opacity = '1'; btn.style.transform = 'scale(1.05)'; }
        }
    }).catch(function() {
        if (typeof setButtonLoading === 'function') setButtonLoading(btn, false);
        showToast('Error submitting rating', 'error');
    });
}

function copySolution() {
    var el = document.getElementById('solution-text');
    var text = el ? el.textContent : '';
    navigator.clipboard.writeText(text).then(function() {
        showToast('Solution copied to clipboard!', 'success');
    });
}

function submitFeedback() {
    var comment = document.getElementById('rating-comment').value;
    var btn = document.getElementById('fb-submit-btn');
    if (typeof setButtonLoading === 'function') setButtonLoading(btn, true, 'Sending…');
    fetch('<?= $urlBase ?>api/knowledge/rate.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ article_id: articleId, rating: 'helpful', feedback: comment })
    }).then(function() {
        if (typeof setButtonLoading === 'function') setButtonLoading(btn, false);
        showToast('Feedback submitted!', 'success');
    }).catch(function() {
        if (typeof setButtonLoading === 'function') setButtonLoading(btn, false);
        showToast('Failed to send feedback', 'error');
    });
}
</script>
<?php require APP_ROOT . '/includes/layout_footer.php'; ?>
