window.App = {
  toast(type,msg){ const el=document.createElement('div');
    el.className=`toast align-items-center text-bg-${type} position-fixed bottom-0 end-0 m-3`;
    el.role='alert'; el.innerHTML=`<div class="d-flex"><div class="toast-body">${msg}</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>`;
    document.body.appendChild(el); new bootstrap.Toast(el,{delay:3500}).show();
  },
  async fetchJson(url,opts={}){
    const res=await fetch(url,{headers:{'X-Requested-With':'XMLHttpRequest',...opts.headers},...opts});
    const ct=res.headers.get('content-type')||'';
    if(!ct.includes('application/json')) throw new Error('Invalid response type');
    const data=await res.json(); if(!res.ok||data?.ok===false) throw new Error(data?.error||'Request failed'); return data;
  }
};
