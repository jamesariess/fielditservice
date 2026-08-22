<?php
$page_title = 'Troubleshooting Wizard';
$active_menu = 'troubleshoot';
require APP_ROOT . '/includes/layout_header.php';
$issueId = (int)($_GET['issue'] ?? 1);
$issues = DemoData::issues();
$issue = null;
foreach ($issues as $i) { if ($i['id'] == $issueId) { $issue = $i; break; } }
if (!$issue) { $issue = $issues[0]; $issueId = $issue['id']; }
?>

<div style="max-width:800px;margin:0 auto;">
    <!-- Back Button -->
    <a href="<?= $urlBase ?>troubleshoot" style="display:inline-flex;align-items:center;gap:6px;font-size:13px;font-weight:600;color:#64748b;text-decoration:none;margin-bottom:20px;transition:color 0.15s;" onmouseover="this.style.color='#2563eb'" onmouseout="this.style.color='#64748b'">
        <i data-lucide="arrow-left" style="width:16px;height:16px;"></i> Back to Troubleshoot
    </a>
    
    <!-- Issue Header -->
    <div id="wizard-header" style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:24px;margin-bottom:20px;">
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px;">
            <div id="issue-icon" style="width:44px;height:44px;border-radius:10px;display:flex;align-items:center;justify-content:center;background:#eff6ff;color:#2563eb;">
                <i data-lucide="stethoscope" style="width:22px;height:22px;"></i>
            </div>
            <div>
                <h1 id="issue-title" style="font-size:22px;font-weight:800;"><?= e($issue['title']) ?></h1>
                <p id="issue-subtitle" style="font-size:13px;color:#64748b;"><?= e(ucfirst($issue['category'] ?? 'General')) ?> Troubleshooting</p>
            </div>
        </div>
        <div id="issue-symptoms" style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px;">
            <?php foreach ($issue['symptoms'] ?? [] as $symptom): ?>
                <span class="badge badge-blue" style="font-size:11px;"><?= e($symptom) ?></span>
            <?php endforeach; ?>
        </div>
        <!-- Progress Bar -->
        <div style="display:flex;align-items:center;gap:12px;">
            <div class="progress-bar" style="flex:1;height:8px;">
                <div id="progress-fill" class="progress-fill blue" style="width:0%;"></div>
            </div>
            <span id="progress-text" style="font-size:12px;font-weight:700;color:#64748b;white-space:nowrap;">Step 1</span>
        </div>
    </div>

    <!-- Step Navigation Dots -->
    <div id="step-dots" style="display:flex;gap:8px;margin-bottom:20px;overflow-x:auto;padding-bottom:8px;"></div>

    <!-- Decision Tree Step -->
    <div id="step-container" style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:28px;min-height:300px;">
        <!-- Loading -->
        <div id="loading-state" style="text-align:center;padding:40px;">
            <div class="skeleton" style="width:60px;height:60px;border-radius:50%;margin:0 auto 16px;"></div>
            <div class="skeleton" style="height:20px;width:200px;margin:0 auto 12px;"></div>
            <div class="skeleton" style="height:14px;width:300px;margin:0 auto;"></div>
        </div>
        
        <!-- Question Step -->
        <div id="question-step" style="display:none;">
            <div style="display:flex;align-items:flex-start;gap:12px;margin-bottom:20px;">
                <div id="step-number" style="width:36px;height:36px;border-radius:50%;background:#2563eb;color:#fff;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:700;flex-shrink:0;">1</div>
                <div style="flex:1;">
                    <h2 id="step-question" style="font-size:18px;font-weight:700;color:#111827;margin-bottom:8px;"></h2>
                    <p id="step-instruction" style="font-size:14px;color:#64748b;line-height:1.6;"></p>
                </div>
            </div>

            <!-- Risk Badge & Tool -->
            <div id="step-meta" style="display:flex;gap:8px;margin-bottom:16px;"></div>

            <!-- Why Section -->
            <div id="step-why-box" style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;padding:14px 16px;margin-bottom:16px;">
                <div style="display:flex;align-items:center;gap:6px;font-size:12px;font-weight:700;color:#1d4ed8;margin-bottom:4px;">
                    <i data-lucide="info" style="width:14px;height:14px;"></i> Why am I doing this?
                </div>
                <p id="step-why" style="font-size:13px;color:#1e40af;line-height:1.5;"></p>
            </div>

            <!-- Expected Results -->
            <div id="step-expected" style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:20px;"></div>

            <!-- YES / NO Buttons -->
            <div style="display:flex;gap:12px;margin-top:20px;">
                <button id="btn-yes" onclick="answerQuestion('yes')" style="flex:1;padding:16px;border-radius:10px;font-size:15px;font-weight:700;border:2px solid #16a34a;background:#f0fdf4;color:#16a34a;cursor:pointer;transition:all 0.15s;display:flex;align-items:center;justify-content:center;gap:8px;"
                    onmouseover="this.style.background='#16a34a';this.style.color='#fff'" onmouseout="this.style.background='#f0fdf4';this.style.color='#16a34a'">
                    <i data-lucide="check" style="width:20px;height:20px;"></i> YES — Done
                </button>
                <button id="btn-no" onclick="answerQuestion('no')" style="flex:1;padding:16px;border-radius:10px;font-size:15px;font-weight:700;border:2px solid #dc2626;background:#fef2f2;color:#dc2626;cursor:pointer;transition:all 0.15s;display:flex;align-items:center;justify-content:center;gap:8px;"
                    onmouseover="this.style.background='#dc2626';this.style.color='#fff'" onmouseout="this.style.background='#fef2f2';this.style.color='#dc2626'">
                    <i data-lucide="x" style="width:20px;height:20px;"></i> NO — Not Fixed
                </button>
            </div>
        </div>

        <!-- Result Screen -->
        <div id="result-step" style="display:none;"></div>
    </div>

    <!-- Action Buttons -->
    <div id="action-buttons" style="display:none;margin-top:20px;gap:12px;"></div>
