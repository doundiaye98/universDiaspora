<?php
declare(strict_types=1);

$config = require __DIR__ . '/../../config/config.php';
$baseUrl = rtrim($config['app']['base_url'], '/');
admin_require_login($baseUrl);

$csrf = admin_csrf_token();
$pdo = db();

$q = trim((string)($_GET['q'] ?? ''));
$serviceFilter = trim((string)($_GET['service'] ?? ''));
if ($serviceFilter !== '' && preg_match('/^[a-z0-9-]{1,120}$/', $serviceFilter) !== 1) {
    $serviceFilter = '';
}
$limit = (int)($_GET['limit'] ?? 30);
$limit = $limit > 0 && $limit <= 200 ? $limit : 30;
$page = (int)($_GET['page_no'] ?? 1);
$page = $page > 0 ? $page : 1;
$offset = ($page - 1) * $limit;

/* Stats globales (avec gestion gracieuse si la table vient juste d'être créée) */
$total = 0;
$byService = [];
$last24h = 0;
try {
    $total = (int)$pdo->query('SELECT COUNT(*) FROM ai_conversations')->fetchColumn();
    $last24h = (int)$pdo->query("SELECT COUNT(*) FROM ai_conversations WHERE created_at >= NOW() - INTERVAL 1 DAY")->fetchColumn();
    $stmt = $pdo->query(
        "SELECT matched_service_slug, COUNT(*) AS n
         FROM ai_conversations
         WHERE matched_service_slug IS NOT NULL AND matched_service_slug <> ''
         GROUP BY matched_service_slug
         ORDER BY n DESC
         LIMIT 8"
    );
    $byService = $stmt ? $stmt->fetchAll() : [];
} catch (Throwable $e) {
    $total = 0;
    $byService = [];
    $last24h = 0;
}

/* Liste filtrée */
$where = [];
$params = [];
if ($q !== '') {
    $where[] = '(question LIKE :q OR answer LIKE :q)';
    $params[':q'] = '%' . $q . '%';
}
if ($serviceFilter !== '') {
    $where[] = 'matched_service_slug = :slug';
    $params[':slug'] = $serviceFilter;
}
$whereSql = empty($where) ? '' : ' WHERE ' . implode(' AND ', $where);

$rows = [];
$filteredTotal = 0;
try {
    $countStmt = $pdo->prepare('SELECT COUNT(*) FROM ai_conversations' . $whereSql);
    $countStmt->execute($params);
    $filteredTotal = (int)$countStmt->fetchColumn();

    $sql = 'SELECT id, session_id, ip, question, answer, intent, matched_service_slug, matched_volet_id, created_at
            FROM ai_conversations'
        . $whereSql
        . ' ORDER BY created_at DESC, id DESC LIMIT ' . (int)$limit . ' OFFSET ' . (int)$offset;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();
} catch (Throwable $e) {
    $rows = [];
    $filteredTotal = 0;
}

/* Liste des slugs disponibles pour le filtre */
$services = function_exists('services_all') ? services_all() : [];

