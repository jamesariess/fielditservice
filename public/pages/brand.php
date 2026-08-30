<?php
if (!defined('APP_ROOT')) { @header('Location: /fielditservice/'); exit; }

$page_title = 'Brand';
$active_menu = 'equipment';
require APP_ROOT . '/includes/layout_header.php';

$brand = isset($_GET['name']) ? trim($_GET['name']) : '';
if (empty($brand)) { header('Location: ' . $urlBase . 'equipment'); exit; }

// Fetch equipment for this brand
$equipment = Database::fetchAll(
    "SELECT id, manufacturer, model_name, device_type, category, year, cpu, ram, storage,
            display_spec, ports, known_issues, tools_needed, repair_guides, location, status,
            image_url, disassembly_guide, assembly_guide, guide_videos
     FROM equipment WHERE deleted_at IS NULL AND LOWER(manufacturer) = LOWER(?)
     ORDER BY device_type, model_name",
    [$brand]
);

$page_title = $brand . ' Equipment';
?>
<div>
    <div style="margin-bottom:20px;" class="fx-reveal">
        <a href="<?= $urlBase ?>equipment" style="display:inline-flex;align-items:center;gap:6px;font-size:13px;color:#64748b;text-decoration:none;margin-bottom:12px;" onmouseover="this.style.color='#2563eb'" onmouseout="this.style.color='#64748b'">
            <i data-lucide="arrow-left" style="width:14px;height:14px;"></i> Back to Equipment
        </a>
        <div style="display:flex;align-items:center;gap:14px;">
            <div class="page-hero-ico green"><i data-lucide="badge-check"></i></div>
            <div>
                <h1 style="font-size:24px;font-weight:800;color:#111827;letter-spacing:-0.03em;" class="dark:text-gray-100"><?= e(ucfirst($brand)) ?></h1>
                <p style="font-size:13px;color:#64748b;margin-top:2px;"><?= count($equipment) ?> device models in this brand</p>
            </div>
        </div>
    </div>

    <!-- Search -->
    <div style="margin-bottom:20px;position:relative;--fx-delay:50ms;" class="fx-reveal">
        <i data-lucide="search" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);width:15px;height:15px;color:#94a3b8;"></i>
        <input type="text" id="brand-search" placeholder="Search models..." class="form-input" style="padding-left:36px;" oninput="filterBrand()">
    </div>

    <!-- Equipment Grid -->
    <div id="brand-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:14px;">
        <?php foreach ($equipment as $e):
            $issueCount = !empty($e['known_issues']) ? count(explode(',', $e['known_issues'])) : 0;
            $hasGuides = !empty($e['disassembly_guide']) || !empty($e['assembly_guide']);
        ?>
        <div class="eq-brand-card" data-search="<?= e(strtolower($e['model_name'] . ' ' . $e['device_type'])) ?>" onclick="openBrandEqViewer(<?= $e['id'] ?>)" style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;cursor:pointer;transition:all 0.15s;overflow:hidden;" onmouseover="this.style.borderColor='#2563eb';this.style.boxShadow='0 4px 12px rgba(37,99,235,0.1)'" onmouseout="this.style.borderColor='#e5e7eb';this.style.boxShadow='none'">
            <?php if ($e['image_url']): ?>
                <div style="height:140px;background:#f8fafc;display:flex;align-items:center;justify-content:center;padding:12px;">
                    <img src="<?= e($e['image_url']) ?>" alt="<?= e($e['model_name']) ?>" style="max-height:100%;max-width:100%;object-fit:contain;" onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                    <div style="display:none;align-items:center;justify-content:center;"><i data-lucide="package" style="width:36px;height:36px;color:#cbd5e1;"></i></div>
                </div>
            <?php else: ?>
                <div style="height:80px;background:#f8fafc;display:flex;align-items:center;justify-content:center;">
                    <i data-lucide="package" style="width:32px;height:32px;color:#cbd5e1;"></i>
                </div>
            <?php endif; ?>
            <div style="padding:14px 16px;">
                <div style="display:flex;gap:6px;margin-bottom:8px;flex-wrap:wrap;">
                    <span style="padding:2px 8px;background:#eff6ff;border-radius:12px;font-size:11px;color:#2563eb;font-weight:600;"><?= e($e['device_type']) ?></span>
                    <?php if ($e['year']): ?><span style="padding:2px 8px;background:#f1f5f9;border-radius:12px;font-size:11px;color:#475569;font-weight:600;"><?= e($e['year']) ?></span><?php endif; ?>
                    <?php if ($hasGuides): ?><span style="padding:2px 8px;background:#f0fdf4;border-radius:12px;font-size:11px;color:#166534;font-weight:600;">&#128295; Guides</span><?php endif; ?>
                    <?php if ($issueCount > 0): ?><span style="padding:2px 8px;background:#fef3c7;border-radius:12px;font-size:11px;color:#92400e;font-weight:600;"><?= $issueCount ?> issues</span><?php endif; ?>
                </div>
                <h3 style="font-size:14px;font-weight:700;color:#111827;margin-bottom:4px;"><?= e($e['model_name']) ?></h3>
                <?php if ($e['cpu']): ?>
                    <p style="font-size:12px;color:#64748b;line-height:1.4;"><?= e($e['cpu']) ?><?php if ($e['ram']): ?> · <?= e($e['ram']) ?><?php endif; ?></p>
                <?php endif; ?>
                <?php if ($e['tools_needed']): ?>
                    <div style="margin-top:8px;display:flex;gap:4px;flex-wrap:wrap;">
                        <?php $tools = array_slice(array_map('trim', explode(',', $e['tools_needed'])), 0, 3); ?>
                        <?php foreach ($tools as $t): ?>
                            <span style="padding:1px 6px;background:#f1f5f9;border-radius:4px;font-size:10px;color:#64748b;"><?= e($t) ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php if (empty($equipment)): ?>
        <div style="text-align:center;padding:40px;">
            <i data-lucide="package" style="width:36px;height:36px;color:#cbd5e1;margin-bottom:8px;"></i>
            <p style="color:#94a3b8;font-size:13px;">No equipment found for <?= e(ucfirst($brand)) ?>.</p>
        </div>
    <?php endif; ?>
