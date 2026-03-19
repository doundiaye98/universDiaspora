<?php
declare(strict_types=1);

/** @var array{title?:string, content:string, active?:string, flash?:array<string,string>, errors?:array<string,string>, old?:array<string,string>} $view */
$title = $view['title'] ?? 'Univers Diaspora';
$active = $view['active'] ?? '';
$flash = $view['flash'] ?? [];
$errors = $view['errors'] ?? [];
$old = $view['old'] ?? [];

$config = require __DIR__ . '/../config/config.php';
$baseUrl = rtrim($config['app']['base_url'], '/');

$services = function_exists('services_all') ? services_all() : (require __DIR__ . '/../data/services.php');

function navLink(string $href, string $label, bool $isActive): string
{
    $cls = 'nav-link' . ($isActive ? ' active' : '');
    return '<a class="' . $cls . '" href="' . h($href) . '">' . h($label) . '</a>';
}

function isExternal(string $href): bool
{
    return (bool) preg_match('~^https?://~i', $href);
}

?><!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= h($title) ?></title>
  <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500;600;700;800;900&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
  <link href="<?= h($baseUrl) ?>/public/assets/css/style.css" rel="stylesheet">
</head>
<?php $isHome = ($active === '' && ($title === 'Univers Diaspora' || $title === 'Univers Diaspora - Accueil' || $title === 'Univers Diaspora')); ?>
<body class="ud-body<?= $isHome ? ' ud-home' : '' ?>">

<header class="ud-topbar">
  <nav class="navbar navbar-expand-lg py-2 ud-navbar">
    <div class="container">
      <a class="navbar-brand ud-brand d-flex align-items-center gap-2 text-decoration-none" href="<?= h($baseUrl) ?>/">
        <img class="ud-brand__logo" src="<?= h($baseUrl) ?>/public/assets/img/logo-univers-diaspora.jpg" alt="Univers Diaspora" width="46" height="46">
        <span class="ud-brand__name">Univers Diaspora</span>
      </a>

      <button class="navbar-toggler ud-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#udOffcanvas" aria-controls="udOffcanvas" aria-label="Ouvrir le menu">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse">
        <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
          <li class="nav-item">
            <a class="nav-link ud-navlink<?= $isHome ? ' active' : '' ?>" href="<?= h($baseUrl) ?>/">Accueil</a>
          </li>

          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle ud-navlink<?= $active !== '' ? ' active' : '' ?>" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              Services
            </a>
            <ul class="dropdown-menu dropdown-menu-end ud-dropdown">
              <?php foreach ($services as $s): ?>
                <?php
                  $href = $s['external_url'] ?? ($baseUrl . '/?page=' . urlencode($s['slug']));
                  $ext = isset($s['external_url']);
                ?>
                <li>
                  <a class="dropdown-item d-flex align-items-center gap-2<?= ($active === ($s['slug'] ?? '')) ? ' active' : '' ?>" href="<?= h($href) ?>" <?= $ext ? 'target="_blank" rel="noopener noreferrer"' : '' ?>>
                    <img class="ud-dd-icon" src="<?= h($baseUrl) ?>/public/assets/img/<?= h($s['icon']) ?>" alt="" width="22" height="22">
                    <span><?= h($s['title']) ?></span>
                    <?php if (!empty($s['coming_soon'])): ?>
                      <span class="ms-auto ud-dd-pill">Bientôt</span>
                    <?php endif; ?>
                  </a>
                </li>
              <?php endforeach; ?>
            </ul>
          </li>

          <li class="nav-item">
            <a class="nav-link ud-navlink" href="<?= h($baseUrl) ?>/#services">Découvrir</a>
          </li>

          <li class="nav-item">
            <a class="nav-link ud-navlink" href="<?= h($baseUrl) ?>/?page=rendez-vous">Rendez-vous</a>
          </li>

          <li class="nav-item ms-lg-2">
            <a class="btn btn-primary ud-nav-cta" href="<?= h($baseUrl) ?>/#contact">Contact</a>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <div class="offcanvas offcanvas-end" tabindex="-1" id="udOffcanvas" aria-labelledby="udOffcanvasLabel">
    <div class="offcanvas-header">
      <div class="d-flex align-items-center gap-2">
        <img class="ud-brand__logo" src="<?= h($baseUrl) ?>/public/assets/img/logo-univers-diaspora.jpg" alt="Univers Diaspora" width="44" height="44">
        <div>
          <div id="udOffcanvasLabel" class="ud-offcanvas__title">Univers Diaspora</div>
          <div class="ud-offcanvas__subtitle">Menu</div>
        </div>
      </div>
      <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Fermer"></button>
    </div>
    <div class="offcanvas-body">
      <div class="ud-mobile-actions mb-3">
        <a class="btn btn-primary w-100 ud-nav-cta" href="<?= h($baseUrl) ?>/#contact" data-bs-dismiss="offcanvas">Contact</a>
        <a class="btn btn-outline-primary w-100 ud-nav-cta ud-nav-cta--ghost mt-2" href="<?= h($baseUrl) ?>/#services" data-bs-dismiss="offcanvas">Découvrir nos services</a>
        <a class="btn btn-outline-primary w-100 ud-nav-cta ud-nav-cta--ghost mt-2" href="<?= h($baseUrl) ?>/?page=rendez-vous" data-bs-dismiss="offcanvas">Rendez-vous</a>
      </div>

      <div class="list-group list-group-flush ud-mobile-list">
        <a class="list-group-item list-group-item-action" href="<?= h($baseUrl) ?>/" data-bs-dismiss="offcanvas">Accueil</a>
        <div class="ud-divider my-2"></div>
        <?php foreach ($services as $s): ?>
          <?php
            $href = $s['external_url'] ?? ($baseUrl . '/?page=' . urlencode($s['slug']));
            $ext = isset($s['external_url']);
          ?>
          <a class="list-group-item list-group-item-action d-flex align-items-center gap-2" href="<?= h($href) ?>" <?= $ext ? 'target="_blank" rel="noopener noreferrer"' : '' ?> data-bs-dismiss="offcanvas">
            <img class="ud-dd-icon" src="<?= h($baseUrl) ?>/public/assets/img/<?= h($s['icon']) ?>" alt="" width="22" height="22">
            <span><?= h($s['title']) ?></span>
            <?php if (!empty($s['coming_soon'])): ?><span class="ms-auto ud-dd-pill">Bientôt</span><?php endif; ?>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</header>

