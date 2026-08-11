/**
 * Univers Diaspora — Globe 3D animé en arrière-plan du hero.
 * --------------------------------------------------------------------
 *  • Sphère terrestre filaire (méridiens / parallèles + équateur)
 *  • Étoiles scintillantes en fond ("Univers")
 *  • Capitales de la diaspora reliées à Paris par des arcs de
 *    grands cercles avec impulsions lumineuses voyageuses
 *  • Couleurs de marque : or (#d9a04a) + bleu profond (#182858)
 *  • Pas de dépendance, Canvas 2D pur, ~6 KB minifié
 *  • Respecte prefers-reduced-motion (rendu statique)
 *  • Pause auto quand l'onglet n'est plus actif
 */
(function () {
  'use strict';

  var canvas = document.getElementById('udHeroGlobe');
  if (!canvas || !canvas.getContext) return;
  var ctx = canvas.getContext('2d', { alpha: true });
  var reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

  /* --------------------------------------------------------------
   *  Données : Paris + capitales représentatives de la diaspora
   *  desservie par Univers Diaspora.
   * -------------------------------------------------------------- */
  var PARIS = { lat: 48.8566, lon: 2.3522, name: 'Paris', isHQ: true };
  var CITIES = [
    PARIS,
    { lat: 14.6928,  lon: -17.4467, name: 'Dakar' },
    { lat:  5.3600,  lon:  -4.0083, name: 'Abidjan' },
    { lat:  3.8480,  lon:  11.5021, name: 'Yaoundé' },
    { lat: 12.6392,  lon:  -8.0029, name: 'Bamako' },
    { lat: -4.4419,  lon:  15.2663, name: 'Kinshasa' },
    { lat:  6.3703,  lon:   2.3912, name: 'Cotonou' },
    { lat:  6.1319,  lon:   1.2228, name: 'Lomé' },
    { lat: 12.3714,  lon:  -1.5197, name: 'Ouagadougou' },
    { lat:  9.6412,  lon: -13.5784, name: 'Conakry' },
    { lat: -18.8792, lon:  47.5079, name: 'Antananarivo' },
    { lat: 15.5007,  lon:  32.5599, name: 'Khartoum' },
    { lat: 45.5017,  lon: -73.5673, name: 'Montréal' },
    { lat: 40.7128,  lon: -74.0060, name: 'New York' },
    { lat: 51.5074,  lon:  -0.1278, name: 'Londres' },
    { lat: 50.8503,  lon:   4.3517, name: 'Bruxelles' }
  ];

  /* Arcs : chaque capitale ↔ Paris, avec phase et vitesse propres. */
  var ARCS = [];
  for (var i = 1; i < CITIES.length; i++) {
    ARCS.push({
      from: CITIES[i],
      to: PARIS,
      phase: Math.random(),
      speed: 0.07 + Math.random() * 0.06
    });
  }

  /* --------------------------------------------------------------
   *  État global
   * -------------------------------------------------------------- */
  var W = 0, H = 0, dpr = 1;
  var cx = 0, cy = 0, R = 0;
  var rotation = 0;
  var TILT = 0.40;          /* ~23° d'inclinaison axiale */
  var stars = [];
  var lastT = 0;
  var running = true;

  /* --------------------------------------------------------------
   *  Couleurs (alignées sur la charte UD)
   * -------------------------------------------------------------- */
  var GOLD       = 'rgba(218, 160, 72, ';
  var GOLD_SOFT  = 'rgba(255, 220, 130, ';
  var BLUE       = 'rgba(40, 80, 160, ';
  var STAR       = 'rgba(255, 255, 255, ';

  /* --------------------------------------------------------------
   *  Outils 3D : SLERP, projection sphère→écran
   * -------------------------------------------------------------- */
  function sphereVec(lat, lon) {
    var phi = lat * Math.PI / 180;
    var lam = lon * Math.PI / 180;
    return {
      x: Math.cos(phi) * Math.sin(lam),
      y: Math.sin(phi),
      z: Math.cos(phi) * Math.cos(lam)
    };
  }

  /* Applique rotation Y puis tilt X et projette en 2D. */
  function projectVec(x, y, z) {
    var cosR = Math.cos(rotation), sinR = Math.sin(rotation);
    var rx =  cosR * x - sinR * z;
    var rz =  sinR * x + cosR * z;
    var cosT = Math.cos(TILT), sinT = Math.sin(TILT);
    var ny = y * cosT - rz * sinT;
    var nz = y * sinT + rz * cosT;
    return {
      x: cx + rx * R,
      y: cy - ny * R,
      z: nz                    /* > 0 = face avant, < 0 = derrière */
    };
  }

  function project(lat, lon) {
    var v = sphereVec(lat, lon);
    return projectVec(v.x, v.y, v.z);
  }

  /* --------------------------------------------------------------
   *  Redimensionnement (devicePixelRatio safe)
   * -------------------------------------------------------------- */
  function resize() {
    dpr = Math.min(window.devicePixelRatio || 1, 2);
    var rect = canvas.getBoundingClientRect();
    W = Math.max(1, rect.width);
    H = Math.max(1, rect.height);
    canvas.width  = Math.floor(W * dpr);
    canvas.height = Math.floor(H * dpr);
    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);

    /* Globe positionné légèrement à droite (laisse l'espace au texte) */
    if (W >= 992) {
      cx = W * 0.72;
      cy = H * 0.55;
      R  = Math.min(W * 0.34, H * 0.55);
    } else {
      cx = W * 0.5;
      cy = H * 0.55;
      R  = Math.min(W * 0.42, H * 0.5);
    }

    /* Étoiles : densité proportionnelle à l'aire */
    var count = Math.min(160, Math.floor((W * H) / 7500));
    stars = [];
    for (var i = 0; i < count; i++) {
      stars.push({
        x: Math.random() * W,
        y: Math.random() * H,
        r: Math.random() * 1.3 + 0.2,
        t: Math.random() * Math.PI * 2,
        ts: 0.4 + Math.random() * 1.6
      });
    }
  }

  /* --------------------------------------------------------------
   *  Rendu : étoiles, atmosphère, grille, arcs, capitales
   * -------------------------------------------------------------- */
  function drawStars(time) {
    for (var i = 0; i < stars.length; i++) {
      var s = stars[i];
      var tw = 0.5 + 0.5 * Math.sin(time * s.ts + s.t);
      ctx.fillStyle = STAR + (0.18 + 0.7 * tw) + ')';
      ctx.beginPath();
      ctx.arc(s.x, s.y, s.r, 0, Math.PI * 2);
      ctx.fill();
    }
  }

  function drawAtmosphere() {
    /* Aura externe */
    var aura = ctx.createRadialGradient(cx, cy, R * 0.35, cx, cy, R * 1.55);
    aura.addColorStop(0,   BLUE + '0.00)');
    aura.addColorStop(0.55, BLUE + '0.10)');
    aura.addColorStop(0.85, GOLD + '0.06)');
    aura.addColorStop(1,   BLUE + '0.00)');
    ctx.fillStyle = aura;
    ctx.beginPath();
    ctx.arc(cx, cy, R * 1.55, 0, Math.PI * 2);
    ctx.fill();

    /* Sphère semi-translucide */
    var core = ctx.createRadialGradient(cx - R * 0.25, cy - R * 0.25, R * 0.1, cx, cy, R);
    core.addColorStop(0, 'rgba(40, 70, 140, 0.18)');
    core.addColorStop(1, 'rgba(8, 14, 36, 0.0)');
    ctx.fillStyle = core;
    ctx.beginPath();
    ctx.arc(cx, cy, R, 0, Math.PI * 2);
    ctx.fill();
  }

  /* Trace une polyligne sphère→2D en sautant les segments cachés. */
  function drawSphereLine(lats, lons, strokeStyle, lineWidth) {
    ctx.lineWidth = lineWidth;
    ctx.strokeStyle = strokeStyle;
    ctx.beginPath();
    var started = false;
    var prev = null;
    for (var i = 0; i < lats.length; i++) {
      var p = project(lats[i], lons[i]);
      if (p.z > -0.05) {
        /* Détection de saut antiméridien : si delta > R, on coupe */
        if (!started || (prev && Math.hypot(p.x - prev.x, p.y - prev.y) > R * 0.6)) {
          ctx.moveTo(p.x, p.y);
        } else {
          ctx.lineTo(p.x, p.y);
        }
        started = true;
        prev = p;
      } else {
        started = false;
        prev = null;
      }
    }
    ctx.stroke();
  }

  function drawGraticule() {
    /* Parallèles tous les 30° */
    for (var lat = -60; lat <= 60; lat += 30) {
      var lats = [], lons = [];
      for (var lon = -180; lon <= 180; lon += 4) {
        lats.push(lat); lons.push(lon);
      }
      drawSphereLine(lats, lons, GOLD + '0.16)', 0.7);
    }

    /* Méridiens tous les 30° */
    for (var lon2 = -180; lon2 < 180; lon2 += 30) {
      var la = [], lo = [];
      for (var lt = -90; lt <= 90; lt += 4) {
        la.push(lt); lo.push(lon2);
      }
      drawSphereLine(la, lo, GOLD + '0.13)', 0.7);
    }

    /* Équateur plus marqué */
    var elats = [], elons = [];
    for (var le = -180; le <= 180; le += 4) { elats.push(0); elons.push(le); }
    drawSphereLine(elats, elons, GOLD + '0.32)', 1.0);

    /* Limbe (cercle extérieur) */
    ctx.lineWidth = 1.2;
    ctx.strokeStyle = GOLD + '0.36)';
    ctx.beginPath();
    ctx.arc(cx, cy, R, 0, Math.PI * 2);
    ctx.stroke();
  }

  /* Échantillonne un grand cercle (SLERP) entre A et B avec un
     « bombé » au-dessus de la sphère pour une trajectoire 3D. */
  function arcSamples(arc, N) {
    var a = sphereVec(arc.from.lat, arc.from.lon);
    var b = sphereVec(arc.to.lat,   arc.to.lon);
    var dot = a.x * b.x + a.y * b.y + a.z * b.z;
    if (dot > 1) dot = 1; else if (dot < -1) dot = -1;
    var ang = Math.acos(dot);
    var sinA = Math.sin(ang);
    if (sinA < 1e-6) return null;

    var pts = [];
    var lift = 0.22;          /* 22% de R en plus à l'apex */
    for (var i = 0; i <= N; i++) {
      var t = i / N;
      var sa = Math.sin((1 - t) * ang) / sinA;
      var sb = Math.sin(t * ang) / sinA;
      var x = sa * a.x + sb * b.x;
      var y = sa * a.y + sb * b.y;
      var z = sa * a.z + sb * b.z;
      var h = 1 + lift * Math.sin(t * Math.PI);
      var p = projectVec(x * h, y * h, z * h);
      pts.push(p);
    }
    return pts;
  }

  function drawArc(arc, time) {
    var pts = arcSamples(arc, 56);
    if (!pts) return;

    /* Tracé filaire */
    ctx.lineWidth = 0.9;
    ctx.strokeStyle = GOLD + '0.55)';
    ctx.beginPath();
    var lastVisible = false;
    for (var i = 0; i < pts.length; i++) {
      var p = pts[i];
      var visible = p.z > -0.55;     /* arcs visibles même un peu derrière */
      if (visible) {
        if (!lastVisible) ctx.moveTo(p.x, p.y);
        else ctx.lineTo(p.x, p.y);
        lastVisible = true;
      } else {
        lastVisible = false;
      }
    }
    ctx.stroke();

    /* Impulsion lumineuse qui voyage sur l'arc */
    var pulsePos = (arc.phase + time * arc.speed) % 1;
    var idx = Math.floor(pulsePos * (pts.length - 1));
    var pulse = pts[idx];
    if (pulse && pulse.z > -0.2) {
      var alpha = Math.max(0.15, Math.min(1, (pulse.z + 0.55) / 1.1));
      /* halo */
      var grad = ctx.createRadialGradient(pulse.x, pulse.y, 0, pulse.x, pulse.y, 16);
      grad.addColorStop(0, GOLD_SOFT + (0.55 * alpha) + ')');
      grad.addColorStop(1, GOLD_SOFT + '0)');
      ctx.fillStyle = grad;
      ctx.beginPath();
      ctx.arc(pulse.x, pulse.y, 16, 0, Math.PI * 2);
      ctx.fill();
      /* point central */
      ctx.fillStyle = GOLD_SOFT + alpha + ')';
      ctx.beginPath();
      ctx.arc(pulse.x, pulse.y, 2.6, 0, Math.PI * 2);
      ctx.fill();
    }
  }

  function drawCities(time) {
    for (var i = 0; i < CITIES.length; i++) {
      var c = CITIES[i];
      var p = project(c.lat, c.lon);
      if (p.z <= 0) continue;
      var alpha = Math.max(0.45, Math.min(1, p.z));
      if (c.isHQ) {
        /* Halo pulsant pour Paris (siège UD) */
        var pulse = 0.5 + 0.5 * Math.sin(time * 1.4);
        var rad = 22 + pulse * 9;
        var grad = ctx.createRadialGradient(p.x, p.y, 0, p.x, p.y, rad);
        grad.addColorStop(0, GOLD_SOFT + (0.45 + 0.25 * pulse) + ')');
        grad.addColorStop(1, GOLD_SOFT + '0)');
        ctx.fillStyle = grad;
        ctx.beginPath();
        ctx.arc(p.x, p.y, rad, 0, Math.PI * 2);
        ctx.fill();
        /* Couronne or */
        ctx.strokeStyle = GOLD_SOFT + '0.85)';
        ctx.lineWidth = 1.2;
        ctx.beginPath();
        ctx.arc(p.x, p.y, 6, 0, Math.PI * 2);
        ctx.stroke();
        /* Noyau */
        ctx.fillStyle = GOLD_SOFT + '1)';
        ctx.beginPath();
        ctx.arc(p.x, p.y, 3, 0, Math.PI * 2);
        ctx.fill();
      } else {
        ctx.fillStyle = GOLD + alpha + ')';
        ctx.beginPath();
        ctx.arc(p.x, p.y, 1.8, 0, Math.PI * 2);
        ctx.fill();
      }
    }
  }

  /* --------------------------------------------------------------
   *  Boucle d'animation (~60 FPS)
   * -------------------------------------------------------------- */
  function frame(t) {
    if (!running) return;
    if (!lastT) lastT = t;
    var dt = (t - lastT) / 1000;
    lastT = t;

    if (!reducedMotion.matches) {
      rotation += dt * 0.05;       /* ~ 1 tour toutes les 2 minutes */
    }

    ctx.clearRect(0, 0, W, H);
    drawStars(t / 1000);
    drawAtmosphere();
    drawGraticule();

    /* Tri arrière-plan → avant pour respecter la profondeur */
    var time = t / 1000;
    var sorted = ARCS.slice().sort(function (a, b) {
      var ma = sphereVec((a.from.lat + a.to.lat) / 2, (a.from.lon + a.to.lon) / 2);
      var mb = sphereVec((b.from.lat + b.to.lat) / 2, (b.from.lon + b.to.lon) / 2);
      var pa = projectVec(ma.x, ma.y, ma.z);
      var pb = projectVec(mb.x, mb.y, mb.z);
      return pa.z - pb.z;
    });
    for (var i = 0; i < sorted.length; i++) drawArc(sorted[i], time);

    drawCities(time);

    requestAnimationFrame(frame);
  }

  /* --------------------------------------------------------------
   *  Boot
   * -------------------------------------------------------------- */
  resize();

  var resizeT = 0;
  window.addEventListener('resize', function () {
    clearTimeout(resizeT);
    resizeT = setTimeout(resize, 80);
  }, { passive: true });

  document.addEventListener('visibilitychange', function () {
    if (document.hidden) {
      running = false;
    } else if (!running) {
      running = true;
      lastT = 0;
      requestAnimationFrame(frame);
    }
  });

  if (reducedMotion.matches) {
    /* Rendu unique, sans boucle */
    ctx.clearRect(0, 0, W, H);
    drawStars(0);
    drawAtmosphere();
    drawGraticule();
    for (var k = 0; k < ARCS.length; k++) drawArc(ARCS[k], 0);
    drawCities(0);
  } else {
    requestAnimationFrame(frame);
  }
})();
