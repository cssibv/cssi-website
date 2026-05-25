/**
 * CSSI A/B Tests Configuration
 * Active tests for service pages: hero text + CTA variations
 *
 * Test 1: Hero subtitle (benefit-focused vs feature-focused)
 * Test 2: CTA button (text + color variations)
 */
(function() {
  'use strict';
  if (!window.CSSI_AB) return;

  var page = location.pathname.replace(/\/$/, '').split('/').pop() || 'index';
  // Remove .html extension
  page = page.replace('.html', '');

  /* =============================================
     CAMERE SUPRAVEGHERE
     ============================================= */
  if (page === 'camere-supraveghere') {

    // TEST 1: Hero subtitle
    CSSI_AB.runTest({
      id: 'cam_hero_v1',
      variants: [
        {
          name: 'control',
          apply: function() { /* Original - no changes */ }
        },
        {
          name: 'benefit',
          apply: function() {
            var desc = document.querySelector('.hero-desc');
            if (desc) desc.textContent = 'Protejează-ți firma 24/7 cu camere profesionale — acces live pe telefon, imagine 4K și alertă instant la mișcare. Instalare în 1-2 zile.';
          }
        },
        {
          name: 'social_proof',
          apply: function() {
            var desc = document.querySelector('.hero-desc');
            if (desc) desc.textContent = 'Peste 3.200 de sisteme CCTV instalate în Brașov și zona centrală. Hikvision & Dahua, configurare gratuită, garanție 3 ani. Vizualizare live pe telefon inclusă.';
          }
        }
      ]
    });

    // TEST 2: CTA button
    CSSI_AB.runTest({
      id: 'cam_cta_v1',
      variants: [
        {
          name: 'cere_oferta',
          apply: function() { /* Original blue "Cere Ofertă" */ }
        },
        {
          name: 'evaluare_gratuita',
          apply: function() {
            var cta = document.querySelector('.hero-ctas a[href="contact"]');
            if (cta) {
              cta.innerHTML = 'Evaluare Gratuită <span style="font-size:16px;font-weight:300;">→</span>';
              cta.style.background = '#16a34a';
              cta.style.boxShadow = '0 8px 24px rgba(22,163,74,0.25)';
              cta.onmouseover = function() { this.style.background='#15803d'; this.style.transform='translateY(-2px)'; };
              cta.onmouseout = function() { this.style.background='#16a34a'; this.style.transform=''; };
            }
          }
        },
        {
          name: 'obtine_pret',
          apply: function() {
            var cta = document.querySelector('.hero-ctas a[href="contact"]');
            if (cta) {
              cta.innerHTML = 'Obține Preț Instant <span style="font-size:16px;font-weight:300;">→</span>';
              cta.href = '/calculator-pret';
              cta.style.background = '#dc2626';
              cta.style.boxShadow = '0 8px 24px rgba(220,38,38,0.25)';
              cta.onmouseover = function() { this.style.background='#b91c1c'; this.style.transform='translateY(-2px)'; };
              cta.onmouseout = function() { this.style.background='#dc2626'; this.style.transform=''; };
            }
          }
        }
      ]
    });
  }

  /* =============================================
     CONTROL ACCES
     ============================================= */
  if (page === 'control-acces') {

    // TEST 1: Hero subtitle
    CSSI_AB.runTest({
      id: 'acc_hero_v1',
      variants: [
        {
          name: 'control',
          apply: function() { /* Original */ }
        },
        {
          name: 'benefit',
          apply: function() {
            var desc = document.querySelector('.hero-desc');
            if (desc) desc.textContent = 'Știi exact cine intră și când. Badge RFID, amprentă sau recunoaștere facială — plus pontaj electronic integrat, fără cost suplimentar.';
          }
        },
        {
          name: 'urgency',
          apply: function() {
            var desc = document.querySelector('.hero-desc');
            if (desc) desc.textContent = 'Codul Muncii obligă evidența timpului de lucru. Cu control acces + pontaj de la CSSI, rezolvi și securitatea și conformitatea — dintr-un singur sistem.';
          }
        }
      ]
    });

    // TEST 2: CTA button
    CSSI_AB.runTest({
      id: 'acc_cta_v1',
      variants: [
        {
          name: 'cere_oferta',
          apply: function() { /* Original */ }
        },
        {
          name: 'configurare',
          apply: function() {
            var cta = document.querySelector('.hero-ctas a[href="contact"]');
            if (cta) {
              cta.innerHTML = 'Configurează Sistem <span style="font-size:16px;font-weight:300;">→</span>';
              cta.href = '/calculator-pret';
              cta.style.background = '#7c3aed';
              cta.style.boxShadow = '0 8px 24px rgba(124,58,237,0.25)';
              cta.onmouseover = function() { this.style.background='#6d28d9'; this.style.transform='translateY(-2px)'; };
              cta.onmouseout = function() { this.style.background='#7c3aed'; this.style.transform=''; };
            }
          }
        },
        {
          name: 'consultanta',
          apply: function() {
            var cta = document.querySelector('.hero-ctas a[href="contact"]');
            if (cta) {
              cta.innerHTML = 'Consultanță Gratuită <span style="font-size:16px;font-weight:300;">→</span>';
              cta.style.background = '#16a34a';
              cta.style.boxShadow = '0 8px 24px rgba(22,163,74,0.25)';
              cta.onmouseover = function() { this.style.background='#15803d'; this.style.transform='translateY(-2px)'; };
              cta.onmouseout = function() { this.style.background='#16a34a'; this.style.transform=''; };
            }
          }
        }
      ]
    });
  }

  /* =============================================
     ALARMA ANTIEFRACTIE
     ============================================= */
  if (page === 'alarma-antiefractie') {

    // TEST 1: Hero subtitle
    CSSI_AB.runTest({
      id: 'alm_hero_v1',
      variants: [
        {
          name: 'control',
          apply: function() { /* Original */ }
        },
        {
          name: 'fear_appeal',
          apply: function() {
            var desc = document.querySelector('.hero-desc');
            if (desc) desc.textContent = 'În România, o efracție are loc la fiecare 15 minute. Cu alarmă Paradox, DSC sau Ajax conectată la dispecerat, ai intervenție în sub 10 minute. Notificare instant pe telefon.';
          }
        },
        {
          name: 'saving',
          apply: function() {
            var desc = document.querySelector('.hero-desc');
            if (desc) desc.textContent = 'Sistem complet de alarmă de la 800 RON — mai puțin decât o pagubă medie din efracție. Monitorizare 24/7, notificare pe telefon, instalare în aceeași zi.';
          }
        }
      ]
    });

    // TEST 2: CTA button
    CSSI_AB.runTest({
      id: 'alm_cta_v1',
      variants: [
        {
          name: 'cere_oferta',
          apply: function() { /* Original */ }
        },
        {
          name: 'protejeaza',
          apply: function() {
            var cta = document.querySelector('.hero-ctas a[href="contact"]');
            if (cta) {
              cta.innerHTML = 'Protejează-ți Firma <span style="font-size:16px;font-weight:300;">→</span>';
              cta.style.background = '#dc2626';
              cta.style.boxShadow = '0 8px 24px rgba(220,38,38,0.25)';
              cta.onmouseover = function() { this.style.background='#b91c1c'; this.style.transform='translateY(-2px)'; };
              cta.onmouseout = function() { this.style.background='#dc2626'; this.style.transform=''; };
            }
          }
        },
        {
          name: 'evaluare',
          apply: function() {
            var cta = document.querySelector('.hero-ctas a[href="contact"]');
            if (cta) {
              cta.innerHTML = 'Evaluare Risc Gratuită <span style="font-size:16px;font-weight:300;">→</span>';
              cta.style.background = '#16a34a';
              cta.style.boxShadow = '0 8px 24px rgba(22,163,74,0.25)';
              cta.onmouseover = function() { this.style.background='#15803d'; this.style.transform='translateY(-2px)'; };
              cta.onmouseout = function() { this.style.background='#16a34a'; this.style.transform=''; };
            }
          }
        }
      ]
    });
  }

  /* =============================================
     CLICK TRACKING — auto-attach to hero CTAs
     ============================================= */
  document.addEventListener('DOMContentLoaded', function() {
    var heroCtas = document.querySelectorAll('.hero-ctas a');
    heroCtas.forEach(function(btn) {
      btn.addEventListener('click', function() {
        // Determine which tests are active on this page
        var prefix = page === 'camere-supraveghere' ? 'cam' : page === 'control-acces' ? 'acc' : 'alm';
        var heroVariant = CSSI_AB.getVariant(prefix + '_hero_v1', ['control', 'benefit', page === 'alarma-antiefractie' ? 'fear_appeal' : 'social_proof']);
        var ctaVariant = CSSI_AB.getVariant(prefix + '_cta_v1', ['cere_oferta', page === 'control-acces' ? 'configurare' : 'evaluare_gratuita', page === 'control-acces' ? 'consultanta' : 'obtine_pret']);

        CSSI_AB.trackClick(prefix + '_hero_v1', heroVariant, 'hero_cta');
        CSSI_AB.trackClick(prefix + '_cta_v1', ctaVariant, btn.textContent.trim());
      });
    });
  });

})();
