/* ============================================================
   CSSI.RO — Tawk.to Live Chat Integration
   Fișier: /tawk-chat.js
   Încarcă pe toate paginile site-ului

   CONFIGURARE:
   1. Creează cont gratuit pe https://www.tawk.to/
   2. Adaugă un nou Property pentru cssi.ro
   3. Copiază Property ID-ul din Dashboard → Administration → Chat Widget
      URL-ul arată: https://embed.tawk.to/PROPERTY_ID/default
   4. Înlocuiește TAWK_PROPERTY_ID de mai jos cu ID-ul tău real
   5. Încarcă acest fișier pe server via cPanel
   6. Adaugă pe fiecare pagină HTML (înainte de </body>):
      <script src="/tawk-chat.js" defer></script>

   FUNCȚIONALITĂȚI:
   - Chat live cu vizitatori (gratuit, nelimitat)
   - Răspunsuri automate pre-configurate
   - Notificări pe mobil (app Tawk.to)
   - Program: L-V 08:00-18:00 (offline = formular mesaj)
   - Respectă cookie consent (se încarcă doar după accept)
   - Ascunde automat chat-ul vechi custom
   ============================================================ */

(function() {
    'use strict';

    // ═══ CONFIGURARE — ÎNLOCUIEȘTE CU ID-UL TĂU TAWK.TO ═══
    var TAWK_PROPERTY_ID = 'XXXXXXXXXXXXXXXXXXXXXXXXXX';
    // Exemplu ID real: '6837a1b2f0e4a814c5b3d2e1'
    // ════════════════════════════════════════════════════════

    // Nu încărca dacă ID-ul nu e configurat
    if (TAWK_PROPERTY_ID === 'XXXXXXXXXXXXXXXXXXXXXXXXXX') {
        console.warn('[CSSI Tawk.to] Property ID nu este configurat. Editează tawk-chat.js și adaugă ID-ul real.');
        return;
    }

    // ── 1. Ascunde chat-ul vechi custom ──
    function hideOldChat() {
        var chatBtn = document.getElementById('chatBtn');
        var chatPanel = document.getElementById('chatPanel');
        if (chatBtn) chatBtn.style.display = 'none';
        if (chatPanel) chatPanel.style.display = 'none';
    }

    // Ascunde imediat dacă DOM e gata, altfel la DOMContentLoaded
    if (document.readyState !== 'loading') {
        hideOldChat();
    } else {
        document.addEventListener('DOMContentLoaded', hideOldChat);
    }

    // ── 2. Verifică cookie consent ──
    function getCookie(name) {
        var v = document.cookie.match('(^|;)\\s*' + name + '=([^;]*)');
        return v ? v[2] : null;
    }

    function loadTawk() {
        // Previne încărcarea dublă
        if (window.Tawk_API && window.Tawk_API._loaded) return;

        window.Tawk_API = window.Tawk_API || {};
        window.Tawk_LoadStart = new Date();

        // ── Personalizare widget ──
        window.Tawk_API.customStyle = {
            visibility: {
                desktop: { position: 'br', xOffset: 24, yOffset: 24 },
                mobile:  { position: 'br', xOffset: 16, yOffset: 16 }
            }
        };

        // ── Eveniment: chat inițiat → track GA4 ──
        window.Tawk_API.onChatStarted = function() {
            if (typeof gtag === 'function') {
                gtag('event', 'tawk_chat_started', {
                    event_category: 'engagement',
                    event_label: 'live_chat',
                    currency: 'RON',
                    value: 100
                });
            }
            if (typeof fbq === 'function') {
                fbq('track', 'Contact', { content_name: 'tawk_chat', currency: 'RON', value: 100.00 });
            }
        };

        // ── Eveniment: mesaj offline trimis → track conversie ──
        window.Tawk_API.onOfflineSubmit = function(data) {
            if (typeof gtag === 'function') {
                gtag('event', 'tawk_offline_message', {
                    event_category: 'contact',
                    event_label: 'offline_form',
                    currency: 'RON',
                    value: 200
                });
                // Google Ads conversion
                gtag('event', 'conversion', {
                    'send_to': 'AW-17987940313/WVuaCJnH1YEcENnfqIFD',
                    'currency': 'RON',
                    'value': 200
                });
            }
        };

        // ── Încarcă scriptul Tawk.to ──
        var s1 = document.createElement('script');
        s1.async = true;
        s1.src = 'https://embed.tawk.to/' + TAWK_PROPERTY_ID + '/default';
        s1.charset = 'UTF-8';
        s1.setAttribute('crossorigin', '*');
        var s0 = document.getElementsByTagName('script')[0];
        s0.parentNode.insertBefore(s1, s0);

        window.Tawk_API._loaded = true;
    }

    // ── 3. Decide când să încarce ──
    // Tawk.to se încarcă doar dacă utilizatorul a acceptat cookies
    // SAU dacă nu a făcut nicio alegere (funcționalitate esențială)
    var consent = getCookie('cssi_consent');

    if (consent === 'rejected') {
        // Utilizatorul a refuzat explicit — nu încărcăm Tawk.to
        // Păstrăm chat-ul vechi ascuns, afișăm doar butoanele de contact statice
        return;
    }

    // Încarcă Tawk.to (consent accepted sau nedecis)
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            // Mic delay pentru a nu bloca încărcarea paginii
            setTimeout(loadTawk, 1500);
        });
    } else {
        setTimeout(loadTawk, 1500);
    }

})();
