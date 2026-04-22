<?php
declare(strict_types=1);

/** @var string $slug */
$services = function_exists('services_all') ? services_all() : (require __DIR__ . '/../data/services.php');
$config = require __DIR__ . '/../config/config.php';
$baseUrl = rtrim($config['app']['base_url'], '/');

$service = function_exists('services_find_by_slug') ? services_find_by_slug($slug) : null;
if ($service === null) {
    foreach ($services as $s) {
        if (($s['slug'] ?? '') === $slug) {
            $service = $s;
            break;
        }
    }
}

if ($service === null) {
    http_response_code(404);
    $title = 'Page introuvable';
    ob_start();
    ?>
    <section class="ud-service-page ud-service-page--notfound py-5">
      <div class="container px-3 px-sm-4 py-5 text-center">
        <h1 class="ud-service-page__title mb-3">Page introuvable</h1>
        <p class="text-muted mb-4" style="max-width: 26rem; margin-left: auto; margin-right: auto;">
          Ce service n’existe pas ou n’est plus disponible.
        </p>
        <div class="d-flex flex-wrap justify-content-center gap-2">
          <a class="btn btn-primary ud-btn ud-btn--cta" href="<?= h($baseUrl) ?>/">Accueil</a>
          <a class="btn btn-outline-primary ud-btn ud-btn--ghost" href="<?= h($baseUrl) ?>/#services">Services</a>
        </div>
      </div>
    </section>
    <?php
    $content = ob_get_clean();
    $view = [
        'title' => $title . ' — Univers Diaspora',
        'meta_description' => 'Page introuvable — Univers Diaspora.',
        'content' => $content,
        'active' => '',
    ];
    require __DIR__ . '/_layout.php';
    exit;
}

$title = $service['title'] ?? 'Service';
$currentSlug = (string)($service['slug'] ?? '');
$description = (string)($service['description'] ?? 'Un accompagnement clair, rapide et sur‑mesure.');
$serviceSteps = service_steps_for_display($service);
$iconSrc = service_icon_url((string)($service['icon'] ?? ''), $baseUrl, $currentSlug);

$otherServices = array_values(array_filter($services, static function (array $s) use ($currentSlug): bool {
    return ($s['slug'] ?? '') !== $currentSlug;
}));

ob_start();
?>
<section class="ud-service-page ud-service-page__hero">
  <div class="container px-3 px-sm-4 py-4 py-md-5">
    <p class="ud-service-page__back mb-4">
      <a class="text-decoration-none" href="<?= h($baseUrl) ?>/#services">← Tous les services</a>
    </p>

    <header class="ud-service-page__head">
      <div class="ud-service-page__icon-wrap">
        <img class="ud-service-page__icon" src="<?= h($iconSrc) ?>" alt="" width="56" height="56" decoding="async">
      </div>
      <div class="ud-service-page__intro">
        <h1 class="ud-service-page__title"><?= h($title) ?></h1>
        <p class="ud-service-page__lead"><?= h($description) ?></p>
        <div class="ud-service-page__actions">
          <a class="btn btn-primary ud-btn ud-btn--cta" href="<?= h($baseUrl) ?>/#contact">Contact</a>
          <a class="btn btn-outline-primary ud-btn ud-btn--ghost" href="<?= h($baseUrl . '/?page=demarrer-maintenant&service=' . rawurlencode($currentSlug)) ?>">Démarrer</a>
        </div>
      </div>
    </header>

    <?php
    $bullets = $service['bullets'] ?? [];
    if (!empty($bullets)):
        ?>
      <ul class="ud-service-page__bullets">
        <?php foreach ($bullets as $b): ?>
          <li><?= h($b) ?></li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>
</section>

<section class="ud-service-page__body">
  <div class="container px-3 px-sm-4 py-4 py-lg-5">
    <h2 class="ud-service-page__h2">Déroulement</h2>
    <ol class="ud-service-page__steps">
      <?php foreach ($serviceSteps as $idx => $st): ?>
        <li class="ud-service-page__step">
          <span class="ud-service-page__step-num"><?= (int)($idx + 1) ?></span>
          <div>
            <strong class="d-block mb-1"><?= h((string)($st['title'] ?? '')) ?></strong>
            <span class="text-muted"><?= h((string)($st['text'] ?? '')) ?></span>
          </div>
        </li>
      <?php endforeach; ?>
    </ol>

    <?php
    $detailsRaw = trim((string)($service['details'] ?? ''));
    if ($detailsRaw !== ''):
        $detailsHtml = !empty($service['details_is_html']);
        ?>
      <div class="ud-service-page__details mt-4 pt-4 border-top">
        <h2 class="ud-service-page__h2">Détails</h2>
        <?php if ($detailsHtml): ?>
          <div class="ud-about-p ud-about-p--html ud-service-page__details-inner">
            <?= services_sanitize_details_html($detailsRaw) ?>
          </div>
        <?php else: ?>
          <div class="ud-about-p ud-service-page__details-inner">
            <?= nl2br(h($detailsRaw)) ?>
          </div>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <p class="ud-service-page__cta-line mt-4 pt-4 mb-0">
      <a class="btn btn-primary ud-btn" href="<?= h($baseUrl) ?>/#contact">Écrire — formulaire</a>
      <a class="btn btn-link text-decoration-none" href="<?= h($baseUrl) ?>/?page=rendez-vous">Rendez-vous</a>
    </p>

    <?php if (!empty($otherServices)): ?>
      <div class="ud-service-page__related mt-5 pt-4 border-top">
        <h2 class="ud-service-page__h2 ud-service-page__h2--small">Autres services</h2>
        <div class="row g-2 g-md-3">
          <?php foreach (array_slice($otherServices, 0, 6) as $s): ?>
            <?php $href = $s['external_url'] ?? ($baseUrl . '/?page=' . urlencode($s['slug'])); ?>
            <div class="col-6 col-md-4">
              <a class="ud-service-page__related-link" href="<?= h($href) ?>" <?= isset($s['external_url']) ? 'target="_blank" rel="noopener noreferrer"' : '' ?>>
                <img src="<?= h(service_icon_url((string)($s['icon'] ?? ''), $baseUrl, (string)($s['slug'] ?? ''))) ?>" alt="" width="32" height="32" loading="lazy">
                <span><?= h($s['title']) ?></span>
              </a>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>
  </div>
</section>
<?php
$content = ob_get_clean();

$view = [
    'title' => $title . ' — Univers Diaspora',
    'meta_description' => function_exists('mb_substr')
        ? mb_substr(trim($description), 0, 160)
        : substr(trim($description), 0, 160),
    'active' => $service['slug'] ?? '',
    'content' => $content,
];

require __DIR__ . '/_layout.php';