<main>
  <?php if (!empty($flash['success'])): ?>
    <div class="container mt-3">
      <div class="alert alert-success mb-0"><?= h($flash['success']) ?></div>
    </div>
  <?php endif; ?>
  <?php if (!empty($flash['error'])): ?>
    <div class="container mt-3">
      <div class="alert alert-danger mb-0"><?= h($flash['error']) ?></div>
    </div>
  <?php endif; ?>

  <?= $view['content'] ?>
</main>

<footer class="ud-footer mt-5 py-4">
  <div class="container">
    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
      <div>
        <div class="ud-footer__brand">Univers Diaspora</div>
        <div class="small text-muted">© <?= (int) date('Y') ?> — Tous droits réservés</div>
      </div>
      <div class="small text-muted">Local: <?= h($baseUrl) ?></div>
    </div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
(() => {
  // Leaflet map (Contact)
  const mapEl = document.getElementById('udMap');
  if (mapEl && typeof L !== 'undefined') {
    try {
      const offices = JSON.parse(mapEl.getAttribute('data-offices') || '[]');
      const map = L.map(mapEl, { scrollWheelZoom: false });
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap'
      }).addTo(map);

      const bounds = L.latLngBounds([]);
      offices.forEach(o => {
        if (!o || typeof o.lat !== 'number' || typeof o.lon !== 'number') return;
        const m = L.marker([o.lat, o.lon]).addTo(map);
        const name = o.name ? String(o.name) : '';
        const address = o.address ? String(o.address) : '';
        const phone = o.phone ? String(o.phone) : '';
        m.bindPopup(`<strong>${name}</strong><br>${address}<br><span style="opacity:.85">${phone}</span>`);
        bounds.extend([o.lat, o.lon]);
      });
      if (bounds.isValid()) {
        map.fitBounds(bounds.pad(0.22));
      } else {
        map.setView([48.890, 2.33], 12);
      }

      // Fix sizing issues: ensure correct size when visible
      const invalidate = () => map.invalidateSize();
      setTimeout(invalidate, 50);
      setTimeout(invalidate, 350);
      window.addEventListener('resize', () => map.invalidateSize(), { passive: true });

      // Enable zoom only after user interacts
      map.on('click', () => map.scrollWheelZoom.enable());

      if ('IntersectionObserver' in window) {
        const io = new IntersectionObserver((entries) => {
          const e = entries[0];
          if (e && e.isIntersecting) {
            invalidate();
            io.disconnect();
          }
        }, { threshold: 0.25 });
        io.observe(mapEl);
      }
    } catch (e) {
      // ignore
    }
  }

  const header = document.querySelector('.ud-topbar');
  if (!header) return;
  const onScroll = () => {
    const y = window.scrollY || document.documentElement.scrollTop || 0;
    header.classList.toggle('ud-scrolled', y > 14);
  };
  onScroll();
  window.addEventListener('scroll', onScroll, { passive: true });
})();
</script>
</body>
</html>