ob_start();
?>
<div class="container-fluid px-3 px-md-4">
  <div class="row g-3 mb-3">
    <div class="col-12 col-md-4">
      <div class="ud-surface ud-stat">
        <div class="ud-stat__label">Total conversations</div>
        <div class="ud-stat__value"><?= number_format($total, 0, ',', ' ') ?></div>
      </div>
    </div>
    <div class="col-12 col-md-4">
      <div class="ud-surface ud-stat">
        <div class="ud-stat__label">Dernières 24 h</div>
        <div class="ud-stat__value"><?= number_format($last24h, 0, ',', ' ') ?></div>
      </div>
    </div>
    <div class="col-12 col-md-4">
      <div class="ud-surface ud-stat">
        <div class="ud-stat__label">Top service identifié</div>
        <div class="ud-stat__value">
          <?= !empty($byService[0]['matched_service_slug'])
            ? h((string)$byService[0]['matched_service_slug']) . ' <small class="text-muted">(' . (int)$byService[0]['n'] . ')</small>'
            : '<span class="text-muted small">—</span>' ?>
        </div>
      </div>
    </div>
  </div>

  <?php if (!empty($byService)): ?>
    <div class="ud-surface mb-3">
      <div class="d-flex flex-wrap gap-2 align-items-center">
        <span class="text-muted small me-2">Répartition par service :</span>
        <?php foreach ($byService as $row): ?>
          <a class="btn btn-sm <?= $serviceFilter === (string)($row['matched_service_slug'] ?? '') ? 'btn-primary' : 'btn-outline-secondary' ?>"
             href="<?= h($baseUrl) ?>/?page=admin-ai-conversations&service=<?= h(rawurlencode((string)$row['matched_service_slug'])) ?>">
            <?= h((string)$row['matched_service_slug']) ?> · <?= (int)$row['n'] ?>
          </a>
        <?php endforeach; ?>
        <?php if ($serviceFilter !== '' || $q !== ''): ?>
          <a class="btn btn-sm btn-link" href="<?= h($baseUrl) ?>/?page=admin-ai-conversations">Réinitialiser</a>
        <?php endif; ?>
      </div>
    </div>
  <?php endif; ?>

  <div class="ud-surface mb-3">
    <form method="get" class="row g-2 align-items-end">
      <input type="hidden" name="page" value="admin-ai-conversations">
      <div class="col-12 col-md-6">
        <label class="form-label mb-1">Rechercher (question ou réponse)</label>
        <input class="form-control" type="text" name="q" value="<?= h($q) ?>" placeholder="ex. immobilier, achat, formation…">
      </div>
      <div class="col-6 col-md-3">
        <label class="form-label mb-1">Service</label>
        <select class="form-select" name="service">
          <option value="">Tous</option>
          <?php foreach ($services as $s): ?>
            <option value="<?= h((string)$s['slug']) ?>" <?= $serviceFilter === (string)$s['slug'] ? 'selected' : '' ?>>
              <?= h((string)$s['title']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-3 col-md-2">
        <label class="form-label mb-1">Par page</label>
        <select class="form-select" name="limit">
          <?php foreach ([20, 30, 50, 100] as $l): ?>
            <option value="<?= $l ?>" <?= $limit === $l ? 'selected' : '' ?>><?= $l ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-3 col-md-1 d-grid">
        <button class="btn btn-primary ud-btn ud-btn--shine" type="submit">Filtrer</button>
      </div>
    </form>
  </div>

  <div class="ud-surface">
    <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
      <div class="small text-muted">
        <?= number_format($filteredTotal, 0, ',', ' ') ?> résultat<?= $filteredTotal > 1 ? 's' : '' ?>
        <?php if ($filteredTotal > $limit): ?>
          · page <?= (int)$page ?> / <?= (int)ceil($filteredTotal / $limit) ?>
        <?php endif; ?>
      </div>
    </div>

    <?php if (empty($rows)): ?>
      <p class="text-muted mb-0">Aucune conversation enregistrée pour ce filtre.</p>
    <?php else: ?>
      <div class="ud-ai-conv-list">
        <?php foreach ($rows as $r): ?>
          <article class="ud-ai-conv-item">
            <header class="d-flex flex-wrap justify-content-between align-items-baseline gap-2 mb-2">
              <div class="small text-muted">
                <span class="text-nowrap"><?= h(date('d/m/Y H:i', strtotime((string)$r['created_at']))) ?></span>
                <?php if (!empty($r['session_id'])): ?>
                  · session <code><?= h(substr((string)$r['session_id'], 0, 10)) ?>…</code>
                <?php endif; ?>
                <?php if (!empty($r['ip'])): ?>
                  · IP <?= h((string)$r['ip']) ?>
                <?php endif; ?>
              </div>
              <div class="d-flex flex-wrap gap-1">
                <?php if (!empty($r['matched_service_slug'])): ?>
                  <span class="badge text-bg-light border">Service : <?= h((string)$r['matched_service_slug']) ?></span>
                <?php endif; ?>
                <?php if (!empty($r['matched_volet_id'])): ?>
                  <span class="badge text-bg-light border">Volet : <?= h((string)$r['matched_volet_id']) ?></span>
                <?php endif; ?>
                <?php if (!empty($r['intent'])): ?>
                  <span class="badge text-bg-secondary">Intent : <?= h((string)$r['intent']) ?></span>
                <?php endif; ?>
              </div>
            </header>
            <div class="ud-ai-conv-q">
              <span class="ud-ai-conv-tag">Q</span>
              <p class="mb-0"><?= nl2br(h((string)$r['question'])) ?></p>
            </div>
            <div class="ud-ai-conv-a mt-2">
              <span class="ud-ai-conv-tag ud-ai-conv-tag--a">R</span>
              <p class="mb-0"><?= nl2br(h((string)$r['answer'])) ?></p>
            </div>
          </article>
        <?php endforeach; ?>
      </div>

      <?php if ($filteredTotal > $limit): ?>
        <nav class="d-flex flex-wrap gap-2 mt-3" aria-label="Pagination">
          <?php
          $pages = (int)ceil($filteredTotal / $limit);
          $buildHref = static function (int $p) use ($baseUrl, $q, $serviceFilter, $limit): string {
              $params = ['page' => 'admin-ai-conversations', 'page_no' => $p, 'limit' => $limit];
              if ($q !== '') $params['q'] = $q;
              if ($serviceFilter !== '') $params['service'] = $serviceFilter;
              return $baseUrl . '/?' . http_build_query($params);
          };
          if ($page > 1): ?>
            <a class="btn btn-sm btn-outline-secondary" href="<?= h($buildHref($page - 1)) ?>">← Précédent</a>
          <?php endif; ?>
          <?php if ($page < $pages): ?>
            <a class="btn btn-sm btn-outline-secondary" href="<?= h($buildHref($page + 1)) ?>">Suivant →</a>
          <?php endif; ?>
        </nav>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</div>

<style>
.ud-stat { padding: .9rem 1rem; }
.ud-stat__label { font-size: .72rem; letter-spacing: .12em; text-transform: uppercase; color: rgba(0,0,0,.55); margin-bottom: .35rem; }
.ud-stat__value { font-size: 1.6rem; font-weight: 700; color: #1b1f2b; }

.ud-ai-conv-list { display: grid; gap: .75rem; }
.ud-ai-conv-item {
  border: 1px solid var(--ud-border, #e5e7eb);
  border-radius: 12px;
  background: rgba(255,255,255,.7);
  padding: .9rem 1rem;
}
.ud-ai-conv-q, .ud-ai-conv-a {
  display: grid;
  grid-template-columns: 28px 1fr;
  gap: .55rem;
  align-items: start;
}
.ud-ai-conv-q p, .ud-ai-conv-a p { white-space: pre-wrap; font-size: .9rem; line-height: 1.55; }
.ud-ai-conv-q p { color: #1b1f2b; }
.ud-ai-conv-a p { color: rgba(27,31,43,.86); }
.ud-ai-conv-tag {
  display: inline-flex;
  width: 24px; height: 24px;
  align-items: center; justify-content: center;
  border-radius: 6px;
  font-size: .72rem; font-weight: 700;
  background: rgba(30,58,110,.1);
  color: #1e3a6e;
}
.ud-ai-conv-tag--a {
  background: rgba(217,160,74,.15);
  color: #8a5a16;
}
</style>
<?php
$content = ob_get_clean();

$view = [
    'title' => 'Admin - Conversations IA',
    'heading' => 'Conversations IA',
    'content' => $content,
];

require __DIR__ . '/_layout.php';
