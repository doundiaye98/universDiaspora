<?php
declare(strict_types=1);

$config = require __DIR__ . '/../../config/config.php';
$baseUrl = rtrim($config['app']['base_url'], '/');
admin_require_login($baseUrl);

$pdo = db();
$errors = $errors ?? [];
$old = $old ?? [];

$list = announcements_all($pdo, false, null);

$editParam = (string)($_GET['edit'] ?? '');
if ($editParam === 'new') {
    $editId = 0;
    $isNew = true;
    $edit = null;
} elseif ($editParam !== '' && ctype_digit($editParam)) {
    $editId = (int)$editParam;
    $isNew = false;
    $edit = $editId > 0 ? announcements_find($editId, $pdo) : null;
} else {
    $editId = 0;
    $isNew = true;
    $edit = null;
}

$csrf = admin_csrf_token();

$form = $edit ?? [
    'id' => 0,
    'category' => 'offre',
    'title' => '',
    'summary' => '',
    'content' => '',
    'sort_order' => 0,
    'is_published' => true,
];

if (!empty($old)) {
    $form['id'] = (int)($old['id'] ?? 0);
    $form['category'] = in_array((string)($old['category'] ?? ''), ['offre', 'recrutement'], true)
        ? (string)$old['category']
        : 'offre';
    $form['title'] = (string)($old['title'] ?? '');
    $form['summary'] = (string)($old['summary'] ?? '');
    $form['content'] = (string)($old['content'] ?? '');
    $form['sort_order'] = (int)($old['sort_order'] ?? 0);
    $form['is_published'] = (($old['is_published'] ?? '') === '1');
}

