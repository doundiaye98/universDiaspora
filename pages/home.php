<?php
declare(strict_types=1);

$services = function_exists('services_all') ? services_all() : (require __DIR__ . '/../data/services.php');
$config = require __DIR__ . '/../config/config.php';
$baseUrl = rtrim($config['app']['base_url'], '/');

ob_start();
?>
<div class="ud-hero-wrap ud-hero-wrap--video-clean">
<section class="ud-hero ud-hero--video-clean">
  <div class="container">
    <div class="row align-items-center g-4 g-xl-5">
      <div class="col-12 col-xl-8">
        <div class="ud-hero__badge">Univers Diaspora · Depuis Mars 2024</div>
        <h1 class="ud-hero__headline ud-hero__headline--video mb-3">
          <span class="ud-hero__brand">Faire de vos rêves</span><br>une réalité.
        </h1>
        <p class="ud-hero__tagline ud-hero__tagline--video mb-4">
          Conseil, accompagnement et solutions concrètes pour la diaspora.
          Une équipe engagée pour transformer vos projets en résultats.
        </p>
        <div class="d-flex flex-wrap gap-2 mb-3">
          <a class="btn btn-primary ud-btn ud-btn--cta" href="<?= h($baseUrl) ?>/?page=rendez-vous">Prendre rendez-vous</a>
          <a class="btn btn-outline-light ud-btn ud-btn--ghost ud-btn--on-dark" href="#services">Découvrir nos services</a>
        </div>
        <div class="ud-hero__chips">
          <span class="ud-hero__chip">12 pôles de services</span>
          <span class="ud-hero__chip">3 bureaux</span>
          <span class="ud-hero__chip">Suivi personnalisé</span>
        </div>
      </div>
      <div class="col-12 col-xl-4">
        <div class="ud-hero__office-cards">
          <div class="ud-hero__office-card">
            <div class="ud-hero__office-city">Paris 18</div>
            <div class="ud-hero__office-name">Rue Richomme</div>
          </div>
          <div class="ud-hero__office-card">
            <div class="ud-hero__office-city">Paris 17</div>
            <div class="ud-hero__office-name">Rue des Moines</div>
          </div>
          <div class="ud-hero__office-card">
            <div class="ud-hero__office-city">Colombes</div>
            <div class="ud-hero__office-name">92700</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
</div>

