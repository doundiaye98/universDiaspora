<?php
declare(strict_types=1);

/** @var string $slug */
$services = function_exists('services_all') ? services_all() : (require __DIR__ . '/../data/services.php');
$config = require __DIR__ . '/../config/config.php';
$baseUrl = ud_site_base_url();

$service = function_exists('services_find_by_slug') ? services_find_by_slug($slug) : null;
if ($service === null) {
    foreach ($services as $s) {
        if (($s['slug'] ?? '') === $slug) {
            $service = $s;
            break;
        }
    }
}

if ($service !== null) {
    $externalUrl = trim((string)($service['external_url'] ?? ''));
    if ($externalUrl !== '' && filter_var($externalUrl, FILTER_VALIDATE_URL)) {
        redirect($externalUrl);
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

$voletsAll = require __DIR__ . '/../data/service_volets.php';
$serviceVolets = isset($voletsAll[$currentSlug]) && is_array($voletsAll[$currentSlug])
    ? $voletsAll[$currentSlug]
    : [];
$hasVolets = !empty($serviceVolets);

$projectsAllPath = __DIR__ . '/../data/service_projects.php';
$projectsAll = is_file($projectsAllPath) ? require $projectsAllPath : [];
$serviceProjects = (isset($projectsAll[$currentSlug]) && is_array($projectsAll[$currentSlug]))
    ? $projectsAll[$currentSlug]
    : [];
$hasProjects = !empty($serviceProjects);

$serviceDisclaimers = [
    'assurances-credits' =>
        'Univers Diaspora n’est ni établissement bancaire, ni intermédiaire en opérations bancaires et services de paiement (IOBSP), ni intermédiaire en assurance. '
        . 'Notre rôle est l’information, l’organisation des dossiers et la mise en relation. '
        . 'Tout conseil, souscription, négociation ou distribution de produits bancaires, de crédit ou d’assurance relève d’établissements habilités, immatriculés à l’ORIAS le cas échéant.',
    'creation-gestion-d-entreprises' =>
        'Univers Diaspora n’est ni avocat, ni expert‑comptable, ni commissaire aux comptes. '
        . 'Nous accompagnons le cadrage administratif et organisationnel de votre projet. '
        . 'Les actes juridiques, comptables et fiscaux engageants restent du ressort des professionnels habilités.',
    'immobilier-btp' =>
        'Univers Diaspora n’est ni notaire, ni agence immobilière au sens de la loi Hoguet, ni maître d’œuvre, ni entreprise de BTP. '
        . 'Nous facilitons le cadrage du projet et la coordination avec les professionnels du secteur. '
        . 'La signature des actes, la maîtrise d’œuvre et l’exécution des travaux relèvent de prestataires habilités.',
    'assistances-administratives' =>
        'Univers Diaspora intervient en assistance administrative et n’est pas mandataire d’une administration. '
        . 'Les décisions finales, les actes officiels et les recours formalisés relèvent des autorités compétentes ou de professionnels du droit.',
    'formations-emplois' =>
        'Univers Diaspora propose un accompagnement à l’orientation et à la recherche d’emploi. '
        . 'Le recours à un organisme de formation enregistré (Qualiopi le cas échéant) ou à un opérateur public de l’emploi reste à votre initiative.',
    'pompes-funebres' =>
        'Univers Diaspora accompagne les familles dans l’organisation et les démarches liées aux obsèques. '
        . 'Les prestations funéraires réglementées sont réalisées par des opérateurs funéraires habilités ; nous coordonnons et orientons sans nous y substituer.',
];
$customDisclaimer = $serviceDisclaimers[$currentSlug] ?? '';

$serviceBgImages = [
    'conseils-accompagnements' => 'conseils-accompagnements.jpg',
    'immobilier-btp' => 'immobilier-btp.jpg',
    'creation-gestion-d-entreprises' => 'creation-gestion-d-entreprises.jpg',
    'transports' => 'transports.jpg',
    'assistances-administratives' => 'assistances-administratives.jpg',
];
$serviceBgFile = $serviceBgImages[$currentSlug] ?? '';
$serviceBgUrl = '';
if ($serviceBgFile !== '') {
    $bgPath = __DIR__ . '/../public/img/services/' . $serviceBgFile;
    if (is_file($bgPath)) {
        $serviceBgUrl = rtrim($baseUrl, '/') . '/public/img/services/' . rawurlencode($serviceBgFile);
    }
}
$hasServiceBg = $serviceBgUrl !== '';

$titleWords = preg_split('/\s+/u', trim((string)$title)) ?: [(string)$title];
$titleLead = count($titleWords) > 1 ? implode(' ', array_slice($titleWords, 0, -1)) : '';
$titleAccent = count($titleWords) > 1 ? (string)end($titleWords) : (string)$titleWords[0];
$voletCount = count($serviceVolets);

ob_start();
?>
<section class="ud-atelier ud-pole<?= $hasServiceBg ? ' ud-atelier--visual' : '' ?>">
  <header class="ud-pole__hero at-reveal">
    <div class="container px-3 px-sm-4">
      <a class="ud-pole__back" href="<?= h($baseUrl) ?>/#services">
        <span aria-hidden="true">&larr;</span> Tous les pôles
      </a>
      <p class="ud-pole__mark">Univers Diaspora</p>
      <p class="ud-pole__promise">Faire de vos rêves une réalité</p>
      <h1 class="ud-pole__title">
        <?php if ($iconSrc !== ''): ?>
          <img class="ud-pole__icon" src="<?= h($iconSrc) ?>" alt="" width="48" height="48" loading="eager">
        <?php endif; ?>
        <?php if ($titleLead !== ''): ?>
          <?= h($titleLead) ?><br><span><?= h($titleAccent) ?></span>
        <?php else: ?>
          <span><?= h($titleAccent) ?></span>
        <?php endif; ?>
      </h1>
      <p class="ud-pole__lead"><?= h($description) ?></p>
      <div class="ud-pole__actions">
        <a class="btn btn-primary ud-btn ud-btn--cta" href="<?= h(ud_appointment_url($baseUrl, $currentSlug)) ?>">Prendre rendez-vous</a>
        <a class="btn btn-outline-light ud-btn ud-btn--ghost ud-btn--on-dark" href="<?= h($baseUrl . '/?page=demarrer-maintenant&service=' . rawurlencode($currentSlug)) ?>">Démarrer le parcours</a>
      </div>
      <?php if ($hasVolets): ?>
        <p class="ud-pole__meta"><?= (int)$voletCount ?> volet<?= $voletCount > 1 ? 's' : '' ?> · Paris &amp; Colombes</p>
      <?php endif; ?>
    </div>
    <?php if ($hasServiceBg): ?>
      <div class="ud-pole__bg" style="background-image:url('<?= h($serviceBgUrl) ?>')" aria-hidden="true"></div>
      <div class="ud-pole__veil" aria-hidden="true"></div>
    <?php endif; ?>
  </header>

  <?php if ($hasVolets): ?>
    <nav id="volets-top" class="at-rail at-reveal" aria-label="Volets <?= h((string)$title) ?>">
      <div class="at-wrap at-rail__inner">
        <?php foreach ($serviceVolets as $idx => $volet): ?>
          <a class="at-rail__link" href="#<?= h((string)$volet['id']) ?>">
            <span class="at-rail__n"><?= str_pad((string)((int)$idx + 1), 2, '0', STR_PAD_LEFT) ?></span>
            <span class="at-rail__t"><?= h((string)$volet['label']) ?></span>
          </a>
        <?php endforeach; ?>
      </div>
    </nav>
  <?php elseif (!empty($service['bullets'])): ?>
    <div class="at-rail at-reveal">
      <div class="at-wrap at-rail__inner at-rail__inner--tags">
        <?php foreach ($service['bullets'] as $b): ?>
          <span class="at-rail__tag"><?= h((string)$b) ?></span>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>

  <div class="at-wrap at-main">
    <section class="at-manifesto at-reveal" aria-label="Cadre de l’accompagnement">
      <div class="at-manifesto__head">
        <p class="at-kicker">Le cadre</p>
        <h2 class="at-heading">Comprendre · Agir · Avancer</h2>
      </div>
      <div class="at-triad">
        <article class="at-triad__item">
          <span class="at-triad__n" aria-hidden="true">01</span>
          <h3 class="at-triad__label">Problème</h3>
          <p class="at-triad__text"><?= h($problemText) ?></p>
        </article>
        <article class="at-triad__item at-triad__item--focus">
          <span class="at-triad__n" aria-hidden="true">02</span>
          <h3 class="at-triad__label">Solution</h3>
          <p class="at-triad__text"><?= h($solutionText) ?></p>
        </article>
        <article class="at-triad__item">
          <span class="at-triad__n" aria-hidden="true">03</span>
          <h3 class="at-triad__label">Résultat</h3>
          <p class="at-triad__text"><?= h($resultText) ?></p>
        </article>
      </div>
    </section>

    <?php if ($hasVolets): ?>
      <section class="at-section" aria-labelledby="at-volets-heading">
        <div class="at-section__head at-reveal">
          <p class="at-kicker">Offre</p>
          <h2 id="at-volets-heading" class="at-heading">Volets d’accompagnement</h2>
          <p class="at-section__sub">Chaque volet est un parcours autonome — choisissez celui qui correspond à votre situation.</p>
        </div>

        <div class="at-volets">
          <?php foreach ($serviceVolets as $idx => $volet): ?>
            <?php
            $rdvHref = ud_appointment_url($baseUrl, $currentSlug, (string)$volet['id']);
            $startHref = $baseUrl . '/?page=demarrer-maintenant'
                . '&service=' . rawurlencode($currentSlug)
                . '&volet=' . rawurlencode((string)$volet['id']);
            $num = str_pad((string)((int)$idx + 1), 2, '0', STR_PAD_LEFT);
            $flip = ((int)$idx % 2) === 1;
            ?>
            <article
              id="<?= h((string)$volet['id']) ?>"
              class="at-volet at-reveal<?= $flip ? ' at-volet--alt' : '' ?>"
            >
              <div class="at-volet__index" aria-hidden="true">
                <span><?= h($num) ?></span>
              </div>
              <div class="at-volet__content">
                <h3 class="at-volet__title"><?= h((string)$volet['label']) ?></h3>
                <p class="at-volet__lead"><?= h((string)$volet['lead']) ?></p>
                <p class="at-volet__text"><?= h((string)$volet['text']) ?></p>
                <div class="at-volet__actions">
                  <a class="at-btn at-btn--navy at-btn--sm" href="<?= h($rdvHref) ?>">Prendre rendez-vous</a>
                  <a class="at-btn at-btn--line at-btn--sm" href="<?= h($startHref) ?>">Démarrer</a>
                </div>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endif; ?>

    <?php if ($hasProjects): ?>
      <section class="at-showcase at-reveal" aria-labelledby="at-projects-heading">
        <div class="at-showcase__glow" aria-hidden="true"></div>
        <div class="at-showcase__head">
          <p class="at-showcase__kicker">Réalisations</p>
          <h2 id="at-projects-heading" class="at-showcase__title">
            Projets <em>réalisés</em>
          </h2>
          <p class="at-showcase__sub">Trois sites conçus, développés et mis en ligne par Univers Diaspora.</p>
        </div>

        <ol class="at-showcase__list">
          <?php foreach ($serviceProjects as $idx => $project): ?>
            <?php
            $pUrl = trim((string)($project['url'] ?? ''));
            $pName = (string)($project['name'] ?? '');
            $pLabel = (string)($project['label'] ?? '');
            $pText = (string)($project['text'] ?? '');
            $pTag = trim((string)($project['tag'] ?? ''));
            $pTone = preg_replace('~[^a-z0-9-]~', '', strtolower((string)($project['tone'] ?? ''))) ?: 'default';
            $pNum = str_pad((string)((int)$idx + 1), 2, '0', STR_PAD_LEFT);
            $pHost = $pUrl !== '' ? (string)(parse_url($pUrl, PHP_URL_HOST) ?: '') : '';
            $nameParts = preg_split('/\s+/u', trim($pName)) ?: [$pName];
            $nameLead = count($nameParts) > 1 ? implode(' ', array_slice($nameParts, 0, -1)) : '';
            $nameAccent = count($nameParts) > 1 ? (string)end($nameParts) : (string)$nameParts[0];
            ?>
            <li class="at-showcase__item at-showcase__item--<?= h($pTone) ?>">
              <a class="at-showcase__card" href="<?= h($pUrl) ?>" target="_blank" rel="noopener noreferrer">
                <span class="at-showcase__index" aria-hidden="true"><?= h($pNum) ?></span>
                <span class="at-showcase__body">
                  <span class="at-showcase__meta">
                    <span class="at-showcase__label"><?= h($pLabel) ?></span>
                    <?php if ($pTag !== ''): ?>
                      <span class="at-showcase__tag"><?= h($pTag) ?></span>
                    <?php endif; ?>
                  </span>
                  <span class="at-showcase__name">
                    <?php if ($nameLead !== ''): ?>
                      <?= h($nameLead) ?> <em><?= h($nameAccent) ?></em>
                    <?php else: ?>
                      <em><?= h($nameAccent) ?></em>
                    <?php endif; ?>
                  </span>
                  <span class="at-showcase__text"><?= h($pText) ?></span>
                  <?php if ($pHost !== ''): ?>
                    <span class="at-showcase__host"><?= h($pHost) ?></span>
                  <?php endif; ?>
                </span>
                <span class="at-showcase__cta">
                  <span class="at-showcase__cta-label">Découvrir</span>
                  <span class="at-showcase__cta-arrow" aria-hidden="true">→</span>
                </span>
              </a>
            </li>
          <?php endforeach; ?>
        </ol>
      </section>
    <?php endif; ?>

    <section class="at-section at-reveal" aria-labelledby="at-steps-heading">
      <div class="at-section__head">
        <p class="at-kicker">Méthode</p>
        <h2 id="at-steps-heading" class="at-heading">Déroulement</h2>
      </div>
      <ol class="at-timeline">
        <?php foreach ($serviceSteps as $idx => $st): ?>
          <li class="at-timeline__item at-reveal at-r<?= (($idx % 3) + 1) ?>">
            <span class="at-timeline__dot" aria-hidden="true"><?= (int)($idx + 1) ?></span>
            <div class="at-timeline__body">
              <h3 class="at-timeline__title"><?= h((string)($st['title'] ?? '')) ?></h3>
              <p class="at-timeline__text"><?= h((string)($st['text'] ?? '')) ?></p>
            </div>
          </li>
        <?php endforeach; ?>
      </ol>
    </section>

    <?php
    $detailsRaw = trim((string)($service['details'] ?? ''));
    if ($detailsRaw !== ''):
        $detailsHtml = !empty($service['details_is_html']);
        ?>
      <section class="at-section at-details at-reveal" aria-labelledby="at-details-heading">
        <div class="at-section__head">
          <p class="at-kicker">Précisions</p>
          <h2 id="at-details-heading" class="at-heading">Détails</h2>
        </div>
        <?php if ($detailsHtml): ?>
          <div class="ud-about-p ud-about-p--html"><?= services_sanitize_details_html($detailsRaw) ?></div>
        <?php else: ?>
          <div class="ud-about-p"><?= nl2br(h($detailsRaw)) ?></div>
        <?php endif; ?>
      </section>
    <?php endif; ?>
  </div>

  <aside class="at-cta-band at-reveal">
    <div class="at-wrap at-cta-band__inner">
      <div>
        <p class="at-kicker at-kicker--light">Prochaine étape</p>
        <h2 class="at-cta-band__title">Prêt à structurer votre projet&nbsp;?</h2>
        <p class="at-cta-band__text">Un échange court pour clarifier votre besoin et vous orienter vers le bon volet.</p>
      </div>
      <div class="ud-pole__actions">
        <a class="btn btn-primary ud-btn ud-btn--cta" href="<?= h(ud_appointment_url($baseUrl, $currentSlug)) ?>">Prendre rendez-vous</a>
        <a class="btn btn-outline-light ud-btn ud-btn--ghost ud-btn--on-dark" href="<?= h($baseUrl) ?>/#contact">Nous écrire</a>
      </div>
    </div>
  </aside>

  <div class="at-wrap">
    <section class="at-section at-reveal" aria-labelledby="at-faq-heading">
      <div class="at-section__head">
        <p class="at-kicker">Questions</p>
        <h2 id="at-faq-heading" class="at-heading">FAQ</h2>
      </div>
      <div class="at-faq">
        <?php foreach ($serviceFaq as $faq): ?>
          <details class="at-faq__item">
            <summary><?= h((string)$faq['q']) ?></summary>
            <p><?= h((string)$faq['a']) ?></p>
          </details>
        <?php endforeach; ?>
      </div>
    </section>

    <aside class="at-disclaimer at-reveal" role="note" aria-label="Mention d’information">
      <div class="at-disclaimer__title">Bon à savoir</div>
      <?php if ($customDisclaimer !== ''): ?>
        <p><?= h($customDisclaimer) ?></p>
      <?php endif; ?>
      <p>
        Cet accompagnement vise l’information, l’orientation et la coordination administrative.
        Il ne constitue ni un conseil juridique, ni un conseil fiscal, ni un conseil financier ou patrimonial réglementé.
        Pour toute décision engageante, l’intervention d’un professionnel habilité est recommandée.
        Voir nos <a href="<?= h($baseUrl) ?>/?page=mentions-legales">mentions légales</a>
        et notre <a href="<?= h($baseUrl) ?>/?page=politique-confidentialite">politique de confidentialité</a>.
      </p>
    </aside>

    <?php if (!empty($otherServices)): ?>
      <section class="at-section at-more at-reveal" aria-labelledby="at-more-heading">
        <div class="at-section__head">
          <p class="at-kicker">Continuer</p>
          <h2 id="at-more-heading" class="at-heading">Autres pôles</h2>
        </div>
        <div class="at-more__grid">
          <?php foreach (array_slice($otherServices, 0, 8) as $s): ?>
            <?php $href = $s['external_url'] ?? ($baseUrl . '/?page=' . urlencode((string)$s['slug'])); ?>
            <a class="at-more__card" href="<?= h($href) ?>" <?= isset($s['external_url']) ? 'target="_blank" rel="noopener noreferrer"' : '' ?>>
              <img <?= function_exists('service_icon_img_attrs')
                ? service_icon_img_attrs((string)($s['icon'] ?? ''), $baseUrl, (string)($s['slug'] ?? ''), 56, 56)
                : 'src="' . h(service_icon_url((string)($s['icon'] ?? ''), $baseUrl, (string)($s['slug'] ?? ''))) . '" alt="" width="56" height="56" loading="lazy"' ?>>
              <span><?= h((string)$s['title']) ?></span>
              <span class="at-more__arrow" aria-hidden="true">→</span>
            </a>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endif; ?>
  </div>
</section>

<script>
(() => {
  const nodes = document.querySelectorAll('.at-reveal');
  if (!nodes.length) return;

  const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (reduce || !('IntersectionObserver' in window)) {
    nodes.forEach((n) => n.classList.add('is-visible'));
    return;
  }

  document.documentElement.classList.add('at-motion');
  const io = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        entry.target.classList.add('is-visible');
        io.unobserve(entry.target);
      }
    });
  }, { threshold: 0.08, rootMargin: '40px 0px' });
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
    'ai_context_slug' => (string)($service['slug'] ?? ''),
    'ai_context_title' => (string)($service['title'] ?? ''),
];

require __DIR__ . '/_layout.php';
