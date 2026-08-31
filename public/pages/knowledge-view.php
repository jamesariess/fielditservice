<?php
if (!defined('APP_ROOT')) { @header('Location: /fielditservice/'); exit; }

$page_title = 'Knowledge Article';
$active_menu = 'knowledge';
require APP_ROOT . '/includes/layout_header.php';

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

if ($article['id'] > 0 && !$demo) {
    try { Database::query("UPDATE knowledge_articles SET use_count = use_count + 1 WHERE id = ?", [$article['id']]); } catch (Exception $e) {}
}
?>

<style>
    .kb-article-shell {
        max-width: 1180px;
        margin: 0 auto;
        display: grid;
        grid-template-columns: minmax(0, 2fr) minmax(260px, 0.9fr);
        gap: 22px;
        align-items: start;
    }
    .kb-article-main, .kb-article-side { min-width: 0; }
    .kb-backlink {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin: 0 0 18px;
        font-size: 13px;
        color: #64748b;
        text-decoration: none;
        transition: color 0.2s ease;
    }
    .kb-backlink:hover { color: #2563eb; }
    .kb-backlink i { width: 14px; height: 14px; }
    .kb-article-hero {
        background: rgba(255,255,255,0.8);
        border: 1px solid rgba(226,232,240,0.9);
        border-radius: 22px;
        padding: 22px 22px 18px;
        box-shadow: 0 18px 36px -28px rgba(15, 23, 42, 0.35);
        margin-bottom: 20px;
    }
    .dark .kb-article-hero {
        background: rgba(15, 23, 42, 0.76);
        border-color: rgba(51,65,85,0.9);
    }
    .kb-article-badges {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        margin-bottom: 14px;
    }
    .kb-article-title {
        font-size: clamp(28px, 3vw, 38px);
        line-height: 1.15;
        letter-spacing: -0.04em;
        margin: 0 0 10px;
        color: #0f172a;
    }
    .dark .kb-article-title { color: #f8fafc; }
    .kb-article-issue {
        margin-top: 12px;
        padding: 14px 16px;
        border-radius: 12px;
        background: linear-gradient(135deg, rgba(37,99,235,0.06), rgba(124,58,237,0.04));
        border-left: 4px solid #2563eb;
        color: #334155;
        font-size: 14px;
        line-height: 1.65;
    }
    .dark .kb-article-issue { color: #dbeafe; }
    .kb-article-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
        margin-top: 18px;
        padding-top: 16px;
        border-top: 1px solid rgba(226,232,240,0.9);
        color: #64748b;
        font-size: 12.5px;
    }
    .dark .kb-article-meta { border-top-color: rgba(51,65,85,0.9); color: #94a3b8; }
    .kb-article-meta span {
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .kb-article-meta i { width: 14px; height: 14px; }
    .kb-section-card {
        background: rgba(255,255,255,0.8);
        border: 1px solid rgba(226,232,240,0.9);
        border-radius: 18px;
        overflow: hidden;
        margin-bottom: 18px;
        box-shadow: 0 14px 24px -20px rgba(15, 23, 42, 0.4);
    }
    .dark .kb-section-card { background: rgba(15,23,42,0.76); border-color: rgba(51,65,85,0.9); }
    .kb-section-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 18px 20px 12px;
        border-bottom: 1px solid #f1f5f9;
    }
    .dark .kb-section-head { border-bottom-color: rgba(51,65,85,0.9); }
    .kb-section-head h3 {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 15px;
        font-weight: 700;
        color: #0f172a;
    }
    .dark .kb-section-head h3 { color: #f8fafc; }
    .kb-section-head h3 i { width: 16px; height: 16px; color: #64748b; }
    .kb-section-body { padding: 18px 20px 20px; }
    .kb-chip-wrap {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }
    .kb-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 10px;
        border-radius: 999px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        color: #475569;
        font-size: 11.5px;
        font-weight: 600;
    }
    .dark .kb-chip {
        background: rgba(15,23,42,0.9);
        border-color: #334155;
        color: #dbeafe;
    }
    .kb-step-list {
        display: grid;
        gap: 12px;
    }
    .kb-step {
        display: flex;
        gap: 12px;
        padding: 14px 12px;
        border-radius: 14px;
        background: linear-gradient(135deg, rgba(248,250,252,0.9), rgba(255,255,255,0.8));
        border: 1px solid #e2e8f0;
    }
    .dark .kb-step {
        background: rgba(15, 23, 42, 0.8);
        border-color: #334155;
    }
    .kb-step-num {
        width: 30px;
        height: 30px;
        border-radius: 10px;
        background: linear-gradient(135deg, #2563eb, #7c3aed);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: 800;
        flex-shrink: 0;
    }
    .kb-step h4 {
        margin: 0 0 4px;
        font-size: 13px;
        color: #0f172a;
    }
    .dark .kb-step h4 { color: #f8fafc; }
    .kb-step p {
        margin: 0;
        font-size: 12.7px;
        color: #64748b;
        line-height: 1.7;
    }
    .dark .kb-step p { color: #94a3b8; }
    .kb-tools-line, .kb-command-line {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }
    .kb-code {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 10px;
        border-radius: 8px;
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
        color: #1d4ed8;
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
    }
    .dark .kb-code {
        background: rgba(15,23,42,0.9);
        border-color: #334155;
        color: #93c5fd;
    }
    .kb-safety {
        display: flex;
        gap: 12px;
        padding: 14px 16px;
        background: linear-gradient(135deg, rgba(254,242,242,0.8), rgba(254,226,226,0.65));
        border: 1px solid #fecaca;
        border-radius: 14px;
        color: #991b1b;
        margin-bottom: 18px;
    }
    .kb-safety i { width: 18px; height: 18px; margin-top: 2px; }
    .kb-safety strong { display: block; margin-bottom: 2px; }
    .kb-side-card {
        position: sticky;
        top: 82px;
        background: rgba(255,255,255,0.8);
        border: 1px solid rgba(226,232,240,0.9);
        border-radius: 18px;
        padding: 18px;
        box-shadow: 0 14px 24px -18px rgba(15, 23, 42, 0.45);
    }
    .dark .kb-side-card { background: rgba(15, 23, 42, 0.76); border-color: rgba(51,65,85,0.9); }
    .kb-side-card h4 {
        margin: 0 0 14px;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #64748b;
    }
    .dark .kb-side-card h4 { color: #94a3b8; }
    .kb-side-stat {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 10px 0;
        border-bottom: 1px solid #f1f5f9;
        color: #334155;
    }
    .dark .kb-side-stat { border-bottom-color: rgba(51,65,85,0.9); color: #dbeafe; }
    .kb-side-stat:last-child { border-bottom: none; }
    .kb-side-stat strong { font-size: 18px; }
    .kb-side-actions {
        display: grid;
        gap: 10px;
        margin-top: 18px;
    }
    .kb-side-actions .btn { width: 100%; justify-content: center; }
    @media (max-width: 980px) {
        .kb-article-shell { grid-template-columns: 1fr; }
        .kb-side-card { position: static; }
    }
</style>

<div class="kb-article-shell">
    <div class="kb-article-main">
        <a href="<?= $urlBase ?>knowledge" class="kb-backlink"><i data-lucide="arrow-left"></i> Back to Knowledge Base</a>

        <div class="kb-article-hero">
            <div class="kb-article-badges">
                <span class="badge badge-blue"><?= e($article['category'] ?? 'General') ?></span>
                <span class="badge badge-green"><i data-lucide="check-circle" style="width:10px;height:10px;"></i> <?= e(ucfirst($article['status'] ?? 'Published')) ?></span>
                <span class="badge badge-gray">v<?= e($article['version'] ?? '1.0') ?></span>
                <?php if (!empty($article['device_type'])): ?>
                    <span class="badge badge-gray"><i data-lucide="monitor" style="width:10px;height:10px;"></i> <?= e($article['device_type']) ?></span>
                <?php endif; ?>
            </div>
            <h1 class="kb-article-title"><?= e($article['title']) ?></h1>
            <?php if (!empty($article['issue'])): ?>
                <div class="kb-article-issue"><?= e($article['issue']) ?></div>
            <?php endif; ?>
            <div class="kb-article-meta">
                <span><i data-lucide="user"></i> <?= e($article['author_name'] ?? 'Admin') ?></span>
                <span><i data-lucide="calendar"></i> <?= e($article['created_at'] ?? '') ?></span>
                <span><i data-lucide="eye"></i> <?= intval($article['use_count'] ?? 0) ?> views</span>
                <?php $helpful = intval($article['helpful_count'] ?? 0); $total = $helpful + intval($article['not_helpful_count'] ?? 0); $rating = $total > 0 ? round(($helpful / $total) * 5, 1) : 5.0; ?>
                <span><i data-lucide="star" style="color:#d97706;"></i> <?= number_format($rating, 1) ?> rating</span>
            </div>
        </div>

        <?php $symptomsRaw = $article['symptoms'] ?? ''; $symptoms = !empty($symptomsRaw) ? preg_split('/[,;|]/', $symptomsRaw) : []; $symptoms = array_map('trim', array_filter($symptoms)); ?>
        <?php if (!empty($symptoms)): ?>
        <div class="kb-section-card">
            <div class="kb-section-head"><h3><i data-lucide="search"></i> Symptoms</h3></div>
            <div class="kb-section-body">
                <div class="kb-chip-wrap">
                    <?php foreach ($symptoms as $s): ?>
                        <span class="kb-chip"><?= e(trim($s)) ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($article['root_cause'])): ?>
        <div class="kb-section-card">
            <div class="kb-section-head"><h3><i data-lucide="target"></i> Root cause</h3></div>
            <div class="kb-section-body"><p style="font-size:13.5px;color:#64748b;line-height:1.8;">
                <?= e($article['root_cause']) ?>
            </p></div>
        </div>
        <?php endif; ?>

        <?php if (!empty($article['solution'])): ?>
        <div class="kb-section-card">
            <div class="kb-section-head">
                <h3><i data-lucide="wrench"></i> Solution steps</h3>
                <button onclick="copySolution()" class="btn btn-secondary btn-sm"><i data-lucide="copy" style="width:12px;height:12px;"></i> Copy</button>
            </div>
            <div class="kb-section-body">
                <div class="kb-step-list">
                    <?php
                    $stepsRaw = $article['solution'] ?? '';
                    $stepLines = preg_split('/[\n|]/', $stepsRaw);
                    $stepNum = 1;
                    foreach ($stepLines as $line) {
                        $line = trim($line);
                        if (empty($line)) continue;
                        if (preg_match('/^([A-Z][\w\s\.]+?):\s*(.+)/', $line, $m)) {
                            $title = trim($m[1]);
                            $desc = trim($m[2]);
                        } else {
                            $title = 'Step ' . $stepNum;
                            $desc = trim($line);
                        }
                    ?>
                        <div class="kb-step">
                            <div class="kb-step-num"><?= $stepNum ?></div>
                            <div>
                                <h4><?= e($title) ?></h4>
                                <p><?= e($desc) ?></p>
                            </div>
                        </div>
                    <?php $stepNum++; } ?>
                </div>
                <div id="solution-text" style="display:none;"><?= e($article['solution']) ?></div>
            </div>
        </div>
        <?php endif; ?>

        <?php $toolsRaw = $article['tools_used'] ?? ''; $tools = !empty($toolsRaw) ? preg_split('/[,;|]/', $toolsRaw) : []; $tools = array_map('trim', array_filter($tools)); ?>
        <?php if (!empty($tools)): ?>
        <div class="kb-section-card">
            <div class="kb-section-head"><h3><i data-lucide="screwdriver"></i> Tools needed</h3></div>
            <div class="kb-section-body">
                <div class="kb-tools-line">
                    <?php foreach ($tools as $t): ?>
                        <span class="kb-chip"><i data-lucide="wrench" style="width:10px;height:10px;"></i><?= e(trim($t)) ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($article['commands_used'])): ?>
        <div class="kb-section-card">
            <div class="kb-section-head"><h3><i data-lucide="terminal"></i> Related commands</h3></div>
            <div class="kb-section-body">
                <div class="kb-command-line">
                    <?php
                    $cmds = preg_split('/[,;|]/', $article['commands_used']);
                    foreach ($cmds as $c):
                        $c = trim($c);
                        if ($c === '') continue;
                    ?>
                        <code class="kb-code" onclick="copyText('<?= e(trim($c)) ?>')"><?= e(trim($c)) ?></code>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($article['safety_warning'])): ?>
        <div class="kb-safety">
            <i data-lucide="shield-alert"></i>
            <div>
                <strong>Safety warning</strong>
                <span><?= e($article['safety_warning']) ?></span>
            </div>
        </div>
        <?php endif; ?>

        <div class="kb-section-card">
            <div class="kb-section-head"><h3><i data-lucide="thumbs-up"></i> Was this helpful?</h3></div>
            <div class="kb-section-body">
                <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:12px;">
                    <button onclick="kbRateArticle('solved')" class="btn btn-success" id="rate-yes"><i data-lucide="check-circle" style="width:15px;height:15px;"></i> Solved</button>
                    <button onclick="kbRateArticle('partial')" class="btn btn-warning" id="rate-partial"><i data-lucide="alert-circle" style="width:15px;height:15px;"></i> Partially</button>
                    <button onclick="kbRateArticle('no')" class="btn btn-danger" id="rate-no"><i data-lucide="x-circle" style="width:15px;height:15px;"></i> Not resolved</button>
                </div>
                <div style="display:flex;gap:8px;flex-wrap:wrap;">
                    <button onclick="kbRateArticle('helpful')" class="btn btn-secondary btn-sm" id="rate-helpful"><i data-lucide="thumbs-up" style="width:13px;height:13px;"></i> Helpful</button>
                    <button onclick="kbRateArticle('not_helpful')" class="btn btn-secondary btn-sm" id="rate-not"><i data-lucide="thumbs-down" style="width:13px;height:13px;"></i> Not helpful</button>
                </div>
                <div id="rating-feedback" style="display:none;margin-top:12px;">
                    <textarea id="rating-comment" placeholder="Optional: tell us why..." class="form-input" rows="2" style="resize:none;"></textarea>
                    <button class="btn btn-primary btn-sm" style="margin-top:8px;" id="fb-submit-btn" onclick="submitFeedback()">Submit feedback</button>
                </div>
            </div>
        </div>
    </div>

    <aside class="kb-article-side">
        <div class="kb-side-card">
            <h4>Article Summary</h4>
            <div class="kb-side-stat"><span>Views</span><strong><?= intval($article['use_count'] ?? 0) ?></strong></div>
            <div class="kb-side-stat"><span>Helpful</span><strong><?= intval($article['helpful_count'] ?? 0) ?></strong></div>
            <div class="kb-side-stat"><span>Success</span><strong><?= intval($article['success_count'] ?? 0) ?>%</strong></div>
            <div class="kb-side-stat"><span>Updated</span><strong><?= date('M j', strtotime($article['created_at'] ?? date('Y-m-d'))) ?></strong></div>
            <div class="kb-side-actions">
                <button onclick="copySolution()" class="btn btn-primary"><i data-lucide="copy" style="width:15px;height:15px;"></i> Copy solution</button>
                <a href="<?= $urlBase ?>knowledge" class="btn btn-secondary"><i data-lucide="book-open" style="width:15px;height:15px;"></i> Browse KB</a>
            </div>
        </div>
    </aside>
</div>

<script>
var articleId = <?= intval($article['id']) ?>;

function copyText(value) {
    navigator.clipboard.writeText(value).then(function() {
        showToast('Copied to clipboard!', 'success');
    }).catch(function() {
        showToast('Copy failed', 'error');
    });
}

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
    copyText(text);
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