</div>

<script>
const issueId = <?= $issueId ?>;
let progressId = null;
let currentStep = 0;
let totalSteps = 1;
let currentNodeId = null;
let stepHistory = [];

async function initWizard() {
    try {
        const res = await fetch(APP_BASE + 'api/troubleshooting/decision.php?issue=' + issueId);
        const data = await res.json();
        
        if (data.error) {
            showError(data.error);
            return;
        }
        
        progressId = data.progress_id;
        totalSteps = data.total_steps || 10;
        currentNodeId = data.node.id;
        currentStep = data.current_step;
        
        updateHeader(data.issue);
        renderStep(data.node);
    } catch (err) {
        showError('Failed to load troubleshooting tree. Please try again.');
    }
}

function updateHeader(issue) {
    document.getElementById('issue-title').textContent = issue.title;
    document.getElementById('issue-subtitle').textContent = (issue.category || 'General') + ' Troubleshooting';
    
    const symptoms = document.getElementById('issue-symptoms');
    symptoms.innerHTML = (issue.symptoms || []).map(s => 
        `<span class="badge badge-blue">${escapeHtml(s)}</span>`
    ).join('');
}

function renderStep(node) {
    document.getElementById('loading-state').style.display = 'none';
    document.getElementById('question-step').style.display = 'block';
    document.getElementById('result-step').style.display = 'none';
    
    // Step number
    document.getElementById('step-number').textContent = currentStep;
    
    // Question
    document.getElementById('step-question').textContent = node.question;
    document.getElementById('step-instruction').textContent = node.instruction || '';
    
    // Risk badge
    const riskColors = { safe: 'green', caution: 'yellow', danger: 'red' };
    const riskLabels = { safe: 'Safe', caution: 'Caution', danger: 'High Risk' };
    const metaHtml = `
        <span class="badge badge-${riskColors[node.risk] || 'gray'}">
            <span class="risk-dot ${node.risk || 'safe'}"></span> ${riskLabels[node.risk] || 'Safe'}
        </span>
        ${node.tool ? `<span class="badge badge-gray"><i data-lucide="wrench" style="width:12px;height:12px;"></i> ${escapeHtml(node.tool)}</span>` : ''}
    `;
    document.getElementById('step-meta').innerHTML = metaHtml;
    
    // Why
    if (node.why) {
        document.getElementById('step-why-box').style.display = '';
        document.getElementById('step-why').textContent = node.why;
    } else {
        document.getElementById('step-why-box').style.display = 'none';
    }
    
    // Expected results
    const expectedHtml = `
        ${node.expected_yes ? `<div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:12px;">
            <div style="font-size:11px;font-weight:700;color:#166534;margin-bottom:4px;">If YES:</div>
            <p style="font-size:13px;color:#15803d;">${escapeHtml(node.expected_yes)}</p>
        </div>` : ''}
        ${node.expected_no ? `<div style="background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:12px;">
            <div style="font-size:11px;font-weight:700;color:#991b1b;margin-bottom:4px;">If NO:</div>
            <p style="font-size:13px;color:#dc2626;">${escapeHtml(node.expected_no)}</p>
        </div>` : ''}
    `;
    document.getElementById('step-expected').innerHTML = expectedHtml;
    
    // Progress
    const pct = Math.min(100, (currentStep / totalSteps) * 100);
    document.getElementById('progress-fill').style.width = pct + '%';
    document.getElementById('progress-text').textContent = `Step ${currentStep} of ~${totalSteps}`;
    
    lucide.createIcons();
    stepHistory.push({ step: currentStep, node });
}

