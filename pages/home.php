<?php
declare(strict_types=1);

$services = function_exists('services_all') ? services_all() : (require __DIR__ . '/../data/services.php');
$config = require __DIR__ . '/../config/config.php';
$baseUrl = ud_site_base_url();

$testimonialErrors = $_SESSION['testimonial_errors'] ?? [];
$testimonialOld = $_SESSION['testimonial_old'] ?? [];
unset($_SESSION['testimonial_errors'], $_SESSION['testimonial_old']);

ob_start();
?>
<div class="ud-hero-wrap ud-hero-wrap--video-clean ud-hero-wrap--cosmos ud-hero-wrap--affiche">
<section class="ud-hero ud-hero--video-clean ud-hero--affiche">
  <div class="container">
    <div class="row align-items-center g-4 g-xl-5">
      <div class="col-12 col-xl-7">
        <p class="ud-hero__mark">Univers Diaspora</p>
        <p class="ud-hero__promise">Faire de vos rêves une réalité</p>
        <h1 class="ud-hero__headline ud-hero__headline--video ud-hero__headline--affiche mb-3">
          Tous vos services<br>
          <span class="ud-hero__brand">réunis en un seul lieu</span>
        </h1>
        <p class="ud-hero__tagline ud-hero__tagline--video mb-4">
          Conseil et accompagnement pour la diaspora — Paris 18<sup>e</sup>, Paris 17<sup>e</sup> et Colombes.
        </p>
        <div class="d-flex flex-wrap gap-2">
          <a class="btn btn-primary ud-btn ud-btn--cta" href="<?= h(ud_appointment_url($baseUrl)) ?>">Prendre rendez-vous</a>
          <a class="btn btn-outline-light ud-btn ud-btn--ghost ud-btn--on-dark" href="#services">Voir les 13 services</a>
        </div>
      </div>
      <div class="col-12 col-xl-5">
        <?php
          $orbitServices = array_values(array_filter($services, static function (array $s): bool {
              return empty($s['coming_soon']);
          }));
          $orbitN = max(1, count($orbitServices));
        ?>
        <div
          class="ud-hero-carousel"
          data-ud-carousel
          data-interval="10000"
          aria-roledescription="carousel"
          aria-label="Nos pôles de services"
        >
          <div class="ud-hero-carousel__top">
            <div class="ud-hero-carousel__badge" aria-hidden="true">
              <span class="ud-hero-carousel__n"><?= (int)$orbitN ?></span>
              <span class="ud-hero-carousel__t">services</span>
            </div>
            <div class="ud-hero-carousel__live" aria-live="polite" aria-atomic="true">
              <span data-ud-carousel-status>1 / <?= (int)$orbitN ?></span>
            </div>
          </div>

          <div class="ud-hero-carousel__stage">
            <button type="button" class="ud-hero-carousel__nav ud-hero-carousel__nav--prev" data-ud-carousel-prev aria-label="Pôle précédent">
              <span aria-hidden="true">‹</span>
            </button>

            <div class="ud-hero-carousel__viewport">
              <div class="ud-hero-carousel__track" data-ud-carousel-track>
                <?php foreach ($orbitServices as $i => $s): ?>
                  <?php
                    $href = $s['external_url'] ?? ($baseUrl . '/?page=' . urlencode((string)$s['slug']));
                    $ext = function_exists('ud_service_opens_new_tab') ? ud_service_opens_new_tab($s) : !empty($s['external_url']);
                    $desc = trim((string)($s['description'] ?? ''));
                  ?>
                  <article
                    class="ud-hero-carousel__slide<?= $i === 0 ? ' is-active' : '' ?>"
                    data-ud-carousel-slide
                    aria-hidden="<?= $i === 0 ? 'false' : 'true' ?>"
                  >
                    <a
                      class="ud-hero-carousel__card"
                      href="<?= h($href) ?>"
                      <?= $ext ? 'target="_blank" rel="noopener noreferrer"' : '' ?>
                    >
                      <span class="ud-hero-carousel__media">
                        <img <?= function_exists('service_icon_img_attrs')
                          ? service_icon_img_attrs((string)($s['icon'] ?? ''), $baseUrl, (string)($s['slug'] ?? ''), 280, 180)
                          : 'src="' . h(service_icon_url((string)($s['icon'] ?? ''), $baseUrl, (string)($s['slug'] ?? ''))) . '" alt="" width="280" height="180" loading="lazy"' ?>>
                        <span class="ud-hero-carousel__veil" aria-hidden="true"></span>
                      </span>
                      <span class="ud-hero-carousel__body">
                        <span class="ud-hero-carousel__kicker">Pôle <?= (int)$i + 1 ?></span>
                        <span class="ud-hero-carousel__title"><?= h((string)$s['title']) ?></span>
                        <?php if ($desc !== ''): ?>
                          <span class="ud-hero-carousel__desc"><?= h($desc) ?></span>
                        <?php endif; ?>
                      </span>
                    </a>
                  </article>
                <?php endforeach; ?>
              </div>
            </div>

            <button type="button" class="ud-hero-carousel__nav ud-hero-carousel__nav--next" data-ud-carousel-next aria-label="Pôle suivant">
              <span aria-hidden="true">›</span>
            </button>
          </div>

          <div class="ud-hero-carousel__dots" data-ud-carousel-dots role="tablist" aria-label="Choisir un pôle">
            <?php foreach ($orbitServices as $i => $s): ?>
              <button
                type="button"
                class="ud-hero-carousel__dot<?= $i === 0 ? ' is-active' : '' ?>"
                data-ud-carousel-dot="<?= (int)$i ?>"
                role="tab"
                aria-selected="<?= $i === 0 ? 'true' : 'false' ?>"
                aria-label="<?= h('Afficher ' . (string)($s['title'] ?? ('pôle ' . ($i + 1)))) ?>"
              ></button>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
