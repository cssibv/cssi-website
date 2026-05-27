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


/* ════════════ FIX 27.05.2026 — DUAL-FIRE GOOGLE ADS + GA4 ════════════
   GA4 events phone_call & whatsapp_click depindeau exclusiv de import GA4->Ads.
   Acum trimitem si o conversie Google Ads DIRECTA via send_to.
   ATENTIE: trebuie inlocuit PHONE_CALL_LABEL_PLACEHOLDER si
   WHATSAPP_CLICK_LABEL_PLACEHOLDER cu conversion labels reale din Google Ads UI.
   Pana atunci, helper-ul detecteaza placeholder-urile si nu trimite (no-op). */
var CSSI_ADS_CONVERSIONS = {
    phone_call:     'AW-17987940313/PHONE_CALL_LABEL_PLACEHOLDER',
    whatsapp_click: 'AW-17987940313/WHATSAPP_CLICK_LABEL_PLACEHOLDER',
    form_submit:    'AW-17987940313/WVuaCJnH1YEcENnfqIFD'
};
var CSSI_DEBUG = (typeof window !== 'undefined' && window.location && window.location.search.indexOf('cssi_debug=1') !== -1);
function cssiLog() {
    if (CSSI_DEBUG && typeof console !== 'undefined' && console.log) {
        console.log.apply(console, ['[CSSI tracking]'].concat(Array.prototype.slice.call(arguments)));
    }
}
function cssiSendAdsConversion(name, value) {
    var sendTo = CSSI_ADS_CONVERSIONS[name];
    if (!sendTo || sendTo.indexOf('PLACEHOLDER') !== -1) {
        cssiLog('Skip Ads conversion for', name, '- label not configured yet');
        return;
    }
    if (typeof gtag === 'function') {
        gtag('event', 'conversion', { send_to: sendTo, currency: 'RON', value: value || 0 });
        cssiLog('Sent Ads conversion:', name, sendTo, value);
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
                cssiSendAdsConversion('phone_call', 150);
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
                cssiSendAdsConversion('whatsapp_click', 150);
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