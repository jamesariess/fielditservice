<?php
if (!defined('APP_ROOT')) { @header('Location: /fielditservice/'); exit; }

$page_title = 'Knowledge Base';
$active_menu = 'knowledge';
require APP_ROOT . '/includes/layout_header.php';

// Fetch published articles from database
$articles = Database::fetchAll(
    "SELECT ka.id, ka.title, ka.category, ka.status, ka.quality_score, ka.use_count,
            ka.symptoms, ka.issue, ka.created_at, ka.helpful_count,
            u.full_name as author_name
     FROM knowledge_articles ka
     LEFT JOIN users u ON ka.author_id = u.id
     WHERE ka.status = 'published' AND ka.deleted_at IS NULL
     ORDER BY ka.helpful_count DESC, ka.use_count DESC, ka.created_at DESC"
);

$countPublished = count($articles);

// Category colors
$catColors = [
    'hardware' => ['bg' => '#eff6ff', 'fg' => '#2563eb'],
    'software' => ['bg' => '#faf5ff', 'fg' => '#9333ea'],
    'network'  => ['bg' => '#f0fdf4', 'fg' => '#16a34a'],
    'printer'  => ['bg' => '#fff7ed', 'fg' => '#ea580c'],
    'cctv'     => ['bg' => '#f8fafc', 'fg' => '#475569'],
    'display'  => ['bg' => '#eff6ff', 'fg' => '#2563eb'],
    'power'    => ['bg' => '#fef2f2', 'fg' => '#dc2626'],
    'audio'    => ['bg' => '#fefce8', 'fg' => '#ca8a04'],
];
$defaultCat = ['bg' => '#f1f5f9', 'fg' => '#475569'];

// Category icons
$catIcons = [
    'hardware' => 'cpu', 'software' => 'monitor', 'network' => 'wifi',
    'printer' => 'printer', 'cctv' => 'camera', 'display' => 'monitor',
    'power' => 'zap', 'audio' => 'volume-2',
];
?>

