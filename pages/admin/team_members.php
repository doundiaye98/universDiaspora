<?php
declare(strict_types=1);

$config = require __DIR__ . '/../../config/config.php';
$baseUrl = rtrim($config['app']['base_url'], '/');
admin_require_login($baseUrl);

$pdo = db();
$errors = $errors ?? [];
$old = $old ?? [];

$list = team_members_all($pdo);

// Même logique que la page Services : trim + id numérique (évite les échecs silencieux si l’URL est mal formée).
$editRaw = trim((string)($_GET['edit'] ?? ''));
$isNew = ($editRaw === 'new');
$editId = $isNew ? 0 : (int) $editRaw;
$edit = null;
if ($editId > 0) {
    $edit = team_members_find($editId, $pdo);
}

$csrf = admin_csrf_token();

$form = $edit ?? [
    'id' => 0,
    'name' => '',
    'role' => '',
    'bio' => '',
    'sort_order' => 0,
    'photo' => null,
];

if (!empty($old)) {
    $form['id'] = (int)($old['id'] ?? 0);
    $form['name'] = (string)($old['name'] ?? '');
    $form['role'] = (string)($old['role'] ?? '');
    $form['bio'] = (string)($old['bio'] ?? '');
    $form['sort_order'] = (int)($old['sort_order'] ?? 0);
    // Après erreur de validation, on est toujours en édition si un id > 0 est reposté (GET edit peut être absent sur POST).
    if ((int)($form['id'] ?? 0) > 0) {
        $isNew = false;
    }
}

if (!empty($errors) && (int)($form['id'] ?? 0) > 0) {
    $again = team_members_find((int)$form['id'], $pdo);
    if (is_array($again)) {
        $form['photo'] = $again['photo'] ?? null;
    }
}

$photoFile = isset($form['photo']) && is_string($form['photo']) && $form['photo'] !== '' ? $form['photo'] : null;
$photoUrl = $photoFile !== null ? ($baseUrl . '/public/assets/img/team/' . rawurlencode($photoFile)) : null;