</div>

<section class="ud-trust-affiche" aria-labelledby="ud-trust-affiche-title">
  <div class="container">
    <div class="ud-trust-affiche__banner">
      <p id="ud-trust-affiche-title" class="ud-trust-affiche__lead">Pourquoi nous faire confiance&nbsp;?</p>
      <ul class="ud-trust-affiche__grid">
        <li>
          <strong>Équipe expérimentée</strong>
          <span>À votre écoute, de Paris à votre pays d’origine.</span>
        </li>
        <li>
          <strong>Accompagnement A à Z</strong>
          <span>Un cadre clair, un suivi personnalisé.</span>
        </li>
        <li>
          <strong>Trois agences</strong>
          <span>Paris 18<sup>e</sup>, Paris 17<sup>e</sup> et Colombes.</span>
        </li>
        <li>
          <strong>Paiement flexible</strong>
          <span>Mensualités possibles selon les projets.</span>
        </li>
      </ul>
    </div>
  </div>
</section>

<section id="services" class="ud-services ud-services--premium py-5">
  <div class="container">
    <div class="ud-section-title text-center mb-4">
      <div class="ud-section-kicker">Catalogue · 13 services d’accompagnement</div>
      <h2 class="ud-title mb-2">Un cabinet, plusieurs expertises</h2>
      <div class="ud-subtitle">
        De la première question au projet abouti, chaque pôle structure votre démarche
        avec un interlocuteur dédié, un cadre clair et un réseau de partenaires qualifiés.
      </div>
      <div class="ud-section-divider mt-3" aria-hidden="true"></div>
    </div>

    <?php
      $voletsAll = is_file(__DIR__ . '/../data/service_volets.php')
          ? require __DIR__ . '/../data/service_volets.php'
          : [];

      $audienceMap = [
          'Entrepreneur' => ['creation-gestion-d-entreprises', 'assurances-credits', 'informatiques'],
          'Particulier' => ['services-a-la-personne', 'assistances-administratives', 'formations-emplois'],
      ];
      $audienceCounts = ['Tous' => count($services), 'Diaspora' => 0, 'Entrepreneur' => 0, 'Particulier' => 0];
      foreach ($services as $s) {
          $slug = (string)($s['slug'] ?? '');
          if (in_array($slug, $audienceMap['Entrepreneur'], true)) {
              $audienceCounts['Entrepreneur']++;
          } elseif (in_array($slug, $audienceMap['Particulier'], true)) {
              $audienceCounts['Particulier']++;
          } else {
              $audienceCounts['Diaspora']++;
          }
      }
    ?>

    <div class="ud-services-toolbar mb-4" role="tablist" aria-label="Filtrer par audience">
      <?php foreach ($audienceCounts as $label => $count): ?>
        <button
          type="button"
          class="ud-services-tab<?= $label === 'Tous' ? ' is-active' : '' ?>"
          role="tab"
          aria-selected="<?= $label === 'Tous' ? 'true' : 'false' ?>"
          data-filter="<?= h($label) ?>"
        >
          <span class="ud-services-tab__label"><?= h($label) ?></span>
          <span class="ud-services-tab__count" aria-hidden="true"><?= (int)$count ?></span>
        </button>
      <?php endforeach; ?>
    </div>

    <div class="ud-services-grid" id="udServicesGrid" role="list">
      <?php foreach ($services as $idx => $s): ?>
        <?php
          $href = $s['external_url'] ?? ($baseUrl . '/?page=' . urlencode((string)$s['slug']));
          $slug = (string)($s['slug'] ?? '');
          $audience = 'Diaspora';
          if (in_array($slug, $audienceMap['Entrepreneur'], true)) {
              $audience = 'Entrepreneur';
          } elseif (in_array($slug, $audienceMap['Particulier'], true)) {
              $audience = 'Particulier';
          }
          $voletsForService = $voletsAll[$slug] ?? [];
          $voletsToShow = array_slice($voletsForService, 0, 3);
          $voletsExtra = max(0, count($voletsForService) - count($voletsToShow));
          $isExternal = function_exists('ud_service_opens_new_tab') ? ud_service_opens_new_tab($s) : !empty($s['external_url']);
          $isSoon = !empty($s['coming_soon']);
        ?>
        <article
          class="ud-svc-card<?= $isSoon ? ' is-soon' : '' ?>"
          data-audience="<?= h($audience) ?>"
          role="listitem"
          style="--i:<?= (int)$idx ?>"
        >
          <span class="ud-svc-card__num" aria-hidden="true"><?= str_pad((string)((int)$idx + 1), 2, '0', STR_PAD_LEFT) ?></span>

          <header class="ud-svc-card__head">
            <span class="ud-svc-card__icon-wrap">
              <img <?= function_exists('service_icon_img_attrs')
                  ? service_icon_img_attrs((string)($s['icon'] ?? ''), $baseUrl, (string)($s['slug'] ?? ''), 88, 88, 'ud-svc-card__icon')
                  : 'class="ud-svc-card__icon" src="' . h(service_icon_url((string)($s['icon'] ?? ''), $baseUrl, (string)($s['slug'] ?? ''))) . '" alt="" width="88" height="88" loading="lazy"' ?>>
            </span>
            <div class="ud-svc-card__tags">
              <span class="ud-svc-card__tag ud-svc-card__tag--<?= h(strtolower($audience)) ?>"><?= h($audience) ?></span>
              <?php if ($isSoon): ?>
                <span class="ud-svc-card__tag ud-svc-card__tag--soon">Ouverture prochaine</span>
              <?php elseif ($isExternal): ?>
                <span class="ud-svc-card__tag ud-svc-card__tag--ext">Site dédié</span>
              <?php endif; ?>
            </div>
          </header>

          <h3 class="ud-svc-card__title"><?= h((string)$s['title']) ?></h3>
          <p class="ud-svc-card__desc"><?= h((string)($s['description'] ?? '')) ?></p>

          <?php if (!empty($voletsToShow)): ?>
            <ul class="ud-svc-card__volets" aria-label="Volets d’accompagnement">
              <?php foreach ($voletsToShow as $v): ?>
                <li><?= h((string)($v['label'] ?? '')) ?></li>
              <?php endforeach; ?>
              <?php if ($voletsExtra > 0): ?>
                <li class="ud-svc-card__volets-more">+ <?= (int)$voletsExtra ?> autre<?= $voletsExtra > 1 ? 's' : '' ?></li>
              <?php endif; ?>
            </ul>
          <?php endif; ?>

          <footer class="ud-svc-card__foot">
            <?php if ($isSoon): ?>
              <span class="ud-svc-card__cta is-disabled"><span>Bientôt disponible</span></span>
            <?php else: ?>
              <a
                class="ud-svc-card__cta"
                href="<?= h($href) ?>"
                <?= $isExternal ? 'target="_blank" rel="noopener noreferrer"' : '' ?>
              >
                <span><?= $isExternal ? 'Accéder au site' : 'Découvrir le pôle' ?></span>
                <span class="ud-svc-card__cta-arrow" aria-hidden="true">→</span>
              </a>
            <?php endif; ?>
          </footer>
        </article>
      <?php endforeach; ?>
    </div>

    <div class="ud-services-foot text-center mt-4 mt-lg-5">
      <p class="ud-services-foot__hint">
        Vous hésitez entre plusieurs pôles ? Décrivez votre besoin en une phrase, nous vous orientons.
      </p>
      <div class="d-flex flex-wrap justify-content-center gap-2">
        <a class="btn btn-primary ud-btn ud-btn--cta" href="<?= h($baseUrl) ?>/?page=demarrer-maintenant">Démarrer maintenant</a>
        <a class="btn btn-outline-primary ud-btn ud-btn--ghost ud-btn--on-dark" href="<?= h(ud_appointment_url($baseUrl)) ?>">Prendre rendez-vous</a>
        <a class="btn btn-outline-primary ud-btn ud-btn--ghost ud-btn--on-dark" href="<?= h($baseUrl) ?>/#contact">Nous écrire</a>
      </div>
    </div>
  </div>