<style>
    .kb-shell {
        max-width: 1280px;
        margin: 0 auto;
    }
    .kb-hero {
        position: relative;
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 20px;
        flex-wrap: wrap;
        padding: 18px 6px 24px;
        margin-bottom: 8px;
    }
    .kb-hero::after {
        content: '';
        position: absolute;
        left: 0;
        bottom: 0;
        width: 110px;
        height: 4px;
        border-radius: 999px;
        background: linear-gradient(90deg, #2563eb, #7c3aed);
    }
    .kb-modal { display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.6); backdrop-filter: blur(6px); z-index: 10000; align-items: center; justify-content: center; }
    .kb-modal.active { display: flex; }
    .kb-modal-content { background: #fff; border-radius: 18px; max-width: 700px; width: 95vw; max-height: 90vh; overflow-y: auto; box-shadow: 0 25px 60px rgba(0,0,0,0.3); position: relative; z-index: 1; }
    .dark .kb-modal-content { background: rgba(15,23,42,0.95); }
    .kb-modal-header { display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 20px 24px; border-bottom: 1px solid #f1f5f9; position: sticky; top: 0; background: #fff; }
    .dark .kb-modal-header { background: rgba(15,23,42,0.95); border-bottom-color: rgba(51,65,85,0.9); }
    .kb-modal-body { padding: 24px; }
    .kb-form-group { margin-bottom: 16px; }
    .kb-form-label { display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 6px; }
    .dark .kb-form-label { color: #dbeafe; }
    .kb-form-input { width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 10px; font-size: 13px; color: #0f172a; }
    .dark .kb-form-input { background: rgba(15,23,42,0.8); border-color: #334155; color: #f8fafc; }
    .kb-form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    @media (max-width: 768px) { .kb-form-grid { grid-template-columns: 1fr; } }
    .kb-hero-copy {
        display: flex;
        align-items: center;
        gap: 16px;
    }
    .kb-hero-icon {
        width: 58px;
        height: 58px;
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #eff6ff, #dbeafe);
        color: #2563eb;
        box-shadow: 0 10px 30px -16px rgba(37, 99, 235, 0.9);
    }
    .kb-hero-icon i { width: 28px; height: 28px; }
    .kb-hero-title {
        font-size: clamp(28px, 4vw, 38px);
        font-weight: 800;
        letter-spacing: -0.04em;
        color: #0f172a;
        line-height: 1.12;
    }
    .dark .kb-hero-title { color: #f8fafc; }
    .kb-hero-sub {
        margin-top: 6px;
        font-size: 14px;
        color: #64748b;
        max-width: 640px;
    }
    .dark .kb-hero-sub { color: #94a3b8; }
    .kb-filter-wrap {
        background: rgba(255,255,255,0.7);
        border: 1px solid rgba(226, 232, 240, 0.9);
        border-radius: 18px;
        backdrop-filter: blur(10px);
        box-shadow: 0 12px 28px -20px rgba(15, 23, 42, 0.35);
        margin-bottom: 20px;
        overflow: hidden;
    }
    .dark .kb-filter-wrap { background: rgba(15, 23, 42, 0.72); border-color: rgba(51,65,85,0.9); }
    .kb-filter-inner {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        padding: 16px;
        align-items: center;
    }
    .kb-search {
        position: relative;
        flex: 1;
        min-width: 240px;
    }
    .kb-search i {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        width: 16px;
        height: 16px;
        color: #94a3b8;
    }
    .kb-search input {
        width: 100%;
        padding: 12px 16px 12px 42px;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        color: #0f172a;
        font-size: 14px;
        outline: none;
        transition: all 0.2s ease;
    }
    .kb-search input:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 4px rgba(37,99,235,0.12);
        background: #fff;
    }
    .dark .kb-search input {
        background: #0f172a;
        color: #f8fafc;
        border-color: #334155;
    }
    .kb-select {
        min-width: 170px;
        padding: 12px 14px;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        font-size: 13px;
        color: #334155;
        outline: none;
    }
    .dark .kb-select {
        background: #0f172a;
        border-color: #334155;
        color: #e2e8f0;
    }
    .kb-stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(180px, 1fr));
        gap: 12px;
        margin: 8px 0 22px;
    }
    .kb-stat {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 16px 18px;
        border-radius: 16px;
        background: rgba(255,255,255,0.75);
        border: 1px solid rgba(226, 232, 240, 0.9);
        box-shadow: 0 8px 25px -22px rgba(15, 23, 42, 0.35);
    }
    .dark .kb-stat { background: rgba(15, 23, 42, 0.7); border-color: rgba(51,65,85,0.9); }
    .kb-stat-icon {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .kb-stat-icon.blue { background: #eff6ff; color: #2563eb; }
    .kb-stat-icon.green { background: #f0fdf4; color: #16a34a; }
    .kb-stat-icon.yellow { background: #fffbeb; color: #d97706; }
    .kb-stat-icon.purple { background: #faf5ff; color: #9333ea; }
    .kb-stat-value {
        font-size: 22px;
        font-weight: 800;
        letter-spacing: -0.03em;
        color: #0f172a;
    }
    .dark .kb-stat-value { color: #f8fafc; }
    .kb-stat-label {
        font-size: 11px;
        color: #64748b;
        margin-top: 2px;
    }
    .dark .kb-stat-label { color: #94a3b8; }
    .kb-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(310px, 1fr));
        gap: 16px;
    }
    .kb-card {
        display: flex;
        flex-direction: column;
        background: rgba(255,255,255,0.8);
        border: 1px solid rgba(226,232,240,0.9);
        border-radius: 18px;
        text-decoration: none;
        overflow: hidden;
        transition: all 0.25s ease;
        box-shadow: 0 10px 24px -22px rgba(15, 23, 42, 0.35);
    }
    .kb-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 18px 36px -22px rgba(37,99,235,0.24);
        border-color: rgba(147,197,253,0.9);
    }
    .dark .kb-card {
        background: rgba(15, 23, 42, 0.78);
        border-color: rgba(51,65,85,0.9);
    }
    .kb-card-top {
        padding: 18px 18px 12px;
    }
    .kb-card-meta {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        margin-bottom: 14px;
    }
    .kb-tag {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 10px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.01em;
    }
    .kb-tag i { width: 10px; height: 10px; }
    .kb-card-title {
        margin: 0;
        font-size: 17px;
        line-height: 1.35;
        font-weight: 700;
        color: #0f172a;
    }
    .dark .kb-card-title { color: #f8fafc; }
    .kb-card-excerpt {
        margin-top: 10px;
        font-size: 13px;
        line-height: 1.65;
        color: #64748b;
    }
    .dark .kb-card-excerpt { color: #94a3b8; }
    .kb-card-foot {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        padding: 14px 18px 18px;
        border-top: 1px solid #f1f5f9;
        margin-top: auto;
        font-size: 11px;
        color: #64748b;
    }
    .dark .kb-card-foot { border-top-color: rgba(51,65,85,0.9); color: #94a3b8; }
    .kb-card-foot strong { color: #0f172a; }
    .dark .kb-card-foot strong { color: #f8fafc; }
    @media (max-width: 900px) {
        .kb-stats { grid-template-columns: repeat(2, minmax(150px, 1fr)); }
    }
    @media (max-width: 640px) {
        .kb-stats { grid-template-columns: 1fr; }
        .kb-filter-inner { padding: 12px; }
        .kb-search, .kb-select { width: 100%; min-width: 100%; }
    }
</style>

<div class="kb-shell">
    <div class="kb-hero fx-reveal">
        <div class="kb-hero-copy">
            <div class="kb-hero-icon"><i data-lucide="book-open"></i></div>
            <div>
                <h1 class="kb-hero-title">Knowledge Base</h1>
                <p class="kb-hero-sub">Approved troubleshooting guides, technical reference notes, and field-tested fixes.</p>
            </div>
        </div>
        <div class="page-hero-actions">
            <button onclick="openArticleModal()" class="btn btn-success"><i data-lucide="plus" style="width:15px;height:15px;"></i> Submit Article</button>
            <?php if (Auth::hasPermission('documentation.edit')): ?>
                <a href="<?= $urlBase ?>admin/knowledge" class="btn btn-secondary"><i data-lucide="shield" style="width:15px;height:15px;"></i> Pending</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="kb-filter-wrap fx-reveal" id="kb-filter-card">
        <div class="kb-filter-inner">
            <div class="kb-search">
                <i data-lucide="search"></i>
                <input type="text" id="kb-search" placeholder="Search articles, symptoms, fixes..." oninput="filterKB()">
            </div>
            <select id="kb-category" class="kb-select" onchange="filterKB()">
                <option value="">All Categories</option>
                <?php
                $cats = array_unique(array_column($articles, 'category'));
                sort($cats);
                foreach ($cats as $cat):
                ?>
                    <option value="<?= e($cat) ?>"><?= e(ucfirst($cat)) ?></option>
                <?php endforeach; ?>
            </select>
            <select id="kb-sort" class="kb-select" onchange="filterKB()">
                <option value="popular">Most Popular</option>
                <option value="recent">Most Recent</option>
                <option value="helpful">Most Helpful</option>
            </select>
        </div>
    </div>

    <div class="kb-stats fx-reveal">
        <?php
        $helpfulTotal = array_sum(array_column($articles, 'helpful_count'));
        $useTotal = array_sum(array_column($articles, 'use_count'));
        $successTotal = Database::fetch("SELECT COALESCE(SUM(success_count), 0) as s FROM knowledge_articles WHERE deleted_at IS NULL");
        $totalAttempts = Database::fetch("SELECT COALESCE(SUM(use_count), 0) as s FROM knowledge_articles WHERE deleted_at IS NULL");
        $successRate = $totalAttempts['s'] > 0 ? round(($successTotal['s'] / max($totalAttempts['s'], 1)) * 100) : 92;

        $kbStats = [
            ['icon' => 'book-open', 'value' => $countPublished, 'label' => 'Published', 'color' => 'blue'],
            ['icon' => 'eye', 'value' => $useTotal, 'label' => 'Views', 'color' => 'green'],
            ['icon' => 'thumbs-up', 'value' => $helpfulTotal, 'label' => 'Helpful', 'color' => 'yellow'],
            ['icon' => 'bar-chart-3', 'value' => $successRate . '%', 'label' => 'Success', 'color' => 'purple'],
        ];
        foreach ($kbStats as $s): ?>
            <div class="kb-stat">
                <div class="kb-stat-icon <?= $s['color'] ?>">
                    <i data-lucide="<?= $s['icon'] ?>" style="width:18px;height:18px;"></i>
                </div>
                <div>
                    <div class="kb-stat-value"><?= $s['value'] ?></div>
                    <div class="kb-stat-label"><?= $s['label'] ?></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div id="kb-grid" class="kb-grid">
        <?php $ki = 0; foreach ($articles as $a):
            $cat = strtolower($a['category'] ?? 'general');
            $cc = $catColors[$cat] ?? $defaultCat;
            $icon = $catIcons[$cat] ?? 'file-text';
            $rating = $a['use_count'] > 0 ? number_format(min(5, 3.5 + ($a['helpful_count'] / max($a['use_count'], 1)) * 2), 1) : '0.0';
            $date = date('M j', strtotime($a['created_at']));
            $excerpt = $a['issue'] ?? $a['symptoms'] ?? 'Troubleshooting guide and documentation.';
            $excerpt = mb_strimwidth(strip_tags($excerpt), 0, 120, '...');
            $fxd = ($ki % 6) * 50; $ki++;
        ?>
            <a href="<?= $urlBase ?>knowledge/view?id=<?= $a['id'] ?>"
               class="kb-card fx-reveal"
               style="--fx-delay:<?= $fxd ?>ms;"
               data-title="<?= e(strtolower($a['title'])) ?>"
               data-category="<?= e($cat) ?>"
               data-date="<?= strtotime($a['created_at']) ?>"
               data-uses="<?= intval($a['use_count']) ?>"
               data-helpful="<?= intval($a['helpful_count']) ?>">
                <div class="kb-card-top">
                    <div class="kb-card-meta">
                        <span class="kb-tag" style="background:<?= $cc['bg'] ?>;color:<?= $cc['fg'] ?>;">
                            <i data-lucide="<?= $icon ?>"></i> <?= e(ucfirst($cat)) ?>
                        </span>
                        <span class="badge badge-green"><i data-lucide="check-circle" style="width:10px;height:10px;"></i> Live</span>
                    </div>
                    <h3 class="kb-card-title"><?= e($a['title']) ?></h3>
                    <p class="kb-card-excerpt"><?= e($excerpt) ?></p>
                </div>
                <div class="kb-card-foot">
                    <span><strong><?= $a['use_count'] ?></strong> uses</span>
                    <span><?= $date ?></span>
                </div>
            </a>
        <?php endforeach; ?>
    </div>

    <?php if (empty($articles)): ?>
        <div style="text-align:center;padding:60px 20px;">
            <i data-lucide="book-open" style="width:48px;height:48px;color:#cbd5e1;margin-bottom:16px;"></i>
            <h3 style="font-size:16px;font-weight:700;color:#475569;margin-bottom:4px;">No articles yet</h3>
            <p style="font-size:13px;color:#94a3b8;">Knowledge articles will appear here once approved by an administrator.</p>
        </div>
    <?php endif; ?>
</div>

<script>
function filterKB() {
    var search = document.getElementById('kb-search').value.toLowerCase();
    var cat = document.getElementById('kb-category').value;
    var sort = document.getElementById('kb-sort').value;
    var cards = document.querySelectorAll('#kb-grid a.kb-card');
    var arr = [];
    cards.forEach(function(c) {
        var matchSearch = !search || c.dataset.title.indexOf(search) !== -1;
        var matchCat = !cat || c.dataset.category === cat;
        c.style.display = (matchSearch && matchCat) ? '' : 'none';
        if (matchSearch && matchCat) arr.push(c);
    });
    arr.sort(function(a, b) {
        if (sort === 'recent') return parseInt(b.dataset.date) - parseInt(a.dataset.date);
        if (sort === 'helpful') return parseInt(b.dataset.helpful) - parseInt(a.dataset.helpful);
        return parseInt(b.dataset.uses) - parseInt(a.dataset.uses);
    });
    var grid = document.getElementById('kb-grid');
    arr.forEach(function(c) { grid.appendChild(c); });
}
</script>

<!-- Article Submission Modal -->
<div class="kb-modal" id="kb-article-modal">
    <div class="kb-modal-content">
        <div class="kb-modal-header">
            <h2 style="font-size: 16px; font-weight: 800; color: #0f172a;">Submit New Article</h2>
            <button onclick="closeArticleModal()" class="btn btn-ghost btn-sm"><i data-lucide="x"></i></button>
        </div>
        <div class="kb-modal-body">
            <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 10px; padding: 12px 14px; margin-bottom: 16px; font-size: 12px; color: #1e40af; display: flex; gap: 10px;">
                <i data-lucide="info" style="width: 16px; height: 16px; flex-shrink: 0; margin-top: 2px;"></i>
                <span>Your article will be reviewed by an administrator before being published to the knowledge base.</span>
            </div>
            <form id="kb-form" onsubmit="submitArticle(event)">
                <!-- Basic Info -->
                <h3 style="font-size: 13px; font-weight: 700; color: #374151; margin: 16px 0 12px; text-transform: uppercase; letter-spacing: 0.05em;">Basic Information</h3>
                <div class="kb-form-group">
                    <label class="kb-form-label">Article Title *</label>
                    <input type="text" id="title" class="kb-form-input" placeholder="e.g., Resolved: No Display - Dell OptiPlex 7090" required>
                </div>
                <div class="kb-form-grid">
                    <div class="kb-form-group">
                        <label class="kb-form-label">Category *</label>
                        <select id="category" class="kb-form-input" required>
                            <option value="">Select category</option>
                            <option value="Hardware">Hardware</option>
                            <option value="Software">Software</option>
                            <option value="Network">Network</option>
                            <option value="Printer">Printer</option>
                            <option value="CCTV">CCTV</option>
                            <option value="Server">Server</option>
                            <option value="Security">Security</option>
                        </select>
                    </div>
                    <div class="kb-form-group">
                        <label class="kb-form-label">Device Type *</label>
                        <select id="device_type" class="kb-form-input" required>
                            <option value="">Select device</option>
                            <option value="Laptop">Laptop</option>
                            <option value="Desktop">Desktop</option>
                            <option value="Server">Server</option>
                            <option value="Printer">Printer</option>
                            <option value="Monitor">Monitor</option>
                            <option value="Router">Router</option>
                            <option value="Switch">Switch</option>
                            <option value="Camera">Camera</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>

                <!-- Issue & Solution -->
                <h3 style="font-size: 13px; font-weight: 700; color: #374151; margin: 16px 0 12px; text-transform: uppercase; letter-spacing: 0.05em;">Issue & Solution</h3>
                <div class="kb-form-group">
                    <label class="kb-form-label">Symptoms/Issue Description *</label>
                    <textarea id="symptoms" class="kb-form-input" rows="3" placeholder="Describe what users or technicians observed..." required></textarea>
                </div>
                <div class="kb-form-group">
                    <label class="kb-form-label">Root Cause *</label>
                    <textarea id="root_cause" class="kb-form-input" rows="2" placeholder="What caused this issue?" required></textarea>
                </div>
                <div class="kb-form-group">
                    <label class="kb-form-label">Solution Steps *</label>
                    <textarea id="solution" class="kb-form-input" rows="4" placeholder="Step 1: Check cable connections&#10;Step 2: Run diagnostics&#10;Step 3: Replace faulty component" required></textarea>
                </div>

                <!-- Additional Info -->
                <h3 style="font-size: 13px; font-weight: 700; color: #374151; margin: 16px 0 12px; text-transform: uppercase; letter-spacing: 0.05em;">Additional Information</h3>
                <div class="kb-form-grid">
                    <div class="kb-form-group">
                        <label class="kb-form-label">Manufacturer (Optional)</label>
                        <input type="text" id="manufacturer" class="kb-form-input" placeholder="e.g., Dell">
                    </div>
                    <div class="kb-form-group">
                        <label class="kb-form-label">Model (Optional)</label>
                        <input type="text" id="model" class="kb-form-input" placeholder="e.g., OptiPlex 7090">
                    </div>
                </div>
                <div class="kb-form-group">
                    <label class="kb-form-label">Tools Needed (Comma-separated)</label>
                    <input type="text" id="tools_used" class="kb-form-input" placeholder="e.g., Multimeter, Thermal paste, Screwdriver">
                </div>
                <div class="kb-form-group">
                    <label class="kb-form-label">Related Commands (Comma-separated)</label>
                    <input type="text" id="commands" class="kb-form-input" placeholder="e.g., ipconfig /all, ping 8.8.8.8">
                </div>
                <div class="kb-form-group">
                    <label class="kb-form-label">Safety Warning (Optional)</label>
                    <textarea id="safety" class="kb-form-input" rows="2" placeholder="e.g., Disconnect power before opening the case"></textarea>
                </div>

                <div style="display: flex; gap: 8px; margin-top: 20px;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">Submit for Review</button>
                    <button type="button" onclick="closeArticleModal()" class="btn btn-secondary">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openArticleModal() {
    document.getElementById('kb-article-modal').classList.add('active');
}

function closeArticleModal() {
    document.getElementById('kb-article-modal').classList.remove('active');
    document.getElementById('kb-form').reset();
}

function submitArticle(e) {
    e.preventDefault();
    
    var formData = new FormData();
    formData.append('title', document.getElementById('title').value);
    formData.append('category', document.getElementById('category').value);
    formData.append('device_type', document.getElementById('device_type').value);
    formData.append('symptoms', document.getElementById('symptoms').value);
    formData.append('root_cause', document.getElementById('root_cause').value);
    formData.append('solution', document.getElementById('solution').value);
    formData.append('manufacturer', document.getElementById('manufacturer').value || null);
    formData.append('model', document.getElementById('model').value || null);
    formData.append('tools_used', document.getElementById('tools_used').value || null);
    formData.append('commands_used', document.getElementById('commands').value || null);
    formData.append('safety_warning', document.getElementById('safety').value || null);
    
    fetch('<?= $urlBase ?>api/knowledge/save.php', {
        method: 'POST',
        body: formData
    }).then(r => r.json()).then(d => {
        if (d.success) {
            showToast('Article submitted! Awaiting admin review.', 'success');
            closeArticleModal();
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast(d.message || 'Error submitting article', 'error');
        }
    }).catch(() => showToast('Error submitting article', 'error'));
}

// Close modal when clicking outside
document.getElementById('kb-article-modal')?.addEventListener('click', function(e) {
    if (e.target === this) closeArticleModal();
});
</script>
<?php require APP_ROOT . '/includes/layout_footer.php'; ?>
