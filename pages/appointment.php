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
<section class="ud-appt-hero">
  <div class="container">
    <nav aria-label="Fil d’ariane" class="ud-breadcrumb">
      <a href="<?= h($baseUrl) ?>/">Accueil</a>
      <span class="ud-breadcrumb__sep">/</span>
      <span>Rendez-vous</span>
    </nav>

    <div class="ud-surface ud-appt-card">
      <div class="ud-section-title text-center">
        <div class="ud-section-kicker">Rendez-vous</div>
        <h1 class="ud-title mb-2">Prenez rendez‑vous</h1>
        <div class="ud-subtitle">Choisissez un bureau, une date et une heure. Nous confirmons rapidement.</div>
        <div class="ud-section-divider mt-3" aria-hidden="true"></div>
      </div>
    </div>
  </div>
</section>

<section class="py-5">
  <div class="container">
    <div class="row g-4">
      <div class="col-12 col-lg-7">
        <form class="ud-form ud-appt-form" method="post" action="<?= h($baseUrl) ?>/?action=appointment">
          <div class="ud-form__head mb-3">
            <div class="ud-form__title">Demande de rendez‑vous</div>
            <div class="ud-form__subtitle">Remplissez les informations ci‑dessous.</div>
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
        <div class="ud-surface ud-cta-box">
          <div class="ud-cta-box__title">Informations</div>
          <div class="ud-cta-box__text">Après envoi, nous vous contactons pour confirmer le rendez‑vous.</div>
          <div class="ud-cta-box__hint mt-2">Astuce: indiquez l’objet de votre demande dans le message.</div>
          <a class="btn btn-outline-primary ud-btn ud-btn--wide ud-btn--ghost mt-3" href="<?= h($baseUrl) ?>/#contact">Ou nous écrire</a>
        </div>
      </div>
    </div>
  </div>
</section>
<?php
$content = ob_get_clean();

$view = [
    'title' => 'Rendez-vous — Univers Diaspora',
    'meta_description' => 'Prenez rendez-vous avec Univers Diaspora : choix du bureau, date et coordonnées pour une prise en charge rapide.',
    'active' => '',
    'content' => $content,
    'flash' => $flash ?? [],
    'errors' => $errors ?? [],
    'old' => $old ?? [],
];

require __DIR__ . '/_layout.php';

