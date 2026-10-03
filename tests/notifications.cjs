const fs=require('fs'),vm=require('vm'),assert=require('assert');
const source=fs.readFileSync('public/assets/js/app.js','utf8');
const section=source.slice(source.indexOf('function toggleNotifications'),source.indexOf('// ==================== Toasts'));
const els={'notif-list':{innerHTML:''},'notif-dot':{style:{},classList:{add(){},remove(){},toggle(){}}}};
const c={URL,APP_BASE:'/public/',location:{origin:'https://example.test'},document:{getElementById:id=>els[id],addEventListener(){},querySelectorAll:()=>[]},setInterval(){},ttEsc:s=>String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/"/g,'&quot;'),showToast(){},api:async()=>({notifications:[{id:3,title:'<script>',message:'<img>',url:'/tickets',is_read:'0'}],unread_count:4})};
vm.createContext(c);vm.runInContext(section,c);
assert.equal(c.notificationUrl('/tickets'),'https://example.test/public/tickets');
assert.equal(c.notificationUrl('/public/tickets'),'https://example.test/public/tickets');
assert.equal(c.notificationUrl('https://other.test/'),'https://example.test/public/notifications');
c.loadNotifications();
setTimeout(()=>{
 assert(els['notif-list'].innerHTML.includes('&lt;script>'));
 assert(els['notif-list'].innerHTML.includes('notif-dot-unread'));
 assert.equal(els['notif-dot'].style.display,'');
 c.api=async()=>({notifications:[],unread_count:0});c.loadNotifications();
 setTimeout(()=>{assert.equal(els['notif-dot'].style.display,'none');console.log('PASS: safe text, string read flags, authoritative unread count, empty reset, deployment links, external-link rejection');},0);
},0);
