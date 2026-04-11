<?php
declare(strict_types=1);

$config = require __DIR__ . '/../../config/config.php';
$baseUrl = rtrim($config['app']['base_url'], '/');
admin_require_login($baseUrl);

$pdo = db();
$admins = $pdo->query('SELECT id, username, is_active, created_at FROM admin_users ORDER BY id ASC')->fetchAll();

$csrf = admin_csrf_token();
$errors = $errors ?? [];
$old = $old ?? [];

ob_start();
?>
<div class="container-fluid px-3 px-md-4 py-3 py-md-4">
  <div class="d-flex align-items-end justify-content-between flex-wrap gap-2 mb-3">
    <div class="min-w-0">
      <div class="ud-admin-kicker">Gestion</div>
      <h1 class="ud-admin-title mb-0">Administrateurs</h1>
      <div class="ud-admin-sub">Ajouter des admins, activer/désactiver, reset mot de passe.</div>
    </div>
  </div>

  <div class="row g-3 g-lg-4">
    <div class="col-12 col-xl-7">
      <div class="ud-admin-panel">
        <div class="ud-admin-panel__head">
          <div class="ud-admin-panel__title">Liste</div>
          <div class="ud-admin-panel__meta"><?= count($admins) ?> admins</div>
        </div>
        <div class="table-responsive">
          <table class="table ud-admin-table mb-0">
            <thead>
              <tr>
                <th>ID</th>
                <th>Utilisateur</th>
                <th>Actif</th>
                <th>Créé</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($admins as $a): ?>
                <tr>
                  <td class="text-nowrap"><?= (int)$a['id'] ?></td>
                  <td class="fw-bold"><?= h((string)$a['username']) ?></td>
                  <td><?= !empty($a['is_active']) ? 'Oui' : 'Non' ?></td>
                  <td class="text-nowrap"><?= h(date('d/m/Y', strtotime((string)$a['created_at']))) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="col-12 col-xl-5">
      <div class="ud-admin-panel">
        <div class="ud-admin-panel__head">
          <div class="ud-admin-panel__title">Ajouter / Modifier</div>
          <div class="ud-admin-panel__meta">Admin</div>
        </div>
        <div class="p-3">
          <form method="post" action="<?= h($baseUrl) ?>/?action=admin-user-save">
            <input type="hidden" name="_csrf" value="<?= h($csrf) ?>">
            <div class="mb-3">
              <label class="form-label">ID (laisser vide pour créer)</label>
              <input class="form-control" name="id" value="<?= h((string)($old['id'] ?? '')) ?>" placeholder="ex: 2">
            </div>
            <div class="mb-3">
              <label class="form-label">Utilisateur</label>
              <input class="form-control <?= isset($errors['username']) ? 'is-invalid' : '' ?>" name="username" value="<?= h((string)($old['username'] ?? '')) ?>" required>
              <?php if (isset($errors['username'])): ?><div class="invalid-feedback"><?= h($errors['username']) ?></div><?php endif; ?>
            </div>
            <div class="mb-3">
              <label class="form-label">Mot de passe (laisser vide pour ne pas changer)</label>
              <input type="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" name="password" value="">
              <?php if (isset($errors['password'])): ?><div class="invalid-feedback"><?= h($errors['password']) ?></div><?php endif; ?>
            </div>
            <div class="form-check mb-3">
              <input class="form-check-input" type="checkbox" value="1" id="isActive" name="is_active" <?= (($old['is_active'] ?? '1') === '1') ? 'checked' : '' ?>>
              <label class="form-check-label" for="isActive">Compte actif</label>
            </div>
            <button class="btn btn-primary w-100 ud-btn ud-btn--shine" type="submit">
              Enregistrer <span class="ud-arrow" aria-hidden="true">→</span>
            </button>
            <div class="small text-muted mt-2">
              Pour modifier: mets l’ID de l’admin existant.
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
<?php
$content = ob_get_clean();

$view = [
    'title' => 'Admin - Administrateurs',
    'content' => $content,
    'flash' => $flash ?? [],
];

require __DIR__ . '/_layout.php';

