<?php
declare(strict_types=1);

$config = require __DIR__ . '/../../config/config.php';
$baseUrl = rtrim($config['app']['base_url'], '/');
admin_require_login($baseUrl);

$csrf = admin_csrf_token();
$pdo = db();

$qContact = trim((string)($_GET['q_contact'] ?? ''));
$qAppt = trim((string)($_GET['q_appt'] ?? ''));
$limit = (int)($_GET['limit'] ?? 20);
$limit = $limit > 0 && $limit <= 100 ? $limit : 20;
$page = (int)($_GET['page_no'] ?? 1);
$page = $page > 0 ? $page : 1;
$offset = ($page - 1) * $limit;

// Counts (for display)
$contactCountSql = 'SELECT COUNT(*) FROM contact_messages';
$apptCountSql = 'SELECT COUNT(*) FROM appointments';
$params = [];

if ($qContact !== '') {
    $contactCountSql .= ' WHERE last_name LIKE :q OR first_name LIKE :q OR email LIKE :q OR phone LIKE :q';
    $params[':q'] = '%' . $qContact . '%';
}
if ($qAppt !== '') {
    $apptCountSql .= ' WHERE office LIKE :qa OR name LIKE :qa OR email LIKE :qa OR phone LIKE :qa';
    $params[':qa'] = '%' . $qAppt . '%';
}

$contactTotal = (int)$pdo->prepare($contactCountSql)->execute($params) ? 0 : 0;
// Re-run with separate params to keep logic clear
$stmtC = $pdo->prepare($contactCountSql);
$stmtA = $pdo->prepare($apptCountSql);
$contactTotal = 0;
$apptTotal = 0;
try {
    if ($qContact !== '') {
        $stmtC->execute([':q' => '%' . $qContact . '%']);
    } else {
        $stmtC->execute();
    }
    $contactTotal = (int)$stmtC->fetchColumn();
} catch (Throwable $e) {
    $contactTotal = 0;
}
try {
    if ($qAppt !== '') {
        $stmtA->execute([':qa' => '%' . $qAppt . '%']);
    } else {
        $stmtA->execute();
    }
    $apptTotal = (int)$stmtA->fetchColumn();
} catch (Throwable $e) {
    $apptTotal = 0;
}

// Lists
$contactSql = 'SELECT id, last_name, first_name, email, phone, created_at FROM contact_messages';
$apptSql = 'SELECT id, office, appointment_at, name, email, phone, status, service_slug, volet_id, created_at FROM appointments';
if ($qContact !== '') {
    $contactSql .= ' WHERE last_name LIKE :q OR first_name LIKE :q OR email LIKE :q OR phone LIKE :q';
}
if ($qAppt !== '') {
    $apptSql .= ' WHERE office LIKE :qa OR name LIKE :qa OR email LIKE :qa OR phone LIKE :qa';
}

$contactSql .= ' ORDER BY id DESC LIMIT :limit OFFSET :offset';
$apptSql .= ' ORDER BY id DESC LIMIT :limit OFFSET :offset';

$contactList = [];
$apptList = [];
try {
    $stC = $pdo->prepare($contactSql);
    if ($qContact !== '') {
        $stC->bindValue(':q', '%' . $qContact . '%');
    }
    $stC->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stC->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stC->execute();
    $contactList = $stC->fetchAll();
} catch (Throwable $e) {
    $contactList = [];
}
try {
    $stA = $pdo->prepare($apptSql);
    if ($qAppt !== '') {
        $stA->bindValue(':qa', '%' . $qAppt . '%');
    }
    $stA->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stA->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stA->execute();
    $apptList = $stA->fetchAll();
} catch (Throwable $e) {
    $apptList = [];
}

