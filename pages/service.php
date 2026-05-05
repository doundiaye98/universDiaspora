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
$problemText = 'Vous manquez de temps, de repères ou d’interlocuteur fiable pour faire avancer ce projet entre la France et votre pays d’origine.';
$solutionText = 'Univers Diaspora vous propose un cadre clair, des étapes simples et un accompagnement humain avec suivi personnalisé.';
$resultText = 'Un projet mieux structuré, des démarches plus rapides et une visibilité concrète sur les prochaines actions.';
$serviceFaq = [];
for ($i = 1; $i <= 3; $i++) {
    $q = trim((string)($service['faq' . $i . '_q'] ?? ''));
    $a = trim((string)($service['faq' . $i . '_a'] ?? ''));
    if ($q !== '' && $a !== '') {
        $serviceFaq[] = ['q' => $q, 'a' => $a];
    }
}
if (empty($serviceFaq)) {
    $serviceFaq = [
        [
            'q' => 'Ce service est-il disponible pour la diaspora à Paris et Colombes ?',
            'a' => 'Oui, nous accompagnons les membres de la diaspora depuis nos bureaux de Paris 18, Paris 17 et Colombes, avec suivi à distance si besoin.',
        ],
        [
            'q' => 'Combien de temps faut-il pour démarrer ?',
            'a' => 'Après votre premier contact, nous revenons vers vous rapidement pour cadrer le besoin et proposer un plan d’action concret.',
        ],
        [
            'q' => 'Quels documents dois-je préparer ?',
            'a' => 'Cela dépend du service choisi. Lors du premier échange, nous vous donnons la liste exacte des éléments à fournir.',
        ],
    ];
}

$otherServices = array_values(array_filter($services, static function (array $s) use ($currentSlug): bool {
    return ($s['slug'] ?? '') !== $currentSlug;
}));

