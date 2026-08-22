<?php
$page_title = 'Statistics';
$active_menu = 'admin-statistics';
require APP_ROOT . '/includes/layout_header.php';
Auth::requirePermission('audit.view');
?>

<div>
    <div style="margin-bottom:24px;">
        <h1 style="font-size:22px;font-weight:800;color:#111827;letter-spacing:-0.02em;">Statistics & Analytics</h1>
        <p style="font-size:13px;color:#64748b;margin-top:2px;">System usage analytics and performance metrics</p>
    </div>

    <!-- Overview Stats -->
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:14px;margin-bottom:28px;">
        <?php
        $stats = [
            ['icon'=>'activity','label'=>'Total Sessions','value'=>'1,247','change'=>'+12%','up'=>true,'color'=>'blue'],
            ['icon'=>'check-circle-2','label'=>'Solved Rate','value'=>'87%','change'=>'+3%','up'=>true,'color'=>'green'],
            ['icon'=>'arrow-up-right','label'=>'Escalation Rate','value'=>'8%','change'=>'-2%','up'=>false,'color'=>'red'],
            ['icon'=>'clock-3','label'=>'Avg Resolution','value'=>'23 min','change'=>'-5 min','up'=>false,'color'=>'yellow'],
            ['icon'=>'users','label'=>'Active Users','value'=>'24','change'=>'+2','up'=>true,'color'=>'purple'],
            ['icon'=>'sparkles','label'=>'AI Sessions','value'=>'342','change'=>'+28%','up'=>true,'color'=>'orange'],
        ];
        foreach ($stats as $s): ?>
            <div class="stat-card">
                <div class="stat-icon <?= $s['color'] ?>"><i data-lucide="<?= $s['icon'] ?>" style="width:22px;height:22px;"></i></div>
                <div>
                    <div class="stat-value" style="font-size:22px;"><?= $s['value'] ?></div>
                    <div class="stat-label"><?= $s['label'] ?></div>
                </div>
                <span class="stat-change <?= $s['up']?'up':'down' ?>" style="margin-left:auto;"><?= $s['change'] ?></span>
            </div>
        <?php endforeach; ?>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;" class="hide-mobile">
        <!-- Most Common Issues -->
        <div class="card">
            <div class="card-header"><h3 style="font-size:15px;font-weight:700;">Most Common Issues</h3></div>
            <div class="card-body" style="padding-top:8px;">
                <div class="space-y-4">
                    <?php
                    $issues = [
                        ['name'=>'No Display','count'=>187,'pct'=>92],
                        ['name'=>'Printer Offline','count'=>143,'pct'=>78],
                        ['name'=>'Network Connectivity','count'=>128,'pct'=>72],
                        ['name'=>'Slow Performance','count'=>98,'pct'=>55],
                        ['name'=>'No Sound','count'=>76,'pct'=>43],
                        ['name'=>'Blue Screen','count'=>54,'pct'=>30],
                        ['name'=>'Login Problems','count'=>42,'pct'=>24],
                        ['name'=>'CCTV Offline','count'=>31,'pct'=>18],
                    ];
                    foreach ($issues as $i => $iss): ?>
                        <div>
                            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px;">
                                <div style="display:flex;align-items:center;gap:8px;">
                                    <span style="width:20px;height:20px;border-radius:5px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:700;color:#64748b;"><?= $i+1 ?></span>
                                    <span style="font-size:13px;font-weight:600;color:#374151;"><?= $iss['name'] ?></span>
                                </div>
                                <span style="font-size:12px;color:#94a3b8;font-weight:500;"><?= $iss['count'] ?> cases</span>
                            </div>
                            <div class="progress-bar"><div class="progress-fill blue" style="width:<?= $iss['pct'] ?>%"></div></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Most Common Devices -->
        <div class="card">
            <div class="card-header"><h3 style="font-size:15px;font-weight:700;">Most Common Devices</h3></div>
            <div class="card-body" style="padding-top:8px;">
                <div class="space-y-4">
                    <?php
                    $devices = [
                        ['name'=>'Dell OptiPlex 7090','type'=>'Desktop','count'=>89,'pct'=>80],
                        ['name'=>'Lenovo ThinkPad T14','type'=>'Laptop','count'=>76,'pct'=>68],
                        ['name'=>'HP LaserJet Pro M404','type'=>'Printer','count'=>67,'pct'=>60],
                        ['name'=>'Dell Latitude 5520','type'=>'Laptop','count'=>54,'pct'=>48],
                        ['name'=>'HP ProBook 450 G9','type'=>'Laptop','count'=>43,'pct'=>38],
                        ['name'=>'Lenovo ThinkCentre M70s','type'=>'Desktop','count'=>38,'pct'=>34],
                    ];
                    foreach ($devices as $d): ?>
                        <div>
                            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px;">
                                <div>
                                    <span style="font-size:13px;font-weight:600;color:#374151;"><?= $d['name'] ?></span>
                                    <span class="badge badge-gray" style="margin-left:6px;font-size:10px;"><?= $d['type'] ?></span>
                                </div>
                                <span style="font-size:12px;color:#94a3b8;font-weight:500;"><?= $d['count'] ?> sessions</span>
                            </div>
                            <div class="progress-bar"><div class="progress-fill green" style="width:<?= $d['pct'] ?>%"></div></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Most Effective Procedures -->
        <div class="card">
            <div class="card-header"><h3 style="font-size:15px;font-weight:700;">Most Effective Procedures</h3></div>
            <div class="card-body" style="padding-top:8px;">
                <?php
                $procedures = [
                    ['name'=>'Display Cable Reseat','success'=>94,'uses'=>87],
                    ['name'=>'IPConfig /renew','success'=>89,'uses'=>124],
                    ['name'=>'RAM Reseat','success'=>82,'uses'=>63],
                    ['name'=>'Power Drain','success'=>78,'uses'=>95],
                    ['name'=>'SFC /scannow','success'=>71,'uses'=>48],
                ];
                foreach ($procedures as $p): ?>
                    <div style="display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid #f1f5f9;">
                        <div style="flex:1;">
                            <div style="font-size:13px;font-weight:600;color:#374151;"><?= $p['name'] ?></div>
                            <div style="font-size:11px;color:#94a3b8;"><?= $p['uses'] ?> uses</div>
                        </div>
                        <div style="text-align:right;">
                            <div style="font-size:16px;font-weight:800;color:<?= $p['success']>=80?'#16a34a':($p['success']>=60?'#d97706':'#dc2626') ?>;"><?= $p['success'] ?>%</div>
                            <div style="font-size:10px;color:#94a3b8;">success</div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Knowledge Quality -->
        <div class="card">
            <div class="card-header"><h3 style="font-size:15px;font-weight:700;">Knowledge Quality</h3></div>
            <div class="card-body" style="padding-top:8px;">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <?php
                    $kbStats = [
                        ['icon'=>'book-open','value'=>'342','label'=>'Total Articles','color'=>'blue'],
                        ['icon'=>'check-circle-2','value'=>'287','label'=>'Published','color'=>'green'],
                        ['icon'=>'clock-3','value'=>'12','label'=>'Pending Review','color'=>'yellow'],
                        ['icon'=>'alert-triangle','value'=>'5','label'=>'Needs Update','color'=>'red'],
                    ];
                    foreach ($kbStats as $ks): ?>
                        <div style="padding:12px;background:#f8fafc;border-radius:10px;text-align:center;">
                            <div style="width:32px;height:32px;border-radius:8px;background:<?= ['blue'=>'#eff6ff','green'=>'#f0fdf4','yellow'=>'#fffbeb','red'=>'#fef2f2'][$ks['color']] ?>;display:flex;align-items:center;justify-content:center;margin:0 auto 6px;">
                                <i data-lucide="<?= $ks['icon'] ?>" style="width:15px;height:15px;color:<?= ['blue'=>'#2563eb','green'=>'#16a34a','yellow'=>'#d97706','red'=>'#dc2626'][$ks['color']] ?>;"></i>
                            </div>
                            <div style="font-size:18px;font-weight:800;color:#111827;"><?= $ks['value'] ?></div>
                            <div style="font-size:10px;color:#94a3b8;font-weight:500;"><?= $ks['label'] ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div style="margin-top:16px;padding:12px;background:#f8fafc;border-radius:10px;">
                    <div style="font-size:12px;font-weight:600;color:#374151;margin-bottom:8px;">Articles Requiring Review</div>
                    <div class="space-y-2">
                        <?php
                        $reviews = [
                            ['title'=>'No Display — Desktop','age'=>'90 days','status'=>'Overdue'],
                            ['title'=>'WiFi Troubleshooting','age'=>'45 days','status'=>'Due Soon'],
                            ['title'=>'BSOD Guide','age'=>'30 days','status'=>'Upcoming'],
                        ];
                        foreach ($reviews as $rv): ?>
                            <div style="display:flex;align-items:center;justify-content:space-between;">
                                <span style="font-size:12px;color:#475569;"><?= $rv['title'] ?></span>
                                <span class="badge <?= $rv['status']==='Overdue'?'badge-red':($rv['status']==='Due Soon'?'badge-yellow':'badge-gray') ?>"><?= $rv['status'] ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div style="margin-top:12px;padding:12px;background:#f8fafc;border-radius:10px;">
                    <div style="font-size:12px;font-weight:600;color:#374151;margin-bottom:8px;">Pending Technician Submissions</div>
                    <div class="space-y-2">
                        <?php
                        $subs = [
                            ['title'=>'POS Printer Blinking Red','author'=>'Ana T.','status'=>'Submitted'],
                            ['title'=>'New WiFi Issue Guide','author'=>'Juan D.','status'=>'Submitted'],
                            ['title'=>'Server Room Temperature','author'=>'Carlos R.','status'=>'Under Review'],
                        ];
                        foreach ($subs as $sb): ?>
                            <div style="display:flex;align-items:center;justify-content:space-between;">
                                <div>
                                    <span style="font-size:12px;color:#475569;"><?= $sb['title'] ?></span>
                                    <span style="font-size:10px;color:#94a3b8;margin-left:4px;">by <?= $sb['author'] ?></span>
                                </div>
                                <span class="badge <?= $sb['status']==='Under Review'?'badge-yellow':'badge-blue' ?>"><?= $sb['status'] ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Knowledge Gaps -->
    <div class="card" style="margin-top:20px;">
        <div class="card-header flex items-center gap-2"><i data-lucide="alert-circle" style="width:16px;height:16px;color:#d97706;"></i><h3 style="font-size:15px;font-weight:700;">Knowledge Gaps</h3></div>
        <div class="card-body" style="padding-top:8px;">
            <p style="font-size:13px;color:#64748b;margin-bottom:12px;">Searches that returned no results - potential articles to create:</p>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(250px,1fr));gap:8px;">
                <?php
                $gaps = [
                    ['query'=>'POS printer blinking red','count'=>8],
                    ['query'=>'Epson TM-T88 paper feed','count'=>5],
                    ['query'=>'Hikvision NVR password reset','count'=>4],
                    ['query'=>'Lenovo dock not detected','count'=>3],
                    ['query'=>'UPS battery replacement','count'=>3],
                ];
                foreach ($gaps as $g): ?>
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:8px 12px;background:#fffbeb;border:1px solid #fde68a;border-radius:8px;">
                        <span style="font-size:12px;color:#92400e;font-weight:500;">"<?= $g['query'] ?>"</span>
                        <span style="font-size:11px;color:#b45309;font-weight:600;"><?= $g['count'] ?>x</span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Incident Patterns -->
    <div class="card" style="margin-top:20px;">
        <div class="card-header flex items-center gap-2"><i data-lucide="trending-up" style="width:16px;height:16px;color:#dc2626;"></i><h3 style="font-size:15px;font-weight:700;">Incident Patterns</h3></div>
        <div class="card-body" style="padding-top:8px;">
            <div class="alert alert-warning"><i data-lucide="alert-triangle"></i><div>
                <b>Printer X has failed 12 times this month</b> - Consider hardware replacement or vendor service.<br>
                <b>Dell Latitude 5520 repeated WiFi issues</b> - May need fleet-wide driver update.<br>
                <b>Floor 3 recurring connectivity issues</b> - Infrastructure investigation recommended.
            </div></div>
        </div>
    </div>
</div>
<?php require APP_ROOT . '/includes/layout_footer.php'; ?>
