<?php
declare(strict_types=1);

$services = require __DIR__ . '/../data/services.php';
$config = require __DIR__ . '/../config/config.php';
$baseUrl = rtrim($config['app']['base_url'], '/');

$selected = (string)($_GET['service'] ?? '');
$service = null;
foreach ($services as $s) {
    if (($s['slug'] ?? '') === $selected) {
        $service = $s;
        break;
    }
}
$hasPicked = ($service !== null && empty($service['coming_soon']));
$progress = $hasPicked ? 66 : 33;

ob_start();
?>
<section class="ud-start-hero">
  <div class="container">
    <nav aria-label="Fil d’ariane" class="ud-breadcrumb">
      <a href="<?= h($baseUrl) ?>/">Accueil</a>
      <span class="ud-breadcrumb__sep">/</span>
      <span>Démarrer maintenant</span>
    </nav>

    <div class="ud-start-hero__card" data-progress="<?= (int) $progress ?>">
      <div class="ud-start-hero__kicker">Démarrer maintenant</div>
      <h1 class="ud-start-hero__title mb-2">On lance ton projet en 3 minutes</h1>
      <div class="ud-start-hero__subtitle">Choisis un service, explique ton besoin, et on te recontacte rapidement.</div>

      <div class="ud-stepper mt-4" aria-label="Progression">
        <div class="ud-stepper__bar" role="progressbar" aria-valuemin="0" aria-valuenow="<?= (int) $progress ?>" aria-valuemax="100">
          <div class="ud-stepper__fill" style="--ud-progress: <?= (int) $progress ?>%"></div>
        </div>
        <div class="ud-stepper__dots">
          <a class="ud-dot is-active" href="#step-1"><span>1</span></a>
          <a class="ud-dot<?= $hasPicked ? ' is-done' : '' ?>" href="#step-2"><span>2</span></a>
          <a class="ud-dot" href="#step-3"><span>3</span></a>
        </div>
      </div>

      <div class="ud-start-steps mt-4">
        <a class="ud-start-step is-active" href="#step-1" data-step="1">
          <div class="ud-start-step__num">1</div>
          <div>
            <div class="ud-start-step__title">Choisir</div>
            <div class="ud-start-step__text">Sélectionne le service qui t’intéresse.</div>
          </div>
        </a>
        <a class="ud-start-step<?= $hasPicked ? ' is-done' : '' ?>" href="#step-2" data-step="2">
          <div class="ud-start-step__num">2</div>
          <div>
            <div class="ud-start-step__title">Décrire</div>
            <div class="ud-start-step__text">Donne les infos essentielles (objectif, pays, délais…).</div>
          </div>
        </a>
        <a class="ud-start-step" href="#step-3" data-step="3">
          <div class="ud-start-step__num">3</div>
          <div>
            <div class="ud-start-step__title">Envoyer</div>
            <div class="ud-start-step__text">On analyse et on te propose une solution claire.</div>
          </div>
        </a>
      </div>
    </div>
  </div>
</section>

