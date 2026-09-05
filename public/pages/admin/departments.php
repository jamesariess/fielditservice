<?php
if (!defined('APP_ROOT')) { @header('Location: /fielditservice/'); exit; }

$page_title = 'Departments';
$active_menu = 'admin-departments';
$required_permission = 'departments.manage';
require APP_ROOT . '/includes/admin_guard.php';
require APP_ROOT . '/includes/layout_header.php';
?>
<div class="max-w-6xl mx-auto">
    <!-- Add Department Modal -->
    <div id="add-dept-modal" class="modal-overlay" style="display:none;">
        <div class="backdrop" onclick="closeModal('add-dept-modal')" style="position:fixed;inset:0;background:rgba(0,0,0,0.6);backdrop-filter:blur(6px);z-index:10000;"></div>
        <div class="modal-panel" style="position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);max-width:460px;background:#fff;border-radius:16px;z-index:10001;box-shadow:0 25px 60px rgba(0,0,0,0.3);max-height:90vh;overflow-y:auto;">
            <div style="padding:20px 24px;border-bottom:1px solid #e5e7eb;display:flex;justify-content:space-between;align-items:center;">
                <h2 style="font-size:18px;font-weight:700;color:#111827;">Add Department</h2>
                <button onclick="closeModal('add-dept-modal')" style="background:none;border:none;cursor:pointer;color:#94a3b8;font-size:20px;">&#10005;</button>
            </div>
            <form onsubmit="addDepartment(event)" style="padding:20px 24px;">
                <div style="margin-bottom:14px;">
                    <label style="display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:4px;">Department Name *</label>
                    <input name="name" required placeholder="e.g., Finance" class="form-input dark-input" style="width:100%;padding:10px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;">
                </div>
                <div style="margin-bottom:16px;">
                    <label style="display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:4px;">Description</label>
                    <textarea name="description" rows="2" placeholder="Brief description" class="form-input dark-input" style="width:100%;padding:10px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;resize:vertical;"></textarea>
                </div>
                <div style="display:flex;gap:8px;justify-content:flex-end;">
                    <button type="button" onclick="closeModal('add-dept-modal')" class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i data-lucide="plus" style="width:15px;height:15px;"></i> Add Department</button>
                </div>
            </form>
        </div>
    </div>

    <div class="page-hero fx-reveal">
        <div>
            <div style="display:flex;align-items:center;gap:14px;">
                <div class="page-hero-ico green"><i data-lucide="building-2"></i></div>
                <div>
                    <h1 class="page-hero-title">Departments</h1>
                    <p class="page-hero-sub">Manage organizational departments and teams</p>
                </div>
            </div>
        </div>
        <div class="page-hero-actions">
            <button onclick="openAddDeptModal()" class="btn btn-primary"><i data-lucide="plus" style="width:15px;height:15px;"></i> Add Department</button>
        </div>
    </div>
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <?php
        $depts = [
            ['name' => 'IT Department', 'users' => 3, 'contacts' => 5, 'desc' => 'Information Technology'],
            ['name' => 'Finance', 'users' => 12, 'contacts' => 2, 'desc' => 'Finance and Accounting'],
            ['name' => 'Marketing', 'users' => 8, 'contacts' => 2, 'desc' => 'Marketing and Sales'],
            ['name' => 'HR', 'users' => 5, 'contacts' => 2, 'desc' => 'Human Resources'],
            ['name' => 'Operations', 'users' => 15, 'contacts' => 3, 'desc' => 'Operations'],
            ['name' => 'Security', 'users' => 4, 'contacts' => 2, 'desc' => 'Security and Facilities'],
        ];
        foreach ($depts as $d): ?>
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5 hover:border-brand-300 transition">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-1"><?= $d['name'] ?></h3>
                <p class="text-sm text-gray-500 mb-3"><?= $d['desc'] ?></p>
                <div class="flex gap-4 text-xs text-gray-500">
                    <span>👥 <?= $d['users'] ?> users</span><span>📞 <?= $d['contacts'] ?> contacts</span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php require APP_ROOT . '/includes/layout_footer.php'; ?>
