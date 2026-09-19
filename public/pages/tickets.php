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
             ORDER BY ts.id DESC",
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
                    <button type="button" id="tt-next-2" class="btn btn-primary">Create Ticket</button>
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
                    <p class="page-hero-sub">Manage your field IT tickets and troubleshooting requests</p>
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
    <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;margin-bottom:16px;">
        <div style="display:flex;gap:6px;flex-wrap:wrap;">
            <button onclick="ticketFilter('all')" class="btn btn-sm filter-btn active" data-filter="all">All (<?= $total ?>)</button>
            <button onclick="ticketFilter('new')" class="btn btn-sm btn-secondary filter-btn" data-filter="new">New (<?= $newCount ?>)</button>
            <button onclick="ticketFilter('in_progress')" class="btn btn-sm btn-secondary filter-btn" data-filter="in_progress">In Progress (<?= $inProgress ?>)</button>
            <button onclick="ticketFilter('solved')" class="btn btn-sm btn-secondary filter-btn" data-filter="solved">Solved (<?= $solved ?>)</button>
            <button onclick="ticketFilter('escalated')" class="btn btn-sm btn-secondary filter-btn" data-filter="escalated">Escalated (<?= $escalated ?>)</button>
        </div>
        <div style="flex:1;min-width:220px;position:relative;">
            <i data-lucide="search" class="ft-search-ico"></i>
            <input id="ticket-search" oninput="ticketApplyFilters()" placeholder="Search company, ticket #, serial, device, problem..." class="form-input" style="width:100%;padding:8px 12px 8px 32px;font-size:13px;border-radius:10px;">
        </div>
        <select id="ticket-sort" onchange="ticketApplyFilters()" class="form-input" style="width:auto;padding:8px 12px;font-size:13px;border-radius:10px;color:#374151;">
            <option value="newest">Newest</option>
            <option value="oldest">Oldest</option>
            <option value="updated">Recently Updated</option>
        </select>
    </div>
    <div class="tickets-grid" id="tickets-grid">
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
        $createdAt     = $t['created_at'] ?? $startTime;
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
    <?php
    // ---- Derived card values (sections are rendered ONLY when the data exists) ----
    $createdTs  = $createdAt ? strtotime($createdAt) : 0;
    $updatedTs  = max($createdTs, $endTime ? strtotime($endTime) : 0);
    $deviceName = trim(trim((string)$manufacturer) . ' ' . trim((string)$model));
    $issueText  = $problem ?: ($taskText ?: $title);
    $stepsCount = 0; $stepsList = [];
    if ($notes && is_string($notes)) {
        $decodedSteps = json_decode($notes, true);
        if (is_array($decodedSteps)) { $stepsList = $decodedSteps; $stepsCount = count($decodedSteps); }
        elseif (is_string($decodedSteps) && $decodedSteps !== '') { $stepsList = [$decodedSteps]; $stepsCount = 1; }
    }
    $issueLong  = mb_strlen($issueText) > 110;
    $searchBlob = mb_strtolower($ticketNum . ' ' . $companyName . ' ' . $customerName . ' ' . $serial . ' ' . $deviceName . ' ' . $deviceTypeVal . ' ' . $issueText . ' ' . $taskText);
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
        'task'                => $taskText,
        'title'               => $title,
        'started_at'          => $startTime,
        'created_at'          => $createdAt,
        'ended_at'            => $endTime,
        'resolution'          => $resolution,
        'status'              => $status,
        'priority'            => $priority,
        'location'            => $location,
        'address'             => $address,
        'steps'               => $stepsList,
        'parts_replaced'      => $partsReplaced,
        'tools_used'          => $toolsUsed,
        'time_spent_minutes'  => $timeSpent,
    ];
    ?>
    <script>window.ttTicketData = window.ttTicketData || {}; window.ttTicketData[<?= (int)$ticketId ?>] = <?= json_encode($reportData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;</script>
    <article class="card ft-ticket-card" data-id="<?= (int)$ticketId ?>" data-status="<?= e($status) ?>" data-created="<?= $createdTs ?>" data-updated="<?= $updatedTs ?>" data-search="<?= e($searchBlob) ?>">
        <!-- Header: company + ticket # | status -->
        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;">
            <div style="display:flex;align-items:flex-start;gap:12px;min-width:0;">
                <div class="ft-co-ico"><i data-lucide="building-2"></i></div>
                <div style="min-width:0;">
                    <div class="ft-company"><?= $companyName !== '' ? e($companyName) : 'Company not specified' ?></div>
                    <div class="ft-tnum">Ticket #<?= e($ticketNum) ?></div>
                </div>
            </div>
            <span class="badge" style="background:<?= $statusBg ?>;color:<?= $statusColor ?>;flex-shrink:0;"><?= e(ucwords(str_replace('_',' ',$status))) ?></span>
        </div>
        <!-- Device information (only fields that actually exist) -->
        <?php if ($deviceName || $serial || $deviceTypeVal): ?>
        <div class="ft-section">
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
        <div class="ft-section" style="margin-top:0;">
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
        <div class="ft-spacer"></div>
        <!-- Footer: created date + next action -->
        <div class="ft-footer">
            <div>
                <div class="ft-label">Created</div>
                <div class="ft-value"><?= $createdAt ? date('m/d/Y', $createdTs) : '—' ?></div>
            </div>
            <button class="btn btn-sm btn-primary" onclick="openTicketDrawer(<?= (int)$ticketId ?>)">
                <?php
                if ($status === 'new') { echo 'Open Ticket'; }
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
    </div><!-- /.tickets-grid -->
    <?php if (empty($tickets)): ?>
    <div class="card" style="text-align:center;padding:48px 24px;border-radius:16px;">
        <div style="width:56px;height:56px;border-radius:14px;background:#eff6ff;display:inline-flex;align-items:center;justify-content:center;margin-bottom:12px;"><i data-lucide="inbox" style="width:24px;height:24px;color:#2563eb;"></i></div>
        <div style="font-size:16px;font-weight:700;color:#111827;">No tickets yet</div>
        <p style="font-size:13px;color:#64748b;margin:6px 0 16px;">Create your first Field IT ticket to start tracking your service requests.</p>
        <button onclick="openNewTicketModal()" class="btn btn-primary"><i data-lucide="plus" style="width:14px;height:14px;"></i> New Ticket</button>
    </div>
    <?php endif; ?>
</div>

<!-- Ticket drawer (View Ticket) — sections appear only when the data exists -->
<div id="ticket-drawer-overlay" onclick="closeTicketDrawer()" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,.45);z-index:90;"></div>
<aside id="ticket-drawer" style="display:none;position:fixed;top:0;right:0;bottom:0;width:min(1000px,96vw);background:#fff;z-index:95;box-shadow:-16px 0 48px rgba(15,23,42,.18);overflow-y:auto;">
    <div id="ticket-drawer-body" style="padding:22px 26px;"></div>