async function answerQuestion(answer) {
    // Disable buttons during request
    document.getElementById('btn-yes').disabled = true;
    document.getElementById('btn-no').disabled = true;
    
    try {
        const res = await fetch(APP_BASE + 'api/troubleshooting/decision.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                progress_id: progressId,
                node_id: currentNodeId,
                issue_id: issueId,
                answer: answer,
            }),
        });
        const data = await res.json();
        
        if (data.solved) {
            showResult('solved', data);
        } else if (data.escalated) {
            showResult('escalated', data);
        } else if (data.redirect) {
            showResult('redirect', data);
        } else if (data.hardware_replacement) {
            showResult('hardware_replacement', data);
        } else if (data.node) {
            currentNodeId = data.node.id;
            currentStep = data.current_step || (currentStep + 1);
            totalSteps = data.total_steps || totalSteps;
            renderStep(data.node);
        } else {
            showResult('escalated', data);
        }
    } catch (err) {
        console.error('Decision tree error:', err);
        showToast('Error advancing step. Please try again.', 'error');
    }
    
    document.getElementById('btn-yes').disabled = false;
    document.getElementById('btn-no').disabled = false;
}

function showResult(type, data) {
    document.getElementById('question-step').style.display = 'none';
    document.getElementById('progress-fill').style.width = '100%';
    
    const configs = {
        solved: {
            icon: 'check-circle', color: '#16a34a', bg: '#f0fdf4', border: '#bbf7d0',
            title: data.message || 'Problem Solved!',
            detail: data.detail || data.solution || '',
            badge: 'badge-green', badgeText: 'SOLVED'
        },
        escalated: {
            icon: 'alert-triangle', color: '#d97706', bg: '#fffbeb', border: '#fde68a',
            title: data.message || 'Escalation Required',
            detail: data.detail || data.summary || 'All standard steps completed. Escalate to supervisor.',
            badge: 'badge-yellow', badgeText: 'ESCALATE'
        },
        hardware_replacement: {
            icon: 'cpu', color: '#dc2626', bg: '#fef2f2', border: '#fecaca',
            title: data.message || 'Hardware Replacement Needed',
            detail: data.detail || data.solution || 'This component needs to be replaced.',
            badge: 'badge-red', badgeText: 'HARDWARE'
        },
        redirect: {
            icon: 'arrow-right-circle', color: '#2563eb', bg: '#eff6ff', border: '#bfdbfe',
            title: data.message || 'Redirecting',
            detail: data.detail || 'Please follow the recommended troubleshooting path.',
            badge: 'badge-blue', badgeText: 'REDIRECT'
        },
    };
    
    const c = configs[type];
    let html = `
        <div style="text-align:center;padding:20px 0;">
            <div style="width:64px;height:64px;border-radius:50%;background:${c.bg};display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                <i data-lucide="${c.icon}" style="width:32px;height:32px;color:${c.color};"></i>
            </div>
            <span class="badge ${c.badge}" style="margin-bottom:12px;display:inline-flex;">${c.badgeText}</span>
            <h2 style="font-size:20px;font-weight:800;color:#111827;margin-bottom:8px;">${escapeHtml(c.title)}</h2>
            <p style="font-size:14px;color:#64748b;max-width:500px;margin:0 auto;line-height:1.6;">${escapeHtml(c.detail)}</p>
        </div>
    `;
    
    if (data.steps_taken) {
        html += `<div style="text-align:center;margin-top:12px;font-size:12px;color:#94a3b8;">Completed in ${data.steps_taken} step${data.steps_taken > 1 ? 's' : ''}</div>`;
    }
    
    document.getElementById('result-step').innerHTML = html;
    document.getElementById('result-step').style.display = 'block';
    
    // Show action buttons
    const actions = document.getElementById('action-buttons');
    actions.style.display = 'flex';
    
    if (type === 'solved') {
        actions.innerHTML = `
            <a href="<?= $urlBase ?>troubleshoot" class="btn btn-secondary btn-lg" style="flex:1;"><i data-lucide="arrow-left"></i> Back to Troubleshoot</a>
            <a href="<?= $urlBase ?>tickets" class="btn btn-primary btn-lg" style="flex:1;"><i data-lucide="check-circle"></i> Mark Ticket Solved</a>
        `;
    } else if (type === 'escalated') {
        actions.innerHTML = `
            <a href="<?= $urlBase ?>troubleshoot" class="btn btn-secondary btn-lg" style="flex:1;"><i data-lucide="arrow-left"></i> Back to Troubleshoot</a>
            <button class="btn btn-danger btn-lg" style="flex:1;" onclick="escalateTicket()"><i data-lucide="send"></i> Escalate to Supervisor</button>
        `;
    } else {
        actions.innerHTML = `
            <a href="<?= $urlBase ?>troubleshoot" class="btn btn-secondary btn-lg" style="flex:1;"><i data-lucide="arrow-left"></i> Back to Troubleshoot</a>
            ${data.redirect_issue ? `<a href="${APP_BASE}troubleshoot/wizard?issue=${data.redirect_issue === 'power' ? 4 : 1}" class="btn btn-primary btn-lg" style="flex:1;"><i data-lucide="arrow-right"></i> Go to ${data.redirect_issue === 'power' ? 'No Power' : 'Related'} Guide</a>` : ''}
        `;
    }
    
    lucide.createIcons();
}

