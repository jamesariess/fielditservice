<?php
$page_title = 'Troubleshooting Wizard';
$active_menu = 'troubleshoot';
require APP_ROOT . '/includes/layout_header.php';
$issueParam = $_GET['issue'] ?? '1';
$backDevice = $_GET['device'] ?? '';
$backUrl = $urlBase . 'troubleshoot' . ($backDevice ? '?device=' . urlencode($backDevice) : '');
$exitUrl = $urlBase . 'troubleshoot'; // Always goes to device selection

// Resolve issue from database by ID or slug
$issue = null;
$issueId = 0;
$issueSlug = '';
if (!empty($issueParam)) {
    if (is_numeric($issueParam)) {
        $issue = Database::fetch('SELECT * FROM troubleshooting_issues WHERE id = ?', [(int)$issueParam]);
    } else {
        $issue = Database::fetch('SELECT * FROM troubleshooting_issues WHERE slug = ?', [$issueParam]);
    }
}
if ($issue) {
    $issueId = $issue['id'];
    $issueSlug = $issue['slug'];
} else {
    // Fallback to first issue
    $issue = Database::fetch('SELECT * FROM troubleshooting_issues ORDER BY id LIMIT 1');
    if ($issue) {
        $issueId = $issue['id'];
        $issueSlug = $issue['slug'];
    } else {
        $issueTitle = 'Issue';
    }
}
$issueTitle = e($issue['title'] ?? 'Issue');
$issueCategory = e(ucfirst($issue['category'] ?? 'General'));
?>

<style>
.wiz-page { max-width: 1200px; margin: 0 auto; }

