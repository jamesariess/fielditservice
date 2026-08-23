<?php
$page_title = 'Troubleshooting Manager';
$active_menu = 'admin-troubleshoot';
require APP_ROOT . '/includes/layout_header.php';

$roleName = $_SESSION['role_name'] ?? '';
if (!in_array(strtolower($roleName), ['admin', 'super admin', 'super_admin'])) {
    header('Location: ' . $urlBase . 'dashboard');
    exit;
}
?>

<link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;500;600&display=swap" rel="stylesheet">

<style>
/* ===== PAGE LAYOUT ===== */
.tm-wrap { max-width: 1400px; margin: 0 auto; padding: 0 4px; }

/* Header */
.tm-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; flex-wrap: wrap; gap: 12px; }
.tm-head h1 { font-size: 22px; font-weight: 800; color: #0f172a; letter-spacing: -0.03em; }
.dark .tm-head h1 { color: #f1f5f9; }
.tm-head-sub { font-size: 13px; color: #64748b; margin-top: 2px; }
.tm-head-actions { display: flex; gap: 8px; }

/* Tabs */
.tm-tabs { display: flex; gap: 2px; background: #f1f5f9; border-radius: 12px; padding: 3px; margin-bottom: 20px; overflow-x: auto; }
.dark .tm-tabs { background: #1e293b; }
.tm-tab {
    display: inline-flex; align-items: center; gap: 6px; padding: 10px 16px;
    border-radius: 10px; font-size: 13px; font-weight: 600; cursor: pointer;
    transition: all 0.15s; color: #64748b; background: transparent; border: none;
    white-space: nowrap; flex-shrink: 0;
}
.tm-tab:hover { color: #334155; background: rgba(255,255,255,0.6); }
.tm-tab.active { background: #fff; color: #2563eb; box-shadow: 0 1px 3px rgba(0,0,0,0.06); }
.dark .tm-tab:hover { color: #e2e8f0; background: rgba(255,255,255,0.05); }
.dark .tm-tab.active { background: #334155; color: #60a5fa; }
.tm-tab i { width: 15px; height: 15px; }
.tm-badge {
    display: inline-flex; align-items: center; justify-content: center;
    min-width: 18px; height: 18px; padding: 0 5px; border-radius: 9px;
    font-size: 10px; font-weight: 700; background: #fee2e2; color: #dc2626;
}
.tm-badge.empty { display: none; }

/* ===== DATA TABLE ===== */
.tm-table-wrap {
    background: #fff; border: 1px solid #e2e8f0; border-radius: 14px;
    overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}
.dark .tm-table-wrap { background: #1e293b; border-color: #334155; }

.tm-toolbar {
    display: flex; align-items: center; gap: 10px; padding: 14px 18px;
    border-bottom: 1px solid #f1f5f9; flex-wrap: wrap;
}
.dark .tm-toolbar { border-bottom-color: #334155; }

.tm-search {
    display: flex; align-items: center; gap: 8px; flex: 1; min-width: 200px;
    background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px;
    padding: 8px 12px; transition: all 0.2s;
}
.tm-search:focus-within { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,0.08); }
.dark .tm-search { background: #0f172a; border-color: #334155; }
.tm-search input {
    flex: 1; border: none; background: transparent; font-size: 13px;
    color: #0f172a; outline: none;
}
.dark .tm-search input { color: #f1f5f9; }
.tm-search input::placeholder { color: #94a3b8; }
.tm-search i { width: 16px; height: 16px; color: #94a3b8; flex-shrink: 0; }

.tm-filter {
    padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 10px;
    font-size: 13px; color: #334155; background: #fff; cursor: pointer;
    font-weight: 500; transition: border-color 0.2s;
}
.dark .tm-filter { background: #0f172a; border-color: #334155; color: #e2e8f0; }
.tm-filter:focus { outline: none; border-color: #2563eb; }

.tm-table { width: 100%; border-collapse: collapse; }
.tm-table th {
    padding: 12px 16px; text-align: left; font-size: 11px; font-weight: 700;
    text-transform: uppercase; letter-spacing: 0.05em; color: #64748b;
    background: #f8fafc; border-bottom: 1px solid #e2e8f0;
}
.dark .tm-table th { background: #0f172a; color: #94a3b8; border-bottom-color: #334155; }
.tm-table td {
    padding: 14px 16px; border-bottom: 1px solid #f1f5f9;
    font-size: 13px; color: #334155; vertical-align: top;
}
.dark .tm-table td { border-bottom-color: #1e293b; color: #e2e8f0; }
.tm-table tr:hover td { background: #f8fafc; }
.dark .tm-table tr:hover td { background: rgba(255,255,255,0.02); }
.tm-table tr:last-child td { border-bottom: none; }

/* Severity row tints */
.tm-table tr[data-severity="critical"] td:first-child { border-left: 3px solid #dc2626; }
.tm-table tr[data-severity="high"] td:first-child { border-left: 3px solid #ea580c; }
.tm-table tr[data-severity="medium"] td:first-child { border-left: 3px solid #d97706; }
.tm-table tr[data-severity="low"] td:first-child { border-left: 3px solid #16a34a; }

.tm-code {
    font-family: 'Fira Code', monospace; font-size: 12px; font-weight: 600;
    color: #0f172a; background: #f1f5f9; padding: 4px 8px; border-radius: 6px;
    display: inline-block; max-width: 180px; overflow: hidden;
    text-overflow: ellipsis; white-space: nowrap;
}
.dark .tm-code { background: #334155; color: #e2e8f0; }

.tm-desc-cell { max-width: 300px; line-height: 1.5; }
.tm-desc-short { color: #64748b; font-size: 12px; }
.dark .tm-desc-short { color: #94a3b8; }

/* Badges */
.tm-sev {
    display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px;
    border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase;
    letter-spacing: 0.03em;
}
.tm-sev.critical { background: #fef2f2; color: #dc2626; }
.tm-sev.high { background: #fff7ed; color: #ea580c; }
.tm-sev.medium { background: #fffbeb; color: #d97706; }
.tm-sev.low { background: #f0fdf4; color: #16a34a; }
.dark .tm-sev.critical { background: rgba(220,38,38,0.15); color: #f87171; }
.dark .tm-sev.high { background: rgba(234,88,12,0.15); color: #fb923c; }
.dark .tm-sev.medium { background: rgba(217,119,6,0.15); color: #fbbf24; }
.dark .tm-sev.low { background: rgba(22,163,74,0.15); color: #4ade80; }

.tm-cat {
    display: inline-flex; align-items: center; padding: 3px 10px;
    border-radius: 6px; font-size: 11px; font-weight: 600;
}
.tm-cat.bsod { background: #fef2f2; color: #dc2626; }
.tm-cat.windows { background: #eff6ff; color: #2563eb; }
.tm-cat.network { background: #f0fdf4; color: #16a34a; }
.tm-cat.hardware { background: #fefce8; color: #ca8a04; }
.tm-cat.printer { background: #fff7ed; color: #ea580c; }
.tm-cat.driver { background: #fdf2f8; color: #db2777; }
.tm-cat.update { background: #f0f9ff; color: #0284c7; }
.tm-cat.other { background: #f8fafc; color: #64748b; }
.dark .tm-cat.bsod { background: rgba(220,38,38,0.12); color: #f87171; }
.dark .tm-cat.windows { background: rgba(37,99,235,0.12); color: #60a5fa; }
.dark .tm-cat.network { background: rgba(22,163,74,0.12); color: #4ade80; }
.dark .tm-cat.hardware { background: rgba(202,138,4,0.12); color: #facc15; }
.dark .tm-cat.printer { background: rgba(234,88,12,0.12); color: #fb923c; }
.dark .tm-cat.driver { background: rgba(219,39,119,0.12); color: #f472b6; }
.dark .tm-cat.update { background: rgba(2,132,199,0.12); color: #38bdf8; }

.tm-actions-cell { display: flex; gap: 4px; }
.tm-icon-btn {
    display: inline-flex; align-items: center; justify-content: center;
    width: 30px; height: 30px; border-radius: 8px; border: none;
    cursor: pointer; transition: all 0.15s; background: transparent;
}
.tm-icon-btn i { width: 15px; height: 15px; }
.tm-icon-btn.edit { color: #64748b; }
.tm-icon-btn.edit:hover { background: #eff6ff; color: #2563eb; }
.tm-icon-btn.del { color: #94a3b8; }
.tm-icon-btn.del:hover { background: #fef2f2; color: #dc2626; }

.tm-empty-table {
    text-align: center; padding: 48px 24px; color: #94a3b8;
}
.tm-empty-table i { width: 40px; height: 40px; margin-bottom: 10px; color: #cbd5e1; }

.tm-table-footer {
    display: flex; align-items: center; justify-content: space-between;
    padding: 12px 18px; border-top: 1px solid #f1f5f9;
    font-size: 12px; color: #94a3b8;
}
.dark .tm-table-footer { border-top-color: #334155; }

/* ===== SLIDE-OVER PANEL ===== */
.tm-panel-overlay {
    position: fixed; inset: 0; background: rgba(0,0,0,0.4); z-index: 999;
    opacity: 0; pointer-events: none; transition: opacity 0.25s;
}
.tm-panel-overlay.open { opacity: 1; pointer-events: auto; }

.tm-panel {
    position: fixed; top: 0; right: -520px; width: 500px; max-width: 95vw;
    height: 100vh; background: #fff; z-index: 1000;
    box-shadow: -8px 0 30px rgba(0,0,0,0.12);
    transition: right 0.3s cubic-bezier(0.4,0,0.2,1);
    display: flex; flex-direction: column;
}
.tm-panel.open { right: 0; }
.dark .tm-panel { background: #1e293b; }

.tm-panel-head {
    display: flex; align-items: center; justify-content: space-between;
    padding: 20px 24px; border-bottom: 1px solid #e2e8f0;
}
.dark .tm-panel-head { border-bottom-color: #334155; }
.tm-panel-head h3 { font-size: 17px; font-weight: 700; color: #0f172a; }
.dark .tm-panel-head h3 { color: #f1f5f9; }
.tm-panel-close {
    display: flex; align-items: center; justify-content: center;
    width: 32px; height: 32px; border-radius: 8px; border: none;
    background: #f1f5f9; cursor: pointer; color: #64748b; transition: all 0.15s;
}
.tm-panel-close:hover { background: #e2e8f0; color: #334155; }

.tm-panel-body { flex: 1; overflow-y: auto; padding: 24px; }

.tm-fg { margin-bottom: 18px; }
.tm-fl {
    display: block; font-size: 12px; font-weight: 600; color: #334155;
    margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.03em;
}
.dark .tm-fl { color: #94a3b8; }
.tm-fi, .tm-fs, .tm-ft {
    width: 100%; padding: 10px 14px; border: 1px solid #e2e8f0;
    border-radius: 10px; font-size: 14px; font-family: inherit;
    color: #0f172a; background: #fff; transition: all 0.2s;
}
.tm-fi:focus, .tm-fs:focus, .tm-ft:focus {
    outline: none; border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,0.08);
}
.dark .tm-fi, .dark .tm-fs, .dark .tm-ft {
    background: #0f172a; border-color: #334155; color: #f1f5f9;
}
.tm-ft { min-height: 80px; resize: vertical; font-family: inherit; }
.tm-fi.code-input { font-family: 'Fira Code', monospace; font-size: 13px; letter-spacing: 0.02em; }
.tm-fr { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }

.tm-panel-foot {
    padding: 16px 24px; border-top: 1px solid #e2e8f0;
    display: flex; gap: 8px; justify-content: flex-end;
}
.dark .tm-panel-foot { border-top-color: #334155; }

/* Buttons */
.tm-btn {
    display: inline-flex; align-items: center; gap: 6px; padding: 9px 18px;
    border-radius: 10px; font-size: 13px; font-weight: 600; cursor: pointer;
    border: none; transition: all 0.15s; white-space: nowrap;
}
.tm-btn i { width: 15px; height: 15px; }
.tm-btn-primary { background: #2563eb; color: #fff; }
.tm-btn-primary:hover { background: #1d4ed8; box-shadow: 0 2px 8px rgba(37,99,235,0.3); }
.tm-btn-secondary { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }
.tm-btn-secondary:hover { background: #e2e8f0; }
.dark .tm-btn-secondary { background: #334155; border-color: #475569; color: #94a3b8; }
.tm-btn-danger { background: #dc2626; color: #fff; }
.tm-btn-danger:hover { background: #b91c1c; }

/* ===== SUBMISSIONS & ISSUES TABS ===== */
.tm-card {
    background: #fff; border: 1px solid #e2e8f0; border-radius: 12px;
    margin-bottom: 10px; overflow: hidden; transition: box-shadow 0.15s;
}
.tm-card:hover { box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
.dark .tm-card { background: #1e293b; border-color: #334155; }
.tm-card-head {
    display: flex; align-items: center; justify-content: space-between;
    padding: 14px 18px; cursor: pointer;
}
.tm-card-title { font-size: 14px; font-weight: 700; color: #0f172a; }
.dark .tm-card-title { color: #f1f5f9; }
.tm-card-meta { font-size: 12px; color: #94a3b8; margin-top: 2px; }
.tm-card-body { padding: 0 18px 14px; display: none; font-size: 13px; color: #64748b; line-height: 1.6; }
.tm-card-body.open { display: block; }

/* Node display */
.tm-node-card {
    background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px;
    padding: 12px 16px; margin-bottom: 8px;
}
.dark .tm-node-card { background: #0f172a; border-color: #1e293b; }
.tm-node-head { display: flex; align-items: center; gap: 6px; margin-bottom: 4px; }
.tm-node-id { font-size: 11px; font-weight: 700; color: #94a3b8; font-family: 'Fira Code', monospace; }
.tm-node-q { font-size: 13px; font-weight: 600; color: #0f172a; }
.dark .tm-node-q { color: #e2e8f0; }
.tm-node-d { font-size: 12px; color: #64748b; margin-top: 2px; }
.tm-node-links { display: flex; gap: 8px; margin-top: 6px; font-size: 11px; font-weight: 600; }
.tm-node-links .yes { color: #16a34a; }
.tm-node-links .no { color: #dc2626; }

/* ===== TREE EDITOR (SIMPLIFIED) ===== */
.tm-tree-section { margin-bottom: 20px; }
.tm-tree-section-head {
    display: flex; align-items: center; justify-content: space-between;
    padding: 14px 18px; background: #f8fafc; border: 1px solid #e2e8f0;
    border-radius: 12px; margin-bottom: 12px; cursor: pointer;
}
.dark .tm-tree-section-head { background: #0f172a; border-color: #334155; }
.tm-tree-section-head h3 { font-size: 15px; font-weight: 700; color: #0f172a; margin: 0; }
.dark .tm-tree-section-head h3 { color: #f1f5f9; }
.tm-tree-section-head .count { font-size: 12px; color: #94a3b8; }
.tm-step-card {
    background: #fff; border: 1px solid #e2e8f0; border-radius: 10px;
    padding: 14px 16px; margin-bottom: 8px; position: relative;
    border-left: 4px solid #16a34a; transition: box-shadow 0.15s;
}
.tm-step-card:hover { box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
.dark .tm-step-card { background: #1e293b; border-color: #334155; }
.tm-step-card .step-head { display: flex; align-items: center; gap: 8px; margin-bottom: 6px; }
.tm-step-card .step-num {
    display: inline-flex; align-items: center; justify-content: center;
    width: 24px; height: 24px; border-radius: 8px; font-size: 11px; font-weight: 700;
    background: #f0fdf4; color: #16a34a; flex-shrink: 0;
}
.dark .tm-step-card .step-num { background: rgba(22,163,74,0.15); color: #4ade80; }
.tm-step-card .step-title { font-size: 14px; font-weight: 700; color: #0f172a; flex: 1; }
.dark .tm-step-card .step-title { color: #f1f5f9; }
.tm-step-card .step-desc { font-size: 12px; color: #64748b; margin-bottom: 6px; line-height: 1.5; }
.tm-step-card .step-meta { display: flex; gap: 12px; flex-wrap: wrap; font-size: 11px; color: #94a3b8; }
.tm-step-card .step-meta span { display: inline-flex; align-items: center; gap: 4px; }
.tm-step-card .step-images { display: flex; gap: 6px; flex-wrap: wrap; margin-top: 8px; }
.tm-step-card .step-images img {
    width: 60px; height: 60px; object-fit: cover; border-radius: 6px;
    border: 1px solid #e2e8f0; cursor: pointer;
}
.tm-step-card .step-actions {
    position: absolute; top: 12px; right: 12px; display: flex; gap: 4px;
}
.tm-visor-badge {
    display: inline-flex; align-items: center; gap: 3px; padding: 2px 8px;
    border-radius: 12px; font-size: 10px; font-weight: 700;
}
.tm-visor-badge.yes { background: #dcfce7; color: #16a34a; }
.tm-visor-badge.no { background: #fee2e2; color: #dc2626; }
.tm-visor-badge.both { background: #fef3c7; color: #d97706; }
.tm-visor-badge.always { background: #f0f9ff; color: #0284c7; }
.dark .tm-visor-badge.yes { background: rgba(22,163,74,0.15); color: #4ade80; }
.dark .tm-visor-badge.no { background: rgba(220,38,38,0.15); color: #f87171; }
.dark .tm-visor-badge.both { background: rgba(217,119,6,0.15); color: #fbbf24; }
.dark .tm-visor-badge.always { background: rgba(2,132,199,0.15); color: #38bdf8; }
.tm-q-card {
    background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px;
    padding: 12px 16px; margin-bottom: 8px; border-left: 4px solid #2563eb;
}
.dark .tm-q-card { background: #0f172a; border-color: #334155; }
.tm-q-card .q-head { display: flex; align-items: center; gap: 8px; margin-bottom: 4px; }
.tm-q-card .q-num {
    display: inline-flex; align-items: center; justify-content: center;
    width: 24px; height: 24px; border-radius: 8px; font-size: 11px; font-weight: 700;
    background: #eff6ff; color: #2563eb; flex-shrink: 0;
}
.dark .tm-q-card .q-num { background: rgba(37,99,235,0.15); color: #60a5fa; }
.tm-q-card .q-text { font-size: 13px; font-weight: 600; color: #0f172a; flex: 1; }
.dark .tm-q-card .q-text { color: #e2e8f0; }
.tm-q-card .q-desc { font-size: 11px; color: #94a3b8; }
.tm-q-card .step-actions { position: absolute; top: 10px; right: 10px; display: flex; gap: 4px; }

/* Tool tags */
.tm-tool-tag {
    display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px;
    border-radius: 8px; font-size: 11px; font-weight: 600;
    background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0;
}
.dark .tm-tool-tag { background: #334155; border-color: #475569; color: #94a3b8; }
.tm-tool-tag .remove-tool {
    cursor: pointer; color: #94a3b8; font-size: 14px; line-height: 1;
}
.tm-tool-tag .remove-tool:hover { color: #dc2626; }

/* Image thumbnails in form */
.tm-img-thumb {
    position: relative; width: 70px; height: 70px; border-radius: 8px;
    border: 1px solid #e2e8f0; overflow: hidden;
}
.tm-img-thumb img { width: 100%; height: 100%; object-fit: cover; }
.tm-img-thumb .remove-img {
    position: absolute; top: 2px; right: 2px; width: 18px; height: 18px;
    border-radius: 50%; background: rgba(220,38,38,0.85); color: #fff;
    border: none; cursor: pointer; font-size: 10px; display: flex;
    align-items: center; justify-content: center; line-height: 1;
}

/* Guide Groups */
.tm-guide-group {
    background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px;
    padding: 12px; margin-bottom: 8px; position: relative;
}
.dark .tm-guide-group { background: #0f172a; border-color: #334155; }
.tm-guide-head {
    display: flex; align-items: center; gap: 8px; margin-bottom: 8px;
}
.tm-guide-num {
    display: inline-flex; align-items: center; justify-content: center;
    width: 22px; height: 22px; border-radius: 6px; font-size: 10px; font-weight: 700;
    background: #dbeafe; color: #2563eb; flex-shrink: 0;
}
.dark .tm-guide-num { background: rgba(37,99,235,0.15); color: #60a5fa; }
.tm-guide-head label {
    font-size: 11px; font-weight: 600; color: #64748b; margin: 0;
}
.tm-guide-remove {
    margin-left: auto; background: none; border: none; color: #94a3b8;
    cursor: pointer; padding: 2px; font-size: 16px; line-height: 1;
}
.tm-guide-remove:hover { color: #dc2626; }
.tm-guide-img-preview {
    display: flex; gap: 8px; align-items: flex-start; margin-top: 8px;
}
.tm-guide-img-thumb {
    position: relative; width: 80px; height: 80px; border-radius: 8px;
    border: 1px solid #e2e8f0; overflow: hidden; flex-shrink: 0;
}
.tm-guide-img-thumb img { width: 100%; height: 100%; object-fit: cover; }
.tm-guide-img-thumb .remove-img {
    position: absolute; top: 2px; right: 2px; width: 18px; height: 18px;
    border-radius: 50%; background: rgba(220,38,38,0.85); color: #fff;
    border: none; cursor: pointer; font-size: 10px; display: flex;
    align-items: center; justify-content: center; line-height: 1;
}

/* Issue Cards */
.tm-issue-card {
    background: #fff; border: 1px solid #e2e8f0; border-radius: 12px;
    padding: 14px 16px; cursor: pointer; transition: all 0.15s;
    display: flex; flex-direction: column; gap: 8px;
}
.tm-issue-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.08); border-color: #2563eb; transform: translateY(-1px); }
.dark .tm-issue-card { background: #1e293b; border-color: #334155; }
.dark .tm-issue-card:hover { border-color: #60a5fa; }
.tm-issue-card .ic-title { font-size: 14px; font-weight: 700; color: #0f172a; }
.dark .tm-issue-card .ic-title { color: #f1f5f9; }
.tm-issue-card .ic-meta { font-size: 11px; color: #94a3b8; }
.tm-issue-card .ic-devices { display: flex; gap: 4px; flex-wrap: wrap; }
.tm-device-tag {
    display: inline-flex; align-items: center; gap: 3px; padding: 2px 8px;
    border-radius: 6px; font-size: 10px; font-weight: 600;
    background: #f1f5f9; color: #475569;
}
.dark .tm-device-tag { background: #334155; color: #94a3b8; }

/* Device chips in modal */
.ni-device-chip {
    display: inline-flex; align-items: center; gap: 5px; padding: 6px 12px;
    border: 1px solid #e2e8f0; border-radius: 20px; font-size: 12px; font-weight: 600;
    cursor: pointer; transition: all 0.15s; color: #64748b; user-select: none;
}
.ni-device-chip:hover { border-color: #2563eb; color: #2563eb; }
.ni-device-chip:has(input:checked) { background: #eff6ff; border-color: #2563eb; color: #2563eb; }
.ni-device-chip input { display: none; }
.dark .ni-device-chip { background: #1e293b; border-color: #334155; color: #94a3b8; }
.dark .ni-device-chip:hover { border-color: #60a5fa; color: #60a5fa; }
.dark .ni-device-chip:has(input:checked) { background: rgba(37,99,235,0.15); border-color: #60a5fa; color: #60a5fa; }

/* Device tabs in tree editor */
.tm-device-tab {
    display: inline-flex; align-items: center; gap: 5px; padding: 6px 14px;
    border-radius: 20px; font-size: 12px; font-weight: 600; cursor: pointer;
    border: 1px solid #e2e8f0; background: #fff; color: #64748b; transition: all 0.15s;
}
.tm-device-tab:hover { border-color: #2563eb; color: #2563eb; }
.tm-device-tab.active { background: #2563eb; color: #fff; border-color: #2563eb; }
.dark .tm-device-tab { background: #1e293b; border-color: #334155; color: #94a3b8; }
.dark .tm-device-tab:hover { border-color: #60a5fa; color: #60a5fa; }
.dark .tm-device-tab.active { background: #2563eb; color: #fff; border-color: #2563eb; }

/* ===== RESPONSIVE ===== */
@media (max-width: 768px) {
    .tm-table-wrap { overflow-x: auto; }
    .tm-table { min-width: 700px; }
    .tm-toolbar { flex-direction: column; align-items: stretch; }
    .tm-fr { grid-template-columns: 1fr; }
}
</style>

<div class="tm-wrap">
    <!-- Header -->
    <div class="tm-head">
        <div>
            <h1>Troubleshooting Manager</h1>
            <div class="tm-head-sub">Manage error codes, troubleshooting trees, and review submissions.</div>
        </div>
        <div class="tm-head-actions">
            <button class="tm-btn tm-btn-primary" onclick="openPanel('add')">
                <i data-lucide="plus"></i> Add Error Code
            </button>
        </div>
    </div>

    <!-- Tabs -->
    <div class="tm-tabs" id="tm-tabs">
        <button class="tm-tab active" data-tab="errors" onclick="switchTab('errors')">
            <i data-lucide="hash"></i> Error Codes
            <span class="tm-badge" id="error-count">0</span>
        </button>
        <button class="tm-tab" data-tab="tree" onclick="switchTab('tree')">
            <i data-lucide="git-branch"></i> Tree Editor
        </button>
        <button class="tm-tab" data-tab="submissions" onclick="switchTab('submissions')">
            <i data-lucide="inbox"></i> Submissions
            <span class="tm-badge" id="pending-count">0</span>
        </button>
        <button class="tm-tab" data-tab="issues" onclick="switchTab('issues')">
            <i data-lucide="list"></i> All Issues
        </button>
    </div>

    <!-- ===== ERROR CODES TAB ===== -->
    <div id="tab-errors" class="tm-tab-content">
        <div class="tm-table-wrap">
            <!-- Toolbar -->
            <div class="tm-toolbar">
                <div class="tm-search">
                    <i data-lucide="search"></i>
                    <input type="text" id="ec-search" placeholder="Search error codes..." oninput="filterErrors()">
                </div>
                <select class="tm-filter" id="ec-cat-filter" onchange="filterErrors()">
                    <option value="">All Categories</option>
                    <option value="bsod">BSOD</option>
                    <option value="windows">Windows</option>
                    <option value="update">Update</option>
                    <option value="network">Network</option>
                    <option value="hardware">Hardware</option>
                    <option value="printer">Printer</option>
                    <option value="driver">Driver</option>
                    <option value="other">Other</option>
                </select>
                <select class="tm-filter" id="ec-sev-filter" onchange="filterErrors()">
                    <option value="">All Severity</option>
                    <option value="critical">Critical</option>
                    <option value="high">High</option>
                    <option value="medium">Medium</option>
                    <option value="low">Low</option>
                </select>
            </div>
            <!-- Table -->
            <div style="overflow-x:auto;">
                <table class="tm-table">
                    <thead>
                        <tr>
                            <th style="width:160px;">Code</th>
                            <th>Description</th>
                            <th style="width:100px;">Category</th>
                            <th style="width:100px;">Severity</th>
                            <th style="width:90px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="ec-table-body">
                        <tr><td colspan="5" class="tm-empty-table"><i data-lucide="search"></i><p>Loading error codes...</p></td></tr>
                    </tbody>
                </table>
            </div>
            <div class="tm-table-footer">
                <span id="ec-result-count">0 error codes</span>
            </div>
        </div>
    </div>

    <!-- ===== TREE EDITOR TAB ===== -->
    <div id="tab-tree" class="tm-tab-content" style="display:none;">
        <!-- Issue selector area (cards + add button) -->
        <div id="tree-issues-area">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;flex-wrap:wrap;gap:10px;">
                <div style="display:flex;align-items:center;gap:10px;flex:1;flex-wrap:wrap;">
                    <div class="tm-search" style="flex:1;min-width:200px;">
                        <i data-lucide="search"></i>
                        <input type="text" id="tree-issue-search" placeholder="Search issues..." oninput="filterIssueCards()">
                    </div>
                    <select class="tm-filter" id="tree-device-filter" onchange="filterIssueCards()">
                        <option value="">All Devices</option>
                        <option value="desktop">Desktop</option>
                        <option value="laptop">Laptop</option>
                        <option value="printer">Printer</option>
                        <option value="camera">Camera</option>
                        <option value="monitor">Monitor</option>
                        <option value="server">Server</option>
                        <option value="router">Router</option>
                    </select>
                </div>
                <button class="tm-btn tm-btn-primary" onclick="openAddIssueModal()"><i data-lucide="plus"></i> Add New Issue</button>
            </div>
            <div id="tree-issue-cards" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:10px;"></div>
        </div>

        <!-- Issue editor (hidden until issue selected) -->
        <div id="tree-editor-area" style="display:none;">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">
                <button class="tm-btn tm-btn-secondary" onclick="showIssueList()" style="padding:6px 12px;font-size:12px;">
                    <i data-lucide="arrow-left" style="width:14px;height:14px;"></i> Back to Issues
                </button>
                <div id="tree-editor-title" style="font-size:16px;font-weight:700;"></div>
            </div>
            <!-- Device tabs -->
            <div id="tree-device-tabs" style="display:flex;gap:4px;margin-bottom:16px;flex-wrap:wrap;"></div>
            <div id="tree-editor-content"></div>
        </div>
    </div>

    <!-- ===== ADD ISSUE MODAL ===== -->
    <div class="tm-panel-overlay" id="issue-overlay" onclick="closeIssueModal()"></div>
    <div class="tm-panel" id="issue-panel" style="width:480px;">
        <div class="tm-panel-head">
            <h3 id="issue-panel-title">Add New Issue</h3>
            <button class="tm-panel-close" onclick="closeIssueModal()"><i data-lucide="x"></i></button>
        </div>
        <div class="tm-panel-body">
            <div class="tm-fg">
                <label class="tm-fl">Issue Title *</label>
                <input class="tm-fi" id="ni-title" placeholder="e.g., Monitor Not Turning On">
            </div>
            <div class="tm-fr">
                <div class="tm-fg">
                    <label class="tm-fl">Category</label>
                    <select class="tm-fs" id="ni-category">
                        <option value="display">Display</option><option value="power">Power</option>
                        <option value="network">Network</option><option value="printer">Printer</option>
                        <option value="sound">Sound</option><option value="cctv">CCTV</option>
                        <option value="software">Software</option><option value="other">Other</option>
                    </select>
                </div>
                <div class="tm-fg">
                    <label class="tm-fl">Severity</label>
                    <select class="tm-fs" id="ni-severity">
                        <option value="medium">Medium</option><option value="low">Low</option>
                        <option value="high">High</option><option value="critical">Critical</option>
                    </select>
                </div>
            </div>
            <div class="tm-fg">
                <label class="tm-fl">Description</label>
                <textarea class="tm-ft" id="ni-desc" placeholder="Brief description of the issue..." style="min-height:60px;"></textarea>
            </div>
            <div class="tm-fg">
                <label class="tm-fl">Applicable Devices *</label>
                <div id="ni-devices" style="display:flex;flex-wrap:wrap;gap:6px;">
                    <label class="ni-device-chip"><input type="checkbox" value="desktop"> Desktop</label>
                    <label class="ni-device-chip"><input type="checkbox" value="laptop"> Laptop</label>
                    <label class="ni-device-chip"><input type="checkbox" value="server"> Server</label>
                    <label class="ni-device-chip"><input type="checkbox" value="printer"> Printer</label>
                    <label class="ni-device-chip"><input type="checkbox" value="monitor"> Monitor</label>
                    <label class="ni-device-chip"><input type="checkbox" value="camera"> Camera</label>
                    <label class="ni-device-chip"><input type="checkbox" value="nvr"> NVR/DVR</label>
                    <label class="ni-device-chip"><input type="checkbox" value="router"> Router</label>
                    <label class="ni-device-chip"><input type="checkbox" value="switch"> Switch</label>
                    <label class="ni-device-chip"><input type="checkbox" value="projector"> Projector</label>
                </div>
            </div>
        </div>
        <div class="tm-panel-foot">
            <button class="tm-btn tm-btn-secondary" onclick="closeIssueModal()">Cancel</button>
            <button class="tm-btn tm-btn-primary" onclick="saveNewIssue()"><i data-lucide="save"></i> Create Issue</button>
        </div>
    </div>

    <!-- ===== SUBMISSIONS TAB ===== -->
    <div id="tab-submissions" class="tm-tab-content" style="display:none;">
        <div id="submissions-list"></div>
    </div>

    <!-- ===== ALL ISSUES TAB ===== -->
    <div id="tab-issues" class="tm-tab-content" style="display:none;">
        <div id="issues-list" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:10px;"></div>
    </div>
</div>

<!-- ===== SLIDE-OVER PANEL ===== -->
<div class="tm-panel-overlay" id="panel-overlay" onclick="closePanel()"></div>
<div class="tm-panel" id="ec-panel">
    <div class="tm-panel-head">
        <h3 id="panel-title">Add Error Code</h3>
        <button class="tm-panel-close" onclick="closePanel()"><i data-lucide="x"></i></button>
    </div>
    <div class="tm-panel-body">
        <div class="tm-fg">
            <label class="tm-fl">Error Code *</label>
            <input class="tm-fi code-input" id="ec-code" placeholder="e.g., CRITICAL_PROCESS_DIED">
        </div>
        <div class="tm-fg">
            <label class="tm-fl">Title *</label>
            <input class="tm-fi" id="ec-title" placeholder="e.g., Critical Process Died (BSOD)">
        </div>
        <div class="tm-fr">
            <div class="tm-fg">
                <label class="tm-fl">Category</label>
                <select class="tm-fs" id="ec-category">
                    <option value="bsod">BSOD</option><option value="windows">Windows</option>
                    <option value="update">Update</option><option value="network">Network</option>
                    <option value="hardware">Hardware</option><option value="printer">Printer</option>
                    <option value="driver">Driver</option><option value="other">Other</option>
                </select>
            </div>
            <div class="tm-fg">
                <label class="tm-fl">Severity</label>
                <select class="tm-fs" id="ec-severity">
                    <option value="critical">Critical</option><option value="high">High</option>
                    <option value="medium">Medium</option><option value="low">Low</option>
                </select>
            </div>
        </div>
        <div class="tm-fg">
            <label class="tm-fl">Description</label>
            <textarea class="tm-ft" id="ec-desc" placeholder="What this error means. Include Hardware and Software causes as separate sections."></textarea>
        </div>
        <div class="tm-fg">
            <label class="tm-fl">Common Causes (Hardware + Software)</label>
            <textarea class="tm-ft" id="ec-causes" placeholder="Hardware: Failing RAM, overheating CPU&#10;Software: Bad driver, malware infection" style="min-height:60px;"></textarea>
        </div>
        <div class="tm-fg">
            <label class="tm-fl">Fix Steps</label>
            <textarea class="tm-ft" id="ec-fixes" placeholder="1. Run sfc /scannow as admin&#10;2. Run DISM /Online /Cleanup-Image /RestoreHealth&#10;3. Check RAM with Memory Diagnostic" style="min-height:100px;"></textarea>
        </div>
    </div>
    <div class="tm-panel-foot">
        <button class="tm-btn tm-btn-secondary" onclick="closePanel()">Cancel</button>
        <button class="tm-btn tm-btn-primary" id="panel-save-btn" onclick="saveErrorFromPanel()">
            <i data-lucide="save"></i> Save Error Code
        </button>
    </div>
</div>

<!-- ===== STEP FORM MODAL ===== -->
<div class="tm-panel-overlay" id="node-overlay" onclick="closeNodePanel()"></div>
<div class="tm-panel" id="node-panel" style="width:520px;">
    <div class="tm-panel-head">
        <h3 id="node-panel-title">Add Step</h3>
        <button class="tm-panel-close" onclick="closeNodePanel()"><i data-lucide="x"></i></button>
    </div>
    <div class="tm-panel-body">
        <!-- Step Title -->
        <div class="tm-fg">
            <label class="tm-fl">Step Title *</label>
            <input class="tm-fi" id="en-question" placeholder="e.g., Reseat the RAM modules">
        </div>
        <!-- Instructions -->
        <div class="tm-fg">
            <label class="tm-fl">Instructions *</label>
            <textarea class="tm-ft" id="en-desc" placeholder="Detailed instructions for the technician..." style="min-height:70px;"></textarea>
        </div>
        <!-- Risk Level -->
        <div class="tm-fr">
            <div class="tm-fg">
                <label class="tm-fl">Risk Level</label>
                <select class="tm-fs" id="en-risk">
                    <option value="safe">Safe</option><option value="caution">Caution</option><option value="danger">Danger</option>
                </select>
            </div>
            <div class="tm-fg">
                <label class="tm-fl">Step Order</label>
                <input class="tm-fi" id="en-steporder" type="number" value="10" min="1">
            </div>
        </div>
        <div class="tm-fg">
            <label class="tm-fl">Visible on Device</label>
            <select class="tm-fs" id="en-device">
                <option value="all">All Devices</option>
            </select>
            <div style="font-size:11px;color:#94a3b8;margin-top:4px;">Which device does this step apply to? "All" shows for every device.</div>
        </div>
        <!-- Visual Guide Groups -->
        <div class="tm-fg">
            <label class="tm-fl">Visual Guides (sequential steps)</label>
            <div id="en-guides-area">
                <div id="en-guides-list"></div>
                <button class="tm-btn tm-btn-secondary" onclick="addGuideGroup()" style="margin-top:8px;font-size:12px;padding:6px 12px;">
                    <i data-lucide="plus" style="width:14px;height:14px;"></i> Add Guide Step
                </button>
                <div style="font-size:11px;color:#94a3b8;margin-top:4px;">Add sequential steps the technician follows. Each step has instructions and an optional image.</div>
            </div>
        </div>
        <!-- Expected Result -->
        <div class="tm-fg">
            <label class="tm-fl">Expected Result</label>
            <input class="tm-fi" id="en-expected" placeholder="What should happen after this step (e.g., Display shows POST screen)">
        </div>
        <!-- Tools Needed -->
        <div class="tm-fg">
            <label class="tm-fl">Tools Needed</label>
            <div id="en-tools-list" style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:6px;"></div>
            <div style="display:flex;gap:6px;">
                <input class="tm-fi" id="en-tools-input" placeholder="Type a tool and press Add" style="flex:1;" onkeydown="if(event.key==='Enter'){event.preventDefault();addTool();}">
                <button class="tm-btn tm-btn-secondary" onclick="addTool()" style="padding:8px 12px;font-size:12px;">Add</button>
            </div>
        </div>
        <!-- Visibility -->
        <div class="tm-fg" style="background:#f0f9ff;border:1px solid #bfdbfe;border-radius:10px;padding:14px;">
            <label class="tm-fl" style="color:#2563eb;margin-bottom:8px;">👁️ When is this step visible?</label>
            <div class="tm-fr" style="gap:10px;">
                <div class="tm-fg" style="margin-bottom:0;">
                    <label class="tm-fl" style="font-size:11px;">Linked to Question</label>
                    <select class="tm-fs" id="en-via" style="font-size:13px;">
                        <option value="">Always (no question filter)</option>
                    </select>
                </div>
                <div class="tm-fg" style="margin-bottom:0;">
                    <label class="tm-fl" style="font-size:11px;">Show when answer is</label>
                    <select class="tm-fs" id="en-visibility" style="font-size:13px;">
                        <option value="always">Always</option>
                        <option value="yes_only">YES only</option>
                        <option value="no_only">NO only</option>
                        <option value="both">BOTH (YES or NO)</option>
                    </select>
                </div>
            </div>
            <div style="font-size:11px;color:#64748b;margin-top:6px;">Link this step to a diagnostic question. If "Always", it shows regardless of the answer.</div>
        </div>
        <!-- Result Type (for terminal nodes) -->
        <div id="terminal-fields" style="display:none;">
            <div class="tm-fr">
                <div class="tm-fg">
                    <label class="tm-fl">Result Type</label>
                    <select class="tm-fs" id="en-result">
                        <option value="solved">Solved</option><option value="escalation">Escalate</option><option value="hardware">Hardware</option>
                    </select>
                </div>
            </div>
            <div class="tm-fg">
                <label class="tm-fl">Solution Text *</label>
                <textarea class="tm-ft" id="en-solution" placeholder="What was the solution..."></textarea>
            </div>
        </div>
    </div>
    <div class="tm-panel-foot">
        <button class="tm-btn tm-btn-secondary" onclick="closeNodePanel()">Cancel</button>
        <button class="tm-btn tm-btn-primary" id="node-save-btn" onclick="saveStepFromPanel()">
            <i data-lucide="save"></i> Save Step
        </button>
    </div>
</div>

<script>
// ===== STATE =====
var allErrors = [];
var filteredErrors = [];
var treeQuestions = [];
var treeSteps = [];
var treeIssueId = null;
var editingErrorId = null;
var editingNodeId = null;
var currentTools = [];
var guideGroups = [];

// ===== TAB SWITCHING =====
function switchTab(tab) {
    document.querySelectorAll('.tm-tab').forEach(function(t) { t.classList.remove('active'); });
    document.querySelector('[data-tab="'+tab+'"]').classList.add('active');
    document.querySelectorAll('.tm-tab-content').forEach(function(c) { c.style.display = 'none'; });
    document.getElementById('tab-'+tab).style.display = '';
    if (tab === 'errors') loadErrorCodes();
    if (tab === 'tree') { showIssueList(); loadAllIssues(); }
    if (tab === 'submissions') loadSubmissions();
    if (tab === 'issues') loadIssues();
}

// ===== ERROR CODES =====
async function loadErrorCodes() {
    try {
        var res = await fetch(APP_BASE + 'api/troubleshooting/errors.php?all=1');
        allErrors = await res.json();
        filteredErrors = allErrors.slice();
        renderErrorTable();
        document.getElementById('error-count').textContent = allErrors.length;
    } catch(e) { console.error(e); }
}
function filterErrors() {
    var q = document.getElementById('ec-search').value.toLowerCase();
    var cat = document.getElementById('ec-cat-filter').value;
    var sev = document.getElementById('ec-sev-filter').value;
    filteredErrors = allErrors.filter(function(ec) {
        if (cat && ec.category !== cat) return false;
        if (sev && ec.severity !== sev) return false;
        if (q) { var s = (ec.code+' '+ec.title+' '+ec.description+' '+ec.common_causes).toLowerCase(); if (s.indexOf(q)===-1) return false; }
        return true;
    });
    renderErrorTable();
}
function renderErrorTable() {
    var tbody = document.getElementById('ec-table-body');
    if (!filteredErrors.length) {
        tbody.innerHTML = '<tr><td colspan="5" class="tm-empty-table"><i data-lucide="search-x"></i><p>No error codes found</p></td></tr>';
        lucide.createIcons(); document.getElementById('ec-result-count').textContent = '0 error codes'; return;
    }
    var html = '';
    filteredErrors.forEach(function(ec) {
        var desc = (ec.description||'').replace(/\n/g,' ').substring(0,120);
        html += '<tr data-severity="'+ec.severity+'">';
        html += '<td><span class="tm-code" title="'+esc(ec.code)+'">'+esc(ec.code)+'</span></td>';
        html += '<td class="tm-desc-cell"><div style="font-weight:600;color:#0f172a;margin-bottom:2px;">'+esc(ec.title)+'</div><div class="tm-desc-short">'+esc(desc)+'...</div></td>';
        html += '<td><span class="tm-cat '+ec.category+'">'+ec.category+'</span></td>';
        html += '<td><span class="tm-sev '+ec.severity+'">'+ec.severity+'</span></td>';
        html += '<td><div class="tm-actions-cell">';
        html += '<button class="tm-icon-btn edit" onclick="editError('+ec.id+')" title="Edit"><i data-lucide="pencil"></i></button>';
        html += '<button class="tm-icon-btn del" onclick="deleteError('+ec.id+')" title="Delete"><i data-lucide="trash-2"></i></button>';
        html += '</div></td></tr>';
    });
    tbody.innerHTML = html; lucide.createIcons();
    document.getElementById('ec-result-count').textContent = filteredErrors.length + ' error code' + (filteredErrors.length!==1?'s':'');
}
function openPanel(mode, error) {
    editingErrorId = null;
    document.getElementById('panel-title').textContent = mode==='add'?'Add New Error Code':'Edit Error Code';
    if (error) {
        editingErrorId = error.id;
        document.getElementById('ec-code').value = error.code||'';
        document.getElementById('ec-title').value = error.title||'';
        document.getElementById('ec-category').value = error.category||'other';
        document.getElementById('ec-severity').value = error.severity||'medium';
        document.getElementById('ec-desc').value = error.description||'';
        document.getElementById('ec-causes').value = error.common_causes||'';
        document.getElementById('ec-fixes').value = error.fix_steps||'';
    } else {
        document.getElementById('ec-code').value=''; document.getElementById('ec-title').value='';
        document.getElementById('ec-category').value='bsod'; document.getElementById('ec-severity').value='medium';
        document.getElementById('ec-desc').value=''; document.getElementById('ec-causes').value=''; document.getElementById('ec-fixes').value='';
    }
    document.getElementById('panel-overlay').classList.add('open');
    document.getElementById('ec-panel').classList.add('open'); lucide.createIcons();
}
function closePanel() { document.getElementById('panel-overlay').classList.remove('open'); document.getElementById('ec-panel').classList.remove('open'); editingErrorId = null; }
async function saveErrorFromPanel() {
    var data = {
        code: document.getElementById('ec-code').value.trim(),
        title: document.getElementById('ec-title').value.trim(),
        category: document.getElementById('ec-category').value,
        severity: document.getElementById('ec-severity').value,
        description: document.getElementById('ec-desc').value.trim(),
        common_causes: document.getElementById('ec-causes').value.trim(),
        fix_steps: document.getElementById('ec-fixes').value.trim(),
    };
    if (!data.code||!data.title) { showToast('Code and title are required','error'); return; }
    try {
        var method = editingErrorId ? 'PUT' : 'POST';
        if (editingErrorId) data.id = editingErrorId;
        var res = await fetch(APP_BASE+'api/troubleshooting/errors.php', {method:method,headers:{'Content-Type':'application/json'},body:JSON.stringify(data)});
        var result = await res.json();
        if (result.error) { showToast(result.error,'error'); return; }
        showToast(editingErrorId?'Error code updated!':'Error code created!','success'); closePanel(); loadErrorCodes();
    } catch(e) { showToast('Failed: '+e.message,'error'); }
}
async function editError(id) {
    try { var res = await fetch(APP_BASE+'api/troubleshooting/errors.php?id='+id); var ec = await res.json(); openPanel('edit',ec); } catch(e) { showToast('Failed','error'); }
}
async function deleteError(id) {
    if (!confirm('Delete this error code?')) return;
    try { await fetch(APP_BASE+'api/troubleshooting/errors.php?id='+id,{method:'DELETE'}); showToast('Deleted','success'); loadErrorCodes(); } catch(e) { showToast('Failed','error'); }
}

// ===== TREE EDITOR (SIMPLIFIED) =====
var allIssues = [];
var selectedDevice = 'all';
var allNodesForIssue = [];

async function loadAllIssues() {
    try {
        var res = await fetch(APP_BASE+'api/troubleshooting/nodes.php?all_issues=1');
        if (!res.ok) {
            // Fallback: load from the issues tab data
            allIssues = [
                {id:1,title:'No Display',slug:'no-display',category:'display',severity:'high',device_types:'desktop,laptop,server'},
                {id:2,title:'No Power',slug:'no-power',category:'power',severity:'critical',device_types:'desktop,laptop,server'},
                {id:3,title:'No Sound',slug:'no-sound',category:'sound',severity:'medium',device_types:'desktop,laptop,server'},
                {id:4,title:'No Internet',slug:'no-internet',category:'network',severity:'high',device_types:'desktop,laptop,server,router,switch'},
                {id:5,title:'WiFi Not Connecting',slug:'wifi-not-connecting',category:'network',severity:'medium',device_types:'desktop,laptop,server,router,switch'},
                {id:6,title:'Printer Offline',slug:'printer-offline',category:'printer',severity:'medium',device_types:'printer'},
                {id:7,title:'Camera Offline',slug:'camera-offline',category:'cctv',severity:'high',device_types:'camera,nvr'},
                {id:8,title:'Blue Screen',slug:'bsod',category:'software',severity:'critical',device_types:'desktop,laptop'},
                {id:9,title:'Slow Performance',slug:'slow-performance',category:'software',severity:'medium',device_types:'desktop,laptop,server'},
                {id:10,title:'Network Slow',slug:'network-slow',category:'network',severity:'medium',device_types:'desktop,laptop,server,router,switch'},
                {id:11,title:'Random Shutdowns',slug:'random-shutdowns',category:'power',severity:'high',device_types:'desktop,laptop,server'},
                {id:12,title:'Overheating',slug:'overheating',category:'power',severity:'high',device_types:'desktop,laptop,server'},
                {id:13,title:'Application Crash',slug:'application-crash',category:'software',severity:'medium',device_types:'desktop,laptop'},
                {id:14,title:'Paper Jam',slug:'paper-jam',category:'printer',severity:'low',device_types:'printer'},
                {id:15,title:'Windows Update Fails',slug:'windows-update-fails',category:'software',severity:'medium',device_types:'desktop,laptop'},
                {id:16,title:'No Recording',slug:'no-recording',category:'cctv',severity:'high',device_types:'camera,nvr'},
                {id:17,title:'DNS Issues',slug:'dns-issues',category:'network',severity:'medium',device_types:'desktop,laptop,server,router,switch'},
                {id:18,title:'Flickering Display',slug:'flickering-display',category:'display',severity:'medium',device_types:'desktop,laptop,server'},
                {id:19,title:'BIOS Issues',slug:'bios-issues',category:'software',severity:'high',device_types:'desktop,laptop,server'},
                {id:20,title:'No Display and Power',slug:'no-display-and-no-power',category:'power',severity:'critical',device_types:'desktop,laptop,server'},
                {id:21,title:'Projector Not Turning On',slug:'projector-not-turning-on',category:'power',severity:'medium',device_types:'monitor,projector'},
            ];
        } else {
            allIssues = await res.json();
        }
        renderIssueCards();
    } catch(e) {
        // Use fallback
        renderIssueCards();
    }
}

function renderIssueCards() {
    var search = (document.getElementById('tree-issue-search')||{}).value || '';
    var deviceFilter = (document.getElementById('tree-device-filter')||{}).value || '';
    search = search.toLowerCase();
    var filtered = allIssues.filter(function(iss) {
        if (search && iss.title.toLowerCase().indexOf(search) === -1) return false;
        if (deviceFilter && iss.device_types && iss.device_types.indexOf(deviceFilter) === -1) return false;
        return true;
    });
    var el = document.getElementById('tree-issue-cards');
    if (!filtered.length) {
        el.innerHTML = '<div class="tm-empty-table" style="grid-column:1/-1;"><i data-lucide="search-x"></i><p>No issues found</p></div>';
        lucide.createIcons(); return;
    }
    var catIcons = {display:'monitor',power:'zap',network:'wifi',printer:'printer',sound:'volume-2',cctv:'camera',software:'cpu',other:'help-circle'};
    var sevC = {critical:'#dc2626',high:'#ea580c',medium:'#d97706',low:'#16a34a'};
    var html = '';
    filtered.forEach(function(iss) {
        var devices = iss.device_types ? iss.device_types.split(',') : [];
        var icon = catIcons[iss.category] || 'help-circle';
        html += '<div class="tm-issue-card" onclick="loadTreeForIssue('+iss.id+')">';
        html += '<div style="display:flex;align-items:center;gap:8px;">';
        html += '<i data-lucide="'+icon+'" style="width:18px;height:18px;color:'+sevC[iss.severity]+';flex-shrink:0;"></i>';
        html += '<span class="ic-title">'+esc(iss.title)+'</span>';
        html += '</div>';
        html += '<div class="ic-devices">';
        devices.forEach(function(d) { html += '<span class="tm-device-tag">'+d.trim()+'</span>'; });
        html += '</div>';
        html += '</div>';
    });
    el.innerHTML = html;
    lucide.createIcons();
}

function filterIssueCards() { renderIssueCards(); }

function showIssueList() {
    document.getElementById('tree-issues-area').style.display = '';
    document.getElementById('tree-editor-area').style.display = 'none';
    treeIssueId = null;
}

async function loadTreeForIssue(issueId) {
    treeIssueId = parseInt(issueId);
    selectedDevice = 'all';
    var iss = allIssues.find(function(i){return i.id==issueId;});
    document.getElementById('tree-editor-title').textContent = iss ? iss.title : 'Issue';
    // Build device tabs
    var devices = (iss && iss.device_types) ? iss.device_types.split(',').map(function(d){return d.trim();}) : [];
    buildDeviceTabs(devices);
    document.getElementById('tree-issues-area').style.display = 'none';
    document.getElementById('tree-editor-area').style.display = '';
    try {
        var res = await fetch(APP_BASE+'api/troubleshooting/nodes.php?issue_id='+issueId);
        var data = await res.json();
        allNodesForIssue = data.nodes || [];
        filterAndRenderNodes();
    } catch(e) { showToast('Failed to load','error'); }
}

function buildDeviceTabs(devices) {
    var el = document.getElementById('tree-device-tabs');
    var html = '<span class="tm-device-tab active" onclick="switchDeviceTab(\'all\')">All Devices</span>';
    var icons = {desktop:'monitor',laptop:'laptop',server:'server',printer:'printer',camera:'camera',monitor:'monitor',router:'wifi',switch:'network',projector:'projector',nvr:'camera'};
    devices.forEach(function(d) {
        var icon = icons[d] || 'box';
        html += '<span class="tm-device-tab" onclick="switchDeviceTab(\''+d+'\')"><i data-lucide="'+icon+'" style="width:12px;height:12px;"></i> '+d.charAt(0).toUpperCase()+d.slice(1)+'</span>';
    });
    el.innerHTML = html;
    lucide.createIcons();
}

function switchDeviceTab(device) {
    selectedDevice = device;
    document.querySelectorAll('.tm-device-tab').forEach(function(t) {
        t.classList.toggle('active', t.textContent.trim().toLowerCase().replace(/[^a-z]/g,'') === device || (device==='all' && t.textContent.indexOf('All')>-1));
    });
    filterAndRenderNodes();
}

function filterAndRenderNodes() {
    var filtered = allNodesForIssue.filter(function(n) {
        return n.device_type === 'all' || n.device_type === selectedDevice || selectedDevice === 'all';
    });
    treeQuestions = filtered.filter(function(n) { return n.node_type === 'question'; });
    treeSteps = filtered.filter(function(n) { return n.node_type === 'step' && !n.is_terminal; });
    var terminals = filtered.filter(function(n) { return n.is_terminal; });
    renderTreeEditor(terminals);
}

// ===== ADD ISSUE MODAL =====
function openAddIssueModal() {
    document.getElementById('ni-title').value = '';
    document.getElementById('ni-category').value = 'display';
    document.getElementById('ni-severity').value = 'medium';
    document.getElementById('ni-desc').value = '';
    document.querySelectorAll('#ni-devices input[type=checkbox]').forEach(function(cb){cb.checked=false;});
    document.getElementById('issue-overlay').classList.add('open');
    document.getElementById('issue-panel').classList.add('open');
    lucide.createIcons();
}
function closeIssueModal() {
    document.getElementById('issue-overlay').classList.remove('open');
    document.getElementById('issue-panel').classList.remove('open');
}
async function saveNewIssue() {
    var title = document.getElementById('ni-title').value.trim();
    if (!title) { showToast('Title is required','error'); return; }
    var devices = [];
    document.querySelectorAll('#ni-devices input[type=checkbox]:checked').forEach(function(cb){devices.push(cb.value);});
    if (!devices.length) { showToast('Select at least one device','error'); return; }
    var slug = title.toLowerCase().replace(/[^a-z0-9]+/g,'-').replace(/^-|-$/g,'');
    var data = {
        title: title,
        slug: slug,
        category: document.getElementById('ni-category').value,
        severity: document.getElementById('ni-severity').value,
        description: document.getElementById('ni-desc').value.trim(),
        device_types: devices.join(','),
    };
    try {
        var res = await fetch(APP_BASE+'api/troubleshooting/nodes.php', {
            method: 'POST', headers: {'Content-Type':'application/json'},
            body: JSON.stringify({action:'create_issue', issue: data})
        });
        var result = await res.json();
        if (result.error) { showToast(result.error,'error'); return; }
        showToast('Issue created!','success');
        closeIssueModal();
        // Add to local list
        allIssues.push({id: result.id, title: title, slug: slug, category: data.category, severity: data.severity, device_types: devices.join(',')});
        renderIssueCards();
    } catch(e) { showToast('Failed: '+e.message,'error'); }
}

function renderTreeEditor(terminals) {
    var html = '';
    // Questions section
    html += '<div class="tm-tree-section">';
    html += '<div class="tm-tree-section-head" onclick="this.nextElementSibling.style.display=this.nextElementSibling.style.display===\'none\'?\'\':\'none\'">';
    html += '<h3>Diagnostic Questions</h3>';
    html += '<div style="display:flex;align-items:center;gap:10px;"><span class="count">'+treeQuestions.length+' questions</span>';
    html += '<button class="tm-btn tm-btn-primary" style="padding:6px 12px;font-size:12px;" onclick="event.stopPropagation();addQuestionToTree()"><i data-lucide="plus"></i> Add Question</button>';
    html += '</div></div>';
    html += '<div>';
    if (!treeQuestions.length) {
        html += '<div style="text-align:center;padding:20px;color:#94a3b8;font-size:13px;">No questions yet. Add diagnostic YES/NO questions that filter which steps appear.</div>';
    } else {
        treeQuestions.forEach(function(q, idx) {
            html += '<div class="tm-q-card" style="position:relative;">';
            html += '<div class="q-head">';
            html += '<span class="q-num">Q'+(idx+1)+'</span>';
            html += '<span class="q-text">'+esc(q.question)+'</span>';
            html += '<div style="flex:1;"></div>';
            html += '<button class="tm-icon-btn edit" onclick="editQuestion('+q.id+')" title="Edit"><i data-lucide="pencil"></i></button>';
            html += '<button class="tm-icon-btn del" onclick="deleteNode('+q.id+')" title="Delete"><i data-lucide="trash-2"></i></button>';
            html += '</div>';
            if (q.description) html += '<div class="q-desc">'+esc(q.description)+'</div>';
            html += '</div>';
        });
    }
    html += '</div></div>';
    // Steps section
    html += '<div class="tm-tree-section">';
    html += '<div class="tm-tree-section-head" onclick="this.nextElementSibling.style.display=this.nextElementSibling.style.display===\'none\'?\'\':\'none\'">';
    html += '<h3>Troubleshooting Steps</h3>';
    html += '<div style="display:flex;align-items:center;gap:10px;"><span class="count">'+treeSteps.length+' steps</span>';
    html += '<button class="tm-btn tm-btn-primary" style="padding:6px 12px;font-size:12px;" onclick="event.stopPropagation();addStepToTree()"><i data-lucide="plus"></i> Add Step</button>';
    html += '</div></div>';
    html += '<div>';
    if (!treeSteps.length) {
        html += '<div style="text-align:center;padding:20px;color:#94a3b8;font-size:13px;">No steps yet. Add actionable troubleshooting steps.</div>';
    } else {
        treeSteps.forEach(function(s, idx) {
            html += '<div class="tm-step-card">';
            html += '<div class="step-head">';
            html += '<span class="step-num">S'+(idx+1)+'</span>';
            html += '<span class="step-title">'+esc(s.question)+'</span>';
            var visMode = s.visibility_mode || 'always';
            var visLabel = visMode === 'yes_only' ? 'YES only' : visMode === 'no_only' ? 'NO only' : visMode === 'both' ? 'BOTH' : 'Always';
            html += '<span class="tm-visor-badge '+visMode+'">'+visLabel+'</span>';
            html += '<span class="tm-sev '+s.risk+'" style="font-size:10px;padding:2px 8px;">'+s.risk+'</span>';
            html += '<div style="flex:1;"></div>';
            html += '<button class="tm-icon-btn edit" onclick="editStep('+s.id+')" title="Edit"><i data-lucide="pencil"></i></button>';
            html += '<button class="tm-icon-btn del" onclick="deleteNode('+s.id+')" title="Delete"><i data-lucide="trash-2"></i></button>';
            html += '</div>';
            if (s.description) html += '<div class="step-desc">'+esc(s.description).substring(0,150)+'</div>';
            html += '<div class="step-meta">';
            if (s.visual_guide) html += '<span><i data-lucide="eye" style="width:12px;height:12px;"></i> Visual Guide</span>';
            if (s.expected_result) html += '<span><i data-lucide="check-circle" style="width:12px;height:12px;"></i> '+esc(s.expected_result)+'</span>';
            if (s.tools_needed) html += '<span><i data-lucide="wrench" style="width:12px;height:12px;"></i> '+esc(s.tools_needed)+'</span>';
            if (s.visible_for_question_id) {
                var linkedQ = treeQuestions.find(function(q){return q.id==s.visible_for_question_id;});
                if (linkedQ) html += '<span style="color:#2563eb;"><i data-lucide="link" style="width:12px;height:12px;"></i> Q: '+esc(linkedQ.question).substring(0,30)+'</span>';
            }
            html += '</div>';
            // Show guide groups if any
            if (s.visual_guide_images) {
                try {
                    var guides = JSON.parse(s.visual_guide_images);
                    if (guides.length && (guides[0].text || guides[0].image)) {
                        html += '<div style="margin-top:8px;background:#f0f9ff;border:1px solid #bfdbfe;border-radius:8px;padding:10px;">';
                        html += '<div style="font-size:10px;font-weight:700;color:#2563eb;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Visual Guide ('+guides.length+' steps)</div>';
                        guides.forEach(function(g, gi) {
                            if (!g.text && !g.image) return;
                            html += '<div style="display:flex;gap:8px;align-items:flex-start;margin-bottom:'+(gi<guides.length-1?'8px':'0')+';padding-bottom:'+(gi<guides.length-1?'8px':'0')+';border-bottom:'+(gi<guides.length-1?'1px solid #e0f2fe':'none')+';">';
                            html += '<span style="display:inline-flex;align-items:center;justify-content:center;width:18px;height:18px;border-radius:4px;font-size:9px;font-weight:700;background:#dbeafe;color:#2563eb;flex-shrink:0;">'+(gi+1)+'</span>';
                            html += '<div style="flex:1;">';
                            if (g.text) html += '<div style="font-size:12px;color:#334155;line-height:1.5;">'+esc(g.text)+'</div>';
                            if (g.image) html += '<img src="'+g.image+'" style="margin-top:4px;max-width:120px;max-height:80px;border-radius:6px;border:1px solid #e2e8f0;">';
                            html += '</div></div>';
                        });
                        html += '</div>';
                    }
                } catch(e) {}
            }
            html += '</div>';
        });
    }
    html += '</div></div>';
    // Terminal nodes
    if (terminals && terminals.length) {
        html += '<div class="tm-tree-section">';
        html += '<div class="tm-tree-section-head"><h3>Terminal Results</h3><span class="count">'+terminals.length+' results</span></div>';
        html += '<div>';
        terminals.forEach(function(t) {
            var typeC = t.result_type==='solved'?'#16a34a':t.result_type==='hardware'?'#d97706':'#dc2626';
            html += '<div class="tm-step-card" style="border-left-color:'+typeC+';">';
            html += '<div class="step-head">';
            html += '<span class="step-title">'+esc(t.question)+'</span>';
            html += '<span class="tm-sev '+(t.result_type==='solved'?'low':t.result_type==='hardware'?'medium':'critical')+'" style="font-size:10px;">'+t.result_type+'</span>';
            html += '<button class="tm-icon-btn del" onclick="deleteNode('+t.id+')" title="Delete"><i data-lucide="trash-2"></i></button>';
            html += '</div>';
            if (t.result_solution) html += '<div class="step-desc">'+esc(t.result_solution)+'</div>';
            html += '</div>';
        });
        html += '</div></div>';
    }
    document.getElementById('tree-editor-content').innerHTML = html;
    lucide.createIcons();
}

// ===== QUESTION OPERATIONS =====
function addQuestionToTree() {
    editingNodeId = null;
    document.getElementById('node-panel-title').textContent = 'Add Diagnostic Question';
    document.getElementById('en-question').value = '';
    document.getElementById('en-desc').value = '';
    document.getElementById('en-risk').value = 'safe';
    document.getElementById('en-steporder').value = 10;
    currentTools = []; guideGroups = [];
    renderToolTags(); renderGuideGroups();
    populateDeviceDropdown(selectedDevice);
    hideStepFields();
    document.getElementById('node-overlay').classList.add('open');
    document.getElementById('node-panel').classList.add('open'); lucide.createIcons();
}
function editQuestion(qId) {
    var q = treeQuestions.find(function(x){return x.id==qId;});
    if (!q) return;
    editingNodeId = qId;
    document.getElementById('node-panel-title').textContent = 'Edit Question';
    document.getElementById('en-question').value = q.question || '';
    document.getElementById('en-desc').value = q.description || '';
    document.getElementById('en-risk').value = q.risk || 'safe';
    document.getElementById('en-steporder').value = q.step_order || 10;
    currentTools = []; guideGroups = [];
    renderToolTags(); renderGuideGroups();
    populateDeviceDropdown(q.device_type || 'all');
    hideStepFields();
    document.getElementById('node-overlay').classList.add('open');
    document.getElementById('node-panel').classList.add('open'); lucide.createIcons();
}

function hideStepFields() {
    ['en-guides-area','en-expected'].forEach(function(id) {
        var el = document.getElementById(id);
        if (el) { var p = el.closest('.tm-fg'); if (p) p.style.display = 'none'; }
    });
    var toolsFg = document.getElementById('en-tools-list');
    if (toolsFg) { var tp = toolsFg.closest('.tm-fg'); if (tp) tp.style.display = 'none'; }
    var toolsDiv = document.getElementById('en-tools-input');
    if (toolsDiv) { var td = toolsDiv.closest('div'); if (td) td.style.display = 'none'; }
    var viaFg = document.getElementById('en-via');
    if (viaFg) { var vp = viaFg.closest('.tm-fg'); if (vp) vp.parentElement.style.display = 'none'; }
    document.getElementById('terminal-fields').style.display = 'none';
}
function showStepFields() {
    ['en-guides-area','en-expected'].forEach(function(id) {
        var el = document.getElementById(id);
        if (el) { var p = el.closest('.tm-fg'); if (p) p.style.display = ''; }
    });
    var toolsFg = document.getElementById('en-tools-list');
    if (toolsFg) { var tp = toolsFg.closest('.tm-fg'); if (tp) tp.style.display = ''; }
    var toolsDiv = document.getElementById('en-tools-input');
    if (toolsDiv) { var td = toolsDiv.closest('div'); if (td) td.style.display = ''; }
    var viaFg = document.getElementById('en-via');
    if (viaFg) { var vp = viaFg.closest('.tm-fg'); if (vp) vp.parentElement.style.display = ''; }
    document.getElementById('terminal-fields').style.display = 'none';
}

// ===== STEP OPERATIONS =====
function addStepToTree() {
    editingNodeId = null;
    document.getElementById('node-panel-title').textContent = 'Add Troubleshooting Step';
    document.getElementById('en-question').value = '';
    document.getElementById('en-desc').value = '';
    document.getElementById('en-risk').value = 'safe';
    document.getElementById('en-steporder').value = treeSteps.length + 1;
    document.getElementById('en-expected').value = '';
    document.getElementById('en-visibility').value = 'always';
    currentTools = []; guideGroups = [];
    renderToolTags(); renderGuideGroups();
    populateDeviceDropdown(selectedDevice);
    showStepFields();
    buildQuestionDropdown(null);
    document.getElementById('node-overlay').classList.add('open');
    document.getElementById('node-panel').classList.add('open'); lucide.createIcons();
}
function editStep(stepId) {
    var s = treeSteps.find(function(x){return x.id==stepId;});
    if (!s) return;
    editingNodeId = stepId;
    document.getElementById('node-panel-title').textContent = 'Edit Step';
    document.getElementById('en-question').value = s.question || '';
    document.getElementById('en-desc').value = s.description || '';
    document.getElementById('en-risk').value = s.risk || 'safe';
    document.getElementById('en-steporder').value = s.step_order || 10;
    document.getElementById('en-expected').value = s.expected_result || '';
    document.getElementById('en-visibility').value = s.visibility_mode || 'always';
    currentTools = s.tools_needed ? s.tools_needed.split(',').map(function(t){return t.trim();}).filter(Boolean) : [];
    renderToolTags();
    guideGroups = [];
    if (s.visual_guide_images) { try { guideGroups = JSON.parse(s.visual_guide_images); } catch(e) {} }
    renderGuideGroups();
    populateDeviceDropdown(s.device_type || 'all');
    showStepFields();
    buildQuestionDropdown(s.visible_for_question_id);
    document.getElementById('node-overlay').classList.add('open');
    document.getElementById('node-panel').classList.add('open'); lucide.createIcons();
}
function populateDeviceDropdown(selectedVal) {
    var sel = document.getElementById('en-device');
    var iss = allIssues.find(function(i){return i.id==treeIssueId;});
    var devices = (iss && iss.device_types) ? iss.device_types.split(',').map(function(d){return d.trim();}) : [];
    sel.innerHTML = '<option value="all">All Devices</option>';
    devices.forEach(function(d) {
        var opt = document.createElement('option');
        opt.value = d;
        opt.textContent = d.charAt(0).toUpperCase() + d.slice(1);
        if (selectedVal && selectedVal === d) opt.selected = true;
        sel.appendChild(opt);
    });
    if (selectedVal === 'all' || !selectedVal) sel.value = 'all';
}
function buildQuestionDropdown(selectedId) {
    var sel = document.getElementById('en-via');
    sel.innerHTML = '<option value="">Always (no question filter)</option>';
    treeQuestions.forEach(function(q) {
        var opt = document.createElement('option');
        opt.value = q.id;
        opt.textContent = q.question;
        if (selectedId && selectedId == q.id) opt.selected = true;
        sel.appendChild(opt);
    });
}

// ===== TOOLS MANAGEMENT =====
function addTool() {
    var input = document.getElementById('en-tools-input');
    var tool = input.value.trim();
    if (!tool) return;
    currentTools.push(tool);
    input.value = '';
    renderToolTags();
}
function removeTool(idx) { currentTools.splice(idx, 1); renderToolTags(); }
function renderToolTags() {
    var el = document.getElementById('en-tools-list');
    if (!currentTools.length) { el.innerHTML = ''; return; }
    var html = '';
    currentTools.forEach(function(t, i) {
        html += '<span class="tm-tool-tag">'+esc(t)+'<span class="remove-tool" onclick="removeTool('+i+')">&times;</span></span>';
    });
    el.innerHTML = html;
}

// ===== GUIDE GROUPS =====
function addGuideGroup() {
    guideGroups.push({ text: '', image: null });
    renderGuideGroups();
}
function removeGuideGroup(idx) {
    guideGroups.splice(idx, 1);
    renderGuideGroups();
}
function handleGuideImage(event, idx) {
    var file = event.target.files[0];
    if (!file) return;
    var reader = new FileReader();
    reader.onload = function(e) {
        guideGroups[idx].image = e.target.result;
        renderGuideGroups();
    };
    reader.readAsDataURL(file);
    event.target.value = '';
}
function removeGuideImage(idx) {
    guideGroups[idx].image = null;
    renderGuideGroups();
}
function updateGuideText(idx, val) {
    guideGroups[idx].text = val;
}
function renderGuideGroups() {
    var el = document.getElementById('en-guides-list');
    if (!guideGroups.length) { el.innerHTML = '<div style="text-align:center;padding:12px;color:#94a3b8;font-size:12px;">No guide steps yet. Click "Add Guide Step" to start.</div>'; return; }
    var html = '';
    guideGroups.forEach(function(g, i) {
        html += '<div class="tm-guide-group">';
        html += '<div class="tm-guide-head">';
        html += '<span class="tm-guide-num">'+(i+1)+'</span>';
        html += '<label>Step '+(i+1)+'</label>';
        html += '<button class="tm-guide-remove" onclick="removeGuideGroup('+i+')" title="Remove step">&times;</button>';
        html += '</div>';
        html += '<textarea class="tm-ft" style="min-height:50px;font-size:13px;" placeholder="What to do in this step..." oninput="updateGuideText('+i+',this.value)">'+esc(g.text)+'</textarea>';
        // Image preview + upload
        html += '<div class="tm-guide-img-preview">';
        if (g.image) {
            html += '<div class="tm-guide-img-thumb">';
            html += '<img src="'+g.image+'" alt="Guide">';
            html += '<button class="remove-img" onclick="removeGuideImage('+i+')">&times;</button>';
            html += '</div>';
        }
        html += '<label style="display:inline-flex;align-items:center;gap:4px;padding:6px 10px;border:1px dashed #cbd5e1;border-radius:8px;cursor:pointer;font-size:11px;font-weight:600;color:#64748b;transition:all 0.2s;align-self:flex-start;">';
        html += '<i data-lucide="image-plus" style="width:14px;height:14px;"></i> '+(g.image?'Change Image':'Add Image');
        html += '<input type="file" accept="image/*" style="display:none;" onchange="handleGuideImage(event,'+i+')">';
        html += '</label>';
        html += '</div>';
        html += '</div>';
    });
    el.innerHTML = html;
    lucide.createIcons();
}

// ===== SAVE STEP/QUESTION =====
async function saveStepFromPanel() {
    var isQuestion = document.getElementById('node-panel-title').textContent.indexOf('Question') !== -1;
    var data = {
        issue_id: treeIssueId,
        question: document.getElementById('en-question').value.trim(),
        description: document.getElementById('en-desc').value.trim(),
        node_type: isQuestion ? 'question' : 'step',
        risk: document.getElementById('en-risk').value,
        step_order: parseInt(document.getElementById('en-steporder').value) || 10,
        is_terminal: 0,
    };
    if (!data.question) { showToast('Title is required','error'); return; }
    if (!isQuestion) {
        // Build visual_guide as concatenated text from guide groups
        var guideTexts = guideGroups.map(function(g) { return g.text; }).filter(Boolean);
        data.visual_guide = guideTexts.length ? guideTexts.join('\n---\n') : null;
        data.expected_result = document.getElementById('en-expected').value.trim() || null;
        data.tools_needed = currentTools.join(', ') || null;
        data.visual_guide_images = guideGroups.length ? JSON.stringify(guideGroups) : null;
        data.visible_for_question_id = document.getElementById('en-via').value ? parseInt(document.getElementById('en-via').value) : null;
        data.visibility_mode = document.getElementById('en-visibility').value || 'always';
    }
    data.device_type = document.getElementById('en-device').value || 'all';
    try {
        var method = editingNodeId ? 'PUT' : 'POST';
        if (editingNodeId) data.id = editingNodeId;
        var res = await fetch(APP_BASE+'api/troubleshooting/nodes.php', {method:method,headers:{'Content-Type':'application/json'},body:JSON.stringify(data)});
        var result = await res.json();
        if (result.error) { showToast(result.error,'error'); return; }
        showToast(editingNodeId?'Updated!':'Created!','success');
        closeNodePanel();
        loadTreeForIssue(treeIssueId);
    } catch(e) { showToast('Failed: '+e.message,'error'); }
}
function closeNodePanel() {
    document.getElementById('node-overlay').classList.remove('open');
    document.getElementById('node-panel').classList.remove('open');
    editingNodeId = null;
}
async function deleteNode(nodeId) {
    if (!confirm('Delete this node?')) return;
    try {
        var res = await fetch(APP_BASE+'api/troubleshooting/nodes.php?id='+nodeId,{method:'DELETE'});
        var data = await res.json();
        if (data.error) { showToast(data.error,'error'); return; }
        showToast('Deleted','success');
        loadTreeForIssue(treeIssueId);
    } catch(e) { showToast('Failed','error'); }
}

// ===== SUBMISSIONS =====
async function loadSubmissions() {
    try {
        var res = await fetch(APP_BASE+'api/troubleshooting/submissions.php?all=1');
        var data = await res.json();
        var pending = data.filter(function(s){return s.status==='pending';}).length;
        var badge = document.getElementById('pending-count');
        badge.textContent = pending;
        badge.className = 'tm-badge'+(pending===0?' empty':'');
        var html = '';
        if (!data.length) { html = '<div class="tm-empty-table"><p>No submissions yet</p></div>'; }
        else {
            data.forEach(function(s) {
                var nodes = s.nodes_data ? JSON.parse(s.nodes_data) : [];
                html += '<div class="tm-card"><div class="tm-card-head" onclick="this.nextElementSibling.classList.toggle(\'open\')">';
                html += '<div><div class="tm-card-title">'+esc(s.title)+'</div>';
                html += '<div class="tm-card-meta">by '+esc(s.submitter_name)+' &bull; '+s.submission_type+' &bull; '+formatDate(s.created_at)+'</div></div>';
                html += '<span class="tm-sev '+(s.status==='approved'?'low':s.status==='rejected'?'critical':'medium')+'">'+s.status+'</span></div>';
                html += '<div class="tm-card-body">';
                if (s.description) html += '<p>'+esc(s.description)+'</p>';
                if (nodes.length) {
                    var qs=nodes.filter(function(n){return(n.node_type||'question')==='question';}).length;
                    var ss=nodes.filter(function(n){return(n.node_type||'question')==='step'&&!n.is_terminal;}).length;
                    var ts=nodes.filter(function(n){return n.is_terminal;}).length;
                    html += '<div style="margin-top:8px;font-weight:600;">Decision Tree ('+nodes.length+' nodes: '+qs+' questions, '+ss+' steps, '+ts+' terminals)</div>';
                    nodes.forEach(function(n,i) {
                        var type=n.node_type||'question';
                        var color=type==='question'?'#2563eb':(n.is_terminal?'#d97706':'#16a34a');
                        var label=type==='question'?'Q':(n.is_terminal?'T':'S');
                        html += '<div class="tm-node-card" style="margin-top:4px;border-left:3px solid '+color+';">';
                        html += '<div style="display:flex;align-items:center;gap:6px;"><span style="font-size:10px;font-weight:700;color:'+color+';">'+label+' #'+(i+1)+'</span>';
                        html += '<div class="tm-node-q">'+esc(n.question)+'</div></div></div>';
                    });
                }
                if (s.status==='pending') {
                    html += '<div style="margin-top:10px;display:flex;gap:6px;">';
                    html += '<button class="tm-btn tm-btn-primary" style="padding:6px 14px;font-size:12px;" onclick="reviewSubmission('+s.id+',\'approve\')">Approve</button>';
                    html += '<button class="tm-btn tm-btn-danger" style="padding:6px 14px;font-size:12px;" onclick="reviewSubmission('+s.id+',\'reject\')">Reject</button>';
                    html += '</div>';
                }
                html += '</div></div>';
            });
        }
        document.getElementById('submissions-list').innerHTML = html; lucide.createIcons();
    } catch(e) { console.error(e); }
}
async function reviewSubmission(id, action) {
    var notes = prompt('Admin notes (optional):') || '';
    try {
        var res = await fetch(APP_BASE+'api/troubleshooting/submissions.php', {method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({id:id,action:action,admin_notes:notes})});
        var data = await res.json();
        showToast(data.message||data.error, data.error?'error':'success'); loadSubmissions();
    } catch(e) { showToast('Failed','error'); }
}

// ===== ALL ISSUES =====
async function loadIssues() {
    var issues = [
        {t:'No Display',s:'no-display',n:19,c:'Display'},{t:'No Power',s:'no-power',n:15,c:'Power'},
        {t:'No Sound',s:'no-sound',n:16,c:'Sound'},{t:'No Internet',s:'no-internet',n:17,c:'Network'},
        {t:'WiFi Not Connecting',s:'wifi-not-connecting',n:11,c:'Network'},{t:'Printer Offline',s:'printer-offline',n:15,c:'Printer'},
        {t:'Camera Offline',s:'camera-offline',n:11,c:'CCTV'},{t:'Blue Screen',s:'bsod',n:16,c:'Software'},
        {t:'Slow Performance',s:'slow-performance',n:22,c:'Software'},{t:'Network Slow',s:'network-slow',n:10,c:'Network'},
        {t:'Random Shutdowns',s:'random-shutdowns',n:10,c:'Power'},{t:'Overheating',s:'overheating',n:8,c:'Power'},
        {t:'Application Crash',s:'application-crash',n:12,c:'Software'},{t:'Paper Jam',s:'paper-jam',n:15,c:'Printer'},
        {t:'Windows Update',s:'windows-update-fails',n:8,c:'Software'},{t:'No Recording',s:'no-recording',n:6,c:'CCTV'},
        {t:'DNS Issues',s:'dns-issues',n:7,c:'Network'},{t:'Flickering Display',s:'flickering-display',n:9,c:'Display'},
        {t:'BIOS Issues',s:'bios-issues',n:5,c:'Software'},{t:'No Display+Power',s:'no-display-and-no-power',n:10,c:'Power'},
        {t:'Projector Not Turning On',s:'projector-not-turning-on',n:3,c:'Power'},
    ];
    var html = '';
    issues.forEach(function(i) {
        html += '<div class="tm-card"><div class="tm-card-head"><div>';
        html += '<div class="tm-card-title">'+i.t+'</div>';
        html += '<div class="tm-card-meta">'+i.n+' decision steps &bull; '+i.c+'</div>';
        html += '</div><span class="tm-code" style="font-size:10px;">'+i.s+'</span></div></div>';
    });
    document.getElementById('issues-list').innerHTML = html;
}

// ===== UTILS =====
function esc(s) { if (!s) return ''; return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
function formatDate(d) {
    if (!d) return '';
    var diff = (new Date() - new Date(d)) / 1000;
    if (diff < 60) return 'Just now';
    if (diff < 3600) return Math.floor(diff/60)+'m ago';
    if (diff < 86400) return Math.floor(diff/3600)+'h ago';
    return Math.floor(diff/86400)+'d ago';
}
function showToast(msg, type) {
    var t = document.createElement('div');
    t.className = 'toast ' + type;
    t.innerHTML = '<span style="font-size:13px;">' + msg + '</span>';
    var c = document.getElementById('toast-container');
    if (c) c.appendChild(t);
    setTimeout(function() { t.remove(); }, 4000);
}

// ===== INIT =====
loadErrorCodes();
lucide.createIcons();
</script>
<?php require APP_ROOT . '/includes/layout_footer.php'; ?>
