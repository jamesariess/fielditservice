<?php
if (!defined('APP_ROOT')) { @header('Location: /fielditservice/'); exit; }

$page_title = 'My Tickets';
$active_menu = 'tickets';
require APP_ROOT . '/includes/layout_header.php';

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

// ---- companies / organizations (for the Company Name field) ----
$organizations = [];
if (!$demo) {
    try {
        $orgs = Database::fetchAll("SELECT id, name FROM organizations ORDER BY name");
        if ($orgs) $organizations = $orgs;
    } catch (Exception $e) {}
}
if (empty($organizations)) {
    $organizations = [['id'=>1,'name'=>'Field IT Services'],['id'=>2,'name'=>'Customer Support Operations']];
}

// ---- company + address history from past tickets ----
// Companies the team has used before, with every address recorded for each one,
// so the same company can offer several saved locations (different branches etc.)
$companyLocationData = [];   // [{company, address, lat, lng}, ...] → JSON for JS
if (!$demo) {
    try {
        $histRows = Database::fetchAll(
            "SELECT company_name, address, latitude, longitude, MAX(started_at) AS last_used
             FROM troubleshooting_sessions
             WHERE company_name IS NOT NULL AND company_name <> ''
             GROUP BY company_name, address, latitude, longitude
             ORDER BY company_name, last_used DESC
             LIMIT 500"
        ) ?: [];
        foreach ($histRows as $r) {
            $companyLocationData[] = [
                'company' => trim((string)$r['company_name']),
                'address' => trim((string)($r['address'] ?? '')),
                'lat'     => trim((string)($r['latitude'] ?? '')),
                'lng'     => trim((string)($r['longitude'] ?? '')),
            ];
        }
        // Merge companies from past tickets into the Company Name suggestions.
        $known = [];
        foreach ($organizations as $org) { $known[mb_strtolower($org['name'])] = $org['name']; }
        foreach ($companyLocationData as $row) {
            if ($row['company'] === '') continue;
            $key = mb_strtolower($row['company']);
            if (!isset($known[$key])) { $known[$key] = $row['company']; $organizations[] = ['id'=>0,'name'=>$row['company']]; }
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
        $issueOptions = Database::fetchAll(
            "SELECT i.id, i.title, i.device_types, i.severity, c.name AS category_name
             FROM troubleshooting_issues i
             LEFT JOIN troubleshooting_categories c ON i.category_id = c.id
             WHERE i.status = 'approved' OR i.status IS NULL
             ORDER BY c.name, i.title"
        ) ?: [];
    } catch (Exception $e) {}
}

// ---- tickets (real data from troubleshooting_sessions) ----
if (!$demo) {
    try {
        $tickets = Database::fetchAll(
            "SELECT ts.*, i.title as issue_title, i.slug as issue_slug, c.name as category_name
             FROM troubleshooting_sessions ts
             LEFT JOIN troubleshooting_issues i ON ts.issue_id = i.id
             LEFT JOIN troubleshooting_categories c ON i.category_id = c.id
             WHERE ts.user_id = ?
             ORDER BY ts.started_at DESC",
            [Auth::userId()]
        );
    } catch (Exception $e) {}
}

// Fallback demo data only when there are genuinely no rows (fresh install)
if (empty($tickets)) {
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
    elseif ($t['status'] === 'in_progress' || $t['status'] === 'new') $inProgress++;
    elseif ($t['status'] === 'escalated') $escalated++;
    else $newCount++;
}
?>

<div id="new-ticket-modal" class="modal-overlay" style="display:none;">
    <div class="backdrop" onclick="closeModal('new-ticket-modal')" style="background:rgba(15,23,42,0.55);backdrop-filter:blur(6px);-webkit-backdrop-filter:blur(6px);"></div>
    <div id="new-ticket-panel" class="modal-panel" style="position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);max-width:560px;background:#fff;border-radius:16px;z-index:10001;box-shadow:0 25px 60px rgba(0,0,0,0.3);max-height:90vh;overflow-y:auto;">
        <div style="padding:20px 24px;border-bottom:1px solid #e5e7eb;display:flex;justify-content:space-between;align-items:center;">
            <h2 style="font-size:18px;font-weight:700;color:#111827;margin:0;">New Ticket</h2>
            <button onclick="closeModal('new-ticket-modal')" style="background:none;border:none;cursor:pointer;color:#94a3b8;font-size:20px;line-height:1;">&#10005;</button>
        </div>
        <div id="new-ticket-body" style="padding:20px 24px;">
            <!-- ============ Step 1: Ticket + Company + Task + Device first ============ -->
            <div id="step-device">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px;">
                    <div><label style="display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:4px;">Ticket No. *</label>
                        <input id="tt-ticket-no" placeholder="Type ticket no. e.g. TK-1007" class="form-input dark-input" style="width:100%;padding:10px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;font-weight:600;"></div>
                    <div><label style="display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:4px;">Serial Number</label>
                        <input id="tt-serial" placeholder="e.g. PW07MWVE" class="form-input dark-input" style="width:100%;padding:10px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;"></div>
                </div>

                <div style="margin-bottom:14px;">
                    <label style="display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:4px;">Company Name *</label>
                    <input id="tt-company" list="tt-company-list" placeholder="Type company name" class="form-input dark-input" style="width:100%;padding:10px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;" autocomplete="off">
                    <datalist id="tt-company-list">
                        <?php foreach ($organizations as $org): ?>
                            <option value="<?= e($org['name']) ?>"></option>
                        <?php endforeach; ?>
                    </datalist>
                </div>

                <div style="margin-bottom:14px;">
                    <label style="display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:4px;">Task *</label>
                    <input id="tt-task" placeholder="e.g. Replace keyboard and test — or diagnose no display" class="form-input dark-input" style="width:100%;padding:10px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;">
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
                    <input id="tt-location" placeholder="e.g. Floor 3, Room 301, Desk 42" class="form-input dark-input" style="width:100%;padding:10px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;">
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
                    <button type="button" id="tt-next-2" class="btn btn-primary">Start Session (Time In)</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div style="max-width:1200px;margin:0 auto;width:100%;">
    <div class="page-hero fx-reveal">
        <div>
            <div style="display:flex;align-items:center;gap:14px;">
                <div class="page-hero-ico blue"><i data-lucide="ticket"></i></div>
                <div>
                    <h1 class="page-hero-title">My Tickets</h1>
                    <p class="page-hero-sub">Manage your troubleshooting sessions and view ticket history</p>
                </div>
            </div>
        </div>
        <div class="page-hero-actions">
            <button onclick="openNewTicketModal()" class="btn btn-primary"><i data-lucide="plus" style="width:16px;height:16px;"></i> New Ticket</button>
        </div>
    </div>
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:20px;">
        <div class="card"><div class="card-body" style="text-align:center;"><div style="font-size:28px;font-weight:800;color:#2563eb;"><?= $total ?></div><div style="font-size:12px;color:#64748b;">Total Tickets</div></div></div>
        <div class="card"><div class="card-body" style="text-align:center;"><div style="font-size:28px;font-weight:800;color:#16a34a;"><?= $solved ?></div><div style="font-size:12px;color:#64748b;">Solved</div></div></div>
        <div class="card"><div class="card-body" style="text-align:center;"><div style="font-size:28px;font-weight:800;color:#d97706;"><?= $inProgress ?></div><div style="font-size:12px;color:#64748b;">In Progress</div></div></div>
        <div class="card"><div class="card-body" style="text-align:center;"><div style="font-size:28px;font-weight:800;color:#dc2626;"><?= $escalated ?></div><div style="font-size:12px;color:#64748b;">Escalated</div></div></div>
    </div>
    <div style="display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap;">
        <button onclick="ticketFilter('all')" class="btn btn-sm filter-btn active" data-filter="all">All (<?= $total ?>)</button>
        <button onclick="ticketFilter('in_progress')" class="btn btn-sm btn-secondary filter-btn" data-filter="in_progress">In Progress</button>
        <button onclick="ticketFilter('solved')" class="btn btn-sm btn-secondary filter-btn" data-filter="solved">Solved</button>
        <button onclick="ticketFilter('escalated')" class="btn btn-sm btn-secondary filter-btn" data-filter="escalated">Escalated</button>
    </div>
    <?php foreach ($tickets as $t):
        $ticketId      = $t['id'];
        $ticketNum     = $t['ticket_number'] ?? ('TK-' . $t['id']);
        $status        = $t['status'] ?? 'new';
        $priority      = $t['priority'] ?? 'medium';
        $problem       = $t['problem_description'] ?? '';
        $model         = $t['model'] ?? '';
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
        $endTime       = $t['ended_at'] ?? '';
        $resolution    = $t['resolution'] ?? '';
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
        // steps_performed is stored as JSON array; decode for display
        if ($notes && is_string($notes)) {
            $decoded = json_decode($notes, true);
            if (is_array($decoded) && count($decoded)) { $notes = implode('\n', $decoded); }
            elseif (is_string($decoded)) { $notes = $decoded; }
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
    <div class="ticket-card-wrap" data-status="<?= e($status) ?>" style="margin-bottom:14px;">
        <div class="card ticket-card" style="padding:0;overflow:hidden;">
            <div class="card-body" style="padding:16px 20px;">
                <!-- Top: identity row -->
                <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;">
                    <div style="flex:1;min-width:0;">
                        <div style="display:flex;gap:8px;margin-bottom:6px;flex-wrap:wrap;">
                            <span style="font-size:12px;color:#64748b;font-weight:700;"><?= e($ticketNum) ?></span>
                            <span class="badge" style="background:<?= $statusBg ?>;color:<?= $statusColor ?>;"><?= e(ucwords(str_replace('_',' ',$status))) ?></span>
                            <span class="badge" style="background:<?= $priorityColor ?>18;color:<?= $priorityColor ?>;"><?= e(ucfirst($priority)) ?></span>
                            <?php if ($serial): ?>
                                <span class="badge badge-gray">SN: <?= e($serial) ?></span>
                            <?php endif; ?>
                            <?php if ($companyName): ?>
                                <span class="badge" style="background:#f5f3ff;color:#6d28d9;"><?= e($companyName) ?></span>
                            <?php endif; ?>
                            <?php if ($deviceTypeVal): ?>
                                <span class="badge badge-blue"><?= e($deviceTypeVal) ?></span>
                            <?php endif; ?>
                        </div>
                        <h3 style="font-size:15px;font-weight:700;color:#111827;margin-bottom:4px;line-height:1.4;"><?= e($title) ?></h3>
                        <?php if ($taskText): ?>
                            <div style="font-size:12px;color:#475569;margin-bottom:6px;display:flex;gap:6px;align-items:flex-start;">
                                <i data-lucide="clipboard-check" style="width:13px;height:13px;color:#94a3b8;vertical-align:-2px;flex-shrink:0;margin-top:2px;"></i>
                                <span><strong style="color:#64748b;">Task:</strong> <?= e($taskText) ?></span>
                            </div>
                        <?php endif; ?>
                        <?php if ($ticketNote): ?>
                            <div style="font-size:12px;color:#475569;background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:6px 10px;margin-bottom:8px;display:flex;gap:6px;align-items:flex-start;">
                                <i data-lucide="sticky-note" style="width:13px;height:13px;color:#d97706;vertical-align:-2px;flex-shrink:0;margin-top:2px;"></i>
                                <span><?= e($ticketNote) ?></span>
                            </div>
                        <?php endif; ?>
                        <div style="display:flex;gap:14px;font-size:12px;color:#64748b;flex-wrap:wrap;">
                            <?php if ($deviceLine): ?>
                                <span><i data-lucide="cpu" style="width:13px;height:13px;color:#94a3b8;vertical-align:-2px;"></i> <?= e($deviceLine) ?></span>
                            <?php endif; ?>
                            <?php if ($department): ?>
                                <span><i data-lucide="building" style="width:13px;height:13px;color:#94a3b8;vertical-align:-2px;"></i> <?= e($department) ?></span>
                            <?php endif; ?>
                            <?php if ($location): ?>
                                <span><i data-lucide="map-pin" style="width:13px;height:13px;color:#94a3b8;vertical-align:-2px;"></i> <?= e($location) ?></span>
                            <?php endif; ?>
                            <span><i data-lucide="user" style="width:13px;height:13px;color:#94a3b8;vertical-align:-2px;"></i> <?= e($assignee) ?></span>
                            <?php if ($startTime): ?>
                                <span><i data-lucide="clock" style="width:13px;height:13px;color:#94a3b8;vertical-align:-2px;"></i> In: <?= date('m/d g:iA', strtotime($startTime)) ?></span>
                            <?php endif; ?>
                            <?php if ($hasEndTime): ?>
                                <span><i data-lucide="clock" style="width:13px;height:13px;color:#94a3b8;vertical-align:-2px;"></i> Out: <?= date('m/d g:iA', strtotime($endTime)) ?></span>
                            <?php endif; ?>
                            <?php if ($timeSpent): ?>
                                <span><i data-lucide="timer" style="width:13px;height:13px;color:#94a3b8;vertical-align:-2px;"></i> <?= $timeSpent ?> min</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div style="text-align:right;flex-shrink:0;">
                        <div style="font-size:11px;color:#94a3b8;margin-bottom:8px;"><?= $timeAgo ?></div>
                        <?php if ($status === 'solved'): ?>
                            <span style="color:#16a34a;font-size:13px;font-weight:600;">&#10004; Resolved</span>
                        <?php elseif ($status === 'escalated'): ?>
                            <span style="color:#dc2626;font-size:12px;font-weight:600;">&#9888; Escalated</span>
                        <?php elseif ($status === 'in_progress'): ?>
                            <span style="color:#2563eb;font-size:12px;font-weight:600;">&#9679; In Progress</span>
                        <?php else: ?>
                            <button onclick="ticketTroubleshoot(<?= $ticketId ?>, '<?= e($t['issue_slug'] ?? 'no-display') ?>')" class="btn btn-primary btn-sm" style="font-size:12px;padding:5px 10px;"><i data-lucide="stethoscope" style="width:13px;height:13px;"></i> Troubleshoot</button>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Action cards row: Time Out + Report -->
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:14px;">

                    <!-- TIME OUT CARD (only when session is open) -->
                    <?php if ($isOpenSession): ?>
                    <div class="action-card timeout-card" style="background:#fffbeb;border:1px solid #fde68a;border-radius:12px;padding:12px 14px;">
                        <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
                            <div style="width:30px;height:30px;border-radius:8px;background:#fff7ed;display:flex;align-items:center;justify-content:center;"><i data-lucide="timer-off" style="width:15px;height:15px;color:#d97706;"></i></div>
                            <div style="font-size:13px;font-weight:700;color:#92400e;">Time Out</div>
                        </div>
                        <p style="font-size:11.5px;color:#78350f;line-height:1.5;margin:0 0 10px;">You started this ticket. Log the finish details below.</p>
                        <div style="display:flex;flex-direction:column;gap:6px;">
                            <div><input id="to-<?= $ticketId ?>-time" type="time" value="<?= date('H:i') ?>" style="border:1px solid #fde68a;border-radius:6px;padding:6px 8px;font-size:12px;width:100%;background:#fff;"></div>
                            <div><input id="to-<?= $ticketId ?>-resolution" placeholder="Result, e.g. Replaced camera & mic. All passed." class="form-input dark-input" style="border:1px solid #fde68a;border-radius:6px;padding:6px 8px;font-size:12px;resize:vertical;background:#fff;" rows="2"></div>
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px;">
                                <div><input id="to-<?= $ticketId ?>-parts" placeholder="Parts (e.g. Camera, Mic)" class="form-input dark-input" style="border:1px solid #fde68a;border-radius:6px;padding:6px 8px;font-size:12px;background:#fff;"></div>
                                <div><input id="to-<?= $ticketId ?>-tools" placeholder="Tools used" class="form-input dark-input" style="border:1px solid #fde68a;border-radius:6px;padding:6px 8px;font-size:12px;background:#fff;"></div>
                            </div>
                            <div><input id="to-<?= $ticketId ?>-addr" placeholder="Destination address (for routing)" class="form-input dark-input" style="border:1px solid #fde68a;border-radius:6px;padding:6px 8px;font-size:12px;background:#fff;"></div>
                        </div>
                        <div style="display:flex;gap:6px;margin-top:10px;">
                            <button onclick="ticketTimeOut(<?= $ticketId ?>)" class="btn btn-warning btn-sm" style="font-size:11.5px;padding:6px 10px;flex:1;"><i data-lucide="timer-off" style="width:12px;height:12px;"></i> Time Out &amp; Save</button>
                            <button onclick="ticketCloseWithoutSave(<?= $ticketId ?>)" class="btn btn-sm btn-secondary" style="font-size:11.5px;padding:6px 10px;">Cancel</button>
                        </div>
                        <div id="to-<?= $ticketId ?>-msg" style="font-size:11px;margin-top:6px;min-height:16px;"></div>
                    </div>
                    <?php endif; ?>

                    <!-- ROUTE CARD (shown after a time-out so the tech can drive to the next job) -->
                    <?php if ($endTime && $status === 'solved'): ?>
                    <div class="action-card route-card" style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:12px;padding:12px 14px;">
                        <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;">
                            <div style="width:30px;height:30px;border-radius:8px;background:#f0fdf4;display:flex;align-items:center;justify-content:center;"><i data-lucide="map" style="width:15px;height:15px;color:#16a34a;"></i></div>
                            <div style="font-size:13px;font-weight:700;color:#166534;">Next Stop</div>
                        </div>
                        <div id="route-<?= $ticketId ?>" style="font-size:12px;color:#15803d;line-height:1.5;">
                            <?php if (!empty($t['last_route_end_addr'])): ?>
                                <div style="color:#475569;font-size:11px;margin-bottom:4px;">From: <?= e($t['last_route_end_addr']) ?></div>
                            <?php endif; ?>
                            <?php if (!empty($t['next_route_addr'])): ?>
                                <div style="font-weight:600;margin-bottom:2px;">Go to: <?= e($nextRouteAddr) ?></div>
                                <div style="color:#475569;font-size:11px;">Ticket <?= e($nextRouteTicket) ?></div>
                            <?php else: ?>
                                <div style="color:#475569;">Routing will appear once you log your next time-out and set its destination.</div>
                            <?php endif; ?>
                        </div>
                        <div style="margin-top:8px;">
                            <button onclick="ticketRouteToNext(<?= $ticketId ?>)" class="btn btn-sm btn-success" style="font-size:11.5px;padding:6px 10px;flex:1;"><i data-lucide="navigation" style="width:12px;height:12px;"></i> Open Route Map</button>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- REPORT CARD (always visible, even on open sessions for in-progress copy) -->
                    <div class="action-card report-card" style="background:#f8fafc;border:1px solid #e5e7eb;border-radius:12px;padding:12px 14px;">
                        <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
                            <div style="width:30px;height:30px;border-radius:8px;background:#eff6ff;display:flex;align-items:center;justify-content:center;"><i data-lucide="file-text" style="width:15px;height:15px;color:#2563eb;"></i></div>
                            <div style="font-size:13px;font-weight:700;color:#1e40af;">Report</div>
                        </div>
                        <div id="report-<?= $ticketId ?>" style="background:#fff;border:1px solid #e5e7eb;border-radius:8px;padding:8px 10px;font-size:11.5px;color:#374151;line-height:1.6;white-space:pre-wrap;max-height:120px;overflow-y:auto;word-break:break-word;"></div>
                        <div style="display:flex;gap:6px;margin-top:8px;">
                            <button onclick="ticketCopyReport(<?= $ticketId ?>)" class="btn btn-sm btn-secondary" style="font-size:11.5px;padding:5px 10px;flex:1;"><i data-lucide="copy" style="width:12px;height:12px;"></i> Copy</button>
                            <button onclick="ticketRegenReport(<?= $ticketId ?>)" class="btn btn-sm btn-ghost" style="font-size:11.5px;padding:5px 10px;color:#94a3b8;">&#8635; Refresh</button>
                        </div>
</div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Route mini-map container (one per solved ticket; opened on demand) -->
<div id="route-map-root"></div>

<link rel="stylesheet" href="<?= e($urlBase) ?>assets/lib/leaflet.css">
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
</style>

<script>
// Wire the new-ticket modal once the page is ready (and re-run on AJAX swaps).
(function() {
    function tryWire() { if (typeof wireNewTicketModal === 'function') wireNewTicketModal(); }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', tryWire);
    } else {
        tryWire();
    }
})();

// Pre-load equipment + troubleshooting data for the New Ticket modal (JSON from PHP).
// Falls back to empty arrays when the DB is not available (demo / offline).
ttEqData    = <?= json_encode($equipmentOptions ?: [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
ttIssueData = <?= json_encode($issueOptions ?: [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
// Past tickets: company + every address used for it (with coordinates when saved).
ttCompanyData = <?= json_encode($companyLocationData ?: [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
</script>

<?php require APP_ROOT . '/includes/layout_footer.php'; ?>
