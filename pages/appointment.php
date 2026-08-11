<?php
declare(strict_types=1);

$config = require __DIR__ . '/../config/config.php';
$baseUrl = ud_site_base_url();

$officesData = require __DIR__ . '/../data/offices.php';
$offices = [];
if (is_array($officesData)) {
    foreach ($officesData as $o) {
        if (!is_array($o)) {
            continue;
        }
        $addr = (string)($o['address'] ?? '');
        if ($addr === '') {
            continue;
        }
        $offices[$addr] = $addr;
    }
}

$old = $old ?? [];
$errors = $errors ?? [];

/* Pré-remplissage depuis ?service=<slug>&volet=<id> (depuis pages/service.php) */
$contextServiceSlug = (string)($old['service_slug'] ?? '');
$contextVoletId = (string)($old['volet_id'] ?? '');
$contextServiceTitle = '';
$contextVoletLabel = '';
$autoMessageEnabled = false;

$serviceSlugIn = $contextServiceSlug !== ''
    ? $contextServiceSlug
    : (isset($_GET['service']) ? (string)$_GET['service'] : '');
$voletIdIn = $contextVoletId !== ''
    ? $contextVoletId
    : (isset($_GET['volet']) ? (string)$_GET['volet'] : '');

$apptCtx = appointment_service_context($serviceSlugIn, $voletIdIn);
if ($apptCtx !== null) {
    $contextServiceSlug = $apptCtx['service_slug'];
    $contextVoletId = $apptCtx['volet_id'];
    $contextServiceTitle = $apptCtx['service_title'];
    $contextVoletLabel = $apptCtx['volet_label'];
    $autoMessageEnabled = true;
    if (trim((string)($old['message'] ?? '')) === '') {
        $old['message'] = appointment_build_message(
            $contextServiceTitle,
            $contextVoletLabel,
            (string)($old['office'] ?? ''),
            (string)($old['date'] ?? ''),
            (string)($old['time'] ?? '')
        );
    }
} else {
    $contextServiceSlug = '';
    $contextVoletId = '';
}

$servicesList = function_exists('services_all') ? services_all() : [];
$voletsAll = is_file(__DIR__ . '/../data/service_volets.php')
    ? require __DIR__ . '/../data/service_volets.php'
    : [];
$skipServiceStep = $contextServiceSlug !== '';

$wizardInitialStep = $skipServiceStep ? 'office' : 'service';
if (!empty($errors)) {
    if (isset($errors['office'])) {
        $wizardInitialStep = 'office';
    } elseif (isset($errors['date']) || isset($errors['time'])) {
        $wizardInitialStep = 'slot';
    } elseif (isset($errors['name']) || isset($errors['email'])) {
        $wizardInitialStep = 'contact';
    } else {
        $wizardInitialStep = 'confirm';
    }
}

$voletsForJs = [];
foreach ($voletsAll as $slug => $volets) {
    if (!is_array($volets)) {
        continue;
    }
    $voletsForJs[$slug] = array_map(static function (array $v): array {
        return [
            'id' => (string)($v['id'] ?? ''),
            'label' => (string)($v['label'] ?? ''),
        ];
    }, $volets);
}

ob_start();
?>
<section class="ud-appt-hero ud-page-rdv py-3 py-md-4">
  <div class="container px-3 px-sm-4">
    <nav aria-label="Fil d’Ariane" class="ud-breadcrumb mb-3">
      <a href="<?= h($baseUrl) ?>/">Accueil</a>
      <span class="ud-breadcrumb__sep">/</span>
      <span>Rendez-vous</span>
    </nav>
  </div>
</section>