ob_start();
?>
<div class="container-fluid px-3 px-md-4 py-3 py-md-4">
  <div class="d-flex flex-column flex-md-row align-items-stretch align-items-md-end justify-content-between gap-3 mb-3">
    <div class="min-w-0">
      <div class="ud-admin-kicker">Site public</div>
      <h1 class="ud-admin-title mb-0">Équipe</h1>
      <div class="ud-admin-sub">Membres affichés sur la page « Notre équipe » (photo professionnelle, poste, texte).</div>
    </div>
    <div class="d-flex flex-wrap gap-2 flex-shrink-0">
      <a class="btn btn-outline-primary ud-btn ud-btn--ghost" href="./?page=equipe" target="_blank" rel="noopener">Voir la page publique</a>
      <a class="btn btn-primary ud-btn ud-btn--shine" href="./?page=admin-team-members&edit=new">+ Ajouter un membre</a>
    </div>
  </div>

  <div class="row g-3 g-lg-4">
    <div class="col-12 col-xl-6">
      <div class="ud-admin-panel">
        <div class="ud-admin-panel__head">
          <div class="ud-admin-panel__title">Membres</div>
          <div class="ud-admin-panel__meta"><?= count($list) ?> entrée(s)</div>
        </div>
        <div class="table-responsive ud-table-scroll">
          <table class="table ud-admin-table mb-0">
            <thead>
              <tr>
                <th></th>
                <th>Nom</th>
                <th>Poste</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($list)): ?>
                <tr><td colspan="4" class="text-muted">Aucun membre. Ajoutez-en un.</td></tr>
              <?php else: ?>
                <?php foreach ($list as $row): ?>
                  <tr>
                    <td class="text-nowrap" style="width:56px;">
                      <?php if (!empty($row['photo'])): ?>
                        <img src="<?= h($baseUrl) ?>/public/assets/img/team/<?= h(basename((string)$row['photo'])) ?>" alt="" width="44" height="44" class="rounded-3 object-fit-cover" style="object-fit:cover;width:44px;height:44px;">
                      <?php else: ?>
                        <span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-light text-muted small" style="width:44px;height:44px;">—</span>
                      <?php endif; ?>
                    </td>
                    <td class="fw-bold"><?= h((string)($row['name'] ?? '')) ?></td>
                    <td class="small"><?= h((string)($row['role'] ?? '')) ?></td>
                    <td class="text-end">
                      <a class="btn btn-sm btn-outline-primary" href="./?page=admin-team-members&edit=<?= (int)($row['id'] ?? 0) ?>">Modifier</a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="col-12 col-xl-6">
      <div class="ud-admin-panel">
        <div class="ud-admin-panel__head">
          <div class="ud-admin-panel__title"><?= $isNew || (int)($form['id'] ?? 0) === 0 ? 'Nouveau membre' : 'Modifier le membre' ?></div>
          <div class="ud-admin-panel__meta"><?= $isNew || (int)($form['id'] ?? 0) === 0 ? 'Création' : 'ID ' . (int)$form['id'] ?></div>
        </div>
        <div class="p-3">
          <form method="post" action="./?action=admin-team-member-save" enctype="multipart/form-data">
            <input type="hidden" name="_csrf" value="<?= h($csrf) ?>">
            <input type="hidden" name="id" value="<?= (int)($form['id'] ?? 0) ?>">

            <div class="row g-3">
              <div class="col-12">
                <label class="form-label">Nom complet <span class="text-danger">*</span></label>
                <input class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" name="name" value="<?= h((string)($form['name'] ?? '')) ?>" required maxlength="200">
                <?php if (isset($errors['name'])): ?><div class="invalid-feedback"><?= h($errors['name']) ?></div><?php endif; ?>
              </div>
              <div class="col-12">
                <label class="form-label">Poste / fonction</label>
                <input class="form-control" name="role" value="<?= h((string)($form['role'] ?? '')) ?>" maxlength="255" placeholder="Ex. Conseiller, Directrice…">
              </div>
              <div class="col-12">
                <label class="form-label">Présentation</label>
                <textarea class="form-control" name="bio" rows="6" maxlength="8000" placeholder="Parcours, missions, valeurs…"><?= h((string)($form['bio'] ?? '')) ?></textarea>
              </div>
              <div class="col-12 col-md-6">
                <label class="form-label">Ordre d’affichage</label>
                <input type="number" class="form-control" name="sort_order" value="<?= (int)($form['sort_order'] ?? 0) ?>">
              </div>
              <div class="col-12">
                <label class="form-label">Photo professionnelle</label>
                <input class="form-control <?= isset($errors['photo']) ? 'is-invalid' : '' ?>" type="file" name="photo" accept="image/jpeg,image/png,image/webp,.jpg,.jpeg,.png,.webp">
                <?php if (isset($errors['photo'])): ?><div class="invalid-feedback d-block"><?= h($errors['photo']) ?></div><?php endif; ?>
                <div class="form-text">Laissez vide pour conserver la photo actuelle lors d’une modification.</div>
              </div>
              <?php if ($photoUrl !== null): ?>
                <div class="col-12">
                  <div class="d-flex align-items-center gap-3 flex-wrap">
                    <img src="<?= h($photoUrl) ?>" alt="" width="96" height="96" class="rounded-3 border" style="object-fit:cover;">
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" value="1" id="udTeamRemovePhoto" name="remove_photo">
                      <label class="form-check-label" for="udTeamRemovePhoto">Supprimer la photo</label>
                    </div>
                  </div>
                </div>
              <?php endif; ?>
              <div class="col-12 d-flex gap-2 flex-wrap">
                <button class="btn btn-primary ud-btn ud-btn--shine" type="submit">Enregistrer</button>
                <?php if ((int)($form['id'] ?? 0) > 0): ?>
                  <button class="btn btn-outline-danger ud-btn" type="submit" form="udDeleteTeamMember">Supprimer</button>
                <?php endif; ?>
              </div>
            </div>
          </form>

          <?php if ((int)($form['id'] ?? 0) > 0): ?>
            <form id="udDeleteTeamMember" method="post" action="./?action=admin-team-member-delete" onsubmit="return confirm('Supprimer ce membre ? La photo sera aussi supprimée.');">
              <input type="hidden" name="_csrf" value="<?= h($csrf) ?>">
              <input type="hidden" name="id" value="<?= (int)($form['id'] ?? 0) ?>">
            </form>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>
<?php
$content = ob_get_clean();

$view = [
    'title' => 'Admin — Équipe',
    'content' => $content,
    'flash' => $flash ?? [],
];

require __DIR__ . '/_layout.php';
