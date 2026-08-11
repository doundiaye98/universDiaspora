/**
 * Carrousel hero des pôles — flèches, pastilles, auto toutes les 10s.
 */
(function () {
  "use strict";

  function initCarousel(root) {
    var track = root.querySelector("[data-ud-carousel-track]");
    var slides = Array.prototype.slice.call(root.querySelectorAll("[data-ud-carousel-slide]"));
    var dots = Array.prototype.slice.call(root.querySelectorAll("[data-ud-carousel-dot]"));
    var status = root.querySelector("[data-ud-carousel-status]");
    var prevBtn = root.querySelector("[data-ud-carousel-prev]");
    var nextBtn = root.querySelector("[data-ud-carousel-next]");
    if (!track || slides.length === 0) return;

    var index = 0;
    var total = slides.length;
    var intervalMs = Math.max(4000, parseInt(root.getAttribute("data-interval") || "10000", 10) || 10000);
    var timer = null;
    var reduced = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

    function goTo(next) {
      index = ((next % total) + total) % total;
      track.style.transform = "translateX(" + (-index * 100) + "%)";
      slides.forEach(function (slide, i) {
        var on = i === index;
        slide.classList.toggle("is-active", on);
        slide.setAttribute("aria-hidden", on ? "false" : "true");
      });
      dots.forEach(function (dot, i) {
        var on = i === index;
        dot.classList.toggle("is-active", on);
        dot.setAttribute("aria-selected", on ? "true" : "false");
      });
      if (status) status.textContent = (index + 1) + " / " + total;
    }

    function next() { goTo(index + 1); }
    function prev() { goTo(index - 1); }

    function stop() {
      if (timer) {
        clearInterval(timer);
        timer = null;
      }
    }

    function start() {
      stop();
      if (reduced || total < 2) return;
      timer = setInterval(next, intervalMs);
    }

    if (prevBtn) prevBtn.addEventListener("click", function () { prev(); start(); });
    if (nextBtn) nextBtn.addEventListener("click", function () { next(); start(); });
    dots.forEach(function (dot) {
      dot.addEventListener("click", function () {
        var i = parseInt(dot.getAttribute("data-ud-carousel-dot") || "0", 10);
        goTo(i);
        start();
      });
    });

    root.addEventListener("mouseenter", stop);
    root.addEventListener("mouseleave", start);
    root.addEventListener("focusin", stop);
    root.addEventListener("focusout", function (e) {
      if (!root.contains(e.relatedTarget)) start();
    });

    var touchX = null;
    root.addEventListener("touchstart", function (e) {
      if (!e.changedTouches || !e.changedTouches[0]) return;
      touchX = e.changedTouches[0].clientX;
      stop();
    }, { passive: true });
    root.addEventListener("touchend", function (e) {
      if (touchX == null || !e.changedTouches || !e.changedTouches[0]) return;
      var dx = e.changedTouches[0].clientX - touchX;
      touchX = null;
      if (Math.abs(dx) > 40) {
        if (dx < 0) next(); else prev();
      }
      start();
    }, { passive: true });

    goTo(0);
    start();
  }

  document.querySelectorAll("[data-ud-carousel]").forEach(initCarousel);
})();
