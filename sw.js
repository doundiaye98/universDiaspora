/**
 * Univers Diaspora — Service Worker (PWA mobile)
 * Cache shell + assets ; navigations réseau d’abord, hors-ligne → offline.html
 */
/* eslint-disable no-restricted-globals */
(function () {
  "use strict";

  var CACHE = "ud-pwa-v1";
  var OFFLINE_URL = "offline.html";

  /** Chemins relatifs à la scope du SW (racine du site / sous-dossier). */
  var PRECACHE = [
    "./",
    OFFLINE_URL,
    "manifest.php",
    "public/assets/css/style.css",
    "public/assets/img/logo-univers-diaspora.jpg",
    "public/assets/img/pwa/icon-192.png",
    "public/assets/img/pwa/icon-512.png",
    "public/assets/img/pwa/apple-touch-icon.png",
  ];

  self.addEventListener("install", function (event) {
    event.waitUntil(
      caches.open(CACHE).then(function (cache) {
        return Promise.all(
          PRECACHE.map(function (path) {
            return cache.add(new Request(path, { cache: "reload" })).catch(function () {
              return undefined;
            });
          })
        );
      }).then(function () {
        return self.skipWaiting();
      })
    );
  });

  self.addEventListener("activate", function (event) {
    event.waitUntil(
      caches.keys().then(function (keys) {
        return Promise.all(
          keys.map(function (key) {
            if (key !== CACHE) {
              return caches.delete(key);
            }
            return undefined;
          })
        );
      }).then(function () {
        return self.clients.claim();
      })
    );
  });

  function isNavigate(request) {
    return request.mode === "navigate" ||
      (request.method === "GET" && request.headers.get("accept") &&
        request.headers.get("accept").indexOf("text/html") !== -1);
  }

  function isStaticAsset(url) {
    return /\/public\/assets\//.test(url.pathname) ||
      /\.(?:css|js|png|jpe?g|webp|svg|woff2?)$/i.test(url.pathname);
  }

  self.addEventListener("fetch", function (event) {
    var request = event.request;
    if (request.method !== "GET") {
      return;
    }

    var url;
    try {
      url = new URL(request.url);
    } catch (e) {
      return;
    }

    // Ne pas intercepter les API / admin / POST-like
    if (url.origin !== self.location.origin) {
      return;
    }
    if (/[?&]action=/.test(url.search) || /\/admin/.test(url.pathname)) {
      return;
    }

    if (isNavigate(request)) {
      event.respondWith(
        fetch(request)
          .then(function (response) {
            return response;
          })
          .catch(function () {
            return caches.match(OFFLINE_URL).then(function (cached) {
              return cached || caches.match("./");
            });
          })
      );
      return;
    }

    if (isStaticAsset(url)) {
      event.respondWith(
        caches.match(request).then(function (cached) {
          var network = fetch(request).then(function (response) {
            if (response && response.ok) {
              var copy = response.clone();
              caches.open(CACHE).then(function (cache) {
                cache.put(request, copy);
              });
            }
            return response;
          }).catch(function () {
            return cached;
          });
          return cached || network;
        })
      );
    }
  });
})();
