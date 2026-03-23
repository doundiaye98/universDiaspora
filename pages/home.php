<?php
declare(strict_types=1);

$services = function_exists('services_all') ? services_all() : (require __DIR__ . '/../data/services.php');
$config = require __DIR__ . '/../config/config.php';
$baseUrl = rtrim($config['app']['base_url'], '/');

ob_start();
?>
<div class="ud-hero-wrap" style="--ud-hero-bg: url('<?= h($baseUrl) ?>/public/assets/img/arriereplan.png');">
<section class="ud-hero">
  <div class="container">
    <div class="row align-items-center g-4">
      <div class="col-12 col-lg-6">
        <div class="ud-hero__kicker">GROUPE YOMBAL</div>
        <h1 class="ud-hero__headline mb-3">Univers Diaspora</h1>
        <p class="ud-hero__tagline mb-4">
          <strong>L'agence UNIVERS DIASPORA vous conseille et vous accompagne pour la réalisation de vos projets d'ici et d'ailleurs !</strong>
        </p>
        <div class="d-flex flex-wrap gap-2">
          <a class="btn btn-primary ud-btn ud-btn--cta" href="#services">Découvrir nos services</a>
          <a class="btn btn-outline-primary ud-btn ud-btn--ghost" href="#contact">Nous contacter</a>
        </div>
      </div>
      <div class="col-12 col-lg-6 text-center">
        <img class="ud-hero__logo img-fluid" src="<?= h($baseUrl) ?>/public/assets/img/entete-univers-diasporas.png" alt="Univers Diaspora">
      </div>
    </div>
  </div>
</section>

<section class="ud-hero-images">
  <div class="container">
    <div class="row align-items-center g-4">
      <div class="col-12 col-lg-4 text-center">
        <img class="img-fluid" src="<?= h($baseUrl) ?>/public/assets/img/diasporas-bulles.png" alt="">
      </div>
      <div class="col-12 col-lg-8 text-center text-lg-end">
        <img class="img-fluid ud-hero-images__screen" src="<?= h($baseUrl) ?>/public/assets/img/diasporas-ordi-droit.jpg" alt="">
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

    <div class="ud-services__meta mb-4">
      <div class="row g-3 align-items-center">
        <div class="col-12 col-lg-7">
          <div class="row g-2 justify-content-center justify-content-lg-start ud-service-chips">
            <div class="col-auto"><span class="ud-chip">Rapidité</span></div>
            <div class="col-auto"><span class="ud-chip">Fiabilité</span></div>
            <div class="col-auto"><span class="ud-chip">Accompagnement</span></div>
            <div class="col-auto"><span class="ud-chip">Sur-mesure</span></div>
          </div>
        </div>
        <div class="col-12 col-lg-5">
          <div class="ud-stats">
            <div class="ud-stat">
              <div class="ud-stat__num"><?= count($services) ?></div>
              <div class="ud-stat__label">pôles</div>
            </div>
            <div class="ud-stat">
              <div class="ud-stat__num">24/7</div>
              <div class="ud-stat__label">support</div>
            </div>
            <div class="ud-stat">
              <div class="ud-stat__num">360°</div>
              <div class="ud-stat__label">solutions</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-4">
      <?php foreach ($services as $s): ?>
        <?php
          $href = $s['external_url'] ?? ($baseUrl . '/?page=' . urlencode($s['slug']));
        ?>
        <div class="col-12 col-md-6 col-lg-4">
          <div class="ud-service-card h-100 ud-reveal" data-reveal-index="<?= (int)($s['sort_order'] ?? 0) ?>">
            <div class="ud-service-card__top">
              <a class="ud-service-card__icon" href="<?= h($href) ?>" <?= isset($s['external_url']) ? 'target="_blank" rel="noopener noreferrer"' : '' ?> aria-label="<?= h($s['title']) ?>">
                <img src="<?= h($baseUrl) ?>/public/assets/img/<?= h($s['icon']) ?>" alt="<?= h($s['title']) ?>">
              </a>
              <?php if (!empty($s['coming_soon'])): ?>
                <span class="ud-pill ud-pill--soon">Bientôt</span>
              <?php endif; ?>
            </div>

            <div class="ud-service-card__body">
              <div class="ud-service-card__title"><?= h($s['title']) ?></div>
              <ul class="ud-service-card__list">
                <?php foreach ($s['bullets'] as $b): ?>
                  <li><?= h($b) ?></li>
                <?php endforeach; ?>
              </ul>
            </div>

            <div class="ud-service-card__footer">
              <?php if (!empty($s['coming_soon'])): ?>
                <div class="ud-badge">EN COURS DE CREATION</div>
              <?php else: ?>
                <a class="btn btn-primary ud-btn ud-btn--wide ud-btn--shine" href="<?= h($href) ?>" <?= isset($s['external_url']) ? 'target="_blank" rel="noopener noreferrer"' : '' ?>>
                  Découvrir <span class="ud-arrow" aria-hidden="true">→</span>
                </a>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
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
                  J’accepte que mes données ci-dessus communiquées et remplies soient transmises et utilisées *
                </label>
                <?php if (isset($errors['consent'])): ?><div class="invalid-feedback d-block"><?= h($errors['consent']) ?></div><?php endif; ?>
              </div>
              <div class="form-check mt-2">
                <input class="form-check-input <?= isset($errors['privacy']) ? 'is-invalid' : '' ?>" type="checkbox" value="1" id="privacy" name="privacy" <?= !empty($old['privacy']) ? 'checked' : '' ?> required>
                <label class="form-check-label" for="privacy">
                  Vous affirmez avoir pris connaissance de notre Politique de confidentialité. *
                </label>
                <?php if (isset($errors['privacy'])): ?><div class="invalid-feedback d-block"><?= h($errors['privacy']) ?></div><?php endif; ?>
              </div>
            </div>
            <div class="col-12">
              <button class="btn btn-primary w-100 ud-btn ud-btn--shine" type="submit">
                Envoyer mon message <span class="ud-arrow" aria-hidden="true">→</span>
              </button>
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
              <div class="ud-contact-mini__value">contact@universdiaspora.com</div>
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
            <div class="ud-cta-box__text">Clique et envoie-nous ton message, on s’occupe du reste.</div>
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
          <div class="small text-muted">Clique sur un marqueur pour voir l’adresse.</div>
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
<?php if (true): ?>
<script>
(() => {
  const cards = document.querySelectorAll('.ud-service-card.ud-reveal');
  if (!cards.length) return;

  const io = new IntersectionObserver((entries) => {
    entries.forEach((e) => {
      if (e.isIntersecting) {
        const el = e.target;
        el.classList.add('is-inview');
        io.unobserve(el);
      }
    });
  }, { threshold: 0.18 });

  cards.forEach((c) => io.observe(c));
})();
</script>
<?php endif; ?>
<?php
$content = ob_get_clean();

$view = [
    'title' => 'Univers Diaspora',
    'active' => '',
    'content' => $content,
    'flash' => $flash ?? [],
    'errors' => $errors ?? [],
    'old' => $old ?? [],
];

require __DIR__ . '/_layout.php';

