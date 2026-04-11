<?php
declare(strict_types=1);

/** @var array{title?:string, meta_description?:string, canonical_url?:string, content:string, active?:string, flash?:array<string,string>, errors?:array<string,string>, old?:array<string,string>} $view */
$title = $view['title'] ?? 'Univers Diaspora';
$active = $view['active'] ?? '';
$flash = $view['flash'] ?? [];
$errors = $view['errors'] ?? [];
$old = $view['old'] ?? [];

$config = require __DIR__ . '/../config/config.php';
$baseUrl = rtrim($config['app']['base_url'], '/');

$services = function_exists('services_all') ? services_all() : (require __DIR__ . '/../data/services.php');

$serviceSlugs = [];
foreach ($services as $s) {
    if (is_array($s) && array_key_exists('slug', $s)) {
        $serviceSlugs[(string) $s['slug']] = true;
    }
}
$isServicePage = $active !== '' && isset($serviceSlugs[$active]);

$defaultMeta = 'Univers Diaspora : conseils, accompagnement et services pour la diaspora en France et à l\'international.';
$metaDescription = trim((string)($view['meta_description'] ?? ''));
if ($metaDescription === '') {
    $metaDescription = $defaultMeta;
}
$pageParam = isset($_GET['page']) ? trim((string)$_GET['page']) : '';
$canonicalUrl = trim((string)($view['canonical_url'] ?? ''));
if ($canonicalUrl === '') {
    if ($pageParam === '' || $pageParam === 'home') {
        $canonicalUrl = $baseUrl . '/';
    } else {
        $canonicalUrl = $baseUrl . '/?page=' . rawurlencode($pageParam);
    }
}

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
  <meta name="description" content="<?= h($metaDescription) ?>">
  <link rel="canonical" href="<?= h($canonicalUrl) ?>">
  <meta property="og:title" content="<?= h($title) ?>">
  <meta property="og:description" content="<?= h($metaDescription) ?>">
  <meta property="og:url" content="<?= h($canonicalUrl) ?>">
  <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=Montserrat:wght@500;600;700;800;900&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
  <link href="<?= h($baseUrl) ?>/public/assets/css/style.css" rel="stylesheet">
</head>
<?php $isHome = ($active === '' && ($pageParam === '' || $pageParam === 'home')); ?>
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

          <li class="nav-item">
            <a class="nav-link ud-navlink<?= $active === 'apropos' ? ' active' : '' ?>" href="<?= h($baseUrl) ?>/?page=apropos">À propos</a>
          </li>

          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle ud-navlink<?= $isServicePage ? ' active' : '' ?>" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
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
            <a class="nav-link ud-navlink<?= $active === 'equipe' ? ' active' : '' ?>" href="<?= h($baseUrl) ?>/?page=equipe">Équipe</a>
          </li>

          <li class="nav-item">
            <a class="nav-link ud-navlink<?= $active === 'offres-recrutement' ? ' active' : '' ?>" href="<?= h($baseUrl) ?>/?page=offres-recrutement">Offres &amp; recrutement</a>
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
        <a class="btn btn-outline-primary w-100 ud-nav-cta ud-nav-cta--ghost mt-2" href="<?= h($baseUrl) ?>/?page=apropos" data-bs-dismiss="offcanvas">À propos</a>
        <a class="btn btn-outline-primary w-100 ud-nav-cta ud-nav-cta--ghost mt-2" href="<?= h($baseUrl) ?>/#services" data-bs-dismiss="offcanvas">Découvrir nos services</a>
        <a class="btn btn-outline-primary w-100 ud-nav-cta ud-nav-cta--ghost mt-2" href="<?= h($baseUrl) ?>/?page=rendez-vous" data-bs-dismiss="offcanvas">Rendez-vous</a>
        <a class="btn btn-outline-primary w-100 ud-nav-cta ud-nav-cta--ghost mt-2" href="<?= h($baseUrl) ?>/?page=equipe" data-bs-dismiss="offcanvas">Équipe</a>
        <a class="btn btn-outline-primary w-100 ud-nav-cta ud-nav-cta--ghost mt-2" href="<?= h($baseUrl) ?>/?page=offres-recrutement" data-bs-dismiss="offcanvas">Offres &amp; recrutement</a>
      </div>

      <div class="list-group list-group-flush ud-mobile-list">
        <a class="list-group-item list-group-item-action" href="<?= h($baseUrl) ?>/" data-bs-dismiss="offcanvas">Accueil</a>
        <a class="list-group-item list-group-item-action" href="<?= h($baseUrl) ?>/?page=apropos" data-bs-dismiss="offcanvas">À propos</a>
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
    <div class="container mt-3 ud-flash-wrap">
      <div class="alert alert-success ud-flash mb-0 shadow-sm border-0"><?= h($flash['success']) ?></div>
    </div>
  <?php endif; ?>
  <?php if (!empty($flash['error'])): ?>
    <div class="container mt-3 ud-flash-wrap">
      <div class="alert alert-danger ud-flash mb-0 shadow-sm border-0"><?= h($flash['error']) ?></div>
    </div>
  <?php endif; ?>

  <?= $view['content'] ?>
