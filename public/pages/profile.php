<?php
if (!defined('APP_ROOT')) { @header('Location: /fielditservice/'); exit; }

$page_title = 'Profile';
$active_menu = 'profile';
$initials2 = '';
foreach (explode(' ', Auth::userName() ?? 'User') as $p) { $initials2 .= strtoupper(substr($p, 0, 1)); if (strlen($initials2) >= 2) break; }
$profileLocation = ['company_name'=>'', 'location_name'=>'', 'address'=>'', 'latitude'=>'', 'longitude'=>''];
$profileCompanies = [];
if (!defined('DEMO_MODE') || !DEMO_MODE) {
    try {
        $saved = Database::fetch(
            "SELECT o.name AS company_name, l.name AS location_name, l.address, l.latitude, l.longitude
             FROM users u LEFT JOIN locations l ON u.location_id = l.id
             LEFT JOIN organizations o ON l.organization_id = o.id WHERE u.id = ?",
            [Auth::userId()]
        );
        if ($saved) { $profileLocation = array_merge($profileLocation, $saved); }
        $profileCompanies = Database::fetchAll("SELECT name FROM organizations ORDER BY name");
    } catch (Throwable $e) {}
}
require APP_ROOT . '/includes/layout_header.php';
?>
<div style="max-width:860px;margin:0 auto;">
    <!-- Page Hero -->
    <div class="page-hero fx-reveal">
        <div>
            <div style="display:flex;align-items:center;gap:14px;">
                <div class="page-hero-ico violet"><i data-lucide="user-round"></i></div>
                <div>
                    <h1 class="page-hero-title">My Profile</h1>
                    <p class="page-hero-sub">Manage your account details</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Identity card -->
    <div class="panel-card fx-reveal" style="--fx-delay:60ms;margin-bottom:20px;">
        <div style="padding:26px 28px;display:flex;align-items:center;gap:20px;flex-wrap:wrap;background:linear-gradient(120deg, rgba(37,99,235,.05), rgba(124,58,237,.06) 60%, transparent);">
            <div style="position:relative;">
                <div style="width:78px;height:78px;border-radius:50%;background:linear-gradient(135deg,#2563eb,#7c3aed);display:flex;align-items:center;justify-content:center;color:#fff;font-size:26px;font-weight:800;letter-spacing:.02em;box-shadow:0 8px 20px rgba(37,99,235,.3);">
                    <?= e($initials2) ?>
                </div>
                <div style="position:absolute;bottom:2px;right:2px;width:20px;height:20px;border-radius:50%;background:#22c55e;border:3px solid #fff;" title="Active"></div>
            </div>
            <div style="flex:1;min-width:200px;">
                <h2 style="font-size:20px;font-weight:800;color:#0f172a;" class="dark:text-gray-100"><?= e(Auth::userName()) ?></h2>
                <p style="font-size:13px;color:#64748b;margin-top:2px;"><?= e($_SESSION['role_name'] ?? 'User') ?></p>
                <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:10px;">
                    <span class="ubadge ubadge-blue"><span class="udot"></span><?= e($_SESSION['department_name'] ?? 'No Department') ?></span>
                    <span class="ubadge ubadge-green"><span class="udot"></span>Active</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Details form -->
    <div class="panel-card fx-reveal" style="--fx-delay:120ms;">
        <div class="panel-card-head">
            <div class="panel-card-title"><i data-lucide="settings-2"></i> Account Details</div>
        </div>
        <div class="panel-card-body">
            <form id="profile-form" data-no-spinner onsubmit="profileSave(event)">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;" class="sm2-grid">
                    <div>
                        <label style="display:block;font-size:12.5px;font-weight:600;color:#374151;margin-bottom:6px;" class="dark:text-gray-300">Full Name</label>
                        <input id="profile-name" type="text" value="<?= e(Auth::userName()) ?>" autocomplete="name" class="field-input">
                    </div>
                    <div>
                        <label style="display:block;font-size:12.5px;font-weight:600;color:#374151;margin-bottom:6px;" class="dark:text-gray-300">Email</label>
                        <input type="email" value="<?= e(Auth::userEmail() ?? '') ?>" class="field-input" disabled style="opacity:.75;">
                    </div>
                    <div>
                        <label style="display:block;font-size:12.5px;font-weight:600;color:#374151;margin-bottom:6px;" class="dark:text-gray-300">Department</label>
                        <input type="text" value="<?= e($_SESSION['department_name'] ?? '') ?>" class="field-input" disabled style="opacity:.75;">
                    </div>
                    <div>
                        <label style="display:block;font-size:12.5px;font-weight:600;color:#374151;margin-bottom:6px;" class="dark:text-gray-300">Role</label>
                        <input type="text" value="<?= e($_SESSION['role_name'] ?? 'User') ?>" class="field-input" disabled style="opacity:.75;">
                    </div>
                    <div>
                        <label class="profile-label">Default Company</label>
                        <input id="profile-company" type="text" list="profile-company-list" value="<?= e($profileLocation['company_name']) ?>" class="field-input" placeholder="Select or type a company" autocomplete="off">
                        <datalist id="profile-company-list">
                            <?php foreach ($profileCompanies as $profileCompany): ?>
                                <option value="<?= e($profileCompany['name']) ?>"></option>
                            <?php endforeach; ?>
                        </datalist>
                        <div class="profile-help">Choose an existing company or type a new one.</div>
                    </div>
                    <div>
                        <label class="profile-label">Location Name</label>
                        <input id="profile-location-name" type="text" value="<?= e($profileLocation['location_name']) ?>" class="field-input" placeholder="e.g. Main Office">
                    </div>
                </div>
                <div style="margin-top:16px;">
                    <label class="profile-label">Default Company Address</label>
                    <input id="profile-address" type="text" value="<?= e($profileLocation['address']) ?>" class="field-input" placeholder="Address used as the starting ticket location">
                    <div id="profile-map" style="height:240px;margin-top:10px;border:1px solid #dbe2ea;border-radius:8px;overflow:hidden;background:#eef2f7;"></div>
                    <input id="profile-lat" type="hidden" value="<?= e($profileLocation['latitude']) ?>">
                    <input id="profile-lng" type="hidden" value="<?= e($profileLocation['longitude']) ?>">
                    <div id="profile-route-status" class="profile-route-status" aria-live="polite"></div>
                    <div style="margin-top:8px;display:flex;gap:8px;align-items:center;">
                        <button type="button" class="btn btn-sm btn-secondary" onclick="profileUseLocation()"><i data-lucide="locate-fixed"></i> Use my location</button>
                        <button type="button" class="btn btn-sm btn-secondary" onclick="profileFindAddress()"><i data-lucide="search"></i> Find address</button>
                        <span style="font-size:11.5px;color:#64748b;">Click the map to set the exact company location.</span>
                    </div>
                </div>
                <div style="margin-top:20px;display:flex;justify-content:flex-end;">
                    <button type="submit" class="btn btn-primary">
                        <i data-lucide="save" style="width:15px;height:15px;"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
