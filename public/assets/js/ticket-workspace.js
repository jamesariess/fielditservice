(function(){
  function esc(value){ return String(value == null ? '' : value); }
  function labelStatus(status){ return String(status||'new').replace('_',' ').replace(/\b\w/g,function(c){return c.toUpperCase();}); }
  function syncTable(){
    var grid=document.getElementById('tickets-grid'), body=document.getElementById('tickets-table-body');
    if(!grid||!body)return;
    body.innerHTML='';
    Array.prototype.slice.call(grid.querySelectorAll('.ft-ticket-card')).forEach(function(card){
      var d=window.ttTicketData&&window.ttTicketData[card.dataset.id]||{};
      var tr=document.createElement('tr'); tr.dataset.cardId=card.dataset.id;
      var numberNode=card.querySelector('.ft-ticket-number, .ft-tnum');
      var action=document.createElement('button'); action.className='ticket-link'; action.type='button'; action.textContent=esc(d.ticket_number||(numberNode ? numberNode.textContent : '')||'#'+card.dataset.id); action.onclick=function(){openTicketDrawer(card.dataset.id);};
      var cells=[action, d.company_name||'Company not specified', d.problem_description||d.issue_title||d.task||'—', [d.device_type,d.model].filter(Boolean).join(' · ')||'—', d.owner_name||'—', d.priority||'—', labelStatus(d.status||card.dataset.status), d.created_at||'—'];
      cells.forEach(function(value,index){var td=document.createElement('td'); if(index===0)td.appendChild(value); else {td.textContent=esc(value); if(index===6){var span=document.createElement('span');span.className='status-pill status-'+(d.status||card.dataset.status);span.textContent=td.textContent;td.textContent='';td.appendChild(span);}} tr.appendChild(td);});
      var td=document.createElement('td'), btn=document.createElement('button'); btn.className='btn btn-sm btn-primary';btn.type='button';btn.textContent='Open';btn.onclick=function(){openTicketDrawer(card.dataset.id);};td.appendChild(btn);tr.appendChild(td);body.appendChild(tr);
    });
    var q=(document.getElementById('ticket-search')?.value||'').toLowerCase().trim(), filter=window.ticketActiveFilter||'';
    Array.prototype.slice.call(body.children).forEach(function(row){var card=grid.querySelector('[data-id="'+CSS.escape(row.dataset.cardId)+'"]');row.style.display=card&&card.style.display!=='none'?'':'none';});
    var empty=document.getElementById('ticket-table-empty'); if(empty)empty.hidden=Array.prototype.some.call(body.children,function(r){return r.style.display!=='none'});
  }
  window.ticketWorkspaceSync=syncTable;
  window.ticketWorkspaceInit=function(){
    var page=document.querySelector('.tickets-page'), toggle=document.getElementById('tickets-view-toggle'); if(!page)return;
    var saved=localStorage.getItem('fieldit-ticket-view')||'cards'; page.classList.toggle('table-view',saved==='table'); page.classList.toggle('cards-view',saved!=='table');
    if(toggle)toggle.querySelectorAll('button').forEach(function(b){b.classList.toggle('active',b.dataset.view===saved);b.onclick=function(){var v=b.dataset.view;localStorage.setItem('fieldit-ticket-view',v);page.classList.toggle('table-view',v==='table');page.classList.toggle('cards-view',v!=='table');toggle.querySelectorAll('button').forEach(function(x){x.classList.toggle('active',x===b);});syncTable();};});
    syncTable();
  };
})();
