<?php
if (!defined('APP_ROOT')) { @header('Location: /fielditservice/'); exit; }

$page_title = 'Troubleshooting Wizard';
$active_menu = 'troubleshoot';
require APP_ROOT . '/includes/layout_header.php';
$issueParam = $_GET['issue'] ?? '1';
$backDevice = $_GET['device'] ?? '';
$backUrl = $urlBase . 'troubleshoot' . ($backDevice ? '?device=' . urlencode($backDevice) : '');
$exitUrl = $urlBase . 'troubleshoot';

$issue = null;
if (!empty($issueParam)) {
    if (is_numeric($issueParam)) {
        $issue = Database::fetch('SELECT * FROM troubleshooting_issues WHERE id = ?', [(int)$issueParam]);
    } else {
        $issue = Database::fetch('SELECT * FROM troubleshooting_issues WHERE slug = ?', [$issueParam]);
    }
}
if (!$issue) { $issue = Database::fetch('SELECT * FROM troubleshooting_issues ORDER BY id LIMIT 1'); }
$issueId = $issue['id'] ?? 1;
$issueTitle = e($issue['title'] ?? 'Issue');
$issueSlug = $issue['slug'] ?? '';
?>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
.wiz-page { max-width: 900px; margin: 0 auto; }
.wiz-head { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 20px; gap: 16px; flex-wrap: wrap; }
.wiz-title { font-size: 24px; font-weight: 800; color: #0f172a; letter-spacing: -0.03em; }
.dark .wiz-title { color: #f1f5f9; }
.wiz-sub { font-size: 14px; color: #64748b; margin-top: 4px; line-height: 1.5; }
.wiz-head-btns { display: flex; gap: 8px; }
.wiz-btn {
    display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px;
    border-radius: 10px; font-size: 13px; font-weight: 600; cursor: pointer;
    text-decoration: none; transition: all 0.15s; border: 1.5px solid #e2e8f0;
    font-family: 'Inter', sans-serif;
}
.wiz-btn-back { background: #fff; color: #374151; }
.wiz-btn-back:hover { background: #f1f5f9; }
.wiz-btn-exit { background: transparent; color: #6b7280; border-color: transparent; }
.wiz-btn-exit:hover { color: #dc2626; background: #fef2f2; }
.dark .wiz-btn-back { background: #1e293b; border-color: #334155; color: #e2e8f0; }

/* Safety Banner */
.wiz-safety {
    display: flex; align-items: flex-start; gap: 10px; padding: 14px 18px;
    background: #fef2f2; border: 1px solid #fecaca; border-radius: 12px;
    margin-bottom: 24px; font-size: 13px; line-height: 1.5; color: #991b1b;
}
.dark .wiz-safety { background: rgba(220,38,38,0.08); border-color: rgba(220,38,38,0.2); color: #fca5a5; }

/* Progress */
.wiz-progress { margin-bottom: 24px; }
.wiz-progress-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px; }
.wiz-progress-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: #94a3b8; }
.wiz-progress-pct { font-size: 12px; font-weight: 600; color: #2563eb; }
.wiz-progress-track { height: 6px; background: #e5e7eb; border-radius: 3px; overflow: hidden; }
.wiz-progress-fill { height: 100%; background: linear-gradient(90deg, #22c55e, #16a34a); border-radius: 3px; transition: width 0.5s cubic-bezier(0.4,0,0.2,1); }

/* Phase Indicator */
.wiz-phase {
    display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px;
    border-radius: 20px; font-size: 12px; font-weight: 700; margin-bottom: 20px;
}
.wiz-phase.phase1 { background: #eff6ff; color: #2563eb; }
.wiz-phase.phase2 { background: #f0fdf4; color: #16a34a; }
.wiz-phase.result { background: #fef3c7; color: #d97706; }
.dark .wiz-phase.phase1 { background: rgba(37,99,235,0.15); color: #60a5fa; }
.dark .wiz-phase.phase2 { background: rgba(22,163,74,0.15); color: #4ade80; }

/* ===== PHASE 1: QUESTION CHECKLIST ===== */
.wiz-questions { margin-bottom: 24px; }
.wiz-q-card {
    background: #fff; border: 1.5px solid #e2e8f0; border-radius: 14px;
    padding: 20px; margin-bottom: 12px; transition: all 0.2s;
}
.wiz-q-card.answered { border-color: #bbf7d0; background: #f0fdf4; }
.dark .wiz-q-card { background: #1e293b; border-color: #334155; }
.dark .wiz-q-card.answered { border-color: rgba(22,163,74,0.3); background: rgba(22,163,74,0.05); }
.wiz-q-num { font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px; }
.wiz-q-text { font-size: 16px; font-weight: 700; color: #0f172a; margin-bottom: 6px; }
.dark .wiz-q-text { color: #f1f5f9; }
.wiz-q-desc { font-size: 13px; color: #64748b; margin-bottom: 14px; line-height: 1.5; }
.dark .wiz-q-desc { color: #94a3b8; }
.wiz-q-btns { display: flex; gap: 10px; }
.wiz-q-btn {
    flex: 1; padding: 12px 16px; border-radius: 10px; font-size: 14px;
    font-weight: 700; cursor: pointer; border: 2px solid #e2e8f0;
    background: #fff; transition: all 0.2s; text-align: center;
    font-family: 'Inter', sans-serif;
}
.wiz-q-btn:hover { border-color: #94a3b8; }
.wiz-q-btn.yes:hover { border-color: #16a34a; background: #f0fdf4; color: #16a34a; }
.wiz-q-btn.no:hover { border-color: #dc2626; background: #fef2f2; color: #dc2626; }
.wiz-q-btn.selected-yes { border-color: #16a34a; background: #16a34a; color: #fff; }
.wiz-q-btn.selected-no { border-color: #dc2626; background: #dc2626; color: #fff; }
.dark .wiz-q-btn { background: #0f172a; border-color: #334155; color: #e2e8f0; }
.dark .wiz-q-btn.selected-yes { background: #16a34a; border-color: #16a34a; color: #fff; }
.dark .wiz-q-btn.selected-no { background: #dc2626; border-color: #dc2626; color: #fff; }

.wiz-start-btn {
    width: 100%; padding: 16px; border-radius: 12px; font-size: 16px;
    font-weight: 700; cursor: pointer; border: none; transition: all 0.2s;
    background: linear-gradient(135deg, #2563eb, #1d4ed8); color: #fff;
    font-family: 'Inter', sans-serif; box-shadow: 0 4px 14px rgba(37,99,235,0.3);
}
.wiz-start-btn:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(37,99,235,0.4); }
.wiz-start-btn:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }

/* ===== PHASE 2: STEP CARD ===== */
.wiz-step-card {
    background: #fff; border: 1.5px solid #e2e8f0; border-radius: 16px;
    padding: 28px; margin-bottom: 16px;
}
.dark .wiz-step-card { background: #1e293b; border-color: #334155; }
.wiz-step-badge { display: inline-flex; align-items: center; gap: 6px; margin-bottom: 12px; }
.wiz-step-num { font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; }
.wiz-risk {
    display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px;
    border-radius: 20px; font-size: 11px; font-weight: 700;
}
.wiz-risk.safe { background: #f0fdf4; color: #16a34a; }
.wiz-risk.caution { background: #fffbeb; color: #d97706; }
.wiz-risk.danger { background: #fef2f2; color: #dc2626; }
.dark .wiz-risk.safe { background: rgba(22,163,74,0.15); color: #4ade80; }
.dark .wiz-risk.caution { background: rgba(217,119,6,0.15); color: #fbbf24; }
.dark .wiz-risk.danger { background: rgba(220,38,38,0.15); color: #f87171; }

.wiz-step-title { font-size: 20px; font-weight: 800; color: #0f172a; margin-bottom: 12px; line-height: 1.3; }
.dark .wiz-step-title { color: #f1f5f9; }
.wiz-step-desc { font-size: 14px; color: #475569; line-height: 1.7; margin-bottom: 16px; }
.dark .wiz-step-desc { color: #94a3b8; }

/* Visual Guide */
.wiz-visual {
    background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 12px;
    padding: 16px; margin-bottom: 16px; font-size: 13px; color: #0c4a6e; line-height: 1.6;
}
.dark .wiz-visual { background: rgba(14,165,233,0.08); border-color: rgba(14,165,233,0.2); color: #7dd3fc; }
.wiz-visual strong { color: #0369a1; }
.dark .wiz-visual strong { color: #38bdf8; }

/* Expected Result */
.wiz-expected {
    background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 10px;
    padding: 12px 16px; margin-bottom: 16px; font-size: 13px; color: #166534; line-height: 1.6;
}
.dark .wiz-expected { background: rgba(22,163,74,0.08); border-color: rgba(22,163,74,0.2); color: #86efac; }
.wiz-expected strong { color: #15803d; }

/* Tools Needed */
.wiz-tools {
    display: flex; align-items: center; gap: 8px; padding: 10px 14px;
    background: #fefce8; border: 1px solid #fef08a; border-radius: 10px;
    font-size: 12px; color: #854d0e; margin-bottom: 16px;
}
.dark .wiz-tools { background: rgba(202,138,4,0.08); border-color: rgba(202,138,4,0.2); color: #fde047; }

/* Action Buttons */
.wiz-actions { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-top: 20px; }
.wiz-action-btn {
    display: flex; flex-direction: column; align-items: center; gap: 4px;
    padding: 20px 16px; border: 2px solid #e2e8f0; border-radius: 14px;
    background: #fff; cursor: pointer; transition: all 0.2s;
    font-family: 'Inter', sans-serif;
}
.dark .wiz-action-btn { background: #0f172a; border-color: #334155; }
.wiz-action-btn.worked:hover { border-color: #16a34a; background: #f0fdf4; }
.wiz-action-btn.failed:hover { border-color: #dc2626; background: #fef2f2; }
.wiz-action-label { font-size: 16px; font-weight: 700; color: #0f172a; }
.dark .wiz-action-label { color: #f1f5f9; }
.wiz-action-sub { font-size: 12px; color: #94a3b8; }

/* ===== RESULT ===== */
.wiz-result {
    text-align: center; padding: 40px 24px; background: #fff;
    border: 1.5px solid #e2e8f0; border-radius: 16px;
}
.dark .wiz-result { background: #1e293b; border-color: #334155; }
.wiz-result-icon {
    width: 72px; height: 72px; border-radius: 50%; display: flex;
    align-items: center; justify-content: center; margin: 0 auto 16px;
}
.wiz-result-badge {
    display: inline-flex; padding: 4px 16px; border-radius: 20px;
    font-size: 13px; font-weight: 700; margin-bottom: 12px;
}
.wiz-result h2 { font-size: 22px; font-weight: 800; color: #0f172a; margin-bottom: 8px; }
.dark .wiz-result h2 { color: #f1f5f9; }
.wiz-result p { font-size: 14px; color: #64748b; max-width: 500px; margin: 0 auto 20px; line-height: 1.6; }

/* Session Log Sidebar */
.wiz-log {
    background: #fff; border: 1px solid #e2e8f0; border-radius: 12px;
    overflow: hidden; margin-top: 16px;
}
.dark .wiz-log { background: #1e293b; border-color: #334155; }
.wiz-log-head { padding: 14px 18px; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; }
.dark .wiz-log-head { border-bottom-color: #334155; }
.wiz-log-title { font-size: 14px; font-weight: 700; color: #0f172a; }
.dark .wiz-log-title { color: #f1f5f9; }
.wiz-log-badge { font-size: 11px; color: #94a3b8; }
.wiz-log-body { padding: 14px 18px; max-height: 300px; overflow-y: auto; }
.wiz-log-entry { padding: 8px 0; border-bottom: 1px solid #f1f5f9; font-size: 12px; }
.dark .wiz-log-entry { border-bottom-color: #1e293b; }
.wiz-log-entry:last-child { border-bottom: none; }
.wiz-log-entry-title { font-weight: 600; color: #0f172a; margin-bottom: 2px; }
.dark .wiz-log-entry-title { color: #e2e8f0; }
.wiz-log-entry-detail { color: #94a3b8; }

/* Report */
.wiz-report {
    margin-top: 24px; text-align: left; background: #f8fafc;
    border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px;
}
.dark .wiz-report { background: #0f172a; border-color: #334155; }
.wiz-report-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; }
.wiz-report h3 { font-size: 15px; font-weight: 700; color: #0f172a; }
.dark .wiz-report h3 { color: #f1f5f9; }
.wiz-report pre {
    background: #fff; border: 1px solid #e2e8f0; border-radius: 8px;
    padding: 14px; font-size: 12px; color: #374151; line-height: 1.6;
    white-space: pre-wrap; font-family: 'Fira Code', monospace;
    max-height: 300px; overflow-y: auto;
}
.dark .wiz-report pre { background: #1e293b; border-color: #334155; color: #e2e8f0; }

/* Session Log */
.wiz-log { margin-top: 16px; }

@media (max-width: 768px) {
    .wiz-q-btns { flex-direction: column; }
    .wiz-actions { grid-template-columns: 1fr; }
}
</style>

<div class="wiz-page">
    <!-- Header -->
    <div class="wiz-head">
        <div>
            <h1 class="wiz-title" id="wiz-title"><?= $issueTitle ?> Troubleshooting</h1>
            <p class="wiz-sub">Answer the diagnostic questions, then follow the filtered steps to resolve the issue.</p>
        </div>
        <div class="wiz-head-btns">
            <a href="<?= $backUrl ?>" class="wiz-btn wiz-btn-back"><i data-lucide="arrow-left"></i> Back</a>
            <a href="<?= $exitUrl ?>" class="wiz-btn wiz-btn-exit"><i data-lucide="x"></i> Exit</a>
        </div>
    </div>

    <!-- Safety -->
    <div class="wiz-safety" id="wiz-safety">
        <i data-lucide="shield-alert" style="width:18px;height:18px;flex-shrink:0;margin-top:1px;color:#dc2626;"></i>
        <div><strong>Safety first:</strong> Always unplug the device before opening it. Use anti-static precautions when handling internal components.</div>
    </div>

    <!-- Progress -->
    <div class="wiz-progress">
        <div class="wiz-progress-head">
            <span class="wiz-progress-label" id="progress-label">Diagnostic Questions</span>
            <span class="wiz-progress-pct" id="progress-pct">0% Complete</span>
        </div>
        <div class="wiz-progress-track">
            <div class="wiz-progress-fill" id="progress-fill" style="width:0%;"></div>
        </div>
    </div>

    <!-- Phase 1: Question Checklist -->
    <div id="phase-questions">
        <div class="wiz-phase phase1"><i data-lucide="clipboard-list" style="width:14px;height:14px;"></i> Phase 1: Diagnostic Questions</div>
        <div class="wiz-questions" id="questions-container">
            <div style="text-align:center;padding:24px;color:#94a3b8;">Loading questions...</div>
        </div>
        <button class="wiz-start-btn" id="start-btn" onclick="startTroubleshooting()" disabled>
            <i data-lucide="play"></i> Start Troubleshooting
        </button>
        <div style="text-align:center;margin-top:8px;font-size:12px;color:#94a3b8;">Answer all questions above, then click Start</div>
    </div>

    <!-- Phase 2: Steps (hidden) -->
    <div id="phase-steps" style="display:none;">
        <div class="wiz-phase phase2"><i data-lucide="wrench" style="width:14px;height:14px;"></i> Phase 2: Troubleshooting Steps</div>
        <div id="step-container"></div>
        <div id="step-actions"></div>
    </div>

    <!-- Result (hidden) -->
    <div id="phase-result" style="display:none;"></div>

    <!-- Session Log -->
    <div class="wiz-log">
        <div class="wiz-log-head">
            <span class="wiz-log-title">Session Log</span>
            <span class="wiz-log-badge">auto-recorded</span>
        </div>
        <div class="wiz-log-body" id="log-entries">
            <div class="wiz-log-entry">
                <div class="wiz-log-entry-title">Session started</div>
                <div class="wiz-log-entry-detail">Procedure: <?= $issueTitle ?></div>
            </div>
        </div>
    </div>
</div>

<script>
var issueId = <?= $issueId ?>;
var issueSlug = '<?= $issueSlug ?>';
var backUrl = '<?= $backUrl ?>';
var sessionId = null;
var questions = [];
var answers = {};
var steps = [];
var currentStepIndex = 0;
var stepHistory = [];
var stepStartTime = null;

// Safe fetch - handles session expiry and non-JSON
async function safeFetch(url, opts) {
    var res = await fetch(url, opts);
    var text = await res.text();
    if (!text || text.trim() === '') throw new Error('Empty response. Session may have expired. Please refresh.');
    try { return JSON.parse(text); } catch(e) {
        if (text.indexOf('<!DOCTYPE') !== -1 || text.indexOf('<html') !== -1) throw new Error('Session expired. Please log in again.');
        throw new Error('Invalid server response: ' + text.substring(0, 100));
    }
}

// ===== INIT: Load questions =====
async function init() {
    try {
        var data = await safeFetch(APP_BASE + 'api/troubleshooting/decision.php?issue=' + encodeURIComponent(issueSlug || issueId));
        if (data.error) { showError(data.error); return; }
        
        sessionId = data.session_id;
        questions = data.questions || [];
        
        // Update title if available
        if (data.issue) {
            document.getElementById('wiz-title').textContent = (data.issue.title || 'Issue') + ' Troubleshooting';
        }
        
        renderQuestions();
        updateProgress(0, questions.length, 'Diagnostic Questions');
    } catch(e) { showError('Failed to load: ' + e.message); }
}

function renderQuestions() {
    var html = '';
    questions.forEach(function(q, i) {
        html += '<div class="wiz-q-card" id="q-card-'+q.id+'">';
        html += '<div class="wiz-q-num">Question ' + (i+1) + ' of ' + questions.length + '</div>';
        html += '<div class="wiz-q-text">' + esc(q.question) + '</div>';
        if (q.description) html += '<div class="wiz-q-desc">' + esc(q.description) + '</div>';
        html += '<div class="wiz-q-btns">';
        html += '<button class="wiz-q-btn yes" onclick="answerQuestion('+q.id+',\'yes\')" id="q-yes-'+q.id+'">YES</button>';
        html += '<button class="wiz-q-btn no" onclick="answerQuestion('+q.id+',\'no\')" id="q-no-'+q.id+'">NO</button>';
        html += '</div></div>';
    });
    document.getElementById('questions-container').innerHTML = html;
    lucide.createIcons();
}

function answerQuestion(qId, answer) {
    answers[qId] = answer;
    
    // Update button styles
    var card = document.getElementById('q-card-' + qId);
    card.classList.add('answered');
    
    var yesBtn = document.getElementById('q-yes-' + qId);
    var noBtn = document.getElementById('q-no-' + qId);
    yesBtn.className = 'wiz-q-btn yes' + (answer === 'yes' ? ' selected-yes' : '');
    noBtn.className = 'wiz-q-btn no' + (answer === 'no' ? ' selected-no' : '');
    
    // Check if all questions answered
    var answered = Object.keys(answers).length;
    var total = questions.length;
    document.getElementById('start-btn').disabled = answered < total;
    updateProgress(answered, total, 'Diagnostic Questions');
    
    addLogEntry('Answered: ' + (answer === 'yes' ? '✅ YES' : '❌ NO'), getQuestionText(qId));
}

function getQuestionText(qId) {
    var q = questions.find(function(x) { return x.id == qId; });
    return q ? q.question : '';
}

// ===== START: Get filtered steps =====
async function startTroubleshooting() {
    try {
        var data = await safeFetch(APP_BASE + 'api/troubleshooting/decision.php', {
            method: 'POST',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify({
                action: 'start',
                issue_id: issueId,
                session_id: sessionId,
                answers: answers,
            }),
        });
        if (data.error) { showError(data.error); return; }
        
        steps = data.steps || [];
        currentStepIndex = 0;
        stepHistory = [];
        
        // Count only non-terminal steps for progress
        var totalSteps = steps.filter(function(s) { return !s.is_terminal && !s.result_type; }).length;
        
        // Switch to Phase 2
        document.getElementById('phase-questions').style.display = 'none';
        document.getElementById('phase-steps').style.display = '';
        document.getElementById('wiz-safety').style.display = 'none';
        
        updateProgress(0, totalSteps, 'Troubleshooting Steps');
        showStep(steps[0]);
    } catch(e) { showError('Failed to start: ' + e.message); }
}

// ===== SHOW STEP =====
function showStep(step) {
    if (!step) { showResult('escalated', {message:'No more steps'}); return; }
    
    // If this is a terminal node, show result directly (no YES/NO buttons)
    if (step.is_terminal || step.result_type) {
        var type = step.result_type || 'escalation';
        var resultTitles = {
            solved: 'Problem Solved!',
            escalation: 'Escalation Required',
            hardware: 'Hardware Replacement Needed'
        };
        var msg = resultTitles[type] || 'Troubleshooting Complete';
        var sol = step.result_solution || step.description || '';
        showResult(type, {
            message: msg,
            solution: sol,
            detail: step.description || '',
            steps_completed: currentStepIndex + 1,
        });
        return;
    }
    
    stepStartTime = Date.now();
    
    var riskLabels = {safe:'Safe',caution:'Caution',danger:'High Risk'};
    var riskClass = step.risk || 'safe';
    
    // Count non-terminal steps for display
    var totalNonTerminal = steps.filter(function(s) { return !s.is_terminal && !s.result_type; }).length;
    
    var html = '<div class="wiz-step-card">';
    html += '<div class="wiz-step-badge">';
    html += '<span class="wiz-step-num">Step ' + (completedSteps + 1) + ' of ' + totalNonTerminal + '</span>';
    html += '<span class="wiz-risk ' + riskClass + '"><span style="width:6px;height:6px;border-radius:50%;background:currentColor;"></span> ' + riskLabels[riskClass] + '</span>';
    html += '</div>';
    html += '<div class="wiz-step-title">' + esc(step.question) + '</div>';
    if (step.description) html += '<div class="wiz-step-desc">' + esc(step.description).replace(/\n/g, '<br>') + '</div>';
    // Show visual guide groups (text + images) or legacy text-only guide
    if (step.visual_guide_images) {
        try {
            var guides = JSON.parse(step.visual_guide_images);
            if (guides.length && (guides[0].text || guides[0].image)) {
                html += '<div class="wiz-visual">';
                html += '<strong>📍 Visual Guide:</strong>';
                guides.forEach(function(g, gi) {
                    if (!g.text && !g.image) return;
                    html += '<div style="display:flex;gap:10px;align-items:flex-start;margin-top:'+(gi>0?'10px':'8px')+';padding-top:'+(gi>0?'10px':'0')+';border-top:'+(gi>0?'1px solid #bfdbfe':'none')+';">';
                    html += '<span style="display:inline-flex;align-items:center;justify-content:center;width:20px;height:20px;border-radius:50%;font-size:10px;font-weight:700;background:#dbeafe;color:#2563eb;flex-shrink:0;">'+(gi+1)+'</span>';
                    html += '<div style="flex:1;">';
                    if (g.text) html += '<div style="font-size:13px;line-height:1.5;">'+esc(g.text).replace(/\n/g,'<br>')+'</div>';
                    if (g.image) html += '<img src="'+g.image+'" style="margin-top:6px;max-width:100%;max-height:200px;border-radius:8px;border:1px solid #bfdbfe;">';
                    html += '</div></div>';
                });
                html += '</div>';
            } else if (step.visual_guide) {
                html += '<div class="wiz-visual"><strong>📍 Visual Guide:</strong><br>' + esc(step.visual_guide).replace(/\n/g, '<br>') + '</div>';
            }
        } catch(e) {
            if (step.visual_guide) html += '<div class="wiz-visual"><strong>📍 Visual Guide:</strong><br>' + esc(step.visual_guide).replace(/\n/g, '<br>') + '</div>';
        }
    } else if (step.visual_guide) {
        html += '<div class="wiz-visual"><strong>📍 Visual Guide:</strong><br>' + esc(step.visual_guide).replace(/\n/g, '<br>') + '</div>';
    }
    if (step.expected_result) html += '<div class="wiz-expected"><strong>Expected result:</strong> ' + esc(step.expected_result) + '</div>';
    if (step.tools_needed) html += '<div class="wiz-tools"><i data-lucide="tool" style="width:14px;height:14px;flex-shrink:0;"></i> <strong>Tools needed:</strong> ' + esc(step.tools_needed) + '</div>';
    html += '</div>';
    
    document.getElementById('step-container').innerHTML = html;
    
    // Action buttons
    var actions = '<div class="wiz-actions">';
    actions += '<button class="wiz-action-btn worked" onclick="answerStep(\'worked\')">';
    actions += '<span class="wiz-action-label" style="color:#16a34a;">✅ Did it work?</span>';
    actions += '<span class="wiz-action-sub">The issue is resolved</span></button>';
    actions += '<button class="wiz-action-btn failed" onclick="answerStep(\'not_worked\')">';
    actions += '<span class="wiz-action-label" style="color:#dc2626;">❌ Did not work</span>';
    actions += '<span class="wiz-action-sub">Try the next step</span></button>';
    actions += '</div>';
    document.getElementById('step-actions').innerHTML = actions;
    
    lucide.createIcons();
    updateProgress(currentStepIndex, steps.length, 'Troubleshooting Steps');
}

// ===== ANSWER STEP =====
var answering = false;
var completedSteps = 0;
var currentNode = null;

async function answerStep(answer) {
    if (answering) return;
    answering = true;
    var timeSpent = Math.round((Date.now() - stepStartTime) / 1000);
    var step = currentNode || steps[currentStepIndex];
    if (!step) { answering = false; return; }
    
    completedSteps++;
    stepHistory.push({
        type: 'step',
        question: step.question,
        answer: answer,
        time_spent: timeSpent,
    });
    
    addLogEntry(
        answer === 'worked' ? '✅ WORKED' : '❌ DID NOT WORK',
        step.question
    );
    
    try {
        var data = await safeFetch(APP_BASE + 'api/troubleshooting/decision.php', {
            method: 'POST',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify({
                action: 'answer_step',
                node_id: step.id,
                answer: answer,
                session_id: sessionId,
                step_history: stepHistory,
                time_spent: timeSpent,
                issue_id: issueId,
            }),
        });
        
        if (data.phase === 'result') {
            showResult(data.result_type || 'escalated', data);
        } else if (data.phase === 'step' && data.node) {
            currentNode = data.node;
            // Count total non-terminal steps
            var totalSteps = steps.filter(function(s) { return !s.is_terminal && !s.result_type; }).length;
            updateProgress(completedSteps, totalSteps, 'Troubleshooting Steps');
            showStep(data.node);
        } else {
            showResult('escalated', {message:'No more steps available'});
        }
    } catch(e) {
        showResult('escalated', {message:'Error: ' + e.message});
    }
    answering = false;
}

// ===== SHOW RESULT =====
function showResult(type, data) {
    document.getElementById('phase-steps').style.display = 'none';
    document.getElementById('phase-result').style.display = '';
    
    var configs = {
        solved: { icon:'check-circle', color:'#16a34a', bg:'#dcfce7', title:'Problem Solved!', badge:'SOLVED', badgeBg:'#dcfce7', badgeColor:'#16a34a' },
        escalation: { icon:'alert-triangle', color:'#d97706', bg:'#fef3c7', title:'Escalation Required', badge:'ESCALATE', badgeBg:'#fef3c7', badgeColor:'#d97706' },
        hardware: { icon:'cpu', color:'#dc2626', bg:'#fee2e2', title:'Hardware Replacement Needed', badge:'HARDWARE', badgeBg:'#fee2e2', badgeColor:'#dc2626' },
    };
    var c = configs[type] || configs.escalation;
    
    var html = '<div class="wiz-result">';
    html += '<div class="wiz-result-icon" style="background:'+c.bg+';"><i data-lucide="'+c.icon+'" style="width:36px;height:36px;color:'+c.color+';"></i></div>';
    html += '<div class="wiz-result-badge" style="background:'+c.badgeBg+';color:'+c.badgeColor+';">'+c.badge+'</div>';
    html += '<h2>' + esc(data.message || c.title) + '</h2>';
    html += '<p>' + esc(data.solution || data.detail || '') + '</p>';
    html += '<div style="font-size:13px;color:#94a3b8;margin-bottom:16px;">Completed in '+completedSteps+' step'+(completedSteps!==1?'s':'')+'</div>';
    html += '</div>';
    
    // Report
    if (data.report) {
        html += '<div class="wiz-report">';
        html += '<div class="wiz-report-head"><h3>Troubleshooting Report</h3>';
        html += '<button onclick="copyReport()" style="display:inline-flex;align-items:center;gap:6px;padding:6px 14px;background:#2563eb;color:#fff;border:none;border-radius:8px;font-size:12px;font-weight:600;cursor:pointer;"><i data-lucide="copy" style="width:13px;height:13px;"></i> Copy Report</button></div>';
        html += '<pre id="report-content">' + esc(data.report) + '</pre>';
        html += '</div>';
    }
    
    // Action buttons
    html += '<div style="display:flex;gap:10px;justify-content:center;margin-top:20px;">';
    html += '<a href="' + backUrl + '" class="wiz-btn wiz-btn-back"><i data-lucide="arrow-left"></i> Back to Troubleshoot</a>';
    html += '</div>';
    
    document.getElementById('phase-result').innerHTML = html;
    document.getElementById('progress-fill').style.width = '100%';
    document.getElementById('progress-pct').textContent = '100% Complete';
    document.getElementById('progress-label').textContent = 'Complete';
    
    lucide.createIcons();
}

function copyReport() {
    var el = document.getElementById('report-content');
    if (el) {
        navigator.clipboard.writeText(el.textContent).then(function() {
            showToast('Report copied to clipboard!', 'success');
        }).catch(function() {
            var t = document.createElement('textarea');
            t.value = el.textContent;
            document.body.appendChild(t);
            t.select();
            document.execCommand('copy');
            t.remove();
            showToast('Report copied!', 'success');
        });
    }
}

// ===== HELPERS =====
function updateProgress(current, total, label) {
    var pct = total > 0 ? Math.round((current / total) * 100) : 0;
    document.getElementById('progress-fill').style.width = pct + '%';
    document.getElementById('progress-pct').textContent = pct + '% Complete';
    document.getElementById('progress-label').textContent = label;
}

function addLogEntry(title, detail) {
    var el = document.getElementById('log-entries');
    var html = '<div class="wiz-log-entry"><div class="wiz-log-entry-title">' + title + '</div>';
    if (detail) html += '<div class="wiz-log-entry-detail">' + esc(detail).substring(0, 100) + '</div>';
    html += '</div>';
    el.insertAdjacentHTML('beforeend', html);
}

function showError(msg) {
    document.getElementById('questions-container').innerHTML = '<div style="text-align:center;padding:24px;color:#dc2626;"><p>'+esc(msg)+'</p></div>';
}

function esc(s) { if (!s) return ''; return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

function showToast(msg, type) {
    var t = document.createElement('div');
    t.className = 'toast ' + type;
    t.innerHTML = '<span style="font-size:13px;">' + msg + '</span>';
    var c = document.getElementById('toast-container');
    if (c) c.appendChild(t);
    setTimeout(function() { t.remove(); }, 4000);
}

init();
lucide.createIcons();
</script>
<?php require APP_ROOT . '/includes/layout_footer.php'; ?>
