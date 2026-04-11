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
      <div class="ud-admin-kicker">Gestion</div>
      <h1 class="ud-admin-title mb-0">Services</h1>
      <div class="ud-admin-sub">Ajouter, modifier, supprimer les services affichés sur le site.</div>
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
          <form method="post" action="<?= h($baseUrl) ?>/?action=admin-service-save">
            <input type="hidden" name="_csrf" value="<?= h($csrf) ?>">
            <input type="hidden" name="id" value="<?= (int)($form['id'] ?? 0) ?>">

            <div class="row g-3">
              <div class="col-12 col-md-6">
                <label class="form-label">Titre</label>
                <input class="form-control <?= isset($errors['title']) ? 'is-invalid' : '' ?>" name="title" value="<?= h((string)($form['title'] ?? '')) ?>" required>
                <?php if (isset($errors['title'])): ?><div class="invalid-feedback"><?= h($errors['title']) ?></div><?php endif; ?>
              </div>
              <div class="col-12 col-md-6">
                <label class="form-label">Slug</label>
                <input class="form-control <?= isset($errors['slug']) ? 'is-invalid' : '' ?>" name="slug" value="<?= h((string)($form['slug'] ?? '')) ?>" placeholder="ex: immobilier-btp" required>
                <?php if (isset($errors['slug'])): ?><div class="invalid-feedback"><?= h($errors['slug']) ?></div><?php endif; ?>
              </div>

              <div class="col-12">
                <label class="form-label">Description courte</label>
                <input class="form-control" name="description" value="<?= h((string)($form['description'] ?? '')) ?>" maxlength="255">
              </div>

              <div class="col-12">
                <label class="form-label">Détails</label>
                <textarea class="form-control font-monospace small" name="details" rows="8"><?= h((string)($form['details'] ?? '')) ?></textarea>
                <div class="form-check mt-2">
                  <input class="form-check-input" type="checkbox" value="1" id="udDetailsHtml" name="details_is_html" <?= !empty($form['details_is_html']) ? 'checked' : '' ?>>
                  <label class="form-check-label" for="udDetailsHtml">Interpréter comme HTML limité</label>
                </div>
                <div class="small text-muted mt-1">Texte simple : retours à la ligne conservés. HTML : balises autorisées &lt;p&gt;, &lt;br&gt;, &lt;strong&gt;, &lt;em&gt;, &lt;ul&gt;, &lt;ol&gt;, &lt;li&gt;, &lt;h2&gt; à &lt;h4&gt;.</div>
              </div>

              <div class="col-12">
                <label class="form-label">Étapes « Comment ça se passe » (3 étapes)</label>
                <div class="small text-muted mb-2">Laissez vide pour utiliser les textes par défaut (vouvoiement).</div>
                <div class="row g-2">
                  <div class="col-12 col-md-4">
                    <input class="form-control form-control-sm" name="step1_title" value="<?= h((string)($form['step1_title'] ?? '')) ?>" placeholder="Titre étape 1">
                    <textarea class="form-control form-control-sm mt-1" name="step1_text" rows="3" placeholder="Texte étape 1"><?= h((string)($form['step1_text'] ?? '')) ?></textarea>
                  </div>
                  <div class="col-12 col-md-4">
                    <input class="form-control form-control-sm" name="step2_title" value="<?= h((string)($form['step2_title'] ?? '')) ?>" placeholder="Titre étape 2">
                    <textarea class="form-control form-control-sm mt-1" name="step2_text" rows="3" placeholder="Texte étape 2"><?= h((string)($form['step2_text'] ?? '')) ?></textarea>
                  </div>
                  <div class="col-12 col-md-4">
                    <input class="form-control form-control-sm" name="step3_title" value="<?= h((string)($form['step3_title'] ?? '')) ?>" placeholder="Titre étape 3">
                    <textarea class="form-control form-control-sm mt-1" name="step3_text" rows="3" placeholder="Texte étape 3"><?= h((string)($form['step3_text'] ?? '')) ?></textarea>
                  </div>
                </div>
              </div>

              <div class="col-12 col-md-6">
                <label class="form-label">Icône (fichier)</label>
                <input class="form-control" name="icon" list="udIconList" value="<?= h((string)($form['icon'] ?? '')) ?>" placeholder="ex: univers-diasporas-icone-conseils.png">
                <datalist id="udIconList">
                  <?php foreach ($icons as $i): ?><option value="<?= h($i) ?>"><?php endforeach; ?>
                </datalist>
              </div>
              <div class="col-12 col-md-6">
                <label class="form-label">Lien externe (optionnel)</label>
                <input class="form-control <?= isset($errors['external_url']) ? 'is-invalid' : '' ?>" name="external_url" value="<?= h((string)($form['external_url'] ?? '')) ?>" placeholder="https://...">
                <?php if (isset($errors['external_url'])): ?><div class="invalid-feedback"><?= h($errors['external_url']) ?></div><?php endif; ?>
              </div>

              <div class="col-12 col-md-4">
                <label class="form-label">Ordre</label>
                <input type="number" class="form-control" name="sort_order" value="<?= (int)($form['sort_order'] ?? 0) ?>">
              </div>
              <div class="col-12 col-md-8 d-flex align-items-end">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" value="1" id="comingSoon" name="coming_soon" <?= !empty($form['coming_soon']) ? 'checked' : '' ?>>
                  <label class="form-check-label" for="comingSoon">Bientôt (coming soon)</label>
                </div>
              </div>

              <div class="col-12">
                <label class="form-label">Bullets (1 par ligne)</label>
                <textarea class="form-control" name="bullets_text" rows="6"><?= h($bulletsText) ?></textarea>
              </div>

              <div class="col-12 d-flex gap-2">
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
    'content' => $content,
    'flash' => $flash ?? [],
];

require __DIR__ . '/_layout.php';

