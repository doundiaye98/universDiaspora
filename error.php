<?php
declare(strict_types=1);

/**
 * Pages d'erreur unifiées (404 / 403 / 500). Référencée dans .htaccess :
 *   ErrorDocument 404 /error.php?code=404
 *   ErrorDocument 403 /error.php?code=403
 *   ErrorDocument 500 /error.php?code=500
 */

require_once __DIR__ . '/app/http.php';

$code = (int)($_GET['code'] ?? 404);
if (!in_array($code, [400, 403, 404, 410, 500, 502, 503], true)) {
    $code = 404;
}
http_response_code($code);

$config = @include __DIR__ . '/config/config.php';
$baseUrl = function_exists('ud_site_base_url') ? ud_site_base_url() : '';
if ($baseUrl === '' && is_array($config)) {
    $baseUrl = rtrim((string)($config['app']['base_url'] ?? ''), '/');
}
if ($baseUrl === '') {
    $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https://' : 'http://')
        . (string)($_SERVER['HTTP_HOST'] ?? 'localhost');
}

$messages = [
    400 => ['Requête invalide', 'La requête envoyée n’a pas pu être traitée. Vérifiez l’adresse et réessayez.'],
    403 => ['Accès refusé', 'Vous n’êtes pas autorisé(e) à consulter cette ressource.'],
    404 => ['Page introuvable', 'La page que vous cherchez n’existe pas ou a été déplacée.'],
    410 => ['Contenu retiré', 'Cette page n’est plus disponible.'],
    500 => ['Erreur interne', 'Un incident technique nous empêche d’afficher cette page. Notre équipe a été notifiée.'],
    502 => ['Mauvaise passerelle', 'Le serveur a reçu une réponse invalide. Réessayez dans quelques instants.'],
    503 => ['Service indisponible', 'Le service est momentanément indisponible. Merci de réessayer ultérieurement.'],
];
[$title, $detail] = $messages[$code];
$assetsPrefix = is_array($config) ? trim((string)($config['app']['assets_public_prefix'] ?? 'public'), '/') : 'public';
$logoUrl = function_exists('ud_public_asset_url')
    ? ud_public_asset_url('img/logo-univers-diaspora.jpg', $baseUrl)
    : $baseUrl . '/' . ($assetsPrefix === '' ? '' : $assetsPrefix . '/') . 'assets/img/logo-univers-diaspora.jpg';
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <title><?= htmlspecialchars($code . ' — ' . $title . ' · Univers Diaspora', ENT_QUOTES, 'UTF-8') ?></title>
  <meta name="theme-color" content="#1a3462">
  <style>
    :root { --bg:#0f1729; --fg:#fff; --gold:#d9a04a; --muted:rgba(255,255,255,.7); }
    *,*::before,*::after { box-sizing:border-box; }
    html,body { height:100%; }
    body {
      margin:0;
      font-family: "Plus Jakarta Sans", -apple-system, BlinkMacSystemFont, "Segoe UI", Helvetica, Arial, sans-serif;
      background: radial-gradient(1200px 600px at 50% -10%, rgba(217,160,74,.18), transparent 60%), var(--bg);
      color: var(--fg);
      display: grid; place-items: center;
      padding: 2rem 1.25rem;
      line-height: 1.55;
    }
    .err {
      max-width: 560px;
      width: 100%;
      text-align: center;
    }
    .err__brand {
      display: inline-flex; align-items: center; gap: .6rem;
      margin-bottom: 1.25rem;
      color: var(--muted);
      font-size: .8rem;
      letter-spacing: .14em;
      text-transform: uppercase;
    }
    .err__brand img { width: 32px; height: 32px; border-radius: 8px; }
    .err__code {
      font-family: "Fraunces", Georgia, serif;
      font-size: clamp(4.5rem, 14vw, 8rem);
      font-weight: 800;
      line-height: 1;
      letter-spacing: -.04em;
      color: var(--gold);
      margin: 0 0 .5rem;
    }
    .err__title {
      font-family: "Fraunces", Georgia, serif;
      font-size: clamp(1.4rem, 4vw, 2rem);
      margin: 0 0 .85rem;
    }
    .err__detail {
      color: var(--muted);
      max-width: 32rem;
      margin: 0 auto 1.6rem;
      font-size: .98rem;
    }
    .err__actions { display:flex; flex-wrap:wrap; gap:.6rem; justify-content:center; }
    .btn {
      display:inline-flex; align-items:center; gap:.4rem;
      padding:.7rem 1.1rem; border-radius:10px;
      font-weight:600; text-decoration:none; font-size:.92rem;
      transition: transform .15s ease, background .2s ease, border-color .2s ease;
      border: 1px solid transparent;
    }
    .btn--primary { background: var(--gold); color: #1b1f2b; }
    .btn--primary:hover { transform: translateY(-1px); background: #e8c98a; }
    .btn--ghost {
      background: transparent; color: #fff;
      border-color: rgba(255,255,255,.25);
    }
    .btn--ghost:hover {
      border-color: rgba(232,201,138,.7);
      background: rgba(217,160,74,.08);
    }
    .err__sign {
      margin-top: 2.6rem;
      font-size: .68rem;
      color: rgba(255,255,255,.4);
      letter-spacing: .18em;
      text-transform: uppercase;
    }
    .err__sign a { color: rgba(255,255,255,.6); text-decoration: none; }
    .err__sign a:hover { color: #fff; }
  </style>
</head>
<body>
  <main class="err">
    <div class="err__brand">
      <img src="<?= htmlspecialchars($logoUrl, ENT_QUOTES, 'UTF-8') ?>" alt="Univers Diaspora">
      Univers Diaspora
    </div>
    <p class="err__code"><?= (int)$code ?></p>
    <h1 class="err__title"><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h1>
    <p class="err__detail"><?= htmlspecialchars($detail, ENT_QUOTES, 'UTF-8') ?></p>
    <div class="err__actions">
      <a class="btn btn--primary" href="<?= htmlspecialchars($baseUrl . '/', ENT_QUOTES, 'UTF-8') ?>">Retour à l’accueil</a>
      <a class="btn btn--ghost" href="<?= htmlspecialchars($baseUrl . '/#services', ENT_QUOTES, 'UTF-8') ?>">Voir les services</a>
      <a class="btn btn--ghost" href="<?= htmlspecialchars(ud_appointment_url($baseUrl), ENT_QUOTES, 'UTF-8') ?>">Prendre rendez-vous</a>
    </div>
    <p class="err__sign">
      Conception &amp; développement —
      <a href="<?= htmlspecialchars($baseUrl . '/?page=apropos', ENT_QUOTES, 'UTF-8') ?>">Studio Univers Diaspora</a>
    </p>
  </main>
</body>
</html>
