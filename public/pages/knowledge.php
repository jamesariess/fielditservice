<?php
$page_title = 'Knowledge Base';
$active_menu = 'knowledge';
require APP_ROOT . '/includes/layout_header.php';
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
                <input type="text" placeholder="Search knowledge base..." class="form-input" style="padding-left:36px;">
            </div>
            <select class="form-input form-select" style="width:auto;">
                <option>All Categories</option><option>Hardware</option><option>Software</option><option>Network</option><option>Printer</option><option>CCTV</option>
            </select>
            <select class="form-input form-select" style="width:auto;">
                <option>Most Recent</option><option>Most Used</option><option>Highest Rated</option>
            </select>
        </div>
    </div>

    <!-- Stats -->
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:24px;" class="hide-mobile">
        <?php
        $kbStats = [
            ['icon'=>'book-open','value'=>'342','label'=>'Total Articles','color'=>'blue'],
            ['icon'=>'check-circle-2','value'=>'287','label'=>'Published','color'=>'green'],
            ['icon'=>'clock-3','value'=>'12','label'=>'Pending Review','color'=>'yellow'],
            ['icon'=>'bar-chart-3','value'=>'89%','label'=>'Success Rate','color'=>'purple'],
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
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:14px;">
        <?php
        $articles = [
            ['title'=>'No Display — Desktop Troubleshooting','cat'=>'hardware','status'=>'Published','author'=>'Admin','rating'=>4.8,'uses'=>137,'date'=>'Aug 15','excerpt'=>'Complete step-by-step guide for diagnosing and resolving no display issues on desktop computers.'],
            ['title'=>'Printer Offline — HP LaserJet','cat'=>'printer','status'=>'Published','author'=>'Maria S.','rating'=>4.6,'uses'=>98,'date'=>'Aug 12','excerpt'=>'Troubleshooting HP LaserJet printers that appear offline in Windows.'],
            ['title'=>'Network Connectivity — WiFi Issues','cat'=>'network','status'=>'Published','author'=>'Admin','rating'=>4.7,'uses'=>156,'date'=>'Aug 10','excerpt'=>'Diagnosing WiFi connectivity problems from physical layer to DNS resolution.'],
            ['title'=>'No Sound — Windows Audio','cat'=>'hardware','status'=>'Published','author'=>'Carlos R.','rating'=>4.5,'uses'=>89,'date'=>'Aug 8','excerpt'=>'Resolving audio issues on Windows including driver, service, and configuration checks.'],
            ['title'=>'Blue Screen (BSOD) — Common STOP Errors','cat'=>'software','status'=>'Published','author'=>'Admin','rating'=>4.9,'uses'=>203,'date'=>'Aug 5','excerpt'=>'Understanding and resolving common Windows BSOD error codes.'],
            ['title'=>'CCTV Camera Offline — IP Camera','cat'=>'cctv','status'=>'Published','author'=>'Juan D.','rating'=>4.4,'uses'=>67,'date'=>'Aug 3','excerpt'=>'Troubleshooting offline IP cameras including PoE, network, and configuration checks.'],
        ];
        $catColors = [
            'hardware'=>['bg'=>'#eff6ff','fg'=>'#2563eb'],
            'software'=>['bg'=>'#faf5ff','fg'=>'#9333ea'],
            'network'=>['bg'=>'#f0fdf4','fg'=>'#16a34a'],
            'printer'=>['bg'=>'#fff7ed','fg'=>'#ea580c'],
            'cctv'=>['bg'=>'#f8fafc','fg'=>'#475569'],
        ];
        foreach ($articles as $a):
            $cc = $catColors[$a['cat']] ?? ['bg'=>'#f1f5f9','fg'=>'#475569'];
        ?>
            <a href="<?= $urlBase ?>knowledge/view?id=1" class="card card-hover" style="text-decoration:none;display:flex;flex-direction:column;">
                <div class="card-body" style="flex:1;">
                    <div style="display:flex;gap:6px;margin-bottom:10px;">
                        <span class="badge" style="background:<?= $cc['bg'] ?>;color:<?= $cc['fg'] ?>;"><?= ucfirst($a['cat']) ?></span>
                        <span class="badge badge-green"><i data-lucide="check-circle" style="width:10px;height:10px;"></i> <?= $a['status'] ?></span>
                    </div>
                    <h3 style="font-size:14px;font-weight:700;color:#111827;margin-bottom:6px;line-height:1.4;"><?= $a['title'] ?></h3>
                    <p style="font-size:12.5px;color:#64748b;line-height:1.5;flex:1;"><?= $a['excerpt'] ?></p>
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-top:14px;padding-top:12px;border-top:1px solid #f1f5f9;">
                        <div style="display:flex;align-items:center;gap:10px;">
                            <span style="display:flex;align-items:center;gap:3px;font-size:11px;color:#d97706;font-weight:600;"><i data-lucide="star" style="width:11px;height:11px;fill:#d97706;"></i> <?= $a['rating'] ?></span>
                            <span style="font-size:11px;color:#94a3b8;">·</span>
                            <span style="font-size:11px;color:#94a3b8;font-weight:500;"><?= $a['uses'] ?> uses</span>
                        </div>
                        <span style="font-size:11px;color:#94a3b8;"><?= $a['date'] ?></span>
                    </div>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</div>
<?php require APP_ROOT . '/includes/layout_footer.php'; ?>
