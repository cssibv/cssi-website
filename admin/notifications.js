// ============================================================
// CSSI — Notification Bell Widget (drop-in pentru toate paginile admin)
// Auto-injecteaza buton + panel + CSS. Foloseste API existent
// (getNotificari, markNotificareRead, markAllRead).
// Inclus prin: <script src="/admin/notifications.js" defer></script>
// ============================================================
(function(){
    'use strict';

    // Daca pagina are deja propriul bell (admin.html dashboard), skip
    if (document.getElementById('notifBell')) return;

    var API_DB = '/admin/api.php';
    var POLL_MS = 30000;
    var pollTimer = null;

    var css = '' +
'.cssi-notif-bell{position:fixed;top:14px;right:14px;z-index:9000;width:42px;height:42px;border-radius:50%;background:#fff;border:1px solid #e2e8f0;display:flex;align-items:center;justify-content:center;cursor:pointer;box-shadow:0 2px 8px rgba(0,0,0,0.08);font-size:20px;transition:all 0.15s;}' +
'.cssi-notif-bell:hover{transform:scale(1.05);box-shadow:0 4px 14px rgba(0,0,0,0.12);}' +
'.cssi-notif-bell.has-unread{animation:cssiNotifPulse 2.4s ease-in-out infinite;}' +
'@keyframes cssiNotifPulse{0%,100%{box-shadow:0 2px 8px rgba(0,0,0,0.08)}50%{box-shadow:0 2px 8px rgba(220,38,38,0.4)}}' +
'.cssi-notif-badge{position:absolute;top:-4px;right:-4px;background:#dc2626;color:#fff;font-size:10px;font-weight:800;min-width:18px;height:18px;border-radius:9px;display:flex;align-items:center;justify-content:center;padding:0 5px;border:2px solid #fff;line-height:1;}' +
'.cssi-notif-panel{display:none;position:fixed;top:64px;right:14px;width:380px;max-width:calc(100vw - 28px);max-height:75vh;background:#fff;border-radius:16px;box-shadow:0 12px 40px rgba(0,0,0,0.18);z-index:9001;overflow:hidden;font-family:"Inter",system-ui,sans-serif;}' +
'.cssi-notif-panel.active{display:block;animation:cssiNotifSlide 0.18s ease;}' +
'@keyframes cssiNotifSlide{from{opacity:0;transform:translateY(-8px)}to{opacity:1;transform:translateY(0)}}' +
'.cssi-notif-head{padding:14px 18px;background:#0f172a;color:#fff;font-weight:800;font-size:14px;display:flex;justify-content:space-between;align-items:center;gap:8px;}' +
'.cssi-notif-head button{background:rgba(255,255,255,0.15);border:none;color:#fff;padding:5px 10px;border-radius:6px;font-size:11px;font-weight:700;cursor:pointer;font-family:inherit;}' +
'.cssi-notif-head button:hover{background:rgba(255,255,255,0.25);}' +
'.cssi-notif-body{max-height:calc(75vh - 50px);overflow-y:auto;padding:8px;}' +
'.cssi-notif-item{display:flex;gap:10px;padding:11px 12px;border-radius:10px;margin-bottom:4px;cursor:pointer;transition:background 0.15s;font-size:12px;line-height:1.45;color:#0f172a;}' +
'.cssi-notif-item:hover{background:#f1f5f9;}' +
'.cssi-notif-item.unread{background:#fef2f2;border-left:3px solid #dc2626;}' +
'.cssi-notif-item .time{font-size:10px;color:#94a3b8;margin-top:3px;font-weight:600;}' +
'.cssi-notif-empty{text-align:center;padding:40px 20px;color:#94a3b8;font-size:13px;}' +
'.cssi-notif-empty .icon{font-size:32px;margin-bottom:8px;opacity:0.5;}' +
'@media(max-width:500px){.cssi-notif-bell{top:10px;right:10px;width:38px;height:38px;font-size:17px;}.cssi-notif-panel{top:56px;right:10px;width:calc(100vw - 20px);}}';

    var styleEl = document.createElement('style');
    styleEl.textContent = css;
    document.head.appendChild(styleEl);

    function escHtml(s){
        var d = document.createElement('div');
        d.textContent = s == null ? '' : String(s);
        return d.innerHTML;
    }

    function fmtAgo(s){
        if (!s) return '';
        var diff = (Date.now() - new Date(s.replace(' ','T')).getTime()) / 1000;
        if (isNaN(diff) || diff < 0) return '';
        if (diff < 60) return 'Acum';
        if (diff < 3600) return Math.floor(diff/60) + 'm';
        if (diff < 86400) return Math.floor(diff/3600) + 'h';
        if (diff < 172800) return 'Ieri';
        return Math.floor(diff/86400) + 'z';
    }

    // Ruteaza notificarea spre pagina de detaliu in functie de status proiect
    function getNotifUrl(n){
        if (!n.proiect_id && !n.cod_proiect) return null;
        var pid = encodeURIComponent(n.proiect_id || n.cod_proiect);
        var s = (n.status_proiect || '').toLowerCase();
        if (s.indexOf('execu') >= 0 || s.indexOf('recep') >= 0 || s.indexOf('factur') >= 0 || s.indexOf('finaliz') >= 0)
            return '/admin/executie-proiect.html?id=' + pid;
        if (s.indexOf('proiectare') >= 0)
            return '/admin/proiectare-proiect.html?id=' + pid;
        if (s === 'contract' || s.indexOf('contract') >= 0)
            return '/admin/contracte.html';
        if (s === 'cotatie' || s.indexOf('coti') >= 0 || s.indexOf('oferta') >= 0)
            return '/admin/oferte.html';
        return '/admin/proiecte.html';
    }

    function injectMarkup(){
        if (document.querySelector('.cssi-notif-bell')) return;

        var bell = document.createElement('div');
        bell.className = 'cssi-notif-bell';
        bell.id = 'cssiNotifBell';
        bell.title = 'Notificări';
        bell.innerHTML = '<span>🔔</span><span class="cssi-notif-badge" id="cssiNotifBadge" style="display:none">0</span>';
        bell.addEventListener('click', togglePanel);

        var panel = document.createElement('div');
        panel.className = 'cssi-notif-panel';
        panel.id = 'cssiNotifPanel';
        panel.innerHTML =
            '<div class="cssi-notif-head">' +
                '<span>🔔 Notificări</span>' +
                '<button id="cssiNotifMarkAll">Marchează tot ca citit</button>' +
            '</div>' +
            '<div class="cssi-notif-body" id="cssiNotifBody">' +
                '<div class="cssi-notif-empty"><div class="icon">⏳</div>Se încarcă...</div>' +
            '</div>';

        document.body.appendChild(bell);
        document.body.appendChild(panel);

        document.getElementById('cssiNotifMarkAll').addEventListener('click', function(e){
            e.stopPropagation();
            markAll();
        });

        document.addEventListener('click', function(e){
            if (!panel.contains(e.target) && !bell.contains(e.target))
                panel.classList.remove('active');
        });
    }

    function togglePanel(e){
        if (e) e.stopPropagation();
        var p = document.getElementById('cssiNotifPanel');
        if (p.classList.contains('active')) p.classList.remove('active');
        else { p.classList.add('active'); loadNotifications(); }
    }

    function renderList(rows){
        var body = document.getElementById('cssiNotifBody');
        if (!rows || !rows.length){
            body.innerHTML = '<div class="cssi-notif-empty"><div class="icon">📭</div>Nicio notificare</div>';
            return;
        }
        var html = '';
        rows.forEach(function(n){
            var cls = n.citit ? '' : 'unread';
            var url = getNotifUrl(n);
            html += '<div class="cssi-notif-item ' + cls + '" data-id="' + n.id + '"';
            if (url) html += ' data-url="' + escHtml(url) + '"';
            html += '>';
            html += '<div style="flex:1">';
            html += '<div>' + escHtml(n.mesaj) + '</div>';
            html += '<div class="time">' + fmtAgo(n.created_at);
            if (n.cod_proiect) html += ' · ' + escHtml(n.cod_proiect);
            if (n.preluat_de) html += ' · ✅ ' + escHtml(n.preluat_de);
            html += '</div>';
            html += '</div></div>';
        });
        body.innerHTML = html;

        body.querySelectorAll('.cssi-notif-item').forEach(function(el){
            el.addEventListener('click', function(ev){
                ev.stopPropagation();
                var id = el.getAttribute('data-id');
                var url = el.getAttribute('data-url');
                if (id) markRead(id);
                if (url) window.location.href = url;
            });
        });
    }

    function loadNotifications(){
        return fetch(API_DB + '?action=getNotificari&limit=20&_t=' + Date.now(), {credentials: 'include'})
            .then(function(r){ return r.json(); })
            .then(function(res){
                if (!res || !res.success) return;
                var bell = document.getElementById('cssiNotifBell');
                var badge = document.getElementById('cssiNotifBadge');
                if (res.unread > 0){
                    badge.style.display = '';
                    badge.textContent = res.unread > 99 ? '99+' : res.unread;
                    if (bell) bell.classList.add('has-unread');
                } else {
                    badge.style.display = 'none';
                    if (bell) bell.classList.remove('has-unread');
                }
                renderList(res.data || []);
            })
            .catch(function(){});
    }

    function markRead(id){
        fetch(API_DB + '?action=markNotificareRead', {
            method: 'POST', credentials: 'include',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({id: id})
        }).then(loadNotifications).catch(function(){});
    }

    function markAll(){
        fetch(API_DB + '?action=markAllRead', {
            method: 'POST', credentials: 'include',
            headers: {'Content-Type': 'application/json'},
            body: '{}'
        }).then(loadNotifications).catch(function(){});
    }

    function init(){
        injectMarkup();
        loadNotifications();
        if (pollTimer) clearInterval(pollTimer);
        pollTimer = setInterval(loadNotifications, POLL_MS);
    }

    window.__cssiNotif = { toggle: togglePanel, markAll: markAll, refresh: loadNotifications };

    if (document.readyState === 'loading')
        document.addEventListener('DOMContentLoaded', init);
    else
        init();
})();