/* Header */
.wiz-header { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 8px; gap: 16px; }
.wiz-title { font-size: 26px; font-weight: 800; color: #111827; letter-spacing: -0.03em; line-height: 1.2; }
.dark .wiz-title { color: #f1f5f9; }
.wiz-subtitle { font-size: 14px; color: #64748b; margin-top: 4px; line-height: 1.5; max-width: 600px; }
.dark .wiz-subtitle { color: #94a3b8; }
.wiz-header-btns { display: flex; gap: 8px; flex-shrink: 0; }
.wiz-btn-back, .wiz-btn-exit {
    display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px;
    border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer;
    text-decoration: none; transition: all 0.2s; border: 1px solid #e5e7eb;
}
.wiz-btn-back { background: #fff; color: #374151; }
.wiz-btn-back:hover { background: #f8fafc; border-color: #d1d5db; }
.wiz-btn-exit { background: transparent; color: #6b7280; border-color: transparent; }
.wiz-btn-exit:hover { color: #dc2626; background: #fef2f2; }
.wiz-btn-back i, .wiz-btn-back svg, .wiz-btn-exit i, .wiz-btn-exit svg { width: 15px; height: 15px; }
.dark .wiz-btn-back { background: #1e293b; border-color: #334155; color: #e2e8f0; }
.dark .wiz-btn-back:hover { background: #334155; }

/* Safety Banner */
.wiz-safety {
    display: flex; align-items: flex-start; gap: 10px; padding: 14px 18px;
    background: #fef2f2; border: 1px solid #fecaca; border-radius: 10px;
    margin-bottom: 20px; font-size: 13px; line-height: 1.5; color: #991b1b;
}
.wiz-safety i, .wiz-safety svg { width: 18px; height: 18px; flex-shrink: 0; margin-top: 1px; color: #dc2626; }
.wiz-safety strong { color: #b91c1c; }
.dark .wiz-safety { background: rgba(220,38,38,0.08); border-color: rgba(220,38,38,0.2); color: #fca5a5; }
.dark .wiz-safety strong { color: #f87171; }

/* Main Layout */
.wiz-layout { display: grid; grid-template-columns: 1fr 340px; gap: 20px; align-items: start; }

/* Left Column - Steps */
.wiz-main { min-width: 0; }

/* Progress */
.wiz-progress-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px; }
.wiz-progress-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: #94a3b8; }
.wiz-progress-pct { font-size: 12px; font-weight: 600; color: #2563eb; }
.wiz-progress-track { height: 6px; background: #e5e7eb; border-radius: 3px; overflow: hidden; margin-bottom: 12px; }
.wiz-progress-fill { height: 100%; background: linear-gradient(90deg, #22c55e, #16a34a); border-radius: 3px; transition: width 0.5s cubic-bezier(0.4,0,0.2,1); }

/* Step Badge */
.wiz-step-badge { display: inline-flex; align-items: center; gap: 6px; margin-bottom: 16px; }
.wiz-step-num { font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; }
.wiz-risk-badge {
    display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px;
    border-radius: 20px; font-size: 11px; font-weight: 600;
}
.wiz-risk-badge.safe { background: #f0fdf4; color: #16a34a; }
.wiz-risk-badge.caution { background: #fffbeb; color: #d97706; }
.wiz-risk-badge.danger { background: #fef2f2; color: #dc2626; }
.dark .wiz-risk-badge.safe { background: rgba(22,163,74,0.15); color: #4ade80; }
.dark .wiz-risk-badge.caution { background: rgba(217,119,6,0.15); color: #fbbf24; }
.dark .wiz-risk-badge.danger { background: rgba(220,38,38,0.15); color: #f87171; }
.wiz-risk-dot { width: 6px; height: 6px; border-radius: 50%; }
.wiz-risk-dot.safe { background: #16a34a; }
.wiz-risk-dot.caution { background: #d97706; }
.wiz-risk-dot.danger { background: #dc2626; }

/* Question */
.wiz-question { font-size: 20px; font-weight: 700; color: #111827; margin-bottom: 16px; line-height: 1.3; }
.dark .wiz-question { color: #f1f5f9; }

/* Instructions */
.wiz-instructions { list-style: disc; padding-left: 20px; margin-bottom: 16px; }
.wiz-instructions li { font-size: 14px; color: #475569; line-height: 1.7; margin-bottom: 4px; }
.dark .wiz-instructions li { color: #94a3b8; }

/* Expected Result */
.wiz-expected {
    display: flex; align-items: flex-start; gap: 8px; padding: 12px 16px;
    background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 8px;
    margin-bottom: 16px; font-size: 13px; color: #0c4a6e;
}
.wiz-expected strong { color: #0369a1; }
.dark .wiz-expected { background: rgba(14,165,233,0.08); border-color: rgba(14,165,233,0.2); color: #7dd3fc; }
.dark .wiz-expected strong { color: #38bdf8; }

/* Why Box */
.wiz-why {
    background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 10px;
    padding: 16px 18px; margin-bottom: 20px;
}
.wiz-why-header {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 8px; cursor: pointer;
}
.wiz-why-title { display: flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 700; color: #166534; }
.wiz-why-title i, .wiz-why-title svg { width: 15px; height: 15px; }
.wiz-why-close { background: none; border: none; cursor: pointer; color: #94a3b8; padding: 2px; }
.wiz-why-close:hover { color: #64748b; }
.wiz-why-text { font-size: 13px; color: #166534; line-height: 1.6; }
.dark .wiz-why { background: rgba(22,163,74,0.06); border-color: rgba(22,163,74,0.15); }
.dark .wiz-why-title { color: #4ade80; }
.dark .wiz-why-text { color: #86efac; }

/* YES/NO Buttons */
.wiz-yesno { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-top: 20px; }
.wiz-yesno-btn {
    display: flex; flex-direction: column; align-items: center; gap: 4px;
    padding: 20px 16px; border: 2px solid #e5e7eb; border-radius: 12px;
    background: #fff; cursor: pointer; transition: all 0.2s;
}
.wiz-yesno-btn:hover { border-color: #2563eb; background: #f8fafc; }
.wiz-yesno-btn.yes:hover { border-color: #16a34a; background: #f0fdf4; }
.wiz-yesno-btn.no:hover { border-color: #dc2626; background: #fef2f2; }
.wiz-yesno-label { font-size: 16px; font-weight: 700; color: #111827; }
.wiz-yesno-sub { font-size: 12px; color: #94a3b8; }
.dark .wiz-yesno-btn { background: #1e293b; border-color: #334155; }
.dark .wiz-yesno-label { color: #f1f5f9; }
.dark .wiz-yesno-btn:hover { background: #334155; }

/* Action Buttons */
.wiz-actions { display: flex; gap: 10px; margin-top: 24px; justify-content: center; }
.wiz-action-btn {
    display: inline-flex; align-items: center; gap: 8px; padding: 12px 24px;
    border-radius: 100px; font-size: 14px; font-weight: 700; cursor: pointer;
    border: none; transition: all 0.2s; text-transform: uppercase; letter-spacing: 0.03em;
}
.wiz-action-btn i, .wiz-action-btn svg { width: 16px; height: 16px; }
.wiz-action-btn.solved { background: #16a34a; color: #fff; }
.wiz-action-btn.solved:hover { background: #15803d; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(22,163,74,0.3); }
.wiz-action-btn.not-solved { background: #ea580c; color: #fff; }
.wiz-action-btn.not-solved:hover { background: #c2410c; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(234,88,12,0.3); }
.wiz-action-btn.escalate { background: #dc2626; color: #fff; }
.wiz-action-btn.escalate:hover { background: #b91c1c; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(220,38,38,0.3); }
.wiz-action-btn.secondary { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }
.wiz-action-btn.secondary:hover { background: #e2e8f0; }

/* Right Sidebar */
.wiz-sidebar { position: sticky; top: 80px; }

.wiz-log {
    background: #fff; border: 1px solid #e5e7eb; border-radius: 12px;
    overflow: hidden; margin-bottom: 16px;
}
.dark .wiz-log { background: #1e293b; border-color: #334155; }
.wiz-log-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 16px 18px; border-bottom: 1px solid #f1f5f9;
}
.dark .wiz-log-header { border-bottom-color: #1e293b; }
.wiz-log-title { font-size: 14px; font-weight: 700; color: #111827; }
.dark .wiz-log-title { color: #f1f5f9; }
.wiz-log-badge { font-size: 11px; color: #94a3b8; font-weight: 500; }
.wiz-log-body { padding: 16px 18px; }
.wiz-log-entry { margin-bottom: 12px; }
.wiz-log-entry-title { font-size: 13px; font-weight: 600; color: #111827; margin-bottom: 2px; }
.dark .wiz-log-entry-title { color: #e2e8f0; }
.wiz-log-entry-detail { font-size: 12px; color: #94a3b8; }

.wiz-notes-label { font-size: 12px; font-weight: 600; color: #64748b; margin-bottom: 6px; margin-top: 12px; }
.wiz-notes-textarea {
    width: 100%; min-height: 80px; padding: 10px 12px; border: 1px solid #e5e7eb;
    border-radius: 8px; font-size: 13px; color: #111827; resize: vertical;
    font-family: inherit; transition: border-color 0.2s;
}
.wiz-notes-textarea:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,0.08); }
.wiz-notes-textarea::placeholder { color: #94a3b8; }
.dark .wiz-notes-textarea { background: #0f172a; border-color: #334155; color: #f1f5f9; }
.wiz-save-btn {
    width: 100%; padding: 10px; background: #f1f5f9; border: 1px solid #e2e8f0;
    border-radius: 8px; font-size: 13px; font-weight: 600; color: #475569;
    cursor: pointer; transition: all 0.2s; margin-top: 8px;
}
.wiz-save-btn:hover { background: #e2e8f0; }
.dark .wiz-save-btn { background: #334155; border-color: #475569; color: #94a3b8; }

/* Result Screen */
.wiz-result {
    text-align: center; padding: 32px 24px;
    background: #fff; border: 1px solid #e5e7eb; border-radius: 12px;
}
.dark .wiz-result { background: #1e293b; border-color: #334155; }

/* Responsive */
@media (max-width: 900px) {
    .wiz-layout { grid-template-columns: 1fr !important; }
    .wiz-sidebar { position: static; }
    .wiz-header { flex-direction: column; }
}
</style>

<div class="wiz-page">
    <!-- Header -->
    <div class="wiz-header">
        <div>
            <h1 class="wiz-title" id="wiz-title"><?= $issueTitle ?> Troubleshooting</h1>
            <p class="wiz-subtitle">Follow the steps in order. Record honest results — the escalation report is generated from your answers.</p>
        </div>
        <div class="wiz-header-btns">
            <a href="<?= $backUrl ?>" class="wiz-btn-back">
                <i data-lucide="arrow-left"></i> Back
            </a>
            <a href="<?= $exitUrl ?>" class="wiz-btn-exit">
                <i data-lucide="x"></i> Exit
            </a>
        </div>
    </div>

    <!-- Safety Banner -->
    <div class="wiz-safety" id="wiz-safety">
        <i data-lucide="shield-alert"></i>
        <div>
            <strong>Safety classification — read before starting:</strong>
            Unplug the power cable before opening the case and follow ESD precautions (use an anti-static wrist strap).
        </div>
    </div>

    <!-- Main Layout -->
    <div class="wiz-layout">
        <!-- Left: Steps -->
        <div class="wiz-main">
            <!-- Progress -->
            <div class="wiz-progress-header">
                <span class="wiz-progress-label">Progress</span>
                <span class="wiz-progress-pct" id="progress-pct">0% Complete</span>
            </div>
            <div class="wiz-progress-track">
                <div class="wiz-progress-fill" id="progress-fill" style="width:0%;"></div>
            </div>

            <!-- Step Badge -->
            <div class="wiz-step-badge">
                <span class="wiz-step-num" id="step-label">Step 1</span>
                <span class="wiz-risk-badge safe" id="risk-badge">
                    <span class="wiz-risk-dot safe" id="risk-dot"></span>
                    <span id="risk-label">Safe</span>
                </span>
            </div>

            <!-- Question -->
            <h2 class="wiz-question" id="step-question">Loading...</h2>

            <!-- Instructions -->
            <ul class="wiz-instructions" id="step-instructions">
                <li>Loading troubleshooting step...</li>
            </ul>

            <!-- Expected Result -->
            <div class="wiz-expected" id="step-expected">
                <strong>Expected result:</strong>
                <span id="expected-text">Loading...</span>
            </div>

            <!-- Why Box -->
            <div class="wiz-why" id="step-why-box" style="display:none;">
                <div class="wiz-why-header" onclick="toggleWhy()">
                    <div class="wiz-why-title">
                        <i data-lucide="help-circle"></i>
                        Why am I doing this?
                    </div>
                    <button class="wiz-why-close" onclick="event.stopPropagation();toggleWhy()">
                        <i data-lucide="x" style="width:14px;height:14px;"></i>
                    </button>
                </div>
                <p class="wiz-why-text" id="step-why-text"></p>
            </div>

            <!-- YES / NO Buttons -->
            <div class="wiz-yesno" id="yesno-buttons">
                <button class="wiz-yesno-btn yes" onclick="answerQuestion('yes')" id="btn-yes">
                    <span class="wiz-yesno-label">YES</span>
                    <span class="wiz-yesno-sub">Done / as expected</span>
                </button>
                <button class="wiz-yesno-btn no" onclick="answerQuestion('no')" id="btn-no">
                    <span class="wiz-yesno-label">NO</span>
                    <span class="wiz-yesno-sub">Did not work</span>
                </button>
            </div>

            <!-- Result Screen -->
            <div id="result-step" style="display:none;"></div>

            <!-- Action Buttons -->
            <div class="wiz-actions" id="action-buttons" style="display:none;"></div>
        </div>

        <!-- Right: Session Log -->
        <div class="wiz-sidebar">
            <div class="wiz-log">
                <div class="wiz-log-header">
                    <span class="wiz-log-title">Session log</span>
                    <span class="wiz-log-badge">auto-recorded</span>
                </div>
                <div class="wiz-log-body">
                    <div class="wiz-log-entry">
                        <div class="wiz-log-entry-title">Session started</div>
                        <div class="wiz-log-entry-detail">Procedure: <span id="log-procedure"><?= $issueTitle ?> Troubleshooting</span></div>
                    </div>
                    <div id="log-entries"></div>
                    <div class="wiz-notes-label">Technician notes (saved with session)</div>
                    <textarea class="wiz-notes-textarea" id="tech-notes" placeholder="Serial numbers, part numbers, photos taken..."></textarea>
                    <button class="wiz-save-btn" onclick="saveNote()">Save note</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const issueId = <?= $issueId ?>;
const issueSlug = '<?= $issueSlug ?>';
const backUrl = '<?= $backUrl ?>';
let progressId = null;
let currentStep = 0;
let totalSteps = 1;
let currentNodeId = null;
let stepHistory = [];
let whyVisible = true;

function toggleWhy() {
    var box = document.getElementById('step-why-box');
    whyVisible = !whyVisible;
    box.style.display = whyVisible ? '' : 'none';
}

function addLogEntry(title, detail) {
    var entries = document.getElementById('log-entries');
    var html = '<div class="wiz-log-entry" style="padding-top:8px;border-top:1px solid #f1f5f9;">' +
        '<div class="wiz-log-entry-title">' + title + '</div>' +
        (detail ? '<div class="wiz-log-entry-detail">' + detail + '</div>' : '') +
    '</div>';
    entries.innerHTML += html;
}

function saveNote() {
    var notes = document.getElementById('tech-notes').value;
    if (notes.trim()) {
        addLogEntry('Note saved', notes.substring(0, 80) + (notes.length > 80 ? '...' : ''));
        showToast('Note saved to session log', 'success');
    }
}

async function initWizard() {
    try {
        var apiParam = issueSlug || issueId;
        var res = await fetch(APP_BASE + 'api/troubleshooting/decision.php?issue=' + encodeURIComponent(apiParam));
        var data = await res.json();

        if (data.error) { showError(data.error); return; }

        progressId = data.progress_id;
        totalSteps = data.total_steps || 10;
        currentNodeId = data.node.id;
        currentStep = data.current_step || 1;

        if (data.issue) {
            document.getElementById('wiz-title').textContent = (data.issue.title || 'Issue') + ' Troubleshooting';
            document.getElementById('log-procedure').textContent = (data.issue.title || 'Issue') + ' Troubleshooting';
        }
        // Session started entry is already in HTML
        renderStep(data.node);
    } catch (err) {
        showError('Failed to load troubleshooting tree. Please try again.');
    }
}

function renderStep(node) {
    // Question
    document.getElementById('step-question').textContent = node.question || 'Is this working?';

    // Instructions (parse from description or generate)
    var instrList = document.getElementById('step-instructions');
    var instructions = node.instruction || node.description || 'Check and verify the current step.';
    var steps = instructions.split('|').map(function(s) { return s.trim(); }).filter(Boolean);
    if (steps.length === 1) {
        // Split by period or newline for bullet points
        steps = instructions.split(/\.\s+/).map(function(s) { return s.trim(); }).filter(Boolean);
    }
    instrList.innerHTML = steps.map(function(s) {
        return '<li>' + escapeHtml(s) + (s.endsWith('.') ? '' : '.') + '</li>';
    }).join('');

    // Risk badge
    var risk = node.risk || 'safe';
    var riskLabels = { safe: 'Safe', caution: 'Caution', danger: 'High Risk' };
    var badge = document.getElementById('risk-badge');
    var dot = document.getElementById('risk-dot');
    var label = document.getElementById('risk-label');
    badge.className = 'wiz-risk-badge ' + risk;
    dot.className = 'wiz-risk-dot ' + risk;
    label.textContent = riskLabels[risk] || 'Safe';

    // Expected result
    var expectedText = document.getElementById('expected-text');
    expectedText.textContent = node.expected_yes || node.expected_no || 'Verify the result matches expectations.';

    // Why box
    var whyBox = document.getElementById('step-why-box');
    var whyText = document.getElementById('step-why-text');
    if (node.why) {
        whyBox.style.display = '';
        whyText.textContent = node.why;
        whyVisible = true;
    } else {
        whyBox.style.display = 'none';
    }

    // Progress
    var pct = Math.min(100, Math.round((currentStep / totalSteps) * 100));
    document.getElementById('progress-fill').style.width = pct + '%';
    document.getElementById('progress-pct').textContent = pct + '% Complete';
    document.getElementById('step-label').textContent = 'Step ' + currentStep;

    // Show step, hide result
    document.getElementById('yesno-buttons').style.display = '';
    document.getElementById('result-step').style.display = 'none';
    document.getElementById('action-buttons').style.display = 'none';

    lucide.createIcons();
    stepHistory.push({ step: currentStep, node: node });
}

async function answerQuestion(answer) {
    document.getElementById('btn-yes').disabled = true;
    document.getElementById('btn-no').disabled = true;

    var answerLabel = answer === 'yes' ? 'YES — Done / as expected' : 'NO — Did not work';
    var question = document.getElementById('step-question').textContent;
    addLogEntry('Step ' + currentStep + ': ' + answerLabel, question);

    // Track step history for summary report
    stepHistory.push({
        step: currentStep,
        question: question,
        answer: answer,
        issue_title: document.getElementById('wiz-title').textContent.replace(' Troubleshooting', ''),
    });

    try {
        var res = await fetch(APP_BASE + 'api/troubleshooting/decision.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                progress_id: progressId,
                node_id: currentNodeId,
                issue_id: issueId,
                answer: answer,
                step_history: stepHistory,
            }),
        });
        var data = await res.json();

        if (data.solved) showResult('solved', data);
        else if (data.escalated) showResult('escalated', data);
        else if (data.redirect) showResult('redirect', data);
        else if (data.hardware_replacement) showResult('hardware_replacement', data);
        else if (data.node) {
            currentNodeId = data.node.id;
            currentStep = data.current_step || (currentStep + 1);
            totalSteps = data.total_steps || totalSteps;
            renderStep(data.node);
        } else showResult('escalated', data);
    } catch (err) {
        console.error('Decision tree error:', err);
        showToast('Error advancing step. Please try again.', 'error');
    }

    document.getElementById('btn-yes').disabled = false;
    document.getElementById('btn-no').disabled = false;
}

function showResult(type, data) {
    document.getElementById('yesno-buttons').style.display = 'none';
    document.getElementById('progress-fill').style.width = '100%';
    document.getElementById('progress-pct').textContent = '100% Complete';

    var configs = {
        solved: { icon: 'check-circle', color: '#16a34a', title: data.message || 'Problem Solved!', detail: data.detail || data.solution || '', badgeText: 'SOLVED' },
        escalated: { icon: 'alert-triangle', color: '#d97706', title: data.message || 'Escalation Required', detail: data.detail || data.summary || 'All standard steps completed. Escalate to supervisor.', badgeText: 'ESCALATE' },
        hardware_replacement: { icon: 'cpu', color: '#dc2626', title: data.message || 'Hardware Replacement Needed', detail: data.detail || data.solution || 'This component needs to be replaced.', badgeText: 'HARDWARE' },
        redirect: { icon: 'arrow-right-circle', color: '#2563eb', title: data.message || 'Redirecting', detail: data.detail || 'Please follow the recommended troubleshooting path.', badgeText: 'REDIRECT' },
    };

    var c = configs[type];
    var html = '<div class="wiz-result">' +
        '<div style="width:64px;height:64px;border-radius:50%;background:' + c.color + '15;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">' +
            '<i data-lucide="' + c.icon + '" style="width:32px;height:32px;color:' + c.color + ';"></i>' +
        '</div>' +
        '<div style="display:inline-flex;padding:4px 14px;border-radius:20px;background:' + c.color + '15;color:' + c.color + ';font-size:12px;font-weight:700;margin-bottom:12px;">' + c.badgeText + '</div>' +
        '<h2 style="font-size:22px;font-weight:800;color:#111827;margin-bottom:8px;">' + escapeHtml(c.title) + '</h2>' +
        '<p style="font-size:14px;color:#64748b;max-width:500px;margin:0 auto;line-height:1.6;">' + escapeHtml(c.detail) + '</p>';

    if (data.steps_taken) {
        html += '<div style="margin-top:12px;font-size:12px;color:#94a3b8;">Completed in ' + data.steps_taken + ' step' + (data.steps_taken > 1 ? 's' : '') + '</div>';
    }
    html += '</div>';

    // Summary Report Section
    if (data.report || stepHistory.length > 0) {
        var reportText = data.report || buildReport();
        html += '<div style="margin-top:24px;text-align:left;background:#f8fafc;border:1px solid #e5e7eb;border-radius:12px;padding:20px;">' +
            '<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">' +
                '<h3 style="font-size:15px;font-weight:700;color:#111827;">Troubleshooting Report</h3>' +
                '<button onclick="copyReport()" style="display:inline-flex;align-items:center;gap:6px;padding:6px 14px;background:#2563eb;color:#fff;border:none;border-radius:8px;font-size:12px;font-weight:600;cursor:pointer;">' +
                    '<i data-lucide="copy" style="width:13px;height:13px;"></i> Copy Report' +
                '</button>' +
            '</div>' +
            '<pre id="report-content" style="background:#fff;border:1px solid #e5e7eb;border-radius:8px;padding:14px;font-size:12px;color:#374151;line-height:1.6;white-space:pre-wrap;font-family:monospace;max-height:300px;overflow-y:auto;">' + escapeHtml(reportText) + '</pre>' +
        '</div>';
    }

    document.getElementById('result-step').innerHTML = html;
    document.getElementById('result-step').style.display = 'block';

    addLogEntry('Session ended', 'Result: ' + c.badgeText);

    // Action buttons
    var actions = document.getElementById('action-buttons');
    actions.style.display = 'flex';
    if (type === 'solved') {
        actions.innerHTML = '<a href="' + backUrl + '" class="wiz-action-btn secondary"><i data-lucide="arrow-left"></i> Back</a>' +
            '<button class="wiz-action-btn solved" onclick="showToast(\'Ticket marked as solved!\',\'success\')"><i data-lucide="check"></i> SOLVED</button>' +
            '<a href="' + APP_BASE + 'tickets" class="wiz-action-btn secondary"><i data-lucide="ticket"></i> View Tickets</a>';
    } else if (type === 'escalated') {
        actions.innerHTML = '<a href="' + backUrl + '" class="wiz-action-btn secondary"><i data-lucide="arrow-left"></i> Back</a>' +
            '<button class="wiz-action-btn escalate" onclick="showToast(\'Escalated to supervisor!\',\'success\')"><i data-lucide="alert-triangle"></i> ESCALATE</button>';
    } else if (type === 'redirect') {
        actions.innerHTML = '<a href="' + backUrl + '" class="wiz-action-btn secondary"><i data-lucide="arrow-left"></i> Back</a>' +
            '<button class="wiz-action-btn not-solved" onclick="showToast(\'Redirected to correct flow\',\'info\')"><i data-lucide="arrow-right"></i> CONTINUE</button>';
    } else {
        actions.innerHTML = '<a href="' + backUrl + '" class="wiz-action-btn secondary"><i data-lucide="arrow-left"></i> Back</a>' +
            '<button class="wiz-action-btn escalate" onclick="showToast(\'Hardware replacement noted\',\'warning\')"><i data-lucide="cpu"></i> ESCALATE</button>';
    }

    lucide.createIcons();
}

function showError(msg) {
    document.getElementById('step-question').textContent = 'Error';
    document.getElementById('step-instructions').innerHTML = '<li>' + escapeHtml(msg) + '</li>';
    document.getElementById('yesno-buttons').style.display = 'none';
}

function buildReport() {
    var issueTitle = document.getElementById('wiz-title').textContent.replace(' Troubleshooting', '');
    var report = 'TROUBLESHOOTING REPORT\n';
    report += '========================================\n';
    report += 'Issue: ' + issueTitle + '\n';
    report += 'Date: ' + new Date().toLocaleString() + '\n';
    report += 'Technician: System Admin\n';
    report += '----------------------------------------\n\n';
    report += 'STEPS TAKEN:\n';
    stepHistory.forEach(function(step, i) {
        report += 'Step ' + (i+1) + ': ' + step.question + ' → ' + step.answer.toUpperCase() + '\n';
    });
    report += '\n----------------------------------------\n';
    report += 'RESULT: COMPLETED\n';
    report += '========================================\n';
    return report;
}

function copyReport() {
    var reportEl = document.getElementById('report-content');
    if (reportEl) {
        var text = reportEl.textContent;
        navigator.clipboard.writeText(text).then(function() {
            showToast('Report copied to clipboard!', 'success');
        }).catch(function() {
            // Fallback
            var textarea = document.createElement('textarea');
            textarea.value = text;
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            textarea.remove();
            showToast('Report copied to clipboard!', 'success');
        });
    }
}

function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function showToast(msg, type) {
    var toast = document.createElement('div');
    toast.className = 'toast ' + type;
    toast.innerHTML = '<i data-lucide="' + (type === 'success' ? 'check-circle' : 'alert-circle') + '" style="width:18px;height:18px;color:' + (type === 'success' ? '#16a34a' : '#dc2626') + '"></i><span style="font-size:13px;">' + msg + '</span>';
    var container = document.getElementById('toast-container');
    if (container) container.appendChild(toast);
    lucide.createIcons();
    setTimeout(function() { toast.remove(); }, 4000);
}

initWizard();
</script>
<?php require APP_ROOT . '/includes/layout_footer.php'; ?>
