/* Cererile din formularele site-ului ajung și la Cereri rapide din admin,
   pe lângă mesajul de WhatsApp. Trimiterea nu blochează nimic: dacă nu merge,
   WhatsApp se deschide oricum. */
(function () {
    // Sursa vizitei (reclamă Google / Facebook / campanie), reținută pe durata vizitei
    try {
        var q = new URLSearchParams(location.search), s = '';
        if (q.get('gclid') || q.get('gbraid') || q.get('wbraid')) s = 'Google Ads';
        else if (q.get('fbclid')) s = 'Facebook / Instagram';
        if (q.get('utm_source')) s = (s ? s + ', ' : '') + q.get('utm_source') + (q.get('utm_campaign') ? ' / ' + q.get('utm_campaign') : '');
        if (s) sessionStorage.setItem('cssi_lead_sursa', s.slice(0, 120));
    } catch (e) {}

    window.cssiSaveLead = function (d) {
        try {
            var sursa = '';
            try { sursa = sessionStorage.getItem('cssi_lead_sursa') || ''; } catch (e) {}
            d.sursa = sursa;
            d.pagina = location.pathname;
            fetch('/admin/api.php?action=siteLead', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(d),
                keepalive: true,
                credentials: 'omit'
            }).catch(function () {});
        } catch (e) {}
    };
})();
