<?php
declare(strict_types=1);

$config = require __DIR__ . '/../config/config.php';
$baseUrl = rtrim($config['app']['base_url'], '/');

$offices = [
    '19, Rue Richomme, 75018 Paris' => '19, Rue Richomme, 75018 Paris',
    '75, Rue des Moines, 75017 Paris' => '75, Rue des Moines, 75017 Paris',
    '21, Rue Marcelin Berthelot, 92700 Colombes' => '21, Rue Marcelin Berthelot, 92700 Colombes',
];

$old = $old ?? [];
$errors = $errors ?? [];

ob_start();
?>
<section class="ud-appt-hero ud-page-rdv py-3 py-md-4 py-lg-5">
  <div class="container px-3 px-sm-4">
    <nav aria-label="Fil d’Ariane" class="ud-breadcrumb mb-3">
      <a href="<?= h($baseUrl) ?>/">Accueil</a>
      <span class="ud-breadcrumb__sep">/</span>
      <span>Rendez-vous</span>
    </nav>

    <div class="ud-surface ud-appt-card mb-4 mb-lg-5">
      <div class="ud-section-title text-center">
        <div class="ud-section-kicker">Univers Diaspora</div>
        <h1 class="ud-title mb-2">Prenez rendez‑vous</h1>
        <div class="ud-subtitle mx-auto" style="max-width: 36rem;">
          Choisissez l’un de nos trois bureaux, une date et une créneau horaire. Nous vous confirmons rapidement votre passage.
        </div>
        <div class="ud-section-divider mt-3" aria-hidden="true"></div>
      </div>
    </div>

    <div class="ud-about-statband row g-3 g-md-4 mb-0">
      <div class="col-12 col-md-4">
        <div class="ud-about-stat ud-surface h-100 text-center text-md-start">
          <div class="ud-about-stat__label">Paris 18ᵉ</div>
          <div class="ud-about-stat__value">Rue Richomme</div>
          <p class="ud-about-stat__hint mb-0">Accueil sur rendez-vous, au cœur du 18ᵉ arrondissement.</p>
        </div>
      </div>
      <div class="col-12 col-md-4">
        <div class="ud-about-stat ud-surface h-100 text-center text-md-start">
          <div class="ud-about-stat__label">Paris 17ᵉ</div>
          <div class="ud-about-stat__value">Rue des Moines</div>
          <p class="ud-about-stat__hint mb-0">Deuxième lieu parisien pour se rapprocher de vous.</p>
        </div>
      </div>
      <div class="col-12 col-md-4">
        <div class="ud-about-stat ud-surface h-100 text-center text-md-start">
          <div class="ud-about-stat__label">Colombes</div>
          <div class="ud-about-stat__value">92700</div>
          <p class="ud-about-stat__hint mb-0">Bureau en proche banlieue ouest, facile d’accès.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="ud-page-body py-5">
  <div class="container px-3 px-sm-4">
    <div class="row g-4">
      <div class="col-12 col-lg-7">
        <form class="ud-form ud-appt-form" method="post" action="<?= h($baseUrl) ?>/?action=appointment">
          <div class="ud-form__head mb-3">
            <div class="ud-form__title">Demande de rendez‑vous</div>
            <div class="ud-form__subtitle">Remplissez les informations ci‑dessous. Les champs marqués d’un astérisque sont obligatoires.</div>
          </div>

          <div class="row g-3">
            <div class="col-12">
              <label class="form-label">Bureau</label>
              <select class="form-select <?= isset($errors['office']) ? 'is-invalid' : '' ?>" name="office" required>
                <option value="">Choisir…</option>
                <?php foreach ($offices as $k => $label): ?>
                  <option value="<?= h($k) ?>" <?= (($old['office'] ?? '') === $k) ? 'selected' : '' ?>><?= h($label) ?></option>
                <?php endforeach; ?>
              </select>
              <?php if (isset($errors['office'])): ?><div class="invalid-feedback"><?= h($errors['office']) ?></div><?php endif; ?>
            </div>

            <div class="col-md-6">
              <label class="form-label">Date</label>
              <input type="date" class="form-control <?= isset($errors['date']) ? 'is-invalid' : '' ?>" name="date" value="<?= h($old['date'] ?? '') ?>" required>
              <?php if (isset($errors['date'])): ?><div class="invalid-feedback"><?= h($errors['date']) ?></div><?php endif; ?>
            </div>
            <div class="col-md-6">
              <label class="form-label">Heure</label>
              <input type="time" class="form-control <?= isset($errors['time']) ? 'is-invalid' : '' ?>" name="time" value="<?= h($old['time'] ?? '') ?>" required>
              <?php if (isset($errors['time'])): ?><div class="invalid-feedback"><?= h($errors['time']) ?></div><?php endif; ?>
            </div>

            <div class="col-md-6">
              <label class="form-label">Nom</label>
              <input class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" name="name" value="<?= h($old['name'] ?? '') ?>" required>
              <?php if (isset($errors['name'])): ?><div class="invalid-feedback"><?= h($errors['name']) ?></div><?php endif; ?>
            </div>
            <div class="col-md-6">
              <label class="form-label">Téléphone</label>
              <input class="form-control" name="phone" value="<?= h($old['phone'] ?? '') ?>">
            </div>
            <div class="col-12">
              <label class="form-label">Email</label>
              <input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" name="email" value="<?= h($old['email'] ?? '') ?>" required>
              <?php if (isset($errors['email'])): ?><div class="invalid-feedback"><?= h($errors['email']) ?></div><?php endif; ?>
            </div>
            <div class="col-12">
              <label class="form-label">Message (optionnel)</label>
              <textarea class="form-control" name="message" rows="4"><?= h($old['message'] ?? '') ?></textarea>
            </div>

            <div class="col-12 small text-muted">
              En envoyant ce formulaire, vous acceptez que vos données soient utilisées pour traiter votre demande de rendez-vous.
              Voir la <a href="<?= h($baseUrl) ?>/?page=politique-confidentialite">politique de confidentialité</a>.
            </div>

            <div class="ud-hp" aria-hidden="true">
              <label>Website</label>
              <input tabindex="-1" autocomplete="off" class="form-control" name="website" value="">
            </div>

            <div class="col-12">
              <button class="btn btn-primary w-100 ud-btn ud-btn--shine" type="submit">
                Envoyer ma demande <span class="ud-arrow" aria-hidden="true">→</span>
              </button>
            </div>
          </div>
        </form>
      </div>

      <div class="col-12 col-lg-5">
        <div class="ud-surface ud-cta-box mb-3">
          <div class="ud-cta-box__title">Après votre envoi</div>
          <div class="ud-cta-box__text">Nous vous contactons par téléphone ou e-mail pour confirmer le créneau et préparer votre rendez‑vous.</div>
          <div class="ud-cta-box__hint mt-2">Indiquez dans le message l’objet de votre venue (conseil, dossier administratif, etc.).</div>
          <a class="btn btn-outline-primary ud-btn ud-btn--wide ud-btn--ghost mt-3" href="<?= h($baseUrl) ?>/#contact">Écrire via le formulaire</a>
        </div>
        <div class="ud-surface">
          <div class="ud-contact-card__title mb-2">Bon à savoir</div>
          <ul class="ud-service-hero__list mb-0">
            <li>Prévoyez une pièce d’identité si un dossier officiel est à traiter sur place.</li>
            <li>Vous pouvez joindre des documents après notre premier échange par e-mail.</li>
            <li>Pour un premier contact rapide, le <a href="<?= h($baseUrl) ?>/?page=demarrer-maintenant">parcours « Démarrer maintenant »</a> est aussi disponible.</li>
          </ul>
        </div>
      </div>
    </div>

    <div class="ud-about-cta ud-surface text-center mt-4 mt-lg-5">
      <h2 class="ud-about-cta__title mb-2">Découvrir l’agence</h2>
      <p class="ud-about-cta__text mx-auto mb-4">
        Notre équipe, nos engagements et l’ensemble de nos services sont présentés sur le site.
      </p>
      <div class="d-flex flex-wrap justify-content-center gap-2">
        <a class="btn btn-primary ud-btn ud-btn--cta" href="<?= h($baseUrl) ?>/?page=equipe">Notre équipe</a>
        <a class="btn btn-outline-primary ud-btn ud-btn--ghost" href="<?= h($baseUrl) ?>/?page=apropos">À propos</a>
        <a class="btn btn-outline-primary ud-btn ud-btn--ghost" href="<?= h($baseUrl) ?>/#services">Services</a>
      </div>
    </div>
  </div>
</section>
<?php
$content = ob_get_clean();

$view = [
    'title' => 'Rendez-vous — Univers Diaspora',
    'meta_description' => 'Prenez rendez-vous avec Univers Diaspora : Paris 18ᵉ, Paris 17ᵉ ou Colombes — confirmation rapide et accompagnement personnalisé.',
    'active' => '',
    'content' => $content,
    'flash' => $flash ?? [],
    'errors' => $errors ?? [],
    'old' => $old ?? [],
];

require __DIR__ . '/_layout.php';
