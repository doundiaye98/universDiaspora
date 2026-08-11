<?php
declare(strict_types=1);

$config = require __DIR__ . '/../../config/config.php';
$baseUrl = rtrim($config['app']['base_url'], '/');
admin_require_login($baseUrl);

$pdo = db();
$rows = testimonials_all($pdo, false);
$pendingCount = count(array_filter($rows, static fn(array $r): bool => empty($r['is_published'])));
$editId = (int)($_GET['edit'] ?? 0);
$edit = null;
foreach ($rows as $r) {
    if ((int)($r['id'] ?? 0) === $editId) {
        $edit = $r;
        break;
    }
}
$csrf = admin_csrf_token();

ob_start();
?>
<div class="container-fluid px-3 px-md-4 py-3 py-md-4">
  <div class="d-flex justify-content-between align-items-end gap-3 mb-3">
    <p class="ud-admin-page-lead text-muted mb-0">
      Gérez les témoignages affichés sur l’accueil.
      <?php if ($pendingCount > 0): ?>
        <span class="badge text-bg-warning ms-1"><?= (int)$pendingCount ?> en attente</span>
      <?php endif; ?>
    </p>
    <a class="btn btn-primary ud-btn ud-btn--shine" href="<?= h($baseUrl) ?>/?page=admin-testimonials&edit=new">+ Nouveau témoignage</a>
  </div>

  <div class="row g-3 g-lg-4">
    <div class="col-12 col-xl-6">
      <div class="ud-admin-panel">
        <div class="ud-admin-panel__head">
          <div class="ud-admin-panel__title">Liste</div>
          <div class="ud-admin-panel__meta"><?= count($rows) ?> témoignages</div>
        </div>
        <div class="table-responsive">
          <table class="table ud-admin-table mb-0">
            <thead>
              <tr><th>Statut</th><th>Ordre</th><th>Auteur</th><th>Extrait</th><th></th></tr>
            </thead>
            <tbody>
            <?php if (empty($rows)): ?>
              <tr><td colspan="5" class="text-muted">Aucun témoignage.</td></tr>
            <?php else: ?>
              <?php foreach ($rows as $r): ?>
                <?php $isPub = !empty($r['is_published']); ?>
                <tr class="<?= $isPub ? '' : 'table-warning' ?>">
                  <td>
                    <?php if ($isPub): ?>
                      <span class="badge text-bg-success">Publié</span>
                    <?php else: ?>
                      <span class="badge text-bg-warning text-dark">En attente</span>
                    <?php endif; ?>
                  </td>
                  <td><?= (int)($r['sort_order'] ?? 0) ?></td>
                  <td class="fw-bold"><?= h((string)($r['author'] ?? '')) ?></td>
                  <td class="text-muted"><?= h(function_exists('mb_strimwidth') ? mb_strimwidth((string)($r['quote'] ?? ''), 0, 56, '…') : substr((string)($r['quote'] ?? ''), 0, 56)) ?></td>
                  <td class="text-end"><a class="btn btn-sm btn-outline-primary" href="<?= h($baseUrl) ?>/?page=admin-testimonials&edit=<?= (int)($r['id'] ?? 0) ?>">Modifier</a></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="col-12 col-xl-6">
      <?php
        $isNew = (($_GET['edit'] ?? '') === 'new');
        $form = $edit ?? [
            'id' => 0, 'quote' => '', 'author' => '', 'location' => '', 'case_label' => 'Cas concret',
            'case_value' => '', 'sort_order' => 0, 'is_published' => 1,
        ];
        if (!empty($old)) {
            $form = array_merge($form, $old);
        }
        $errors = $errors ?? [];
      ?>
      <div class="ud-admin-panel">
        <div class="ud-admin-panel__head">
          <div class="ud-admin-panel__title"><?= ($isNew || (int)$form['id'] === 0) ? 'Nouveau témoignage' : 'Modifier témoignage' ?></div>
          <div class="ud-admin-panel__meta"><?= ($isNew || (int)$form['id'] === 0) ? 'Création' : 'ID ' . (int)$form['id'] ?></div>
        </div>
        <div class="p-3">
          <form method="post" action="<?= h($baseUrl) ?>/?action=admin-testimonial-save">
            <input type="hidden" name="_csrf" value="<?= h($csrf) ?>">
            <input type="hidden" name="id" value="<?= (int)($form['id'] ?? 0) ?>">
            <div class="row g-3">
              <div class="col-12">
                <label class="form-label">Témoignage</label>
                <textarea class="form-control <?= isset($errors['quote']) ? 'is-invalid' : '' ?>" name="quote" rows="4" required><?= h((string)($form['quote'] ?? '')) ?></textarea>
                <?php if (isset($errors['quote'])): ?><div class="invalid-feedback"><?= h($errors['quote']) ?></div><?php endif; ?>
              </div>
              <div class="col-md-6">
                <label class="form-label">Auteur</label>
                <input class="form-control <?= isset($errors['author']) ? 'is-invalid' : '' ?>" name="author" value="<?= h((string)($form['author'] ?? '')) ?>" required>
                <?php if (isset($errors['author'])): ?><div class="invalid-feedback"><?= h($errors['author']) ?></div><?php endif; ?>
              </div>
              <div class="col-md-6">
                <label class="form-label">Localisation</label>
                <input class="form-control" name="location" value="<?= h((string)($form['location'] ?? '')) ?>" placeholder="Paris 18">
              </div>
              <?php if (!empty($form['submitter_email'])): ?>
                <div class="col-12">
                  <label class="form-label text-muted">E-mail visiteur (non publié)</label>
                  <input class="form-control" type="email" value="<?= h((string)$form['submitter_email']) ?>" readonly disabled>
                </div>
              <?php endif; ?>
              <div class="col-md-6">
                <label class="form-label">Label cas</label>
                <input class="form-control" name="case_label" value="<?= h((string)($form['case_label'] ?? '')) ?>" placeholder="Cas concret">
              </div>
              <div class="col-md-6">
                <label class="form-label">Valeur cas</label>
                <input class="form-control" name="case_value" value="<?= h((string)($form['case_value'] ?? '')) ?>" placeholder="Dossier validé en 12 jours">
              </div>
              <div class="col-md-4">
                <label class="form-label">Ordre</label>
                <input type="number" class="form-control" name="sort_order" value="<?= (int)($form['sort_order'] ?? 0) ?>">
              </div>
              <div class="col-md-8 d-flex align-items-end">
                <div class="form-check mb-2">
                  <input class="form-check-input" type="checkbox" value="1" id="is_published" name="is_published" <?= !empty($form['is_published']) ? 'checked' : '' ?>>
                  <label class="form-check-label" for="is_published">Publié sur l’accueil</label>
                </div>
              </div>
              <div class="col-12 d-flex flex-wrap gap-2">
                <button class="btn btn-primary ud-btn ud-btn--shine" type="submit">Enregistrer</button>
                <?php if ((int)($form['id'] ?? 0) > 0): ?>
                  <button class="btn btn-outline-danger ud-btn" type="submit" form="udDeleteTestimonial">Supprimer</button>
                <?php endif; ?>
              </div>
            </div>
          </form>

          <?php if ((int)($form['id'] ?? 0) > 0): ?>
            <form id="udDeleteTestimonial" method="post" action="<?= h($baseUrl) ?>/?action=admin-testimonial-delete" onsubmit="return confirm('Supprimer ce témoignage ?');">
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
    'title' => 'Admin - Témoignages',
    'heading' => 'Témoignages',
    'content' => $content,
    'flash' => $flash ?? [],
];
require __DIR__ . '/_layout.php';