ob_start();
?>
<style>
  .ud-service-premium {
    color: #fff;
    background:
      radial-gradient(1200px 520px at 88% 0%, rgba(217,160,74,.12), transparent 58%),
      radial-gradient(900px 420px at 5% 30%, rgba(30,58,110,.24), transparent 62%),
      linear-gradient(160deg, rgba(10,18,40,.92), rgba(7,12,30,.95));
    padding-bottom: 0;
  }
  .sp-container { max-width: 1240px; margin: 0 auto; padding: 0 1rem; }
  .sp-back {
    display: inline-flex;
    align-items: center;
    gap: .5rem;
    text-decoration: none;
    color: rgba(255,255,255,.72);
    letter-spacing: .12em;
    text-transform: uppercase;
    font-size: .66rem;
    margin-bottom: 1.2rem;
  }
  .sp-back:hover { color: var(--ud-gold); }
  .sp-hero {
    padding: 3.25rem 0 3rem;
  }
  .sp-kicker {
    font-size: .64rem;
    letter-spacing: .28em;
    text-transform: uppercase;
    color: rgba(255,255,255,.54);
    margin-bottom: 1.15rem;
  }
  .sp-title {
    margin: 0 0 1rem;
    font-family: var(--ud-font-display, serif);
    font-size: clamp(2.3rem, 7vw, 4.6rem);
    line-height: .97;
    letter-spacing: -.02em;
    color: #fff;
  }
  .sp-title em { color: var(--ud-gold); font-style: italic; }
  .sp-sub {
    color: rgba(255,255,255,.74);
    max-width: 650px;
    font-size: .95rem;
    line-height: 1.8;
    margin-bottom: 1.6rem;
  }
  .sp-actions { display: flex; flex-wrap: wrap; gap: .65rem; margin-bottom: 1.4rem; }
  .sp-chip-row { display: flex; flex-wrap: wrap; gap: .5rem; }
  .sp-chip {
    border: 1px solid rgba(217,160,74,.42);
    color: rgba(255,255,255,.82);
    background: rgba(217,160,74,.1);
    border-radius: 999px;
    padding: .38rem .7rem;
    font-size: .66rem;
    letter-spacing: .08em;
    text-transform: uppercase;
  }
  .sp-main-section { padding: 2.75rem 0; }
  .sp-value-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: .8rem;
    margin-bottom: 1.5rem;
  }
  .sp-value {
    border: 1px solid rgba(255,255,255,.12);
    border-radius: 10px;
    background: rgba(255,255,255,.04);
    padding: .9rem;
  }
  .sp-value__title {
    font-size: .72rem;
    letter-spacing: .12em;
    text-transform: uppercase;
    color: var(--ud-gold-soft);
    margin: 0 0 .5rem;
  }
  .sp-value__text {
    margin: 0;
    color: rgba(255,255,255,.78);
    font-size: .86rem;
    line-height: 1.6;
  }
  .sp-label {
    color: rgba(255,255,255,.5);
    letter-spacing: .24em;
    text-transform: uppercase;
    font-size: .64rem;
    margin-bottom: 1.2rem;
  }
  .sp-steps {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
    gap: .95rem;
  }
  .sp-step {
    border: 1px solid rgba(255,255,255,.12);
    border-radius: 12px;
    background: rgba(255,255,255,.04);
    padding: 1rem;
  }
  .sp-step__num {
    width: 2.4rem;
    height: 2.4rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    font-size: .9rem;
    font-weight: 700;
    margin-bottom: .7rem;
    color: var(--ud-gold);
    border: 1px solid rgba(217,160,74,.55);
    background: rgba(217,160,74,.12);
  }
  .sp-step__title { font-weight: 700; margin-bottom: .3rem; font-size: 1rem; }
  .sp-step__desc { color: rgba(255,255,255,.68); font-size: .86rem; line-height: 1.7; }
  .sp-details {
    margin-top: 2rem;
    border-top: 1px solid rgba(255,255,255,.1);
    padding-top: 1.6rem;
  }
  .sp-details .ud-about-p,
  .sp-details .ud-about-p * { color: rgba(255,255,255,.78) !important; }
  .sp-related-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
    gap: .75rem;
  }
  .sp-related {
    text-decoration: none;
    color: #fff;
    border: 1px solid rgba(255,255,255,.11);
    border-radius: 10px;
    background: rgba(255,255,255,.04);
    padding: .7rem .8rem;
    display: flex;
    align-items: center;
    gap: .55rem;
    transition: transform .24s ease, border-color .24s ease, background .24s ease;
  }
  .sp-related:hover {
    transform: translateY(-3px);
    border-color: rgba(217,160,74,.55);
    background: rgba(217,160,74,.1);
  }
  .sp-faq {
    display: grid;
    gap: .55rem;
  }
  .sp-faq__item {
    border: 1px solid rgba(255,255,255,.12);
    border-radius: 10px;
    background: rgba(255,255,255,.03);
    padding: .7rem .85rem;
  }
  .sp-faq__item summary {
    cursor: pointer;
    font-weight: 700;
    color: #fff;
  }
  .sp-faq__item p {
    margin: .6rem 0 0;
    color: rgba(255,255,255,.74);
    line-height: 1.6;
    font-size: .9rem;
  }
  .sp-reveal {
    opacity: 0;
    transform: translateY(24px);
    transition: opacity .62s cubic-bezier(.4,0,.2,1), transform .62s cubic-bezier(.4,0,.2,1);
  }
  .sp-reveal.is-visible {
    opacity: 1;
    transform: translateY(0);
  }
  .sp-r1 { transition-delay: .08s; }
  .sp-r2 { transition-delay: .16s; }
  .sp-r3 { transition-delay: .24s; }
  @media (prefers-reduced-motion: reduce) {
    .sp-reveal { opacity: 1 !important; transform: none !important; transition: none !important; }
  }
  /* Colle le fond bleu à la section finale / footer */
  .ud-service-premium + .ud-footer {
    margin-top: 0 !important;
  }
</style>