<section class="ud-page-body pb-5">
  <div class="container px-3 px-sm-4">
    <div
      class="ud-rdv-wizard ud-surface"
      data-skip-service="<?= $skipServiceStep ? '1' : '0' ?>"
      data-initial-step="<?= h($wizardInitialStep) ?>"
      data-service-title="<?= h($contextServiceTitle) ?>"
      data-volet-label="<?= h($contextVoletLabel) ?>"
      data-volet-id="<?= h($contextVoletId) ?>"
      data-volets="<?= h(json_encode($voletsForJs, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS)) ?>"
    >
      <nav class="ud-rdv-wizard__steps" aria-label="Étapes du rendez-vous">
        <button type="button" class="ud-rdv-wizard__step<?= $skipServiceStep ? ' is-done' : ($wizardInitialStep === 'service' ? ' is-active' : '') ?>" data-rdv-step="service" aria-selected="<?= $wizardInitialStep === 'service' ? 'true' : 'false' ?>">Mon besoin</button>
        <button type="button" class="ud-rdv-wizard__step<?= $wizardInitialStep === 'office' ? ' is-active' : '' ?>" data-rdv-step="office" aria-selected="<?= $wizardInitialStep === 'office' ? 'true' : 'false' ?>">Bureau</button>
        <button type="button" class="ud-rdv-wizard__step<?= $wizardInitialStep === 'slot' ? ' is-active' : '' ?>" data-rdv-step="slot" aria-selected="<?= $wizardInitialStep === 'slot' ? 'true' : 'false' ?>">Créneau</button>
        <button type="button" class="ud-rdv-wizard__step<?= $wizardInitialStep === 'contact' ? ' is-active' : '' ?>" data-rdv-step="contact" aria-selected="<?= $wizardInitialStep === 'contact' ? 'true' : 'false' ?>">Coordonnées</button>
        <button type="button" class="ud-rdv-wizard__step<?= $wizardInitialStep === 'confirm' ? ' is-active' : '' ?>" data-rdv-step="confirm" aria-selected="<?= $wizardInitialStep === 'confirm' ? 'true' : 'false' ?>">Confirmation</button>
      </nav>

      <?php if ($skipServiceStep && $contextServiceTitle !== ''): ?>
        <div class="ud-rdv-wizard__context">
          <span class="ud-rdv-wizard__context-label">Rendez-vous pour</span>
          <strong><?= h($contextServiceTitle) ?></strong>
          <?php if ($contextVoletLabel !== ''): ?>
            <span class="ud-rdv-wizard__context-volet">— <?= h($contextVoletLabel) ?></span>
          <?php endif; ?>
        </div>
      <?php endif; ?>

      <form class="ud-form ud-appt-form ud-rdv-wizard__form" method="post" action="<?= h($baseUrl) ?>/?action=appointment" novalidate data-auto-message="1">
        <input type="hidden" name="service_slug" value="<?= h($contextServiceSlug) ?>">
        <input type="hidden" name="volet_id" value="<?= h($contextVoletId) ?>">

        <div class="ud-hp" aria-hidden="true">
          <label>Website</label>
          <input tabindex="-1" autocomplete="off" name="website" value="">
        </div>

        <?php if (!$skipServiceStep): ?>
        <!-- Étape 1 : service -->
        <div class="ud-rdv-wizard__panel<?= $wizardInitialStep === 'service' ? ' is-active' : '' ?>" data-rdv-panel="service"<?= $wizardInitialStep !== 'service' ? ' hidden' : '' ?>>
          <p class="ud-rdv-wizard__kicker">Étape 1 sur 5</p>
          <h2 class="ud-rdv-wizard__title">Pour quel service souhaitez-vous un rendez-vous&nbsp;?</h2>
          <p class="ud-rdv-wizard__lead">Choisissez le pôle concerné. Vous pourrez préciser un volet d’accompagnement si besoin.</p>
          <div class="mb-3">
            <label class="form-label visually-hidden" for="udRdvService">Service</label>
            <select class="form-select form-select-lg" id="udRdvService" required>
              <option value="">Choisir un service…</option>
              <?php foreach ($servicesList as $s): ?>
                <?php $slug = (string)($s['slug'] ?? ''); if ($slug === '') continue; ?>
                <option value="<?= h($slug) ?>"<?= (($old['service_slug'] ?? '') === $slug) ? ' selected' : '' ?>><?= h((string)($s['title'] ?? $slug)) ?></option>
              <?php endforeach; ?>
              <option value="__general__"<?= (($old['service_slug'] ?? '') === '') ? ' selected' : '' ?>>Besoin général / je ne sais pas encore</option>
            </select>
          </div>
          <div id="udRdvVoletWrap" class="mb-4" hidden>
            <label class="form-label" for="udRdvVolet">Volet (facultatif)</label>
            <select class="form-select" id="udRdvVolet"></select>
          </div>
          <button type="button" class="btn btn-primary ud-btn ud-rdv-wizard__cta w-100" data-rdv-next>Confirmer</button>
        </div>
        <?php endif; ?>

        <!-- Étape 2 : bureau -->
        <div class="ud-rdv-wizard__panel<?= $wizardInitialStep === 'office' ? ' is-active' : '' ?>" data-rdv-panel="office"<?= $wizardInitialStep !== 'office' ? ' hidden' : '' ?>>
          <button type="button" class="ud-rdv-wizard__back" data-rdv-back<?= $skipServiceStep ? ' hidden' : '' ?>>‹ Étape précédente</button>
          <p class="ud-rdv-wizard__kicker">Étape 2 sur 5</p>
          <h2 class="ud-rdv-wizard__title">Dans quel bureau souhaitez-vous nous rencontrer&nbsp;?</h2>
          <div class="ud-rdv-office-list mb-4">
            <?php foreach ($offices as $k => $label): ?>
              <label class="ud-rdv-office-option">
                <input type="radio" name="office" value="<?= h($k) ?>"<?= (($old['office'] ?? '') === $k) ? ' checked' : '' ?> required>
                <span class="ud-rdv-office-option__box">
                  <span class="ud-rdv-office-option__title"><?= h($label) ?></span>
                </span>
              </label>
            <?php endforeach; ?>
          </div>
          <?php if (isset($errors['office'])): ?><div class="text-danger small mb-3" data-server="1"><?= h($errors['office']) ?></div><?php endif; ?>
          <button type="button" class="btn btn-primary ud-btn ud-rdv-wizard__cta w-100" data-rdv-next>Confirmer</button>
        </div>

        <!-- Étape 3 : créneau -->
        <div class="ud-rdv-wizard__panel<?= $wizardInitialStep === 'slot' ? ' is-active' : '' ?>" data-rdv-panel="slot"<?= $wizardInitialStep !== 'slot' ? ' hidden' : '' ?>>
          <button type="button" class="ud-rdv-wizard__back" data-rdv-back>‹ Étape précédente</button>
          <p class="ud-rdv-wizard__kicker">Étape 3 sur 5</p>
          <h2 class="ud-rdv-wizard__title">Quel créneau vous convient&nbsp;?</h2>
          <p class="ud-rdv-wizard__lead">Nous vous recontactons pour confirmer la disponibilité exacte.</p>
          <div class="row g-3 mb-4">
            <div class="col-md-6">
              <label class="form-label" for="udRdvDate">Date</label>
              <input type="date" class="form-control form-control-lg<?= isset($errors['date']) ? ' is-invalid' : '' ?>" name="date" id="udRdvDate" value="<?= h($old['date'] ?? '') ?>" required>
              <?php if (isset($errors['date'])): ?><div class="invalid-feedback" data-server="1"><?= h($errors['date']) ?></div><?php endif; ?>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="udRdvTime">Heure</label>
              <input type="time" class="form-control form-control-lg<?= isset($errors['time']) ? ' is-invalid' : '' ?>" name="time" id="udRdvTime" value="<?= h($old['time'] ?? '') ?>" required>
              <?php if (isset($errors['time'])): ?><div class="invalid-feedback" data-server="1"><?= h($errors['time']) ?></div><?php endif; ?>
            </div>
          </div>
          <button type="button" class="btn btn-primary ud-btn ud-rdv-wizard__cta w-100" data-rdv-next>Confirmer</button>
        </div>

        <!-- Étape 4 : coordonnées -->
        <div class="ud-rdv-wizard__panel<?= $wizardInitialStep === 'contact' ? ' is-active' : '' ?>" data-rdv-panel="contact"<?= $wizardInitialStep !== 'contact' ? ' hidden' : '' ?>>
          <button type="button" class="ud-rdv-wizard__back" data-rdv-back>‹ Étape précédente</button>
          <p class="ud-rdv-wizard__kicker">Étape 4 sur 5</p>
          <h2 class="ud-rdv-wizard__title">Comment pouvons-nous vous joindre&nbsp;?</h2>
          <div class="row g-3 mb-4">
            <div class="col-12">
              <label class="form-label" for="udRdvName">Nom et prénom</label>
              <input class="form-control form-control-lg<?= isset($errors['name']) ? ' is-invalid' : '' ?>" name="name" id="udRdvName" value="<?= h($old['name'] ?? '') ?>" required autocomplete="name">
              <?php if (isset($errors['name'])): ?><div class="invalid-feedback" data-server="1"><?= h($errors['name']) ?></div><?php endif; ?>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="udRdvEmail">E-mail</label>
              <input type="email" class="form-control form-control-lg<?= isset($errors['email']) ? ' is-invalid' : '' ?>" name="email" id="udRdvEmail" value="<?= h($old['email'] ?? '') ?>" required autocomplete="email">
              <?php if (isset($errors['email'])): ?><div class="invalid-feedback" data-server="1"><?= h($errors['email']) ?></div><?php endif; ?>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="udRdvPhone">Téléphone</label>
              <input type="tel" class="form-control form-control-lg" name="phone" id="udRdvPhone" value="<?= h($old['phone'] ?? '') ?>" autocomplete="tel">
            </div>
          </div>
          <button type="button" class="btn btn-primary ud-btn ud-rdv-wizard__cta w-100" data-rdv-next>Confirmer</button>
        </div>

        <!-- Étape 5 : confirmation -->
        <div class="ud-rdv-wizard__panel<?= $wizardInitialStep === 'confirm' ? ' is-active' : '' ?>" data-rdv-panel="confirm"<?= $wizardInitialStep !== 'confirm' ? ' hidden' : '' ?>>
          <button type="button" class="ud-rdv-wizard__back" data-rdv-back>‹ Étape précédente</button>
          <p class="ud-rdv-wizard__kicker">Étape 5 sur 5</p>
          <h2 class="ud-rdv-wizard__title">Vérifiez votre demande</h2>
          <div id="udRdvSummary" class="ud-rdv-summary mb-4" aria-live="polite"></div>
          <div class="mb-4">
            <label class="form-label" for="udRdvMessage">Message</label>
            <div class="form-text mb-1">Généré automatiquement si un service est indiqué ; vous pouvez le modifier.</div>
            <textarea class="form-control" name="message" id="udRdvMessage" rows="6" data-auto-message-field="1"><?= h($old['message'] ?? '') ?></textarea>
          </div>
          <p class="small text-muted mb-3">
            En envoyant ce formulaire, vous acceptez que vos données soient utilisées pour traiter votre demande.
            <a href="<?= h($baseUrl) ?>/?page=politique-confidentialite">Politique de confidentialité</a>.
          </p>
          <button class="btn btn-primary w-100 ud-btn ud-btn--shine ud-rdv-wizard__cta" type="submit">
            Envoyer ma demande <span class="ud-arrow" aria-hidden="true">→</span>
          </button>
        </div>
      </form>
    </div>

    <aside class="ud-rdv-footnote" aria-label="Informations pratiques">
      <p class="ud-rdv-footnote__places">Bureaux : Paris 18<sup>e</sup>, Paris 17<sup>e</sup> et Colombes</p>
      <p class="ud-rdv-footnote__delay">Confirmation par e-mail ou téléphone sous 24 à 48 h ouvrées.</p>
    </aside>
  </div>
</section>
<script src="<?= h(ud_public_asset_url('js/appointment-wizard.js', $baseUrl)) ?>?v=<?= (int) @filemtime(__DIR__ . '/../public/assets/js/appointment-wizard.js') ?>"></script>
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