</aside>

<style>
/* ===== My Tickets: summary stats ===== */
.ft-stats { display:grid; grid-template-columns:repeat(auto-fit,minmax(150px,1fr)); gap:12px; margin-bottom:18px; }
.ft-stat-ico { width:30px; height:30px; border-radius:8px; display:inline-flex; align-items:center; justify-content:center; margin-bottom:6px; }
.ft-stat-ico svg { width:15px; height:15px; }
.ft-stat-num { font-size:24px; font-weight:800; line-height:1.2; }
.ft-stat-lbl { font-size:11px; color:#64748b; font-weight:600; text-transform:uppercase; letter-spacing:.5px; }
.ft-dot { display:inline-block; width:6px; height:6px; border-radius:50%; margin-top:6px; }

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

/* ===== Drawer ===== */
.ftd-section { margin-top:16px; }
.ftd-title { font-size:10px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:.6px; margin-bottom:8px; }
.ftd-row { display:flex; justify-content:space-between; gap:12px; padding:5px 0; font-size:12.5px; }
.ftd-lbl { color:#64748b; font-weight:600; flex-shrink:0; }
.ftd-val { color:#111827; font-weight:600; text-align:right; word-break:break-word; }
.dark #ticket-drawer { background:#0f172a; }
.dark .ftd-val { color:#e2e8f0; }
.dark .ftd-row { border-color:#1e293b; }

/* ===== Drawer: service-report layout ===== */
.ftd-grid { display:grid; grid-template-columns:1fr 1.15fr; gap:0 24px; align-items:start; margin-top:6px; }
@media (max-width:900px) { .ftd-grid { grid-template-columns:1fr; } }
.ftd-timebox { background:#f8fafc; border:1px solid #e5e7eb; border-radius:10px; padding:10px 12px; }
.dark .ftd-timebox { background:#0f172a; border-color:#1e293b; }
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
</style>

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