ob_start();
?>
<div class="container-fluid px-3 px-md-4 py-3 py-md-4">
  <p class="ud-admin-page-lead text-muted mb-3 mb-md-4">Messages contact et demandes de rendez-vous — recherche et listes ci-dessous.</p>

  <div class="row g-3 g-lg-4">
    <div class="col-12 col-xl-6" id="ud-inbox-contacts">
      <div class="ud-admin-panel">
        <div class="ud-admin-panel__head">
          <div class="ud-admin-panel__title">Messages contact</div>
          <div class="ud-admin-panel__meta"><?= (int)$contactTotal ?> total</div>
        </div>
        <div class="p-3 border-bottom">
          <form method="get" class="row g-2 align-items-end">
            <input type="hidden" name="page" value="admin-messages">
            <div class="col-12">
              <label class="form-label mb-1">Recherche</label>
              <input class="form-control" name="q_contact" value="<?= h($qContact) ?>" placeholder="Nom, email, téléphone…">
            </div>
            <div class="col-6 col-md-4">
              <label class="form-label mb-1">Affichage</label>
              <select class="form-select" name="limit">
                <?php foreach ([10,20,30,50] as $l): ?>
                  <option value="<?= $l ?>" <?= $limit === $l ? 'selected' : '' ?>><?= $l ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-6 col-md-4">
              <label class="form-label mb-1">Page</label>
              <input class="form-control" type="number" min="1" name="page_no" value="<?= (int)$page ?>">
            </div>
            <div class="col-12 d-grid mt-2">
              <button class="btn btn-primary ud-btn ud-btn--shine" type="submit">Appliquer</button>
            </div>
          </form>
        </div>
        <div class="table-responsive">
          <table class="table ud-admin-table mb-0">
            <thead>
              <tr>
                <th>Date</th>
                <th>Nom</th>
                <th>Email</th>
                <th>Tel</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($contactList)): ?>
                <tr><td colspan="4" class="text-muted">Aucun message.</td></tr>
              <?php else: ?>
                <?php foreach ($contactList as $c): ?>
                  <tr>
                    <td class="text-nowrap"><?= h(date('d/m/Y H:i', strtotime((string)($c['created_at'] ?? 'now')))) ?></td>
                    <td><?= h((string)($c['first_name'] ?? '') . ' ' . (string)($c['last_name'] ?? '')) ?></td>
                    <td class="text-nowrap"><?= h((string)($c['email'] ?? '')) ?></td>
                    <td class="text-nowrap"><?= h((string)($c['phone'] ?? '')) ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="col-12 col-xl-6" id="ud-inbox-appts">
      <div class="ud-admin-panel">
        <div class="ud-admin-panel__head">
          <div class="ud-admin-panel__title">Demandes reçues</div>
          <div class="ud-admin-panel__meta"><?= (int)$apptTotal ?> total</div>
        </div>
        <div class="p-3 border-bottom">
          <form method="get" class="row g-2 align-items-end">
            <input type="hidden" name="page" value="admin-messages">
            <div class="col-12">
              <label class="form-label mb-1">Recherche</label>
              <input class="form-control" name="q_appt" value="<?= h($qAppt) ?>" placeholder="Bureau, nom, email…">
            </div>
            <div class="col-6 col-md-4">
              <label class="form-label mb-1">Affichage</label>
              <select class="form-select" name="limit">
                <?php foreach ([10,20,30,50] as $l): ?>
                  <option value="<?= $l ?>" <?= $limit === $l ? 'selected' : '' ?>><?= $l ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-6 col-md-4">
              <label class="form-label mb-1">Page</label>
              <input class="form-control" type="number" min="1" name="page_no" value="<?= (int)$page ?>">
            </div>
            <div class="col-12 d-grid mt-2">
              <button class="btn btn-primary ud-btn ud-btn--shine" type="submit">Appliquer</button>
            </div>
          </form>
        </div>
        <div class="table-responsive">
          <table class="table ud-admin-table mb-0">
            <thead>
              <tr>
                <th>Date RDV</th>
                <th>Bureau</th>
                <th>Service / Volet</th>
                <th>Nom</th>
                <th>Email</th>
                <th>Téléphone</th>
                <th>Statut</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($apptList)): ?>
                <tr><td colspan="8" class="text-muted">Aucune demande.</td></tr>
              <?php else: ?>
                <?php foreach ($apptList as $a): ?>
                  <?php
                    $st = (string)($a['status'] ?? 'pending');
                    $svcSlug = (string)($a['service_slug'] ?? '');
                    $vId = (string)($a['volet_id'] ?? '');
                  ?>
                  <tr>
                    <td class="text-nowrap"><?= h(date('d/m/Y H:i', strtotime((string)($a['appointment_at'] ?? 'now')))) ?></td>
                    <td><?= h((string)($a['office'] ?? '')) ?></td>
                    <td class="small">
                      <?php if ($svcSlug !== ''): ?>
                        <div><?= h($svcSlug) ?></div>
                        <?php if ($vId !== ''): ?>
                          <div class="text-muted">↳ <?= h($vId) ?></div>
                        <?php endif; ?>
                      <?php else: ?>
                        <span class="text-muted">—</span>
                      <?php endif; ?>
                    </td>
                    <td><?= h((string)($a['name'] ?? '')) ?></td>
                    <td class="text-nowrap"><?= h((string)($a['email'] ?? '')) ?></td>
                    <td class="text-nowrap"><?= h((string)($a['phone'] ?? '')) ?></td>
                    <td>
                      <span class="ud-admin-status <?= 'ud-admin-status--' . h($st) ?>">
                        <?= $st === 'confirmed' ? 'Confirmé' : ($st === 'cancelled' ? 'Annulé' : 'En attente') ?>
                      </span>
                    </td>
                    <td class="text-nowrap">
                      <?php if ($st === 'pending'): ?>
                        <form class="d-inline" method="post" action="<?= h($baseUrl) ?>/?action=admin-appointment-status">
                          <input type="hidden" name="_csrf" value="<?= h($csrf) ?>">
                          <input type="hidden" name="id" value="<?= (int)($a['id'] ?? 0) ?>">
                          <input type="hidden" name="status" value="confirmed">
                          <button type="submit" class="btn btn-sm btn-success ud-admin-btn">Confirmer</button>
                        </form>
                        <form class="d-inline ms-1" method="post" action="<?= h($baseUrl) ?>/?action=admin-appointment-status">
                          <input type="hidden" name="_csrf" value="<?= h($csrf) ?>">
                          <input type="hidden" name="id" value="<?= (int)($a['id'] ?? 0) ?>">
                          <input type="hidden" name="status" value="cancelled">
                          <button type="submit" class="btn btn-sm btn-outline-danger ud-admin-btn">Annuler</button>
                        </form>
                      <?php else: ?>
                        <form method="post" action="<?= h($baseUrl) ?>/?action=admin-appointment-status">
                          <input type="hidden" name="_csrf" value="<?= h($csrf) ?>">
                          <input type="hidden" name="id" value="<?= (int)($a['id'] ?? 0) ?>">
                          <input type="hidden" name="status" value="pending">
                          <button type="submit" class="btn btn-sm btn-outline-primary ud-admin-btn">Remettre en attente</button>
                        </form>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
<?php
$content = ob_get_clean();

$view = [
    'title' => 'Admin - Inbox',
    'heading' => 'Inbox',
    'content' => $content,
    'flash' => $flash ?? [],
];

require __DIR__ . '/_layout.php';

