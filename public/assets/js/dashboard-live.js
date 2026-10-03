(function () {
    'use strict';
    var status = document.getElementById('dashboard-refresh-status');
    if (!status) return;
    var busy = false, stopped = false, timer, delay = 15000, lastContent;
    var colors = ['#3b82f6', '#8b5cf6', '#10b981', '#f59e0b', '#ef4444'];
    function escape(value) {
        var span = document.createElement('span');
        span.textContent = value == null ? '' : String(value);
        return span.innerHTML;
    }
    function render(data) {
        var content = JSON.stringify([data.stats, data.recent, data.top_issues]);
        if (content === lastContent) return;
        document.querySelectorAll('[data-dashboard-stat]').forEach(function (el) {
            el.textContent = Number(data.stats[el.dataset.dashboardStat] || 0).toLocaleString();
        });
        document.getElementById('dashboard-pending').textContent = data.stats.pending_tickets + ' pending';
        document.getElementById('dashboard-escalated').textContent = data.stats.escalated_count + ' escalated';
        document.getElementById('dashboard-recent').innerHTML = data.recent.map(function (row) {
            var solved = ['solved', 'completed'].includes(row.status);
            var badge = solved ? 'badge-green' : row.status === 'escalated' ? 'badge-red' : 'badge-blue';
            var label = solved ? 'Solved' : String(row.status || 'new').replace(/_/g, ' ');
            return '<a class="ticket-row" style="text-decoration:none;" href="' + escape(APP_BASE + 'tickets') + '">' +
                '<div style="flex:1;min-width:0;"><div class="dash-text" style="font-size:13px;font-weight:600;overflow-wrap:anywhere;">' + escape(row.issue_title) + '</div>' +
                '<div class="dash-text-muted" style="font-size:12px;">' + escape([row.device_type, row.device_model].filter(Boolean).join(' ')) + '</div></div>' +
                '<span class="badge ' + badge + '" style="text-transform:capitalize;flex-shrink:0;">' + escape(label) + '</span></a>';
        }).join('') || '<p class="dash-text-muted" style="padding:16px 0;">No recent sessions.</p>';
        var max = Math.max.apply(null, [1].concat(data.top_issues.map(function (row) { return Number(row.cnt); })));
        document.getElementById('dashboard-issues').innerHTML = data.top_issues.map(function (row, i) {
            var count = Math.max(0, Number(row.cnt) || 0);
            return '<div class="bar-chart-row"><div class="bar-chart-label">' + escape(row.title) + '</div>' +
                '<div class="bar-chart-track"><div class="bar-chart-fill" style="width:' + (count / max * 100) + '%;background:' + colors[i % colors.length] + ';"></div></div>' +
                '<div class="bar-chart-count">' + count + '</div></div>';
        }).join('') || '<p class="dash-text-muted">No sessions this week.</p>';
        lastContent = content;
    }
    function schedule() {
        clearTimeout(timer);
        if (!stopped && !document.hidden) timer = setTimeout(refresh, delay);
    }
    async function refresh() {
        if (busy || stopped || document.hidden) return;
        busy = true;
        var controller = new AbortController();
        var timeout = setTimeout(function () { controller.abort(); }, 10000);
        try {
            var response = await fetch(APP_BASE + 'api/dashboard/index.php', {
                credentials: 'same-origin', cache: 'no-store', signal: controller.signal,
                headers: {Accept: 'application/json'}
            });
            if (response.status === 401 || response.status === 403) {
                stopped = true;
                throw new Error('Sign in again to refresh the dashboard.');
            }
            if (!response.ok) throw new Error('Dashboard refresh failed. Retrying automatically.');
            var data = await response.json();
            if (!data.stats || !Array.isArray(data.recent) || !Array.isArray(data.top_issues)) throw new Error('Invalid dashboard response. Retrying automatically.');
            render(data);
            delay = 15000;
            status.textContent = 'Updated ' + new Date().toLocaleTimeString([], {hour: '2-digit', minute: '2-digit', second: '2-digit'});
        } catch (error) {
            status.textContent = error.name === 'AbortError' ? 'Dashboard refresh timed out. Retrying automatically.' : error.message;
            delay = Math.min(delay * 2, 60000);
        } finally {
            clearTimeout(timeout);
            busy = false;
            schedule();
        }
    }
    document.addEventListener('visibilitychange', function () {
        clearTimeout(timer);
        if (!document.hidden) refresh();
    });
    window.addEventListener('focus', refresh);
    window.addEventListener('online', refresh);
    refresh();
}());