</div>

<!-- Equipment Viewer Modal -->
<div id="brand-eq-overlay" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.5);z-index:9998;backdrop-filter:blur(4px);" onclick="closeBrandEqViewer()"></div>
<div id="brand-eq-panel" style="display:none;position:fixed;top:0;right:0;width:min(700px,95vw);height:100vh;background:#fff;z-index:9999;box-shadow:-4px 0 24px rgba(0,0,0,0.15);overflow-y:auto;">
    <div style="position:sticky;top:0;background:#fff;border-bottom:1px solid #e5e7eb;padding:16px 24px;display:flex;align-items:center;justify-content:space-between;z-index:1;">
        <h2 id="brand-eq-title" style="font-size:16px;font-weight:800;color:#111827;">Equipment Details</h2>
        <button onclick="closeBrandEqViewer()" style="background:none;border:none;cursor:pointer;padding:4px;border-radius:6px;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='none'"><i data-lucide="x" style="width:18px;height:18px;color:#64748b;"></i></button>
    </div>
    <div id="brand-eq-content" style="padding:24px;"></div>
</div>

<script>
var brandEqData = <?= json_encode($equipment) ?>;

function filterBrand() {
    var search = document.getElementById('brand-search').value.toLowerCase();
    document.querySelectorAll('.eq-brand-card').forEach(function(card) {
        card.style.display = (!search || card.dataset.search.indexOf(search) !== -1) ? '' : 'none';
    });
}