</section>

<script>
(() => {
  const section = document.getElementById('services');
  const grid = document.getElementById('udServicesGrid');
  if (!section || !grid) return;

  const tabs = section.querySelectorAll('.ud-services-tab');
  const cards = grid.querySelectorAll('.ud-svc-card');

  const applyFilter = (audience) => {
    tabs.forEach((t) => {
      const active = t.dataset.filter === audience;
      t.classList.toggle('is-active', active);
      t.setAttribute('aria-selected', active ? 'true' : 'false');
    });
    cards.forEach((card) => {
      const show = audience === 'Tous' || card.dataset.audience === audience;
      card.classList.toggle('is-hidden', !show);
    });
  };

  tabs.forEach((t) => {
    t.addEventListener('click', () => applyFilter(t.dataset.filter || 'Tous'));
  });
})();
</script>

<section id="temoignages" class="ud-trust py-5 scroll-margin-top" aria-label="Témoignages">
  <div class="container">
    <?php
      $testimonials = testimonials_all();
      $testimonials = array_slice($testimonials, 0, 3);
    ?>
    <div class="ud-section-title text-center mb-4">
      <div class="ud-section-kicker">Témoignages</div>
      <h2 class="ud-title mb-2">Ils nous font confiance</h2>
      <div class="ud-subtitle mx-auto" style="max-width: 36rem;">
        Partagez votre expérience : votre commentaire est relu par notre équipe avant d’apparaître sur cette page.
      </div>
      <div class="ud-section-divider mt-3" aria-hidden="true"></div>
    </div>
    <div class="ud-trust-band mb-4">
      <span>Diaspora · Paris · Colombes</span>
      <span>Accompagnement humain</span>
      <span>Réponse rapide</span>
    </div>
    <div class="row g-3 mb-4 mb-lg-5">
      <?php foreach ($testimonials as $t): ?>
        <div class="col-12 col-md-6 col-xl-4">
          <article class="ud-testimonial h-100">
            <p class="ud-testimonial__text">“<?= h((string)($t['quote'] ?? '')) ?>”</p>
            <?php if (!empty($t['case_label']) && !empty($t['case_value'])): ?>
              <div class="ud-testimonial__case"><?= h((string)$t['case_label']) ?> : <?= h((string)$t['case_value']) ?></div>
            <?php endif; ?>
            <div class="ud-testimonial__meta">
              <?= h((string)($t['author'] ?? '')) ?><?= !empty($t['location']) ? ' · ' . h((string)$t['location']) : '' ?>
            </div>
          </article>
        </div>
      <?php endforeach; ?>
      <?php if (empty($testimonials)): ?>
        <div class="col-12">
          <article class="ud-testimonial h-100">
            <p class="ud-testimonial__text">Soyez le premier à laisser un témoignage ci-dessous.</p>
            <div class="ud-testimonial__meta">Univers Diaspora</div>
          </article>
        </div>
      <?php endif; ?>
    </div>

    <div class="row g-4 justify-content-center">
      <div class="col-12 col-lg-8">
        <div class="ud-testimonial-form p-3 p-md-4">
          <h3 class="h5 ud-testimonial-form__title mb-1">Laisser votre commentaire</h3>
          <p class="small text-muted mb-3">Votre texte ne sera pas publié immédiatement : nous le validons d’abord (sous 48&nbsp;h ouvrées en général).</p>
          <form class="ud-form ud-contact-form" method="post" action="<?= h($baseUrl) ?>/?action=testimonial-submit" novalidate>
            <div class="visually-hidden" aria-hidden="true">
              <label for="t-web">Ne pas remplir</label>
              <input type="text" name="website" id="t-web" tabindex="-1" autocomplete="off" value="">
            </div>
            <div class="mb-3">
              <label class="form-label" for="t-quote">Votre témoignage <span class="text-danger">*</span></label>
              <textarea class="form-control <?= isset($testimonialErrors['quote']) ? 'is-invalid' : '' ?>" id="t-quote" name="quote" rows="4" required minlength="20" maxlength="2000" placeholder="Qu’avez-vous apprécié dans l’accompagnement ? (20 caractères minimum)"><?= h((string)($testimonialOld['quote'] ?? '')) ?></textarea>
              <?php if (isset($testimonialErrors['quote'])): ?><div class="invalid-feedback"><?= h($testimonialErrors['quote']) ?></div><?php endif; ?>
            </div>
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label" for="t-author">Prénom ou pseudo affiché <span class="text-danger">*</span></label>
                <input class="form-control <?= isset($testimonialErrors['author']) ? 'is-invalid' : '' ?>" type="text" id="t-author" name="author" required maxlength="120" value="<?= h((string)($testimonialOld['author'] ?? '')) ?>" placeholder="Ex. Marie K.">
                <?php if (isset($testimonialErrors['author'])): ?><div class="invalid-feedback"><?= h($testimonialErrors['author']) ?></div><?php endif; ?>
              </div>
              <div class="col-md-6">
                <label class="form-label" for="t-location">Ville ou pays (facultatif)</label>
                <input class="form-control <?= isset($testimonialErrors['location']) ? 'is-invalid' : '' ?>" type="text" id="t-location" name="location" maxlength="120" value="<?= h((string)($testimonialOld['location'] ?? '')) ?>" placeholder="Ex. Paris 18">
                <?php if (isset($testimonialErrors['location'])): ?><div class="invalid-feedback"><?= h($testimonialErrors['location']) ?></div><?php endif; ?>
              </div>
              <div class="col-12">
                <label class="form-label" for="t-email">E-mail (facultatif, non affiché)</label>
                <input class="form-control <?= isset($testimonialErrors['email']) ? 'is-invalid' : '' ?>" type="email" id="t-email" name="email" value="<?= h((string)($testimonialOld['email'] ?? '')) ?>" placeholder="Pour vous recontacter si besoin">
                <?php if (isset($testimonialErrors['email'])): ?><div class="invalid-feedback"><?= h($testimonialErrors['email']) ?></div><?php endif; ?>
              </div>
              <div class="col-12">
                <div class="form-check">
                  <input class="form-check-input <?= isset($testimonialErrors['consent']) ? 'is-invalid' : '' ?>" type="checkbox" value="1" id="t-consent" name="consent" <?= !empty($testimonialOld['consent']) ? 'checked' : '' ?> required>
                  <label class="form-check-label" for="t-consent">
                    J’accepte que mon témoignage soit publié sur le site après validation, avec le prénom ou pseudo indiqué. *
                  </label>
                  <?php if (isset($testimonialErrors['consent'])): ?><div class="invalid-feedback d-block"><?= h($testimonialErrors['consent']) ?></div><?php endif; ?>
                </div>
                <div class="form-check mt-2">
                  <input class="form-check-input <?= isset($testimonialErrors['privacy']) ? 'is-invalid' : '' ?>" type="checkbox" value="1" id="t-privacy" name="privacy" <?= !empty($testimonialOld['privacy']) ? 'checked' : '' ?> required>
                  <label class="form-check-label" for="t-privacy">
                    J’ai lu la <a href="<?= h($baseUrl) ?>/?page=politique-confidentialite" target="_blank" rel="noopener noreferrer">politique de confidentialité</a>. *
                  </label>
                  <?php if (isset($testimonialErrors['privacy'])): ?><div class="invalid-feedback d-block"><?= h($testimonialErrors['privacy']) ?></div><?php endif; ?>
                </div>
              </div>
              <div class="col-12">
                <button class="btn btn-primary w-100 ud-btn ud-btn--shine" type="submit">
                  Envoyer mon témoignage <span class="ud-arrow" aria-hidden="true">→</span>
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>
<?php if (!empty($testimonialErrors)): ?>
<script>
(() => {
  const el = document.getElementById('temoignages');
  if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
})();
</script>
<?php endif; ?>