function escalateTicket() {
    showToast('Ticket escalated to supervisor with full diagnostic report.', 'success');
    setTimeout(() => window.location.href = APP_BASE + 'tickets', 1500);
}

function showError(msg) {
    document.getElementById('loading-state').innerHTML = `
        <div style="text-align:center;padding:40px;">
            <i data-lucide="alert-circle" style="width:48px;height:48px;color:#dc2626;margin-bottom:16px;"></i>
            <h3 style="font-size:16px;font-weight:700;color:#111827;margin-bottom:8px;">Error</h3>
            <p style="font-size:13px;color:#64748b;">${msg}</p>
            <a href="<?= $urlBase ?>troubleshoot" class="btn btn-primary" style="margin-top:16px;">Back to Troubleshoot</a>
        </div>
    `;
    lucide.createIcons();
}

function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function showToast(msg, type) {
    const toast = document.createElement('div');
    toast.className = 'toast ' + type;
    toast.innerHTML = `<i data-lucide="${type === 'success' ? 'check-circle' : 'alert-circle'}" style="width:18px;height:18px;color:${type === 'success' ? '#16a34a' : '#dc2626'}"></i><span style="font-size:13px;">${msg}</span>`;
    document.getElementById('toast-container').appendChild(toast);
    lucide.createIcons();
    setTimeout(() => toast.remove(), 4000);
}

// Start
initWizard();
</script>
<?php require APP_ROOT . '/includes/layout_footer.php'; ?>
