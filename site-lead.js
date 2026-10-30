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

    // Telefon valid: număr românesc de 10 cifre (07 mobil, 02/03 fix), cu sau fără +40 / 0040,
    // ori număr străin cu + și 8-15 cifre. Spațiile, liniuțele, punctele și parantezele nu contează.
    function telefonOk(v) {
        var t = String(v || '').replace(/[\s\-\.\(\)\/]/g, '');
        if (/^(\+40|0040|40)(?=[237])/.test(t)) t = '0' + t.replace(/^(\+40|0040|40)/, '');
        if (/^0[237]\d{8}$/.test(t)) return true;
        return /^(\+|00)[1-9]\d{7,14}$/.test(t) && !/^(\+|00)40/.test(t);
    }

    // Verifică telefonul înainte de trimitere; la număr greșit marchează câmpul, arată mesajul
    // sub el și întoarce false, ca formularul să nu plece până nu e corectat.
    window.cssiCheckPhone = function (input) {
        if (!input) return true;
        var msg = input.parentNode.querySelector('.cssi-tel-err');
        if (!msg) {
            msg = document.createElement('div');
            msg.className = 'cssi-tel-err';
            msg.setAttribute('role', 'alert');
            msg.style.cssText = 'display:none;grid-column:1/-1;color:#dc2626;font-size:14px;font-weight:600;margin-top:-8px;text-align:left;';
            msg.textContent = 'Numărul de telefon pare greșit. Verifică-l, de exemplu 0722 123 456.';
            input.insertAdjacentElement('afterend', msg);
            input.addEventListener('input', function () {
                if (msg.style.display !== 'none' && telefonOk(input.value)) {
                    msg.style.display = 'none';
                    input.style.borderColor = '';
                    input.removeAttribute('aria-invalid');
                }
            });
        }
        if (telefonOk(input.value)) return true;
        msg.style.display = 'block';
        input.style.borderColor = '#dc2626';
        input.setAttribute('aria-invalid', 'true');
        input.focus();
        return false;
    };

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
