const fs = require('fs');
const { chromium } = require('playwright');

(async () => {
  const css = [
    fs.readFileSync('public/assets/css/app.css', 'utf8'),
    fs.readFileSync('public/assets/css/ticket-management.css', 'utf8')
  ].join('\n');

  const html = `<!doctype html>
<html>
<head>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <style>${css}</style>
</head>
<body>
  <header class="app-header">Header</header>
  <main class="app-main"><div class="page-content">Page</div></main>
  <nav class="bottom-nav">
    <a class="active"><i></i><span>Home</span></a>
    <a><i></i><span>Fix</span></a>
    <a><i></i><span>AI</span></a>
    <a><i></i><span>KB</span></a>
    <a><i></i><span>Tickets</span></a>
  </nav>

  <div id="generic" class="modal-overlay open" data-modal-case>
    <div class="backdrop"></div>
    <div class="modal-panel">
      <div><h2>Create User</h2><button>x</button></div>
      <form><input><select><option>Admin</option></select><div><button>Cancel</button><button>Create</button></div></form>
    </div>
  </div>

  <div id="manual" class="manual-step-modal is-open" data-modal-case>
    <div class="manual-step-backdrop"></div>
    <form class="manual-step-panel">
      <div class="manual-step-head"><div><h2>Add Troubleshooting Step</h2><p>Add it directly</p></div><button class="manage-close">x</button></div>
      <label><select><option>Select a problem</option></select></label>
      <div class="manual-step-entry-group"><textarea></textarea></div>
      <div class="manual-step-actions"><button>Cancel</button><button>Save</button></div>
    </form>
  </div>

  <div id="kb-editor-overlay" data-modal-case></div>
  <div id="kb-editor-panel" data-modal-case>
    <div><h2>New Article</h2><button>x</button></div>
    <form id="kb-editor-form"><input><textarea></textarea><div><button>Cancel</button><button>Save</button></div></form>
  </div>

  <div id="eq-editor-overlay" data-modal-case></div>
  <div id="eq-editor-panel" data-modal-case>
    <div><h2>Add Equipment</h2><button>x</button></div>
    <form id="eq-editor-form"><input><textarea></textarea><div><button>Cancel</button><button>Save</button></div></form>
  </div>

  <div class="audit-modal" data-modal-case>
    <div class="audit-modal-backdrop"></div>
    <section class="audit-modal-panel">
      <header><h2>Event details</h2><button>x</button></header>
      <dl class="audit-detail-list"><dt>Action</dt><dd>Invite</dd></dl>
    </section>
  </div>
</body>
</html>`;

  const browser = await chromium.launch({
    headless: true,
    executablePath: process.env.CHROME_PATH || undefined
  });
  const page = await browser.newPage({ viewport: { width: 390, height: 844 }, isMobile: true });
  await page.setContent(html);

  const result = await page.evaluate(() => {
    const rect = (selector) => {
      const el = document.querySelector(selector);
      const r = el.getBoundingClientRect();
      const style = getComputedStyle(el);
      return { top: r.top, bottom: r.bottom, height: r.height, width: r.width, display: style.display, z: style.zIndex };
    };
    const cases = [
      ['generic', '#generic', '#generic .modal-panel'],
      ['manual', '#manual', '.manual-step-panel'],
      ['kb', '#kb-editor-panel,#kb-editor-overlay', '#kb-editor-panel'],
      ['eq', '#eq-editor-panel,#eq-editor-overlay', '#eq-editor-panel'],
      ['audit', '.audit-modal', '.audit-modal-panel']
    ];
    const items = {};
    document.querySelectorAll('[data-modal-case]').forEach((el) => { el.style.display = 'none'; });
    for (const [name, showSelector, panelSelector] of cases) {
      document.querySelectorAll('[data-modal-case]').forEach((el) => { el.style.display = 'none'; });
      document.querySelectorAll(showSelector).forEach((el) => { el.style.display = el.classList.contains('modal-overlay') || el.id.endsWith('overlay') || el.classList.contains('audit-modal') ? 'flex' : 'flex'; });
      items[name] = rect(panelSelector);
    }
    return { header: rect('.app-header'), nav: rect('.bottom-nav'), navStyle: getComputedStyle(document.querySelector('.bottom-nav')).display, items };
  });

  await browser.close();

  const failures = [];
  if (result.navStyle === 'none') failures.push('bottom nav hidden');
  for (const [name, r] of Object.entries(result.items)) {
    if (r.top < 63) failures.push(`${name} starts under header: ${r.top}`);
    if (r.bottom > result.nav.top + 1) failures.push(`${name} covers bottom nav: ${r.bottom} > ${result.nav.top}`);
    if (r.width > 390) failures.push(`${name} too wide: ${r.width}`);
  }

  console.log(JSON.stringify(result, null, 2));
  if (failures.length) {
    console.error('FAIL ' + failures.join('; '));
    process.exit(1);
  }
  console.log('mobile modal render checks passed');
})().catch((error) => {
  console.error(error);
  process.exit(1);
});
