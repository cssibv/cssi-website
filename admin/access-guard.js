// ============================================================
// CSSI Portal — Access Guard
// Inclus pe fiecare pagină admin. Verifică server-side dacă
// user-ul curent are acces la modulul paginii (data-module).
// Dacă nu → redirect la /admin (sau /teren pt tehnician).
// ============================================================
(function () {
    var module = (document.body && document.body.dataset && document.body.dataset.module) || '';
    if (!module) return; // pagina nu are guard
    fetch('/admin/api.php?action=checkModuleAccess&module=' + encodeURIComponent(module) + '&_t=' + Date.now(),
          { credentials: 'include', headers: { 'X-Requested-With': 'fetch' } })
        .then(function (r) {
            if (r.status === 401) {
                // Neautentificat → curăță sessionStorage + redirect la login
                try { sessionStorage.removeItem('cssi-role'); sessionStorage.removeItem('cssi-user'); } catch(e){}
                var role = '';
                try { role = sessionStorage.getItem('cssi-role') || ''; } catch(e){}
                var TECH = ['zoli','sanyi','bogdan','cezar','cristi','denes'];
                window.location.replace(TECH.indexOf(role) >= 0 ? '/teren' : '/admin');
                return null;
            }
            return r.json();
        })
        .then(function (res) {
            if (!res) return;
            if (res.success && res.allowed) return; // OK, continuă pagina
            // 403 sau erroare → afișez mesaj și redirect
            var role = '';
            try { role = sessionStorage.getItem('cssi-role') || ''; } catch(e){}
            var TECH = ['zoli','sanyi','bogdan','cezar','cristi','denes'];
            var target = TECH.indexOf(role) >= 0 ? '/admin/executie.html' : '/admin';
            // Mesaj scurt înainte de redirect
            try {
                document.body.innerHTML =
                    '<div style="position:fixed;inset:0;background:#fef2f2;display:flex;align-items:center;justify-content:center;font-family:Inter,system-ui,sans-serif;text-align:center;padding:24px">' +
                    '<div style="max-width:400px"><div style="font-size:64px;margin-bottom:12px">🚫</div>' +
                    '<div style="font-size:20px;font-weight:800;color:#dc2626;margin-bottom:8px">Acces interzis</div>' +
                    '<div style="font-size:14px;color:#64748b;margin-bottom:16px">Nu ai permisiune pentru modulul „<strong>' + module + '</strong>". Te redirecționăm.</div>' +
                    '<div style="font-size:12px;color:#94a3b8">Dacă ai nevoie de acces, vorbește cu administratorul.</div>' +
                    '</div></div>';
            } catch(e){}
            setTimeout(function () { window.location.replace(target); }, 1400);
        })
        .catch(function () {
            // Eroare rețea — nu blochez, las pagina să încerce
        });
})();
