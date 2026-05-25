/**
 * CSSI A/B Testing Script — Lightweight, cookie-based
 * No dependencies. Works with GA4 via dataLayer.
 *
 * Usage:
 *   <script src="/ab-test.js" defer></script>
 *   Then define tests in a <script> block after it.
 */
(function() {
  'use strict';

  window.CSSI_AB = {
    // Get or assign variant for a test
    getVariant: function(testId, variants) {
      var cookieName = 'cssi_ab_' + testId;
      var existing = this._getCookie(cookieName);
      if (existing && variants.indexOf(existing) !== -1) return existing;
      // Random assignment
      var variant = variants[Math.floor(Math.random() * variants.length)];
      this._setCookie(cookieName, variant, 90); // 90 days
      return variant;
    },

    // Run a test: swap DOM elements based on variant
    runTest: function(config) {
      var variant = this.getVariant(config.id, config.variants.map(function(v) { return v.name; }));
      var chosen = config.variants.find(function(v) { return v.name === variant; });
      if (!chosen) return;

      // Apply DOM changes
      if (chosen.apply && typeof chosen.apply === 'function') {
        if (document.readyState === 'loading') {
          document.addEventListener('DOMContentLoaded', function() { chosen.apply(); });
        } else {
          chosen.apply();
        }
      }

      // Track in GA4
      this._trackGA4(config.id, variant);

      return variant;
    },

    // Track CTA clicks
    trackClick: function(testId, variant, label) {
      window.dataLayer = window.dataLayer || [];
      window.dataLayer.push({
        event: 'ab_test_click',
        ab_test_id: testId,
        ab_variant: variant,
        ab_click_label: label || 'cta'
      });
    },

    // Internal: GA4 tracking
    _trackGA4: function(testId, variant) {
      window.dataLayer = window.dataLayer || [];
      window.dataLayer.push({
        event: 'ab_test_impression',
        ab_test_id: testId,
        ab_variant: variant
      });
    },

    // Internal: Cookie helpers
    _getCookie: function(name) {
      var match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
      return match ? decodeURIComponent(match[2]) : null;
    },

    _setCookie: function(name, value, days) {
      var d = new Date();
      d.setTime(d.getTime() + (days * 24 * 60 * 60 * 1000));
      document.cookie = name + '=' + encodeURIComponent(value) + ';expires=' + d.toUTCString() + ';path=/;SameSite=Lax';
    }
  };
})();
