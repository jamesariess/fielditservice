const fs=require('fs'),assert=require('assert'),{chromium}=require('playwright');
(async()=>{
const browser=await chromium.launch({headless:true,executablePath:'C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe'});
const css=fs.readFileSync('public/pages/chat.php','utf8').match(/<style>([\s\S]*?)<\/style>/)[1];
const fixture=`<style>body{margin:0;padding:16px;box-sizing:border-box;font-family:Arial}*{box-sizing:border-box}${css}</style><div class="team-chat-shell"><aside class="tc-side"><div class="tc-side-head"><h2>Team Chat</h2></div><div style="overflow:auto"><section class="tc-section"><a class="tc-conv active"><span class="tc-avatar tc-fieldmate">F</span><span class="tc-meta"><span class="tc-name">FieldMate</span><span class="tc-sub">IT support assistant</span></span></a></section><section class="tc-section"><h3 class="tc-section-title">Your department</h3><button class="tc-person">Technician</button></section></div></aside><main class="tc-chat"><header class="tc-chat-head"><span class="tc-avatar tc-fieldmate">F</span><div><h1>FieldMate</h1><p>IT support assistant</p></div><button class="tc-request tc-new" onclick="fieldMateNew()">New</button></header><div class="tc-messages" id="chat-messages"><div class="tc-empty" id="fieldmate-welcome"><h2>Hi, I'm FieldMate.</h2><p>What's happening with your device?</p></div></div><div class="tc-compose"><form onsubmit="fieldMateSend(event)"><input id="chat-msg-input"><button class="tc-send">Send</button></form></div></main></div>`;
for(const width of [1440,390,320])for(const dark of [false,true]){
const page=await browser.newPage({viewport:{width,height:850}});
await page.route('http://fieldmate.test/**',r=>r.fulfill({body:fixture.replace('<input id="chat-msg-input">','<textarea id="chat-msg-input" rows="1"></textarea>'),contentType:'text/html'}));await page.goto('http://fieldmate.test/');
await page.evaluate(()=>{window.APP_BASE='/';window.fieldMateUserId=1;window.requests=[];window.api=async(url,opts)=>{if(!opts)return {history:[]};requests.push(opts.body);return {response:'IT Bot: test answer <script>unsafe</script>',session_id:opts.body.session_id};};window.showToast=()=>{};});
await page.addScriptTag({path:'public/assets/js/fieldmate.js'});
await page.evaluate(()=>document.dispatchEvent(new Event('DOMContentLoaded')));
if(dark)await page.evaluate(()=>document.documentElement.classList.add('dark'));
await page.waitForFunction(()=>!document.querySelector('.tc-send').disabled);
await page.locator('#chat-msg-input').fill('Unexpected restarts');await page.locator('.tc-send').click();
await page.waitForFunction(()=>document.querySelectorAll('.tc-msg').length===2&&!document.querySelector('.tc-send').disabled);
assert((await page.locator('.tc-msg').last().innerText()).includes('FieldMate: test answer'));
assert.equal(await page.locator('.tc-bubble script').count(),0);
await page.locator('#chat-msg-input').fill('Still not working');await page.locator('.tc-send').click();
await page.waitForFunction(()=>requests.length===2&&!document.querySelector('.tc-send').disabled);
assert.equal(await page.evaluate(()=>requests[1].history.length),2);
const metrics=await page.evaluate(()=>({overflow:document.documentElement.scrollWidth>innerWidth,input:document.querySelector('.tc-compose').getBoundingClientRect().bottom,bg:getComputedStyle(document.querySelector('.tc-chat')).backgroundColor}));
assert(!metrics.overflow);assert(metrics.input<=850);if(dark)assert.equal(metrics.bg,'rgb(17, 24, 39)');
if(width<800){assert.equal(await page.locator('.tc-side').isVisible(),false);await page.evaluate(()=>document.querySelector('.team-chat-shell').classList.add('people-open'));assert.equal(await page.locator('.tc-side').isVisible(),true);await page.evaluate(()=>document.querySelector('.team-chat-shell').classList.remove('people-open'));}
await page.screenshot({path:`tests/fieldmate-${width}-${dark?'dark':'light'}.png`});
if(width<800){await page.setViewportSize({width,height:500});await page.waitForTimeout(100);assert((await page.locator('.tc-compose').boundingBox()).y+(await page.locator('.tc-compose').boundingBox()).height<=500);}
await page.evaluate(()=>{window.api=async()=>{throw new Error('offline')};});await page.locator('#chat-msg-input').fill('Keep this message');await page.locator('.tc-send').click();await page.waitForFunction(()=>!document.querySelector('.tc-send').disabled);assert.equal(await page.locator('#chat-msg-input').inputValue(),'Keep this message');
console.log(`PASS ${width} ${dark?'dark':'light'}: send, history, safe rendering, retry text, composer bounds`);await page.close();}
await browser.close();
})().catch(e=>{console.error(e);process.exitCode=1;});
