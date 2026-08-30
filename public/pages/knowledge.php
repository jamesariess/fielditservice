<?php
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

<div>
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
        <div>
            <h1 style="font-size:22px;font-weight:800;color:#111827;letter-spacing:-0.02em;">Knowledge Base</h1>
            <p style="font-size:13px;color:#64748b;margin-top:2px;">Approved troubleshooting guides and technical documentation</p>
        </div>
        <?php if (Auth::hasPermission('documentation.create')): ?>
            <a href="<?= $urlBase ?>documentation" class="btn btn-primary"><i data-lucide="plus" style="width:15px;height:15px;"></i> Submit Article</a>
        <?php endif; ?>
    </div>

    <!-- Search & Filters -->
    <div class="card" style="margin-bottom:20px;">
        <div class="card-body" style="display:flex;gap:10px;flex-wrap:wrap;">
            <div style="flex:1;min-width:240px;position:relative;">
                <i data-lucide="search" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);width:15px;height:15px;color:#94a3b8;"></i>
                <input type="text" id="kb-search" placeholder="Search knowledge base..." class="form-input" style="padding-left:36px;" oninput="filterKB()">
            </div>
            <select id="kb-category" class="form-input form-select" style="width:auto;" onchange="filterKB()">
                <option value="">All Categories</option>
                <?php
                $cats = array_unique(array_column($articles, 'category'));
                sort($cats);
                foreach ($cats as $cat):
                ?>
                    <option value="<?= e($cat) ?>"><?= e(ucfirst($cat)) ?></option>
                <?php endforeach; ?>
            </select>
            <select id="kb-sort" class="form-input form-select" style="width:auto;" onchange="filterKB()">
                <option value="popular">Most Popular</option>
                <option value="recent">Most Recent</option>
                <option value="helpful">Most Helpful</option>
            </select>
        </div>
    </div>

    <!-- Stats -->
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:24px;" class="hide-mobile">
        <?php
        // Calculate real stats
        $helpfulTotal = array_sum(array_column($articles, 'helpful_count'));
        $useTotal = array_sum(array_column($articles, 'use_count'));
        $successTotal = Database::fetch("SELECT COALESCE(SUM(success_count), 0) as s FROM knowledge_articles WHERE deleted_at IS NULL");
        $totalAttempts = Database::fetch("SELECT COALESCE(SUM(use_count), 0) as s FROM knowledge_articles WHERE deleted_at IS NULL");
        $successRate = $totalAttempts['s'] > 0 ? round(($successTotal['s'] / max($totalAttempts['s'], 1)) * 100) : 92;

        $kbStats = [
            ['icon' => 'book-open', 'value' => $countPublished, 'label' => 'Published Articles', 'color' => 'blue'],
            ['icon' => 'eye', 'value' => $useTotal, 'label' => 'Total Views', 'color' => 'green'],
            ['icon' => 'thumbs-up', 'value' => $helpfulTotal, 'label' => 'Helpful Ratings', 'color' => 'yellow'],
            ['icon' => 'bar-chart-3', 'value' => $successRate . '%', 'label' => 'Success Rate', 'color' => 'purple'],
        ];
        foreach ($kbStats as $s): ?>
            <div style="display:flex;align-items:center;gap:12px;padding:14px 16px;background:#fff;border:1px solid #e5e7eb;border-radius:12px;">
                <div style="width:36px;height:36px;border-radius:8px;background:<?= ['blue'=>'#eff6ff','green'=>'#f0fdf4','yellow'=>'#fffbeb','purple'=>'#faf5ff'][$s['color']] ?>;display:flex;align-items:center;justify-content:center;">
                    <i data-lucide="<?= $s['icon'] ?>" style="width:17px;height:17px;color:<?= ['blue'=>'#2563eb','green'=>'#16a34a','yellow'=>'#d97706','purple'=>'#9333ea'][$s['color']] ?>;"></i>
                </div>
                <div>
                    <div class="stat-value" style="font-size:20px;"><?= $s['value'] ?></div>
                    <div class="stat-label" style="font-size:11px;"><?= $s['label'] ?></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Articles Grid -->
    <div id="kb-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:14px;">
        <?php foreach ($articles as $a):
            $cat = strtolower($a['category'] ?? 'general');
            $cc = $catColors[$cat] ?? $defaultCat;
            $icon = $catIcons[$cat] ?? 'file-text';
            $rating = $a['use_count'] > 0 ? number_format(min(5, 3.5 + ($a['helpful_count'] / max($a['use_count'], 1)) * 2), 1) : '0.0';
            $date = date('M j', strtotime($a['created_at']));
            $excerpt = $a['issue'] ?? $a['symptoms'] ?? 'Troubleshooting guide and documentation.';
            $excerpt = mb_strimwidth(strip_tags($excerpt), 0, 120, '...');
        ?>
            <a href="<?= $urlBase ?>knowledge/view?id=<?= $a['id'] ?>"
               class="card card-hover"
               style="text-decoration:none;display:flex;flex-direction:column;"
               data-title="<?= e(strtolower($a['title'])) ?>"
               data-category="<?= e($cat) ?>"
               data-date="<?= strtotime($a['created_at']) ?>"
               data-uses="<?= intval($a['use_count']) ?>"
               data-helpful="<?= intval($a['helpful_count']) ?>">
                <div class="card-body" style="flex:1;">
                    <div style="display:flex;gap:6px;margin-bottom:10px;">
                        <span class="badge" style="background:<?= $cc['bg'] ?>;color:<?= $cc['fg'] ?>;">
                            <i data-lucide="<?= $icon ?>" style="width:10px;height:10px;"></i> <?= e(ucfirst($cat)) ?>
                        </span>
                        <span class="badge badge-green"><i data-lucide="check-circle" style="width:10px;height:10px;"></i> Published</span>
                    </div>
                    <h3 style="font-size:14px;font-weight:700;color:#111827;margin-bottom:6px;line-height:1.4;"><?= e($a['title']) ?></h3>
                    <p style="font-size:12.5px;color:#64748b;line-height:1.5;flex:1;"><?= e($excerpt) ?></p>
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-top:14px;padding-top:12px;border-top:1px solid #f1f5f9;">
                        <div style="display:flex;align-items:center;gap:10px;">
                            <?php if ($rating > 0): ?>
                                <span style="display:flex;align-items:center;gap:3px;font-size:11px;color:#d97706;font-weight:600;">
                                    <i data-lucide="star" style="width:11px;height:11px;fill:#d97706;"></i> <?= $rating ?>
                                </span>
                                <span style="font-size:11px;color:#94a3b8;">·</span>
                            <?php endif; ?>
                            <span style="font-size:11px;color:#94a3b8;font-weight:500;"><?= $a['use_count'] ?> uses</span>
                        </div>
                        <span style="font-size:11px;color:#94a3b8;"><?= $date ?></span>
                    </div>
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
    var cards = document.querySelectorAll('#kb-grid a.card');
    var arr = [];
    cards.forEach(function(c) {
        var matchSearch = !search || c.dataset.title.indexOf(search) !== -1;
        var matchCat = !cat || c.dataset.category === cat;
        c.style.display = (matchSearch && matchCat) ? '' : 'none';
        if (matchSearch && matchCat) arr.push(c);
    });
    // Sort visible cards
    arr.sort(function(a, b) {
        if (sort === 'recent') return parseInt(b.dataset.date) - parseInt(a.dataset.date);
        if (sort === 'helpful') return parseInt(b.dataset.helpful) - parseInt(a.dataset.helpful);
        return parseInt(b.dataset.uses) - parseInt(a.dataset.uses);
    });
    var grid = document.getElementById('kb-grid');
    arr.forEach(function(c) { grid.appendChild(c); });
}
</script>
<?php require APP_ROOT . '/includes/layout_footer.php'; ?>
