<?php
if (!defined('APP_ROOT')) { http_response_code(403); exit; }
Auth::requireLogin();
$role = strtolower((string)($_SESSION['role_name'] ?? ''));
if (!Auth::hasPermission('system.settings') && !in_array($role, ['admin','super admin','super_admin','manager'], true)) {
    http_response_code(403); echo 'Permission denied'; return;
}
$view = ($_GET['view'] ?? 'management') === 'approvals' ? 'approvals' : 'management';
$page_title = $view === 'management' ? 'Ticket Management' : 'Reusable Entry Approvals';
$active_menu = 'admin-ticket-approvals';
$rows = [];
$loadError = false;
if (!defined('DEMO_MODE') || !DEMO_MODE) {
    require_once APP_ROOT . '/includes/TicketSuggestions.php';
    require_once APP_ROOT . '/includes/TicketFieldMemory.php';
    require_once APP_ROOT . '/includes/TicketStepSuggestions.php';
    try {
        TicketSuggestions::ensure();
        TicketFieldMemory::ensure();
        TicketStepSuggestions::sync();
        $rows = Database::fetchAll("SELECT q.id, 'suggestions' kind, q.type category, q.value,
            '' context, '' ticket, COALESCE(u.full_name,'Unknown user') submitter,
            IF(q.deleted_at IS NOT NULL,'rejected',q.status) status, q.created_at
            FROM ticket_suggestions q LEFT JOIN users u ON u.id=q.created_by
            UNION ALL
            SELECT q.id, q.memory_type, q.memory_type, q.value,
            COALESCE(i.title,q.company_key,''), '', COALESCE(u.full_name,'Unknown user'),
            IF(q.deleted_at IS NOT NULL,'rejected',q.status), q.created_at
            FROM ticket_field_memory q LEFT JOIN users u ON u.id=q.created_by
            LEFT JOIN troubleshooting_issues i ON i.id=q.issue_id
            UNION ALL
            SELECT q.id, 'checklist', 'checklist', q.title, COALESCE(i.title,''),
            COALESCE(s.ticket_number,''), COALESCE(u.full_name,'Unknown user'), q.status, q.created_at
            FROM ticket_step_suggestions q JOIN troubleshooting_sessions s ON s.id=q.session_id
            LEFT JOIN users u ON u.id=s.user_id LEFT JOIN troubleshooting_issues i ON i.id=q.issue_id
            UNION ALL
            SELECT t.id, 'library', 'checklist', t.title, i.title, '', 'Shared library', 'approved', t.created_at
            FROM troubleshooting_steps t JOIN troubleshooting_issues i ON i.id=t.issue_id
            ORDER BY created_at DESC, id DESC");
    } catch (Throwable $e) { error_log('Ticket approvals: ' . $e->getMessage()); $loadError = true; }
}
require APP_ROOT . '/includes/layout_header.php';
if ($view === 'management') {
    $managedTickets = Database::fetchAll("SELECT ts.id, ts.ticket_number, ts.company_name, ts.customer_name, ts.location, ts.problem_description, ts.priority, CASE WHEN ts.resolution_type='cancelled' THEN 'cancelled' ELSE ts.status END AS status, ts.created_at, u.full_name AS owner_name, i.title AS issue_title FROM troubleshooting_sessions ts LEFT JOIN users u ON u.id=ts.user_id LEFT JOIN troubleshooting_issues i ON i.id=ts.issue_id ORDER BY ts.created_at DESC, ts.id DESC");
    ?>
    <link rel="stylesheet" href="<?= e(app_base()) ?>assets/css/ticket-management.css?v=<?= filemtime(APP_ROOT . '/public/assets/css/ticket-management.css') ?>">
    <section id="ticket-management">
        <div class="page-hero"><div><h1>Ticket Management</h1><p>Review, update, and close field service tickets and reports.</p></div><a class="btn btn-secondary" href="<?= e(app_base()) ?>admin/ticket-approvals?view=approvals">Reusable Approvals</a></div>
        <div class="approval-filters"><input id="manage-search" class="form-input" type="search" placeholder="Search ticket, company, problem, location or owner"><select id="manage-status" class="form-input"><option value="">All statuses</option><option>new</option><option>in_progress</option><option>solved</option><option>partial</option><option>escalated</option><option>unsolved</option><option>cancelled</option></select></div>
        <div class="approval-table-scroll"><table class="approval-table"><thead><tr><th>Ticket</th><th>Company / Location</th><th>Problem</th><th>Owner</th><th>Status</th><th>Created</th><th>Actions</th></tr></thead><tbody id="manage-body">
        <?php foreach ($managedTickets as $ticket): $company = trim((string)($ticket['company_name'] ?: $ticket['customer_name'])); ?><tr data-search="<?= e(strtolower(implode(' ', [$ticket['ticket_number'], $company, $ticket['location'], $ticket['problem_description'], $ticket['owner_name'], $ticket['issue_title']])) ) ?>" data-status="<?= e($ticket['status']) ?>"><td><strong><?= e($ticket['ticket_number'] ?: 'SD'.$ticket['id']) ?></strong></td><td><?= e($company ?: 'Company not specified') ?><small><?= e($ticket['location'] ?: 'No location') ?></small></td><td><?= e($ticket['problem_description'] ?: $ticket['issue_title'] ?: 'No problem specified') ?></td><td><?= e($ticket['owner_name'] ?: 'Unknown') ?></td><td><span class="approval-status <?= e($ticket['status']) ?>"><?= e(ucwords(str_replace('_',' ', $ticket['status']))) ?></span></td><td><?= e(date('Y-m-d', strtotime($ticket['created_at']))) ?></td><td><details><summary class="btn btn-sm btn-secondary">Edit</summary><form class="manage-form" data-id="<?= (int)$ticket['id'] ?>" style="min-width:260px;padding:10px;background:var(--card-bg,#fff);"><input name="ticket_number" class="form-input" value="<?= e($ticket['ticket_number']) ?>" placeholder="Ticket number"><input name="company_name" class="form-input" value="<?= e($company) ?>" placeholder="Company name"><input name="location" class="form-input" value="<?= e($ticket['location']) ?>" placeholder="Location"><textarea name="problem_description" class="form-input" rows="2" placeholder="Problem / report"><?= e($ticket['problem_description']) ?></textarea><select name="priority" class="form-input"><option <?= $ticket['priority']==='low'?'selected':'' ?>>low</option><option <?= $ticket['priority']==='medium'?'selected':'' ?>>medium</option><option <?= $ticket['priority']==='high'?'selected':'' ?>>high</option><option <?= $ticket['priority']==='critical'?'selected':'' ?>>critical</option></select><select name="status" class="form-input"><option <?= $ticket['status']==='new'?'selected':'' ?>>new</option><option <?= $ticket['status']==='in_progress'?'selected':'' ?>>in_progress</option><option <?= $ticket['status']==='solved'?'selected':'' ?>>solved</option><option <?= $ticket['status']==='partial'?'selected':'' ?>>partial</option><option <?= $ticket['status']==='escalated'?'selected':'' ?>>escalated</option><option <?= $ticket['status']==='unsolved'?'selected':'' ?>>unsolved</option></select><p class="manage-message" role="status" aria-live="polite"></p><button class="btn btn-primary" type="submit">Save changes</button><button class="btn btn-secondary" type="button" data-cancel="<?= (int)$ticket['id'] ?>">Cancel ticket</button></form></details></td></tr><?php endforeach; ?>
        </tbody></table></div>
    </section>
    <style>#ticket-management h1{font-size:24px;margin:0}#ticket-management .page-hero p{margin:5px 0 0;color:#64748b;font-size:13px}.manage-form{display:grid;gap:7px}.manage-form .form-input{font-size:12px;padding:7px}.approval-status.new{background:#fff5d9;color:#875600}.approval-status.in_progress{background:#e7f0ff;color:#1d4ed8}.approval-status.solved{background:#e5f6ed;color:#166534}.approval-status.unsolved,.approval-status.escalated,.approval-status.cancelled{background:#feecec;color:#a52a2a}</style>
    <script>
    const manageSearch = document.getElementById('manage-search');
    const manageStatus = document.getElementById('manage-status');
    const manageApi = <?= json_encode(app_base() . 'api/tickets/manage.php') ?>;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    function filterManaged() {
        const query = (manageSearch.value || '').toLowerCase();
        const status = manageStatus.value;
        document.querySelectorAll('#manage-body tr').forEach(row => {
            row.style.display = (!query || row.dataset.search.includes(query)) && (!status || row.dataset.status === status) ? '' : 'none';
        });
    }
    async function updateManagedTicket(id, data) {
        const response = await fetch(manageApi + '?id=' + encodeURIComponent(id), {
            method: 'PATCH',
            credentials: 'same-origin',
            headers: {'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken},
            body: JSON.stringify(data)
        });
        const raw = await response.text();
        let payload = {};
        try { payload = raw ? JSON.parse(raw) : {}; } catch (_) { throw new Error('The server returned an invalid response.'); }
        if (!response.ok || !payload.success) throw new Error(payload.error || 'Ticket update failed.');
    }
    manageSearch.oninput = filterManaged;
    manageStatus.onchange = filterManaged;
    document.querySelectorAll('.manage-form').forEach(form => {
        const message = form.querySelector('.manage-message');
        const report = text => { message.textContent = text; message.classList.add('is-error'); };
        form.onsubmit = async event => {
            event.preventDefault();
            const save = form.querySelector('[type="submit"]');
            save.disabled = true;
            message.textContent = 'Saving changes...';
            message.classList.remove('is-error');
            try {
                await updateManagedTicket(form.dataset.id, Object.fromEntries(new FormData(form)));
                message.textContent = 'Saved. Refreshing ticket list...';
                location.reload();
            } catch (error) { report(error.message); save.disabled = false; }
        };
        form.querySelector('[data-cancel]').onclick = async () => {
            const decision = await Swal.fire({
                title: 'Cancel this ticket?',
                text: 'The ticket will be marked as cancelled and removed from active work.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, cancel ticket',
                cancelButtonText: 'Keep ticket',
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#64748b',
                reverseButtons: true,
                focusCancel: true
            });
            if (!decision.isConfirmed) return;
            const cancel = form.querySelector('[data-cancel]');
            cancel.disabled = true;
            message.textContent = 'Cancelling ticket...';
            message.classList.remove('is-error');
            try {
                await updateManagedTicket(form.dataset.id, {status: 'cancelled'});
                await Swal.fire({title: 'Ticket cancelled', text: 'The ticket has been removed from active work.', icon: 'success', timer: 1100, showConfirmButton: false});
                location.reload();
            } catch (error) { report(error.message); cancel.disabled = false; }
        };
    });
    </script>
    <script>
    document.querySelectorAll('#ticket-management details').forEach(function(panel) {
        var form = panel.querySelector('.manage-form');
        var summary = panel.querySelector('summary');
        var ticket = panel.closest('tr').querySelector('strong').textContent;
        summary.setAttribute('aria-label', 'Edit ' + ticket);
        summary.setAttribute('title', 'Edit ' + ticket);
        summary.innerHTML = '<i data-lucide="square-pen" aria-hidden="true"></i>';
        var heading = document.createElement('h2');
        heading.textContent = 'Edit ' + ticket;
        form.prepend(heading);
        var close = document.createElement('button');
        close.type = 'button';
        close.className = 'manage-close';
        close.setAttribute('aria-label', 'Close edit panel');
        close.setAttribute('title', 'Close');
        close.innerHTML = '<i data-lucide="x" aria-hidden="true"></i>';
        close.addEventListener('click', function() { panel.open = false; summary.focus(); });
        heading.after(close);
        var labels = {ticket_number:'Ticket number', company_name:'Company', location:'Location', problem_description:'Problem / task', priority:'Priority', status:'Status'};
        Object.keys(labels).forEach(function(name) {
            var field = form.querySelector('[name="' + name + '"]');
            if (!field) return;
            var label = document.createElement('label');
            label.textContent = labels[name];
            if (name === 'problem_description') label.className = 'wide';
            field.parentNode.insertBefore(label, field);
            label.appendChild(field);
        });
        var actions = document.createElement('div');
        actions.className = 'manage-form-actions';
        Array.from(form.querySelectorAll('button:not(.manage-close)')).forEach(function(button) { actions.appendChild(button); });
        form.appendChild(actions);
        form.querySelector('[data-cancel]').classList.add('manage-cancel-ticket');
        panel.addEventListener('toggle', function() {
            if (panel.open) document.querySelectorAll('#ticket-management details').forEach(function(other) { if (other !== panel) other.open = false; });
        });
    });
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') document.querySelectorAll('#ticket-management details[open]').forEach(function(panel) { panel.open = false; });
    });
    if (window.lucide) lucide.createIcons();
    </script>
    <?php require APP_ROOT . '/includes/layout_footer.php'; return;
}
?>
<section id="admin-ticket-approvals">
    <div class="page-hero"><div><h1>Ticket Approvals</h1></div><a class="btn btn-secondary" href="<?= e(app_base()) ?>tickets">My Tickets</a></div>
    <?php if ($loadError): ?><p role="alert">Approval records could not be loaded. Please try again.</p><?php endif; ?>
    <div class="approval-filters">
        <input id="approval-search" class="form-input" type="search" aria-label="Search approvals" placeholder="Search text, problem, company, ticket or person">
        <select id="approval-category" class="form-input" aria-label="Category"><option value="all">All categories</option><option value="suggestions">Company &amp; Task</option><option value="checklist">Checklist</option><option value="result">Results of Checking</option><option value="recommendation">Recommendations</option><option value="confirmed_by">Confirmed By</option></select>
        <select id="approval-status" class="form-input" aria-label="Status"><option value="review">Awaiting review</option><option value="all">All statuses</option><option value="pending">Pending</option><option value="duplicate">Already exists</option><option value="approved">Approved</option><option value="rejected">Rejected</option></select>
        <select id="approval-context" class="form-input" aria-label="Problem or company"><option value="">All problems / companies</option></select>
    </div>
    <div class="approval-actions"><span id="approval-count" role="status"></span><span class="approval-grow"></span><button type="button" class="btn btn-primary" id="approval-approve">Approve Selected</button><button type="button" class="btn btn-secondary" id="approval-reject">Reject Selected</button></div>
    <div class="approval-table-scroll"><table class="approval-table"><thead><tr><th><input type="checkbox" id="approval-all" aria-label="Select current page"></th><th>Entry</th><th>Problem / Company</th><th>Ticket / Submitted By</th><th>Status</th><th>Actions</th></tr></thead><tbody id="approval-body"></tbody></table></div>
    <div class="approval-actions"><button type="button" class="btn btn-secondary" id="approval-prev" aria-label="Previous page">Previous</button><span id="approval-page"></span><button type="button" class="btn btn-secondary" id="approval-next" aria-label="Next page">Next</button></div>
