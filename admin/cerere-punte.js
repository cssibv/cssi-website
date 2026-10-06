// Puntea Cereri rapide → Proiect Nou (dashboard) / Intervenție nouă (CRM Clienți, Execuție).
// Cererea preluată vine prin ?cerere=<id>; datele ei precompletează formularul, iar după
// salvare cererea primește o notă cu codul proiectului și trece la Finalizat.
(function(){
    var API = '/admin/api.php';
    var KEY = 'cssi-cerere-punte';

    function post(action, body){
        body = body || {}; body.action = action;
        return fetch(API + '?action=' + action, {method:'POST', credentials:'include', headers:{'Content-Type':'application/json'}, body:JSON.stringify(body)})
            .then(function(r){ return r.json(); });
    }
    function plain(s){ return String(s || '').toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, ''); }
    function tel9(t){ return String(t || '').replace(/\D/g, '').slice(-9); }

    // 'oferta' / 'interventie' după cum începe solicitarea; '' când nu e clar
    window.cssiCerereTip = function(tip){
        var t = plain(tip).trim();
        if (t.indexOf('ofert') === 0) return 'oferta';
        if (t.indexOf('interven') === 0) return 'interventie';
        return '';
    };

    // Solicitarea fără cuvântul de început („Intervenție cameră defectă” → „Cameră defectă”)
    window.cssiCerereFaraTip = function(tip){
        var rest = String(tip || '').trim().replace(/^\S+\s*/, '').replace(/^[\s:,.\-]+/, '');
        if (!window.cssiCerereTip(tip)) return String(tip || '').trim();
        return rest ? rest.charAt(0).toUpperCase() + rest.slice(1) : '';
    };

    // Serviciul ghicit din text (numele din CRM_SERVICII); '' dacă nu se potrivește nimic
    var SERV = [
        [/interfon/, 'Videointerfonie'],
        [/camer|supraveghe|video|dvr|nvr/, 'Supraveghere Video'],
        [/alarm|efract/, 'Alarmă Antiefracție'],
        [/incendiu|fum/, 'Detecție Incendiu'],
        [/control acces|cartel|turnichet/, 'Control Acces'],
        [/garaj/, 'Uși Garaj'],
        [/barier|parcar/, 'Bariere Auto / Parcări'],
        [/poart|automatiz/, 'Automatizări Porți'],
        [/aer cond|clima/, 'Aer Condiționat'],
        [/ventil/, 'Ventilație'],
        [/sonoriz|boxe/, 'Sonorizare'],
        [/pontaj/, 'Pontaj Electronic'],
        [/electric|tablou|priz/, 'Instalații Electrice'],
        [/sanitar|termic|central/, 'Instalații Termice-Sanitare']
    ];
    window.cssiCerereServiciu = function(text){
        var t = plain(text);
        for (var i = 0; i < SERV.length; i++) if (SERV[i][0].test(t)) return SERV[i][1];
        return '';
    };

    // Din Cereri rapide: ține minte cererea și deschide formularul potrivit
    window.cssiCererePunteGo = function(cerere, fel){
        var note = (cerere.detalii || []).filter(function(d){ return !d.auto; }).map(function(d){ return d.text; });
        try {
            sessionStorage.setItem(KEY, JSON.stringify({id:cerere.id, nume:cerere.nume || '', telefon:cerere.telefon || '', tip:cerere.tip || '', detalii:note.join('\n')}));
        } catch (e) {}
        location.href = (fel === 'interventie' ? '/admin/crm-clienti.html' : '/admin.html') + '?cerere=' + cerere.id;
    };

    // În pagina țintă: cererea din ?cerere=<id> (sau null), cu clientul existent după telefon
    window.cssiCererePunte = function(){
        var m = /[?&]cerere=(\d+)/.exec(location.search);
        if (!m) return Promise.resolve(null);
        var id = +m[1];
        try { history.replaceState(null, '', location.pathname); } catch (e) {}
        var c = null;
        try { c = JSON.parse(sessionStorage.getItem(KEY)); sessionStorage.removeItem(KEY); } catch (e) {}
        var gata = (c && c.id === id) ? Promise.resolve(c) : post('getCereriRapide').then(function(res){
            var l = ((res && res.data) || []).filter(function(x){ return x.id === id; })[0];
            if (!l) return null;
            return {id:l.id, nume:l.nume || '', telefon:l.telefon || '', tip:l.tip || '',
                    detalii:(l.detalii || []).filter(function(d){ return !d.auto; }).map(function(d){ return d.text; }).join('\n')};
        });
        return gata.then(function(c){
            if (!c) return null;
            var t = tel9(c.telefon);
            if (t.length < 9) return c;
            return fetch(API + '?action=getClienti&_t=' + Date.now(), {credentials:'include'}).then(function(r){ return r.json(); }).then(function(res){
                c.client = ((res && res.data) || []).filter(function(x){ return tel9(x.telefon) === t; })[0] || null;
                return c;
            }).catch(function(){ return c; });
        }).catch(function(){ return null; });
    };

    // După salvare: nota cu ce s-a creat, apoi cererea trece la Finalizat
    window.cssiCererePunteGata = function(id, text){
        return post('addCerereRapidaDetaliu', {id:id, text:text})
            .then(function(){ return post('setCerereRapidaStatus', {id:id, status:'Finalizat'}); })
            .catch(function(){});
    };
})();
