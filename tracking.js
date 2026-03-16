/* ============================================================
   CSSI.RO — Tracking Events (Meta Pixel + GA4)
   Fișier: /tracking.js
   Încărcat pe toate paginile site-ului

   CONFIGURARE:
   - Meta Pixel: se incarcă inline din <head> (vezi fiecare pagină)
   - GA4 gtag: se incarcă inline din <head>
   - Acest fișier: adaugă evenimentele de conversie
   ============================================================ */

document.addEventListener('DOMContentLoaded', function() {

    /* --- 1. Track click pe număr de telefon --- */
    document.querySelectorAll('a[href^="tel:"]').forEach(function(link) {
        link.addEventListener('click', function() {
            if (typeof gtag === 'function') {
                gtag('event', 'phone_call', {
                    event_category: 'contact',
                    event_label: this.href.replace('tel:', ''),
                    value: 1
                });
            }
            if (typeof fbq === 'function') {
                fbq('track', 'Contact', { content_name: 'phone_call' });
            }
        });
    });

    /* --- 2. Track click pe WhatsApp --- */
    document.querySelectorAll('a[href*="wa.me"], a[href*="whatsapp"]').forEach(function(link) {
        link.addEventListener('click', function() {
            if (typeof gtag === 'function') {
                gtag('event', 'whatsapp_click', {
                    event_category: 'contact',
                    event_label: 'WhatsApp',
                    value: 1
                });
            }
            if (typeof fbq === 'function') {
                fbq('track', 'Lead', { content_name: 'whatsapp' });
            }
        });
    });

    /* --- 3. Track trimitere formular (window.open spre wa.me) --- */
    var originalOpen = window.open;
    window.open = function(url) {
        if (url && url.indexOf('wa.me') !== -1) {

            if (typeof gtag === 'function') {
                /* Eveniment GA4 existent — generate_lead */
                gtag('event', 'generate_lead', {
                    event_category: 'conversion',
                    event_label: 'form_whatsapp',
                    value: 1
                });

                /* FIX: Eveniment form_submit pentru Google Ads Conversion */
                gtag('event', 'form_submit', {
                    event_category: 'conversion',
                    event_label: 'formular_contact',
                    send_to: 'AW-17987940313',
                    value: 1
                });
            }

            if (typeof fbq === 'function') {
                fbq('track', 'Lead', { content_name: 'form_submit' });
            }
        }
        return originalOpen.apply(this, arguments);
    };

    /* --- 4. Track click pe email --- */
    document.querySelectorAll('a[href^="mailto:"]').forEach(function(link) {
        link.addEventListener('click', function() {
            if (typeof gtag === 'function') {
                gtag('event', 'email_click', {
                    event_category: 'contact',
                    event_label: this.href.replace('mailto:', '')
                });
            }
        });
    });

    /* --- 5. Track click pe CTA "Cere Ofertă" --- */
    document.querySelectorAll('a').forEach(function(link) {
        var text = link.textContent || '';
        if (text.indexOf('Ofertă') !== -1 || text.indexOf('Cere') !== -1) {
            link.addEventListener('click', function() {
                if (typeof gtag === 'function') {
                    gtag('event', 'cta_click', {
                        event_category: 'engagement',
                        event_label: 'cere_oferta'
                    });
                }
                if (typeof fbq === 'function') {
                    fbq('track', 'ViewContent', { content_name: 'cere_oferta' });
                }
            });
        }
    });

    /* --- 6. Track ViewContent pe paginile de servicii --- */
    var serviciiPages = [
        'detectie-incendiu', 'camere-supraveghere', 'alarma-antiefractie',
        'control-acces', 'automatizari-porti', 'bariere-auto',
        'interfoane-videointerfoane', 'instalatii-electrice',
        'instalatii-termice-sanitare', 'pontaj-electronic',
        'sonorizare', 'aer-conditionat', 'ventilatie', 'usi-garaj'
    ];

    var currentPage = window.location.pathname;
    serviciiPages.forEach(function(page) {
        if (currentPage.indexOf(page) !== -1) {
            if (typeof fbq === 'function') {
                fbq('track', 'ViewContent', {
                    content_name: page,
                    content_category: 'servicii'
                });
            }
        }
    });

});
