<?php
if (!defined('APP_ROOT')) { @header('Location: /fielditservice/'); exit; }

$page_title = 'Profile';
$active_menu = 'profile';
$initials2 = '';
foreach (explode(' ', Auth::userName() ?? 'User') as $p) { $initials2 .= strtoupper(substr($p, 0, 1)); if (strlen($initials2) >= 2) break; }
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
                        <input type="text" value="<?= e(Auth::userName()) ?>" autocomplete="name" class="field-input">
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
</style>

<script>
function profileSave(e) {
    e.preventDefault();
    var btn = e.target.querySelector('button[type="submit"]');
    if (!btn || btn.disabled) return;
    setButtonLoading(btn, true, 'Saving…');
    setTimeout(function() {
        setButtonLoading(btn, false);
        showToast('Profile saved successfully!', 'success');
    }, 900);
}
</script>
<?php require APP_ROOT . '/includes/layout_footer.php'; ?>
