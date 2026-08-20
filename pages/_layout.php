<?php
declare(strict_types=1);

/** @var array{title?:string, meta_description?:string, canonical_url?:string, content:string, active?:string, flash?:array<string,string>, errors?:array<string,string>, old?:array<string,string>} $view */
$title = $view['title'] ?? 'Univers Diaspora';
$active = $view['active'] ?? '';
$flash = $view['flash'] ?? [];
$errors = $view['errors'] ?? [];
$old = $view['old'] ?? [];

$config = require __DIR__ . '/../config/config.php';
$baseUrl = ud_site_base_url();
$aiAssistantEnabled = (bool)($config['ai_assistant']['enabled'] ?? false);
$aiWidgetVisible = (bool)($config['ai_assistant']['show_widget'] ?? $aiAssistantEnabled);

/* Contexte IA : slug et titre du service en cours, pour personnaliser le bot. */
$aiContextSlug = trim((string)($view['ai_context_slug'] ?? ''));
$aiContextTitle = trim((string)($view['ai_context_title'] ?? ''));
$aiWelcomeMessage = 'Bonjour, je suis l’assistant d’Univers Diaspora. Indiquez en une phrase votre besoin principal (achat immobilier, création d’entreprise, démarche administrative, formation, voyage…) : je vous oriente vers le service adapté et la prise de rendez-vous.';
if ($aiContextSlug !== '' && $aiContextTitle !== '') {
    $aiWelcomeMessage = 'Bonjour. Vous consultez la page « ' . $aiContextTitle . ' ». '
        . 'Posez-moi une question précise sur ce service (volet, étapes, démarche) et je vous oriente vers la prochaine action utile, ou tapez /services pour voir l’ensemble du catalogue.';
}

/** Liens officiels des réseaux sociaux (affichés dans le pied de page). */
$socialLinks = [
    ['label' => 'YouTube', 'href' => 'https://www.youtube.com/@UniversDiasporaTV'],
    ['label' => 'TikTok', 'href' => 'https://www.tiktok.com/@univers_diaspora'],
    ['label' => 'Instagram', 'href' => 'https://www.instagram.com/univers.diaspora'],
    ['label' => 'LinkedIn', 'href' => 'https://www.linkedin.com/in/univers-diaspora-ab4932401'],
];

$services = function_exists('services_all') ? services_all() : (require __DIR__ . '/../data/services.php');

$serviceSlugs = [];
foreach ($services as $s) {
    if (is_array($s) && array_key_exists('slug', $s)) {
        $serviceSlugs[(string) $s['slug']] = true;
    }
}
$isServicePage = $active !== '' && isset($serviceSlugs[$active]);

$defaultMeta = 'Univers Diaspora : conseils, accompagnement et services pour la diaspora.';
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

