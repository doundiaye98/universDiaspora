/**
 * PWA — enregistrement SW + bannières d’installation (Android + guide iPhone).
 */
(function () {
  "use strict";

  var script = document.currentScript;
  var swUrl = script && script.getAttribute("data-sw");
  var scope = script && script.getAttribute("data-scope");
  var STORAGE_KEY = "ud_pwa_hint_dismissed";
  var DISMISS_MS = 14 * 24 * 60 * 60 * 1000;

  function isStandalone() {
    return window.matchMedia("(display-mode: standalone)").matches ||
      window.navigator.standalone === true;
  }

  function isIos() {
    var ua = window.navigator.userAgent || "";
    var iOS = /iPad|iPhone|iPod/.test(ua);
    var iPadOs = window.navigator.platform === "MacIntel" && window.navigator.maxTouchPoints > 1;
    return iOS || iPadOs;
  }

  function isMobileViewport() {
    return window.matchMedia("(max-width: 767.98px)").matches;
  }

  function wasDismissed() {
    try {
      var raw = localStorage.getItem(STORAGE_KEY);
      if (!raw) return false;
      var ts = parseInt(raw, 10);
      if (!ts) return false;
      return Date.now() - ts < DISMISS_MS;
    } catch (e) {
      return false;
    }
  }

  function markDismissed() {
    try {
      localStorage.setItem(STORAGE_KEY, String(Date.now()));
    } catch (e) { /* ignore */ }
  }

  function hideBar(bar) {
    if (!bar) return;
    bar.hidden = true;
    bar.classList.remove("is-ios", "is-android");
  }

  function showIosGuide(bar) {
    if (!bar) return;
    bar.classList.add("is-ios");
    bar.classList.remove("is-android");
    var title = bar.querySelector("[data-ud-pwa-title]");
    var text = bar.querySelector("[data-ud-pwa-text]");
    var btn = bar.querySelector("[data-ud-pwa-install]");
    if (title) title.textContent = "Ajouter sur l’écran d’accueil";
    if (text) {
      text.textContent = "Safari → bouton Partager □↑ → « Sur l’écran d’accueil »";
    }
    if (btn) {
      btn.hidden = true;
    }
    bar.hidden = false;
  }

  function showAndroidBar(bar) {
    if (!bar) return;
    bar.classList.add("is-android");
    bar.classList.remove("is-ios");
    var title = bar.querySelector("[data-ud-pwa-title]");
    var text = bar.querySelector("[data-ud-pwa-text]");
    var btn = bar.querySelector("[data-ud-pwa-install]");
    if (title) title.textContent = "Installer Univers Diaspora";
    if (text) text.textContent = "Accès rapide depuis l’écran d’accueil";
    if (btn) btn.hidden = false;
    bar.hidden = false;
  }

  // Service worker (iOS 16.4+ / Android)
  if ("serviceWorker" in navigator && swUrl) {
    window.addEventListener("load", function () {
      navigator.serviceWorker.register(swUrl, scope ? { scope: scope } : undefined).catch(function () {});
    });
  }

  if (isStandalone() || wasDismissed() || !isMobileViewport()) {
    return;
  }

  var bar = document.getElementById("ud-pwa-install");
  var deferredPrompt = null;

  // iPhone / iPad : pas de beforeinstallprompt — guide manuel
  if (isIos()) {
    // Laisse le temps à la page de peindre, puis affiche le guide
    window.setTimeout(function () {
      if (!isStandalone() && !wasDismissed() && isMobileViewport()) {
        showIosGuide(bar);
      }
    }, 1800);
  }

  window.addEventListener("beforeinstallprompt", function (e) {
    e.preventDefault();
    deferredPrompt = e;
    if (!isIos()) {
      showAndroidBar(bar);
    }
  });

  document.addEventListener("click", function (e) {
    var target = e.target && e.target.closest ? e.target : null;
    if (!target) return;

    var installBtn = target.closest("[data-ud-pwa-install]");
    if (installBtn && deferredPrompt) {
      e.preventDefault();
      deferredPrompt.prompt();
      deferredPrompt.userChoice.finally(function () {
        deferredPrompt = null;
        markDismissed();
        hideBar(bar);
      });
      return;
    }

    var dismiss = target.closest("[data-ud-pwa-dismiss]");
    if (dismiss) {
      markDismissed();
      hideBar(bar);
    }
  });

  window.addEventListener("appinstalled", function () {
    markDismissed();
    hideBar(bar);
    deferredPrompt = null;
  });
})();
