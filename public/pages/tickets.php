<?php
if (!defined('APP_ROOT')) { @header('Location: /fielditservice/'); exit; }

// This route is always the signed-in user's work queue. Managers and admins
// use Ticket Management when they need to review the full team queue.
$canViewAllTickets = false;
$page_title = 'My Tickets';
$active_menu = 'tickets';
require APP_ROOT . '/includes/layout_header.php';
?>
<link rel="stylesheet" href="<?= $urlBase ?>assets/css/workspace-refresh.css?v=<?= filemtime(APP_ROOT . '/public/assets/css/workspace-refresh.css') ?>">
<script src="<?= $urlBase ?>assets/js/ticket-workspace.js?v=<?= filemtime(APP_ROOT . '/public/assets/js/ticket-workspace.js') ?>"></script>
<?php
$demo = !defined('DEMO_MODE') || DEMO_MODE;
$tickets = [];
$mfrOptions = [];   // for the equipment search typeahead (JSON-encoded into JS)
$modelOptions = [];  // same for model autocomplete
$canSearchModels = false;

// ---- manufacturer + model search data (only when DB is available) ----
$canSearchModels = false;
if (!$demo) {
    try {
        $mfrs = Database::fetchAll("SELECT id, name FROM manufacturers ORDER BY name");
        if ($mfrs) {
            $canSearchModels = true;
            foreach ($mfrs as $m) { $mfrOptions[] = ['id'=>$m['id'],'name'=>$m['name']]; }
        }
    } catch (Exception $e) {}
}
if ($canSearchModels) {
    try {
        $models = Database::fetchAll("SELECT dm.id, dm.name, m.name AS manufacturer, dt.name AS device_type
                                     FROM device_models dm
                                     LEFT JOIN manufacturers m ON dm.manufacturer_id = m.id
                                     LEFT JOIN device_types dt ON dm.device_type_id = dt.id
                                     ORDER BY m.name, dm.name");
        if ($models) { $modelOptions = $models; }
    } catch (Exception $e) {}
}

// ---- approved suggestions for shared dropdowns ----
$companyOptions = [];
$taskOptions = [];
$ticketFieldOptions = [];
$ticketCompanyContacts = [];
$profileTicketDefault = ['company'=>'', 'location'=>'', 'address'=>'', 'lat'=>'', 'lng'=>''];
if (!$demo) {
    try {
        require_once APP_ROOT . '/includes/TicketSuggestions.php';
        require_once APP_ROOT . '/includes/TicketFieldMemory.php';
        TicketSuggestions::ensure();
        TicketFieldMemory::ensure();
        $companyOptions = TicketSuggestions::approved('company');
        $taskOptions = TicketSuggestions::approved('task');
        $ticketFieldOptions = TicketFieldMemory::issueOptions();
        $ticketCompanyContacts = TicketFieldMemory::companyContacts();
        $profileRow = Database::fetch(
            "SELECT o.name AS company, l.name AS location, l.address, l.latitude AS lat, l.longitude AS lng
             FROM users u LEFT JOIN locations l ON u.location_id = l.id
             LEFT JOIN organizations o ON l.organization_id = o.id WHERE u.id = ?",
            [Auth::userId()]
        );
        if ($profileRow) { $profileTicketDefault = array_merge($profileTicketDefault, $profileRow); }
    } catch (Exception $e) {}
}
if (empty($companyOptions)) {
    $companyOptions = ['Field IT Services', 'Customer Support Operations'];
}

// ---- company + address history from past tickets ----
// Companies the team has used before, with every address recorded for each one,
// so the same company can offer several saved locations (different branches etc.)
$companyLocationData = [];   // [{company, address, lat, lng}, ...] → JSON for JS
if (!$demo) {
    try {
        $historyScope = $canViewAllTickets ? '' : ' AND user_id = ' . (int)Auth::userId();
        $histRows = Database::fetchAll(
            "SELECT company_name, location, address, latitude, longitude, MAX(started_at) AS last_used
             FROM troubleshooting_sessions
             WHERE company_name IS NOT NULL AND company_name <> ''" . $historyScope . "
             GROUP BY company_name, location, address, latitude, longitude
             ORDER BY company_name, last_used DESC
             LIMIT 500"
        ) ?: [];
        foreach ($histRows as $r) {
            $companyLocationData[] = [
                'company' => trim((string)$r['company_name']),
                'location' => trim((string)($r['location'] ?? '')),
                'address' => trim((string)($r['address'] ?? '')),
                'lat'     => trim((string)($r['latitude'] ?? '')),
                'lng'     => trim((string)($r['longitude'] ?? '')),
            ];
        }
    } catch (Exception $e) {}
}

// Shared organization locations are approved address-book entries. Load them
// once, not once per ticket card.
if (!$demo) {
    try {
        $savedLocations = Database::fetchAll(
            "SELECT o.name AS company_name, l.name AS location_name, l.address, l.latitude, l.longitude
             FROM locations l JOIN organizations o ON l.organization_id = o.id
             WHERE l.address IS NOT NULL AND l.address <> '' ORDER BY o.name, l.name"
        ) ?: [];
        foreach ($savedLocations as $r) {
            $companyLocationData[] = [
                'company' => trim((string)$r['company_name']),
                'location' => trim((string)($r['location_name'] ?? '')),
                'address' => trim((string)$r['address']),
                'lat' => trim((string)($r['latitude'] ?? '')),
                'lng' => trim((string)($r['longitude'] ?? '')),
            ];
        }
    } catch (Exception $e) {}
}


// ---- real equipment (the device/model picker is fed from the equipment table) ----
$equipmentOptions = [];
if (!$demo) {
    try {
        $equipmentOptions = Database::fetchAll(
            "SELECT id, manufacturer, model_name, device_type, serial_number, asset_tag, location, status, image_url
             FROM equipment
             WHERE deleted_at IS NULL
             ORDER BY device_type, manufacturer, model_name"
        ) ?: [];
    } catch (Exception $e) {}
}

// ---- troubleshooting issues (the Problem picker) ----
$issueOptions = [];
if (!$demo) {
    try {
        $knowledgeColumns = array_column(Database::fetchAll("SHOW COLUMNS FROM knowledge_articles"), 'Field');
        if (!in_array('troubleshooting_issue_id', $knowledgeColumns, true)) {
            Database::query("ALTER TABLE knowledge_articles ADD COLUMN troubleshooting_issue_id INT NULL AFTER id, ADD KEY idx_knowledge_troubleshooting_issue (troubleshooting_issue_id)");
        }
        $issueOptions = Database::fetchAll(
            "SELECT i.id, i.title, i.device_types, i.severity, i.description,
                    i.symptoms AS issue_symptoms, c.name AS category_name,
                    ka.id AS knowledge_id, ka.title AS knowledge_title,
                    ka.symptoms AS knowledge_symptoms, ka.root_cause
             FROM troubleshooting_issues i
             LEFT JOIN troubleshooting_categories c ON i.category_id = c.id
             LEFT JOIN knowledge_articles ka ON ka.id = (
                 SELECT linked.id FROM knowledge_articles linked
                 WHERE linked.troubleshooting_issue_id = i.id
                   AND linked.status = 'published' AND linked.deleted_at IS NULL
                 ORDER BY linked.updated_at DESC, linked.id DESC LIMIT 1
             )
             WHERE i.status = 'approved' OR i.status IS NULL
             ORDER BY c.name, i.title"
        ) ?: [];
        foreach ($issueOptions as &$issueOption) {
            $rawSymptoms = $issueOption['knowledge_symptoms'] ?: $issueOption['issue_symptoms'];
            $decodedSymptoms = json_decode((string)$rawSymptoms, true);
            $issueOption['symptom_list'] = is_array($decodedSymptoms)
                ? array_values(array_filter(array_map('trim', $decodedSymptoms)))
                : array_values(array_filter(array_map('trim', preg_split('/[,;|]+/', (string)$rawSymptoms))));
        }
        unset($issueOption);
    } catch (Exception $e) {}
}

// ---- tickets (real data from troubleshooting_sessions) ----
if (!$demo) {
    try {
        $ticketScope = $canViewAllTickets ? '' : ' WHERE ts.user_id = ?';
        $ticketParams = $canViewAllTickets ? [] : [Auth::userId()];
        $tickets = Database::fetchAll(
            "SELECT ts.*, i.title as issue_title, i.slug as issue_slug, c.name as category_name,
                    owner.full_name AS owner_name, owner.email AS owner_email
             FROM troubleshooting_sessions ts
             LEFT JOIN troubleshooting_issues i ON ts.issue_id = i.id
             LEFT JOIN troubleshooting_categories c ON i.category_id = c.id
             LEFT JOIN users owner ON owner.id = ts.user_id" . $ticketScope . "
             ORDER BY ts.id DESC",
            $ticketParams
        );
    } catch (Exception $e) {}
}

// Fallback demo data only when there are genuinely no rows (fresh install)
if ($demo && empty($tickets)) {
    $tickets = [
        ['id'=>1,'ticket_number'=>'TK-1001','problem_description'=>'Camera and microphone not working. Replaced camera and mic. All passed.','status'=>'solved','priority'=>'high','category_name'=>'Display','manufacturer'=>'Lenovo','model'=>'ThinkPad T14 Gen 3','serial_number'=>'PW07MWVE','department'=>'Operations','location'=>'Floor 3, Desk 42','customer_name'=>'Rica Pagulayan','started_at'=>'2026-09-18 16:30:00','ended_at'=>'2026-09-18 17:10:00','resolution'=>'Replaced camera and mic. Laptop camera and mic now working. Run LDT all passed. Test camera and mic working good. Boot to Windows.','resolution_type'=>'completed','parts_replaced'=>'Camera, Microphone','tools_used'=>'Precision screwdriver, ESD strap','steps_performed'=>'upon checking camera and mic is not working. Update drivers and Lenovo Vantage still same issue. Replaced camera and mic.','time_spent_minutes'=>40,'address'=>'1 Aviation Ground Handling Services Corporation, Pasay City','issue_slug'=>'no-display','issue_title'=>'No Display'],
        ['id'=>2,'ticket_number'=>'TK-1002','problem_description'=>'Battery life at 64%, needs replacement','status'=>'in_progress','priority'=>'medium','category_name'=>'Hardware','manufacturer'=>'Lenovo','model'=>'ThinkPad X1 Carbon Gen 9','serial_number'=>'PF4BCHJT','department'=>'Field IT','location'=>'IBM Eastwood','customer_name'=>'Glenn','started_at'=>'2026-09-18 13:15:00','ended_at'=>null,'resolution'=>null,'resolution_type'=>null,'parts_replaced'=>null,'tools_used'=>null,'steps_performed'=>null,'time_spent_minutes'=>null,'address'=>null,'issue_slug'=>'no-display','issue_title'=>'No Display'],
        ['id'=>3,'ticket_number'=>'TK-1003','problem_description'=>'Printer offline, not responding','status'=>'escalated','priority'=>'high','category_name'=>'Printer','manufacturer'=>'HP','model'=>'LaserJet Pro M404','serial_number'=>'HPLCJ404X','department'=>'Reception','location'=>'Ground Floor, Reception','customer_name'=>'Reception Desk','started_at'=>'2026-08-31 11:01:00','ended_at'=>null,'resolution'=>null,'resolution_type'=>null,'parts_replaced'=>null,'tools_used'=>null,'steps_performed'=>null,'time_spent_minutes'=>null,'address'=>null,'issue_slug'=>'printer-offline','issue_title'=>'Printer Offline'],
    ];
}

$total = count($tickets);
$solved = 0; $inProgress = 0; $escalated = 0; $newCount = 0;
foreach ($tickets as $t) {
    if ($t['status'] === 'solved' || $t['status'] === 'partial') $solved++;
    elseif ($t['status'] === 'in_progress') $inProgress++;
    elseif ($t['status'] === 'escalated') $escalated++;
    else $newCount++;
}
?>

<div id="new-ticket-modal" class="modal-overlay" style="display:none;">
    <div class="backdrop" onclick="closeModal('new-ticket-modal')"></div>
    <div id="new-ticket-panel" class="modal-panel" style="max-width:560px;background:#fff;border-radius:16px;z-index:10002;box-shadow:0 25px 60px rgba(0,0,0,0.3);max-height:90vh;overflow-y:auto;">
        <div style="padding:20px 24px;border-bottom:1px solid #e5e7eb;display:flex;justify-content:space-between;align-items:center;">
            <h2 style="font-size:18px;font-weight:700;color:#111827;margin:0;">New Ticket</h2>
            <button onclick="closeModal('new-ticket-modal')" style="background:none;border:none;cursor:pointer;color:#94a3b8;font-size:20px;line-height:1;">&#10005;</button>
        </div>
        <div id="new-ticket-body" style="padding:20px 24px;">
            <!-- ============ Step 1: Ticket + Company + Task + Device first ============ -->
            <div id="step-device">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px;">
                    <div><label style="display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:4px;">Ticket No. *</label>
                        <div class="tt-ticket-number-wrap"><span>SD</span><input id="tt-ticket-no" type="text" inputmode="numeric" pattern="[0-9]*" maxlength="24" placeholder="25646" oninput="this.value=this.value.replace(/[^0-9]/g,'')" class="form-input dark-input" style="width:100%;padding:10px 14px 10px 42px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;font-weight:600;"></div></div>
                    <div><label style="display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:4px;">Serial Number</label>
                        <input id="tt-serial" placeholder="e.g. PW07MWVE" class="form-input dark-input" style="width:100%;padding:10px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;"></div>
                </div>

                <div style="margin-bottom:14px;">
                    <label style="display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:4px;">Company Name *</label>
                    <select id="tt-company" class="form-input dark-input" style="width:100%;padding:10px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;">
                        <option value="">— Select company —</option>
                        <?php foreach ($companyOptions as $company): ?>
                            <option value="<?= e($company) ?>"><?= e($company) ?></option>
                        <?php endforeach; ?>
                        <option value="__OTHER__">Other — type a new company…</option>
                    </select>
                    <input id="tt-company-other" placeholder="Type new company name" class="form-input dark-input" style="display:none;width:100%;margin-top:8px;padding:10px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;" autocomplete="off">
                    <div id="tt-company-hint" style="display:none;font-size:11px;color:#64748b;margin-top:6px;"></div>
                </div>

                <div style="margin-bottom:14px;">
                    <label style="display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:4px;">Task *</label>
                    <select id="tt-task" class="form-input dark-input" style="width:100%;padding:10px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;">
                        <option value="">— Select task —</option>
                        <?php foreach ($taskOptions as $task): ?>
                            <option value="<?= e($task) ?>"><?= e($task) ?></option>
                        <?php endforeach; ?>
                        <option value="__OTHER__">Other — type a new task…</option>
                    </select>
                    <input id="tt-task-other" placeholder="Type new task" class="form-input dark-input" style="display:none;width:100%;margin-top:8px;padding:10px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;" autocomplete="off">
                    <div id="tt-task-hint" style="display:none;font-size:11px;color:#64748b;margin-top:6px;"></div>
                </div>
                <div style="margin-bottom:14px;">
                    <label style="display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:4px;">Device * <span style="font-weight:500;color:#94a3b8;">(pick first — filters the Problem list)</span></label>
                    <select id="tt-device" class="form-input dark-input" style="width:100%;padding:10px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;">
                        <option value="">— Select device —</option>
                    </select>
                    <input id="tt-device-other" placeholder="Type the device (e.g. Laptop, Printer, CCTV)" class="form-input dark-input" style="display:none;width:100%;margin-top:8px;padding:10px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;">
                </div>
                <div style="margin-bottom:14px;">
                    <label style="display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:4px;">Device / Model <span style="font-weight:500;color:#94a3b8;">(from Equipment)</span></label>
                    <div style="position:relative;">
                        <i data-lucide="search" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);width:15px;height:15px;color:#94a3b8;"></i>
                        <input id="tt-equip-search" placeholder="Search equipment — model, serial or manufacturer" class="form-input dark-input" style="width:100%;padding:10px 14px 10px 36px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;">
                    </div>
                    <div id="tt-equip-results" style="display:none;margin-top:8px;border:1px solid #e5e7eb;border-radius:10px;max-height:200px;overflow-y:auto;background:#fff;box-shadow:0 4px 12px rgba(0,0,0,0.06);"></div>
                    <div id="tt-selected-equip" style="display:none;margin-top:10px;padding:12px;background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;">
                        <div style="display:flex;align-items:flex-start;gap:8px;">
                            <div style="flex:1;min-width:0;">
                                <div style="font-size:13px;font-weight:600;color:#1d4ed8;" id="tt-selected-name"></div>
                                <div style="font-size:12px;color:#64748b;margin-top:4px;">
                                    <span id="tt-selected-mfr"></span> · <span id="tt-selected-type"></span>
                                </div>
                                <div style="margin-top:8px;display:flex;gap:8px;flex-wrap:wrap;">
                                    <span class="badge badge-blue" id="tt-selected-serial-badge" style="display:none;">SN: <span id="tt-selected-serial"></span></span>
                                    <span class="badge badge-gray" id="tt-selected-asset-badge" style="display:none;">Asset: <span id="tt-selected-asset"></span></span>
                                    <a id="tt-selected-manual" href="" target="_blank" style="font-size:12px;color:#2563eb;text-decoration:underline;display:none;">Service manual</a>
                                </div>
                            </div>
                            <button type="button" onclick="ticketClearEquipSelection()" title="Clear selection" style="background:none;border:none;color:#94a3b8;cursor:pointer;font-size:18px;line-height:1;">&times;</button>
                        </div>
                    </div>
                    <input id="tt-model-search" placeholder="Or type the model manually" class="form-input dark-input" style="width:100%;margin-top:8px;padding:9px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:12px;">
                </div>
                <input type="hidden" id="tt-device-type">
                <input type="hidden" id="tt-title">
                <input type="hidden" id="tt-description">
                <input type="hidden" id="tt-manufacturer-search">

                <button type="button" id="tt-next-1" class="btn btn-primary" style="width:100%;margin-top:6px;">Next: Problem &amp; Location</button>
            </div>

            <!-- ============ Step 2: Problem (troubleshooting) + Note + Address ============ -->
            <div id="step-problem-loc" style="display:none;">
                <div style="border-bottom:1px solid #e5e7eb;padding-bottom:12px;margin-bottom:14px;">
                    <button type="button" onclick="ticketStepBack()" class="btn btn-ghost btn-sm" style="margin-bottom:4px;">&#8592; Back</button>
                    <div style="font-size:12px;color:#94a3b8;font-weight:500;">Step 2 of 2 — Problem &amp; Location</div>
                </div>

                <div style="margin-bottom:14px;">
                    <label style="display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:4px;">Problem <span style="font-weight:500;color:#94a3b8;">(from troubleshooting)</span></label>
                    <select id="tt-issue" class="form-input dark-input" style="width:100%;padding:10px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;">
                        <option value="">— Select a known problem —</option>
                    </select>
                    <textarea id="tt-issue-custom" rows="2" placeholder="Describe the problem in your own words (required if you picked “Other / not listed”)" class="form-input dark-input" style="display:none;width:100%;margin-top:8px;padding:10px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;resize:vertical;"></textarea>
                    <div id="tt-issue-hint" style="display:none;font-size:11px;color:#94a3b8;margin-top:6px;"></div>
                    <div id="tt-problem-insight" class="tt-problem-insight" style="display:none;">
                        <div class="tt-insight-head"><i data-lucide="scan-search"></i><span>What to look for</span><a id="tt-insight-kb-link" href="#" target="_blank" rel="noopener" style="display:none;">Open guide</a></div>
                        <div class="tt-insight-block"><span class="tt-insight-label">Common symptoms</span><div id="tt-insight-symptoms" class="tt-insight-chips"></div></div>
                        <div class="tt-insight-block"><span class="tt-insight-label">Common cause</span><p id="tt-insight-cause"></p></div>
                    </div>
                </div>

                <div style="margin-bottom:14px;">
                    <label style="display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:4px;">Note</label>
                    <textarea id="tt-note" rows="2" placeholder="Any extra note for this ticket (optional)" class="form-input dark-input" style="width:100%;padding:10px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;resize:vertical;"></textarea>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px;">
                    <div><label style="display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:4px;">Priority *</label>
                        <select id="tt-priority" required class="form-input dark-input" style="width:100%;padding:10px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;">
                            <option value="low">Low</option><option value="medium" selected>Medium</option><option value="high">High</option><option value="critical">Critical</option>
                        </select></div>
                    <div><label style="display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:4px;">Customer Name</label>
                        <input id="tt-customer" placeholder="e.g. John Smith" class="form-input dark-input" style="width:100%;padding:10px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;"></div>
                </div>
                <div style="margin-bottom:14px;">
                    <label style="display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:4px;">Location</label>
                    <select id="tt-location-select" class="form-input dark-input" style="width:100%;padding:10px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;"></select>
                    <input id="tt-location" placeholder="Type a new location, e.g. Floor 3, Room 301" class="form-input dark-input" style="display:none;width:100%;padding:10px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;margin-top:7px;">
                </div>
                <!-- Map -->
                <div style="margin-bottom:6px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
                        <label style="font-size:12px;font-weight:600;color:#374151;margin:0;">Address (tap to set from map)</label>
                        <div style="display:flex;gap:6px;">
                            <button type="button" id="tt-map-locate" class="btn btn-sm btn-secondary" style="font-size:11px;">Use my location</button>
                            <button type="button" id="tt-map-clear" class="btn btn-sm btn-ghost" style="font-size:11px;color:#94a3b8;">Clear</button>
                        </div>
                    </div>
                    <div id="tt-map" style="height:180px;border:1px solid #e5e7eb;border-radius:10px;overflow:hidden;background:#eef2f7;"></div>
                    <input id="tt-address" list="tt-address-list" placeholder="Pick a saved address for this company — or type a new one" class="form-input dark-input" style="width:100%;padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:12px;margin-top:6px;" autocomplete="off">
                    <datalist id="tt-address-list"></datalist>
                    <input id="tt-lat" type="hidden">
                    <input id="tt-lng" type="hidden">
                </div>

                <div style="display:flex;gap:8px;justify-content:space-between;margin-top:6px;">
                    <button type="button" onclick="ticketStepBack()" class="btn btn-secondary">Back</button>
                    <button type="button" id="tt-next-2" class="btn btn-primary">Create Ticket</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="tickets-page">
    <div class="page-hero tickets-hero fx-reveal">
        <div>
            <div style="display:flex;align-items:center;gap:14px;">
                <div class="page-hero-ico blue"><i data-lucide="ticket"></i></div>
                <div>
                    <h1 class="page-hero-title"><?= $canViewAllTickets ? 'Team Tickets' : 'My Tickets' ?></h1>
                    <p class="page-hero-sub"><?= $canViewAllTickets ? 'Review the complete field queue; each technician keeps control of their own workflow.' : 'Create and manage only the field tickets assigned to you.' ?></p>
                </div>
            </div>
        </div>
        <div class="page-hero-actions">
            <button onclick="openNewTicketModal()" class="btn btn-primary"><i data-lucide="plus" style="width:16px;height:16px;"></i> New Ticket</button>
        </div>
    </div>
    <div class="ft-stats">
        <div class="card"><div class="card-body" style="text-align:center;padding:14px 10px;">
            <div class="ft-stat-ico" style="background:#eff6ff;"><i data-lucide="ticket" style="color:#2563eb;"></i></div>
            <div class="ft-stat-num" style="color:#2563eb;"><?= $total ?></div>
            <div class="ft-stat-lbl">Total Tickets</div>
            <span class="ft-dot" style="background:#2563eb;"></span>
        </div></div>
        <div class="card"><div class="card-body" style="text-align:center;padding:14px 10px;">
            <div class="ft-stat-ico" style="background:#fffbeb;"><i data-lucide="plus-circle" style="color:#d97706;"></i></div>
            <div class="ft-stat-num" style="color:#d97706;"><?= $newCount ?></div>
            <div class="ft-stat-lbl">New</div>
            <span class="ft-dot" style="background:#d97706;"></span>
        </div></div>
        <div class="card"><div class="card-body" style="text-align:center;padding:14px 10px;">
            <div class="ft-stat-ico" style="background:#eff6ff;"><i data-lucide="loader" style="color:#2563eb;"></i></div>
            <div class="ft-stat-num" style="color:#2563eb;"><?= $inProgress ?></div>
            <div class="ft-stat-lbl">In Progress</div>
            <span class="ft-dot" style="background:#2563eb;"></span>
        </div></div>
        <div class="card"><div class="card-body" style="text-align:center;padding:14px 10px;">
            <div class="ft-stat-ico" style="background:#f0fdf4;"><i data-lucide="check-circle" style="color:#16a34a;"></i></div>
            <div class="ft-stat-num" style="color:#16a34a;"><?= $solved ?></div>
            <div class="ft-stat-lbl">Solved</div>
            <span class="ft-dot" style="background:#16a34a;"></span>
        </div></div>
        <div class="card"><div class="card-body" style="text-align:center;padding:14px 10px;">
            <div class="ft-stat-ico" style="background:#fef2f2;"><i data-lucide="alert-triangle" style="color:#dc2626;"></i></div>
            <div class="ft-stat-num" style="color:#dc2626;"><?= $escalated ?></div>
            <div class="ft-stat-lbl">Escalated</div>
            <span class="ft-dot" style="background:#dc2626;"></span>
        </div></div>
    </div>
    <div class="tickets-toolbar">
        <div class="tickets-filter-row">
            <button onclick="ticketFilter('new')" class="btn btn-sm filter-btn active" data-filter="new">New (<?= $newCount ?>)</button>
            <button onclick="ticketFilter('in_progress')" class="btn btn-sm btn-secondary filter-btn" data-filter="in_progress">In Progress (<?= $inProgress ?>)</button>
            <button onclick="ticketFilter('solved')" class="btn btn-sm btn-secondary filter-btn" data-filter="solved">Solved (<?= $solved ?>)</button>
            <button onclick="ticketFilter('escalated')" class="btn btn-sm btn-secondary filter-btn" data-filter="escalated">Escalated (<?= $escalated ?>)</button>
        </div>
        <div class="tickets-search-wrap">
            <i data-lucide="search" class="ft-search-ico"></i>
            <input id="ticket-search" oninput="ticketApplyFilters()" placeholder="Search company, ticket #, serial, device, problem..." class="form-input" style="width:100%;padding:8px 12px 8px 32px;font-size:13px;border-radius:10px;">
        </div>
        <select id="ticket-sort" onchange="ticketApplyFilters()" class="form-input tickets-sort">
            <option value="nearest" <?= ($profileTicketDefault['lat'] !== '' && $profileTicketDefault['lng'] !== '') ? 'selected' : '' ?>>Nearest first</option>
            <option value="newest">Newest</option>
            <option value="oldest">Oldest</option>
            <option value="updated">Recently Updated</option>
        </select>
        <button type="button" class="ticket-copy-list" onclick="ticketCopyNewList()" title="Copy new ticket numbers and company names" aria-label="Copy <?= (int)$newCount ?> new ticket numbers and company names"><i data-lucide="clipboard-copy" aria-hidden="true"></i></button>
        <div class="tickets-view-toggle" id="tickets-view-toggle" aria-label="Ticket view">
            <button type="button" data-view="cards" class="active" aria-label="Card view" title="Card view"><i data-lucide="layout-grid"></i></button>
            <button type="button" data-view="table" aria-label="Table view" title="Table view"><i data-lucide="list"></i></button>
        </div>
    </div>
    <div class="tickets-table-wrap" aria-live="polite">
        <table class="tickets-table">
            <thead><tr><th>Ticket</th><th>Company / Site</th><th>Issue</th><th>Device</th><th>Assigned to</th><th>Priority</th><th>Status</th><th>Created</th><th>Action</th></tr></thead>
            <tbody id="tickets-table-body"></tbody>
        </table>
        <div class="ticket-empty" id="ticket-table-empty" hidden>
            <span><i data-lucide="search-x"></i></span>
            <h2>No matching tickets</h2>
            <p>Try a different status or clear your search.</p>
            <button type="button" class="btn btn-secondary btn-sm" onclick="ticketClearFilters()">Clear filters</button>
        </div>
    </div>
    <div class="tickets-grid" id="tickets-grid">
    <?php foreach ($tickets as $t):
        $ticketId      = $t['id'];
        $issueId       = (int)($t['issue_id'] ?? 0);
        $ticketNum     = $t['ticket_number'] ?? ('SD' . $t['id']);
        if (preg_match('/^(?:SD|TK-)?(\d+)$/i', (string)$ticketNum, $ticketNumberMatch)) {
            $ticketNum = 'SD' . $ticketNumberMatch[1];
        }
        $status        = $t['status'] ?? 'new';
        $priority      = $t['priority'] ?? 'medium';
        $problem       = $t['problem_description'] ?? '';
        $model         = $t['model'] ?? '';
        $modelName     = $model;
        $manufacturer  = $t['manufacturer'] ?? '';
        $serial        = $t['serial_number'] ?? '';
        $department    = $t['department'] ?? '';
        $location      = $t['location'] ?? '';
        $customerName  = $t['customer_name'] ?? '';
        $companyName   = $t['company_name'] ?? '';
        $taskText      = $t['task'] ?? '';
        $ticketNote    = $t['notes'] ?? '';
        $deviceTypeVal = $t['device_type'] ?? '';
        $startTime     = $t['started_at'] ?? '';
        $createdAt     = $t['created_at'] ?? $startTime;
        $endTime       = $t['ended_at'] ?? '';
        $resolution    = $t['resolution'] ?? '';
        $resultOfChecking = $t['result_of_checking'] ?? '';
        $recommendation = $t['recommendation'] ?? '';
        $confirmedBy = $t['confirmed_by'] ?? '';
        $resolutionType= $t['resolution_type'] ?? '';
        $partsReplaced = $t['parts_replaced'] ?? '';
        $toolsUsed     = $t['tools_used'] ?? '';
        // Routing helper fields (hydrated by the time-out response, or read from DB)
        $nextRouteAddr  = $t['next_route_addr'] ?? '';
        $nextRouteTicket = $t['next_route_ticket'] ?? '';
        $timeSpent     = $t['time_spent_minutes'] ?? null;
        $notes         = $t['steps_performed'] ?? '';
        $address       = $t['address'] ?? '';
        $latitude      = $t['latitude'] ?? '';
        $longitude     = $t['longitude'] ?? '';
        $stepsList = [];
        if ($notes && is_string($notes)) {
            $decoded = json_decode($notes, true);
            if (is_array($decoded)) {
                $stepsList = array_values(array_filter(array_map('trim', $decoded), fn($s) => $s !== ''));
            } elseif (is_string($decoded) && trim($decoded) !== '') {
                $stepsList = [trim($decoded)];
            } elseif (trim($notes) !== '' && $notes !== '[]') {
                $stepsList = [trim($notes)];
            }
        } elseif (is_array($notes)) {
            $stepsList = array_values(array_filter(array_map('trim', $notes), fn($s) => $s !== ''));
        }
        // Human-readable title: model is the hero; fall back to the problem text
        $title = $model ? $model : (($t['issue_title'] ?? $t['issue_slug'] ?? '') . ($problem ? ' — ' . $problem : ''));
        $statusColor = $status === 'solved' ? '#16a34a' : ($status === 'escalated' ? '#dc2626' : ($status === 'in_progress' ? '#2563eb' : '#d97706'));
        $statusBg    = $status === 'solved' ? '#f0fdf4' : ($status === 'escalated' ? '#fef2f2' : ($status === 'in_progress' ? '#eff6ff' : '#fffbeb'));
        $priorityColor = $priority === 'high' ? '#dc2626' : ($priority === 'low' ? '#16a34a' : '#d97706');
        $timeAgo = $startTime ? date('M d, g:i A', strtotime($startTime)) : '—';
        $assignee = $customerName ?: 'You';
        $deviceLine = $manufacturer ? $manufacturer . ($model ? ' ' . $model : '') : $model;
        $hasEndTime = $endTime && $status !== 'new' && $status !== 'in_progress';
        $isOpenSession = ($status === 'in_progress' || $status === 'new') && $startTime && !$endTime;
    ?>
    <?php
    // ---- Derived card values (sections are rendered ONLY when the data exists) ----
    $createdTs  = $createdAt ? strtotime($createdAt) : 0;
    $updatedTs  = max($createdTs, $endTime ? strtotime($endTime) : 0);
    $deviceName = trim(trim((string)$manufacturer) . ' ' . trim((string)$model));
    $issueText  = $problem ?: ($taskText ?: $title);
    $stepsCount = count($stepsList);
    $issueLong  = mb_strlen($issueText) > 110;
    $ownerName = trim((string)($t['owner_name'] ?? '')) ?: 'Unknown user';
    $isOwner = (int)($t['user_id'] ?? 0) === (int)Auth::userId();
    $searchBlob = mb_strtolower($ticketNum . ' ' . $companyName . ' ' . $ownerName . ' ' . $customerName . ' ' . $serial . ' ' . $deviceName . ' ' . $deviceTypeVal . ' ' . $issueText . ' ' . $taskText);
    // Full ticket data for the drawer (real DB fields only — nothing invented)
    $reportData = [
        'id'                  => (int)$ticketId,
        'issue_id'            => (int)($t['issue_id'] ?? 0),
        'ticket_number'       => $ticketNum,
        'company_name'        => $companyName,
        'customer_name'       => $customerName,
        'serial_number'       => $serial,
        'device_type'         => $deviceTypeVal,
        'model'               => $model,
        'manufacturer'        => $manufacturer,
        'problem_description' => $problem,
        'issue_title'         => $t['issue_title'] ?? '',
        'task'                => $taskText,
        'title'               => $title,
        'started_at'          => $startTime,
        'created_at'          => $createdAt,
        'ended_at'            => $endTime,
        'resolution'          => $resolution,
        'result_of_checking'  => $resultOfChecking,
        'recommendation'      => $recommendation,
        'confirmed_by'        => $confirmedBy,
        'status'              => $status,
        'priority'            => $priority,
        'location'            => $location,
        'address'             => $address,
        'latitude'            => $latitude,
        'longitude'           => $longitude,
        'steps'               => $stepsList,
        'parts_replaced'      => $partsReplaced,
        'tools_used'          => $toolsUsed,
        'time_spent_minutes'  => $timeSpent,
        'owner_name'          => $ownerName,
        'can_edit'            => $isOwner,
    ];
    ?>
    <script>window.ttTicketData = window.ttTicketData || {}; window.ttTicketData[<?= (int)$ticketId ?>] = <?= json_encode($reportData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;</script>
    <article class="card ft-ticket-card" data-id="<?= (int)$ticketId ?>" data-status="<?= e($status) ?>" data-owner="<?= e($ownerName) ?>" data-created="<?= $createdTs ?>" data-updated="<?= $updatedTs ?>" data-lat="<?= e($latitude) ?>" data-lng="<?= e($longitude) ?>" data-address="<?= e($address ?: $location) ?>" data-search="<?= e($searchBlob) ?>">
        <!-- Header: company + ticket # | status -->
        <div class="ft-card-head">
            <div class="ft-card-identity">
                <div class="ft-co-ico"><i data-lucide="building-2"></i></div>
                <div style="min-width:0;">
                    <div class="ft-company"><?= $companyName !== '' ? e($companyName) : 'Company not specified' ?></div>
                     <div class="ft-tnum">Ticket #<?= e($ticketNum) ?></div>
                    <?php if ($canViewAllTickets): ?><div class="ft-owner"><i data-lucide="user-round"></i><?= e($ownerName) ?><?= $isOwner ? ' (You)' : '' ?></div><?php endif; ?>
                </div>
            </div>
            <span class="badge" style="background:<?= $statusBg ?>;color:<?= $statusColor ?>;flex-shrink:0;"><?= e(ucwords(str_replace('_',' ',$status))) ?></span>
        </div>
        <!-- Device information (only fields that actually exist) -->
        <?php if ($deviceName || $serial || $deviceTypeVal): ?>
        <div class="ft-section ft-device-block">
            <?php if ($deviceName): ?>
                <div class="ft-label">Device</div>
                <div class="ft-value"><?= e($deviceName) ?></div>
            <?php endif; ?>
            <?php if ($deviceTypeVal): ?>
                <div style="font-size:11px;color:#64748b;margin-top:2px;"><?= e($deviceTypeVal) ?></div>
            <?php endif; ?>
            <?php if ($serial): ?>
                <div class="ft-label" style="margin-top:10px;">Serial Number</div>
                <div class="ft-value"><?= e($serial) ?></div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        <div class="ft-divider"></div>
        <!-- Issue / Task -->
        <div class="ft-section ft-issue-block" style="margin-top:0;">
            <div class="ft-label">Issue / Task</div>
            <div class="ft-value ft-issue<?= $issueLong ? ' ft-clamped' : '' ?>" id="issue-<?= (int)$ticketId ?>"><?= e($issueText) ?></div>
            <?php if ($issueLong): ?>
                <button type="button" class="ft-more" onclick="ticketToggleIssue(<?= (int)$ticketId ?>, this)">View more</button>
            <?php endif; ?>
        </div>
        <?php if ($status === 'in_progress' && $stepsCount > 0): ?>
            <div class="ft-progress"><i data-lucide="list-checks"></i> <?= (int)$stepsCount ?> troubleshooting step<?= $stepsCount > 1 ? 's' : '' ?> logged</div>
        <?php endif; ?>
        <?php if ($status === 'solved' && $resolution): ?>
            <div class="ft-section" style="margin-top:12px;">
                <div class="ft-label">Resolution</div>
                <div class="ft-value ft-clamp2"><?= e($resolution) ?></div>
            </div>
        <?php endif; ?>
        <div class="ft-travel" id="ticket-travel-<?= (int)$ticketId ?>">
            <div class="ft-travel-main">
                <span class="ft-travel-icon"><i data-lucide="navigation"></i></span>
                <span><strong class="ft-travel-distance"><?= ($latitude !== '' && $longitude !== '') ? 'Calculating...' : 'Location needed' ?></strong><small class="ft-travel-address"><?= e($address ?: ($location ?: 'Add an address and map pin to calculate travel.')) ?></small></span>
            </div>
            <span class="ft-travel-eta"><?= ($latitude !== '' && $longitude !== '') ? 'ETA --' : 'No ETA' ?></span>
        </div>
        <div class="ft-spacer"></div>
        <!-- Footer: created date + next action -->
        <div class="ft-footer">
            <div>
                <div class="ft-label">Created</div>
                <div class="ft-value"><?= $createdAt ? date('m/d/Y', $createdTs) : '—' ?></div>
            </div>
            <button class="btn btn-sm btn-primary" onclick="openTicketDrawer(<?= (int)$ticketId ?>)"><i data-lucide="<?= $isOwner ? 'arrow-up-right' : 'eye' ?>"></i>
                <?php
                if (!$isOwner) { echo 'View Ticket'; }
                elseif ($status === 'new') { echo 'Open Ticket'; }
                elseif ($status === 'in_progress') { echo 'Continue Troubleshooting'; }
                elseif ($status === 'solved') { echo 'View Report'; }
                else { echo 'View Ticket'; }
                ?>
            </button>
        </div>

    <!-- Ticket card: on-site checklist + quick field-guide access (only when this ticket has an issue) -->
    <?php if (!empty($issueId)): ?>
    <?php
        // Pull the real guide bits for this issue (tools, videos, tips) server-side so the
        // card can show them immediately without a separate JS fetch.
        $cardGuide = ['tools'=>[], 'videos'=>[], 'tips'=>[], 'step_count'=>0, 'estimated_time'=>''];
        if (!empty($issueId)) {
            try {
                $issueRow = Database::fetch(
                    "SELECT i.tools_needed, i.safety_warnings, i.estimated_time
                     FROM troubleshooting_issues i WHERE i.id = ?",
                    [$issueId]
                );
                if ($issueRow) {
                    $cardGuide['estimated_time'] = (string)($issueRow['estimated_time'] ?? '');
                    $t = json_decode((string)($issueRow['tools_needed'] ?? ''), true);
                    if (is_array($t)) { foreach ($t as $x) { $x = trim((string)$x); if ($x !== '') $cardGuide['tools'][] = $x; } }
                    elseif (trim((string)($issueRow['tools_needed'] ?? '')) !== '') {
                        foreach (preg_split('/[,;\r\n]+/', (string)$issueRow['tools_needed']) as $x) {
                            $x = trim($x); if ($x !== '') $cardGuide['tools'][] = $x;
                        }
                    }
                    $w = json_decode((string)($issueRow['safety_warnings'] ?? ''), true);
                    if (is_array($w)) { foreach ($w as $x) { $x = trim((string)$x); if ($x !== '' && stripos($x,'none') !== 0) $cardGuide['tips'][] = $x; } }
                }
                $stepRows = Database::fetchAll(
                    "SELECT step_number, title, risk_level, media_url FROM troubleshooting_steps WHERE issue_id = ? ORDER BY step_number ASC",
                    [$issueId]
                ) ?: [];
                foreach ($stepRows as $sr) {
                    $cardGuide['step_count'] = (int)$cardGuide['step_count'] + 1;
                    $cardGuide['videos'][] = [
                        'label' => 'Step ' . $sr['step_number'] . ' — ' . (string)$sr['title'],
                        'url'   => (string)($sr['media_url'] ?? ''),
                    ];
                }
            } catch (Exception $e) {}
        }
        // Merge model-level service manual + known tools into the same list.
        if (!empty($modelName) && !empty($manufacturer)) {
            try {
                $dm = Database::fetch(
                    "SELECT dm.service_manual_url, dm.required_tools, dm.known_issues
                     FROM device_models dm
                     LEFT JOIN manufacturers m ON dm.manufacturer_id = m.id
                     WHERE dm.name = ? AND m.name = ?
                     LIMIT 1",
                    [$modelName, $manufacturer]
                );
                if ($dm) {
                    if (!empty($dm['service_manual_url'])) {
                        $cardGuide['videos'][] = ['label' => 'Service manual — ' . $modelName, 'url' => (string)$dm['service_manual_url']];
                    }
                    $rawTools = (string)($dm['required_tools'] ?? '');
                    $t = json_decode($rawTools, true);
                    if (!is_array($t) && trim($rawTools) !== '') { $t = preg_split('/[,;\r\n]+/', $rawTools); }
                    if (is_array($t)) { foreach ($t as $x) { $x = trim((string)$x); if ($x !== '' && !in_array($x, $cardGuide['tools'], true)) $cardGuide['tools'][] = $x; } }
                    foreach (preg_split('/\\r?\\n/', (string)($dm['known_issues'] ?? '')) as $k) {
                        $k = trim($k);
                        if ($k !== '' && !in_array($k, $cardGuide['tips'], true)) $cardGuide['tips'][] = $k;
                    }
                }
            } catch (Exception $e) {}
        }
        $cardGuideJson = json_encode($cardGuide);
    ?>
        <div class="ft-card-guide" id="fcg-<?= (int)$ticketId ?>">
        <!-- Collapsible on-site checklist -->
        <?php if ($isOwner): ?>
        <div class="ft-card-guide-section">
            <button type="button" class="ft-card-guide-toggle" id="fcgt-<?= (int)$ticketId ?>" onclick="ttCardGuideToggle(<?= (int)$ticketId ?>, this)">
                <i data-lucide="check-square" class="ft-card-guide-toggle-icon" style="width:13px;height:13px;"></i>
                <span class="ft-card-guide-toggle-label">Steps done</span>
                <span class="ft-card-guide-toggle-count" id="ftgc-<?= (int)$ticketId ?>">0</span>
                <i data-lucide="chevron-down" class="ft-card-guide-toggle-chevron" style="width:12px;height:12px;"></i>
            </button>
            <div class="ft-card-guide-body" id="fcgb-<?= (int)$ticketId ?>" style="display:none;">
                <div class="ft-card-guide-suggest" id="fcgs-<?= (int)$ticketId ?>"></div>
                <div class="ft-card-guide-done" id="fcgd-<?= (int)$ticketId ?>"></div>
                <div class="ft-card-guide-add">
                    <input id="fcga-<?= (int)$ticketId ?>" class="form-input" placeholder="Add what you did on site..." style="font-size:12px;padding:6px 9px;border-radius:8px;">
                    <button type="button" class="btn btn-sm btn-secondary" onclick="ttCardGuideAdd(<?= (int)$ticketId ?>)" style="padding:4px 10px;font-size:11px;">Add</button>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <!-- Quick access: tools, manual, videos, tips -->
        <?php if (!empty($cardGuide['tools']) || !empty($cardGuide['videos']) || !empty($cardGuide['tips'])): ?>
        <button type="button" class="ft-card-guide-quick" id="fcgq-<?= (int)$ticketId ?>" onclick="ttCardGuideQuickOpen(<?= (int)$ticketId ?>, <?= $cardGuideJson ?>, this)">
            <i data-lucide="book-open" style="width:12px;height:12px;"></i>
            Guides &amp; Tools
        </button>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    </article>
    <?php endforeach; ?>
    <?php if ($tickets): ?>
        <div class="tickets-filter-empty" id="tickets-filter-empty" hidden aria-live="polite">
            <span><i data-lucide="inbox"></i></span>
            <h2>No tickets here</h2>
            <p>No tickets match the current status or search.</p>
            <button type="button" class="btn btn-secondary" onclick="ticketClearFilters()"><i data-lucide="rotate-ccw"></i> Clear filters</button>
        </div>
    <?php endif; ?>
    <?php if (!$tickets): ?>
        <div class="tickets-empty">
            <span><i data-lucide="inbox"></i></span>
            <h2>No tickets yet</h2>
            <p><?= $canViewAllTickets ? 'The team queue is clear.' : 'Create your first ticket when work is assigned to you.' ?></p>
            <button type="button" class="btn btn-primary" onclick="openNewTicketModal()"><i data-lucide="plus"></i> New Ticket</button>
        </div>
        <?php endif; ?>
    </div><!-- /.tickets-grid -->
</div>

<!-- Ticket drawer (View Ticket) — sections appear only when the data exists -->
<div id="ticket-drawer-overlay" class="ftd-modal-overlay" onclick="closeTicketDrawer()" style="display:none;"></div>
<aside id="ticket-drawer" class="ftd-modal" role="dialog" aria-modal="true" aria-labelledby="ticket-drawer-title" style="display:none;">
    <div id="ticket-drawer-body" class="ftd-modal-body"></div>
</aside>

<style>
/* ===== My Tickets: summary stats ===== */
.tickets-page .tickets-hero { margin-bottom:16px; }
.ft-stats { display:grid; grid-template-columns:repeat(auto-fit,minmax(130px,1fr)); gap:10px; margin-bottom:14px; }
.ft-stats .card { border-radius:14px; }
.ft-stat-ico { width:30px; height:30px; border-radius:8px; display:inline-flex; align-items:center; justify-content:center; margin-bottom:6px; }
.ft-stat-ico svg { width:15px; height:15px; }
.ft-stat-num { font-size:24px; font-weight:800; line-height:1.2; }
.ft-stat-lbl { font-size:11px; color:#64748b; font-weight:600; text-transform:uppercase; letter-spacing:.5px; }
.ft-dot { display:inline-block; width:6px; height:6px; border-radius:50%; margin-top:6px; }
.tickets-toolbar { display:flex; gap:10px; flex-wrap:wrap; align-items:center; margin-bottom:14px; }
.ticket-copy-list{width:40px;height:40px;display:grid;place-items:center;padding:0;border:1px solid #c7d7f4;border-radius:10px;background:#f8fbff;color:#1d4ed8;cursor:pointer;box-shadow:0 1px 2px rgba(37,99,235,.06);transition:background .16s ease,border-color .16s ease,transform .16s ease}.ticket-copy-list:hover{background:#eef5ff;border-color:#8fb6f5;transform:translateY(-1px)}.ticket-copy-list:focus-visible{outline:2px solid #2563eb;outline-offset:2px}.ticket-copy-list svg{width:17px;height:17px}.dark .ticket-copy-list{background:#16243a;border-color:#294b7f;color:#bfdbfe}
.tickets-filter-row { display:flex; gap:6px; flex-wrap:wrap; }
.tickets-search-wrap { flex:1; min-width:220px; position:relative; }
.tickets-sort { width:auto; padding:8px 12px; font-size:13px; border-radius:10px; color:#374151; }

/* ===== Ticket cards ===== */
.tickets-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(330px,1fr)); gap:16px; align-items:stretch; }
@media (max-width:760px) { .tickets-grid { grid-template-columns:1fr; } }
.ft-ticket-card { display:flex; flex-direction:column; border-radius:16px; padding:22px 24px; border:1px solid #e5e7eb; box-shadow:0 1px 2px rgba(15,23,42,.04); transition:transform .16s ease, box-shadow .16s ease, border-color .16s ease; }
.ft-ticket-card:hover { transform:translateY(-3px); box-shadow:0 10px 28px rgba(15,23,42,.10); border-color:#cbd5e1; }
.dark .ft-ticket-card { background:#0f172a; border-color:#1e293b; }
.ft-co-ico { width:38px; height:38px; border-radius:10px; background:#eff6ff; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.ft-co-ico svg { width:18px; height:18px; color:#2563eb; }
.dark .ft-co-ico { background:#1e293b; }
.dark .ft-co-ico svg { color:#60a5fa; }
.ft-company { font-size:16px; font-weight:700; color:#111827; line-height:1.3; }
.dark .ft-company { color:#f1f5f9; }
.ft-tnum { font-size:12px; color:#64748b; font-weight:600; margin-top:2px; }
.ft-label { font-size:10px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:.6px; }
.ft-value { font-size:13px; font-weight:600; color:#111827; line-height:1.5; word-break:break-word; margin-top:2px; }
.dark .ft-value { color:#e2e8f0; }
.ft-section { margin-top:14px; }
.ft-divider { height:1px; background:#e5e7eb; margin:16px 0; }
.dark .ft-divider { background:#1e293b; }
.ft-issue { font-size:13.5px; }
.ft-clamped { display:-webkit-box; -webkit-line-clamp:3; -webkit-box-orient:vertical; overflow:hidden; }
.ft-clamp2 { display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; }
.ft-more { background:none; border:none; color:#2563eb; font-size:12px; font-weight:600; cursor:pointer; padding:4px 0 0; text-align:left; }
.ft-progress { display:inline-flex; align-items:center; gap:6px; margin-top:12px; font-size:11.5px; font-weight:600; color:#2563eb; background:#eff6ff; border-radius:999px; padding:5px 10px; align-self:flex-start; }
.ft-progress svg { width:12px; height:12px; }
.dark .ft-progress { background:#1e293b; }
.ft-spacer { flex:1 1 auto; min-height:16px; }
.ft-footer { display:flex; justify-content:space-between; align-items:flex-end; gap:10px; }
.ft-search-ico { position:absolute; left:10px; top:50%; transform:translateY(-50%); width:14px; height:14px; color:#94a3b8; pointer-events:none; }

/* ===== Ticket detail modal ===== */
.ftd-modal-overlay { position:fixed; inset:0; background:rgba(15,23,42,.54); backdrop-filter:blur(3px); z-index:10000; }
.ftd-modal {
    position:fixed;
    top:24px;
    bottom:24px;
    left:50%;
    width:min(1040px, calc(100vw - 40px));
    max-height:none;
    transform:translateX(-50%);
    background:#fff;
    border:1px solid #e5e7eb;
    border-radius:16px;
    z-index:10001;
    box-shadow:0 28px 70px rgba(15,23,42,.28);
    overflow-y:auto;
}
.ftd-modal-body { padding:0 26px 24px; }
.ftd-modal-head {
    position:sticky;
    top:0;
    z-index:3;
    background:rgba(255,255,255,.96);
    backdrop-filter:blur(10px);
    padding:20px 0 16px;
}
.ftd-close-btn { width:34px; height:34px; padding:0; border-radius:10px; font-size:18px; line-height:1; }
.ftd-section { margin-top:16px; }
.ftd-title { font-size:10px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:.6px; margin-bottom:8px; }
.ftd-row { display:flex; justify-content:space-between; gap:12px; padding:5px 0; font-size:12.5px; }
.ftd-lbl { color:#64748b; font-weight:600; flex-shrink:0; }
.ftd-val { color:#111827; font-weight:600; text-align:right; word-break:break-word; }
.dark #ticket-drawer { background:#0f172a; border-color:#1e293b; }
.dark .ftd-modal-head { background:rgba(15,23,42,.96); }
.dark .ftd-val { color:#e2e8f0; }
.dark .ftd-row { border-color:#1e293b; }

/* ===== Drawer: service-report layout ===== */
.ftd-grid { display:grid; grid-template-columns:1fr 1.15fr; gap:0 24px; align-items:start; margin-top:6px; }
@media (max-width:900px) { .ftd-grid { grid-template-columns:1fr; } }
.ftd-timebox { background:#f8fafc; border:1px solid #e5e7eb; border-radius:10px; padding:10px 12px; }
.ftd-ticket-map { height:190px; overflow:hidden; border:1px solid #dbe2ea; border-radius:7px; background:#eef2f7; }
.ft-guide-symptoms { display:flex; flex-wrap:wrap; gap:5px; margin-bottom:12px; }
.ft-guide-symptoms span { padding:4px 7px; border:1px solid #d8e1ec; border-radius:999px; color:#45546a; background:#fff; font-size:10.5px; }
.ft-guide-cause { margin-bottom:10px; padding:9px 10px; border-left:3px solid #2563eb; color:#344258; background:#eef4ff; font-size:11px; line-height:1.5; }
.ft-guide-kb-link { display:inline-flex; align-items:center; gap:6px; margin-bottom:12px; color:#2457d6; font-size:10.5px; font-weight:800; text-decoration:none; }
.ft-guide-kb-link svg { width:13px; height:13px; }
.tt-memory-select { width:100%; margin:0 0 6px; padding:7px 9px; font-size:12px; border-radius:7px; }
.dark .ftd-timebox { background:#0f172a; border-color:#1e293b; }
.dark .ft-guide-symptoms span { color:#c5d0df; background:#162234; border-color:#3c4c63; }
.dark .ft-guide-cause { color:#c5d0df; background:#152641; }
.ftd-time { font-size:18px; font-weight:800; color:#111827; margin-top:2px; }
.dark .ftd-time { color:#f1f5f9; }
.ftd-zero { color:#cbd5e1; }
.ftd-subdate { font-size:11px; color:#94a3b8; margin-top:2px; min-height:15px; }
.ftd-field { margin-top:10px; }
.ftd-field .ftd-lbl { margin-bottom:4px; display:block; }
.ftd-steps { font-size:12.5px; color:#374151; line-height:1.6; }
.dark .ftd-steps { color:#cbd5e1; }

/* ===== Troubleshooting checklist (mobile-friendly 44px tap rows) ===== */
.ft-chk-row { display:flex; gap:10px; align-items:flex-start; padding:10px 12px; border:1px solid #e5e7eb; border-radius:10px; margin-bottom:6px; background:#fff; cursor:pointer; min-height:44px; transition:border-color .15s ease, background .15s ease; }
.ft-chk-row:hover { border-color:#93c5fd; }
.ft-chk-row.done { border-color:#bbf7d0; background:#f0fdf4; }
.ft-chk-box { width:20px; height:20px; border-radius:6px; border:2px solid #cbd5e1; flex-shrink:0; display:flex; align-items:center; justify-content:center; font-size:12px; font-weight:800; color:#fff; margin-top:1px; }
.ft-chk-row.done .ft-chk-box { background:#16a34a; border-color:#16a34a; }
.ft-chk-txt { font-size:12.5px; font-weight:600; color:#111827; line-height:1.45; word-break:break-word; }
.ft-tool-chip { display:inline-flex; align-items:center; gap:5px; background:#eff6ff; color:#1d4ed8; border-radius:999px; padding:5px 11px; font-size:11.5px; font-weight:600; margin:0 6px 6px 0; }
.ft-video-link { display:flex; align-items:center; gap:8px; background:#fff; border:1px solid #e5e7eb; border-radius:10px; padding:9px 12px; font-size:12.5px; font-weight:600; color:#2563eb; text-decoration:none; margin-bottom:6px; }
.ft-video-link:hover { border-color:#93c5fd; background:#eff6ff; }
.ft-tip { display:flex; gap:8px; font-size:12px; font-weight:600; color:#7c2d12; background:#fff7ed; border:1px solid #fed7aa; border-radius:8px; padding:8px 10px; margin-bottom:6px; line-height:1.45; }
.dark .ft-chk-row { background:#0f172a; border-color:#1e293b; }
.dark .ft-chk-row.done { background:#052e16; border-color:#14532d; }
.dark .ft-chk-txt { color:#e2e8f0; }
.dark .ft-tool-chip { background:#1e293b; color:#93c5fd; }
.dark .ft-video-link { background:#0f172a; border-color:#1e293b; }
.dark .ft-tip { background:#2a1508; border-color:#7c2d12; color:#fdba74; }

@media (max-width: 760px) {
    .tickets-page { padding-bottom:16px; }
    .tickets-page .tickets-hero { margin-bottom:12px; }
    .tickets-page .page-hero-ico { width:38px; height:38px; border-radius:11px; }
    .tickets-page .page-hero-title { font-size:20px; }
    .tickets-page .page-hero-sub { font-size:12.5px; line-height:1.35; margin-top:2px; }
    .ft-stats { grid-template-columns:repeat(2,minmax(0,1fr)); gap:8px; margin-bottom:12px; }
    .ft-stats .card-body { padding:10px 8px !important; }
    .ft-stat-ico { width:26px; height:26px; margin-bottom:3px; }
    .ft-stat-ico svg { width:13px; height:13px; }
    .ft-stat-num { font-size:20px; line-height:1.1; }
    .ft-stat-lbl { font-size:10px; letter-spacing:.35px; }
    .ft-dot { margin-top:4px; }
    .tickets-toolbar { gap:8px; margin-bottom:12px; }
    .tickets-filter-row { gap:6px; width:100%; }
    .tickets-filter-row .btn { padding:6px 10px; font-size:12px; }
    .tickets-search-wrap { flex:1 1 150px; min-width:0; }
    .tickets-sort { flex:0 0 156px; min-width:0; }
    .tickets-grid { gap:12px; }
    .ft-ticket-card { padding:16px; border-radius:14px; }
    .ft-co-ico { width:34px; height:34px; border-radius:9px; }
    .ft-company { font-size:15px; }
    .ft-section { margin-top:10px; }
    .ft-divider { margin:12px 0; }
    .ft-spacer { min-height:10px; }
    .ft-footer { align-items:center; }
    #ticket-drawer { top:9px; bottom:9px; width:calc(100vw - 18px) !important; max-height:none; border-radius:14px; }
    #ticket-drawer-body { padding:0 16px 18px !important; }
    .ftd-modal-head { padding:14px 0 12px; }
}

@media (max-width: 430px) {
    .ft-stats { grid-template-columns:repeat(2,minmax(0,1fr)); }
    .tickets-toolbar { display:grid; grid-template-columns:1fr 136px; }
    .tickets-filter-row { grid-column:1 / -1; }
    .tickets-search-wrap { min-width:0; }
    .tickets-sort { width:100%; flex-basis:auto; }
}
</style>

<!-- Route mini-map container (one per solved ticket; opened on demand) -->
<div id="route-map-root"></div>

<link rel="stylesheet" href="<?= e($urlBase) ?>assets/lib/leaflet.css">
<script src="<?= e($urlBase) ?>assets/lib/leaflet.js"></script>
<style>
/* Ticket map icons + leaflet tweaks */
.tt-map-icon { background: transparent !important; border: none !important; }
.leaflet-popup-content b { color: #111827; }
#tt-map { position: relative; }
#tt-map .leaflet-control-zoom a { background: #fff !important; color: #111827 !important; border-color: #e5e7eb !important; }
.dark #tt-map .leaflet-control-zoom a { background: #1e293b !important; color: #f1f5f9 !important; border-color: #334155 !important; }
.dark .tt-map-icon { background: transparent !important; }
@media (max-width: 640px) { #tt-map { height: 140px; } }

/* New-ticket device-type chips */
.tt-chip-row { display: flex; gap: 8px; flex-wrap: wrap; }
.tt-chip { display: inline-flex; align-items: center; gap: 6px; padding: 7px 12px; background: #fff; border: 1px solid #e5e7eb; border-radius: 999px; font-size: 12px; font-weight: 600; color: #374151; cursor: pointer; transition: all .15s ease; }
.tt-chip:hover { border-color: #2563eb; color: #2563eb; transform: translateY(-1px); }
.tt-chip.active { background: #2563eb; border-color: #2563eb; color: #fff; box-shadow: 0 4px 12px rgba(37,99,235,.28); }
.tt-chip .tt-chip-count { font-size: 10px; font-weight: 700; opacity: .7; }

/* ===== 2026 visual refresh: presentation only, ticket behavior stays unchanged ===== */
.tickets-page {
    --tt-ink: #172033;
    --tt-muted: #64748b;
    --tt-line: #dfe5ed;
    --tt-panel: #ffffff;
    --tt-soft: #f6f8fb;
    --tt-blue: #2457d6;
    max-width: 1540px;
    margin: 0 auto;
}

.tickets-page .tickets-hero {
    padding: 2px 0 18px;
    border-bottom: 1px solid var(--tt-line);
    align-items: center;
}
.tickets-page .page-hero-ico.blue {
    width: 42px;
    height: 42px;
    border-radius: 8px;
    background: #eaf1ff;
    color: var(--tt-blue);
    box-shadow: none;
}
.tickets-page .page-hero-title {
    color: var(--tt-ink);
    font-size: 24px;
    font-weight: 800;
    letter-spacing: 0;
}
.tickets-page .page-hero-sub { color: var(--tt-muted); }
.tickets-page .page-hero-actions .btn-primary {
    min-height: 40px;
    border-radius: 8px;
    padding-inline: 16px;
    box-shadow: 0 2px 7px rgba(36,87,214,.2);
}

.ft-stats {
    grid-template-columns: repeat(5, minmax(0, 1fr));
    gap: 10px;
    margin: 18px 0;
}
.ft-stats .card {
    overflow: hidden;
    border: 1px solid var(--tt-line);
    border-radius: 8px;
    box-shadow: 0 3px 10px rgba(15,23,42,.04);
    background: var(--tt-panel);
}
.ft-stats .card:hover { border-color:#b9c8dc; box-shadow:0 7px 18px rgba(15,23,42,.07); }
.ft-stats .card-body {
    display: grid;
    grid-template-columns: 34px 1fr;
    grid-template-rows: auto auto;
    column-gap: 11px;
    align-items: center;
    min-height: 78px;
    padding: 13px 16px !important;
    text-align: left !important;
}
.ft-stat-ico {
    grid-row: 1 / 3;
    width: 34px;
    height: 34px;
    margin: 0;
    border-radius: 7px;
}
.ft-stat-num { align-self: end; font-size: 21px; line-height: 1; }
.ft-stat-lbl {
    align-self: start;
    margin-top: 5px;
    font-size: 10px;
    letter-spacing: .45px;
}
.ft-dot { display: none; }

.tt-approval-panel {
    margin-bottom: 16px;
    overflow: hidden;
    border: 1px solid #cbd9ee;
    border-radius: 8px;
    background: #f7faff;
    box-shadow: none;
}
.tt-approval-body { padding: 14px 16px !important; }
.tt-approval-title {
    display: flex;
    align-items: center;
    gap: 7px;
    color: #21314d;
    font-size: 13px;
    font-weight: 800;
}
.tt-approval-title svg { width: 16px; height: 16px; color: var(--tt-blue); }
.tt-approval-count {
    display: inline-flex;
    min-width: 21px;
    height: 21px;
    padding: 0 6px;
    align-items: center;
    justify-content: center;
    border-radius: 999px;
    color: #fff;
    background: var(--tt-blue);
    font-size: 10px;
}
.tt-approval-help { margin-top: 3px; color: var(--tt-muted); font-size: 11.5px; }
.tt-approval-heading { display:flex; justify-content:space-between; gap:12px; align-items:center; flex-wrap:wrap; margin-bottom:12px; }
.tt-approval-tabs { display:flex; gap:4px; overflow-x:auto; padding-bottom:8px; border-bottom:1px solid #d9e2ef; }
.tt-approval-tab { flex:0 0 auto; display:inline-flex; align-items:center; gap:7px; min-height:34px; padding:7px 10px; border:0; border-radius:6px; color:#5f6f85; background:transparent; font:inherit; font-size:11.5px; font-weight:700; cursor:pointer; }
.tt-approval-tab:hover { background:#edf3fc; color:#1f3a61; }
.tt-approval-tab.active { color:#185adb; background:#eaf1ff; }
.tt-approval-tab span { min-width:18px; padding:1px 5px; border-radius:999px; color:inherit; background:rgba(37,99,235,.1); font-size:10px; text-align:center; }
.tt-approval-pane { display:none; padding-top:10px; }
.tt-approval-pane.active { display:block; }
.tt-approval-actions { display:flex; justify-content:flex-end; gap:8px; margin-bottom:8px; }
.tt-approval-note { margin-bottom:9px; padding:8px 10px; border-left:3px solid #2563eb; color:#53647a; background:#edf4ff; font-size:11px; line-height:1.45; }
.tt-checklist-bulkbar { display:flex; align-items:center; gap:8px; margin-bottom:9px; }
.tt-checklist-bulkbar label { display:flex; align-items:center; gap:6px; color:#53647a; font-size:11px; font-weight:700; }
.tt-checklist-bulkbar > span { flex:1; }
.tt-approval-list { display:grid; grid-template-columns:repeat(auto-fit,minmax(260px,1fr)); gap:8px; }
.tt-approval-row { display:flex; align-items:flex-start; gap:9px; min-width:0; padding:10px; border:1px solid #dbe4f0; border-radius:7px; background:#fff; cursor:pointer; }
.tt-approval-row:hover { border-color:#9eb9e6; }
.tt-approval-row input { flex:0 0 auto; margin-top:2px; }
.tt-approval-row span { min-width:0; }
.tt-approval-row strong, .tt-approval-row small { display:block; overflow-wrap:anywhere; }
.tt-approval-row strong { color:#162033; font-size:12px; line-height:1.4; }
.tt-approval-row small { margin-top:3px; color:#69798f; font-size:10.5px; line-height:1.35; }
.tt-step-review { align-items:center; cursor:default; }
.tt-step-review.is-duplicate { border-color:#fecaca; background:#fff7f7; }
.tt-step-state { display:inline-flex; flex:0 0 auto; align-items:center; justify-content:center; width:28px; height:28px; border-radius:6px; color:#2563eb; background:#eaf1ff; }
.tt-step-state svg { width:15px; height:15px; }
.is-duplicate .tt-step-state { color:#dc2626; background:#fee2e2; }
.tt-step-copy { flex:1 1 auto; }
.tt-step-actions { display:flex; flex:0 0 auto; align-items:center; gap:6px; }
.tt-duplicate-badge { padding:4px 6px; border-radius:5px; color:#b91c1c; background:#fee2e2; font-size:9.5px; font-weight:800; text-transform:uppercase; }
.tt-existing-match { color:#b91c1c !important; font-weight:700; }
.tt-approval-empty {
    display: flex;
    grid-column: 1 / -1;
    align-items: center;
    gap: 8px;
    min-height: 38px;
    padding: 8px 10px;
    border: 1px dashed #c9d4e3;
    border-radius: 7px;
    color: #617086;
    background: rgba(255,255,255,.65);
    font-size: 12px;
    font-weight: 600;
}
.tt-approval-empty svg { width: 16px; height: 16px; color: #16a34a; }

.tickets-toolbar {
    display: grid;
    grid-template-columns: auto minmax(260px, 1fr) 170px;
    gap: 10px;
    padding: 11px;
    margin-bottom: 18px;
    border: 1px solid var(--tt-line);
    border-radius: 8px;
    background: var(--tt-panel);
    box-shadow: 0 4px 14px rgba(15,23,42,.045);
}
.tickets-filter-row {
    gap: 3px;
    padding: 3px;
    border-radius: 7px;
    background: #eef2f6;
    flex-wrap: nowrap;
    overflow-x: auto;
}
.tickets-filter-row .btn {
    flex: 0 0 auto;
    min-height: 34px;
    padding: 6px 11px;
    border: 0;
    border-radius: 5px;
    color: #526177;
    background: transparent;
    box-shadow: none;
}
.tickets-filter-row .filter-btn.active {
    color: var(--tt-ink);
    background: #fff;
    box-shadow: 0 1px 3px rgba(15,23,42,.12);
}
.tickets-search-wrap .form-input,
.tickets-sort {
    height: 40px;
    border-color: #d7dee8;
    border-radius: 7px !important;
    background: #fff;
}
.tickets-search-wrap .form-input:focus,
.tickets-sort:focus {
    border-color: #7aa2ef;
    box-shadow: 0 0 0 3px rgba(36,87,214,.1);
}

.tickets-grid {
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 16px;
}
.ft-ticket-card {
    position: relative;
    overflow: hidden;
    min-width: 0;
    min-height: 306px;
    padding: 20px;
    border-color: var(--tt-line);
    border-radius: 8px;
    box-shadow: 0 4px 14px rgba(15,23,42,.045);
    background: var(--tt-panel);
}
.ft-ticket-card::before {
    content: '';
    position: absolute;
    inset: 0 0 auto;
    width: auto;
    height: 3px;
    background: #d4a017;
}
.ft-ticket-card[data-status="in_progress"]::before { background: #2563eb; }
.ft-ticket-card[data-status="solved"]::before { background: #16a34a; }
.ft-ticket-card[data-status="escalated"]::before { background: #dc2626; }
.ft-ticket-card:hover {
    transform: translateY(-2px);
    border-color: #aebfd5;
    box-shadow: 0 12px 28px rgba(15,23,42,.09);
}
.ft-co-ico {
    width: 36px;
    height: 36px;
    border-radius: 7px;
    background: #edf3ff;
}
.ft-company {
    color: var(--tt-ink);
    font-size: 15px;
    font-weight: 800;
    line-height: 1.25;
}
.ft-tnum { color: #77859a; }
.ft-owner { display:flex; align-items:center; gap:5px; margin-top:6px; color:#586a83; font-size:10.5px; font-weight:700; }
.ft-owner svg { width:12px; height:12px; color:#2563eb; }
.ft-ticket-card > div:first-child > .badge {
    border: 1px solid currentColor;
    border-radius: 999px;
    padding: 4px 9px;
    font-size: 10px;
    line-height: 1.2;
}
.ft-section { margin-top: 13px; }
.ft-divider { margin: 14px 0; background: #e8edf3; }
.ft-label { color: #8997ab; letter-spacing: .5px; }
.ft-value { color: #253047; }
.ft-footer {
    padding-top: 14px;
    border-top: 1px solid #edf0f4;
}
.ft-footer .btn-primary {
    min-height: 34px;
    border-radius: 7px;
    padding: 7px 12px;
    white-space: normal;
    text-align: center;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:6px;
}
.ft-footer .btn-primary svg { width:13px; height:13px; }
.ft-card-guide {
    margin: 14px -18px -18px;
    padding: 10px 18px;
    border-top: 1px solid #edf0f4;
    background: #fafbfc;
}
.ft-travel { display:flex; align-items:center; justify-content:space-between; gap:10px; margin-top:14px; padding:10px 11px; border:1px solid #dce5f0; border-radius:7px; background:#f7f9fc; }
.ft-travel-main { display:flex; align-items:center; gap:9px; min-width:0; }
.ft-travel-icon { display:inline-flex; flex:0 0 auto; align-items:center; justify-content:center; width:28px; height:28px; border-radius:6px; color:#2563eb; background:#e8f0ff; }
.ft-travel-icon svg { width:14px; height:14px; }
.ft-travel-main span:last-child { min-width:0; }
.ft-travel-distance, .ft-travel-address { display:block; }
.ft-travel-distance { color:#1e293b; font-size:12px; }
.ft-travel-address { margin-top:2px; overflow:hidden; color:#728096; font-size:10px; text-overflow:ellipsis; white-space:nowrap; }
.ft-travel-eta { flex:0 0 auto; padding:5px 7px; border-radius:5px; color:#166534; background:#eaf8ef; font-size:10.5px; font-weight:800; }
.ft-ticket-card[data-route-rank="1"] .ft-travel { border-color:#8bb3f4; background:#eef5ff; }
.ft-ticket-card[data-route-rank="1"] .ft-travel::before { content:'FIRST STOP'; flex:0 0 auto; color:#1d4ed8; font-size:8px; font-weight:900; }
.tickets-empty { grid-column:1 / -1; display:flex; min-height:320px; align-items:center; justify-content:center; flex-direction:column; padding:40px; border:1px dashed #cbd7e6; border-radius:8px; color:#64748b; background:rgba(255,255,255,.6); text-align:center; }
.tickets-empty > span { display:inline-flex; align-items:center; justify-content:center; width:44px; height:44px; border-radius:8px; color:#2563eb; background:#eaf1ff; }
.tickets-empty > span svg { width:21px; height:21px; }
.tickets-empty h2 { margin:13px 0 4px; color:var(--tt-ink); font-size:16px; }
.tickets-empty p { margin:0 0 15px; font-size:12px; }
.ftd-owner-line { display:flex; align-items:center; gap:5px; margin-top:7px; color:#64748b; font-size:11px; font-weight:650; }
.ftd-owner-line svg { width:12px; height:12px; }
.ftd-owner-line span { margin-left:3px; padding:2px 6px; border-radius:5px; color:#475569; background:#eef2f7; font-size:9px; font-weight:800; text-transform:uppercase; }
.ftd-readonly { display:flex; flex:1; align-items:center; justify-content:center; gap:7px; min-height:36px; padding:8px 10px; border:1px solid #d7e0ec; border-radius:7px; color:#526177; background:#f5f7fa; font-size:11px; font-weight:700; text-align:center; }
.ftd-readonly svg { width:13px; height:13px; }
.ft-chk-row.is-readonly { cursor:default; opacity:.84; }
#ticket-drawer .form-input[readonly], #ticket-drawer .form-input:disabled { color:#475569; background:#f3f6f9; cursor:default; }

.ftd-modal-overlay { background: rgba(14,23,40,.62); backdrop-filter: blur(4px); }
.ftd-modal {
    width: min(1080px, calc(100vw - 48px));
    border-color: #d8e0ea;
    border-radius: 10px;
    box-shadow: 0 30px 80px rgba(10,18,32,.32);
}
.ftd-modal-body { padding: 0 30px 28px; }
.ftd-modal-head {
    padding: 18px 0 15px;
    border-bottom: 1px solid #e7ebf0;
}
.ftd-close-btn { border-radius: 7px; }
.ftd-grid { gap: 0 30px; }
.ftd-timebox,
.ft-chk-row,
.ft-video-link,
.ft-tip { border-radius: 7px; }
.ftd-timebox { background: #f6f8fb; }
.ft-chk-row { min-height: 46px; }

#new-ticket-panel {
    border: 1px solid #d8e0ea !important;
    border-radius: 10px !important;
    box-shadow: 0 28px 75px rgba(10,18,32,.3) !important;
}
#new-ticket-panel .form-input,
#new-ticket-panel select,
#new-ticket-panel textarea {
    border-color: #d7dee8 !important;
    border-radius: 7px !important;
    background: #fff;
}
#new-ticket-panel .form-input:focus,
#new-ticket-panel select:focus,
#new-ticket-panel textarea:focus {
    border-color: #7aa2ef !important;
    box-shadow: 0 0 0 3px rgba(36,87,214,.1) !important;
    outline: 0;
}
#new-ticket-panel .btn { border-radius: 7px; }
.tt-ticket-number-wrap { position:relative; }
.tt-ticket-number-wrap > span { position:absolute; left:12px; top:50%; z-index:1; transform:translateY(-50%); color:#2563eb; font-size:12px; font-weight:900; pointer-events:none; }
.tt-problem-insight { margin-top:10px; overflow:hidden; border:1px solid #dbe4ef; border-radius:7px; background:#f8fafc; }
.tt-insight-head { display:flex; align-items:center; gap:7px; padding:9px 10px; border-bottom:1px solid #e4eaf1; color:#24324a; font-size:11.5px; font-weight:800; }
.tt-insight-head svg { width:15px; height:15px; color:#2563eb; }
.tt-insight-head a { margin-left:auto; color:#2563eb; font-size:10.5px; text-decoration:none; }
.tt-insight-block { padding:9px 10px; }
.tt-insight-block + .tt-insight-block { padding-top:0; }
.tt-insight-label { display:block; margin-bottom:6px; color:#7c8ba0; font-size:9.5px; font-weight:800; text-transform:uppercase; }
.tt-insight-chips { display:flex; flex-wrap:wrap; gap:5px; }
.tt-insight-chips span { padding:4px 7px; border:1px solid #d8e1ec; border-radius:999px; color:#45546a; background:#fff; font-size:10.5px; }
.tt-insight-chips small { color:#8190a4; font-size:10.5px; }
.tt-insight-block p { margin:0; color:#344258; font-size:11px; line-height:1.5; }

.dark .tickets-page {
    --tt-ink: #eef2f7;
    --tt-muted: #9ba9bb;
    --tt-line: #2a3748;
    --tt-panel: #111b2b;
    --tt-soft: #162234;
}
.dark .ft-stats .card,
.dark .tickets-toolbar,
.dark .ft-ticket-card { background: var(--tt-panel); }
.dark .tt-approval-panel { background: #111b2b; border-color: #344258; }
.dark .tt-approval-title { color: #e7edf5; }
.dark .tt-approval-empty { color: #a9b6c7; background: #0d1726; border-color: #3b4a60; }
.dark .tt-approval-tabs { border-color:#344258; }
.dark .tt-approval-tab:hover, .dark .tt-approval-tab.active { color:#8fb7ff; background:#192a45; }
.dark .tt-approval-row { background:#0d1726; border-color:#344258; }
.dark .tt-approval-row strong { color:#e7edf5; }
.dark .tt-approval-row small { color:#9ba9bb; }
.dark .tt-approval-note { color:#b7c5d8; background:#152641; }
.dark .tt-step-review.is-duplicate { background:#2a171b; border-color:#71333c; }
@media (max-width: 640px) { .tt-step-review { align-items:flex-start; flex-wrap:wrap; } .tt-step-actions { width:100%; justify-content:flex-end; } }
.dark .tickets-filter-row { background: #0b1422; }
.dark .tickets-filter-row .filter-btn.active { color: #eef2f7; background: #263449; }
.dark .tickets-search-wrap .form-input,
.dark .tickets-sort,
.dark #new-ticket-panel .form-input,
.dark #new-ticket-panel select,
.dark #new-ticket-panel textarea { color: #e5eaf1; background: #0d1726; border-color: #344258 !important; }
.dark #new-ticket-panel { background: #111b2b !important; border-color: #344258 !important; }
.dark #new-ticket-panel h2,
.dark #new-ticket-panel label { color: #e7edf5 !important; }
.dark .tt-problem-insight { background:#0d1726; border-color:#344258; }
.dark .tt-insight-head { color:#e7edf5; border-color:#344258; }
.dark .tt-insight-chips span { color:#c5d0df; background:#162234; border-color:#3c4c63; }
.dark .tt-insight-block p { color:#c5d0df; }
.dark #new-ticket-panel > div:first-child { border-color: #2a3748 !important; }
.dark .ft-value { color: #dce4ee; }
.dark .ft-divider,
.dark .ft-footer,
.dark .ft-card-guide { border-color: #293648; }
.dark .ft-card-guide { background: #0d1726; }
.dark .ft-travel { background:#0d1726; border-color:#344258; }
.dark .ft-travel-distance { color:#e7edf5; }
.dark .ft-travel-eta { color:#86efac; background:#153321; }
.dark #ticket-drawer [id^="guide-"],
.dark #ticket-drawer [id^="report-"] {
    background: #111b2b !important;
    border-color: #344258 !important;
}
.dark #ticket-drawer [id^="report-"] > div { border-color: #2a3748 !important; }
.dark #ticket-drawer [id^="report-"] > div > span:last-child { color: #e5eaf1 !important; }
.dark #ticket-drawer-title { color:#f1f5f9 !important; }
.dark .ftd-owner-line { color:#a9b6c7; }
.dark .ftd-owner-line span { color:#c8d3e2; background:#263449; }
.dark .ftd-readonly { color:#c3cfde; background:#162234; border-color:#344258; }
.dark #ticket-drawer .form-input[readonly],
.dark #ticket-drawer .form-input:disabled { color:#cbd5e1; background:#162234; border-color:#344258; }
.dark #ticket-drawer .form-input {
    color: #e5eaf1;
    background: #0d1726;
    border-color: #344258;
}

@media (max-width: 1250px) {
    .ft-stats { grid-template-columns: repeat(5, minmax(108px, 1fr)); overflow-x: auto; }
    .ft-stats .card-body { padding-inline: 12px !important; }
    .tickets-toolbar { grid-template-columns: 1fr 160px; }
    .tickets-filter-row { grid-column: 1 / -1; }
}
@media (max-width: 1450px) and (min-width: 1051px) { .tickets-grid { grid-template-columns:repeat(3,minmax(0,1fr)); } }
@media (max-width: 1050px) and (min-width: 641px) { .tickets-grid { grid-template-columns:repeat(2,minmax(0,1fr)); } }
@media (max-width: 760px) {
    .tickets-page .tickets-hero { padding-bottom: 14px; }
    .tickets-page .page-hero-actions .btn-primary { width: 40px; padding: 0; font-size: 0; }
    .tickets-page .page-hero-actions .btn-primary svg { width: 18px !important; height: 18px !important; }
    .ft-stats {
        grid-template-columns: repeat(5, minmax(112px, 1fr));
        border-radius: 7px;
        scrollbar-width: thin;
    }
    .ft-stats .card-body { min-height: 68px; padding: 10px !important; }
    .ft-stat-ico { width: 30px; height: 30px; }
    .ft-stat-num { font-size: 19px; }
    .tickets-toolbar { grid-template-columns: minmax(0, 1fr) 132px; padding: 8px; }
    .tickets-filter-row { flex-wrap: nowrap; overflow-x: auto; padding-bottom: 3px; }
    .tickets-filter-row .btn { flex: 0 0 auto; }
    .tickets-grid { gap: 10px; }
    .tickets-grid { grid-template-columns:1fr; }
    .ft-ticket-card { min-height: 0; padding: 16px; }
    .ft-card-guide { margin: 12px -16px -16px; padding: 10px 16px; }
    #ticket-drawer { width: calc(100vw - 14px) !important; border-radius: 8px; }
    #ticket-drawer-body { padding: 0 15px 18px !important; }
}
@media (max-width: 430px) {
    .tickets-page .page-hero-sub { max-width: 245px; }
    .tickets-toolbar { grid-template-columns: 1fr; }
    .tickets-filter-row,
    .tickets-search-wrap,
    .tickets-sort { grid-column: 1; width: 100%; }
    .ft-footer { align-items: center; }
    .ft-footer .btn-primary { max-width: 58%; }
}
</style>

<script>
// Wire the new-ticket modal once the page is ready (and re-run on AJAX swaps).
(function() {
// Pre-load equipment + troubleshooting data for the New Ticket modal (JSON from PHP).
// Falls back to empty arrays when the DB is not available (demo / offline).
ttEqData    = <?= json_encode($equipmentOptions ?: [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
ttIssueData = <?= json_encode($issueOptions ?: [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
// Past tickets: company + every address used for it (with coordinates when saved).
ttCompanyData = <?= json_encode($companyLocationData ?: [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
ttTaskData = <?= json_encode($taskOptions ?: [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
ttProfileTicketDefault = <?= json_encode($profileTicketDefault, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
<?php
require_once APP_ROOT . '/includes/TicketRouteOrigin.php';
$routeOrigin = $demo ? $profileTicketDefault : TicketRouteOrigin::forUser((int)Auth::userId());
?>
window.ttCurrentRouteOrigin = <?= json_encode($routeOrigin, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
window.ticketTravelLoaded = false;
window.ticketTravelLoading = false;
ttIssueFieldOptions = <?= json_encode($ticketFieldOptions, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
ttCompanyContacts = <?= json_encode($ticketCompanyContacts, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;

    function tryWire() {
        if (typeof wireNewTicketModal === 'function') wireNewTicketModal();
        if (typeof ticketInitDefaultFilter === 'function') ticketInitDefaultFilter();
        if (typeof ticketRestoreApprovalTab === 'function') ticketRestoreApprovalTab();
        if (typeof ticketRestorePageState === 'function') ticketRestorePageState();
        if (typeof ticketWorkspaceInit === 'function') ticketWorkspaceInit();
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', tryWire);
    } else {
        tryWire();
    }
})();
</script>

<?php require APP_ROOT . '/includes/layout_footer.php'; ?>
