<?php
declare(strict_types=1);

$config = require __DIR__ . '/../../config/config.php';
$baseUrl = rtrim($config['app']['base_url'], '/');
admin_require_login($baseUrl);

$pdo = db();
$services = services_all($pdo);

$editId = (int)($_GET['edit'] ?? 0);
$edit = null;
if ($editId > 0) {
    foreach ($services as $s) {
        if ((int)($s['id'] ?? 0) === $editId) {
            $edit = $s;
            break;
        }
    }
}

// Build icon datalist from assets folder if available
$icons = [];
try {
    $dir = __DIR__ . '/../../public/assets/img';
    if (is_dir($dir)) {
        foreach (scandir($dir) ?: [] as $f) {
            if (!is_string($f)) continue;
            if (preg_match('~\\.(png|jpg|jpeg|webp|gif)$~i', $f)) $icons[] = $f;
        }
    }
} catch (Throwable $e) {}
sort($icons);

$csrf = admin_csrf_token();

ob_start();
?>
<div class="container-fluid px-3 px-md-4 py-3 py-md-4">
  <div class="d-flex flex-column flex-md-row align-items-stretch align-items-md-end justify-content-between gap-3 mb-3">
    <div class="min-w-0">
      <p class="ud-admin-page-lead text-muted mb-0">Ajouter, modifier ou supprimer les services affichés sur le site.</p>
    </div>
    <a class="btn btn-primary ud-btn ud-btn--shine flex-shrink-0 align-self-md-center" href="<?= h($baseUrl) ?>/?page=admin-services&edit=new">
      + Nouveau service
    </a>
  </div>

  <div class="row g-3 g-lg-4">
    <div class="col-12 col-xl-6">
      <div class="ud-admin-panel">
        <div class="ud-admin-panel__head">
          <div class="ud-admin-panel__title">Liste</div>
          <div class="ud-admin-panel__meta"><?= count($services) ?> services</div>
        </div>
        <div class="table-responsive">
          <table class="table ud-admin-table mb-0">
            <thead>
              <tr>
                <th>Ordre</th>
                <th>Service</th>
                <th>Slug</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($services)): ?>
                <tr><td colspan="4" class="text-muted">Aucun service.</td></tr>
              <?php else: ?>
                <?php foreach ($services as $s): ?>
                  <tr>
                    <td class="text-nowrap"><?= (int)($s['sort_order'] ?? 0) ?></td>
                    <td class="text-nowrap fw-bold"><?= h((string)($s['title'] ?? '')) ?></td>
                    <td class="text-nowrap text-muted"><?= h((string)($s['slug'] ?? '')) ?></td>
                    <td class="text-end">
                      <a class="btn btn-sm btn-outline-primary" href="<?= h($baseUrl) ?>/?page=admin-services&edit=<?= (int)($s['id'] ?? 0) ?>">Modifier</a>
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
      <?php
        $isNew = (($_GET['edit'] ?? '') === 'new');
        $form = $edit ?? [
            'id' => 0,
            'slug' => '',
            'title' => '',
            'description' => '',
            'details' => '',
            'details_is_html' => false,
            'step1_title' => '',
            'step1_text' => '',
            'step2_title' => '',
            'step2_text' => '',
            'step3_title' => '',
            'step3_text' => '',
            'icon' => '',
            'external_url' => '',
            'coming_soon' => false,
            'sort_order' => 0,
            'bullets' => [],
        ];
        $bulletsText = implode("\n", $form['bullets'] ?? []);
        if (!empty($old)) {
            // when validation fails (from index.php), we can reuse $old
            $form['id'] = (int)($old['id'] ?? 0);
            $form['slug'] = (string)($old['slug'] ?? '');
            $form['title'] = (string)($old['title'] ?? '');
            $form['description'] = (string)($old['description'] ?? '');
            $form['details'] = (string)($old['details'] ?? '');
            $form['details_is_html'] = (($old['details_is_html'] ?? '') === '1');
            $form['step1_title'] = (string)($old['step1_title'] ?? '');
            $form['step1_text'] = (string)($old['step1_text'] ?? '');
            $form['step2_title'] = (string)($old['step2_title'] ?? '');
            $form['step2_text'] = (string)($old['step2_text'] ?? '');
            $form['step3_title'] = (string)($old['step3_title'] ?? '');
            $form['step3_text'] = (string)($old['step3_text'] ?? '');
            $form['icon'] = (string)($old['icon'] ?? '');
            $form['external_url'] = (string)($old['external_url'] ?? '');
            $form['sort_order'] = (int)($old['sort_order'] ?? 0);
            $form['coming_soon'] = (($old['coming_soon'] ?? '') === '1');
            $bulletsText = (string)($old['bullets_text'] ?? '');
        }
        $errors = $errors ?? [];
      ?>

      <div class="ud-admin-panel">
        <div class="ud-admin-panel__head">
          <div class="ud-admin-panel__title"><?= ($isNew || (int)$form['id'] === 0) ? 'Nouveau service' : 'Modifier service' ?></div>
          <div class="ud-admin-panel__meta"><?= ($isNew || (int)$form['id'] === 0) ? 'Création' : 'ID ' . (int)$form['id'] ?></div>
        </div>
        <div class="p-3">
          <form method="post" enctype="multipart/form-data" action="<?= h($baseUrl) ?>/?action=admin-service-save">
            <input type="hidden" name="_csrf" value="<?= h($csrf) ?>">
            <input type="hidden" name="id" value="<?= (int)($form['id'] ?? 0) ?>">

            <div class="row g-3">
              <div class="col-12 ud-admin-form-block">
                <span class="ud-admin-form-block__title">Titre et identité</span>
                <div class="row g-3">
                  <div class="col-12 col-md-6">
                    <label class="form-label" for="udSvcTitle">Titre</label>
                    <input id="udSvcTitle" class="form-control <?= isset($errors['title']) ? 'is-invalid' : '' ?>" name="title" value="<?= h((string)($form['title'] ?? '')) ?>" required>
                    <?php if (isset($errors['title'])): ?><div class="invalid-feedback"><?= h($errors['title']) ?></div><?php endif; ?>
                  </div>
                  <div class="col-12 col-md-6">
                    <label class="form-label" for="udSvcSlug">Slug</label>
                    <input id="udSvcSlug" class="form-control <?= isset($errors['slug']) ? 'is-invalid' : '' ?>" name="slug" value="<?= h((string)($form['slug'] ?? '')) ?>" placeholder="ex: immobilier-btp" required>
                    <?php if (isset($errors['slug'])): ?><div class="invalid-feedback"><?= h($errors['slug']) ?></div><?php endif; ?>
                  </div>
                  <div class="col-12">
                    <label class="form-label" for="udSvcDesc">Description courte</label>
                    <input id="udSvcDesc" class="form-control" name="description" value="<?= h((string)($form['description'] ?? '')) ?>" maxlength="255">
                  </div>
                </div>
              </div>

              <div class="col-12 ud-admin-form-block">
                <span class="ud-admin-form-block__title">Détails (page service)</span>
                <label class="form-label" for="udSvcDetails">Contenu</label>
                <textarea id="udSvcDetails" class="form-control font-monospace small" name="details" rows="8"><?= h((string)($form['details'] ?? '')) ?></textarea>
                <div class="form-check mt-2">
                  <input class="form-check-input" type="checkbox" value="1" id="udDetailsHtml" name="details_is_html" <?= !empty($form['details_is_html']) ? 'checked' : '' ?>>
                  <label class="form-check-label" for="udDetailsHtml">Autoriser le HTML dans les détails</label>
                </div>
                <ul class="form-text small text-muted mb-0 mt-2 ps-3 ud-admin-form-hint">
                  <li class="mb-1"><strong>Case décochée</strong> — texte simple ; les retours à la ligne sont conservés.</li>
                  <li class="mb-0"><strong>Case cochée</strong> — seules ces balises sont acceptées : <code>p</code>, <code>br</code>, <code>strong</code>, <code>em</code>, <code>ul</code>, <code>ol</code>, <code>li</code>, <code>h2</code> à <code>h4</code>.</li>
                </ul>
              </div>

              <div class="col-12 ud-admin-form-block">
                <span class="ud-admin-form-block__title">Déroulement — 3 étapes</span>
                <p class="small text-muted mb-3 mb-md-2">Tout laisser vide : textes par défaut (vouvoiement).</p>
                <div class="row g-2">
                  <div class="col-12 col-md-4">
                    <label class="form-label small text-muted mb-1">Étape 1</label>
                    <input class="form-control form-control-sm" name="step1_title" value="<?= h((string)($form['step1_title'] ?? '')) ?>" placeholder="Titre 1" aria-label="Titre étape 1">
                    <textarea class="form-control form-control-sm mt-1" name="step1_text" rows="3" placeholder="Texte 1" aria-label="Texte étape 1"><?= h((string)($form['step1_text'] ?? '')) ?></textarea>
                  </div>
                  <div class="col-12 col-md-4">
                    <label class="form-label small text-muted mb-1">Étape 2</label>
                    <input class="form-control form-control-sm" name="step2_title" value="<?= h((string)($form['step2_title'] ?? '')) ?>" placeholder="Titre 2" aria-label="Titre étape 2">
                    <textarea class="form-control form-control-sm mt-1" name="step2_text" rows="3" placeholder="Texte 2" aria-label="Texte étape 2"><?= h((string)($form['step2_text'] ?? '')) ?></textarea>
                  </div>
                  <div class="col-12 col-md-4">
                    <label class="form-label small text-muted mb-1">Étape 3</label>
                    <input class="form-control form-control-sm" name="step3_title" value="<?= h((string)($form['step3_title'] ?? '')) ?>" placeholder="Titre 3" aria-label="Titre étape 3">
                    <textarea class="form-control form-control-sm mt-1" name="step3_text" rows="3" placeholder="Texte 3" aria-label="Texte étape 3"><?= h((string)($form['step3_text'] ?? '')) ?></textarea>
                  </div>
                </div>
              </div>

              <div class="col-12 ud-admin-form-block">
                <span class="ud-admin-form-block__title">Photo et lien externe</span>
                <div class="row g-3">
                  <div class="col-12 col-md-6">
                    <div class="ud-admin-form-block__box h-100">
                      <div class="fw-semibold small mb-2">Photo du service</div>
                      <p class="small text-muted mb-3">Cartes d’accueil, menu « Services », en-tête de la fiche.</p>
                      <?php
                        $iconPreviewUrl = service_icon_url((string)($form['icon'] ?? ''), $baseUrl, (string)($form['slug'] ?? ''));
                      ?>
                      <div class="d-flex flex-column flex-sm-row align-items-start gap-3 mb-3">
                        <img src="<?= h($iconPreviewUrl) ?>" alt="" width="88" height="88" class="rounded-3 border bg-white flex-shrink-0" style="object-fit:contain;">
                        <p class="small text-muted mb-0">Aperçu — taille à l’écran variable.</p>
                      </div>
                      <div class="mb-3">
                        <label class="form-label" for="udSvcIconName">Fichier dans <code>public/assets/img/</code></label>
                        <input id="udSvcIconName" class="form-control <?= isset($errors['icon_upload']) ? 'is-invalid' : '' ?>" name="icon" list="udIconList" value="<?= h((string)($form['icon'] ?? '')) ?>" placeholder="ex. univers-diasporas-icone-immobilier.png" autocomplete="off">
                        <datalist id="udIconList">
                          <?php foreach ($icons as $i): ?><option value="<?= h($i) ?>"><?php endforeach; ?>
                        </datalist>
                      </div>
                      <div class="mb-0">
                        <label class="form-label" for="udSvcIconUpload">Téléverser une nouvelle photo</label>
                        <input id="udSvcIconUpload" type="file" class="form-control <?= isset($errors['icon_upload']) ? 'is-invalid' : '' ?>" name="icon_upload" accept="image/jpeg,image/png,image/webp,image/gif">
                        <?php if (isset($errors['icon_upload'])): ?><div class="invalid-feedback d-block"><?= h($errors['icon_upload']) ?></div><?php endif; ?>
                      </div>
                      <ul class="form-text small text-muted mb-0 mt-2 ps-3 ud-admin-form-hint">
                        <li class="mb-1">Fichiers acceptés : JPG, PNG, WebP, GIF — taille max. 2&nbsp;Mo.</li>
                        <li class="mb-1">Après envoi, le <strong>nom du fichier</strong> affiché plus haut est remplacé automatiquement.</li>
                        <li class="mb-0">Si le champ est vide ou que le fichier n’existe pas sur le serveur, le site affiche une <strong>image de secours</strong> selon le slug (ex. <code>developpement-web</code>).</li>
                      </ul>
                    </div>
                  </div>
                  <div class="col-12 col-md-6">
                    <div class="ud-admin-form-block__box h-100">
                      <div class="fw-semibold small mb-2">Lien externe</div>
                      <p class="small text-muted mb-3">Optionnel — ouvre dans un nouvel onglet si renseigné côté affichage.</p>
                      <label class="form-label" for="udSvcExtUrl">URL</label>
                      <input id="udSvcExtUrl" class="form-control <?= isset($errors['external_url']) ? 'is-invalid' : '' ?>" name="external_url" value="<?= h((string)($form['external_url'] ?? '')) ?>" placeholder="https://...">
                      <?php if (isset($errors['external_url'])): ?><div class="invalid-feedback"><?= h($errors['external_url']) ?></div><?php endif; ?>
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-12 ud-admin-form-block">
                <span class="ud-admin-form-block__title">Ordre et visibilité</span>
                <div class="row g-3 align-items-end">
                  <div class="col-12 col-sm-4 col-md-3">
                    <label class="form-label" for="udSvcSort">Ordre d’affichage</label>
                    <input id="udSvcSort" type="number" class="form-control" name="sort_order" value="<?= (int)($form['sort_order'] ?? 0) ?>">
                  </div>
                  <div class="col-12 col-sm-8 col-md-9">
                    <div class="form-check mt-2 mt-sm-0">
                      <input class="form-check-input" type="checkbox" value="1" id="comingSoon" name="coming_soon" <?= !empty($form['coming_soon']) ? 'checked' : '' ?>>
                      <label class="form-check-label" for="comingSoon">Bientôt (coming soon)</label>
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-12 ud-admin-form-block">
                <span class="ud-admin-form-block__title">Liste à puces (page service)</span>
                <label class="form-label" for="udSvcBullets">Une ligne = une puce</label>
                <textarea id="udSvcBullets" class="form-control" name="bullets_text" rows="6"><?= h($bulletsText) ?></textarea>
              </div>

              <div class="col-12 d-flex flex-wrap gap-2 pt-1">
                <button class="btn btn-primary ud-btn ud-btn--shine" type="submit">Enregistrer</button>
                <?php if ((int)($form['id'] ?? 0) > 0): ?>
                  <button class="btn btn-outline-danger ud-btn" type="submit" form="udDeleteService">Supprimer</button>
                <?php endif; ?>
              </div>
            </div>
          </form>

          <?php if ((int)($form['id'] ?? 0) > 0): ?>
            <form id="udDeleteService" method="post" action="<?= h($baseUrl) ?>/?action=admin-service-delete" onsubmit="return confirm('Supprimer ce service ?');">
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
    'title' => 'Admin - Services',
    'heading' => 'Services',
    'content' => $content,
    'flash' => $flash ?? [],
];

require __DIR__ . '/_layout.php';

