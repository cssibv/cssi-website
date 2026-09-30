// ============================================================
// CSSI Portal — Programarea zilelor de execuție
// Fereastră comună folosită oriunde un proiect trece în Execuție
// (CRM, Board Proiecte, Proiectare, Dosar), în CRM → Neprogramat
// și la modificarea unei zile deja programate (CRM).
//
//   cssiExecStart(opts) → Promise<{data, zile[], ora, durata, atribuiri} | null>
//     null = utilizatorul a renunțat → apelantul NU schimbă nimic.
//     opts: {titlu, subtitlu, okLabel,
//            initial:{data, ora, durata, atribuiri}  – valori precompletate (modificare zi),
//            single:true                             – o singură zi (ascunde „Durata în zile”),
//            excludeIds:[id programare]              – ignorate la verificarea suprapunerilor}
//   cssiSaveProgramare(proiectDbId, sel, obiectiv) → Promise<{success, saved, total}>
//     creează câte o zi de lucru (programare) pentru fiecare zi lucrătoare din durată,
//     prin endpoint-ul existent saveProgramare.
//   cssiTechList() → Promise<[{id, name}]>   – echipa de teren din Utilizatori (getTehnicieni)
//   cssiFindConflicts({zile, ora, durata, atribuiri, excludeIds}) → Promise<[conflict]>
//
// Data poate fi și în trecut (lucrări începute deja / înregistrate retroactiv).
// ============================================================
(function () {
    // Rezervă dacă getTehnicieni nu răspunde (ex. API vechi în cache).
    var TECH_FALLBACK = [
        {id:'zoli',name:'Zoli'},{id:'sanyi',name:'Sanyi'},{id:'bogdan',name:'Bogdan'},
        {id:'cezar',name:'Cezar'},{id:'cristi',name:'Cristi'},{id:'denes',name:'Denes'}
    ];
    var techPromise = null;

    function ymd(d){ return d.getFullYear()+'-'+String(d.getMonth()+1).padStart(2,'0')+'-'+String(d.getDate()).padStart(2,'0'); }
    function parse(s){ var p = String(s).split('-'); return new Date(+p[0], +p[1]-1, +p[2]); }
    var ZI = ['Dum','Lun','Mar','Mie','Joi','Vin','Sâm'];
    var LUNA = ['ian','feb','mar','apr','mai','iun','iul','aug','sep','oct','noi','dec'];
    function fmt(s){ var d = parse(s); return ZI[d.getDay()]+' '+d.getDate()+' '+LUNA[d.getMonth()]; }
    function esc(s){ var d=document.createElement('div'); d.textContent = s==null ? '' : String(s); return d.innerHTML; }
    function toMin(hhmm){ var p = String(hhmm||'08:00').split(':'); return (+p[0])*60 + (+p[1]||0); }
    function hhmm(min){ min = Math.round(min); return String(Math.floor(min/60)%24).padStart(2,'0')+':'+String(min%60).padStart(2,'0'); }

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

    window.cssiTechList = function(){
        if (!techPromise) {
            techPromise = fetch('/admin/api.php?action=getTehnicieni&_t='+Date.now(), {credentials:'include'})
                .then(function(r){ return r.json(); })
                .then(function(res){ return (res && res.success && res.data && res.data.length) ? res.data : TECH_FALLBACK; })
                .catch(function(){ return TECH_FALLBACK; });
        }
        return techPromise;
    };

    // Suprapuneri: aceeași persoană, aceeași zi, intervale orare care se intersectează.
    window.cssiFindConflicts = function(q){
        var zile = q.zile || [];
        if (!zile.length || !(q.atribuiri||[]).length) return Promise.resolve([]);
        var excl = {}; (q.excludeIds||[]).forEach(function(id){ excl[String(id)] = 1; });
        var s1 = toMin(q.ora), e1 = s1 + (parseFloat(q.durata)||8)*60;
        var want = {}; q.atribuiri.forEach(function(u){ want[u] = 1; });
        var days = {}; zile.forEach(function(z){ days[z] = 1; });
        var url = '/admin/api.php?action=getExecutie&planner=1&from='+zile[0]+'&to='+zile[zile.length-1]+'&_t='+Date.now();
        return Promise.all([fetch(url,{credentials:'include'}).then(function(r){return r.json();}), window.cssiTechList()])
            .then(function(res){
                var ex = res[0], names = {}; res[1].forEach(function(t){ names[t.id] = t.name; });
                if (!ex || !ex.success || !ex.data) return [];
                var proj = {}; (ex.data.proiecte||[]).forEach(function(p){ proj[p.proiect_db_id] = p; });
                var out = [];
                (ex.data.programari||[]).forEach(function(pg){
                    if (excl[String(pg.id)] || !days[String(pg.data_programata).slice(0,10)]) return;
                    if (pg.status === 'Anulat' || pg.status === 'Finalizat') return;
                    var s2 = toMin(String(pg.ora_start||'08:00').slice(0,5)), e2 = s2 + (parseFloat(pg.durata_ore)||8)*60;
                    if (!(s1 < e2 && s2 < e1)) return;
                    var client = pg.client_nume || (proj[pg.proiect_id] && proj[pg.proiect_id].client_nume) || '—';
                    (pg.atribuiri||[]).forEach(function(u){
                        if (want[u]) out.push({ tech: names[u] || u, data: String(pg.data_programata).slice(0,10),
                                                de: hhmm(s2), pana: hhmm(e2), client: client });
                    });
                });
                return out;
            })
            .catch(function(){ return []; });   // verificarea e ajutătoare — nu blocăm programarea dacă pică
    };

    function ensureDom(){
        if (document.getElementById('cssiEsOverlay')) return;
        var css = document.createElement('style');
        css.textContent =
            '#cssiEsOverlay{position:fixed;inset:0;background:rgba(15,23,42,0.55);display:none;align-items:center;justify-content:center;z-index:9999;padding:16px;font-family:Inter,system-ui,-apple-system,sans-serif;}' +
            '#cssiEsOverlay.on{display:flex;}' +
            '#cssiEsBox{background:#fff;border-radius:18px;width:100%;max-width:460px;max-height:calc(100vh - 32px);overflow:auto;box-shadow:0 24px 60px -12px rgba(15,23,42,0.5);}' +
            '#cssiEsBox .h{padding:18px 20px 12px;border-bottom:1px solid #e2e8f0;}' +
            '#cssiEsBox .h b{display:block;font-size:17px;font-weight:800;color:#0f172a;}' +
            '#cssiEsBox .h span{display:block;font-size:12px;color:#64748b;margin-top:3px;}' +
            '#cssiEsBox .b{padding:16px 20px;display:flex;flex-direction:column;gap:12px;}' +
            '#cssiEsBox label.l{display:block;font-size:12px;font-weight:700;color:#334155;margin-bottom:5px;}' +
            '#cssiEsBox input[type=date],#cssiEsBox input[type=time],#cssiEsBox input[type=number]{width:100%;box-sizing:border-box;padding:10px 12px;border:2px solid #e2e8f0;border-radius:10px;font-size:14px;font-family:inherit;}' +
            '#cssiEsBox input:focus{outline:none;border-color:#3b82f6;}' +
            '#cssiEsBox .row3{display:grid;grid-template-columns:1.3fr 1fr 1fr;gap:10px;align-items:end;}' +
            '#cssiEsBox .row3.single{grid-template-columns:1fr 1fr;}#cssiEsBox .row3.single>.zile{display:none;}' +
            '#cssiEsBox .prev{background:#f0fdfa;color:#0f766e;font-size:12px;font-weight:700;padding:8px 10px;border-radius:8px;}' +
            '@media(max-width:420px){#cssiEsBox .row3{grid-template-columns:1fr 1fr;}#cssiEsBox .row3>div:first-child{grid-column:1 / -1;}}' +
            '#cssiEsBox .tech{display:flex;flex-wrap:wrap;gap:6px;}' +
            '#cssiEsBox .tech label{display:flex;align-items:center;gap:5px;font-size:12px;font-weight:700;background:#f1f5f9;padding:6px 10px;border-radius:8px;cursor:pointer;}' +
            '#cssiEsBox .tech em{font-size:12px;color:#94a3b8;font-style:normal;}' +
            '#cssiEsBox .err{display:none;background:#fef2f2;color:#dc2626;font-size:12px;font-weight:700;padding:8px 10px;border-radius:8px;}' +
            '#cssiEsBox .warn{display:none;background:#fffbeb;border:1px solid #fcd34d;color:#92400e;font-size:12px;padding:10px 12px;border-radius:10px;}' +
            '#cssiEsBox .warn b{display:block;margin-bottom:4px;font-size:13px;}' +
            '#cssiEsBox .warn li{margin:2px 0 0 16px;}' +
            '#cssiEsBox .f{display:flex;gap:8px;padding:0 20px 18px;}' +
            '#cssiEsBox .f button{flex:1;padding:12px;border:none;border-radius:12px;font-size:14px;font-weight:800;cursor:pointer;font-family:inherit;}' +
            '#cssiEsBox .f button:disabled{opacity:0.6;cursor:wait;}' +
            '#cssiEsBox .f .no{background:#f1f5f9;color:#334155;}' +
            '#cssiEsBox .f .ok{background:#14b8a6;color:#fff;}' +
            '#cssiEsBox .f .ok.force{background:#d97706;}';
        document.head.appendChild(css);
        var ov = document.createElement('div');
        ov.id = 'cssiEsOverlay';
        ov.innerHTML =
            '<div id="cssiEsBox" role="dialog" aria-modal="true">' +
            '<div class="h"><b id="cssiEsTitle"></b><span id="cssiEsSub"></span></div>' +
            '<div class="b">' +
            '<div><label class="l" for="cssiEsData" id="cssiEsDataLbl">Data de început *</label><input type="date" id="cssiEsData"></div>' +
            '<div class="row3" id="cssiEsRow"><div class="zile"><label class="l" for="cssiEsZile">Durata (zile lucrătoare)</label><input type="number" id="cssiEsZile" value="1" min="1" max="60" step="1"></div>' +
            '<div><label class="l" for="cssiEsOra">Ora start</label><input type="time" id="cssiEsOra" value="08:00"></div>' +
            '<div><label class="l" for="cssiEsDur">Ore pe zi</label><input type="number" id="cssiEsDur" value="8" min="0.5" step="0.5"></div></div>' +
            '<div class="prev" id="cssiEsPrev"></div>' +
            '<div><label class="l">Echipă</label><div class="tech" id="cssiEsTech"><em>Se încarcă echipa…</em></div></div>' +
            '<div class="warn" id="cssiEsWarn"></div>' +
            '<div class="err" id="cssiEsErr"></div>' +
            '</div>' +
            '<div class="f"><button type="button" class="no" id="cssiEsNo">Renunță</button><button type="button" class="ok" id="cssiEsOk">✓ Confirmă</button></div>' +
            '</div>';
        document.body.appendChild(ov);
    }

    window.cssiExecStart = function (opts) {
        opts = opts || {};
        var init = opts.initial || {};
        ensureDom();
        return new Promise(function (resolve) {
            var ov = document.getElementById('cssiEsOverlay');
            var today = ymd(new Date());
            var inp = document.getElementById('cssiEsData');
            var zileInp = document.getElementById('cssiEsZile');
            var err = document.getElementById('cssiEsErr');
            var warn = document.getElementById('cssiEsWarn');
            var okBtn = document.getElementById('cssiEsOk');
            var okLabel = opts.okLabel || '✓ Confirmă';
            var acknowledged = false;   // a văzut avertismentul de suprapunere și vrea să continue

            document.getElementById('cssiEsTitle').textContent = opts.titlu || '📅 Data de început a execuției';
            document.getElementById('cssiEsSub').textContent = opts.subtitlu || 'Proiectul intră în Execuție și apare în calendar din ziua aleasă.';
            document.getElementById('cssiEsDataLbl').textContent = opts.single ? 'Data *' : 'Data de început *';
            document.getElementById('cssiEsRow').classList.toggle('single', !!opts.single);
            inp.value = init.data || today;
            inp.removeAttribute('min');
            document.getElementById('cssiEsOra').value = init.ora || '08:00';
            document.getElementById('cssiEsDur').value = init.durata || '8';
            zileInp.value = '1';
            err.style.display = 'none';
            warn.style.display = 'none';
            okBtn.textContent = okLabel; okBtn.classList.remove('force'); okBtn.disabled = false;

            // Echipa din Utilizatori; bifele precompletate la modificare
            var techBox = document.getElementById('cssiEsTech');
            var pre = {}; (init.atribuiri||[]).forEach(function(u){ pre[u] = 1; });
            window.cssiTechList().then(function(list){
                var known = {}; list.forEach(function(t){ known[t.id] = 1; });
                // un tehnician deja alocat care nu mai e în listă (ex. dezactivat) rămâne vizibil
                Object.keys(pre).forEach(function(u){ if (!known[u]) list = list.concat([{id:u, name:u}]); });
                techBox.innerHTML = list.map(function(t){
                    return '<label><input type="checkbox" value="'+esc(t.id)+'"'+(pre[t.id]?' checked':'')+'> '+esc(t.name)+'</label>';
                }).join('');
            });

            function zile(){
                var v = inp.value; if (!v) return [];
                if (opts.single) return [v];
                var n = parseInt(zileInp.value, 10);
                return workDays(v, (n >= 1 && n <= 60) ? n : 1);
            }
            function upd(){
                var z = zile(), el = document.getElementById('cssiEsPrev');
                el.style.display = (opts.single || !z.length) ? 'none' : '';
                if (z.length && !opts.single)
                    el.textContent = '📆 ' + (z.length === 1 ? fmt(z[0]) + ' · 1 zi' : fmt(z[0]) + ' → ' + fmt(z[z.length-1]) + ' · ' + z.length + ' zile lucrătoare');
                // orice modificare anulează confirmarea peste avertisment
                acknowledged = false; warn.style.display = 'none';
                okBtn.textContent = okLabel; okBtn.classList.remove('force');
            }
            inp.oninput = upd; inp.onchange = upd; zileInp.oninput = upd;
            document.getElementById('cssiEsOra').oninput = upd;
            document.getElementById('cssiEsDur').oninput = upd;
            techBox.onchange = upd;
            upd();
            ov.classList.add('on');
            setTimeout(function(){ inp.focus(); }, 50);

            function close(val){
                ov.classList.remove('on');
                okBtn.onclick = null;
                document.getElementById('cssiEsNo').onclick = null;
                ov.onclick = null;
                document.removeEventListener('keydown', onKey, true);
                resolve(val);
            }
            function onKey(e){ if (e.key === 'Escape') { e.stopPropagation(); close(null); } }
            document.addEventListener('keydown', onKey, true);
            ov.onclick = function(e){ if (e.target === ov) close(null); };
            document.getElementById('cssiEsNo').onclick = function(){ close(null); };
            okBtn.onclick = function(){
                var v = inp.value;
                err.style.display = 'none';
                if (!v) { err.textContent = 'Alege data.'; err.style.display = 'block'; return; }
                if (!opts.single) {
                    var zRaw = parseInt(zileInp.value, 10);
                    if (!(zRaw >= 1 && zRaw <= 60)) { err.textContent = 'Durata trebuie să fie între 1 și 60 de zile lucrătoare.'; err.style.display = 'block'; return; }
                }
                var sel = {
                    data: v,
                    zile: zile(),
                    ora: document.getElementById('cssiEsOra').value || '08:00',
                    durata: parseFloat(document.getElementById('cssiEsDur').value) || 8,
                    atribuiri: [].slice.call(techBox.querySelectorAll('input:checked')).map(function(c){ return c.value; })
                };
                if (acknowledged) { close(sel); return; }
                okBtn.disabled = true; okBtn.textContent = '⏳ Verific echipa…';
                window.cssiFindConflicts({ zile: sel.zile, ora: sel.ora, durata: sel.durata, atribuiri: sel.atribuiri, excludeIds: opts.excludeIds })
                    .then(function(list){
                        okBtn.disabled = false;
                        if (!list.length) { close(sel); return; }
                        acknowledged = true;
                        var shown = list.slice(0, 6);
                        warn.innerHTML = '<b>⚠️ Echipa e deja programată în acel interval</b><ul>' + shown.map(function(c){
                            return '<li><strong>'+esc(c.tech)+'</strong> · '+esc(fmt(c.data))+' '+esc(c.de)+'–'+esc(c.pana)+' · '+esc(c.client)+'</li>';
                        }).join('') + (list.length > shown.length ? '<li>… și încă '+(list.length-shown.length)+'</li>' : '') + '</ul>'
                            + '<div style="margin-top:6px">Schimbă ora, data sau echipa — ori apasă din nou ca să programezi oricum.</div>';
                        warn.style.display = 'block';
                        okBtn.textContent = '⚠️ Programează oricum'; okBtn.classList.add('force');
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