<section id="contact" class="ud-contact py-5">
  <div class="container">
    <div class="ud-contact-head mb-4">
      <div class="ud-section-title text-center">
        <div class="ud-section-kicker">Contact</div>
        <h2 class="ud-title mb-2">Parlons de votre projet</h2>
        <div class="ud-subtitle">Décrivez votre besoin, on vous répond rapidement.</div>
        <div class="ud-section-divider mt-3" aria-hidden="true"></div>
      </div>
    </div>

    <div class="row g-4">
      <div class="col-12 col-lg-7">
        <form class="ud-form ud-contact-form" method="post" action="<?= h($baseUrl) ?>/?action=contact">
          <div class="ud-form__head mb-3">
            <div class="ud-form__title">Envoyer un message</div>
            <div class="ud-form__subtitle">Nous vous recontactons dès que possible.</div>
          </div>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Nom</label>
              <input class="form-control <?= isset($errors['last_name']) ? 'is-invalid' : '' ?>" name="last_name" value="<?= h($old['last_name'] ?? '') ?>" required>
              <?php if (isset($errors['last_name'])): ?><div class="invalid-feedback"><?= h($errors['last_name']) ?></div><?php endif; ?>
            </div>
            <div class="col-md-6">
              <label class="form-label">Prénom</label>
              <input class="form-control <?= isset($errors['first_name']) ? 'is-invalid' : '' ?>" name="first_name" value="<?= h($old['first_name'] ?? '') ?>" required>
              <?php if (isset($errors['first_name'])): ?><div class="invalid-feedback"><?= h($errors['first_name']) ?></div><?php endif; ?>
            </div>
            <div class="col-md-6">
              <label class="form-label">Email</label>
              <input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" name="email" value="<?= h($old['email'] ?? '') ?>" required>
              <?php if (isset($errors['email'])): ?><div class="invalid-feedback"><?= h($errors['email']) ?></div><?php endif; ?>
            </div>
            <div class="col-md-6">
              <label class="form-label">Téléphone</label>
              <input class="form-control" name="phone" value="<?= h($old['phone'] ?? '') ?>">
            </div>
            <div class="col-12">
              <label class="form-label">Message</label>
              <textarea class="form-control <?= isset($errors['message']) ? 'is-invalid' : '' ?>" name="message" rows="4" required><?= h($old['message'] ?? '') ?></textarea>
              <?php if (isset($errors['message'])): ?><div class="invalid-feedback"><?= h($errors['message']) ?></div><?php endif; ?>
            </div>
            <div class="col-12">
              <div class="form-check">
                <input class="form-check-input <?= isset($errors['consent']) ? 'is-invalid' : '' ?>" type="checkbox" value="1" id="consent" name="consent" <?= !empty($old['consent']) ? 'checked' : '' ?> required>
                <label class="form-check-label" for="consent">
                  J’accepte que mes données soient traitées pour répondre à ma demande (base légale : intérêt légitime / exécution de mesures précontractuelles). *
                </label>
                <?php if (isset($errors['consent'])): ?><div class="invalid-feedback d-block"><?= h($errors['consent']) ?></div><?php endif; ?>
              </div>
              <div class="form-check mt-2">
                <input class="form-check-input <?= isset($errors['privacy']) ? 'is-invalid' : '' ?>" type="checkbox" value="1" id="privacy" name="privacy" <?= !empty($old['privacy']) ? 'checked' : '' ?> required>
                <label class="form-check-label" for="privacy">
                  J’ai lu la <a href="<?= h($baseUrl) ?>/?page=politique-confidentialite" target="_blank" rel="noopener noreferrer">politique de confidentialité</a>. *
                </label>
                <?php if (isset($errors['privacy'])): ?><div class="invalid-feedback d-block"><?= h($errors['privacy']) ?></div><?php endif; ?>
              </div>
            </div>
            <div class="col-12">
              <div class="ud-form-reassure mb-2">
                Réponse sous 24h ouvrées · Données traitées de manière confidentielle.
              </div>
              <button class="btn btn-primary w-100 ud-btn ud-btn--shine" type="submit">
                Envoyer mon message <span class="ud-arrow" aria-hidden="true">→</span>
              </button>
              <div class="ud-form-next mt-2">
                Prochaines étapes : 1) analyse de votre demande, 2) retour par e-mail/téléphone, 3) proposition de solution.
              </div>
            </div>
          </div>
        </form>
      </div>
      <div class="col-12 col-lg-5">
        <div class="ud-contact-side">
          <div class="ud-surface ud-contact-card mb-3">
            <div class="ud-contact-card__title">Ou directement</div>
            <div class="ud-contact-mini mb-2">
              <div class="ud-contact-mini__label">Email</div>
              <div class="ud-contact-mini__value"><a href="mailto:contact@universdiaspora.com">contact@universdiaspora.com</a></div>
            </div>
            <div class="ud-contact-mini mb-3">
              <div class="ud-contact-mini__label">Horaires</div>
              <div class="ud-contact-mini__value">Lun–Sam • 10:00–19:00</div>
            </div>

            <div class="ud-contact-card__subtitle">Nos bureaux</div>
            <?php
              $offices = require __DIR__ . '/../data/offices.php';
              if (!is_array($offices)) {
                  $offices = [];
              }
              foreach ($offices as &$officeRow) {
                  if (!is_array($officeRow)) {
                      continue;
                  }
                  $officeRow['maps_url'] = 'https://www.google.com/maps/search/?api=1&query='
                      . rawurlencode((string)($officeRow['lat'] ?? '') . ',' . (string)($officeRow['lon'] ?? ''));
                  $accessRows = is_array($officeRow['access'] ?? null) ? $officeRow['access'] : [];
                  foreach ($accessRows as &$accessRow) {
                      if (!is_array($accessRow)) {
                          continue;
                      }
                      $accessRow['badges'] = function_exists('ud_transit_line_badges')
                          ? ud_transit_line_badges((string)($accessRow['type'] ?? ''), (string)($accessRow['lines'] ?? ''))
                          : [];
                  }
                  unset($accessRow);
                  $officeRow['access'] = $accessRows;
              }
              unset($officeRow);
            ?>
            <div class="ud-locations">
              <?php foreach ($offices as $idx => $office): ?>
                <?php if (!is_array($office)) {
                    continue;
                } ?>
                <div class="ud-location" data-office-idx="<?= (int)$idx ?>">
                  <div class="ud-location__head">
                    <span class="ud-location__num" aria-hidden="true"><?= (int)$idx + 1 ?></span>
                    <strong class="ud-location__name"><?= h((string)($office['name'] ?? '')) ?></strong>
                  </div>
                  <a
                    class="ud-location__addr"
                    href="<?= h((string)($office['maps_url'] ?? '#')) ?>"
                    target="_blank"
                    rel="noopener noreferrer"
                    title="Ouvrir dans Google Maps"
                  >
                    <?= h((string)($office['address'] ?? '')) ?>
                    <span class="ud-location__maps" aria-hidden="true">Google Maps ↗</span>
                  </a>
                  <?php if (!empty($office['phone_fixe'])): ?>
                    <div class="ud-location__row"><span>Fixe</span><strong><?= h((string)$office['phone_fixe']) ?></strong></div>
                  <?php endif; ?>
                  <?php if (!empty($office['phone_mobile'])): ?>
                    <div class="ud-location__row"><span>Tél</span><strong><?= h((string)$office['phone_mobile']) ?></strong></div>
                  <?php endif; ?>
                  <?php
                    $access = is_array($office['access'] ?? null) ? $office['access'] : [];
                  ?>
                  <?php if ($access !== []): ?>
                    <div class="ud-location__access">
                      <div class="ud-location__access-title">Comment venir</div>
                      <ul class="ud-location__access-list">
                        <?php foreach ($access as $a): ?>
                          <?php if (!is_array($a)) {
                              continue;
                          } ?>
                          <li>
                            <span class="ud-location__access-type"><?= h((string)($a['type'] ?? '')) ?></span>
                            <span class="ud-location__access-lines" aria-label="Lignes">
                              <?php
                                $badges = is_array($a['badges'] ?? null) ? $a['badges'] : [];
                                if ($badges === [] && function_exists('ud_transit_line_badges')) {
                                    $badges = ud_transit_line_badges((string)($a['type'] ?? ''), (string)($a['lines'] ?? ''));
                                }
                              ?>
                              <?php foreach ($badges as $badge): ?>
                                <?php if (!is_array($badge)) {
                                    continue;
                                } ?>
                                <span class="ud-line-badge" style="background:<?= h((string)($badge['bg'] ?? '#1e3a6e')) ?>;color:<?= h((string)($badge['fg'] ?? '#fff')) ?>"><?= h((string)($badge['label'] ?? '')) ?></span>
                              <?php endforeach; ?>
                            </span>
                            <span class="ud-location__access-stops"><?= h((string)($a['stops'] ?? '')) ?></span>
                          </li>
                        <?php endforeach; ?>
                      </ul>
                    </div>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
            </div>
          </div>

          <div class="ud-surface ud-cta-box">
            <div class="ud-cta-box__title">Besoin d’un accompagnement rapide ?</div>
            <div class="ud-cta-box__text">Envoyez-nous un message : nous nous occupons du reste.</div>
            <a class="btn btn-primary ud-btn ud-btn--wide ud-btn--shine mt-3" href="<?= h($baseUrl) ?>/?page=demarrer-maintenant">
              Démarrer maintenant <span class="ud-arrow" aria-hidden="true">→</span>
            </a>
            <div class="ud-cta-box__hint mt-2">100% confidentiel — réponse rapide.</div>
          </div>
        </div>
      </div>
    </div>

    <div class="ud-surface ud-map-card mt-4">
      <div class="ud-map-card__head mb-2">
        <div class="ud-map-card__title">Carte des bureaux</div>
        <p class="ud-map-card__hint">Paris 18ᵉ, Paris 17ᵉ et Colombes — métro, Transilien et bus indiqués sous chaque adresse. Cliquez un numéro ou une adresse pour Google Maps.</p>
      </div>
      <?php
        $officesJson = htmlspecialchars(json_encode($offices, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
      ?>
      <div id="udMap" class="ud-map" data-offices="<?= $officesJson ?>" aria-label="Carte des bureaux Univers Diaspora"></div>
      <ol class="ud-map-legend" aria-label="Légende des bureaux">
        <?php foreach ($offices as $idx => $office): ?>
          <?php if (!is_array($office)) {
              continue;
          } ?>
          <li>
            <span class="ud-map-legend__num"><?= (int)$idx + 1 ?></span>
            <span class="ud-map-legend__label"><?= h((string)($office['name'] ?? '')) ?></span>
          </li>
        <?php endforeach; ?>
      </ol>
    </div>

    <div class="ud-surface ud-access-card mt-4">
      <div class="ud-access-card__title">Comment venir ?</div>
      <p class="ud-access-card__hint">Accès en transports en commun pour chaque agence.</p>
      <div class="ud-access-grid">
        <?php foreach ($offices as $office): ?>
          <?php
            if (!is_array($office)) {
                continue;
            }
            $access = is_array($office['access'] ?? null) ? $office['access'] : [];
            if ($access === []) {
                continue;
            }
          ?>
          <article class="ud-access-block">
            <h3 class="ud-access-block__name"><?= h((string)($office['short_name'] ?? $office['name'] ?? '')) ?></h3>
            <p class="ud-access-block__addr"><?= h((string)($office['address'] ?? '')) ?></p>
            <ul class="ud-access-block__list">
              <?php foreach ($access as $a): ?>
                <?php if (!is_array($a)) {
                    continue;
                } ?>
                <li>
                  <span class="ud-access-pill-row">
                    <span class="ud-access-kind"><?= h((string)($a['type'] ?? '')) ?></span>
                    <?php
                      $badges = is_array($a['badges'] ?? null) ? $a['badges'] : [];
                      if ($badges === [] && function_exists('ud_transit_line_badges')) {
                          $badges = ud_transit_line_badges((string)($a['type'] ?? ''), (string)($a['lines'] ?? ''));
                      }
                    ?>
                    <?php foreach ($badges as $badge): ?>
                      <?php if (!is_array($badge)) {
                          continue;
                      } ?>
                      <span class="ud-line-badge" style="background:<?= h((string)($badge['bg'] ?? '#1e3a6e')) ?>;color:<?= h((string)($badge['fg'] ?? '#fff')) ?>"><?= h((string)($badge['label'] ?? '')) ?></span>
                    <?php endforeach; ?>
                  </span>
                  <span class="ud-access-stop"><?= h((string)($a['stops'] ?? '')) ?></span>
                </li>
              <?php endforeach; ?>
            </ul>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>
<script src="<?= h(ud_public_asset_url('js/ud-hero-carousel.js', $baseUrl)) ?>?v=<?= (int) @filemtime(__DIR__ . '/../public/assets/js/ud-hero-carousel.js') ?>" defer></script>
<?php
$content = ob_get_clean();

$view = [
    'title' => 'Univers Diaspora — Faire de vos rêves une réalité',
    'meta_description' => 'Univers Diaspora : tous vos services réunis en un seul lieu. Conseil et accompagnement pour la diaspora à Paris 18ᵉ, Paris 17ᵉ et Colombes.',
    'active' => '',
    'content' => $content,
    'flash' => $flash ?? [],
    'errors' => $errors ?? [],
    'old' => $old ?? [],
];

require __DIR__ . '/_layout.php';

