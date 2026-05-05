<?php
declare(strict_types=1);

/** @var array{title?:string, heading?:string, content:string, flash?:array<string,string>} $view */
$title = $view['title'] ?? 'Admin';
$content = $view['content'] ?? '';
$flash = $view['flash'] ?? [];
$adminHeading = trim((string)($view['heading'] ?? ''));
if ($adminHeading === '') {
    $adminHeading = preg_replace('~^Admin\s*-\s*~i', '', $title);
}
if ($adminHeading === '') {
    $adminHeading = 'Administration';
}

$config = require __DIR__ . '/../../config/config.php';
$baseUrl = rtrim($config['app']['base_url'], '/');

admin_require_login($baseUrl);
$csrf = admin_csrf_token();
$currentPage = (string)($_GET['page'] ?? 'admin');
$canManageAdmins = admin_has_min_role('super_admin');

function adminNavLink(string $href, string $label, bool $active = false, bool $danger = false): string
{
    $cls = 'ud-admin-nav__link' . ($active ? ' is-active' : '') . ($danger ? ' ud-admin-nav__link--danger' : '');
    return '<a class="' . $cls . '" href="' . h($href) . '">' . h($label) . '</a>';
}

?><!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <title><?= h($title) ?></title>
  <meta name="theme-color" content="#121a2e">
  <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,700;9..144,800&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="<?= h(ud_public_asset_url('css/style.css', $baseUrl)) ?>" rel="stylesheet">
</head>
<body class="ud-body ud-admin-shell">