ob_start();
?>
<div class="container-fluid px-3 px-md-4 py-3 py-md-4">
  <div class="d-flex flex-column flex-md-row align-items-stretch align-items-md-end justify-content-between gap-3 mb-3">
    <div class="min-w-0">
      <p class="ud-admin-page-lead text-muted mb-0">Offres commerciales et postes à pourvoir — visibles sur la page publique dédiée.</p>
    </div>
    <a class="btn btn-primary ud-btn ud-btn--shine flex-shrink-0 align-self-md-center" href="<?= h($baseUrl) ?>/?page=admin-announcements&edit=new">
      + Nouvelle annonce
    </a>
  </div>

  <div class="row g-3 g-lg-4">
    <div class="col-12 col-xl-6">
      <div class="ud-admin-panel">
        <div class="ud-admin-panel__head">
          <div class="ud-admin-panel__title">Annonces</div>
          <div class="ud-admin-panel__meta"><?= count($list) ?> entrée(s)</div>
        </div>
        <div class="table-responsive">
          <table class="table ud-admin-table mb-0">
            <thead>
              <tr>
                <th>Type</th>
                <th>Titre</th>
                <th>Publié</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($list)): ?>
                <tr><td colspan="4" class="text-muted">Aucune annonce.</td></tr>
              <?php else: ?>
                <?php foreach ($list as $row): ?>
                  <tr>
                    <td class="text-nowrap">
                      <span class="ud-admin-badge <?= ($row['category'] ?? '') === 'recrutement' ? 'ud-admin-badge--appointment' : 'ud-admin-badge--contact' ?>">
                        <?= ($row['category'] ?? '') === 'recrutement' ? 'Recrutement' : 'Offre' ?>
                      </span>
                    </td>
                    <td class="fw-bold"><?= h((string)($row['title'] ?? '')) ?></td>
                    <td><?= !empty($row['is_published']) ? 'Oui' : 'Non' ?></td>
                    <td class="text-end">
                      <a class="btn btn-sm btn-outline-primary" href="<?= h($baseUrl) ?>/?page=admin-announcements&edit=<?= (int)($row['id'] ?? 0) ?>">Modifier</a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
        <div class="ud-admin-public-cta border-top">
          <div class="ud-admin-public-cta__inner">
            <div class="ud-admin-public-cta__head">
              <div class="ud-admin-public-cta__icon" aria-hidden="true">↗</div>
              <div class="ud-admin-public-cta__text">
                <div class="ud-admin-public-cta__title">Aperçu côté visiteurs</div>
                <p class="ud-admin-public-cta__desc mb-0">Les annonces <strong>publiées</strong> apparaissent sur la page dédiée du site. Ouvrez-la dans un nouvel onglet pour vérifier le rendu.</p>
              </div>
            </div>
            <a class="btn btn-primary ud-btn ud-btn--shine ud-admin-public-cta__btn" href="<?= h($baseUrl) ?>/?page=offres-recrutement" target="_blank" rel="noopener">
              Ouvrir la page « Offres &amp; recrutement »
            </a>
          </div>
        </div>
      </div>
    </div>

    <div class="col-12 col-xl-6">
      <div class="ud-admin-panel">
        <div class="ud-admin-panel__head">
          <div class="ud-admin-panel__title"><?= $isNew || (int)($form['id'] ?? 0) === 0 ? 'Nouvelle annonce' : 'Modifier l’annonce' ?></div>
          <div class="ud-admin-panel__meta"><?= $isNew || (int)($form['id'] ?? 0) === 0 ? 'Création' : 'ID ' . (int)$form['id'] ?></div>
        </div>
        <div class="p-3">
          <form method="post" action="<?= h($baseUrl) ?>/?action=admin-announcement-save">
            <input type="hidden" name="_csrf" value="<?= h($csrf) ?>">
            <input type="hidden" name="id" value="<?= (int)($form['id'] ?? 0) ?>">

            <div class="row g-3">
              <div class="col-12">
                <label class="form-label">Type</label>
                <select class="form-select <?= isset($errors['category']) ? 'is-invalid' : '' ?>" name="category" required>
                  <option value="offre" <?= (($form['category'] ?? '') === 'offre') ? 'selected' : '' ?>>Offre commerciale</option>
                  <option value="recrutement" <?= (($form['category'] ?? '') === 'recrutement') ? 'selected' : '' ?>>Recrutement</option>
                </select>
                <?php if (isset($errors['category'])): ?><div class="invalid-feedback"><?= h($errors['category']) ?></div><?php endif; ?>
              </div>
              <div class="col-12">
                <label class="form-label">Titre</label>
                <input class="form-control <?= isset($errors['title']) ? 'is-invalid' : '' ?>" name="title" value="<?= h((string)($form['title'] ?? '')) ?>" required maxlength="190">
                <?php if (isset($errors['title'])): ?><div class="invalid-feedback"><?= h($errors['title']) ?></div><?php endif; ?>
              </div>
              <div class="col-12">
                <label class="form-label">Résumé (liste)</label>
                <input class="form-control" name="summary" value="<?= h((string)($form['summary'] ?? '')) ?>" maxlength="255" placeholder="Une ligne pour les cartes sur le site">
              </div>
              <div class="col-12">
                <label class="form-label">Contenu détaillé</label>
                <textarea class="form-control" name="content" rows="8" placeholder="Détails, conditions, profil recherché…"><?= h((string)($form['content'] ?? '')) ?></textarea>
              </div>
              <div class="col-12 col-md-6">
                <label class="form-label">Ordre d’affichage</label>
                <input type="number" class="form-control" name="sort_order" value="<?= (int)($form['sort_order'] ?? 0) ?>">
              </div>
              <div class="col-12 col-md-6 d-flex align-items-end">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" value="1" id="annPub" name="is_published" <?= !empty($form['is_published']) ? 'checked' : '' ?>>
                  <label class="form-check-label" for="annPub">Publié sur le site</label>
                </div>
              </div>
              <div class="col-12 d-flex gap-2 flex-wrap">
                <button class="btn btn-primary ud-btn ud-btn--shine" type="submit">Enregistrer</button>
                <?php if ((int)($form['id'] ?? 0) > 0): ?>
                  <button class="btn btn-outline-danger ud-btn" type="submit" form="udDeleteAnnouncement">Supprimer</button>
                <?php endif; ?>
              </div>
            </div>
          </form>

          <?php if ((int)($form['id'] ?? 0) > 0): ?>
            <form id="udDeleteAnnouncement" method="post" action="<?= h($baseUrl) ?>/?action=admin-announcement-delete" onsubmit="return confirm('Supprimer cette annonce ?');">
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
    'title' => 'Admin - Offres & recrutement',
    'heading' => 'Offres & recrutement',
    'content' => $content,
    'flash' => $flash ?? [],
];

require __DIR__ . '/_layout.php';