<section class="py-5">
  <div class="container">
    <div class="row g-4">
      <div class="col-12 col-lg-7">
        <div id="step-1" class="ud-surface ud-step-section" data-step-section="1">
          <div class="d-flex align-items-start justify-content-between flex-wrap gap-2 mb-3">
            <div>
              <h2 class="h5 mb-1">1) Choisis ton service</h2>
              <div class="text-muted small">Clique sur une carte pour sélectionner.</div>
            </div>
            <?php if ($service): ?>
              <span class="ud-pill ud-pill--soon" style="border-style:solid;">Sélectionné</span>
            <?php endif; ?>
          </div>

          <div class="row g-3">
            <?php foreach ($services as $s): ?>
              <?php
                $href = $baseUrl . '/?page=demarrer-maintenant&service=' . urlencode((string)$s['slug']);
                $isSelected = ($selected !== '' && $selected === ($s['slug'] ?? ''));
              ?>
              <div class="col-12 col-sm-6">
                <a class="ud-pick<?= $isSelected ? ' is-selected' : '' ?>" href="<?= h($href) ?>">
                  <img src="<?= h($baseUrl) ?>/public/assets/img/<?= h($s['icon']) ?>" alt="" width="40" height="40">
                  <div class="ud-pick__text">
                    <div class="ud-pick__title"><?= h($s['title']) ?></div>
                    <div class="ud-pick__meta"><?= !empty($s['coming_soon']) ? 'Bientôt disponible' : 'Disponible' ?></div>
                  </div>
                  <span class="ud-pick__arrow" aria-hidden="true">→</span>
                </a>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <div id="step-2" class="ud-surface mt-4 ud-step-section" data-step-section="2">
          <h2 class="h5 mb-2">2) Prépare ton message</h2>
          <div class="text-muted small mb-3">Copie/colle ce modèle dans le formulaire pour aller plus vite.</div>

          <?php
            $template = "Bonjour Univers Diaspora,\n\n";
            $template .= "Je souhaite un accompagnement pour : " . ($service['title'] ?? '[Service]') . "\n";
            $template .= "Pays / Ville : \n";
            $template .= "Objectif : \n";
            $template .= "Budget (optionnel) : \n";
            $template .= "Délai / date souhaitée : \n";
            $template .= "Détails : \n\n";
            $template .= "Merci.";
          ?>

          <textarea class="form-control" rows="10" readonly><?= h($template) ?></textarea>
          <div class="small text-muted mt-2">Astuce: clique dans la zone puis Ctrl+A / Ctrl+C.</div>
        </div>
      </div>

      <div class="col-12 col-lg-5">
        <div id="step-3" class="ud-surface ud-cta-box ud-step-section" data-step-section="3">
          <div class="ud-cta-box__title">3) Envoie-nous le message</div>
          <div class="ud-cta-box__text">Tu seras redirigé vers le formulaire de contact.</div>
          <a class="btn btn-primary ud-btn ud-btn--wide ud-btn--shine mt-3" href="<?= h($baseUrl) ?>/#contact">
            Aller au formulaire <span class="ud-arrow" aria-hidden="true">→</span>
          </a>
          <div class="ud-cta-box__hint mt-2">Pense à coller le modèle de message si besoin.</div>
        </div>

        <div class="ud-surface mt-3">
          <div class="ud-contact-card__title">Ce qu’on te demandera</div>
          <ul class="ud-service-hero__list">
            <li>Ton nom et ton email</li>
            <li>Le service concerné</li>
            <li>Les détails utiles (pays, délais…)</li>
            <li>Ton numéro (optionnel)</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</section>

<script>
(() => {
  const stepCards = Array.from(document.querySelectorAll('.ud-start-step[data-step]'));
  const dots = Array.from(document.querySelectorAll('.ud-dot'));
  const sections = Array.from(document.querySelectorAll('.ud-step-section[data-step-section]'));
  const card = document.querySelector('.ud-start-hero__card');
  const fill = document.querySelector('.ud-stepper__fill');

  if (!stepCards.length || !sections.length) return;

  const setActive = (step) => {
    stepCards.forEach(el => el.classList.toggle('is-active', el.dataset.step === String(step)));
    dots.forEach((el, idx) => el.classList.toggle('is-active', String(idx + 1) === String(step)));
  };

  const setProgress = (pct) => {
    if (!fill) return;
    fill.style.setProperty('--ud-progress', pct + '%');
    const bar = fill.parentElement;
    if (bar) bar.setAttribute('aria-valuenow', String(pct));
  };

  // initial progress from PHP (33 or 66)
  const initial = Number(card?.dataset.progress || 33);
  setProgress(initial);

  // Observe sections to highlight the current step
  const obs = new IntersectionObserver((entries) => {
    const visible = entries
      .filter(e => e.isIntersecting)
      .sort((a, b) => (b.intersectionRatio - a.intersectionRatio))[0];
    if (!visible) return;
    const step = visible.target.getAttribute('data-step-section');
    if (step) setActive(step);
  }, { threshold: [0.2, 0.35, 0.5, 0.65] });

  sections.forEach(s => obs.observe(s));

  // When user clicks a step card, smooth scroll
  stepCards.forEach(el => {
    el.addEventListener('click', (e) => {
      const href = el.getAttribute('href');
      if (!href || !href.startsWith('#')) return;
      const target = document.querySelector(href);
      if (!target) return;
      e.preventDefault();
      target.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
  });

  dots.forEach(el => {
    el.addEventListener('click', (e) => {
      const href = el.getAttribute('href');
      if (!href || !href.startsWith('#')) return;
      const target = document.querySelector(href);
      if (!target) return;
      e.preventDefault();
      target.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
  });
})();
</script>
<?php
$content = ob_get_clean();

$view = [
    'title' => 'Démarrer maintenant — Univers Diaspora',
    'meta_description' => 'Démarrez votre projet avec Univers Diaspora : choisissez un service, préparez votre message et contactez-nous.',
    'active' => '',
    'content' => $content,
];

require __DIR__ . '/_layout.php';

