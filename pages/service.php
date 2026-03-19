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
    <div class="container py-5">
      <h1 class="h4 mb-3">Page introuvable</h1>
      <a class="btn btn-primary" href="<?= h($baseUrl) ?>/">Retour à l’accueil</a>
    </div>
    <?php
    $content = ob_get_clean();
    $view = ['title' => $title, 'content' => $content, 'active' => ''];
    require __DIR__ . '/_layout.php';
    exit;
}

$title = $service['title'] ?? 'Service';
$currentSlug = (string)($service['slug'] ?? '');
$description = (string)($service['description'] ?? 'Un accompagnement clair, rapide et sur‑mesure.');

$otherServices = array_values(array_filter($services, static function (array $s) use ($currentSlug): bool {
    return ($s['slug'] ?? '') !== $currentSlug;
}));

ob_start();
?>
<section class="ud-service-hero">
  <div class="container">
    <nav aria-label="Fil d’ariane" class="ud-breadcrumb">
      <a href="<?= h($baseUrl) ?>/">Accueil</a>
      <span class="ud-breadcrumb__sep">/</span>
      <span><?= h($title) ?></span>
    </nav>

    <div class="ud-service-hero__grid">
      <div class="ud-service-hero__left">
        <div class="ud-service-hero__badge">
          <img src="<?= h($baseUrl) ?>/public/assets/img/<?= h($service['icon']) ?>" alt="<?= h($title) ?>" width="64" height="64">
        </div>
        <div>
          <h1 class="ud-service-hero__title mb-2"><?= h($title) ?></h1>
          <div class="ud-service-hero__subtitle"><?= h($description) ?></div>
          <div class="d-flex flex-wrap gap-2 mt-3">
            <a class="btn btn-primary ud-btn ud-btn--cta" href="<?= h($baseUrl) ?>/#contact">Demander un devis</a>
            <a class="btn btn-outline-primary ud-btn ud-btn--ghost" href="<?= h($baseUrl) ?>/#services">Voir tous les services</a>
          </div>
        </div>
      </div>

      <div class="ud-service-hero__right">
        <div class="ud-service-hero__card">
          <div class="ud-service-hero__card-title">Ce que nous proposons</div>
          <ul class="ud-service-hero__list">
            <?php foreach (($service['bullets'] ?? []) as $b): ?>
              <li><?= h($b) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="ud-service-body py-5">
  <div class="container">
    <div class="row g-4">
      <div class="col-12 col-lg-7">
        <div class="ud-surface">
          <h2 class="h5 mb-3">Comment ça se passe</h2>
          <div class="ud-steps">
            <div class="ud-step">
              <div class="ud-step__num">1</div>
              <div>
                <div class="ud-step__title">Analyse</div>
                <div class="ud-step__text">On comprend ton besoin et on te conseille la meilleure option.</div>
              </div>
            </div>
            <div class="ud-step">
              <div class="ud-step__num">2</div>
              <div>
                <div class="ud-step__title">Proposition</div>
                <div class="ud-step__text">On te présente une solution claire, avec un plan d’action.</div>
              </div>
            </div>
            <div class="ud-step">
              <div class="ud-step__num">3</div>
              <div>
                <div class="ud-step__title">Accompagnement</div>
                <div class="ud-step__text">On avance avec toi jusqu’à la réalisation.</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-12 col-lg-5">
        <div class="ud-surface ud-cta-box">
          <div class="ud-cta-box__title">Parlons de ton projet</div>
          <div class="ud-cta-box__text">Réponse rapide. Tu peux détailler ton besoin via le formulaire.</div>
          <a class="btn btn-primary ud-btn ud-btn--wide ud-btn--shine mt-3" href="<?= h($baseUrl) ?>/#contact">
            Contacter Univers Diaspora <span class="ud-arrow" aria-hidden="true">→</span>
          </a>
          <div class="ud-cta-box__hint mt-2">Tu seras redirigé vers le formulaire de contact de l’accueil.</div>
        </div>
      </div>
    </div>

    <div class="ud-related mt-5">
      <div class="d-flex align-items-end justify-content-between flex-wrap gap-2 mb-3">
        <div>
          <div class="ud-related__kicker">Explorer</div>
          <div class="ud-related__title">Autres services</div>
        </div>
        <a class="small fw-bold text-decoration-none" href="<?= h($baseUrl) ?>/#services">Tous les services →</a>
      </div>

      <div class="row g-3">
        <?php foreach (array_slice($otherServices, 0, 6) as $s): ?>
          <?php $href = $s['external_url'] ?? ($baseUrl . '/?page=' . urlencode($s['slug'])); ?>
          <div class="col-12 col-sm-6 col-lg-4">
            <a class="ud-related-card" href="<?= h($href) ?>" <?= isset($s['external_url']) ? 'target="_blank" rel="noopener noreferrer"' : '' ?>>
              <img src="<?= h($baseUrl) ?>/public/assets/img/<?= h($s['icon']) ?>" alt="" width="40" height="40">
              <div class="ud-related-card__text">
                <div class="ud-related-card__title"><?= h($s['title']) ?></div>
                <div class="ud-related-card__meta">Découvrir</div>
              </div>
              <span class="ud-related-card__arrow" aria-hidden="true">→</span>
            </a>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>
<?php
$content = ob_get_clean();

$view = [
    'title' => $title,
    'active' => $service['slug'] ?? '',
    'content' => $content,
];

require __DIR__ . '/_layout.php';

