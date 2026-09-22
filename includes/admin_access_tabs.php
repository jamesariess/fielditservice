<?php
if (!defined('APP_ROOT')) { exit; }
$accessSection = $accessSection ?? 'users';
$accessTabs = [
    ['id' => 'users', 'label' => 'Users', 'icon' => 'users', 'url' => '/admin/users', 'permission' => 'users.manage'],
    ['id' => 'roles', 'label' => 'Roles & Permissions', 'icon' => 'shield-check', 'url' => '/admin/roles', 'permission' => 'roles.manage'],
    ['id' => 'departments', 'label' => 'Departments', 'icon' => 'building-2', 'url' => '/admin/departments', 'permission' => 'departments.manage'],
];
?>
<nav class="admin-access-tabs" aria-label="Users and access administration">
    <?php foreach ($accessTabs as $accessTab): ?>
        <?php if (!Auth::hasPermission($accessTab['permission'])) continue; ?>
        <a href="<?= e(app_base() . ltrim($accessTab['url'], '/')) ?>"
           class="admin-access-tab <?= $accessSection === $accessTab['id'] ? 'active' : '' ?>"
           <?= $accessSection === $accessTab['id'] ? 'aria-current="page"' : '' ?>>
            <i data-lucide="<?= e($accessTab['icon']) ?>"></i>
            <span><?= e($accessTab['label']) ?></span>
        </a>
    <?php endforeach; ?>
</nav>
<style>
.admin-access-tabs { display:flex;align-items:center;gap:4px;margin:0 auto 18px;max-width:72rem;padding:5px;border:1px solid #dbe3ef;border-radius:8px;background:#fff;overflow-x:auto; }
.admin-access-tab { min-height:38px;display:inline-flex;align-items:center;justify-content:center;gap:7px;padding:8px 14px;border-radius:6px;color:#64748b;font-size:13px;font-weight:700;text-decoration:none;white-space:nowrap;transition:background .15s,color .15s,box-shadow .15s; }
.admin-access-tab i { width:16px;height:16px; }
.admin-access-tab:hover { color:#1d4ed8;background:#f1f5f9; }
.admin-access-tab.active { color:#1d4ed8;background:#eff6ff;box-shadow:inset 0 0 0 1px #bfdbfe; }
.dark .admin-access-tabs { background:#111827;border-color:#334155; }
.dark .admin-access-tab { color:#94a3b8; }
.dark .admin-access-tab:hover,.dark .admin-access-tab.active { color:#93c5fd;background:#1e293b; }
@media (max-width:640px) { .admin-access-tab { flex:1;padding:8px 10px; } }
</style>
