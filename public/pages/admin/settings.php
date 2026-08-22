<?php
$page_title = 'System Settings';
$active_menu = 'admin-settings';
require APP_ROOT . '/includes/layout_header.php';
Auth::requirePermission('system.settings');
?>

<div style="max-width:800px;">
    <div style="margin-bottom:24px;">
        <h1 style="font-size:22px;font-weight:800;color:#111827;letter-spacing:-0.02em;">System Settings</h1>
        <p style="font-size:13px;color:#64748b;margin-top:2px;">Configure application settings and preferences</p>
    </div>

    <!-- General -->
    <div class="card" style="margin-bottom:16px;">
        <div class="card-header"><h3 style="font-size:15px;font-weight:700;">General</h3></div>
        <div class="card-body">
            <div class="space-y-4">
                <div><label class="form-label">Application Name</label><input type="text" value="Field IT Support Hub" class="form-input"></div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div><label class="form-label">Timezone</label><select class="form-input form-select"><option>UTC</option><option>Asia/Manila</option><option>America/New_York</option></select></div>
                    <div><label class="form-label">Date Format</label><select class="form-input form-select"><option>YYYY-MM-DD</option><option>MM/DD/YYYY</option><option>DD/MM/YYYY</option></select></div>
                </div>
                <div><label class="form-label">Session Timeout (hours)</label><input type="number" value="8" class="form-input" style="width:120px;"></div>
            </div>
        </div>
    </div>

    <!-- Security -->
    <div class="card" style="margin-bottom:16px;">
        <div class="card-header"><h3 style="font-size:15px;font-weight:700;">Security</h3></div>
        <div class="card-body">
            <div class="space-y-4">
                <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 0;border-bottom:1px solid #f1f5f9;">
                    <div><div style="font-size:13px;font-weight:600;color:#374151;">Rate Limiting</div><div style="font-size:12px;color:#94a3b8;">Limit API requests per user per minute</div></div>
                    <input type="number" value="60" class="form-input" style="width:80px;">
                </div>
                <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 0;border-bottom:1px solid #f1f5f9;">
                    <div><div style="font-size:13px;font-weight:600;color:#374151;">Max Login Attempts</div><div style="font-size:12px;color:#94a3b8;">Lock account after failed attempts</div></div>
                    <input type="number" value="5" class="form-input" style="width:80px;">
                </div>
                <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 0;border-bottom:1px solid #f1f5f9;">
                    <div><div style="font-size:13px;font-weight:600;color:#374151;">Lockout Duration (minutes)</div><div style="font-size:12px;color:#94a3b8;">How long to lock accounts</div></div>
                    <input type="number" value="15" class="form-input" style="width:80px;">
                </div>
                <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 0;">
                    <div><div style="font-size:13px;font-weight:600;color:#374151;">Enforce Strong Passwords</div><div style="font-size:12px;color:#94a3b8;">Require uppercase, lowercase, numbers, symbols</div></div>
                    <label style="position:relative;width:44px;height:24px;cursor:pointer;"><input type="checkbox" checked style="opacity:0;width:0;height:0;"><span style="position:absolute;inset:0;background:#2563eb;border-radius:12px;transition:0.2s;"><span style="position:absolute;top:2px;left:22px;width:20px;height:20px;background:#fff;border-radius:50%;transition:0.2s;"></span></span></label>
                </div>
            </div>
        </div>
    </div>

    <!-- AI Configuration -->
    <div class="card" style="margin-bottom:16px;">
        <div class="card-header"><h3 style="font-size:15px;font-weight:700;">AI Configuration</h3></div>
        <div class="card-body">
            <div class="space-y-4">
                <div><label class="form-label">AI Provider</label><select class="form-input form-select"><option value="ollama">Ollama (Local) - Free</option><option value="openai">OpenAI (External - Paid)</option><option value="none">Disabled</option></select></div>
                <div style="display:grid;grid-template-columns:2fr 1fr;gap:12px;">
                    <div><label class="form-label">Ollama URL</label><input type="text" value="http://localhost:11434" class="form-input"></div>
                    <div><label class="form-label">Model</label><input type="text" value="llama3.2" class="form-input"></div>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;">
                    <div><label class="form-label">Rate Limit (req/min/user)</label><input type="number" value="30" class="form-input"></div>
                    <div><label class="form-label">Max Tokens</label><input type="number" value="1500" class="form-input"></div>
                    <div><label class="form-label">Temperature</label><input type="number" value="0.7" step="0.1" class="form-input"></div>
                </div>
                <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 0;border-bottom:1px solid #f1f5f9;">
                    <div><div style="font-size:13px;font-weight:600;">Enable Web Research</div><div style="font-size:12px;color:#94a3b8;">Allow AI to search approved external sources</div></div>
                    <label style="position:relative;width:44px;height:24px;cursor:pointer;"><input type="checkbox" checked style="opacity:0;width:0;height:0;"><span style="position:absolute;inset:0;background:#2563eb;border-radius:12px;transition:0.2s;"><span style="position:absolute;top:2px;left:22px;width:20px;height:20px;background:#fff;border-radius:50%;transition:0.2s;"></span></span></label>
                </div>
                <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 0;">
                    <div><div style="font-size:13px;font-weight:600;">AI Logging</div><div style="font-size:12px;color:#94a3b8;">Log AI conversations for improvement</div></div>
                    <label style="position:relative;width:44px;height:24px;cursor:pointer;"><input type="checkbox" checked style="opacity:0;width:0;height:0;"><span style="position:absolute;inset:0;background:#2563eb;border-radius:12px;transition:0.2s;"><span style="position:absolute;top:2px;left:22px;width:20px;height:20px;background:#fff;border-radius:50%;transition:0.2s;"></span></span></label>
                </div>
            </div>
        </div>
    </div>

    <!-- File Upload -->
    <div class="card" style="margin-bottom:16px;">
        <div class="card-header"><h3 style="font-size:15px;font-weight:700;">File Upload</h3></div>
        <div class="card-body">
            <div class="space-y-4">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div><label class="form-label">Max File Size (MB)</label><input type="number" value="10" class="form-input"></div>
                    <div><label class="form-label">Allowed Image Types</label><input type="text" value="jpg,png,gif,webp" class="form-input"></div>
                </div>
                <div><label class="form-label">Allowed Video Types</label><input type="text" value="mp4,webm" class="form-input"></div>
                <div><label class="form-label">Allowed Document Types</label><input type="text" value="pdf,txt" class="form-input"></div>
            </div>
        </div>
    </div>

    <!-- Notifications -->
    <div class="card" style="margin-bottom:16px;">
        <div class="card-header"><h3 style="font-size:15px;font-weight:700;">Notifications</h3></div>
        <div class="card-body">
            <div class="space-y-3">
                <?php
                $notifs = [
                    ['label'=>'New Assignment','desc'=>'When a ticket is assigned to you','enabled'=>true],
                    ['label'=>'Escalation Alert','desc'=>'When an issue is escalated','enabled'=>true],
                    ['label'=>'Knowledge Review','desc'=>'When articles need review','enabled'=>true],
                    ['label'=>'Chat Messages','desc'=>'New team chat messages','enabled'=>true],
                    ['label'=>'System Announcements','desc'=>'Platform updates and alerts','enabled'=>false],
                ];
                foreach ($notifs as $n): ?>
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:8px 0;border-bottom:1px solid #f1f5f9;">
                        <div><div style="font-size:13px;font-weight:600;color:#374151;"><?= $n['label'] ?></div><div style="font-size:12px;color:#94a3b8;"><?= $n['desc'] ?></div></div>
                        <div style="width:40px;height:22px;border-radius:11px;background:<?= $n['enabled']?'#2563eb':'#d1d5db' ?>;position:relative;cursor:pointer;">
                            <div style="width:18px;height:18px;background:#fff;border-radius:50%;position:absolute;top:2px;<?= $n['enabled']?'right:2px':'left:2px' ?>;transition:all 0.2s;box-shadow:0 1px 3px rgba(0,0,0,0.1);"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:20px;">
        <button class="btn btn-secondary">Reset to Defaults</button>
        <button class="btn btn-primary" onclick="showToast('Settings saved successfully','success')"><i data-lucide="save" style="width:15px;height:15px;"></i> Save Settings</button>
    </div>
</div>
<?php require APP_ROOT . '/includes/layout_footer.php'; ?>
