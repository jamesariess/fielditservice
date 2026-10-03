<?php
if (!defined('APP_ROOT')) { @header('Location: /fielditservice/'); exit; }

$page_title = 'Tools & Equipment Reference';
$active_menu = 'tools';
require APP_ROOT . '/includes/layout_header.php';
$tools = Database::fetchAll('SELECT * FROM tools ORDER BY id');
?>

<div>
    <!-- Page Hero -->
    <div class="page-hero fx-reveal">
        <div>
            <div style="display:flex;align-items:center;gap:14px;">
                <div class="page-hero-ico amber"><i data-lucide="wrench"></i></div>
                <div>
                    <h1 class="page-hero-title">Tools &amp; Equipment Reference</h1>
                    <p class="page-hero-sub">Essential tools for field IT technicians</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats strip -->
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:14px;margin-bottom:26px;--fx-delay:60ms;" class="fx-reveal">
        <?php
        $toolCount = count($tools);
        $cats = [];
        foreach ($tools as $t) { $cats[$t['category'] ?? 'Other'] = ($cats[$t['category'] ?? 'Other'] ?? 0) + 1; }
        $safetyCount = 0; foreach ($tools as $t) { if (stripos($t['safety'] ?? '', 'disconnect') !== false || stripos($t['safety'] ?? '', 'power off') !== false) $safetyCount++; }
        $mini = [
            ['icon'=>'hammer','label'=>'Total Tools','value'=>number_format($toolCount),'color'=>'#d97706','bg'=>'#fffbeb'],
            ['icon'=>'folder-tree','label'=>'Categories','value'=>count($cats),'color'=>'#2563eb','bg'=>'#eff6ff'],
            ['icon'=>'shield-check','label'=>'Safety Warnings','value'=>$safetyCount,'color'=>'#dc2626','bg'=>'#fef2f2'],
        ];
        foreach ($mini as $m): ?>
            <div class="stat-card-premium" style="padding:18px;">
                <div style="display:flex;align-items:center;gap:12px;">
                    <div style="width:42px;height:42px;border-radius:12px;background:<?= $m['bg'] ?>;display:flex;align-items:center;justify-content:center;">
                        <i data-lucide="<?= $m['icon'] ?>" style="width:20px;height:20px;color:<?= $m['color'] ?>;"></i>
                    </div>
                    <div>
                        <div class="stat-num" style="font-size:22px;font-weight:800;line-height:1;"><?= $m['value'] ?></div>
                        <div style="font-size:12px;color:#64748b;font-weight:500;margin-top:2px;"><?= $m['label'] ?></div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div style="display:flex;gap:10px;margin-bottom:20px;flex-wrap:wrap;align-items:center;--fx-delay:100ms;" class="fx-reveal">
        <div style="flex:1;min-width:240px;position:relative;">
            <i data-lucide="search" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);width:15px;height:15px;color:#94a3b8;"></i>
            <input type="text" id="tool-search" placeholder="Filter tools by name or purpose..." class="field-input" style="padding-left:36px;" oninput="filterTools(this.value)">
        </div>
    </div>

    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4" id="tools-grid">
        <?php foreach ($tools as $i => $tool): ?>
            <div class="panel-card fx-reveal tool-card" style="--fx-delay:<?= ($i % 6) * 60 ?>ms;padding:5px 5px;" data-name="<?= e(strtolower(($tool['name'] ?? '').' '.($tool['purpose'] ?? ''))) ?>">
                <div style="padding:20px 5px;">
                    <div style="width:50px;height:50px;border-radius:14px;background:linear-gradient(135deg,#fffbeb,#fef3c7);display:flex;align-items:center;justify-content:center;margin-bottom:14px;box-shadow:0 4px 12px rgba(217,119,6,.15);">
                        <i data-lucide="<?= e($tool['icon'] ?? 'wrench') ?>" style="width:24px;height:24px;color:#d97706;"></i>
                    </div>
                    <h3 style="font-size:15px;font-weight:700;color:#0f172a;margin-bottom:10px;" class="dark:text-gray-100"><?= e($tool['name']) ?></h3>
                    <div style="display:flex;flex-direction:column;gap:8px;font-size:13px;">
                        <div style="display:flex;gap:6px;">
                            <i data-lucide="target" style="width:14px;height:14px;color:#d97706;flex-shrink:0;margin-top:2px;"></i>
                            <span style="color:#475569;"><b>Purpose:</b> <?= e($tool['purpose'] ?? '') ?></span>
                        </div>
                        <div style="display:flex;gap:6px;">
                            <i data-lucide="calendar-clock" style="width:14px;height:14px;color:#64748b;flex-shrink:0;margin-top:2px;"></i>
                            <span style="color:#475569;"><b>When to use:</b> <?= e($tool['when_to_use'] ?? '') ?></span>
                        </div>
                        <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:10px;padding:8px 12px;display:flex;gap:6px;">
                            <i data-lucide="alert-triangle" style="width:14px;height:14px;color:#d97706;flex-shrink:0;margin-top:2px;"></i>
                            <span style="font-size:12px;color:#b45309;"><?= e($tool['safety'] ?? '') ?></span>
                        </div>
                    </div>
                </div>
                <div style="padding:0 5px 12px 5px;display:flex;align-items:center;gap:6px;">
                    <span class="ubadge ubadge-amber"><span class="udot"></span><?= e($tool['category'] ?? 'General') ?></span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<style>
@media (max-width: 640px) {
    #tools-grid { grid-template-columns: 1fr !important; }
}
</style>

<script>
function filterTools(q) {
    var query = q.toLowerCase();
    document.querySelectorAll('.tool-card').forEach(function(card) {
        card.style.display = (card.dataset.name || '').indexOf(query) >= 0 ? '' : 'none';
    });
}
</script>
<?php require APP_ROOT . '/includes/layout_footer.php'; ?>
