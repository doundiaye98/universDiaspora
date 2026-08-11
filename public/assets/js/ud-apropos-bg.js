/**
 * Fond À propos — glissement droite → gauche (g1…g4).
 */
(function () {
  "use strict";

  var root = document.querySelector("[data-ud-apropos-bg]");
  if (!root) return;

  var slides = Array.prototype.slice.call(root.querySelectorAll(".ud-apropos__bg-slide"));
  if (slides.length < 2) return;

  var index = 0;
  var intervalMs = 6500;
  var animMs = 1100;
  var busy = false;
  var reduced = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  if (reduced) return;

  function goNext() {
    if (busy) return;
    busy = true;

    var current = slides[index];
    var nextIndex = (index + 1) % slides.length;
    var next = slides[nextIndex];

    // Place la suivante hors écran à droite, puis glisse vers le centre
    next.classList.remove("is-leaving", "is-active");
    next.style.transition = "none";
    next.style.transform = "translateX(100%)";
    // force reflow
    void next.offsetWidth;
    next.style.transition = "";
    next.style.transform = "";

    next.classList.add("is-active");
    current.classList.remove("is-active");
    current.classList.add("is-leaving");

    window.setTimeout(function () {
      current.classList.remove("is-leaving");
      index = nextIndex;
      busy = false;
    }, animMs);
  }

  setInterval(goNext, intervalMs);
})();
