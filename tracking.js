/* ============================================================
   CSSI.RO — Tracking Events (Meta Pixel + GA4)
   Fisier: /tracking.js
   Incarcat pe toate paginile site-ului

   CONFIGURARE:
   - Meta Pixel: se incarca inline din <head> (vezi fiecare pagina)
   - GA4 gtag: se incarca inline din <head>
   - Acest fisier: adauga evenimentele de conversie

   ACTUALIZARE 31.03.2026:
   - Adaugat Enhanced Conversions for Leads
   - Trimite user_data (email, telefon, nume) hash-uit automat
     de gtag la Google Ads pentru conversii optimizate

   ACTUALIZARE 25.05.2026:
   - Adaugat valori monetare (RON) pe toate evenimentele GA4
   - Activat allow_enhanced_conversions in cookie-consent.js
   - Valori estimate pe baza cost mediu servicii CSSI:
     phone_call=150, whatsapp=150, form_lead=300, email=50, cta=25
   ============================================================ */


/* ════════════ NOTA 27.05.2026 — TRACKING phone_call & whatsapp_click ════════════
   VERIFICAT in Google Ads UI: conversiile "CSSI.ro (web) phone_call" si
   "CSSI.ro (web) whatsapp_click" EXISTA si se importa corect din GA4 (eveniment
   GA4 -> conversie Ads). whatsapp_click are deja date (2 conv, 154 RON).
   phone_call e configurat corect; 0 conversii = volum mic + consent modeling
   inca neactivat (sub pragul de clicuri).
   => NU adaugam fire direct catre Google Ads (ar dubla numararea peste import GA4).
   Pastram doar debug logging pentru diagnostic viitor. */
var CSSI_DEBUG = (typeof window !== 'undefined' && window.location && window.location.search.indexOf('cssi_debug=1') !== -1);
function cssiLog() {
    if (CSSI_DEBUG && typeof console !== 'undefined' && console.log) {
        console.log.apply(console, ['[CSSI tracking]'].concat(Array.prototype.slice.call(arguments)));
    }
}

document.addEventListener('DOMContentLoaded', function() {

    /* --- 1. Track click pe număr de telefon --- */
    document.querySelectorAll('a[href^="tel:"]').forEach(function(link) {
        link.addEventListener('click', function() {
            if (typeof gtag === 'function') {
                gtag('event', 'phone_call', {
                    event_category: 'contact',
                    event_label: this.href.replace('tel:', ''),
                    currency: 'RON',
                    value: 150
                });
            }
            if (typeof fbq === 'function') {
                fbq('track', 'Contact', { content_name: 'phone_call', currency: 'RON', value: 150.00 });
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
                    currency: 'RON',
                    value: 150
                });
            }
            if (typeof fbq === 'function') {
                fbq('track', 'Lead', { content_name: 'whatsapp', currency: 'RON', value: 150.00 });
            }
        });
    });

    /* --- 3. Track trimitere formular (window.open spre wa.me) --- */
    /* --- Enhanced Conversions for Leads: trimite date utilizator cu conversia --- */
    var originalOpen = window.open;
    window.open = function(url) {
        if (url && url.indexOf('wa.me') !== -1) {

            if (typeof gtag === 'function') {

                /* ═══ Enhanced Conversions for Leads ═══
                   Captează datele din formularul de contact și le trimite
                   hash-uite (automat de gtag) pentru conversii optimizate.
                   Google Ads folosește aceste date pentru a potrivi conversiile
                   cu utilizatorii autentificați Google. */
                var contactForm = document.getElementById('contactForm');
                if (contactForm) {
                    var emailField = contactForm.querySelector('input[name="email"]');
                    var phoneField = contactForm.querySelector('input[name="phone"]');
                    var nameField = contactForm.querySelector('input[name="name"]');

                    var userData = {};
                    if (emailField && emailField.value) {
                        userData.email = emailField.value.trim().toLowerCase();
                    }
                    if (phoneField && phoneField.value) {
                        /* Normalizare telefon: +40 prefix, fără spații */
                        var phone = phoneField.value.trim().replace(/[\s\-\.\(\)]/g, '');
                        if (phone.indexOf('0') === 0) {
                            phone = '+4' + phone;
                        } else if (phone.indexOf('4') === 0 && phone.indexOf('+') !== 0) {
                            phone = '+' + phone;
                        }
                        userData.phone_number = phone;
                    }
                    if (nameField && nameField.value) {
                        var nameParts = nameField.value.trim().split(/\s+/);
                        if (nameParts.length >= 2) {
                            userData.address = {
                                first_name: nameParts[0],
                                last_name: nameParts.slice(1).join(' ')
                            };
                        } else if (nameParts.length === 1) {
                            userData.address = { first_name: nameParts[0] };
                        }
                    }

                    /* Setează user_data ÎNAINTE de evenimentul de conversie */
                    if (Object.keys(userData).length > 0) {
                        gtag('set', 'user_data', userData);
                    }
                }

                /* Eveniment GA4 — generate_lead */
                gtag('event', 'generate_lead', {
                    event_category: 'conversion',
                    event_label: 'form_whatsapp',
                    currency: 'RON',
                    value: 300
                });

                /* Eveniment form_submit pentru Google Ads Conversion */
                gtag('event', 'form_submit', {
                    event_category: 'conversion',
                    event_label: 'form_whatsapp',
                    currency: 'RON',
                    value: 300
                });

                /* Google Ads Conversion — "Solicitati o oferta" */
                gtag('event', 'conversion', {
                    'send_to': 'AW-17987940313/WVuaCJnH1YEcENnfqIFD',
                    'currency': 'RON',
                    'value': 300
                });
            }

            if (typeof fbq === 'function') {
                fbq('track', 'Lead', { content_name: 'form_submit', currency: 'RON', value: 300.00 });
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
                    event_label: this.href.replace('mailto:', ''),
                    currency: 'RON',
                    value: 50
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
                        event_label: 'cere_oferta',
                        currency: 'RON',
                        value: 25
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