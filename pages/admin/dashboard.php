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
    'job_applications' => (int) $pdo->query('SELECT COUNT(*) FROM job_applications')->fetchColumn(),
    'team_members' => (int) $pdo->query('SELECT COUNT(*) FROM team_members')->fetchColumn(),
];

$latestContacts = $pdo->query('SELECT id, last_name, first_name, email, phone, created_at FROM contact_messages ORDER BY id DESC LIMIT 8')->fetchAll();
$latestAppointments = $pdo->query('SELECT id, office, appointment_at, name, email, phone, status, created_at FROM appointments ORDER BY id DESC LIMIT 8')->fetchAll();

// Build a single activity timeline (mixed)
$timeline = [];
foreach ($latestContacts as $c) {
    $timeline[] = [
        'type' => 'contact',
        'ts' => (string)($c['created_at'] ?? ''),
        'title' => trim((string)($c['first_name'] ?? '') . ' ' . (string)($c['last_name'] ?? '')),
        'subtitle' => (string)($c['email'] ?? ''),
        'meta' => (string)($c['phone'] ?? ''),
    ];
}
foreach ($latestAppointments as $a) {
    $status = (string)($a['status'] ?? 'pending');
    $statusLabel = $status === 'confirmed' ? 'Confirmé' : ($status === 'cancelled' ? 'Annulé' : 'En attente');
    $timeline[] = [
        'type' => 'appointment',
        'ts' => (string)($a['created_at'] ?? $a['appointment_at'] ?? ''),
        'title' => (string)($a['name'] ?? ''),
        'subtitle' => (string)($a['office'] ?? ''),
        'meta' => trim(
            ((string)($a['email'] ?? '') !== '' ? (string)($a['email'] ?? '') : '')
            . ' '
            . ((string)($a['phone'] ?? '') !== '' ? '• ' . (string)($a['phone'] ?? '') : '')
            . ' • Statut: ' . $statusLabel
        ),
    ];
}
usort($timeline, static function (array $x, array $y): int {
    return strcmp((string)$y['ts'], (string)$x['ts']);
});
$timeline = array_slice($timeline, 0, 10);

