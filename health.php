<?php
declare(strict_types=1);

/**
 * Diagnostic déploiement Hostinger — supprimez ce fichier après mise en ligne OK.
 */
header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store');

$root = __DIR__;
$checks = [];

$checks[] = ['index.php', is_file($root . '/index.php')];
$checks[] = ['.htaccess', is_file($root . '/.htaccess')];
$checks[] = ['app/bootstrap.php', is_file($root . '/app/bootstrap.php')];
$checks[] = ['config/config.php', is_file($root . '/config/config.php')];
$checks[] = ['config/config.local.php', is_file($root . '/config/config.local.php')];
$checks[] = ['public/assets/css/style.css', is_file($root . '/public/assets/css/style.css')];

$entries = @scandir($root) ?: [];
$entries = array_values(array_filter($entries, static fn(string $e): bool => $e !== '.' && $e !== '..'));
sort($entries);

?><!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex,nofollow">
  <title>Diagnostic Hostinger — Univers Diaspora</title>
  <style>
    body{font-family:system-ui,sans-serif;max-width:42rem;margin:2rem auto;padding:0 1rem;line-height:1.5}
    h1{font-size:1.25rem}
    .ok{color:#0a7a3e;font-weight:700}
    .ko{color:#b42318;font-weight:700}
    code{background:#f4f4f4;padding:.1rem .35rem;border-radius:4px}
    ul{padding-left:1.2rem}
  </style>
</head>
<body>
  <h1>Diagnostic Hostinger</h1>
  <p>Dossier scanné : <code><?= htmlspecialchars($root, ENT_QUOTES, 'UTF-8') ?></code></p>
  <p>PHP <?= htmlspecialchars(PHP_VERSION, ENT_QUOTES, 'UTF-8') ?></p>

  <h2>Fichiers requis</h2>
  <ul>
    <?php foreach ($checks as [$label, $ok]): ?>
      <li><span class="<?= $ok ? 'ok' : 'ko' ?>"><?= $ok ? 'OK' : 'MANQUANT' ?></span> — <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></li>
    <?php endforeach; ?>
  </ul>

  <?php
    $cssFs = $root . '/public/assets/css/style.css';
    $logoFs = $root . '/public/assets/img/logo-univers-diaspora.jpg';
  ?>
  <h2>Design &amp; assets</h2>
  <ul>
    <li><span class="<?= is_file($cssFs) ? 'ok' : 'ko' ?>"><?= is_file($cssFs) ? 'OK' : 'MANQUANT' ?></span> — <code>public/assets/css/style.css</code></li>
    <li><span class="<?= is_file($logoFs) ? 'ok' : 'ko' ?>"><?= is_file($logoFs) ? 'OK' : 'MANQUANT' ?></span> — <code>public/assets/img/logo-univers-diaspora.jpg</code></li>
  </ul>
  <?php if (is_file($cssFs)): ?>
    <p>Test : <a href="public/assets/css/style.css">public/assets/css/style.css</a></p>
  <?php endif; ?>
  <p>Si ces fichiers manquent, uploadez tout le dossier <code>public/assets/</code>.</p>

  <h2>Contenu de ce dossier (racine web)</h2>
  <p><?= htmlspecialchars(implode(', ', $entries), ENT_QUOTES, 'UTF-8') ?></p>

  <?php if (is_file($root . '/index.php')): ?>
    <p><a href="index.php">Ouvrir index.php</a></p>
  <?php endif; ?>

  <h2>Base de données &amp; admin</h2>
  <?php
    $dbOk = false;
    $dbMsg = '';
    $adminRows = [];
    $cfgUser = '';
    try {
        require_once $root . '/app/db.php';
        $cfg = require $root . '/config/config.php';
        $cfgUser = trim((string)($cfg['admin']['username'] ?? ''));
        $pdo = db();
        $dbOk = true;
        $dbMsg = 'Connexion OK — base `' . htmlspecialchars((string)($cfg['db']['name'] ?? ''), ENT_QUOTES, 'UTF-8') . '`';
        $adminRows = $pdo->query('SELECT id, username, is_active, role FROM admin_users ORDER BY id ASC')->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (Throwable $e) {
        $dbMsg = 'Échec: ' . $e->getMessage();
    }
  ?>
  <ul>
    <li><span class="<?= $dbOk ? 'ok' : 'ko' ?>"><?= $dbOk ? 'OK' : 'KO' ?></span> — <?= htmlspecialchars($dbMsg, ENT_QUOTES, 'UTF-8') ?></li>
    <li>Config <code>admin.username</code> : <code><?= htmlspecialchars($cfgUser !== '' ? $cfgUser : '(vide)', ENT_QUOTES, 'UTF-8') ?></code></li>
  </ul>
  <?php if ($dbOk): ?>
    <p>Comptes dans <code>admin_users</code> (<?= count($adminRows) ?>) :</p>
    <ul>
      <?php if (!$adminRows): ?>
        <li class="ko">Aucun compte — ouvrez <code>/reset_admin.php</code> une fois.</li>
      <?php else: ?>
        <?php foreach ($adminRows as $ar): ?>
          <li>
            id=<?= (int)$ar['id'] ?> —
            <code><?= htmlspecialchars((string)$ar['username'], ENT_QUOTES, 'UTF-8') ?></code>
            — <?= !empty($ar['is_active']) ? 'actif' : 'désactivé' ?>
            — <?= htmlspecialchars((string)($ar['role'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
            <?php if ($cfgUser !== '' && (string)$ar['username'] === $cfgUser): ?>
              <span class="ok">(correspond à la config)</span>
            <?php endif; ?>
          </li>
        <?php endforeach; ?>
      <?php endif; ?>
    </ul>
    <p>
      Login : <a href="./?page=admin-login">/?page=admin-login</a>
      — Reset : <a href="./reset_admin.php">/reset_admin.php</a> (à supprimer après usage)
    </p>
  <?php endif; ?>

  <h2>Si vous voyez la 404 Hostinger (« simple accident »)</h2>
  <ol>
    <li>Les fichiers doivent être <strong>directement</strong> dans <code>public_html/</code>, pas dans <code>public_html/universDiaspora/</code>.</li>
    <li>Supprimez l’ancienne page par défaut Hostinger (<code>default.php</code>, <code>index.html</code> vide).</li>
    <li>hPanel → Sites web → votre site → vérifiez que la racine pointe sur <code>public_html</code>.</li>
    <li>Testez : <code>/health.php</code> puis <code>/index.php</code>.</li>
  </ol>
</body>
</html>
