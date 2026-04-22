<?php
declare(strict_types=1);

$config = require __DIR__ . '/../../config/config.php';
$baseUrl = rtrim($config['app']['base_url'], '/');
admin_require_login($baseUrl);

$pdo = db();
$list = job_applications_all($pdo, 300);

ob_start();
?>
<div class="container-fluid px-3 px-md-4 py-3 py-md-4">
  <div class="d-flex flex-column flex-md-row align-items-stretch align-items-md-end justify-content-between gap-3 mb-3">
    <div class="min-w-0">
      <p class="ud-admin-page-lead text-muted mb-0">CV et lettres reçus depuis la page « Offres &amp; recrutement ».</p>
    </div>
    <a class="btn btn-outline-primary ud-btn ud-btn--ghost flex-shrink-0 align-self-md-center" href="<?= h($baseUrl) ?>/?page=admin-announcements">Gérer les annonces</a>
  </div>

  <div class="ud-admin-panel">
    <div class="ud-admin-panel__head">
      <div class="ud-admin-panel__title">Liste</div>
      <div class="ud-admin-panel__meta"><?= count($list) ?> dernière(s)</div>
    </div>
    <div class="table-responsive ud-table-scroll">
      <table class="table ud-admin-table mb-0">
        <thead>
          <tr>
            <th>Date</th>
            <th>Poste</th>
            <th>Candidat</th>
            <th>Contact</th>
            <th class="text-end">Fichiers</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($list)): ?>
            <tr><td colspan="5" class="text-muted">Aucune candidature pour le moment.</td></tr>
          <?php else: ?>
            <?php foreach ($list as $row): ?>
              <?php
                $jid = (int)($row['id'] ?? 0);
                $title = (string)($row['announcement_title'] ?? '');
                if ($title === '') {
                    $title = '— (annonce supprimée)';
                }
              ?>
              <tr>
                <td class="text-nowrap small"><?= h((string)($row['created_at'] ?? '')) ?></td>
                <td class="fw-bold"><?= h($title) ?></td>
                <td>
                  <?= h((string)($row['full_name'] ?? '')) ?>
                  <?php if (trim((string)($row['message'] ?? '')) !== ''): ?>
                    <div class="small text-muted mt-1"><?= nl2br(h((string)($row['message'] ?? ''))) ?></div>
                  <?php endif; ?>
                </td>
                <td class="small">
                  <div><?= h((string)($row['email'] ?? '')) ?></div>
                  <?php if (trim((string)($row['phone'] ?? '')) !== ''): ?>
                    <div class="text-muted"><?= h((string)($row['phone'] ?? '')) ?></div>
                  <?php endif; ?>
                </td>
                <td class="text-end text-nowrap">
                  <a class="btn btn-sm btn-outline-primary" href="<?= h($baseUrl) ?>/?action=admin-job-application-file&amp;id=<?= $jid ?>&amp;kind=cv">CV</a>
                  <a class="btn btn-sm btn-outline-primary" href="<?= h($baseUrl) ?>/?action=admin-job-application-file&amp;id=<?= $jid ?>&amp;kind=cover">Lettre</a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php
$content = ob_get_clean();

$view = [
    'title' => 'Admin - Candidatures',
    'heading' => 'Candidatures',
    'content' => $content,
    'flash' => $flash ?? [],
];

require __DIR__ . '/_layout.php';
