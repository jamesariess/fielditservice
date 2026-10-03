<?php
/**
 * Real-page mobile crawl harness.
 *
 * Loads every application route inside a same-origin iframe at several phone
 * widths and reports where the layout actually breaks: content that pokes past
 * the right edge with nothing able to scroll it, content sitting off the left
 * edge, and JavaScript errors thrown by the page itself. This is the check that
 * catches "this page is broken on my phone" without opening 30 pages by hand.
 *
 * Requires the dev server that hosts the app and tests/ on one origin:
 *   php -S 127.0.0.1:8125 -t . _z_devserver_router.php   (with DB_* env set)
 * Then open http://127.0.0.1:8125/tests/mobile-crawl.php and press Run.
 *
 * Results land in window.__crawl for scripted use.
 */
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Mobile crawl — Field IT Support Hub</title>
<style>
  body{font:14px/1.5 -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;margin:0;padding:20px;background:#0f172a;color:#e2e8f0}
  h1{font-size:18px;margin:0 0 4px}
  p.sub{margin:0 0 16px;color:#94a3b8;font-size:13px}
  button{background:#2563eb;color:#fff;border:0;border-radius:8px;padding:9px 16px;font-size:14px;font-weight:600;cursor:pointer}
  button:disabled{opacity:.5;cursor:default}
  #status{margin:14px 0;font-size:13px;color:#94a3b8}
  table{border-collapse:collapse;width:100%;font-size:12.5px;background:#111c33;border-radius:10px;overflow:hidden}
  th,td{text-align:left;padding:8px 10px;border-bottom:1px solid #1e293b;vertical-align:top}
  th{background:#16233d;font-size:11px;text-transform:uppercase;letter-spacing:.04em;color:#93a4bd}
  tr.route-head td{background:#1b2942;font-weight:700;color:#cbd5e1}
  code{background:#0b1424;padding:1px 5px;border-radius:4px;color:#a5c8ff;font-size:12px}
  .ok{color:#4ade80;font-weight:700}
  .bad{color:#f87171;font-weight:700}
  .warn{color:#fbbf24;font-weight:700}
  #framehost{position:fixed;left:-20000px;top:0}
  iframe{border:0;background:#fff}
  #errors{margin-top:18px}
  details{background:#111c33;border-radius:10px;padding:10px 14px;margin-bottom:8px}
  summary{cursor:pointer;font-weight:600}
  pre{margin:8px 0 0;white-space:pre-wrap;font-size:12px;color:#fca5a5}
</style>
</head>
<body>
<h1>Mobile crawl</h1>
<p class="sub">Every route rendered in a same-origin iframe at phone widths. Anything listed is content the user cannot reach or read.</p>
<button id="run">Run crawl</button>
<div id="status">Idle.</div>
<div id="report"></div>
<div id="framehost"><iframe id="probe" width="320" height="900"></iframe></div>
<div id="errors"></div>

<script>
const ORIGIN = location.origin;
const WIDTHS = [320, 360, 390, 430];
const ROUTES = [
  '/', '/tickets', '/troubleshoot', '/troubleshoot/wizard', '/knowledge', '/knowledge/view',
  '/equipment', '/equipment/model', '/brand', '/commands', '/tools', '/ai', '/chat',
  '/documentation', '/profile',
  '/admin/users', '/admin/roles', '/admin/departments', '/admin/knowledge', '/admin/equipment',
  '/admin/ai', '/admin/audit', '/admin/statistics', '/admin/settings', '/admin/troubleshoot',
  '/admin/ticket-approvals'
];

const frame = document.getElementById('probe');
const statusEl = document.getElementById('status');
const reportEl = document.getElementById('report');
const errorsEl = document.getElementById('errors');

const sleep = ms => new Promise(r => setTimeout(r, ms));

function labelFor(el) {
  let s = el.tagName.toLowerCase();
  if (el.id) s += '#' + el.id;
  const cls = (el.getAttribute('class') || '').trim().split(/\s+/).filter(Boolean).slice(0, 3);
  if (cls.length) s += '.' + cls.join('.');
  const text = (el.textContent || '').trim().replace(/\s+/g, ' ').slice(0, 40);
  return s + (text ? '  "' + text + '"' : '');
}

function measure(doc, win, width) {
  const de = doc.documentElement;
  const body = doc.body;
  const vw = de.clientWidth;
  const out = { overflow: de.scrollWidth > vw + 1, scrollWidth: de.scrollWidth, clientWidth: vw, offscreen: [], clipped: [], selfOverflow: [], small: [], smallTap: [] };

  // How the element's ancestors handle horizontal excess:
  //   'scroll' - a user can reach it (a data table, a chart) => acceptable
  //   'clip'   - it is cut off with no way to see it        => a real bug
  // Note html/body count: this app sets overflow-x:hidden there, which hides
  // page-level blowouts instead of fixing them.
  const ancestorClip = el => {
    for (let p = el.parentElement; p; p = p.parentElement) {
      const cs = win.getComputedStyle(p);
      const ox = cs.overflowX;
      if (ox === 'auto' || ox === 'scroll') return 'scroll';
      if (ox === 'hidden' || ox === 'clip') return 'clip';
    }
    return 'none';
  };
  const hidden = el => {
    for (let n = el; n && n.nodeType === 1; n = n.parentElement) {
      const cs = win.getComputedStyle(n);
      if (cs.display === 'none' || cs.visibility === 'hidden' || cs.opacity === '0') return true;
    }
    return false;
  };

  const chainFor = el => {
    const parts = [];
    for (let n = el.parentElement; n && n !== body && parts.length < 3; n = n.parentElement) {
      const cls = (n.getAttribute('class') || '').trim().split(/\s+/)[0] || '';
      parts.push(n.tagName.toLowerCase() + (n.id ? '#' + n.id : '') + (cls ? '.' + cls : ''));
    }
    return parts.join(' < ');
  };

  const nodes = doc.querySelectorAll('body *');
  for (const el of nodes) {
    if (hidden(el)) continue;
    const r = el.getBoundingClientRect();
    if (r.width < 1 || r.height < 1) continue;
    const cs = win.getComputedStyle(el);
    const fixedLike = cs.position === 'fixed' || cs.position === 'absolute';
    if ((r.right > vw + 1 || r.left < -1) && !fixedLike) {
      const clip = ancestorClip(el);
      const item = { el: labelFor(el), left: Math.round(r.left), right: Math.round(r.right), w: Math.round(r.width), chain: chainFor(el) };
      if (clip === 'scroll') continue;                // reachable by scrolling
      if (clip === 'clip') out.clipped.push(item);    // cut off, unreachable
      else out.offscreen.push(item);                  // spills into the page
    }
    // Content wider than its own box that nothing clips or scrolls: it pushes the page.
    if (el.scrollWidth > el.clientWidth + 4 && cs.overflowX === 'visible' && el.clientWidth > 0) {
      const child = [...el.children].find(c => c.getBoundingClientRect().right > vw + 1);
      if (child) out.selfOverflow.push({ el: labelFor(el), scrollWidth: el.scrollWidth, clientWidth: el.clientWidth });
    }
    // Long words (addresses, serials, error text) that cannot wrap.
    const fs = parseFloat(cs.fontSize) || 16;
    if (fs < 10.5 && (el.textContent || '').trim().length > 3 && el.children.length === 0) {
      out.small.push({ el: labelFor(el), fontSize: fs });
    }
    if (/^(BUTTON|A)$/.test(el.tagName) && (r.height < 28 || r.width < 28)) {
      out.smallTap.push({ el: labelFor(el), w: Math.round(r.width), h: Math.round(r.height) });
    }
  }

  // Deduplicate: only the outermost offender per branch matters.
  const prune = list => {
    const seen = new Set();
    return list.filter(item => {
      if (seen.has(item.el)) return false;
      seen.add(item.el);
      return true;
    }).slice(0, 8);
  };
  out.offscreen = prune(out.offscreen);
  out.clipped = prune(out.clipped);
  out.selfOverflow = prune(out.selfOverflow);
  out.small = prune(out.small);
  out.smallTap = prune(out.smallTap);
  return out;
}

async function probe(route, width) {
  const res = await fetch(route, { credentials: 'same-origin' });
  const html = await res.text();
  if (res.status !== 200) return { route, width, status: res.status, error: 'HTTP ' + res.status };

  const withBase = /<head[^>]*>/i.test(html)
    ? html.replace(/<head([^>]*)>/i, '<head$1><base href="' + ORIGIN + '/">')
    : html.replace(/<html([^>]*)>/i, '<html$1><head><base href="' + ORIGIN + '/"></head>');

  // Record errors the page throws while it boots: listeners attached after the
  // load event would miss everything that matters.
  const spy = '<script>window.__errs=[];window.addEventListener("error",function(e){window.__errs.push(String(e.message||e.error||e))});window.addEventListener("unhandledrejection",function(e){window.__errs.push("rejected: "+String((e.reason&&e.reason.message)||e.reason))});<\/script>';
  const withSpy = withBase.replace(/<base[^>]*>/i, m => m + spy);

  const errors = [];
  frame.width = width;
  frame.height = 1200;

  const done = new Promise(resolve => {
    frame.onload = () => resolve();
  });
  frame.srcdoc = withSpy;
  await Promise.race([done, sleep(6000)]);
  const win = frame.contentWindow;
  const doc = win.document;
  // Give fonts, icon replacement and the page's own defer/async scripts a moment.
  await sleep(500);

  const m = measure(doc, win, width);
  m.route = route;
  m.width = width;
  m.status = 200;
  m.errors = ((win.__errs || []).concat(errors)).slice(0, 5);
  m.title = doc.title;
  return m;
}

async function run() {
  const btn = document.getElementById('run');
  btn.disabled = true;
  const results = [];
  let i = 0;
  const total = ROUTES.length * WIDTHS.length;
  for (const width of WIDTHS) {
    for (const route of ROUTES) {
      i++;
      statusEl.textContent = `Probing ${route} at ${width}px (${i}/${total})…`;
      try {
        const r = await probe(route, width);
        results.push(r);
      } catch (err) {
        results.push({ route, width, error: String(err && err.message || err) });
      }
      render(results);
    }
  }
  statusEl.textContent = `Done — ${results.length} page/width combinations.`;
  btn.disabled = false;
  window.__crawl = { results, at: new Date().toISOString() };
  return results;
}

function render(results) {
  const byRoute = new Map();
  for (const r of results) {
    if (!byRoute.has(r.route)) byRoute.set(r.route, []);
    byRoute.get(r.route).push(r);
  }
  let html = '<table><thead><tr><th>Page</th><th>Width</th><th>Problem</th></tr></thead><tbody>';
  const errorBlocks = [];
  for (const [route, rows] of byRoute) {
    html += `<tr class="route-head"><td colspan="3"><code>${route}</code></td></tr>`;
    for (const r of rows) {
      if (r.error) {
        html += `<tr><td>${route}</td><td>${r.width}</td><td class="bad">${r.error}</td></tr>`;
        continue;
      }
      const problems = [];
      if (r.overflow) problems.push(`<span class="bad">page scrolls sideways</span> (${r.scrollWidth}px in ${r.clientWidth}px)`);
      for (const o of r.offscreen) problems.push(`<span class="bad">spills past the screen</span> <code>${o.el}</code> right=${o.right}px${o.chain ? ' <em>' + o.chain + '</em>' : ''}`);
      for (const o of r.clipped) problems.push(`<span class="bad">cut off, unreachable</span> <code>${o.el}</code> right=${o.right}px${o.chain ? ' <em>' + o.chain + '</em>' : ''}`);
      for (const o of r.selfOverflow) problems.push(`<span class="warn">overflows own box</span> <code>${o.el}</code> ${o.scrollWidth}>${o.clientWidth}`);
      for (const o of r.small) problems.push(`<span class="warn">tiny text</span> ${o.fontSize}px <code>${o.el}</code>`);
      for (const o of r.smallTap) problems.push(`<span class="warn">small tap target</span> ${o.w}x${o.h} <code>${o.el}</code>`);
      html += `<tr><td>${route}</td><td>${r.width}</td><td>${problems.length ? problems.join('<br>') : '<span class="ok">ok</span>'}</td></tr>`;
      if (r.errors && r.errors.length) errorBlocks.push({ route, width: r.width, errors: r.errors, title: r.title });
    }
  }
  html += '</tbody></table>';
  reportEl.innerHTML = html;

  errorsEl.innerHTML = errorBlocks.length
    ? '<h3 style="font-size:14px">JavaScript errors</h3>' + errorBlocks.map(b =>
        `<details><summary>${b.route} @ ${b.width}px</summary><pre>${b.errors.join('\n')}</pre></details>`).join('')
    : '';
}

document.getElementById('run').addEventListener('click', run);
// Convenience for scripting: window.__runCrawl()
window.__runCrawl = run;
</script>
</body>
</html>
