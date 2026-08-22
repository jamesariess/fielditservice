<?php
$page_title = 'Command Reference';
$active_menu = 'commands';
require APP_ROOT . '/includes/layout_header.php';
?>

<div>
    <div style="margin-bottom:24px;">
        <h1 style="font-size:22px;font-weight:800;color:#111827;letter-spacing:-0.02em;">CMD & PowerShell Reference</h1>
        <p style="font-size:13px;color:#64748b;margin-top:2px;">Essential commands for field IT troubleshooting</p>
    </div>

    <!-- Search & Tabs -->
    <div style="display:flex;gap:10px;margin-bottom:20px;flex-wrap:wrap;align-items:center;">
        <div style="flex:1;min-width:240px;position:relative;">
            <i data-lucide="terminal" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);width:15px;height:15px;color:#94a3b8;"></i>
            <input type="text" id="cmd-search" placeholder="Search commands..." class="form-input" style="padding-left:36px;" oninput="filterCmds(this.value)">
        </div>
        <div style="display:flex;gap:4px;flex-wrap:wrap;" id="cat-tabs">
            <?php
            $cats = [['All','all'],['Network','network'],['System','system'],['Disk','disk'],['Process','process']];
            foreach ($cats as $i => $c): ?>
                <button onclick="filterCat('<?= $c[1] ?>')" class="cmd-btn <?= $i===0?'active':'' ?>" data-cat="<?= $c[1] ?>" style="padding:6px 14px;border-radius:8px;font-size:12px;font-weight:600;border:1px solid #e5e7eb;background:<?= $i===0?'#2563eb':'#fff' ?>;color:<?= $i===0?'#fff':'#475569' ?>;cursor:pointer;transition:all 0.15s;">
                    <?= $c[0] ?>
                </button>
            <?php endforeach; ?>
        </div>
    </div>

    <div id="cmds-list" style="display:flex;flex-direction:column;gap:12px;">
        <?php
        $cmds = [];
        $cmds[] = ['cmd'=>'ipconfig','cat'=>'network','risk'=>'safe','desc'=>'Display current IP configuration','when'=>'When troubleshooting network connectivity issues','example'=>'ipconfig /all','output'=>'Shows IP address, subnet mask, default gateway, DNS servers','errors'=>'Media disconnected means cable not connected','next'=>'If no IP address: check DHCP, try ipconfig /renew'];
        $cmds[] = ['cmd'=>'ipconfig /release','cat'=>'network','risk'=>'safe','desc'=>'Release current IP address','when'=>'IP address conflict or DHCP issues','example'=>'ipconfig /release','output'=>'IP address released, adapter shows 0.0.0.0','errors'=>'','next'=>'Follow with ipconfig /renew to get new IP'];
        $cmds[] = ['cmd'=>'ipconfig /renew','cat'=>'network','risk'=>'safe','desc'=>'Request new IP from DHCP server','when'=>'After ipconfig /release or when no IP assigned','example'=>'ipconfig /renew','output'=>'New IP address assigned from DHCP server','errors'=>'169.254.x.x means DHCP failure','next'=>'Check network cable and DHCP server connectivity'];
        $cmds[] = ['cmd'=>'ipconfig /flushdns','cat'=>'network','risk'=>'safe','desc'=>'Clear DNS resolver cache','when'=>'DNS resolution fails or DNS cache is stale','example'=>'ipconfig /flushdns','output'=>'Successfully flushed DNS Resolver Cache','errors'=>'','next'=>'Test with nslookup and ping by hostname'];
        $cmds[] = ['cmd'=>'ping','cat'=>'network','risk'=>'safe','desc'=>'Test connectivity to a host','when'=>'Verifying network connectivity','example'=>'ping 8.8.8.8','output'=>'Reply from host with TTL and time values','errors'=>'Request timed out means host unreachable','next'=>'127.0.0.1 fail = stack issue; gateway fail = local network; 8.8.8.8 fail = internet; hostname fail = DNS'];
        $cmds[] = ['cmd'=>'nslookup','cat'=>'network','risk'=>'safe','desc'=>'Query DNS records','when'=>'DNS resolution is suspected to be failing','example'=>'nslookup google.com','output'=>'DNS server address and resolved IP address','errors'=>'DNS request timed out means DNS server issue','next'=>'Try different DNS server like 8.8.8.8'];
        $cmds[] = ['cmd'=>'tracert','cat'=>'network','risk'=>'safe','desc'=>'Trace route to destination','when'=>'Finding where network path breaks','example'=>'tracert google.com','output'=>'List of hops with response times','errors'=>'Timeout at intermediate hop','next'=>'Identify the hop where timeouts begin to locate the problem'];
        $cmds[] = ['cmd'=>'arp -a','cat'=>'network','risk'=>'safe','desc'=>'Display ARP table','when'=>'Checking for IP conflicts or ARP issues','example'=>'arp -a','output'=>'List of IP-to-MAC address mappings','errors'=>'','next'=>'Check for duplicate IP entries indicating IP conflict'];
        $cmds[] = ['cmd'=>'sfc /scannow','cat'=>'system','risk'=>'caution','desc'=>'Scan and repair system files','when'=>'Windows system files may be corrupted','example'=>'sfc /scannow (run as Administrator)','output'=>'Verification phase and repair results','errors'=>'Windows Resource Protection could not perform','next'=>'If errors found: run DISM /Online /Cleanup-Image /RestoreHealth first'];
        $cmds[] = ['cmd'=>'DISM /Online /Cleanup-Image /RestoreHealth','cat'=>'system','risk'=>'caution','desc'=>'Repair Windows image','when'=>'sfc /scannow cannot repair corrupted files','example'=>'DISM /Online /Cleanup-Image /RestoreHealth','output'=>'Image repair progress and result','errors'=>'','next'=>'After DISM completes, run sfc /scannow again'];
        $cmds[] = ['cmd'=>'systeminfo','cat'=>'system','risk'=>'safe','desc'=>'Display system information','when'=>'Gathering device details for troubleshooting','example'=>'systeminfo','output'=>'OS version, RAM, processor, network adapters, hotfixes','errors'=>'','next'=>'Record relevant info for escalation or documentation'];
        $cmds[] = ['cmd'=>'chkdsk','cat'=>'disk','risk'=>'caution','desc'=>'Check disk for errors','when'=>'Disk errors or file system corruption suspected','example'=>'chkdsk C: /f /r','output'=>'Disk check stages and repair results','errors'=>'Volume in use means needs restart','next'=>'If /f requires restart: type Y to schedule on next boot'];
        $cmds[] = ['cmd'=>'taskmgr','cat'=>'process','risk'=>'safe','desc'=>'Open Task Manager','when'=>'Checking resource usage or killing processes','example'=>'taskmgr','output'=>'Task Manager window with processes and performance','errors'=>'','next'=>'Check CPU, Memory, Disk columns for high usage processes'];
        $cmds[] = ['cmd'=>'taskkill','cat'=>'process','risk'=>'caution','desc'=>'Kill a process','when'=>'Unresponsive application needs to be closed','example'=>'taskkill /PID 1234 /F','output'=>'Process terminated successfully','errors'=>'Access denied means needs admin privileges','next'=>'Use with caution - unsaved data will be lost'];

        $riskMap = [
            'safe' => ['color'=>'#16a34a','bg'=>'#f0fdf4','label'=>'Safe'],
            'caution' => ['color'=>'#d97706','bg'=>'#fffbeb','label'=>'Caution'],
        ];

        foreach ($cmds as $c):
            $r = $riskMap[$c['risk']];
        ?>
            <div class="card" data-cmd="<?= e(strtolower($c['cmd'])) ?>" data-cat="<?= $c['cat'] ?>">
                <div class="card-body" style="display:flex;gap:16px;align-items:flex-start;">
                    <div style="flex:1;min-width:0;">
                        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:8px;">
                            <code style="padding:4px 10px;background:#f1f5f9;border-radius:6px;font-size:13px;font-weight:700;color:#1d4ed8;font-family:monospace;"><?= e($c['cmd']) ?></code>
                            <span class="badge" style="background:<?= $r['bg'] ?>;color:<?= $r['color'] ?>;gap:4px;"><span class="risk-dot <?= $c['risk'] ?>"></span> <?= $r['label'] ?></span>
                            <span class="badge badge-gray"><?= $c['cat'] ?></span>
                        </div>
                        <p style="font-size:13px;color:#475569;margin-bottom:10px;"><?= $c['desc'] ?></p>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                            <div style="padding:10px 12px;background:#f8fafc;border-radius:8px;border:1px solid #f1f5f9;">
                                <div style="font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:3px;">When to use</div>
                                <div style="font-size:12px;color:#475569;line-height:1.5;"><?= $c['when'] ?></div>
                            </div>
                            <div style="padding:10px 12px;background:#f8fafc;border-radius:8px;border:1px solid #f1f5f9;">
                                <div style="font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:3px;">Example</div>
                                <code style="font-size:12px;color:#1d4ed8;font-family:monospace;"><?= e($c['example']) ?></code>
                            </div>
                            <div style="padding:10px 12px;background:#f8fafc;border-radius:8px;border:1px solid #f1f5f9;">
                                <div style="font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:3px;">Expected Output</div>
                                <div style="font-size:12px;color:#475569;line-height:1.5;"><?= $c['output'] ?></div>
                            </div>
                            <div style="padding:10px 12px;background:#f8fafc;border-radius:8px;border:1px solid #f1f5f9;">
                                <div style="font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:3px;">Next Step</div>
                                <div style="font-size:12px;color:#475569;line-height:1.5;"><?= $c['next'] ?></div>
                            </div>
                        </div>
                        <?php if ($c['errors']): ?>
                            <div class="alert alert-warning" style="margin-top:10px;padding:10px 14px;">
                                <i data-lucide="alert-triangle" style="width:14px;height:14px;"></i>
                                <div><span style="font-weight:600;font-size:12px;">Common Errors: </span><span style="font-size:12px;"><?= $c['errors'] ?></span></div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <button onclick="copyCmd('<?= e($c['cmd']) ?>')" style="width:32px;height:32px;border-radius:8px;border:1px solid #e5e7eb;background:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;flex-shrink:0;transition:all 0.15s;color:#94a3b8;" onmouseover="this.style.borderColor='#2563eb';this.style.color='#2563eb'" onmouseout="this.style.borderColor='#e5e7eb';this.style.color='#94a3b8'">
                        <i data-lucide="copy" style="width:14px;height:14px;"></i>
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

?>
<script>
function filterCmds(q) {
    var query = q.toLowerCase();
    document.querySelectorAll('#cmds-list .card').forEach(function(c) {
        c.style.display = c.dataset.cmd.indexOf(query) >= 0 ? '' : 'none';
    });
}
function filterCat(cat) {
    document.querySelectorAll('.cmd-btn').forEach(function(b) {
        var isActive = b.dataset.cat === cat;
        b.style.background = isActive ? '#2563eb' : '#fff';
        b.style.color = isActive ? '#fff' : '#475569';
        b.style.borderColor = isActive ? '#2563eb' : '#e5e7eb';
    });
    document.querySelectorAll('#cmds-list .card').forEach(function(c) {
        c.style.display = (cat === 'all' || c.dataset.cat === cat) ? '' : 'none';
    });
}
function copyCmd(cmd) {
    navigator.clipboard.writeText(cmd).then(function() { showToast('Command copied: ' + cmd, 'success'); });
}
</script>
<?php require APP_ROOT . '/includes/layout_footer.php'; ?>