</section>
<style>
#admin-ticket-approvals h1{font-size:24px;margin:0}
.approval-filters{display:grid;grid-template-columns:minmax(240px,2fr) repeat(3,minmax(160px,1fr));gap:10px;margin:16px 0}
.approval-actions{display:flex;align-items:center;gap:10px;margin:12px 0;flex-wrap:wrap;font-size:12px}.approval-grow{flex:1}
.approval-table-scroll{overflow:auto;border:1px solid #dce2ea;border-radius:7px;background:var(--card-bg,#fff)}
.approval-table{width:100%;border-collapse:collapse;min-width:840px;font-size:12px;text-align:left}
.approval-table th{padding:12px;background:#f3f6f9;color:#546176;white-space:nowrap}.approval-table td{padding:12px;border-top:1px solid #e5eaf0;vertical-align:top;overflow-wrap:anywhere}
.approval-table td:nth-child(2){width:32%;min-width:240px}.approval-table td:nth-child(3){width:22%}.approval-table td:last-child{min-width:170px}
.approval-table small{display:block;color:#64748b;margin-top:5px}.approval-table button{margin:0 4px 4px 0}.approval-table input{width:16px;height:16px}
.approval-status{display:inline-block;padding:4px 7px;border-radius:4px;background:#fff5d9;color:#875600;white-space:nowrap}.approval-status.approved{background:#e5f6ed;color:#166534}.approval-status.duplicate,.approval-status.rejected{background:#feecec;color:#a52a2a}
.dark .approval-table-scroll{background:#111827;border-color:#374151}.dark .approval-table th{background:#1f2937;color:#d1d5db}.dark .approval-table td{border-color:#374151}
@media(max-width:900px){.approval-filters{grid-template-columns:1fr 1fr}}
@media(max-width:500px){.approval-filters{grid-template-columns:1fr}}
</style>
<script>
(function(){
    var rows = <?= json_encode($rows, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP) ?>;
    var root=document.getElementById('admin-ticket-approvals'), page=0, size=25, busy=false;
    var search=root.querySelector('#approval-search'), category=root.querySelector('#approval-category'), status=root.querySelector('#approval-status'), context=root.querySelector('#approval-context');
    var selected=new Set(), visible=[];
    var labels={company:'Company',task:'Task',checklist:'Checklist',result:'Result of Checking',recommendation:'Recommendation',confirmed_by:'Confirmed By'};
    var params=new URLSearchParams(location.search);
    if(Array.from(category.options).some(function(o){return o.value===params.get('category');})) category.value=params.get('category');
    Array.from(new Set(rows.map(function(r){return r.context;}).filter(Boolean))).sort().forEach(function(value){var o=new Option(value,value);context.add(o);});
    function esc(v){return String(v==null?'':v).replace(/[&<>"']/g,function(c){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c];});}
    function key(r){return r.kind+':'+r.id;}
    function actionable(r){return r.kind!=='library' && ['pending','duplicate'].includes(r.status);}
    function render(){
        var q=search.value.trim().toLowerCase();
        var filtered=rows.filter(function(r){return (category.value==='all'||r.kind===category.value||(category.value==='checklist'&&r.kind==='library'))&&(status.value==='all'||(status.value==='review'?['pending','duplicate'].includes(r.status):r.status===status.value))&&(!context.value||r.context===context.value)&&(!q||[r.value,r.context,r.ticket,r.submitter].join(' ').toLowerCase().includes(q));});
        page=Math.min(page,Math.max(0,Math.ceil(filtered.length/size)-1));visible=filtered.slice(page*size,(page+1)*size);
        root.querySelector('#approval-body').innerHTML=visible.map(function(r){var k=key(r);return '<tr><td>'+(actionable(r)?'<input type="checkbox" data-key="'+esc(k)+'" aria-label="Select entry" '+(selected.has(k)?'checked':'')+'>':'')+'</td><td>'+esc(r.value)+'<small>'+esc(labels[r.category]||r.category)+'</small></td><td>'+esc(r.context||'—')+'</td><td>'+esc(r.ticket||'—')+'<small>'+esc(r.submitter)+'</small></td><td><span class="approval-status '+esc(r.status)+'">'+esc(r.status==='duplicate'?'Already exists':r.status)+'</span></td><td>'+(actionable(r)?(r.status==='pending'&&(r.kind!=='checklist'||r.context)?'<button class="btn btn-sm btn-primary" data-action="approve" data-key="'+esc(k)+'">Approve</button>':'')+'<button class="btn btn-sm btn-secondary" data-action="reject" data-key="'+esc(k)+'">'+(r.status==='duplicate'?'Dismiss':'Reject')+'</button>':'')+'</td></tr>';}).join('')||'<tr><td colspan="6">No matching entries.</td></tr>';
        root.querySelector('#approval-count').textContent=filtered.length+' entries · '+selected.size+' selected';
        root.querySelector('#approval-page').textContent='Page '+(page+1)+' of '+Math.max(1,Math.ceil(filtered.length/size));
        root.querySelector('#approval-prev').disabled=busy||page===0;root.querySelector('#approval-next').disabled=busy||(page+1)*size>=filtered.length;
        root.querySelectorAll('button[data-action],#approval-approve,#approval-reject').forEach(function(b){b.disabled=busy;});
        var eligible=visible.filter(actionable), all=root.querySelector('#approval-all');all.checked=eligible.length>0&&eligible.every(function(r){return selected.has(key(r));});all.indeterminate=!all.checked&&eligible.some(function(r){return selected.has(key(r));});
    }
    async function review(action,keys){
        if(busy)return;var targets=rows.filter(function(r){return keys.includes(key(r))&&actionable(r);});
        if(!targets.length){showToast('Select entries first.','warning');return;}busy=true;render();
        try{
            var groups={}, skipped=0;targets.forEach(function(r){(groups[r.kind]||(groups[r.kind]=[])).push(Number(r.id));});
            for(var kind of Object.keys(groups)){
                var endpoint=kind==='suggestions'?'/api/tickets/suggestions':'/api/tickets/approvals';
                var mapped=action==='reject'&&kind!=='checklist'?'delete':action;
                var res=await api(endpoint,{method:'POST',body:{kind:kind,action:mapped,ids:groups[kind]}});
                if(!res.success)throw new Error(res.error||'Review failed');
                skipped+=(res.duplicate_ids||[]).length+(res.problem_required_ids||[]).length;
                selected.clear();
            }
            var response=await fetch(location.href,{credentials:'same-origin'});
            if(!response.ok)throw new Error('Saved, but the table could not refresh. Reload to see current records.');
            var doc=new DOMParser().parseFromString(await response.text(),'text/html');
            var data=doc.getElementById('approval-records');
            if(!data)throw new Error('Saved. Refresh the page to reload records.');
            rows=JSON.parse(data.textContent);showToast(skipped?'Review saved; '+skipped+' duplicate or unlinked entries were skipped.':'Review saved.',skipped?'warning':'success');
        }catch(e){showToast(e.message,'error');}finally{busy=false;render();}
    }
    [search,category,status,context].forEach(function(el){el.addEventListener(el===search?'input':'change',function(){page=0;selected.clear();render();});});
    root.querySelector('#approval-all').onchange=function(){var checked=this.checked;visible.filter(actionable).forEach(function(r){if(checked)selected.add(key(r));else selected.delete(key(r));});render();};
    root.querySelector('#approval-body').onchange=function(e){if(e.target.matches('input[data-key]')){if(e.target.checked)selected.add(e.target.dataset.key);else selected.delete(e.target.dataset.key);render();}};
    root.querySelector('#approval-body').onclick=function(e){var b=e.target.closest('button[data-action]');if(b)review(b.dataset.action,[b.dataset.key]);};
    root.querySelector('#approval-approve').onclick=function(){review('approve',Array.from(selected));};root.querySelector('#approval-reject').onclick=function(){review('reject',Array.from(selected));};
    root.querySelector('#approval-prev').onclick=function(){page--;render();};root.querySelector('#approval-next').onclick=function(){page++;render();};render();
})();
</script>
<script type="application/json" id="approval-records"><?= json_encode($rows, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP) ?></script>
<?php require APP_ROOT . '/includes/layout_footer.php'; ?>