function openBrandEqViewer(id) {
    var art = null;
    for (var i = 0; i < brandEqData.length; i++) { if (brandEqData[i].id == id) { art = brandEqData[i]; break; } }
    if (!art) return;
    document.getElementById('brand-eq-title').textContent = art.manufacturer + ' ' + art.model_name;

    var html = '';
    // Image
    if (art.image_url) {
        html += '<div style="text-align:center;margin-bottom:20px;padding:20px;background:#f8fafc;border-radius:12px;border:1px solid #e5e7eb;">';
        html += '<img src="' + esc(art.image_url) + '" alt="" style="max-height:180px;max-width:100%;object-fit:contain;" onerror="this.style.display=\'none\'">';
        html += '</div>';
    }
    // Header
    html += '<div style="margin-bottom:16px;"><h1 style="font-size:18px;font-weight:800;color:#111827;">' + esc(art.manufacturer) + ' ' + esc(art.model_name) + '</h1>';
    html += '<div style="display:flex;gap:6px;margin-top:6px;flex-wrap:wrap;">';
    html += '<span style="padding:2px 8px;background:#eff6ff;border-radius:12px;font-size:11px;color:#2563eb;font-weight:600;">' + esc(art.device_type) + '</span>';
    if (art.year) html += '<span style="padding:2px 8px;background:#f1f5f9;border-radius:12px;font-size:11px;color:#475569;font-weight:600;">' + esc(art.year) + '</span>';
    if (art.location) html += '<span style="padding:2px 8px;background:#f0fdf4;border-radius:12px;font-size:11px;color:#166534;font-weight:600;">' + esc(art.location) + '</span>';
    html += '</div></div>';

    // Specs
    var specs = [['CPU',art.cpu],['RAM',art.ram],['Storage',art.storage],['Display',art.display_spec],['Ports',art.ports]];
    var specHtml = '';
    specs.forEach(function(s){if(s[1])specHtml+='<div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid #f1f5f9;"><span style="font-size:12px;color:#64748b;">'+s[0]+'</span><span style="font-size:12px;color:#111827;font-weight:600;text-align:right;max-width:60%;">'+esc(s[1])+'</span></div>';});
    if (specHtml) html += '<div style="background:#f8fafc;border:1px solid #e5e7eb;border-radius:10px;padding:14px 16px;margin-bottom:16px;"><h3 style="font-size:13px;font-weight:700;color:#374151;margin-bottom:8px;">Specifications</h3>'+specHtml+'</div>';

    // Tools as image grid with hover
    if (art.tools_needed) {
        var tools = []; try { tools = JSON.parse(art.tools_needed); } catch(e) { tools = art.tools_needed.split(',').map(function(t){return {name:t.trim(),desc:'',howto:'',image:''};}); }
        if (tools.length && tools[0] && tools[0].name) {
            html += '<div style="margin-bottom:16px;"><h3 style="font-size:13px;font-weight:700;color:#374151;margin-bottom:8px;">Required Tools ('+tools.length+')</h3>';
            html += '<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(100px,1fr));gap:8px;">';
            tools.forEach(function(t){
                html += '<div onclick="showBrandToolPopup(\''+esc(t.name).replace(/'/g,"\\'")+'\',\''+esc(t.desc).replace(/'/g,"\\'")+'\',\''+esc(t.howto).replace(/'/g,"\\'")+'\',\''+esc(t.image).replace(/'/g,"\\'")+'\')" style="position:relative;border-radius:8px;overflow:hidden;cursor:pointer;border:1px solid #e5e7eb;background:#f8fafc;text-align:center;" onmouseover="this.style.borderColor=\'#2563eb\';var o=this.querySelector(\'.tov\');if(o)o.style.display=\'flex\'" onmouseout="this.style.borderColor=\'#e5e7eb\';var o=this.querySelector(\'.tov\');if(o)o.style.display=\'none\'">';
                if(t.image)html+='<img src="'+esc(t.image)+'" style="width:100%;height:80px;object-fit:cover;" onerror="this.style.display=\'none\'">';
                else html+='<div style="height:80px;display:flex;align-items:center;justify-content:center;"><i data-lucide="wrench" style="width:24px;height:24px;color:#cbd5e1;"></i></div>';
                html+='<div style="padding:4px 6px;font-size:10px;font-weight:600;color:#374151;line-height:1.3;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">'+esc(t.name)+'</div>';
                html+='<div class="tov" style="display:none;position:absolute;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.75);color:#fff;align-items:center;justify-content:center;padding:8px;font-size:11px;font-weight:700;border-radius:8px;">'+esc(t.name)+'</div>';
                html+='</div>';
            });
            html+='</div></div>';
        }
    }

    // Disassembly with video links
    if (art.disassembly_guide) {
        var dSteps = art.disassembly_guide.split('|').filter(function(l){return l.trim();});
        html += '<div style="margin-bottom:16px;"><h3 style="font-size:13px;font-weight:700;color:#991b1b;margin-bottom:8px;">Disassembly Guide ('+dSteps.length+' steps)</h3>';
        html += '<div style="background:#fef2f2;border:1px solid #fecaca;border-radius:10px;padding:14px 16px;">';
        dSteps.forEach(function(step,i){
            var videoUrl = '';
            var videoMatch = step.match(/\[video:(https?:\/\/[^\]]+)\]/);
            if (videoMatch) { videoUrl = videoMatch[1]; step = step.replace(/\s*\[video:[^\]]+\]/, ''); }
            html += '<div style="padding:8px 0;'+(i<dSteps.length-1?'border-bottom:1px solid #fecaca;':'')+'">';
            html += '<div style="display:flex;gap:10px;"><div style="width:22px;height:22px;border-radius:50%;background:#dc2626;color:#fff;display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:800;flex-shrink:0;">'+(i+1)+'</div>';
            html += '<div style="flex:1;"><div style="font-size:12px;color:#991b1b;line-height:1.5;">'+esc(step.replace(/^\d+\.\s*/,''))+'</div>';
            if (videoUrl) {
                html += '<a href="'+esc(videoUrl)+'" target="_blank" style="display:inline-flex;align-items:center;gap:4px;margin-top:4px;padding:3px 8px;background:#fee2e2;border-radius:6px;font-size:10px;color:#991b1b;font-weight:600;text-decoration:none;" onmouseover="this.style.background=#fecaca" onmouseout="this.style.background=#fee2e2"><i data-lucide="play-circle" style="width:12px;height:12px;"></i> Watch Video</a>';
            }
            html += '</div></div></div>';
        });
        html += '</div></div>';
    }

    // Assembly with video links
    if (art.assembly_guide) {
        var aSteps = art.assembly_guide.split('|').filter(function(l){return l.trim();});
        html += '<div style="margin-bottom:16px;"><h3 style="font-size:13px;font-weight:700;color:#166534;margin-bottom:8px;">Assembly Guide ('+aSteps.length+' steps)</h3>';
        html += '<div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:14px 16px;">';
        aSteps.forEach(function(step,i){
            var videoUrl = '';
            var videoMatch = step.match(/\[video:(https?:\/\/[^\]]+)\]/);
            if (videoMatch) { videoUrl = videoMatch[1]; step = step.replace(/\s*\[video:[^\]]+\]/, ''); }
            html += '<div style="padding:8px 0;'+(i<aSteps.length-1?'border-bottom:1px solid #bbf7d0;':'')+'">';
            html += '<div style="display:flex;gap:10px;"><div style="width:22px;height:22px;border-radius:50%;background:#16a34a;color:#fff;display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:800;flex-shrink:0;">'+(i+1)+'</div>';
            html += '<div style="flex:1;"><div style="font-size:12px;color:#166534;line-height:1.5;">'+esc(step.replace(/^\d+\.\s*/,''))+'</div>';
            if (videoUrl) {
                html += '<a href="'+esc(videoUrl)+'" target="_blank" style="display:inline-flex;align-items:center;gap:4px;margin-top:4px;padding:3px 8px;background:#dcfce7;border-radius:6px;font-size:10px;color:#166534;font-weight:600;text-decoration:none;" onmouseover="this.style.background=#bbf7d0" onmouseout="this.style.background=#dcfce7"><i data-lucide="play-circle" style="width:12px;height:12px;"></i> Watch Video</a>';
            }
            html += '</div></div></div>';
        });
        html += '</div></div>';
    }

    // Known Issues
    if (art.known_issues) {
        var issues = art.known_issues.split(',').map(function(s){return s.trim();}).filter(function(s){return s;});
        html += '<div style="margin-bottom:16px;"><h3 style="font-size:13px;font-weight:700;color:#374151;margin-bottom:8px;">Known Issues ('+issues.length+')</h3>';
        issues.forEach(function(is){html+='<div style="padding:8px 12px;background:#fef3c7;border-radius:8px;border-left:3px solid #d97706;margin-bottom:6px;"><div style="font-size:12px;color:#92400e;line-height:1.5;">'+esc(is)+'</div></div>';});
        html += '</div>';
    }

    document.getElementById('brand-eq-content').innerHTML = html;
    document.getElementById('brand-eq-overlay').style.display = 'block';
    document.getElementById('brand-eq-panel').style.display = 'block';
    if (typeof lucide !== 'undefined') lucide.createIcons();
}
function closeBrandEqViewer() { document.getElementById('brand-eq-overlay').style.display='none'; document.getElementById('brand-eq-panel').style.display='none'; }
function esc(s){if(!s)return'';var d=document.createElement('div');d.textContent=s;return d.innerHTML;}
function showBrandToolPopup(name,desc,howto,image){
    var body='<div style="text-align:center;margin-bottom:12px;">';
    if(image)body+='<img src="'+image+'" style="max-height:120px;border-radius:8px;" onerror="this.style.display=\'none\'">';
    body+='</div><h3 style="font-size:15px;font-weight:800;color:#111827;margin-bottom:8px;">'+esc(name)+'</h3>';
    if(desc)body+='<p style="font-size:12px;color:#64748b;margin-bottom:8px;">'+esc(desc)+'</p>';
    if(howto)body+='<div style="padding:8px 12px;background:#eff6ff;border-radius:8px;border-left:3px solid #2563eb;"><div style="font-size:11px;font-weight:700;color:#2563eb;margin-bottom:2px;">How to use:</div><div style="font-size:12px;color:#374151;">'+esc(howto)+'</div></div>';
    Swal.fire({title:name,html:body,icon:null,confirmButtonColor:'#2563eb',confirmButtonText:'Close',width:400});
}
</script>
<?php require APP_ROOT . '/includes/layout_footer.php'; ?>
