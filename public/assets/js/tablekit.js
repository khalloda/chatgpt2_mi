/* Minimal table kit: pin offsets, tooltips, exports hook, and density */
(function(){
  function setStickyOffsets(table){
    const startCols=[...table.querySelectorAll('th.sticky-start')];
    const endCols=[...table.querySelectorAll('th.sticky-end')];
    let acc=0; startCols.forEach(th=>{ th.style.setProperty('--sticky-offset', acc+'px'); 
      const w=th.getBoundingClientRect().width; acc+=w; th.classList.add('sticky-shadow-start'); });
    acc=0; endCols.reverse().forEach(th=>{ th.style.setProperty('--sticky-offset', acc+'px');
      const w=th.getBoundingClientRect().width; acc+=w; th.classList.add('sticky-shadow-end'); });
  }
  function initTooltips(root=document){
    [...root.querySelectorAll('[data-bs-toggle="tooltip"]')].forEach(el=> new bootstrap.Tooltip(el));
  }
  function attachExports(table){
    const btn = table.parentElement?.querySelector('[data-export]');
    if(!btn) return;
    btn.addEventListener('click', async ()=>{
      // lazy-load SheetJS only when exporting
      if(!window.XLSX){ const s=document.createElement('script'); s.src='https://cdn.jsdelivr.net/npm/xlsx@0.20.3/dist/xlsx.full.min.js';
        await new Promise(r=>{ s.onload=r; document.head.appendChild(s); }); }
      const wb=XLSX.utils.book_new();
      const rows=[...table.rows].map(r=>[...r.cells].filter(c=>getComputedStyle(c).display!=='none').map(c=>c.innerText.trim()));
      const ws=XLSX.utils.aoa_to_sheet(rows);
      XLSX.utils.book_append_sheet(wb,ws,'Export'); XLSX.writeFile(wb, (table.dataset.exportName||'export')+'.xlsx');
    });
  }
  function initTable(table){
    setStickyOffsets(table);
    attachExports(table);
    initTooltips(table);
    // Recompute on resize
    let t; window.addEventListener('resize', ()=>{ clearTimeout(t); t=setTimeout(()=>setStickyOffsets(table), 120); });
  }
  window.TableKit={ initAll(){
      document.querySelectorAll('table.table-sticky').forEach(initTable);
    }, init: initTable };
})();
document.addEventListener('DOMContentLoaded', ()=> TableKit.initAll());
