/* CSSI Lead Magnet — exit-intent popup pentru Ghidul de Securitate (task #94)
   Reguli: max 1 afișare / 14 zile (localStorage), nu pe /admin, nu după download. */
(function(){
  'use strict';
  var KEY='cssi_lm_v1';
  try{
    if(location.pathname.indexOf('/admin')===0) return;
    var st=JSON.parse(localStorage.getItem(KEY)||'{}');
    if(st.done) return;                                   // a descărcat deja
    if(st.last && (Date.now()-st.last) < 14*24*3600*1000) return; // cap 14 zile
  }catch(e){ return; }

  var shown=false;

  function track(ev){ try{ window.dataLayer=window.dataLayer||[]; window.dataLayer.push({event:ev}); }catch(e){} }

  function show(){
    if(shown) return; shown=true;
    try{ var s=JSON.parse(localStorage.getItem(KEY)||'{}'); s.last=Date.now(); localStorage.setItem(KEY,JSON.stringify(s)); }catch(e){}
    var ov=document.createElement('div');
    ov.id='cssiLmOverlay';
    ov.style.cssText='position:fixed;inset:0;background:rgba(11,26,46,.72);z-index:99999;display:flex;align-items:center;justify-content:center;padding:16px;backdrop-filter:blur(2px);';
    ov.innerHTML=
      '<div style="background:#fff;max-width:430px;width:100%;border-radius:16px;overflow:hidden;box-shadow:0 24px 64px rgba(0,0,0,.35);font-family:Sora,system-ui,sans-serif;position:relative;">'+
        '<button id="cssiLmClose" aria-label="Închide" style="position:absolute;top:10px;right:12px;background:rgba(255,255,255,.18);border:none;color:#fff;font-size:20px;line-height:1;cursor:pointer;border-radius:50%;width:30px;height:30px;">×</button>'+
        '<div style="background:linear-gradient(135deg,#0b1a2e,#1a4b8c);padding:26px 24px 20px;color:#fff;">'+
          '<div style="font-size:11px;font-weight:700;letter-spacing:.08em;color:#f5a623;text-transform:uppercase;margin-bottom:8px;">Ghid gratuit · PDF</div>'+
          '<div style="font-size:20px;font-weight:800;line-height:1.25;">Securitatea Locuinței Tale,<br>Pas cu Pas (2026)</div>'+
        '</div>'+
        '<div style="padding:20px 24px 24px;">'+
          '<ul style="margin:0 0 16px;padding:0;list-style:none;color:#334155;font-size:13.5px;line-height:1.9;">'+
            '<li>✅ Checklist 15 pași înainte de plecare</li>'+
            '<li>✅ Cele 10 întrebări pentru orice firmă de securitate</li>'+
            '<li>✅ Costuri reale Brașov + cele 5 greșeli clasice</li>'+
          '</ul>'+
          '<a id="cssiLmDl" href="/downloads/ghid-securitate-locuinta-cssi-2026.pdf" target="_blank" rel="noopener" style="display:block;text-align:center;background:#e63022;color:#fff;font-weight:700;font-size:14.5px;padding:13px 16px;border-radius:10px;text-decoration:none;box-shadow:0 6px 18px rgba(230,48,34,.35);">📥 Descarcă ghidul gratuit</a>'+
          '<div style="text-align:center;margin-top:12px;font-size:12px;color:#64748b;">sau sună direct: <a href="tel:0752288400" style="color:#1a4b8c;font-weight:700;text-decoration:none;">0752 288 400</a></div>'+
        '</div>'+
      '</div>';
    document.body.appendChild(ov);
    track('lead_magnet_shown');
    document.getElementById('cssiLmClose').onclick=function(){ ov.remove(); track('lead_magnet_closed'); };
    ov.addEventListener('click',function(e){ if(e.target===ov){ ov.remove(); track('lead_magnet_closed'); } });
    document.getElementById('cssiLmDl').addEventListener('click',function(){
      try{ var s=JSON.parse(localStorage.getItem(KEY)||'{}'); s.done=1; localStorage.setItem(KEY,JSON.stringify(s)); }catch(e){}
      track('lead_magnet_download');
      setTimeout(function(){ ov.remove(); },400);
    });
  }

  var isMobile=/Mobi|Android/i.test(navigator.userAgent);
  if(isMobile){
    // mobil: după 40s sau la 65% scroll
    var t=setTimeout(show,40000);
    window.addEventListener('scroll',function onSc(){
      var p=(window.scrollY+window.innerHeight)/document.documentElement.scrollHeight;
      if(p>0.65){ clearTimeout(t); window.removeEventListener('scroll',onSc); show(); }
    },{passive:true});
  }else{
    // desktop: exit-intent (mouse iese pe sus), activ după minim 12s pe pagină
    var armed=false; setTimeout(function(){armed=true;},12000);
    document.addEventListener('mouseout',function(e){
      if(armed && !e.relatedTarget && e.clientY<=0) show();
    });
  }
})();
