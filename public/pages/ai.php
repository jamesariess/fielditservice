<?php
$page_title = 'IT Support AI';
$active_menu = 'ai';
require APP_ROOT . '/includes/layout_header.php';
?>

<div style="max-width:800px;margin:0 auto;display:flex;flex-direction:column;height:calc(100vh - 8rem);">
    <!-- Chat Container -->
    <div class="card" style="flex:1;display:flex;flex-direction:column;overflow:hidden;">
        <!-- Chat Header -->
        <div style="padding:16px 20px;border-bottom:1px solid #e5e7eb;display:flex;align-items:center;gap:12px;flex-shrink:0;">
            <div style="width:40px;height:40px;border-radius:12px;background:linear-gradient(135deg,#8b5cf6,#6d28d9);display:flex;align-items:center;justify-content:center;box-shadow:0 2px 8px rgba(139,92,246,0.3);">
                <i data-lucide="sparkles" style="width:20px;height:20px;color:#fff;"></i>
            </div>
            <div style="flex:1;">
                <h1 style="font-size:15px;font-weight:700;color:#111827;">IT Support AI</h1>
                <div style="display:flex;align-items:center;gap:6px;font-size:12px;color:#16a34a;">
                    <span style="width:6px;height:6px;border-radius:50%;background:#16a34a;display:inline-block;"></span>
                    Online — Built-in Assistant
                </div>
            </div>
            <button class="btn btn-sm btn-secondary"><i data-lucide="settings" style="width:13px;height:13px;"></i> Settings</button>
        </div>

        <!-- Messages -->
        <div id="chat-messages" style="flex:1;overflow-y:auto;padding:24px;display:flex;flex-direction:column;gap:16px;" class="custom-scroll">
            <!-- Welcome -->
            <div style="display:flex;gap:12px;">
                <div style="width:32px;height:32px;border-radius:10px;background:linear-gradient(135deg,#8b5cf6,#6d28d9);display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:2px;">
                    <i data-lucide="sparkles" style="width:14px;height:14px;color:#fff;"></i>
                </div>
                <div class="chat-bubble ai">
                    <p style="margin-bottom:10px;">Hello! I'm your <strong>IT Support AI</strong> assistant. I specialize in troubleshooting hardware, software, network, printer, and CCTV issues.</p>
                    <p style="margin-bottom:12px;font-size:12px;color:#64748b;">I can help with:</p>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:4px 16px;font-size:12.5px;color:#475569;margin-bottom:14px;">
                        <div style="display:flex;align-items:center;gap:6px;"><i data-lucide="monitor" style="width:12px;height:12px;color:#64748b;"></i> Hardware issues</div>
                        <div style="display:flex;align-items:center;gap:6px;"><i data-lucide="wifi" style="width:12px;height:12px;color:#64748b;"></i> Network problems</div>
                        <div style="display:flex;align-items:center;gap:6px;"><i data-lucide="printer" style="width:12px;height:12px;color:#64748b;"></i> Printer issues</div>
                        <div style="display:flex;align-items:center;gap:6px;"><i data-lucide="camera" style="width:12px;height:12px;color:#64748b;"></i> CCTV problems</div>
                        <div style="display:flex;align-items:center;gap:6px;"><i data-lucide="app-window" style="width:12px;height:12px;color:#64748b;"></i> Software errors</div>
                        <div style="display:flex;align-items:center;gap:6px;"><i data-lucide="terminal" style="width:12px;height:12px;color:#64748b;"></i> Windows commands</div>
                    </div>
                    <p style="font-size:12px;color:#64748b;">Describe your issue and I'll guide you through it step by step.</p>
                </div>
            </div>

            <!-- Quick Start Buttons -->
            <div style="display:flex;gap:8px;flex-wrap:wrap;padding-left:44px;">
                <button onclick="aiSendQuick('My computer has no display')" style="display:flex;align-items:center;gap:6px;padding:7px 14px;background:#eff6ff;border:1px solid transparent;border-radius:20px;font-size:12px;font-weight:600;color:#2563eb;cursor:pointer;transition:all 0.15s;" onmouseover="this.style.transform='translateY(-1px)'" onmouseout="this.style.transform=''">
                    <i data-lucide="monitor" style="width:13px;height:13px;"></i> No Display
                </button>
                <button onclick="aiSendQuick('My computer won\'t turn on')" style="display:flex;align-items:center;gap:6px;padding:7px 14px;background:#fef2f2;border:1px solid transparent;border-radius:20px;font-size:12px;font-weight:600;color:#dc2626;cursor:pointer;transition:all 0.15s;" onmouseover="this.style.transform='translateY(-1px)'" onmouseout="this.style.transform=''">
                    <i data-lucide="power" style="width:13px;height:13px;"></i> No Power
                </button>
                <button onclick="aiSendQuick('I can\'t connect to the network')" style="display:flex;align-items:center;gap:6px;padding:7px 14px;background:#f0fdf4;border:1px solid transparent;border-radius:20px;font-size:12px;font-weight:600;color:#16a34a;cursor:pointer;transition:all 0.15s;" onmouseover="this.style.transform='translateY(-1px)'" onmouseout="this.style.transform=''">
                    <i data-lucide="wifi" style="width:13px;height:13px;"></i> Network Issue
                </button>
                <button onclick="aiSendQuick('My computer is running slow')" style="display:flex;align-items:center;gap:6px;padding:7px 14px;background:#fffbeb;border:1px solid transparent;border-radius:20px;font-size:12px;font-weight:600;color:#d97706;cursor:pointer;transition:all 0.15s;" onmouseover="this.style.transform='translateY(-1px)'" onmouseout="this.style.transform=''">
                    <i data-lucide="gauge" style="width:13px;height:13px;"></i> Slow Computer
                </button>
                <button onclick="aiSendQuick('My printer is not printing')" style="display:flex;align-items:center;gap:6px;padding:7px 14px;background:#fff7ed;border:1px solid transparent;border-radius:20px;font-size:12px;font-weight:600;color:#ea580c;cursor:pointer;transition:all 0.15s;" onmouseover="this.style.transform='translateY(-1px)'" onmouseout="this.style.transform=''">
                    <i data-lucide="printer" style="width:13px;height:13px;"></i> Printer
                </button>
            </div>
        </div>

        <!-- Input -->
        <div style="padding:16px 20px;border-top:1px solid #e5e7eb;flex-shrink:0;">
            <form id="chat-form" onsubmit="aiSendMessage(event)" style="display:flex;gap:10px;align-items:flex-end;">
                <div style="flex:1;position:relative;">
                    <textarea id="chat-input" rows="1" placeholder="Describe your IT problem..."
                              style="width:100%;padding:11px 16px;border:1px solid #d1d5db;border-radius:12px;font-size:13px;resize:none;outline:none;transition:all 0.15s;font-family:inherit;line-height:1.5;max-height:120px;overflow-y:auto;"
                              class="dark-input"
                              onfocus="this.style.borderColor='#2563eb';this.style.boxShadow='0 0 0 3px rgba(37,99,235,0.1)'"
                              onblur="this.style.borderColor='#d1d5db';this.style.boxShadow=''"
                              onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();aiSendMessage(event);}"
                              oninput="this.style.height='auto';this.style.height=Math.min(this.scrollHeight,120)+'px';"></textarea>
                </div>
                <button type="submit" id="send-btn" style="width:42px;height:42px;border-radius:12px;background:#2563eb;color:#fff;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all 0.15s;flex-shrink:0;" onmouseover="this.style.background='#1d4ed8'" onmouseout="this.style.background='#2563eb'">
                    <i data-lucide="send" style="width:17px;height:17px;"></i>
                </button>
            </form>
            <div style="display:flex;justify-content:space-between;margin-top:6px;">
                <span style="font-size:10px;color:#94a3b8;">AI responses should be verified against approved procedures.</span>
                <span style="font-size:10px;color:#94a3b8;">Enter to send · Shift+Enter for new line</span>
            </div>
        </div>
    </div>
</div>

<?php require APP_ROOT . '/includes/layout_footer.php'; ?>
