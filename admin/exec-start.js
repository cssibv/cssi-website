// ============================================================
// CSSI Portal — Data de început la intrarea în Execuție
// Fereastră comună folosită oriunde un proiect trece în Execuție
// (CRM, Board Proiecte, Proiectare, Dosar) și în CRM → Neprogramat.
//
//   cssiExecStart({titlu, subtitlu}) → Promise<{data, zile[], ora, durata, atribuiri} | null>
//     null = utilizatorul a renunțat → apelantul NU schimbă statusul.
//   cssiSaveProgramare(proiectDbId, sel, obiectiv) → Promise<{success, saved, total}>
//     creează câte o zi de lucru (programare) pentru fiecare zi lucrătoare din durată,
//     prin endpoint-ul existent saveProgramare.
//
// Data nu poate fi în trecut (min = azi, verificat și la trimitere).
// ============================================================
(function () {
    var TECH = [
        {id:'zoli',name:'Zoli'},{id:'sanyi',name:'Sanyi'},{id:'bogdan',name:'Bogdan'},
        {id:'cezar',name:'Cezar'},{id:'cristi',name:'Cristi'},{id:'denes',name:'Denes'}
    ];

    function ymd(d){ return d.getFullYear()+'-'+String(d.getMonth()+1).padStart(2,'0')+'-'+String(d.getDate()).padStart(2,'0'); }
    function parse(s){ var p = String(s).split('-'); return new Date(+p[0], +p[1]-1, +p[2]); }
    var ZI = ['Dum','Lun','Mar','Mie','Joi','Vin','Sâm'];
    var LUNA = ['ian','feb','mar','apr','mai','iun','iul','aug','sep','oct','noi','dec'];
    function fmt(s){ var d = parse(s); return ZI[d.getDay()]+' '+d.getDate()+' '+LUNA[d.getMonth()]; }

    // Zilele de lucru: prima = data aleasă, următoarele sar peste sâmbătă și duminică.
    function workDays(start, n){
        var out = [start], d = parse(start);
        while (out.length < n) {
            d.setDate(d.getDate() + 1);
            var wd = d.getDay();
            if (wd !== 0 && wd !== 6) out.push(ymd(d));
        }
        return out;
    }
    function nZile(){ var n = parseInt(document.getElementById('cssiEsZile').value, 10); return (n >= 1 && n <= 60) ? n : 1; }
    function updPrev(){
        var v = document.getElementById('cssiEsData').value, el = document.getElementById('cssiEsPrev');
        if (!v) { el.textContent = ''; return; }
        var z = workDays(v, nZile());
        el.textContent = '📆 ' + (z.length === 1 ? fmt(z[0]) + ' · 1 zi' : fmt(z[0]) + ' → ' + fmt(z[z.length-1]) + ' · ' + z.length + ' zile lucrătoare');
    }
    function esc(s){ var d=document.createElement('div'); d.textContent = s==null ? '' : String(s); return d.innerHTML; }

    function ensureDom(){
        if (document.getElementById('cssiEsOverlay')) return;
        var css = document.createElement('style');
        css.textContent =
            '#cssiEsOverlay{position:fixed;inset:0;background:rgba(15,23,42,0.55);display:none;align-items:center;justify-content:center;z-index:9999;padding:16px;font-family:Inter,system-ui,-apple-system,sans-serif;}' +
            '#cssiEsOverlay.on{display:flex;}' +
            '#cssiEsBox{background:#fff;border-radius:18px;width:100%;max-width:440px;box-shadow:0 24px 60px -12px rgba(15,23,42,0.5);overflow:hidden;}' +
            '#cssiEsBox .h{padding:18px 20px 12px;border-bottom:1px solid #e2e8f0;}' +
            '#cssiEsBox .h b{display:block;font-size:17px;font-weight:800;color:#0f172a;}' +
            '#cssiEsBox .h span{display:block;font-size:12px;color:#64748b;margin-top:3px;}' +
            '#cssiEsBox .b{padding:16px 20px;display:flex;flex-direction:column;gap:12px;}' +
            '#cssiEsBox label.l{display:block;font-size:12px;font-weight:700;color:#334155;margin-bottom:5px;}' +
            '#cssiEsBox input[type=date],#cssiEsBox input[type=time],#cssiEsBox input[type=number]{width:100%;box-sizing:border-box;padding:10px 12px;border:2px solid #e2e8f0;border-radius:10px;font-size:14px;font-family:inherit;}' +
            '#cssiEsBox input:focus{outline:none;border-color:#3b82f6;}' +
            '#cssiEsBox .row3{display:grid;grid-template-columns:1.3fr 1fr 1fr;gap:10px;align-items:end;}' +
            '#cssiEsBox .prev{background:#f0fdfa;color:#0f766e;font-size:12px;font-weight:700;padding:8px 10px;border-radius:8px;}' +
            '@media(max-width:420px){#cssiEsBox .row3{grid-template-columns:1fr 1fr;}#cssiEsBox .row3>div:first-child{grid-column:1 / -1;}}' +
            '#cssiEsBox .tech{display:flex;flex-wrap:wrap;gap:6px;}' +
            '#cssiEsBox .tech label{display:flex;align-items:center;gap:5px;font-size:12px;font-weight:700;background:#f1f5f9;padding:6px 10px;border-radius:8px;cursor:pointer;}' +
            '#cssiEsBox .err{display:none;background:#fef2f2;color:#dc2626;font-size:12px;font-weight:700;padding:8px 10px;border-radius:8px;}' +
            '#cssiEsBox .f{display:flex;gap:8px;padding:0 20px 18px;}' +
            '#cssiEsBox .f button{flex:1;padding:12px;border:none;border-radius:12px;font-size:14px;font-weight:800;cursor:pointer;font-family:inherit;}' +
            '#cssiEsBox .f .no{background:#f1f5f9;color:#334155;}' +
            '#cssiEsBox .f .ok{background:#14b8a6;color:#fff;}';
        document.head.appendChild(css);
        var ov = document.createElement('div');
        ov.id = 'cssiEsOverlay';
        ov.innerHTML =
            '<div id="cssiEsBox" role="dialog" aria-modal="true">' +
            '<div class="h"><b id="cssiEsTitle">📅 Data de început a execuției</b><span id="cssiEsSub"></span></div>' +
            '<div class="b">' +
            '<div><label class="l" for="cssiEsData">Data de început *</label><input type="date" id="cssiEsData"></div>' +
            '<div class="row3"><div><label class="l" for="cssiEsZile">Durata (zile lucrătoare)</label><input type="number" id="cssiEsZile" value="1" min="1" max="60" step="1"></div>' +
            '<div><label class="l" for="cssiEsOra">Ora start</label><input type="time" id="cssiEsOra" value="08:00"></div>' +
            '<div><label class="l" for="cssiEsDur">Ore pe zi</label><input type="number" id="cssiEsDur" value="8" min="0.5" step="0.5"></div></div>' +
            '<div class="prev" id="cssiEsPrev"></div>' +
            '<div><label class="l">Echipă</label><div class="tech" id="cssiEsTech"></div></div>' +
            '<div class="err" id="cssiEsErr"></div>' +
            '</div>' +
            '<div class="f"><button type="button" class="no" id="cssiEsNo">Renunță</button><button type="button" class="ok" id="cssiEsOk">✓ Confirmă</button></div>' +
            '</div>';
        document.body.appendChild(ov);
        document.getElementById('cssiEsTech').innerHTML = TECH.map(function(t){
            return '<label><input type="checkbox" value="'+t.id+'"> '+t.name+'</label>';
        }).join('');
    }

    window.cssiExecStart = function (opts) {
        opts = opts || {};
        ensureDom();
        return new Promise(function (resolve) {
            var ov = document.getElementById('cssiEsOverlay');
            var today = ymd(new Date());
            var inp = document.getElementById('cssiEsData');
            var err = document.getElementById('cssiEsErr');
            document.getElementById('cssiEsTitle').textContent = opts.titlu || '📅 Data de început a execuției';
            document.getElementById('cssiEsSub').textContent = opts.subtitlu || 'Proiectul intră în Execuție și apare în calendar din ziua aleasă.';
            inp.min = today;
            inp.value = today;
            document.getElementById('cssiEsOra').value = '08:00';
            document.getElementById('cssiEsDur').value = '8';
            document.getElementById('cssiEsZile').value = '1';
            [].forEach.call(document.querySelectorAll('#cssiEsTech input'), function(c){ c.checked = false; });
            err.style.display = 'none';
            inp.oninput = updPrev; inp.onchange = updPrev;
            document.getElementById('cssiEsZile').oninput = updPrev;
            updPrev();
            ov.classList.add('on');
            setTimeout(function(){ inp.focus(); }, 50);

            function close(val){
                ov.classList.remove('on');
                document.getElementById('cssiEsOk').onclick = null;
                document.getElementById('cssiEsNo').onclick = null;
                ov.onclick = null;
                document.removeEventListener('keydown', onKey, true);
                resolve(val);
            }
            function onKey(e){ if (e.key === 'Escape') { e.stopPropagation(); close(null); } }
            document.addEventListener('keydown', onKey, true);
            ov.onclick = function(e){ if (e.target === ov) close(null); };
            document.getElementById('cssiEsNo').onclick = function(){ close(null); };
            document.getElementById('cssiEsOk').onclick = function(){
                var v = inp.value;
                if (!v) { err.textContent = 'Alege data de început.'; err.style.display = 'block'; return; }
                if (v < today) { err.textContent = 'Data nu poate fi în trecut.'; err.style.display = 'block'; return; }
                var zRaw = parseInt(document.getElementById('cssiEsZile').value, 10);
                if (!(zRaw >= 1 && zRaw <= 60)) { err.textContent = 'Durata trebuie să fie între 1 și 60 de zile lucrătoare.'; err.style.display = 'block'; return; }
                close({
                    data: v,
                    zile: workDays(v, zRaw),
                    ora: document.getElementById('cssiEsOra').value || '08:00',
                    durata: parseFloat(document.getElementById('cssiEsDur').value) || 8,
                    atribuiri: [].slice.call(document.querySelectorAll('#cssiEsTech input:checked')).map(function(c){ return c.value; })
                });
            };
        });
    };

    // O programare (o zi) per zi lucrătoare, trimise pe rând. Rezultat: {success, saved, total, error}.
    window.cssiSaveProgramare = function (proiectDbId, sel, obiectiv) {
        var zile = (sel.zile && sel.zile.length) ? sel.zile : [sel.data];
        var saved = 0;
        var chain = Promise.resolve();
        zile.forEach(function(zi, i){
            chain = chain.then(function(){
                return fetch('/admin/api.php?action=saveProgramare', {
                    method: 'POST', credentials: 'include',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({
                        action: 'saveProgramare',
                        proiect_id: parseInt(proiectDbId, 10),
                        data_programata: zi,
                        ora_start: (sel.ora || '08:00') + ':00',
                        durata_ore: sel.durata || 8,
                        status: 'Programat',
                        obiectiv: (obiectiv || 'Execuție') + (zile.length > 1 ? ' (ziua ' + (i+1) + '/' + zile.length + ')' : ''),
                        atribuiri: sel.atribuiri || []
                    })
                }).then(function(r){ return r.json(); }).then(function(res){
                    if (!res || !res.success) throw new Error((res && res.error) || 'eroare');
                    saved++;
                });
            });
        });
        return chain.then(function(){ return {success: true, saved: saved, total: zile.length}; },
                          function(e){ return {success: false, saved: saved, total: zile.length, error: e.message}; });
    };

    // Folosit de apelanți după schimbarea statusului: salvează zilele și anunță rezultatul.
    window.cssiAfterExecStart = function (proiectDbId, sel, obiectiv, notify) {
        return window.cssiSaveProgramare(proiectDbId, sel, obiectiv).then(function(res){
            var z = (sel.zile && sel.zile.length) ? sel.zile : [sel.data];
            var d = function(s){ return s.split('-').reverse().slice(0,2).join('.'); };
            if (notify) {
                if (res.success) notify('📅 Programat ' + (z.length === 1 ? d(z[0]) : z.length + ' zile: ' + d(z[0]) + ' → ' + d(z[z.length-1])), 'success');
                else if (res.saved) notify('⚠️ S-au salvat doar ' + res.saved + ' din ' + res.total + ' zile (' + res.error + '). Completează restul din Planificare.', 'error');
                else notify('⚠️ Status schimbat, dar programarea nu s-a salvat: ' + res.error + '. Apare în CRM → Neprogramat.', 'error');
            }
            return res;
        });
    };
})();
