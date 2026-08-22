<?php
$page_title = 'Knowledge Article';
$active_menu = 'knowledge';
require APP_ROOT . '/includes/layout_header.php';

// Get article ID from query string
$articleId = isset($_GET['id']) ? intval($_GET['id']) : 1;
$demo = !defined('DEMO_MODE') || DEMO_MODE;
$article = null;

if (!$demo) {
    $db = Database::getInstance();
    $article = $db->fetch("SELECT ka.*, u.full_name as author_name FROM knowledge_articles ka LEFT JOIN users u ON ka.author_id = u.id WHERE ka.id = ?", [$articleId]);
}

// Demo data fallback
if (!$article) {
    $articles = DemoData::getKnowledgeArticles();
    $article = $articles[0] ?? [
        'id' => 1, 'title' => 'No Display — Desktop Troubleshooting', 'content' => '',
        'category' => 'Hardware', 'status' => 'published', 'quality_score' => 92,
        'use_count' => 137, 'avg_rating' => 4.8, 'author_name' => 'Admin',
        'created_at' => '2026-08-15', 'version' => '1.0',
        'symptoms' => 'Black screen, No signal message, Monitor LED on but no image, Flickering display',
        'root_cause' => 'Most commonly caused by loose display cables, improperly seated RAM, or monitor input source misconfiguration.',
        'solution_steps' => 'Check Power:Verify both computer and monitor have power.|Reseat Display Cable:Disconnect and reconnect HDMI/DisplayPort/VGA cable.|Test Different Cable:Try a known-good display cable.|Test Different Monitor:Connect a known-good monitor.|Reseat RAM:Power off, remove and reseat RAM modules.|Reseat GPU:Power off, remove and reinsert the graphics card.',
        'tools_needed' => 'Known-good monitor, Known-good cable, Phillips screwdriver, ESD wrist strap',
        'safety_warning' => 'Turn off and disconnect power before opening the computer case. Use ESD protection when handling internal components.',
        'related_commands' => 'systeminfo,msinfo32',
        'success_rate' => 92, 'views' => 137
    ];
}
?>
<div style="max-width:800px;margin:0 auto;">
    <a href="/knowledge" style="display:inline-flex;align-items:center;gap:6px;font-size:13px;color:#64748b;text-decoration:none;margin-bottom:20px;" onmouseover="this.style.color='#2563eb'" onmouseout="this.style.color='#64748b'">
        <i data-lucide="arrow-left" style="width:14px;height:14px;"></i> Back to Knowledge Base
    </a>

    <!-- Article Header -->
    <div class="card" style="margin-bottom:16px;">
        <div class="card-body">
            <div style="display:flex;gap:8px;margin-bottom:12px;flex-wrap:wrap;">
                <span class="badge badge-blue"><?= e($article['category'] ?? 'General') ?></span>
                <span class="badge badge-green"><i data-lucide="check-circle" style="width:10px;height:10px;"></i> <?= e($article['status'] ?? 'Published') ?></span>
                <span class="badge badge-gray">v<?= e($article['version'] ?? '1.0') ?></span>
            </div>
            <h1 style="font-size:22px;font-weight:800;color:#111827;margin-bottom:8px;"><?= e($article['title']) ?></h1>
            <p style="font-size:14px;color:#64748b;line-height:1.6;">Complete step-by-step guide for diagnosing and resolving this issue.</p>

            <div style="display:flex;gap:16px;margin-top:16px;padding-top:16px;border-top:1px solid #f1f5f9;flex-wrap:wrap;font-size:12px;color:#94a3b8;">
                <span style="display:flex;align-items:center;gap:4px;"><i data-lucide="user" style="width:13px;height:13px;"></i> <?= e($article['author_name'] ?? 'Admin') ?></span>
                <span style="display:flex;align-items:center;gap:4px;"><i data-lucide="calendar" style="width:13px;height:13px;"></i> <?= e($article['created_at'] ?? 'Aug 15, 2026') ?></span>
                <span style="display:flex;align-items:center;gap:4px;"><i data-lucide="eye" style="width:13px;height:13px;"></i> <?= intval($article['views'] ?? $article['use_count'] ?? 0) ?> views</span>
                <span style="display:flex;align-items:center;gap:4px;"><i data-lucide="star" style="width:13px;height:13px;color:#d97706;"></i> <?= number_format($article['avg_rating'] ?? 4.8, 1) ?> rating</span>
            </div>
        </div>
    </div>

    <!-- Symptoms -->
    <?php $symptoms = explode(', ', $article['symptoms'] ?? 'Black screen, No signal, Monitor LED on'); ?>
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

    <!-- Root Cause -->
    <div class="card" style="margin-bottom:16px;">
        <div class="card-header"><h3 style="font-size:15px;font-weight:700;display:flex;align-items:center;gap:6px;"><i data-lucide="target" style="width:15px;height:15px;color:#64748b;"></i> Root Cause</h3></div>
        <div class="card-body"><p style="font-size:13.5px;color:#475569;line-height:1.7;"><?= e($article['root_cause'] ?? 'Commonly caused by configuration or connection issues.') ?></p></div>
    </div>

    <!-- Solution Steps -->
    <div class="card" style="margin-bottom:16px;">
        <div class="card-header"><h3 style="font-size:15px;font-weight:700;display:flex;align-items:center;gap:6px;"><i data-lucide="wrench" style="width:15px;height:15px;color:#64748b;"></i> Solution Steps</h3></div>
        <div class="card-body">
            <div class="space-y-4">
                <?php
                $stepsRaw = $article['solution_steps'] ?? 'Check Power:Verify both computer and monitor have power.|Reseat Display Cable:Disconnect and reconnect HDMI cable.|Test Different Cable:Try a known-good display cable.|Test Different Monitor:Connect a known-good monitor.|Reseat RAM:Power off, remove and reseat RAM.|Reseat GPU:Power off, remove and reinsert GPU.';
                $stepLines = explode('|', $stepsRaw);
                $stepNum = 1;
                foreach ($stepLines as $line) {
                    $parts = explode(':', $line, 2);
                    $title = $parts[0] ?? 'Step ' . $stepNum;
                    $desc = $parts[1] ?? '';
                    $risk = in_array($stepNum, [5, 6]) ? 'caution' : 'safe';
                    $riskColor = $risk === 'safe' ? '#16a34a' : '#d97706';
                ?>
                    <div style="display:flex;gap:12px;padding:12px;background:#f8fafc;border-radius:10px;border:1px solid #f1f5f9;">
                        <div style="width:28px;height:28px;border-radius:8px;background:#2563eb;color:#fff;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;flex-shrink:0;"><?= $stepNum ?></div>
                        <div>
                            <div style="font-size:13px;font-weight:700;color:#111827;margin-bottom:2px;"><?= e($title) ?></div>
                            <div style="font-size:12px;color:#64748b;line-height:1.5;"><?= e($desc) ?></div>
                            <div style="margin-top:4px;"><span class="risk-dot <?= $risk ?>" style="display:inline-block;"></span> <span style="font-size:10px;color:<?= $riskColor ?>;font-weight:600;text-transform:uppercase;"><?= $risk ?></span></div>
                        </div>
                    </div>
                <?php $stepNum++; } ?>
            </div>
        </div>
    </div>

    <!-- Tools Needed -->
    <?php $tools = explode(', ', $article['tools_needed'] ?? 'Phillips screwdriver, ESD wrist strap'); ?>
    <div class="card" style="margin-bottom:16px;">
        <div class="card-header"><h3 style="font-size:15px;font-weight:700;display:flex;align-items:center;gap:6px;"><i data-lucide="screwdriver" style="width:15px;height:15px;color:#64748b;"></i> Tools Needed</h3></div>
        <div class="card-body">
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                <?php foreach ($tools as $t): ?>
                    <span class="badge badge-blue"><?= e(trim($t)) ?></span>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Safety Warnings -->
    <?php if (!empty($article['safety_warning'])): ?>
    <div class="alert alert-warning" style="margin-bottom:16px;">
        <i data-lucide="shield-alert"></i>
        <div><b>Safety:</b> <?= e($article['safety_warning']) ?></div>
    </div>
    <?php endif; ?>

    <!-- Related Commands -->
    <?php $cmds = explode(',', $article['related_commands'] ?? 'systeminfo'); ?>
    <div class="card" style="margin-bottom:16px;">
        <div class="card-header"><h3 style="font-size:15px;font-weight:700;display:flex;align-items:center;gap:6px;"><i data-lucide="terminal" style="width:15px;height:15px;color:#64748b;"></i> Related Commands</h3></div>
        <div class="card-body">
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                <?php foreach ($cmds as $c): ?>
                    <code style="padding:4px 10px;background:#f1f5f9;border-radius:6px;font-size:12px;font-weight:600;color:#1d4ed8;"><?= e(trim($c)) ?></code>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Quality Score -->
    <div class="card" style="margin-bottom:16px;background:linear-gradient(135deg,#f0fdf4,#ecfdf5);border-color:#bbf7d0;">
        <div class="card-body" style="text-align:center;">
            <div style="font-size:12px;font-weight:700;color:#166534;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:4px;">Verified Knowledge</div>
            <div style="display:flex;align-items:center;justify-content:center;gap:2px;margin-bottom:4px;">
                <span style="font-size:18px;color:#d97706;">&#9733;&#9733;&#9733;&#9733;&#9733;</span>
            </div>
            <div style="font-size:13px;color:#15803d;font-weight:600;"><?= intval($article['success_rate'] ?? $article['quality_score'] ?? 92) ?>% successful</div>
            <div style="font-size:12px;color:#16a34a;">Used <?= intval($article['views'] ?? $article['use_count'] ?? 0) ?> times · Last reviewed: <?= e($article['created_at'] ?? 'Aug 15, 2026') ?></div>
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
                <button onclick="kbRateArticle('nothelpful')" class="btn btn-secondary btn-sm" id="rate-not"><i data-lucide="thumbs-down" style="width:13px;height:13px;"></i> Not Helpful</button>
            </div>
            <div id="rating-feedback" style="display:none;margin-top:12px;">
                <textarea placeholder="Optional: tell us why..." class="form-input" rows="2" style="resize:none;"></textarea>
                <button class="btn btn-primary btn-sm" style="margin-top:8px;" onclick="showToast('Feedback submitted!','success')">Submit Feedback</button>
            </div>
        </div>
    </div>

    <!-- Related Articles -->
    <div class="card" style="margin-bottom:16px;">
        <div class="card-header"><h3 style="font-size:15px;font-weight:700;display:flex;align-items:center;gap:6px;"><i data-lucide="link" style="width:15px;height:15px;color:#64748b;"></i> Related Articles</h3></div>
        <div class="card-body">
            <div class="space-y-2">
                <a href="/knowledge/view?id=2" style="display:flex;align-items:center;gap:8px;padding:8px;border-radius:8px;text-decoration:none;transition:background 0.15s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
                    <i data-lucide="file-text" style="width:14px;height:14px;color:#64748b;"></i>
                    <span style="font-size:13px;color:#2563eb;font-weight:500;">Printer Offline — HP LaserJet Troubleshooting</span>
                </a>
                <a href="/knowledge/view?id=3" style="display:flex;align-items:center;gap:8px;padding:8px;border-radius:8px;text-decoration:none;transition:background 0.15s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
                    <i data-lucide="file-text" style="width:14px;height:14px;color:#64748b;"></i>
                    <span style="font-size:13px;color:#2563eb;font-weight:500;">WiFi Connectivity Troubleshooting</span>
                </a>
                <a href="/knowledge/view?id=4" style="display:flex;align-items:center;gap:8px;padding:8px;border-radius:8px;text-decoration:none;transition:background 0.15s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
                    <i data-lucide="file-text" style="width:14px;height:14px;color:#64748b;"></i>
                    <span style="font-size:13px;color:#2563eb;font-weight:500;">RAM Reseat Procedure</span>
                </a>
            </div>
        </div>
    </div>
</div>

<?php require APP_ROOT . '/includes/layout_footer.php'; ?>