ob_start();
?>
<div class="container-fluid px-3 px-md-4 py-3 py-md-4 ud-admin-dashboard">
  <div class="ud-admin-dash-hero mb-4">
    <div class="row g-3 align-items-stretch">
      <div class="col-12">
        <p class="ud-admin-dash-hero__lead mb-3">
          Vue d’ensemble : volumes, accès rapides et dernières demandes (contacts &amp; rendez-vous).
        </p>
        <div class="d-flex flex-wrap gap-2">
          <a class="btn btn-primary ud-btn ud-btn--shine btn-sm" href="<?= h($baseUrl) ?>/?page=admin-messages">Ouvrir l’Inbox</a>
          <a class="btn btn-outline-primary ud-btn ud-btn--ghost btn-sm" href="<?= h($baseUrl) ?>/?page=admin-services">Services</a>
          <a class="btn btn-outline-primary ud-btn ud-btn--ghost btn-sm" href="<?= h($baseUrl) ?>/?page=admin-announcements">Offres &amp; recrutement</a>
          <a class="btn btn-outline-primary ud-btn ud-btn--ghost btn-sm" href="<?= h($baseUrl) ?>/?page=admin-team-members">Équipe</a>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-3 mb-4 row-cols-2 row-cols-md-3 row-cols-xl-6">
    <div class="col">
      <a class="ud-admin-metric ud-admin-metric--link ud-admin-stat" href="<?= h($baseUrl) ?>/?page=admin-services">
        <div class="ud-admin-metric__label">Services</div>
        <div class="ud-admin-metric__value"><?= (int) $counts['services'] ?></div>
        <div class="ud-admin-metric__hint">Actifs dans le site</div>
      </a>
    </div>
    <div class="col">
      <a class="ud-admin-metric ud-admin-metric--link ud-admin-stat" href="<?= h($baseUrl) ?>/?page=admin-team-members">
        <div class="ud-admin-metric__label">Équipe</div>
        <div class="ud-admin-metric__value"><?= (int) $counts['team_members'] ?></div>
        <div class="ud-admin-metric__hint">Membres sur la page « Notre équipe »</div>
      </a>
    </div>
    <div class="col">
      <a class="ud-admin-metric ud-admin-metric--link ud-admin-stat" href="<?= h($baseUrl) ?>/?page=admin-messages#ud-inbox-contacts">
        <div class="ud-admin-metric__label">Messages contact</div>
        <div class="ud-admin-metric__value"><?= (int) $counts['contacts'] ?></div>
        <div class="ud-admin-metric__hint">Voir dans l’Inbox</div>
      </a>
    </div>
    <div class="col">
      <a class="ud-admin-metric ud-admin-metric--link ud-admin-stat" href="<?= h($baseUrl) ?>/?page=admin-messages#ud-inbox-appts">
        <div class="ud-admin-metric__label">Rendez‑vous</div>
        <div class="ud-admin-metric__value"><?= (int) $counts['appointments'] ?></div>
        <div class="ud-admin-metric__hint">Voir dans l’Inbox</div>
      </a>
    </div>
    <div class="col">
      <a class="ud-admin-metric ud-admin-metric--link ud-admin-stat" href="<?= h($baseUrl) ?>/?page=admin-job-applications">
        <div class="ud-admin-metric__label">Candidatures</div>
        <div class="ud-admin-metric__value"><?= (int) $counts['job_applications'] ?></div>
        <div class="ud-admin-metric__hint">CV &amp; lettres (PDF)</div>
      </a>
    </div>
    <div class="col">
      <a class="ud-admin-metric ud-admin-metric--link ud-admin-stat" href="<?= h($baseUrl) ?>/?page=admin-admins">
        <div class="ud-admin-metric__label">Administrateurs</div>
        <div class="ud-admin-metric__value"><?= (int) $counts['admins'] ?></div>
        <div class="ud-admin-metric__hint">Accès admin</div>
      </a>
    </div>
  </div>

  <div class="row g-3 g-lg-4 align-items-start">
    <div class="col-12 col-xl-7">
      <div class="ud-admin-panel ud-admin-panel--hero">
        <div class="ud-admin-panel__head">
          <div class="ud-admin-panel__title">Activité récente</div>
          <div class="ud-admin-panel__meta"><?= count($timeline) ?> éléments</div>
        </div>
        <div class="ud-admin-timeline p-3">
          <?php if (empty($timeline)): ?>
            <div class="text-muted">Aucune activité pour le moment.</div>
          <?php else: ?>
            <?php foreach ($timeline as $t): ?>
              <?php
                $isContact = $t['type'] === 'contact';
                $badgeCls = $isContact ? 'ud-admin-badge ud-admin-badge--contact' : 'ud-admin-badge ud-admin-badge--appointment';
                $label = $isContact ? 'Contact' : 'Rendez‑vous';
                $tsRaw = (string)($t['ts'] ?? '');
                $tsFmt = $tsRaw !== '' ? date('d/m/Y H:i', strtotime($tsRaw)) : '';
              ?>
              <div class="ud-admin-timeline__item">
                <div class="ud-admin-timeline__dot"></div>
                <div class="ud-admin-timeline__body">
                  <div class="d-flex flex-column flex-sm-row align-items-start justify-content-between gap-2">
                    <div class="min-w-0">
                      <span class="<?= $badgeCls ?>"><?= h($label) ?></span>
                      <div class="ud-admin-timeline__title mt-2"><?= h($t['title'] ?? '') ?></div>
                      <div class="ud-admin-timeline__sub"><?= h($t['subtitle'] ?? '') ?></div>
                    </div>
                    <div class="text-nowrap ud-admin-timeline__time flex-shrink-0 ms-sm-auto"><?= h($tsFmt) ?></div>
                  </div>
                  <?php if (!empty($t['meta'])): ?>
                    <div class="ud-admin-timeline__meta"><?= h($t['meta']) ?></div>
                  <?php endif; ?>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div class="col-12 col-xl-5">
      <div class="ud-admin-panel">
        <div class="ud-admin-panel__head">
          <div class="ud-admin-panel__title">Détails</div>
          <div class="ud-admin-panel__meta">Tables</div>
        </div>

        <div class="accordion ud-admin-accordion" id="udAdminAccordion">
          <div class="accordion-item ud-admin-accordion__item">
            <h2 class="accordion-header" id="udAdminAcc1">
              <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#udAdminCol1" aria-expanded="true" aria-controls="udAdminCol1">
                Derniers messages (Contact)
              </button>
            </h2>
            <div id="udAdminCol1" class="accordion-collapse collapse show" aria-labelledby="udAdminAcc1" data-bs-parent="#udAdminAccordion">
              <div class="accordion-body">
                <div class="table-responsive">
                  <table class="table ud-admin-table mb-0">
                    <thead>
                      <tr>
                        <th>Date</th>
                        <th>Nom</th>
                        <th>Email</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php if (empty($latestContacts)): ?>
                        <tr><td colspan="3" class="text-muted">Aucun message.</td></tr>
                      <?php else: ?>
                        <?php foreach ($latestContacts as $c): ?>
                          <tr>
                            <td class="text-nowrap"><?= h(date('d/m/Y H:i', strtotime((string)$c['created_at']))) ?></td>
                            <td><?= h(($c['first_name'] ?? '') . ' ' . ($c['last_name'] ?? '')) ?></td>
                            <td class="text-nowrap"><?= h((string)($c['email'] ?? '')) ?></td>
                          </tr>
                        <?php endforeach; ?>
                      <?php endif; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>

          <div class="accordion-item ud-admin-accordion__item">
            <h2 class="accordion-header" id="udAdminAcc2">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#udAdminCol2" aria-expanded="false" aria-controls="udAdminCol2">
                Derniers rendez‑vous
              </button>
            </h2>
            <div id="udAdminCol2" class="accordion-collapse collapse" aria-labelledby="udAdminAcc2" data-bs-parent="#udAdminAccordion">
              <div class="accordion-body">
                <div class="table-responsive">
                  <table class="table ud-admin-table mb-0">
                    <thead>
                      <tr>
                        <th>Date</th>
                        <th>Bureau</th>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Statut</th>
                      </tr>
                    </thead>
                    <tbody>
                <?php if (empty($latestAppointments)): ?>
                        <tr><td colspan="6" class="text-muted">Aucun rendez‑vous.</td></tr>
                      <?php else: ?>
                        <?php foreach ($latestAppointments as $a): ?>
                          <tr>
                            <td class="text-nowrap"><?= h(date('d/m/Y H:i', strtotime((string)$a['appointment_at']))) ?></td>
                            <td><?= h((string)($a['office'] ?? '')) ?></td>
                            <td><?= h((string)($a['name'] ?? '')) ?></td>
                            <td class="text-nowrap"><?= h((string)($a['email'] ?? '')) ?></td>
                            <td class="text-nowrap"><?= h((string)($a['phone'] ?? '')) ?></td>
                            <td>
                              <?php
                                $st = (string)($a['status'] ?? 'pending');
                              ?>
                              <span class="ud-admin-status ud-admin-status--<?= h($st) ?>">
                                <?= $st === 'confirmed' ? 'Confirmé' : ($st === 'cancelled' ? 'Annulé' : 'En attente') ?>
                              </span>
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

      </div>
    </div>
  </div>
</div>
<?php
$content = ob_get_clean();

$view = [
    'title' => 'Admin - Dashboard',
    'heading' => 'Tableau de bord',
    'active' => '',
    'content' => $content,
    'flash' => $flash ?? [],
];

require __DIR__ . '/_layout.php';