<section class="ud-service-premium">
  <div class="sp-container">
    <section class="sp-hero">
      <a class="sp-back sp-reveal" href="<?= h($baseUrl) ?>/#services">← Tous les services</a>
      <div class="sp-kicker sp-reveal sp-r1">Univers Diaspora · Service</div>
      <h1 class="sp-title sp-reveal sp-r1"><?= h($title) ?></h1>
      <p class="sp-sub sp-reveal sp-r2"><?= h($description) ?></p>
      <div class="sp-actions sp-reveal sp-r2">
        <a class="btn btn-primary ud-btn ud-btn--cta" href="<?= h($baseUrl) ?>/#contact">Contact</a>
        <a class="btn btn-outline-light ud-btn ud-btn--ghost" href="<?= h($baseUrl . '/?page=demarrer-maintenant&service=' . rawurlencode($currentSlug)) ?>">Démarrer</a>
      </div>
      <?php $bullets = $service['bullets'] ?? []; ?>
      <?php if (!empty($bullets)): ?>
        <div class="sp-chip-row sp-reveal sp-r3">
          <?php foreach ($bullets as $b): ?>
            <span class="sp-chip"><?= h((string)$b) ?></span>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>

    <section class="sp-main-section">
      <div class="sp-value-grid sp-reveal sp-r1">
        <article class="sp-value">
          <h2 class="sp-value__title">Problème</h2>
          <p class="sp-value__text"><?= h($problemText) ?></p>
        </article>
        <article class="sp-value">
          <h2 class="sp-value__title">Solution</h2>
          <p class="sp-value__text"><?= h($solutionText) ?></p>
        </article>
        <article class="sp-value">
          <h2 class="sp-value__title">Résultat attendu</h2>
          <p class="sp-value__text"><?= h($resultText) ?></p>
        </article>
      </div>
      <div class="sp-label sp-reveal">— Déroulement</div>
      <div class="sp-steps">
        <?php foreach ($serviceSteps as $idx => $st): ?>
          <article class="sp-step sp-reveal sp-r<?= (($idx % 3) + 1) ?>">
            <div class="sp-step__num"><?= (int)($idx + 1) ?></div>
            <div class="sp-step__title"><?= h((string)($st['title'] ?? '')) ?></div>
            <div class="sp-step__desc"><?= h((string)($st['text'] ?? '')) ?></div>
          </article>
        <?php endforeach; ?>
      </div>

      <?php
      $detailsRaw = trim((string)($service['details'] ?? ''));
      if ($detailsRaw !== ''):
          $detailsHtml = !empty($service['details_is_html']);
          ?>
        <div class="sp-details sp-reveal sp-r2">
          <div class="sp-label">— Détails</div>
          <?php if ($detailsHtml): ?>
            <div class="ud-about-p ud-about-p--html"><?= services_sanitize_details_html($detailsRaw) ?></div>
          <?php else: ?>
            <div class="ud-about-p"><?= nl2br(h($detailsRaw)) ?></div>
          <?php endif; ?>
        </div>
      <?php endif; ?>

      <div class="sp-actions sp-reveal sp-r3 mt-4">
        <a class="btn btn-primary ud-btn ud-btn--cta" href="<?= h($baseUrl) ?>/#contact">Écrire — formulaire</a>
        <a class="btn btn-outline-light ud-btn ud-btn--ghost" href="<?= h($baseUrl) ?>/?page=rendez-vous">Prendre rendez-vous</a>
      </div>
    </section>

    <section class="sp-main-section">
      <div class="sp-label sp-reveal">— FAQ</div>
      <div class="sp-faq">
        <?php foreach ($serviceFaq as $faq): ?>
          <details class="sp-faq__item sp-reveal sp-r2">
            <summary><?= h((string)$faq['q']) ?></summary>
            <p><?= h((string)$faq['a']) ?></p>
          </details>
        <?php endforeach; ?>
      </div>
    </section>

    <?php if (!empty($otherServices)): ?>
      <section class="sp-main-section border-bottom-0">
        <div class="sp-label sp-reveal">— Autres services</div>
        <div class="sp-related-grid">
          <?php foreach (array_slice($otherServices, 0, 8) as $s): ?>
            <?php $href = $s['external_url'] ?? ($baseUrl . '/?page=' . urlencode((string)$s['slug'])); ?>
            <a class="sp-related sp-reveal sp-r2" href="<?= h($href) ?>" <?= isset($s['external_url']) ? 'target="_blank" rel="noopener noreferrer"' : '' ?>>
              <img src="<?= h(service_icon_url((string)($s['icon'] ?? ''), $baseUrl, (string)($s['slug'] ?? ''))) ?>" alt="" width="24" height="24" loading="lazy">
              <span><?= h((string)$s['title']) ?></span>
            </a>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endif; ?>
  </div>
</section>

<script>
(() => {
  const nodes = document.querySelectorAll('.sp-reveal');
  if (!nodes.length) return;
  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches || !('IntersectionObserver' in window)) {
    nodes.forEach((n) => n.classList.add('is-visible'));
    return;
  }
  const io = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        entry.target.classList.add('is-visible');
        io.unobserve(entry.target);
      }
    });
  }, { threshold: 0.12 });
  nodes.forEach((n) => io.observe(n));
})();
</script>
<?php
$faqSchema = [
    '@context' => 'https://schema.org',
    '@type' => 'FAQPage',
    'mainEntity' => array_map(static function (array $faq): array {
        return [
            '@type' => 'Question',
            'name' => (string)$faq['q'],
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text' => (string)$faq['a'],
            ],
        ];
    }, $serviceFaq),
];
?>
<script type="application/ld+json"><?= json_encode($faqSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
<?php
$content = ob_get_clean();

$view = [
    'title' => $title . ' à Paris et Colombes — Univers Diaspora',
    'meta_description' => (function () use ($title, $description): string {
        $base = trim($title . ' pour la diaspora à Paris et Colombes. ' . trim($description) . ' Accompagnement rapide et personnalisé.');
        return function_exists('mb_substr') ? mb_substr($base, 0, 160) : substr($base, 0, 160);
    })(),
    'active' => $service['slug'] ?? '',
    'content' => $content,
];

require __DIR__ . '/_layout.php';
