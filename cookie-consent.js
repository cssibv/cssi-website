/* ═══════════════════════════════════════════════════════
   CSSI Cookie Consent + Google Analytics (Consent Mode v2)
   ═══════════════════════════════════════════════════════ */

(function() {
    'use strict';

    // ── Google Analytics Config ──
    var GA_ID = 'G-XGSRGBQBCS';

    // ── 1. Set default consent BEFORE gtag loads ──
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('consent', 'default', {
        'analytics_storage': 'denied',
        'ad_storage': 'denied',
        'ad_user_data': 'denied',
        'ad_personalization': 'denied',
        'functionality_storage': 'granted',
        'security_storage': 'granted',
        'wait_for_update': 500
    });
    gtag('js', new Date());
    gtag('config', GA_ID, { 'anonymize_ip': true });

    // ── Google Ads Conversion Tracking ──
    var AW_ID = 'AW-17987940313';
    gtag('config', AW_ID);

    // ── 2. Load gtag.js ──
    var gtagScript = document.createElement('script');
    gtagScript.async = true;
    gtagScript.src = 'https://www.googletagmanager.com/gtag/js?id=' + GA_ID;
    document.head.appendChild(gtagScript);

    // ── 2b. Meta (Facebook) Pixel ──
    // ÎNLOCUIEȘTE ID-ul de mai jos cu cel real din Meta Business Manager
    var META_PIXEL_ID = 'XXXXXXXXXXXXXXXXX';
    !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
    n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
    n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
    t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}
    (window,document,'script','https://connect.facebook.net/en_US/fbevents.js');
    fbq('consent', 'revoke');
    fbq('init', META_PIXEL_ID);
    fbq('track', 'PageView');

    // ── 2c. Load tracking events (phone, WhatsApp, form, CTA) ──
    var trackScript = document.createElement('script');
    trackScript.src = '/tracking.js';
    trackScript.defer = true;
    document.head.appendChild(trackScript);

    // ── 3. Check saved consent ──
    var saved = getCookie('cssi_consent');
    if (saved === 'accepted') {
        updateConsent(true);
    } else if (saved === 'rejected') {
        updateConsent(false);
    } else {
        // No choice yet — show banner after DOM ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', showBanner);
        } else {
            showBanner();
        }
    }

    // ── Update Google + Meta Consent ──
    function updateConsent(granted) {
        gtag('consent', 'update', {
            'analytics_storage': granted ? 'granted' : 'denied',
            'ad_storage': granted ? 'granted' : 'denied',
            'ad_user_data': granted ? 'granted' : 'denied',
            'ad_personalization': granted ? 'granted' : 'denied'
        });
        // Meta Pixel consent
        if (typeof fbq === 'function') {
            fbq('consent', granted ? 'grant' : 'revoke');
        }
    }

    // ── Cookie helpers (365 days) ──
    function getRootDomain() {
        var h = location.hostname;
        var parts = h.split('.');
        if (parts.length >= 2) return '.' + parts.slice(-2).join('.');
        return h;
    }
    function setCookie(name, value) {
        var d = new Date();
        d.setTime(d.getTime() + 365 * 24 * 60 * 60 * 1000);
        var domain = getRootDomain();
        document.cookie = name + '=' + value + ';expires=' + d.toUTCString() + ';path=/;domain=' + domain + ';SameSite=Lax;Secure';
    }
    function getCookie(name) {
        var v = document.cookie.match('(^|;)\\s*' + name + '=([^;]*)');
        return v ? v[2] : null;
    }

    // ── Show Banner ──
    function showBanner() {
        var overlay = document.createElement('div');
        overlay.id = 'cssiCookieOverlay';
        overlay.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:99998;opacity:0;transition:opacity 0.3s;';

        var banner = document.createElement('div');
        banner.id = 'cssiCookieBanner';
        banner.innerHTML = '' +
            '<div style="max-width:480px;width:calc(100% - 32px);background:#1a1a1a;border-radius:16px;padding:24px;box-shadow:0 20px 60px rgba(0,0,0,0.4);border:1px solid rgba(255,255,255,0.08);position:fixed;bottom:16px;left:50%;transform:translateX(-50%) translateY(20px);z-index:99999;font-family:Inter,system-ui,sans-serif;opacity:0;transition:all 0.4s cubic-bezier(0.34,1.56,0.64,1);" id="cssiCookieBox">' +
                '<div style="display:flex;align-items:flex-start;gap:12px;margin-bottom:16px;">' +
                    '<span style="font-size:28px;line-height:1;">🍪</span>' +
                    '<div>' +
                        '<h3 style="color:#fff;font-size:16px;font-weight:700;margin:0 0 6px;">Folosim cookie-uri</h3>' +
                        '<p style="color:#9ca3af;font-size:13px;line-height:1.6;margin:0;">Acest site folosește cookie-uri pentru a analiza traficul și a îmbunătăți experiența dvs. de navigare. Datele sunt anonimizate și nu sunt partajate cu terți în scopuri publicitare.</p>' +
                    '</div>' +
                '</div>' +
                '<div style="display:flex;gap:8px;">' +
                    '<button id="cssiCookieAccept" style="flex:1;padding:12px 16px;background:linear-gradient(135deg,#dc2626,#b91c1c);color:#fff;border:none;border-radius:10px;font-size:14px;font-weight:600;cursor:pointer;font-family:inherit;transition:transform 0.2s,box-shadow 0.2s;">Acceptă</button>' +
                    '<button id="cssiCookieReject" style="flex:1;padding:12px 16px;background:rgba(255,255,255,0.08);color:#9ca3af;border:1px solid rgba(255,255,255,0.1);border-radius:10px;font-size:14px;font-weight:500;cursor:pointer;font-family:inherit;transition:all 0.2s;">Refuză</button>' +
                '</div>' +
                '<p style="color:#6b7280;font-size:11px;text-align:center;margin:12px 0 0;line-height:1.5;">Puteți schimba preferințele oricând. <a href="/politica-cookies.html" style="color:#f87171;text-decoration:underline;">Politica de cookies</a></p>' +
            '</div>';

        document.body.appendChild(overlay);
        document.body.appendChild(banner);

        // Animate in
        requestAnimationFrame(function() {
            overlay.style.opacity = '1';
            var box = document.getElementById('cssiCookieBox');
            if (box) {
                box.style.opacity = '1';
                box.style.transform = 'translateX(-50%) translateY(0)';
            }
        });

        // Hover effects
        var acceptBtn = document.getElementById('cssiCookieAccept');
        var rejectBtn = document.getElementById('cssiCookieReject');

        acceptBtn.onmouseover = function() { this.style.transform = 'scale(1.03)'; this.style.boxShadow = '0 4px 15px rgba(220,38,38,0.4)'; };
        acceptBtn.onmouseout = function() { this.style.transform = 'scale(1)'; this.style.boxShadow = 'none'; };
        rejectBtn.onmouseover = function() { this.style.color = '#fff'; this.style.borderColor = 'rgba(255,255,255,0.2)'; };
        rejectBtn.onmouseout = function() { this.style.color = '#9ca3af'; this.style.borderColor = 'rgba(255,255,255,0.1)'; };

        // Accept
        acceptBtn.addEventListener('click', function() {
            setCookie('cssi_consent', 'accepted');
            updateConsent(true);
            closeBanner(overlay, banner);
        });

        // Reject
        rejectBtn.addEventListener('click', function() {
            setCookie('cssi_consent', 'rejected');
            updateConsent(false);
            closeBanner(overlay, banner);
        });
    }

    // ── Close Banner with animation ──
    function closeBanner(overlay, banner) {
        var box = document.getElementById('cssiCookieBox');
        if (box) {
            box.style.opacity = '0';
            box.style.transform = 'translateX(-50%) translateY(20px)';
        }
        overlay.style.opacity = '0';
        setTimeout(function() {
            if (banner.parentNode) banner.parentNode.removeChild(banner);
            if (overlay.parentNode) overlay.parentNode.removeChild(overlay);
        }, 400);
    }

})();