$ogImage = ud_public_asset_url('img/logo-univers-diaspora.jpg', $baseUrl);

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
  <!--
   ┌────────────────────────────────────────────────────────────────────┐
   │  Univers Diaspora                                                  │
   │  Site web officiel — conception et développement intégrés          │
   │                                                                    │
   │  Conçu, développé et maintenu par : Studio Univers Diaspora        │
   │  © <?= (int) date('Y') ?> Univers Diaspora — Tous droits réservés.                  │
   │  Toute reproduction, totale ou partielle, est interdite sans       │
   │  autorisation écrite préalable.                                    │
   │                                                                    │
   │  Site : <?= str_pad($baseUrl, 60) ?>│
   │  Contact : contact@universdiaspora.com                             │
   └────────────────────────────────────────────────────────────────────┘
  -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <title><?= h($title) ?></title>
  <meta name="description" content="<?= h($metaDescription) ?>">
  <link rel="canonical" href="<?= h($canonicalUrl) ?>">
  <meta property="og:type" content="website">
  <meta property="og:locale" content="fr_FR">
  <meta property="og:site_name" content="<?= h((string)($config['app']['name'] ?? 'Univers Diaspora')) ?>">
  <meta property="og:title" content="<?= h($title) ?>">
  <meta property="og:description" content="<?= h($metaDescription) ?>">
  <meta property="og:url" content="<?= h($canonicalUrl) ?>">
  <meta property="og:image" content="<?= h($ogImage) ?>">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="<?= h($title) ?>">
  <meta name="twitter:description" content="<?= h($metaDescription) ?>">
  <meta name="twitter:image" content="<?= h($ogImage) ?>">
  <meta name="author" content="Univers Diaspora">
  <meta name="creator" content="Univers Diaspora">
  <meta name="publisher" content="Univers Diaspora">
  <meta name="copyright" content="© <?= (int) date('Y') ?> Univers Diaspora — Tous droits réservés">
  <meta name="generator" content="Studio Univers Diaspora · Plateforme web sur-mesure">
  <meta name="designer" content="Studio Univers Diaspora">
  <meta name="application-name" content="Univers Diaspora">
  <link rel="author" href="<?= h($baseUrl) ?>/?page=apropos">
  <?php
    $appName = (string)($config['app']['name'] ?? 'Univers Diaspora');
    $organizationId = $baseUrl . '/#organization';
    $websiteId = $baseUrl . '/#website';

    $organizationSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        '@id' => $organizationId,
        'name' => $appName,
        'legalName' => $appName,
        'url' => $baseUrl . '/',
        'logo' => $ogImage,
        'image' => $ogImage,
        'email' => 'contact@universdiaspora.com',
        'description' => 'Univers Diaspora : conseil, accompagnement et solutions concrètes pour la diaspora à Paris (18ᵉ et 17ᵉ) et Colombes.',
        'address' => [
            '@type' => 'PostalAddress',
            'streetAddress' => '19, Rue Richomme',
            'addressLocality' => 'Paris',
            'postalCode' => '75018',
            'addressCountry' => 'FR',
        ],
        'areaServed' => ['Paris', 'Colombes', 'Île-de-France', 'Diaspora'],
        'sameAs' => array_values(array_map(static fn(array $s): string => (string)$s['href'], $socialLinks)),
    ];

    $websiteSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        '@id' => $websiteId,
        'url' => $baseUrl . '/',
        'name' => $appName,
        'inLanguage' => 'fr-FR',
        'publisher' => ['@id' => $organizationId],
        'creator' => ['@id' => $organizationId],
        'author' => ['@id' => $organizationId],
        'copyrightHolder' => ['@id' => $organizationId],
        'copyrightYear' => (int) date('Y'),
    ];

    $localBusinessSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'LocalBusiness',
        'name' => $appName,
        'url' => $baseUrl . '/',
        'image' => $ogImage,
        'email' => 'contact@universdiaspora.com',
        'address' => [
            '@type' => 'PostalAddress',
            'streetAddress' => '19, Rue Richomme',
            'addressLocality' => 'Paris',
            'postalCode' => '75018',
            'addressCountry' => 'FR',
        ],
        'areaServed' => ['Paris', 'Colombes', 'Diaspora'],
    ];

    $combinedSchema = [
        '@context' => 'https://schema.org',
        '@graph' => [$organizationSchema, $websiteSchema, $localBusinessSchema],
    ];
  ?>
  <script type="application/ld+json"><?= json_encode($combinedSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
  <meta name="theme-color" content="#1a3462">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <meta name="apple-mobile-web-app-title" content="Univers Diaspora">
  <link rel="manifest" href="<?= h($baseUrl) ?>/manifest.php">
  <?php
    // iOS ignore souvent apple-touch-icon si l’URL contient ?v=…
    $appleTouch = rtrim($baseUrl, '/') . '/' . (ud_assets_public_prefix() === '' ? 'assets/' : ud_assets_public_prefix() . '/assets/') . 'img/pwa/apple-touch-icon.png';
    $icon192Clean = rtrim($baseUrl, '/') . '/' . (ud_assets_public_prefix() === '' ? 'assets/' : ud_assets_public_prefix() . '/assets/') . 'img/pwa/icon-192.png';
  ?>
  <link rel="apple-touch-icon" href="<?= h($appleTouch) ?>">
  <link rel="icon" type="image/png" sizes="192x192" href="<?= h($icon192Clean) ?>">
  <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,600;0,9..144,700;0,9..144,800;1,9..144,600&family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
  <link href="<?= h(ud_public_asset_url('css/style.css', $baseUrl)) ?>" rel="stylesheet">
</head>
<?php $isHome = ($active === '' && ($pageParam === '' || $pageParam === 'home')); ?>
<?php
$pageBgUrl = '';
if ($isHome) {
    $pageBgFs = dirname(__DIR__) . '/public/img/g1.jpg';
    if (is_file($pageBgFs)) {
        $pageBgUrl = rtrim($baseUrl, '/') . '/public/img/g1.jpg';
    }
}
$isApptNav = ($pageParam === 'rendez-vous' || $pageParam === 'appointment' || str_contains((string)($_SERVER['REQUEST_URI'] ?? ''), '/rendez-vous'));
?>
<body class="ud-body ud-cosmos<?= $isHome ? ' ud-home' : '' ?><?= $pageBgUrl !== '' ? ' ud-home--photo' : '' ?>">

<?php if ($pageBgUrl !== ''): ?>
<div
  class="ud-page-bg"
  style="--ud-page-bg-image: url('<?= h($pageBgUrl) ?>')"
  aria-hidden="true"
></div>
<?php endif; ?>

<canvas
  id="udHeroGlobe"
  class="ud-hero-globe ud-hero-globe--full"
  aria-hidden="true"
  role="presentation"
></canvas>

<header class="ud-topbar">
  <nav class="navbar navbar-expand-lg py-2 ud-navbar">
    <div class="container ud-navbar__inner">
      <button class="navbar-toggler ud-toggler d-lg-none flex-shrink-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#udOffcanvas" aria-controls="udOffcanvas" aria-expanded="false" aria-label="Ouvrir le menu">
        <span class="navbar-toggler-icon"></span>
      </button>

      <a class="navbar-brand ud-brand d-flex align-items-center gap-2 text-decoration-none mx-lg-0" href="<?= h($baseUrl) ?>/">
        <img class="ud-brand__logo" src="<?= h(ud_public_asset_url('img/logo-univers-diaspora.jpg', $baseUrl)) ?>" alt="Univers Diaspora" width="46" height="46">
        <span class="ud-brand__name">Univers Diaspora</span>
      </a>

      <a class="ud-topbar__rdv d-lg-none" href="<?= h(ud_appointment_url($baseUrl)) ?>" aria-label="Prendre rendez-vous">
        <span aria-hidden="true">RDV</span>
      </a>

      <div class="collapse navbar-collapse">
        <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
          <li class="nav-item">
            <a class="nav-link ud-navlink<?= $isHome ? ' active' : '' ?>" href="<?= h($baseUrl) ?>/">Accueil</a>
          </li>

          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle ud-navlink<?= $isServicePage ? ' active' : '' ?>" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              Services
            </a>
            <ul class="dropdown-menu dropdown-menu-end ud-dropdown">
              <?php foreach ($services as $s): ?>
                <?php
                  $href = $s['external_url'] ?? ($baseUrl . '/?page=' . urlencode($s['slug']));
                  $ext = function_exists('ud_service_opens_new_tab') ? ud_service_opens_new_tab($s) : !empty($s['external_url']);
                  $navIconSrc = function_exists('service_icon_url')
                    ? service_icon_url((string)($s['icon'] ?? ''), $baseUrl, (string)($s['slug'] ?? ''))
                    : ud_public_asset_url('img/' . basename((string)($s['icon'] ?? '')), $baseUrl);
                ?>
                <li>
                  <a class="dropdown-item d-flex align-items-center gap-2<?= ($active === ($s['slug'] ?? '')) ? ' active' : '' ?>" href="<?= h($href) ?>" <?= $ext ? 'target="_blank" rel="noopener noreferrer"' : '' ?>>
              <img <?= function_exists('service_icon_img_attrs')
                ? service_icon_img_attrs((string)($s['icon'] ?? ''), $baseUrl, (string)($s['slug'] ?? ''), 56, 56, 'ud-dd-icon')
                : 'src="' . h(service_icon_url((string)($s['icon'] ?? ''), $baseUrl, (string)($s['slug'] ?? ''))) . '" alt="" width="56" height="56" class="ud-dd-icon" loading="lazy"' ?>>
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
            <a class="nav-link ud-navlink<?= $isApptNav ? ' active' : '' ?>" href="<?= h(ud_appointment_url($baseUrl)) ?>">Rendez-vous</a>
          </li>

          <li class="nav-item">
            <a class="nav-link ud-navlink ud-nav-cta" href="<?= h($baseUrl) ?>/#contact">Contact</a>
          </li>

          <li class="nav-item">
            <a class="nav-link ud-navlink<?= $active === 'apropos' ? ' active' : '' ?>" href="<?= h($baseUrl) ?>/?page=apropos">À propos</a>
          </li>

          <li class="nav-item">
            <a class="nav-link ud-navlink<?= $active === 'offres-recrutement' ? ' active' : '' ?>" href="<?= h($baseUrl) ?>/?page=offres-recrutement">Offres &amp; recrutement</a>
          </li>
        </ul>
      </div>
    </div>
  </nav>
</header>

<nav class="ud-tabbar d-lg-none" aria-label="Navigation principale">
  <a class="ud-tabbar__item<?= $isHome ? ' is-active' : '' ?>" href="<?= h($baseUrl) ?>/">
    <span class="ud-tabbar__icon" aria-hidden="true">
      <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.85" stroke-linecap="round" stroke-linejoin="round"><path d="M3 10.5 12 3l9 7.5"/><path d="M5.5 9.5V21h13V9.5"/></svg>
    </span>
    <span class="ud-tabbar__label">Accueil</span>
  </a>
  <a class="ud-tabbar__item<?= $isServicePage ? ' is-active' : '' ?>" href="<?= h($baseUrl) ?>/#services">
    <span class="ud-tabbar__icon" aria-hidden="true">
      <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.85" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>
    </span>
    <span class="ud-tabbar__label">Services</span>
  </a>
  <a class="ud-tabbar__item ud-tabbar__item--cta<?= $isApptNav ? ' is-active' : '' ?>" href="<?= h(ud_appointment_url($baseUrl)) ?>">
    <span class="ud-tabbar__icon" aria-hidden="true">
      <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.85" stroke-linecap="round" stroke-linejoin="round"><rect x="3.5" y="5" width="17" height="15.5" rx="2"/><path d="M8 3.5v3M16 3.5v3M3.5 10h17"/></svg>
    </span>
    <span class="ud-tabbar__label">RDV</span>
  </a>
  <a class="ud-tabbar__item" href="<?= h($baseUrl) ?>/#contact">
    <span class="ud-tabbar__icon" aria-hidden="true">
      <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.85" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6.5h16v11H4z"/><path d="m4 7 8 6 8-6"/></svg>
    </span>
    <span class="ud-tabbar__label">Contact</span>
  </a>
  <button type="button" class="ud-tabbar__item" data-bs-toggle="offcanvas" data-bs-target="#udOffcanvas" aria-controls="udOffcanvas">
    <span class="ud-tabbar__icon" aria-hidden="true">
      <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.85" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="3.25"/><path d="M5.5 19.5c1.6-3.2 4-4.8 6.5-4.8s4.9 1.6 6.5 4.8"/></svg>
    </span>
    <span class="ud-tabbar__label">Menu</span>
  </button>
</nav>

<div class="offcanvas offcanvas-end ud-mobile-offcanvas" tabindex="-1" id="udOffcanvas" aria-labelledby="udOffcanvasLabel">
  <div class="offcanvas-header">
    <div class="d-flex align-items-center gap-2">
      <img class="ud-brand__logo" src="<?= h(ud_public_asset_url('img/logo-univers-diaspora.jpg', $baseUrl)) ?>" alt="Univers Diaspora" width="44" height="44">
      <div>
        <div id="udOffcanvasLabel" class="ud-offcanvas__title">Univers Diaspora</div>
        <div class="ud-offcanvas__subtitle">Menu</div>
      </div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Fermer"></button>
  </div>
  <div class="offcanvas-body">
    <div class="ud-mobile-actions mb-3">
      <a class="btn btn-primary w-100 ud-nav-cta" href="<?= h($baseUrl) ?>/#contact">Contact</a>
      <a class="btn btn-outline-primary w-100 ud-nav-cta ud-nav-cta--ghost mt-2" href="<?= h(ud_appointment_url($baseUrl)) ?>">Prendre rendez-vous</a>
    </div>

    <div class="list-group list-group-flush ud-mobile-list">
      <a class="list-group-item list-group-item-action<?= $isHome ? ' active' : '' ?>" href="<?= h($baseUrl) ?>/">Accueil</a>
      <a class="list-group-item list-group-item-action" href="<?= h($baseUrl) ?>/#services">Services</a>
      <a class="list-group-item list-group-item-action" href="<?= h($baseUrl) ?>/#services">Découvrir</a>
      <a class="list-group-item list-group-item-action" href="<?= h(ud_appointment_url($baseUrl)) ?>">Rendez-vous</a>
      <a class="list-group-item list-group-item-action" href="<?= h($baseUrl) ?>/#contact">Contact</a>
      <a class="list-group-item list-group-item-action<?= $active === 'apropos' ? ' active' : '' ?>" href="<?= h($baseUrl) ?>/?page=apropos">À propos</a>
      <a class="list-group-item list-group-item-action<?= $active === 'offres-recrutement' ? ' active' : '' ?>" href="<?= h($baseUrl) ?>/?page=offres-recrutement">Offres &amp; recrutement</a>
      <div class="ud-divider my-2"></div>
      <div class="ud-mobile-list__label px-1 mb-1">Nos services</div>
      <?php foreach ($services as $s): ?>
        <?php
          $href = $s['external_url'] ?? ($baseUrl . '/?page=' . urlencode($s['slug']));
          $ext = function_exists('ud_service_opens_new_tab') ? ud_service_opens_new_tab($s) : !empty($s['external_url']);
          $navIconSrc = function_exists('service_icon_url')
            ? service_icon_url((string)($s['icon'] ?? ''), $baseUrl, (string)($s['slug'] ?? ''))
            : ud_public_asset_url('img/' . basename((string)($s['icon'] ?? '')), $baseUrl);
        ?>
        <a class="list-group-item list-group-item-action d-flex align-items-center gap-2" href="<?= h($href) ?>" <?= $ext ? 'target="_blank" rel="noopener noreferrer"' : '' ?>>
          <img <?= function_exists('service_icon_img_attrs')
            ? service_icon_img_attrs((string)($s['icon'] ?? ''), $baseUrl, (string)($s['slug'] ?? ''), 56, 56, 'ud-dd-icon')
            : 'src="' . h($navIconSrc) . '" alt="" width="56" height="56" class="ud-dd-icon" loading="lazy"' ?>>
          <span><?= h($s['title']) ?></span>
          <?php if (!empty($s['coming_soon'])): ?><span class="ms-auto ud-dd-pill">Bientôt</span><?php endif; ?>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<main id="contenu-principal" class="ud-cosmos-page">
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
          <img class="ud-footer__logo" src="<?= h(ud_public_asset_url('img/logo-univers-diaspora.jpg', $baseUrl)) ?>" alt="" width="48" height="48" loading="lazy">
          <span class="ud-footer__brand">Univers Diaspora</span>
        </div>
        <p class="ud-footer__lead mb-3">Conseil, accompagnement et solutions concrètes pour vos projets — avec une équipe à l’écoute.</p>
        <div class="ud-footer__heading">Réseaux sociaux</div>
        <ul class="ud-footer__social list-unstyled mb-0">
          <?php foreach ($socialLinks as $soc): ?>
            <li>
              <a href="<?= h((string)$soc['href']) ?>" target="_blank" rel="noopener noreferrer"><?= h((string)$soc['label']) ?></a>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
      <div class="col-12 col-sm-6 col-md-4 col-lg-3">
        <div class="ud-footer__heading">Navigation</div>
        <ul class="ud-footer__list list-unstyled mb-0">
          <li><a href="<?= h($baseUrl) ?>/">Accueil</a></li>
          <li><a href="<?= h($baseUrl) ?>/#services">Services</a></li>
          <li><a href="<?= h($baseUrl) ?>/#services">Découvrir</a></li>
          <li><a href="<?= h(ud_appointment_url($baseUrl)) ?>">Rendez-vous</a></li>
          <li><a href="<?= h($baseUrl) ?>/#contact">Contact</a></li>
          <li><a href="<?= h($baseUrl) ?>/?page=apropos">À propos</a></li>
          <li><a href="<?= h($baseUrl) ?>/?page=offres-recrutement">Offres &amp; recrutement</a></li>
        </ul>
      </div>
      <div class="col-12 col-sm-6 col-md-4 col-lg-3">
        <div class="ud-footer__heading">Informations</div>
        <ul class="ud-footer__list list-unstyled mb-0">
          <li><a href="<?= h($baseUrl) ?>/?page=mentions-legales">Mentions légales</a></li>
          <li><a href="<?= h($baseUrl) ?>/?page=politique-confidentialite">Confidentialité</a></li>
          <li><a href="<?= h($baseUrl) ?>/?page=demarrer-maintenant">Démarrer</a></li>
        </ul>
      </div>
      <div class="col-12 col-md-4 col-lg-2 text-sm-start text-md-end">
        <div class="ud-footer__heading">Site</div>
        <p class="ud-footer__url small mb-0"><?= h($baseUrl) ?></p>
      </div>
    </div>
    <div class="ud-footer__bottom mt-4 pt-4">
      <div class="ud-footer__bottom-row">
        <span class="ud-footer__copy">© <?= (int) date('Y') ?> Univers Diaspora — Tous droits réservés</span>
        <a class="ud-footer__signature" href="<?= h($baseUrl) ?>/?page=apropos" aria-label="À propos d’Univers Diaspora">
          <span class="ud-footer__signature-rule" aria-hidden="true"></span>
          <span class="ud-footer__signature-mono" aria-hidden="true">UD</span>
          <span class="ud-footer__signature-text">
            <span class="ud-footer__signature-kicker">Conception &amp; développement</span>
            <span class="ud-footer__signature-brand">Studio Univers Diaspora</span>
          </span>
        </a>
      </div>
      <div class="ud-footer__legal">
        <span>SIRET / RCS — voir <a href="<?= h($baseUrl) ?>/?page=mentions-legales">mentions légales</a></span>
        <span aria-hidden="true">·</span>
        <span>Données — voir <a href="<?= h($baseUrl) ?>/?page=politique-confidentialite">politique de confidentialité</a></span>
      </div>
    </div>
  </div>
</footer>

<aside
  class="ud-cookie"
  id="udCookieBanner"
  role="dialog"
  aria-modal="false"
  aria-labelledby="udCookieTitle"
  aria-describedby="udCookieText"
  hidden
>
  <div class="ud-cookie__panel">
    <div class="ud-cookie__mark" aria-hidden="true"></div>
    <div class="ud-cookie__body">
      <p class="ud-cookie__title" id="udCookieTitle">Respect de votre vie privée</p>
      <p class="ud-cookie__text" id="udCookieText">
        Ce site dépose uniquement des cookies techniques indispensables
        (session et sécurité). Aucune publicité, aucun tracking commercial.
      </p>
      <div class="ud-cookie__row">
        <a class="ud-cookie__more" href="<?= h($baseUrl) ?>/?page=politique-confidentialite">
          Politique de confidentialité
        </a>
        <button type="button" class="ud-cookie__accept" data-cookie-choice="essential">
          Accepter
        </button>
      </div>
    </div>
  </div>
</aside>

<?php if ($aiWidgetVisible): ?>
<div
  class="ud-ai-chat"
  id="udAiChat"
  data-endpoint="<?= h($baseUrl) ?>/?action=ai-chat"
  data-context-slug="<?= h($aiContextSlug) ?>"
  data-context-title="<?= h($aiContextTitle) ?>"
>
  <button class="ud-ai-chat__toggle" id="udAiToggle" type="button" aria-expanded="false" aria-controls="udAiPanel" aria-label="Ouvrir le chatbot IA Univers Diaspora">
    <span class="ud-ai-chat__toggle-ring" aria-hidden="true"></span>
    <span class="ud-ai-chat__toggle-face" aria-hidden="true">
      <span class="ud-ai-chat__toggle-icon ud-ai-chat__toggle-icon--chat">
        <svg viewBox="0 0 48 48" width="30" height="30" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
          <path d="M9 13c0-2.8 2.2-5 5-5h20c2.8 0 5 2.2 5 5v13c0 2.8-2.2 5-5 5H22.2L14 40.2c-.8.6-1.9.1-1.9-.8V31H14c-2.8 0-5-2.2-5-5V13z" fill="#0d1528"/>
          <circle class="ud-ai-dot" cx="18" cy="19.5" r="2.2" fill="#f0d7a0"/>
          <circle class="ud-ai-dot" cx="24" cy="19.5" r="2.2" fill="#f0d7a0"/>
          <circle class="ud-ai-dot" cx="30" cy="19.5" r="2.2" fill="#f0d7a0"/>
        </svg>
      </span>
      <span class="ud-ai-chat__toggle-icon ud-ai-chat__toggle-icon--close">
        <svg viewBox="0 0 24 24" width="22" height="22" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
          <path d="M7 7l10 10M17 7L7 17" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>
        </svg>
      </span>
    </span>
    <span class="ud-ai-chat__toggle-tip">
      <strong>Chatbot IA</strong>
      <span>Discutez avec nous</span>
    </span>
  </button>
  <section class="ud-ai-chat__panel" id="udAiPanel" aria-label="Assistant IA">
    <header class="ud-ai-chat__head">
      <div class="ud-ai-chat__head-brand">
        <span class="ud-ai-chat__head-mark" aria-hidden="true">IA</span>
        <div>
          <div class="ud-ai-chat__title">Chatbot IA</div>
          <div class="ud-ai-chat__subtitle">Univers Diaspora · en ligne</div>
        </div>
      </div>
      <button class="ud-ai-chat__close" id="udAiClose" type="button" aria-label="Fermer">×</button>
    </header>
    <div class="ud-ai-chat__messages" id="udAiMessages">
      <article class="ud-ai-msg ud-ai-msg--bot">
        <?= h($aiWelcomeMessage) ?>
      </article>
      <?php if ($aiContextSlug !== ''): ?>
        <div class="ud-ai-suggest" role="group" aria-label="Suggestions de questions">
          <button type="button" class="ud-ai-suggest__btn" data-prefill="Quels volets propose le service « <?= h($aiContextTitle) ?> » ?">Volets disponibles</button>
          <button type="button" class="ud-ai-suggest__btn" data-prefill="Comment se déroule l’accompagnement « <?= h($aiContextTitle) ?> » ?">Déroulement</button>
          <button type="button" class="ud-ai-suggest__btn" data-prefill="/services">Voir tous les services</button>
        </div>
      <?php endif; ?>
    </div>
    <form class="ud-ai-chat__form" id="udAiForm">
      <label class="visually-hidden" for="udAiInput">Votre message</label>
      <input id="udAiInput" name="message" class="ud-ai-chat__input" maxlength="700" placeholder="<?= $aiContextSlug !== '' ? 'Ex. : Quel volet est adapté à mon besoin ?' : 'Ex. : J’aimerais préparer un achat immobilier à Paris…' ?>" required>
      <button class="ud-ai-chat__send" type="submit">Envoyer</button>
    </form>
  </section>
</div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
(() => {
  const ocEl = document.getElementById('udOffcanvas');
  if (!ocEl || typeof bootstrap === 'undefined') return;

  // Panneau à la racine du body : évite les bugs position:fixed sur iOS / Safari mobile.
  if (ocEl.parentElement !== document.body) {
    document.body.appendChild(ocEl);
  }

  bootstrap.Offcanvas.getOrCreateInstance(ocEl, { scroll: false });

  // Fermer le menu au tap sur un lien, sans bloquer la navigation.
  ocEl.addEventListener('click', (e) => {
    const link = e.target.closest('a[href]');
    if (!link) return;
    const inst = bootstrap.Offcanvas.getInstance(ocEl);
    if (inst) inst.hide();
  });
})();
</script>
<script src="<?= h(ud_public_asset_url('js/cookie-consent.js', $baseUrl)) ?>?v=<?= (int) @filemtime(__DIR__ . '/../public/assets/js/cookie-consent.js') ?>" defer></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script src="<?= h(ud_public_asset_url('js/ud-hero-globe.js', $baseUrl)) ?>?v=<?= (int) filemtime(__DIR__ . '/../public/assets/js/ud-hero-globe.js') ?>" defer></script>
<?php if ($aiWidgetVisible): ?>
<script src="<?= h(ud_public_asset_url('js/ai-assistant.js', $baseUrl)) ?>"></script>
<?php endif; ?>
<script>
(() => {
  // Carte des bureaux : fond clair, marqueurs numérotés, légende
  const mapEl = document.getElementById('udMap');
  if (mapEl && typeof L !== 'undefined') {
    try {
      const offices = JSON.parse(mapEl.getAttribute('data-offices') || '[]');

      const attOsm = '&copy; <a href="https://www.openstreetmap.org/copyright" rel="noopener">OpenStreetMap</a>';
      const attCarto = attOsm + ' &copy; <a href="https://carto.com/attributions" rel="noopener">CARTO</a>';
      const attEsri = 'Tuiles &copy; <a href="https://www.esri.com/" rel="noopener">Esri</a>';

      const layerPlanDetail = L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
        subdomains: 'abcd',
        maxNativeZoom: 20,
        maxZoom: 20,
        attribution: attCarto
      });
      const layerOsm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxNativeZoom: 19,
        maxZoom: 20,
        attribution: attOsm
      });
      const layerSatellite = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
        maxNativeZoom: 19,
        maxZoom: 20,
        attribution: attEsri
      });

      const baseLayers = {
        'Plan (rues & quartiers)': layerPlanDetail,
        'Plan classique (OSM)': layerOsm,
        'Vue satellite': layerSatellite
      };

      const map = L.map(mapEl, {
        scrollWheelZoom: false,
        maxZoom: 20,
        layers: [layerPlanDetail]
      });
      L.control
        .layers(baseLayers, null, { position: 'topright', collapsed: false })
        .addTo(map);

      const bounds = L.latLngBounds([]);
      const markers = [];

      offices.forEach((o, idx) => {
        if (!o || typeof o.lat !== 'number' || typeof o.lon !== 'number') return;
        const n = idx + 1;
        const shortName = String(o.name || '').split('—')[0].trim() || ('Bureau ' + n);
        const icon = L.divIcon({
          className: 'ud-map-pin',
          html: '<div class="ud-map-pin__dot"><span>' + n + '</span></div>',
          iconSize: [32, 32],
          iconAnchor: [16, 30],
          popupAnchor: [0, -28]
        });
        const m = L.marker([o.lat, o.lon], { icon: icon, title: shortName }).addTo(map);
        const name = o.name ? String(o.name) : '';
        const address = o.address ? String(o.address) : '';
        const phone = o.phone ? String(o.phone) : '';
        let accessHtml = '';
        if (Array.isArray(o.access) && o.access.length) {
          accessHtml = '<div style="margin-top:.55rem;padding-top:.45rem;border-top:1px solid rgba(24,40,88,.12)"><strong style="font-size:11px;letter-spacing:.08em;text-transform:uppercase;color:#1e3a6e">Comment venir</strong><ul style="margin:.35rem 0 0;padding:0;list-style:none">';
          o.access.forEach((a) => {
            if (!a) return;
            const t = a.type ? String(a.type) : '';
            const stops = a.stops ? String(a.stops) : '';
            let badges = '';
            if (Array.isArray(a.badges)) {
              a.badges.forEach((b) => {
                if (!b) return;
                const label = b.label != null ? String(b.label) : '';
                const bg = b.bg != null ? String(b.bg) : '#1e3a6e';
                const fg = b.fg != null ? String(b.fg) : '#fff';
                badges += '<span style="display:inline-flex;align-items:center;justify-content:center;min-width:1.35rem;height:1.35rem;padding:0 .35rem;margin:0 .2rem 0 0;border-radius:999px;font-size:11px;font-weight:800;background:' + bg.replace(/"/g, '') + ';color:' + fg.replace(/"/g, '') + '">' + label.replace(/</g, '&lt;') + '</span>';
              });
            }
            accessHtml += '<li style="margin:.28rem 0;display:flex;flex-wrap:wrap;align-items:center;gap:.25rem"><span style="font-weight:700;color:#1e3a6e;margin-right:.15rem">' + t.replace(/</g, '&lt;') + '</span>' + badges + '<span style="opacity:.85">' + stops.replace(/</g, '&lt;') + '</span></li>';
          });
          accessHtml += '</ul></div>';
        }
        const mapsUrl = o.maps_url
          ? String(o.maps_url)
          : ('https://www.google.com/maps/search/?api=1&query=' + encodeURIComponent(o.lat + ',' + o.lon));
        const mapsLink = '<a href="' + mapsUrl.replace(/"/g, '&quot;') + '" target="_blank" rel="noopener noreferrer" style="color:#1e3a6e;font-weight:700;text-decoration:underline">Ouvrir dans Google Maps ↗</a>';
        m.bindPopup(
          '<strong style="color:#1e3a6e">' + name + '</strong><br>' +
          address +
          (phone ? ('<br><span style="opacity:.85">' + phone + '</span>') : '') +
          accessHtml +
          '<br>' + mapsLink
        );
        m.bindTooltip(shortName, {
          permanent: true,
          direction: 'top',
          offset: [0, -34],
          className: 'ud-map-label',
          opacity: 1
        });
        bounds.extend([o.lat, o.lon]);
        markers.push(m);
      });

      if (bounds.isValid()) {
        map.fitBounds(bounds.pad(0.28), { maxZoom: 13 });
      } else {
        map.setView([48.890, 2.33], 11);
      }

      document.querySelectorAll('.ud-location[data-office-idx]').forEach((el) => {
        el.addEventListener('click', (ev) => {
          if (ev.target.closest('a')) return;
          const i = Number(el.getAttribute('data-office-idx'));
          const m = markers[i];
          if (!m) return;
          map.flyTo(m.getLatLng(), 15, { duration: 0.7 });
          m.openPopup();
        }, { passive: true });
      });

      const invalidate = () => map.invalidateSize();
      setTimeout(invalidate, 50);
      setTimeout(invalidate, 350);
      window.addEventListener('resize', () => map.invalidateSize(), { passive: true });
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
<div id="ud-pwa-install" class="ud-pwa-install" hidden>
  <div class="ud-pwa-install__inner">
    <div class="ud-pwa-install__text">
      <strong data-ud-pwa-title>Installer Univers Diaspora</strong>
      <span data-ud-pwa-text>Accès rapide depuis l’écran d’accueil</span>
    </div>
    <div class="ud-pwa-install__actions">
      <button type="button" class="ud-pwa-install__btn" data-ud-pwa-install>Installer</button>
      <button type="button" class="ud-pwa-install__dismiss" data-ud-pwa-dismiss aria-label="Fermer">×</button>
    </div>
  </div>
</div>
<script
  src="<?= h(ud_public_asset_url('js/ud-pwa.js', $baseUrl)) ?>"
  data-sw="<?= h($baseUrl) ?>/sw.js"
  data-scope="<?= h($baseUrl) ?>/"
  defer
></script>
</body>
</html>