<div class="ud-admin-shell__wrap">
  <!-- Mobile topbar -->
  <header class="ud-admin-topbar d-lg-none">
    <div class="container-fluid d-flex align-items-center justify-content-between">
      <button class="btn ud-admin-topbar__btn" type="button" data-bs-toggle="offcanvas" data-bs-target="#udAdminNav" aria-controls="udAdminNav" aria-label="Ouvrir le menu admin">
        <span class="ud-admin-topbar__burger"></span>
      </button>
      <div class="ud-admin-topbar__title">Admin</div>
      <a class="btn ud-admin-topbar__btn" href="<?= h($baseUrl) ?>/?action=admin-logout">Déconnexion</a>
    </div>
  </header>

  <!-- Desktop sidebar -->
  <aside class="ud-admin-side d-none d-lg-flex">
    <a class="ud-admin-side__brand" href="<?= h($baseUrl) ?>/?page=admin">
      <img src="<?= h(ud_public_asset_url('img/logo-univers-diaspora.jpg', $baseUrl)) ?>" alt="" width="44" height="44">
      <div>
        <div class="ud-admin-side__title">Admin</div>
        <div class="ud-admin-side__sub">Univers Diaspora</div>
      </div>
    </a>

    <nav class="ud-admin-nav">
      <?= adminNavLink($baseUrl . '/?page=admin', 'Dashboard', $currentPage === 'admin') ?>
      <?= adminNavLink($baseUrl . '/?page=admin-services', 'Services', $currentPage === 'admin-services') ?>
      <?= adminNavLink($baseUrl . '/?page=admin-announcements', 'Offres &amp; recrutement', $currentPage === 'admin-announcements') ?>
      <?= adminNavLink($baseUrl . '/?page=admin-team-members', 'Équipe', $currentPage === 'admin-team-members') ?>
      <?= adminNavLink($baseUrl . '/?page=admin-testimonials', 'Témoignages', $currentPage === 'admin-testimonials') ?>
      <?= adminNavLink($baseUrl . '/?page=admin-job-applications', 'Candidatures', $currentPage === 'admin-job-applications') ?>
      <?php if ($canManageAdmins): ?><?= adminNavLink($baseUrl . '/?page=admin-admins', 'Administrateurs', $currentPage === 'admin-admins') ?><?php endif; ?>
      <?= adminNavLink($baseUrl . '/?page=admin-messages', 'Inbox', $currentPage === 'admin-messages') ?>
      <div class="ud-admin-nav__divider"></div>
      <?= adminNavLink($baseUrl . '/', 'Voir le site') ?>
      <?= adminNavLink($baseUrl . '/?action=admin-logout', 'Déconnexion', false, true) ?>
    </nav>

    <div class="ud-admin-side__foot small text-muted">
      CSRF: <?= h(substr($csrf, 0, 8)) ?>…
    </div>
  </aside>

  <!-- Mobile offcanvas nav -->
  <div class="offcanvas offcanvas-start ud-admin-offcanvas d-lg-none" tabindex="-1" id="udAdminNav" aria-labelledby="udAdminNavLabel">
    <div class="offcanvas-header">
      <div class="d-flex align-items-center gap-2">
        <img src="<?= h(ud_public_asset_url('img/logo-univers-diaspora.jpg', $baseUrl)) ?>" alt="" width="42" height="42" style="border-radius:14px;">
        <div>
          <div id="udAdminNavLabel" class="ud-admin-side__title">Admin</div>
          <div class="ud-admin-side__sub">Univers Diaspora</div>
        </div>
      </div>
      <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Fermer"></button>
    </div>
    <div class="offcanvas-body">
      <nav class="ud-admin-nav">
        <?= adminNavLink($baseUrl . '/?page=admin', 'Dashboard', $currentPage === 'admin') ?>
        <?= adminNavLink($baseUrl . '/?page=admin-services', 'Services', $currentPage === 'admin-services') ?>
        <?= adminNavLink($baseUrl . '/?page=admin-announcements', 'Offres &amp; recrutement', $currentPage === 'admin-announcements') ?>
        <?= adminNavLink($baseUrl . '/?page=admin-team-members', 'Équipe', $currentPage === 'admin-team-members') ?>
        <?= adminNavLink($baseUrl . '/?page=admin-testimonials', 'Témoignages', $currentPage === 'admin-testimonials') ?>
        <?= adminNavLink($baseUrl . '/?page=admin-job-applications', 'Candidatures', $currentPage === 'admin-job-applications') ?>
        <?php if ($canManageAdmins): ?><?= adminNavLink($baseUrl . '/?page=admin-admins', 'Administrateurs', $currentPage === 'admin-admins') ?><?php endif; ?>
        <?= adminNavLink($baseUrl . '/?page=admin-messages', 'Inbox', $currentPage === 'admin-messages') ?>
        <div class="ud-admin-nav__divider"></div>
        <?= adminNavLink($baseUrl . '/', 'Voir le site') ?>
        <?= adminNavLink($baseUrl . '/?action=admin-logout', 'Déconnexion', false, true) ?>
      </nav>
      <div class="small text-muted mt-3">CSRF: <?= h(substr($csrf, 0, 8)) ?>…</div>
    </div>
  </div>

  <div class="ud-admin-main">
    <header class="ud-admin-main__top">
      <div class="min-w-0">
        <h1 class="ud-admin-main__page-title"><?= h($adminHeading) ?></h1>
        <p class="ud-admin-main__meta mb-0">Connecté en tant que <strong><?= h($_SESSION['admin']['username'] ?? 'admin') ?></strong> · rôle <strong><?= h(admin_role()) ?></strong></p>
      </div>
    </header>

    <?php if (!empty($flash['success'])): ?>
      <div class="container-fluid px-3 px-md-4 mt-3">
        <div class="alert alert-success mb-0"><?= h($flash['success']) ?></div>
      </div>
    <?php endif; ?>
    <?php if (!empty($flash['error'])): ?>
      <div class="container-fluid px-3 px-md-4 mt-3">
        <div class="alert alert-danger mb-0"><?= h($flash['error']) ?></div>
      </div>
    <?php endif; ?>

    <main class="ud-admin-main__content ud-admin-main__content--padded">
      <?= $content ?>
    </main>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