</main>

<footer class="ud-footer mt-5">
  <div class="ud-footer__accent" aria-hidden="true"></div>
  <div class="container py-5">
    <div class="row g-4 g-lg-5">
      <div class="col-12 col-lg-4">
        <div class="d-flex align-items-center gap-2 mb-3">
          <img class="ud-footer__logo" src="<?= h($baseUrl) ?>/public/assets/img/logo-univers-diaspora.jpg" alt="" width="48" height="48" loading="lazy">
          <span class="ud-footer__brand">Univers Diaspora</span>
        </div>
        <p class="ud-footer__lead mb-0">Conseil, accompagnement et solutions concrètes pour vos projets — avec une équipe à l’écoute.</p>
      </div>
      <div class="col-6 col-md-4 col-lg-3">
        <div class="ud-footer__heading">Navigation</div>
        <ul class="ud-footer__list list-unstyled mb-0">
          <li><a href="<?= h($baseUrl) ?>/">Accueil</a></li>
          <li><a href="<?= h($baseUrl) ?>/?page=apropos">À propos</a></li>
          <li><a href="<?= h($baseUrl) ?>/#services">Services</a></li>
          <li><a href="<?= h($baseUrl) ?>/?page=equipe">Équipe</a></li>
          <li><a href="<?= h($baseUrl) ?>/?page=offres-recrutement">Offres &amp; recrutement</a></li>
          <li><a href="<?= h($baseUrl) ?>/?page=rendez-vous">Rendez-vous</a></li>
          <li><a href="<?= h($baseUrl) ?>/#contact">Contact</a></li>
        </ul>
      </div>
      <div class="col-6 col-md-4 col-lg-3">
        <div class="ud-footer__heading">Informations</div>
        <ul class="ud-footer__list list-unstyled mb-0">
          <li><a href="<?= h($baseUrl) ?>/?page=mentions-legales">Mentions légales</a></li>
          <li><a href="<?= h($baseUrl) ?>/?page=politique-confidentialite">Confidentialité</a></li>
          <li><a href="<?= h($baseUrl) ?>/?page=demarrer-maintenant">Démarrer</a></li>
        </ul>
      </div>
      <div class="col-12 col-md-4 col-lg-2 text-md-end">
        <div class="ud-footer__heading">Site</div>
        <p class="ud-footer__url small mb-0"><?= h($baseUrl) ?></p>
      </div>
    </div>
    <div class="ud-footer__bottom mt-4 pt-4">
      <span class="ud-footer__copy">© <?= (int) date('Y') ?> Univers Diaspora — Tous droits réservés</span>
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

