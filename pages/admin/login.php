<?php
declare(strict_types=1);

$config = require __DIR__ . '/../../config/config.php';
$baseUrl = rtrim($config['app']['base_url'], '/');

$old = $old ?? [];
$errors = $errors ?? [];

ob_start();
?>
<section class="ud-admin-wrap py-5">
  <div class="container px-3 px-sm-4">
    <div class="row justify-content-center">
      <div class="col-12 col-md-8 col-lg-5">
        <div class="ud-admin-card">
          <div class="ud-admin-brand">
            <img src="<?= h(ud_public_asset_url('img/logo-univers-diaspora.jpg', $baseUrl)) ?>" alt="Univers Diaspora" width="54" height="54">
            <div>
              <div class="ud-admin-brand__title">Admin</div>
              <div class="ud-admin-brand__sub">Univers Diaspora</div>
            </div>
          </div>

          <form class="mt-4" method="post" action="<?= h($baseUrl) ?>/?action=admin-login">
            <div class="mb-3">
              <label class="form-label">Utilisateur</label>
              <input class="form-control <?= isset($errors['username']) ? 'is-invalid' : '' ?>" name="username" value="<?= h($old['username'] ?? '') ?>" autocomplete="username" required>
              <?php if (isset($errors['username'])): ?><div class="invalid-feedback"><?= h($errors['username']) ?></div><?php endif; ?>
            </div>
            <div class="mb-3">
              <label class="form-label">Mot de passe</label>
              <input type="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" name="password" autocomplete="current-password" required>
              <?php if (isset($errors['password'])): ?><div class="invalid-feedback"><?= h($errors['password']) ?></div><?php endif; ?>
            </div>
            <button class="btn btn-primary w-100 ud-btn ud-btn--shine" type="submit">
              Se connecter <span class="ud-arrow" aria-hidden="true">→</span>
            </button>
          </form>

          <div class="ud-admin-foot mt-3">
            <a class="small text-decoration-none" href="<?= h($baseUrl) ?>/">← Retour au site</a>
          </div>
        </div>
        <div class="text-center small text-muted mt-3">
          Accès caché — aucun lien public.
        </div>
      </div>
    </div>
  </div>
</section>
<?php
$content = ob_get_clean();

$view = [
    'title' => 'Admin - Connexion',
    'active' => '',
    'content' => $content,
    'flash' => $flash ?? [],
    'errors' => $errors ?? [],
    'old' => $old ?? [],
];

require __DIR__ . '/../_layout.php';