<section id="services" class="ud-services py-5">
  <div class="container">
    <div class="ud-section-title text-center mb-4">
      <div class="ud-section-kicker">Découvrir nos services</div>
      <h2 class="ud-title mb-2">Tout pour réussir vos projets</h2>
      <div class="ud-subtitle">Conseils, accompagnement et solutions concrètes — de l’idée à la réalisation.</div>
      <div class="ud-section-divider mt-3" aria-hidden="true"></div>
    </div>

    <div class="row g-3 ud-services-grid-simple">
      <?php foreach ($services as $s): ?>
        <?php $href = $s['external_url'] ?? ($baseUrl . '/?page=' . urlencode((string)$s['slug'])); ?>
        <?php
          $slug = (string)($s['slug'] ?? '');
          $target = 'Diaspora';
          if (in_array($slug, ['creation-gestion-d-entreprises', 'assurances-credits', 'informatiques'], true)) {
              $target = 'Entrepreneur';
          } elseif (in_array($slug, ['services-a-la-personne', 'assistances-administratives', 'formations-emplois'], true)) {
              $target = 'Particulier';
          }
        ?>
        <div class="col-12 col-md-6 col-xl-4">
          <article class="ud-service-simple h-100<?= !empty($s['coming_soon']) ? ' is-soon' : '' ?>">
            <div class="ud-service-simple__top">
              <img class="ud-service-simple__icon" src="<?= h(service_icon_url((string)($s['icon'] ?? ''), $baseUrl, (string)($s['slug'] ?? ''))) ?>" alt="" width="48" height="48" loading="lazy">
              <?php if (!empty($s['coming_soon'])): ?><span class="ud-pill ud-pill--soon">Bientôt</span><?php endif; ?>
            </div>
            <h3 class="ud-service-simple__title"><?= h((string)$s['title']) ?></h3>
            <p class="ud-service-simple__desc"><?= h((string)($s['description'] ?? '')) ?></p>
            <div class="ud-service-simple__audience">Pour qui : <?= h($target) ?></div>
            <?php if (empty($s['coming_soon'])): ?>
              <a class="btn btn-primary ud-btn ud-btn--shine ud-service-simple__cta" href="<?= h($href) ?>" <?= isset($s['external_url']) ? 'target="_blank" rel="noopener noreferrer"' : '' ?>>
                Découvrir <span class="ud-arrow" aria-hidden="true">→</span>
              </a>
            <?php else: ?>
              <span class="ud-badge">En cours de création</span>
            <?php endif; ?>
          </article>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="ud-trust py-5" aria-label="Preuves de confiance">
  <div class="container">
    <?php
      $testimonials = testimonials_all();
      $testimonials = array_slice($testimonials, 0, 3);
    ?>
    <div class="ud-trust-band mb-4">
      <span>Ils nous font confiance</span>
      <span>Diaspora · Paris · Colombes</span>
      <span>Accompagnement humain</span>
      <span>Réponse rapide</span>
    </div>
    <div class="row g-3">
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
            <p class="ud-testimonial__text">“Votre témoignage ici prochainement.”</p>
            <div class="ud-testimonial__meta">Univers Diaspora</div>
          </article>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>

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
              <div class="ud-contact-mini__value">Lun–Sam • 09:00–18:00</div>
            </div>

            <div class="ud-contact-card__subtitle">Nos bureaux</div>
            <div class="ud-locations">
              <div class="ud-location">
                <div class="ud-location__addr">19, Rue Richomme, 75018 Paris</div>
                <div class="ud-location__row"><span>Fixe</span><strong>09 70 70 70 59</strong></div>
                <div class="ud-location__row"><span>Portable</span><strong>06 23 63 58 66</strong></div>
              </div>
              <div class="ud-location">
                <div class="ud-location__addr">75, Rue des Moines, 75017 Paris</div>
                <div class="ud-location__row"><span>Fixe</span><strong>01 42 29 41 44</strong></div>
                <div class="ud-location__row"><span>Portable</span><strong>06 59 40 89 56</strong></div>
              </div>
              <div class="ud-location">
                <div class="ud-location__addr">21, Rue M. Berteaux, 92700 Colombes</div>
                <div class="ud-location__row"><span>Fixe</span><strong>09 70 70 70 46</strong></div>
                <div class="ud-location__row"><span>Portable</span><strong>06 31 27 33 76</strong></div>
              </div>
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
      <div class="d-flex align-items-end justify-content-between flex-wrap gap-2 mb-2">
        <div>
          <div class="ud-contact-card__title mb-0">Carte des bureaux</div>
          <div class="small text-muted">Utilise l’icône des calques (en haut à droite) pour passer entre plan, carte OSM et vue aérienne. Clique sur un marqueur pour l’adresse.</div>
        </div>
      </div>
      <?php
        $offices = [
          [
            'name' => 'Paris 18 — Rue Richomme',
            'lat' => 48.8861978,
            'lon' => 2.3511478,
            'address' => '19, Rue Richomme, 75018 Paris',
            'phone' => 'Fixe: 09 70 70 70 59 • Portable: 06 23 63 58 66',
          ],
          [
            'name' => 'Paris 17 — Rue des Moines',
            'lat' => 48.8913495,
            'lon' => 2.3215895,
            'address' => '75, Rue des Moines, 75017 Paris',
            'phone' => 'Fixe: 01 42 29 41 44 • Portable: 06 59 40 89 56',
          ],
          [
            'name' => 'Colombes — Rue Marcelin Berthelot',
            'lat' => 48.9289708,
            'lon' => 2.2571219,
            'address' => '21, Rue Marcelin Berthelot, 92700 Colombes',
            'phone' => 'Fixe: 09 70 70 70 46 • Portable: 06 31 27 33 76',
          ],
        ];
        $officesJson = htmlspecialchars(json_encode($offices, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
      ?>
      <div id="udMap" class="ud-map" data-offices="<?= $officesJson ?>" aria-label="Carte des bureaux Univers Diaspora"></div>
    </div>
  </div>
</section>
<?php
$content = ob_get_clean();

$view = [
    'title' => 'Univers Diaspora — Accueil',
    'meta_description' => 'Univers Diaspora vous conseille et vous accompagne pour vos projets : services, contact et rendez-vous.',
    'active' => '',
    'content' => $content,
    'flash' => $flash ?? [],
    'errors' => $errors ?? [],
    'old' => $old ?? [],
];

require __DIR__ . '/_layout.php';

