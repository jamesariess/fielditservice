const fs=require('fs'),assert=require('assert'),{chromium}=require('playwright');
const css=fs.readFileSync('public/assets/css/dark-compat.css','utf8');
const html=`<main class="page-content"><h1 id="title" style="color:#111827">Equipment Brand</h1><div id="cat-tabs"><button class="cmd-btn" style="background:#fff;color:#475569">Network</button><button class="cmd-btn active" style="background:#2563eb;color:#fff">All</button></div><div id="cmds-list"><p id="description" style="color:#475569">Display current IP configuration</p><div id="detail" style="background:#f8fafc;padding:12px"><span style="color:#475569">When troubleshooting network connectivity</span><code style="color:#1d4ed8">ipconfig /all</code></div></div><div id="tools-grid"><h3 style="color:#0f172a">Multimeter</h3><div id="warning" style="background:#fffbeb;color:#b45309;padding:12px">Do not touch PSU internals.</div></div><div id="device-table-wrap"><div style="background:#fff"><table><thead><tr style="background:#f8fafc"><th style="color:#64748b">Model</th></tr></thead><tbody><tr class="device-row"><td style="color:#111827">OptiPlex 7090</td></tr></tbody></table></div></div></main><div id="dv-panel" style="background:#fff"><h2 style="color:#111827">Device Details</h2></div>`;
(async()=>{
const browser=await chromium.launch({headless:true,executablePath:'C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe'});
for(const width of [1440,390]){
const page=await browser.newPage({viewport:{width,height:900}});
await page.setContent(`<style>body{background:#0b1320;font:14px Arial;padding:20px}.page-content>div{margin:16px 0}button,td,th{padding:12px}${css}</style>${html}`);
await page.evaluate(()=>document.documentElement.classList.add('dark'));
const colors=await page.evaluate(()=>Object.fromEntries(['title','description','detail','warning','dv-panel'].map(id=>[id,{color:getComputedStyle(document.getElementById(id)).color,bg:getComputedStyle(document.getElementById(id)).backgroundColor}])));
assert.equal(colors.title.color,'rgb(243, 244, 246)');assert.equal(colors.description.color,'rgb(186, 199, 214)');assert.equal(colors.detail.bg,'rgb(36, 50, 68)');assert.equal(colors.warning.bg,'rgb(58, 45, 24)');assert.equal(colors['dv-panel'].bg,'rgb(24, 34, 48)');
assert.equal(await page.locator('.cmd-btn.active').evaluate(e=>getComputedStyle(e).color),'rgb(255, 255, 255)');
await page.screenshot({path:`tests/dark-audit-${width}.png`});
await page.evaluate(()=>document.documentElement.classList.remove('dark'));assert.equal(await page.locator('#detail').evaluate(e=>getComputedStyle(e).backgroundColor),'rgb(248, 250, 252)');
console.log(`PASS ${width}: text, surfaces, warnings, viewer, selected controls, light-mode restoration`);await page.close();
}await browser.close();
})().catch(e=>{console.error(e);process.exitCode=1;});
