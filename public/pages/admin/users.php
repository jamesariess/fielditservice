<?php
if (!defined('APP_ROOT')) { @header('Location: /fielditservice/'); exit; }

$page_title = 'User Management';
$active_menu = 'admin-users';
$required_permission = 'users.manage';
require APP_ROOT . '/includes/admin_guard.php';
require APP_ROOT . '/includes/layout_header.php';

$users = Database::fetchAll(
    "SELECT u.id, u.full_name, u.email, u.status, u.last_login, u.created_at,
            u.role_id, u.department_id,
            r.name as role_name, d.name as department_name
     FROM users u
     LEFT JOIN roles r ON u.role_id = r.id
     LEFT JOIN departments d ON u.department_id = d.id
     ORDER BY u.created_at DESC"
);
$roles = Database::fetchAll("SELECT id, name FROM roles ORDER BY name");
$departments = Database::fetchAll("SELECT id, name FROM departments ORDER BY name");

$countTotal = count($users);
$countActive = 0;
foreach ($users as $u) { if ($u['status'] === 'active') $countActive++; }
$countRoles = count($roles);
?>

<div id="invite-user-modal" class="modal-overlay" style="display:none;">
    <div class="backdrop" onclick="closeModal('invite-user-modal')"></div>
    <div class="modal-panel" style="max-width:480px;margin-top:8vh;">
        <div style="padding:20px 24px;border-bottom:1px solid #e5e7eb;display:flex;justify-content:space-between;align-items:center;">
            <h2 style="font-size:18px;font-weight:700;color:#111827;">Invite User</h2>
            <button onclick="closeModal('invite-user-modal')" style="background:none;border:none;cursor:pointer;color:#94a3b8;font-size:20px;">&#10005;</button>
        </div>
        <form onsubmit="inviteUser(event)" style="padding:20px 24px;">
            <div style="margin-bottom:14px;">
                <label style="display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:4px;">Full Name *</label>
                <input name="name" required placeholder="Juan Dela Cruz" style="width:100%;padding:10px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;">
            </div>
            <div style="margin-bottom:14px;">
                <label style="display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:4px;">Email *</label>
                <input name="email" type="email" required placeholder="user@company.com" style="width:100%;padding:10px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;">
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px;">
                <div>
                    <label style="display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:4px;">Role</label>
                    <select name="role" style="width:100%;padding:10px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;">
                        <?php foreach ($roles as $r): ?>
                            <option value="<?= $r['id'] ?>"><?= e($r['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label style="display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:4px;">Department</label>
                    <select name="department" style="width:100%;padding:10px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;">
                        <?php foreach ($departments as $d): ?>
                            <option value="<?= $d['id'] ?>"><?= e($d['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div style="display:flex;gap:8px;justify-content:flex-end;">
                <button type="button" onclick="closeModal('invite-user-modal')" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-primary"><i data-lucide="user-plus" style="width:14px;height:14px;"></i> Send Invite</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit User Modal -->
<div id="edit-user-modal" class="modal-overlay" style="display:none;">
    <div class="backdrop" onclick="closeModal('edit-user-modal')"></div>
    <div class="modal-panel" style="max-width:480px;margin-top:8vh;">
        <div style="padding:20px 24px;border-bottom:1px solid #e5e7eb;display:flex;justify-content:space-between;align-items:center;">
            <h2 style="font-size:18px;font-weight:700;color:#111827;">Edit User</h2>
            <button onclick="closeModal('edit-user-modal')" style="background:none;border:none;cursor:pointer;color:#94a3b8;font-size:20px;">&#10005;</button>
        </div>
        <form id="edit-user-form" onsubmit="return false;" style="padding:20px 24px;">
            <input type="hidden" id="edit-user-id">
            <div style="margin-bottom:14px;">
                <label style="display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:4px;">Full Name</label>
                <input id="edit-user-name" style="width:100%;padding:10px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;">
            </div>
            <div style="margin-bottom:14px;">
                <label style="display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:4px;">Email</label>
                <input id="edit-user-email" type="email" style="width:100%;padding:10px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;">
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px;">
                <div>
                    <label style="display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:4px;">Role</label>
                    <select id="edit-user-role" style="width:100%;padding:10px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;">
                        <?php foreach ($roles as $r): ?>
                            <option value="<?= $r['id'] ?>"><?= e($r['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label style="display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:4px;">Department</label>
                    <select id="edit-user-dept" style="width:100%;padding:10px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;">
                        <?php foreach ($departments as $d): ?>
                            <option value="<?= $d['id'] ?>"><?= e($d['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div style="display:flex;gap:8px;justify-content:flex-end;">
                <button type="button" onclick="closeModal('edit-user-modal')" class="btn btn-secondary">Cancel</button>
                <button type="button" onclick="saveEditUser()" class="btn btn-primary"><i data-lucide="save" style="width:14px;height:14px;"></i> Save Changes</button>
            </div>
        </form>
    </div>
</div>

<div class="max-w-6xl mx-auto">
    <div class="page-hero fx-reveal">
        <div>
            <div style="display:flex;align-items:center;gap:14px;">
                <div class="page-hero-ico violet"><i data-lucide="users"></i></div>
                <div>
                    <h1 class="page-hero-title">User Management</h1>
                    <p class="page-hero-sub">Manage users, roles, and permissions</p>
                </div>
            </div>
        </div>
        <div class="page-hero-actions">
            <button onclick="openInviteUserModal()" class="btn btn-primary"><i data-lucide="user-plus" style="width:15px;height:15px;"></i> Invite User</button>
        </div>
    </div>
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-3 text-center">
            <div class="text-xl font-bold text-gray-900 dark:text-white"><?= $countTotal ?></div>
            <div class="text-xs text-gray-500">Total Users</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-3 text-center">
            <div class="text-xl font-bold text-green-600"><?= $countActive ?></div>
            <div class="text-xs text-gray-500">Active</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-3 text-center">
            <div class="text-xl font-bold text-brand-600"><?= $countRoles ?></div>
            <div class="text-xs text-gray-500">Roles</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-3 text-center">
            <div class="text-xl font-bold text-yellow-600">0</div>
            <div class="text-xs text-gray-500">Pending Invites</div>
        </div>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <table class="w-full">
            <thead><tr class="border-b border-gray-200 dark:border-gray-700">
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">User</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Role</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase hide-mobile">Department</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Status</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase hide-mobile">Last Login</th>
                <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Actions</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                <?php foreach ($users as $u):
                    $initials = '';
                    foreach (explode(' ', $u['full_name']) as $p) { $initials .= strtoupper(substr($p, 0, 1)); if (strlen($initials) >= 2) break; }
                    $lastLogin = $u['last_login'] ? time_ago($u['last_login']) : 'Never';
                ?>
                    <tr>
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-3">
                                <div style="width:36px;height:36px;border-radius:10px;background:#eff6ff;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;color:#2563eb;flex-shrink:0;"><?= $initials ?></div>
                                <div>
                                    <div class="text-sm font-semibold text-gray-900 dark:text-white"><?= e($u['full_name']) ?></div>
                                    <div class="text-xs text-gray-500"><?= e($u['email']) ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3"><span class="text-sm text-gray-600"><?= e($u['role_name'] ?? '—') ?></span></td>
                        <td class="px-5 py-3 hide-mobile"><span class="text-sm text-gray-500"><?= e($u['department_name'] ?? '—') ?></span></td>
                        <td class="px-5 py-3">
                            <?php if ($u['status'] === 'active'): ?>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Active</span>
                            <?php else: ?>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500"><?= e(ucfirst($u['status'])) ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="px-5 py-3 hide-mobile"><span class="text-xs text-gray-400"><?= $lastLogin ?></span></td>
                        <td class="px-5 py-3 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <button onclick="editUser(<?= $u['id'] ?>)" class="p-1.5 text-gray-400 hover:text-brand-600 rounded hover:bg-brand-50"><i data-lucide="pencil" class="w-4 h-4"></i></button>
                                <button onclick="deleteUser(<?= $u['id'] ?>)" class="p-1.5 text-gray-400 hover:text-red-600 rounded hover:bg-red-50"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require APP_ROOT . '/includes/layout_footer.php'; ?>
<script>
    window.editUserCache = <?php
        $cache = [];
        foreach ($users as $u) {
            $cache[$u['id']] = ['id'=>$u['id'],'full_name'=>$u['full_name'],'email'=>$u['email'],'role_id'=>$u['role_id'],'department_id'=>$u['department_id']];
        }
        echo json_encode($cache);
    ?>;
</script>
