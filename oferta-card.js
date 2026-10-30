/* Cardul „Ofertă Gratuită în 24h” (#oferta), comun pentru pagina principală și paginile de servicii.
   În pagină stă doar locul lui, urmat imediat de acest script (fără defer, ca să apară odată cu pagina):
     <div id="oferta" data-serviciu="camere"></div><script src="/oferta-card.js?v=1"></script>
   data-serviciu (opțional) = valoarea serviciului preselectat din listă; omul îl poate schimba.
   Orice schimbare a cardului se face aici și se vede peste tot. */
(function () {
    var card = document.getElementById('oferta');
    if (!card || card.getAttribute('data-gata')) return;
    card.setAttribute('data-gata', '1');

    var camp = 'width:100%;padding:16px 20px;border:1px solid #e2e8f0;border-radius:12px;font-size:16px;font-family:inherit;outline:none;';
    var focus = ' onfocus="this.style.borderColor=\'#dc2626\';this.style.boxShadow=\'0 0 0 2px rgba(220,38,38,0.2)\'" onblur="this.style.borderColor=\'#e2e8f0\';this.style.boxShadow=\'\'"';
    var servicii = [
        ['camere', 'Camere Supraveghere'], ['alarma', 'Alarmă Antiefracție'], ['control-acces', 'Control Acces'],
        ['automatizari', 'Automatizări Porți'], ['interfoane', 'Interfoane &amp; Videointerfoane'], ['bariere', 'Bariere Auto'],
        ['usi-garaj', 'Uși de Garaj'], ['pontaj', 'Pontaj Electronic'], ['sonorizare', 'Sonorizare'],
        ['instalatii', 'Instalații (Electric/Termice/AC)'], ['altele', 'Altele']
    ];
    var ales = card.getAttribute('data-serviciu') || '';
    var optiuni = '<option value="">Ce serviciu te interesează? *</option>';
    for (var i = 0; i < servicii.length; i++) {
        optiuni += '<option value="' + servicii[i][0] + '"' + (servicii[i][0] === ales ? ' selected' : '') + '>' + servicii[i][1] + '</option>';
    }

    card.style.cssText = 'background:#fff;border-radius:24px;padding:32px;box-shadow:0 20px 25px -5px rgba(0,0,0,0.1);scroll-margin-top:140px;';
    card.innerHTML =
        '<div style="text-align:center;margin-bottom:24px;">' +
            '<h2 style="font-size:24px;font-weight:800;color:#0f172a;">Ofertă Gratuită în 24h</h2>' +
            '<p style="color:#475569;margin-top:8px;">Evaluare la fața locului + consultanță fără obligații</p>' +
        '</div>' +
        '<div id="formSuccess" style="display:none;text-align:center;padding:48px 0;">' +
            '<div style="width:80px;height:80px;background:#dcfce7;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 24px;">' +
                '<svg style="width:40px;height:40px;color:#16a34a;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>' +
            '</div>' +
            '<h3 style="font-size:24px;font-weight:600;color:#0f172a;margin-bottom:8px;">Mulțumim!</h3>' +
            '<p style="color:#475569;">Te contactăm în maxim 24 de ore.</p>' +
        '</div>' +
        '<form id="heroForm" onsubmit="return handleHeroSubmit(event)">' +
            '<div id="heroStep1" style="display:flex;flex-direction:column;gap:16px;">' +
                '<input type="text" id="heroName" name="name" required placeholder="Nume complet *" style="' + camp + '"' + focus + '>' +
                '<input type="tel" id="heroPhone" name="phone" required placeholder="Telefon *" style="' + camp + '"' + focus + '>' +
                '<select id="heroService" required style="' + camp + 'background:#fff;color:#475569;" onfocus="this.style.borderColor=\'#dc2626\'" onblur="this.style.borderColor=\'#e2e8f0\'">' + optiuni + '</select>' +
                '<button type="submit" style="width:100%;background:linear-gradient(to right,#dc2626,#b91c1c);color:#fff;padding:16px;border-radius:12px;font-weight:700;font-size:18px;border:none;cursor:pointer;box-shadow:0 10px 15px -3px rgba(220,38,38,0.3);transition:all 0.2s;" onmouseover="this.style.background=\'linear-gradient(to right,#b91c1c,#991b1b)\';this.style.transform=\'translateY(-1px)\'" onmouseout="this.style.background=\'linear-gradient(to right,#dc2626,#b91c1c)\';this.style.transform=\'\'">Trimite formular</button>' +
                '<div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">' +
                    '<a href="tel:0752288400" style="display:flex;align-items:center;justify-content:center;gap:8px;padding:14px;border-radius:12px;font-weight:600;font-size:16px;color:#fff;background:#2563eb;">' +
                        '<svg style="width:18px;height:18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>' +
                        'Sună' +
                    '</a>' +
                    '<a href="https://wa.me/40752288400?text=Bun%C4%83%20ziua%2C%20a%C8%99%20dori%20o%20ofert%C4%83." target="_blank" rel="noopener noreferrer" style="display:flex;align-items:center;justify-content:center;gap:8px;padding:14px;border-radius:12px;font-weight:600;font-size:16px;color:#fff;background:#22c55e;">' +
                        '<svg style="width:18px;height:18px;" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>' +
                        'WhatsApp' +
                    '</a>' +
                '</div>' +
                '<div style="display:flex;flex-wrap:wrap;justify-content:center;gap:12px;margin-top:4px;">' +
                    '<span style="color:#64748b;font-size:13px;">✓ Răspuns în 1-12h</span>' +
                    '<span style="color:#64748b;font-size:13px;">✓ Fără obligații</span>' +
                    '<span style="color:#64748b;font-size:13px;">✓ Evaluare gratuită</span>' +
                '</div>' +
            '</div>' +
        '</form>';

    // Paginile de servicii (.hero-has-form): pe mobil cardul vine imediat după titlu și introducere,
    // pe un singur rând; butoanele „Cere Ofertă”/telefon din hero dispar, cardul le are deja.
    var css = document.createElement('style');
    css.textContent = '@media (max-width:768px){.hero-grid.hero-has-form{grid-template-columns:1fr !important;gap:20px !important;}' +
        '.hero-has-form .hero-ctas{display:none !important;}.hero-has-form .hero-desc{margin-bottom:0 !important;font-size:15px !important;}' +
        '.hero-has-form .hero-h1{font-size:26px !important;line-height:1.15 !important;}}' +
        // Paginile cu hero centrat (despre-noi, portofoliu, servicii): .oferta-split pune titlul (.os-intro) în stânga
        // și cardul (.os-card) în dreapta; .os-rest (opțional) continuă sub titlu. Sub 1024px: titlu, card, restul.
        '.oferta-split{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,460px);grid-template-areas:"intro card" "rest card";column-gap:48px;row-gap:32px;align-items:start;}' +
        '.oferta-split.os-simple{grid-template-areas:"intro card";align-items:center;}' +
        '.oferta-split>.os-intro{grid-area:intro;text-align:left !important;}.oferta-split>.os-card{grid-area:card;}.oferta-split>.os-rest{grid-area:rest;}' +
        '.oferta-split>.os-intro p{margin-left:0 !important;margin-right:0 !important;}' +
        '.oferta-split>.os-intro .breadcrumb,.oferta-split>.os-intro .btn-row{justify-content:flex-start !important;}' +
        '@media (max-width:1023px){.oferta-split,.oferta-split.os-simple{grid-template-columns:minmax(0,1fr);grid-template-areas:"intro" "card" "rest";row-gap:24px;}' +
        '.oferta-split>.os-card{width:100%;max-width:560px;justify-self:center;}.oferta-split .os-hide-mobile{display:none !important;}}';
    document.head.appendChild(css);

    var paginaPrincipala = /^\/(index\.html)?$/.test(location.pathname);

    window.handleHeroSubmit = function (e) {
        e.preventDefault();
        if (window.cssiCheckPhone && !window.cssiCheckPhone(document.getElementById('heroPhone'))) return false;
        window.showHeroSuccess();
        return false;
    };

    window.showHeroSuccess = function () {
        var name = document.getElementById('heroName').value;
        var phone = document.getElementById('heroPhone').value;
        var serviceEl = document.getElementById('heroService');
        var service = serviceEl.options[serviceEl.selectedIndex].text;

        var msg = '🔔 *Cerere ofertă de pe cssi.ro*\n\n';
        msg += '👤 *Nume:* ' + name + '\n';
        msg += '📞 *Telefon:* ' + phone + '\n';
        if (service && service !== 'Ce serviciu te interesează? *') msg += '🛠️ *Serviciu:* ' + service + '\n';

        // Apel unic către cssiTrackLead() din /tracking.js (conversia, cu transaction_id).
        if (typeof window.cssiTrackLead === 'function') {
            window.cssiTrackLead(service || 'Hero Form', 'heroForm');
        }
        // Cereri rapide; cssiSaveLead adaugă singur pagina (location.pathname), care apare în notă.
        if (typeof window.cssiSaveLead === 'function') {
            window.cssiSaveLead({ formular: (paginaPrincipala ? 'Pagina principală, ' : ales ? 'Pagina de serviciu, ' : '') + 'Ofertă în 24h',
                nume: name, telefon: phone, serviciu: serviceEl.value ? service : '', tipLucrare: '', detalii: '' });
        }

        window.open('https://wa.me/40752288400?text=' + encodeURIComponent(msg), '_blank');

        document.getElementById('heroForm').style.display = 'none';
        document.getElementById('formSuccess').style.display = 'block';
    };

    document.addEventListener('DOMContentLoaded', function () {
        // Pe mobil bulina WhatsApp ar acoperi butonul WhatsApp din card:
        // o ascund cât timp cardul e pe ecran și reapare după ce derulezi mai jos.
        var wa = document.getElementById('waFloat');
        if (wa && 'IntersectionObserver' in window) {
            var mobil = window.matchMedia('(max-width: 767px)'), vizibil = false;
            var aplica = function () {
                var ascunde = mobil.matches && vizibil;
                wa.style.opacity = ascunde ? '0' : '';
                wa.style.pointerEvents = ascunde ? 'none' : '';
                wa.setAttribute('aria-hidden', ascunde ? 'true' : 'false');
                wa.tabIndex = ascunde ? -1 : 0;
            };
            new IntersectionObserver(function (e) { vizibil = e[0].isIntersecting; aplica(); }).observe(card);
            if (mobil.addEventListener) mobil.addEventListener('change', aplica);
        }

        // Butoanele „Cere Ofertă” din meniul de mobil duc la #oferta: închid meniul, altfel ar acoperi cardul.
        document.addEventListener('click', function (e) {
            var a = e.target.closest ? e.target.closest('a[href="#oferta"]') : null;
            if (!a) return;
            var meniu = document.getElementById('mobileMenu'), nm = document.getElementById('nm');
            if (meniu && meniu.classList.contains('open') && typeof window.toggleMobileMenu === 'function') window.toggleMobileMenu();
            if (nm) nm.classList.remove('open');   // meniul de mobil de pe /portofoliu
        });
    });
})();
