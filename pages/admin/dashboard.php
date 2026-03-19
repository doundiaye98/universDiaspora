<?php
declare(strict_types=1);

$config = require __DIR__ . '/../../config/config.php';
$baseUrl = rtrim($config['app']['base_url'], '/');

admin_require_login($baseUrl);

$pdo = db();

$counts = [
    'contacts' => (int) $pdo->query('SELECT COUNT(*) FROM contact_messages')->fetchColumn(),
    'appointments' => (int) $pdo->query('SELECT COUNT(*) FROM appointments')->fetchColumn(),
    'services' => (int) $pdo->query('SELECT COUNT(*) FROM services')->fetchColumn(),
    'admins' => (int) $pdo->query('SELECT COUNT(*) FROM admin_users')->fetchColumn(),
];

$latestContacts = $pdo->query('SELECT id, last_name, first_name, email, phone, created_at FROM contact_messages ORDER BY id DESC LIMIT 8')->fetchAll();
$latestAppointments = $pdo->query('SELECT id, office, appointment_at, name, email, phone, created_at FROM appointments ORDER BY id DESC LIMIT 8')->fetchAll();

ob_start();
?>
<div class="container py-4">
  <div class="row g-3 mb-4">
    <div class="col-12 col-md-6 col-xl-3">
      <a class="ud-admin-metric ud-admin-metric--link" href="<?= h($baseUrl) ?>/?page=admin-services">
        <div class="ud-admin-metric__label">Services</div>
        <div class="ud-admin-metric__value"><?= (int) $counts['services'] ?></div>
        <div class="ud-admin-metric__hint">Gérer les pages</div>
      </a>
    </div>
    <div class="col-12 col-md-6 col-xl-3">
      <div class="ud-admin-metric">
        <div class="ud-admin-metric__label">Messages contact</div>
        <div class="ud-admin-metric__value"><?= (int) $counts['contacts'] ?></div>
        <div class="ud-admin-metric__hint">Total reçus</div>
      </div>
    </div>
    <div class="col-12 col-md-6 col-xl-3">
      <div class="ud-admin-metric">
        <div class="ud-admin-metric__label">Rendez‑vous</div>
        <div class="ud-admin-metric__value"><?= (int) $counts['appointments'] ?></div>
        <div class="ud-admin-metric__hint">Demandes</div>
      </div>
    </div>
    <div class="col-12 col-md-6 col-xl-3">
      <a class="ud-admin-metric ud-admin-metric--link" href="<?= h($baseUrl) ?>/?page=admin-admins">
        <div class="ud-admin-metric__label">Admins</div>
        <div class="ud-admin-metric__value"><?= (int) $counts['admins'] ?></div>
        <div class="ud-admin-metric__hint">Accès</div>
      </a>
    </div>
  </div>

  <div class="row g-4">
    <div class="col-12 col-lg-6">
      <div class="ud-admin-panel">
        <div class="ud-admin-panel__head">
          <div class="ud-admin-panel__title">Derniers messages (Contact)</div>
          <div class="ud-admin-panel__meta"><?= count($latestContacts) ?> derniers</div>
        </div>
        <div class="table-responsive">
          <table class="table ud-admin-table mb-0">
            <thead>
              <tr>
                <th>Date</th>
                <th>Nom</th>
                <th>Email</th>
                <th>Téléphone</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($latestContacts)): ?>
                <tr><td colspan="4" class="text-muted">Aucun message.</td></tr>
              <?php else: ?>
                <?php foreach ($latestContacts as $c): ?>
                  <tr>
                    <td class="text-nowrap"><?= h(date('d/m/Y H:i', strtotime((string)$c['created_at']))) ?></td>
                    <td><?= h(($c['first_name'] ?? '') . ' ' . ($c['last_name'] ?? '')) ?></td>
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

    <div class="col-12 col-lg-6">
      <div class="ud-admin-panel">
        <div class="ud-admin-panel__head">
          <div class="ud-admin-panel__title">Derniers rendez‑vous</div>
          <div class="ud-admin-panel__meta"><?= count($latestAppointments) ?> derniers</div>
        </div>
        <div class="table-responsive">
          <table class="table ud-admin-table mb-0">
            <thead>
              <tr>
                <th>Date RDV</th>
                <th>Bureau</th>
                <th>Nom</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($latestAppointments)): ?>
                <tr><td colspan="3" class="text-muted">Aucun rendez‑vous.</td></tr>
              <?php else: ?>
                <?php foreach ($latestAppointments as $a): ?>
                  <tr>
                    <td class="text-nowrap"><?= h(date('d/m/Y H:i', strtotime((string)$a['appointment_at']))) ?></td>
                    <td><?= h((string)($a['office'] ?? '')) ?></td>
                    <td><?= h((string)($a['name'] ?? '')) ?></td>
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
    'title' => 'Admin - Dashboard',
    'active' => '',
    'content' => $content,
    'flash' => $flash ?? [],
];

require __DIR__ . '/_layout.php';

