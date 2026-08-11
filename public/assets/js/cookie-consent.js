/**
 * Bannière cookies / confidentialité — Univers Diaspora
 * Consentement stocké en localStorage (clé ud_cookie_consent).
 */
(() => {
  const STORAGE_KEY = 'ud_cookie_consent';
  const VERSION = '2';

  function readConsent() {
    try {
      const raw = localStorage.getItem(STORAGE_KEY);
      if (!raw) return null;
      const data = JSON.parse(raw);
      if (!data || data.v !== VERSION) return null;
      return data;
    } catch (_) {
      return null;
    }
  }

  function writeConsent(choice) {
    try {
      localStorage.setItem(STORAGE_KEY, JSON.stringify({
        v: VERSION,
        choice: choice,
        at: new Date().toISOString(),
      }));
    } catch (_) {
      /* ignore */
    }
  }

  function hideBanner(banner) {
    if (!banner) return;
    banner.classList.add('is-leaving');
    document.body.classList.remove('ud-cookie-open');
    window.setTimeout(() => {
      banner.hidden = true;
      banner.classList.remove('is-visible', 'is-leaving');
    }, 280);
  }

  function showBanner(banner) {
    banner.hidden = false;
    document.body.classList.add('ud-cookie-open');
    requestAnimationFrame(() => banner.classList.add('is-visible'));
  }

  document.addEventListener('DOMContentLoaded', () => {
    const banner = document.getElementById('udCookieBanner');
    if (!banner) return;

    if (readConsent()) {
      banner.hidden = true;
      return;
    }

    showBanner(banner);

    banner.addEventListener('click', (e) => {
      const btn = e.target.closest('[data-cookie-choice]');
      if (!btn) return;
      const choice = btn.getAttribute('data-cookie-choice') || 'essential';
      writeConsent(choice);
      hideBanner(banner);
    });
  });
})();