@media (max-width:640px){ .sm2-grid { grid-template-columns: 1fr !important; } }
.profile-label { display:block;font-size:12.5px;font-weight:600;color:#374151;margin-bottom:6px; }
.profile-help { margin-top:5px;font-size:11.5px;color:#64748b; }
.profile-route-status { margin-top:10px;padding:10px 12px;border:1px solid #fbbf24;border-radius:7px;background:#fffbeb;color:#92400e;font-size:12px;font-weight:600; }
.profile-route-status.ready { border-color:#86efac;background:#f0fdf4;color:#166534; }
.dark .profile-label { color:#cbd5e1; }
</style>

<link rel="stylesheet" href="<?= e(app_base()) ?>assets/lib/leaflet.css">
<script src="<?= e(app_base()) ?>assets/lib/leaflet.js"></script>
<script>
var profileMap = null, profileMarker = null;
function profileCreateMarker(lat, lng) {
    return L.circleMarker([lat, lng], {
        radius: 9,
        color: '#ffffff',
        weight: 3,
        fillColor: '#2563eb',
        fillOpacity: 1,
        className: 'profile-map-pin'
    }).addTo(profileMap);
}
function profileUpdateRouteStatus() {
    var lat = document.getElementById('profile-lat').value;
    var lng = document.getElementById('profile-lng').value;
    var status = document.getElementById('profile-route-status');
    var ready = lat !== '' && lng !== '';
    status.classList.toggle('ready', ready);
    status.textContent = ready
        ? 'Routing start point ready. Ticket distance and ETA will begin from this company location.'
        : 'Map pin required. Search the address, use your location, or click the map before saving.';
}
function profileSetPoint(lat, lng) {
    document.getElementById('profile-lat').value = Number(lat).toFixed(7);
    document.getElementById('profile-lng').value = Number(lng).toFixed(7);
    if (profileMarker) profileMarker.setLatLng([lat, lng]);
    else profileMarker = profileCreateMarker(lat, lng);
    profileMap.setView([lat, lng], 16);
    profileUpdateRouteStatus();
}
function profileInitMap() {
    if (!window.L || profileMap) return;
    var lat = parseFloat(document.getElementById('profile-lat').value) || 14.5995;
    var lng = parseFloat(document.getElementById('profile-lng').value) || 120.9842;
    profileMap = L.map('profile-map').setView([lat, lng], (document.getElementById('profile-lat').value ? 16 : 12));
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom:19, attribution:'&copy; OpenStreetMap' }).addTo(profileMap);
    if (document.getElementById('profile-lat').value) profileMarker = profileCreateMarker(lat, lng);
    profileMap.on('click', function(e) { profileSetPoint(e.latlng.lat, e.latlng.lng); });
}
function profileUseLocation() {
    if (!navigator.geolocation) return showToast('Location is not available on this device.', 'warning');
    navigator.geolocation.getCurrentPosition(function(pos) { profileSetPoint(pos.coords.latitude, pos.coords.longitude); }, function(){ showToast('Could not get your location.', 'warning'); });
}
function profileFindAddress() {
    var address = document.getElementById('profile-address').value.trim();
    if (!address) return;
    var button = document.querySelector('button[onclick="profileFindAddress()"]');
    if (button) setButtonLoading(button, true, 'Finding…');
    api('/api/geocode?q=' + encodeURIComponent(address), { timeoutMs: 10000 })
        .then(function(result){
            profileSetPoint(result.lat, result.lng);
            showToast('Location found using ' + result.provider + '.', 'success');
        })
        .catch(function(error){ showToast(error.message || 'Address was not found. Paste a Google Maps link or click the map.', 'warning'); })
        .finally(function(){ if (button) setButtonLoading(button, false); });
}
function profileSave(e) {
    e.preventDefault();
    var company = document.getElementById('profile-company').value.trim();
    var address = document.getElementById('profile-address').value.trim();
    var lat = document.getElementById('profile-lat').value;
    var lng = document.getElementById('profile-lng').value;
    if (!company) return showToast('Select or enter your assigned company.', 'warning');
    if (!address) return showToast('Enter the company address used for routing.', 'warning');
    if (!lat || !lng) return showToast('Set the exact company location on the map before saving.', 'warning');
    var btn = e.target.querySelector('button[type="submit"]');
    if (!btn || btn.disabled) return;
    setButtonLoading(btn, true, 'Saving…');
    api('/api/profile/', { method:'POST', timeoutMs:10000, body:{
        name: document.getElementById('profile-name').value,
        company_name: company,
        location_name: document.getElementById('profile-location-name').value,
        address: document.getElementById('profile-address').value,
        latitude: document.getElementById('profile-lat').value,
        longitude: document.getElementById('profile-lng').value
    }}).then(function(res){
        showToast(res.success ? 'Profile and company location saved.' : (res.error || 'Save failed.'), res.success ? 'success' : 'error');
    }).catch(function(err){ showToast(err.message, 'error'); })
      .finally(function(){ setButtonLoading(btn, false); });
}
function profileBoot() {
    profileInitMap();
    profileUpdateRouteStatus();
    document.getElementById('profile-address').addEventListener('input', function() {
        document.getElementById('profile-lat').value = '';
        document.getElementById('profile-lng').value = '';
        if (profileMarker && profileMap) { profileMap.removeLayer(profileMarker); profileMarker = null; }
        profileUpdateRouteStatus();
    });
}
if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', profileBoot); else profileBoot();
</script>
<?php require APP_ROOT . '/includes/layout_footer.php'; ?>
