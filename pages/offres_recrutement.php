<?php
declare(strict_types=1);

if (!function_exists('announcements_all')) {
    require_once __DIR__ . '/../app/announcements.php';
}

$config = require __DIR__ . '/../config/config.php';
$baseUrl = function_exists('ud_site_base_url')
    ? ud_site_base_url()
    : rtrim((string)($config['app']['base_url'] ?? ''), '/');
$rhContact = ud_offres_recrutement_contact($config);

$offres = [];
$recrutements = [];
try {
    $offres = announcements_all(null, true, 'offre');
    $recrutements = announcements_all(null, true, 'recrutement');
} catch (Throwable $e) {
    error_log('[offres_recrutement] ' . $e->getMessage());
}

$nbOffres = count($offres);
$nbRecrutements = count($recrutements);

$jobErrors = $_SESSION['job_apply_errors'] ?? [];
$jobOld = $_SESSION['job_apply_old'] ?? [];
unset($_SESSION['job_apply_errors'], $_SESSION['job_apply_old']);

$applyFocus = (int)($_GET['apply'] ?? 0);
if (!empty($jobErrors) && $applyFocus === 0) {
    $applyFocus = (int)($jobOld['announcement_id'] ?? 0);
}

$defaultTab = ($applyFocus > 0 || (isset($_GET['rubrique']) && $_GET['rubrique'] === 'recrutement')) ? 'job' : 'partner';
$applyJobTitle = '';
if ($applyFocus > 0) {
    foreach ($recrutements as $row) {
        if ((int)($row['id'] ?? 0) === $applyFocus) {
            $applyJobTitle = (string)($row['title'] ?? '');
            break;
        }
    }
}

ob_start();
?>
<section class="ud-appt-hero ud-page-offres py-3 py-md-4 py-lg-5">
  <div class="container px-3 px-sm-4">
    <nav aria-label="Fil d’Ariane" class="ud-breadcrumb mb-3">
      <a href="<?= h($baseUrl) ?>/">Accueil</a>
      <span class="ud-breadcrumb__sep">/</span>
      <span>Offres &amp; recrutement</span>
    </nav>

    <div class="ud-surface ud-appt-card mb-4 mb-lg-5">
      <div class="ud-section-title text-center">
        <div class="ud-section-kicker">Univers Diaspora</div>
        <h1 class="ud-title mb-2">Offres &amp; recrutement</h1>
        <div class="ud-subtitle mx-auto" style="max-width: 38rem;">
          Partenariats et opportunités business d’un côté, postes ouverts pour rejoindre notre équipe de l’autre.
        </div>
        <div class="ud-section-divider mt-3" aria-hidden="true"></div>
      </div>
    </div>

    <div class="ud-about-statband row g-3 g-md-4 mb-4">
      <div class="col-12 col-md-4">
        <div class="ud-about-stat ud-surface h-100 text-center text-md-start">
          <div class="ud-about-stat__label">Partenariats</div>
          <div class="ud-about-stat__value"><?= $nbOffres ?> annonce<?= $nbOffres > 1 ? 's' : '' ?></div>
          <p class="ud-about-stat__hint mb-0">Offres commerciales — contact par e-mail, sans candidature emploi.</p>
        </div>
      </div>
      <div class="col-12 col-md-4">
        <div class="ud-about-stat ud-surface h-100 text-center text-md-start">
          <div class="ud-about-stat__label">Carrières</div>
          <div class="ud-about-stat__value"><?= $nbRecrutements ?> poste<?= $nbRecrutements > 1 ? 's' : '' ?></div>
          <p class="ud-about-stat__hint mb-0">Candidature en ligne — CV et lettre de motivation en PDF (5&nbsp;Mo max.).</p>
        </div>
      </div>
      <div class="col-12 col-md-4">
        <div class="ud-about-stat ud-surface h-100 text-center text-md-start">
          <div class="ud-about-stat__label">Contact</div>
          <?php if ($rhContact['email'] !== ''): ?>
            <div class="ud-about-stat__value" style="font-size: clamp(.95rem, 2.5vw, 1.15rem); word-break: break-word;">
              <a href="mailto:<?= h($rhContact['email']) ?>"><?= h($rhContact['email']) ?></a>
            </div>
          <?php else: ?>
            <div class="ud-about-stat__value">—</div>
          <?php endif; ?>
          <p class="ud-about-stat__hint mb-0">
            <?php if (!empty($rhContact['phones'])): ?>
              <?php foreach ($rhContact['phones'] as $i => $phone): ?>
                <?php if ($i > 0): ?> · <?php endif; ?>
                <a href="<?= h(ud_phone_tel_href($phone)) ?>"><?= h(ud_phone_display_fr($phone)) ?></a>
              <?php endforeach; ?>
            <?php else: ?>
              Écrivez-nous pour toute question.
            <?php endif; ?>
          </p>
        </div>
      </div>
    </div>

    <?php if (!empty($jobErrors['announcement']) || !empty($jobErrors['global'])): ?>
      <div class="alert alert-danger ud-job-apply-alert shadow-sm border-0 mb-4" role="alert">
        <?= h((string)($jobErrors['global'] ?? $jobErrors['announcement'] ?? 'Une erreur est survenue.')) ?>
      </div>
    <?php endif; ?>

    <div class="ud-services-toolbar mb-4" role="tablist" aria-label="Choisir une rubrique">
      <button
        type="button"
        class="ud-services-tab<?= $defaultTab === 'partner' ? ' is-active' : '' ?>"
        role="tab"
        aria-selected="<?= $defaultTab === 'partner' ? 'true' : 'false' ?>"
        aria-controls="udOffresPanelPartner"
        id="udOffresTabPartner"
        data-offres-tab="partner"
      >
        <span class="ud-services-tab__label">Partenariats</span>
        <span class="ud-services-tab__count" aria-hidden="true"><?= $nbOffres ?></span>
      </button>
      <button
        type="button"
        class="ud-services-tab<?= $defaultTab === 'job' ? ' is-active' : '' ?>"
        role="tab"
        aria-selected="<?= $defaultTab === 'job' ? 'true' : 'false' ?>"
        aria-controls="udOffresPanelJob"
        id="udOffresTabJob"
        data-offres-tab="job"
      >
        <span class="ud-services-tab__label">Carrières</span>
        <span class="ud-services-tab__count" aria-hidden="true"><?= $nbRecrutements ?></span>
      </button>
    </div>

    <div
      id="udOffresPanelPartner"
      class="ud-offres-panel"
      role="tabpanel"
      aria-labelledby="udOffresTabPartner"
      data-offres-panel="partner"
      <?= $defaultTab !== 'partner' ? ' hidden' : '' ?>
    >
      <?php if (empty($offres)): ?>
        <div class="ud-surface ud-offres-empty text-center py-5">
          <p class="mb-0 text-muted fw-semibold">Aucune offre commerciale publiée pour le moment.</p>
        </div>
      <?php else: ?>
        <div class="row g-3 g-lg-4">
          <?php foreach ($offres as $a): ?>
            <div class="col-12 col-lg-6">
              <article class="ud-offres-tile ud-surface h-100 d-flex flex-column">
                <h2 class="ud-offres-tile__title"><?= h($a['title']) ?></h2>
                <?php if (trim((string)($a['summary'] ?? '')) !== ''): ?>
                  <p class="ud-offres-tile__summary flex-grow-1"><?= h($a['summary']) ?></p>
                <?php endif; ?>
                <?php if (trim((string)($a['content'] ?? '')) !== ''): ?>
                  <details class="ud-offres-tile__details mt-2">
                    <summary class="ud-offres-tile__more">Lire la suite</summary>
                    <div class="ud-offres-tile__body"><?= nl2br(h($a['content'])) ?></div>
                  </details>
                <?php endif; ?>
                <?php if ($rhContact['email'] !== ''): ?>
                  <div class="mt-3 pt-3 border-top border-light-subtle">
                    <a
                      class="btn btn-primary ud-btn ud-btn--shine btn-sm w-100"
                      href="mailto:<?= h($rhContact['email']) ?>?subject=<?= rawurlencode('Demande — ' . (string)($a['title'] ?? 'Offre commerciale')) ?>"
                    >Contacter</a>
                  </div>
                <?php endif; ?>
              </article>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <div
      id="udOffresPanelJob"
      class="ud-offres-panel"
      role="tabpanel"
      aria-labelledby="udOffresTabJob"
      data-offres-panel="job"
      <?= $defaultTab !== 'job' ? ' hidden' : '' ?>
    >
      <?php if (empty($recrutements)): ?>
        <div class="ud-surface ud-offres-empty text-center py-5">
          <p class="mb-0 text-muted fw-semibold">Aucun poste publié pour le moment.</p>
        </div>
      <?php else: ?>
        <ul class="list-unstyled ud-offres-jobs mb-0">
          <?php foreach ($recrutements as $a): ?>
            <?php
              $aid = (int)$a['id'];
              $title = (string)($a['title'] ?? '');
            ?>
            <li class="ud-offres-jobs__item ud-surface" id="poste-<?= $aid ?>">
              <div class="ud-offres-jobs__main">
                <h2 class="ud-offres-jobs__title"><?= h($title) ?></h2>
                <?php if (trim((string)($a['summary'] ?? '')) !== ''): ?>
                  <p class="ud-offres-jobs__summary mb-0"><?= h($a['summary']) ?></p>
                <?php endif; ?>
                <?php if (trim((string)($a['content'] ?? '')) !== ''): ?>
                  <details class="ud-offres-tile__details mt-2">
                    <summary class="ud-offres-tile__more">Description du poste</summary>
                    <div class="ud-offres-tile__body"><?= nl2br(h($a['content'])) ?></div>
                  </details>
                <?php endif; ?>
              </div>
              <button
                type="button"
                class="btn btn-primary ud-btn ud-btn--shine ud-offres-jobs__btn"
                data-bs-toggle="modal"
                data-bs-target="#udJobModal"
                data-job-id="<?= $aid ?>"
                data-job-title="<?= h($title) ?>"
              >Postuler</button>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>

    <div class="ud-surface text-center mt-4 mt-lg-5 py-4 px-3">
      <p class="text-muted fw-semibold mb-3">Une autre demande&nbsp;?</p>
      <div class="d-flex flex-wrap justify-content-center gap-2">
        <a class="btn btn-primary ud-btn ud-btn--cta" href="<?= h($baseUrl) ?>/#contact">Nous contacter</a>
        <a class="btn btn-outline-primary ud-btn ud-btn--ghost" href="<?= h(ud_appointment_url($baseUrl)) ?>">Prendre rendez-vous</a>
      </div>
    </div>
  </div>
</section>

<?php if (!empty($recrutements)): ?>
<div
  class="modal fade"
  id="udJobModal"
  tabindex="-1"
  aria-labelledby="udJobModalLabel"
  aria-hidden="true"
  data-apply-focus="<?= $applyFocus > 0 ? (int)$applyFocus : 0 ?>"
>
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
    <div class="modal-content ud-offres-modal">
      <div class="modal-header border-0 pb-0">
        <div>
          <p class="ud-offres-modal__kicker mb-1">Candidature</p>
          <h2 class="modal-title ud-offres-modal__title" id="udJobModalLabel"><?= $applyJobTitle !== '' ? h($applyJobTitle) : 'Postuler' ?></h2>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
      </div>
      <div class="modal-body pt-2">
        <p class="text-muted small fw-semibold mb-3">CV et lettre de motivation en <strong>PDF</strong> (5&nbsp;Mo max. chacun).</p>
        <form method="post" action="?action=job-application" enctype="multipart/form-data" id="udJobApplyForm" novalidate>
          <input type="hidden" name="announcement_id" id="udJobAnnouncementId" value="<?= $applyFocus > 0 ? $applyFocus : '' ?>">
          <div class="visually-hidden" aria-hidden="true">
            <label for="udJobWebsite">Ne pas remplir</label>
            <input type="text" name="website" id="udJobWebsite" tabindex="-1" autocomplete="off">
          </div>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label" for="udJobFullName">Nom et prénom <span class="text-danger">*</span></label>
              <input class="form-control<?= isset($jobErrors['full_name']) && $applyFocus > 0 ? ' is-invalid' : '' ?>" type="text" name="full_name" id="udJobFullName" required maxlength="200" value="<?= h((string)($jobOld['full_name'] ?? '')) ?>">
              <?php if (isset($jobErrors['full_name']) && $applyFocus > 0): ?>
                <div class="invalid-feedback"><?= h($jobErrors['full_name']) ?></div>
              <?php endif; ?>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="udJobEmail">Email <span class="text-danger">*</span></label>
              <input class="form-control<?= isset($jobErrors['email']) && $applyFocus > 0 ? ' is-invalid' : '' ?>" type="email" name="email" id="udJobEmail" required value="<?= h((string)($jobOld['email'] ?? '')) ?>">
              <?php if (isset($jobErrors['email']) && $applyFocus > 0): ?>
                <div class="invalid-feedback"><?= h($jobErrors['email']) ?></div>
              <?php endif; ?>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="udJobPhone">Téléphone</label>
              <input class="form-control<?= isset($jobErrors['phone']) && $applyFocus > 0 ? ' is-invalid' : '' ?>" type="tel" name="phone" id="udJobPhone" maxlength="50" value="<?= h((string)($jobOld['phone'] ?? '')) ?>">
              <?php if (isset($jobErrors['phone']) && $applyFocus > 0): ?>
                <div class="invalid-feedback"><?= h($jobErrors['phone']) ?></div>
              <?php endif; ?>
            </div>
            <div class="col-12">
              <label class="form-label" for="udJobMessage">Message (facultatif)</label>
              <textarea class="form-control<?= isset($jobErrors['message']) && $applyFocus > 0 ? ' is-invalid' : '' ?>" name="message" id="udJobMessage" rows="3" maxlength="4000" placeholder="Quelques lignes sur votre motivation…"><?= h((string)($jobOld['message'] ?? '')) ?></textarea>
              <?php if (isset($jobErrors['message']) && $applyFocus > 0): ?>
                <div class="invalid-feedback"><?= h($jobErrors['message']) ?></div>
              <?php endif; ?>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="udJobCv">CV (PDF) <span class="text-danger">*</span></label>
              <input class="form-control<?= isset($jobErrors['cv']) && $applyFocus > 0 ? ' is-invalid' : '' ?>" type="file" name="cv" id="udJobCv" accept=".pdf,application/pdf" required>
              <?php if (isset($jobErrors['cv']) && $applyFocus > 0): ?>
                <div class="invalid-feedback d-block"><?= h($jobErrors['cv']) ?></div>
              <?php endif; ?>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="udJobCover">Lettre de motivation (PDF) <span class="text-danger">*</span></label>
              <input class="form-control<?= isset($jobErrors['cover_letter']) && $applyFocus > 0 ? ' is-invalid' : '' ?>" type="file" name="cover_letter" id="udJobCover" accept=".pdf,application/pdf" required>
              <?php if (isset($jobErrors['cover_letter']) && $applyFocus > 0): ?>
                <div class="invalid-feedback d-block"><?= h($jobErrors['cover_letter']) ?></div>
              <?php endif; ?>
            </div>
          </div>
          <div class="modal-footer border-0 px-0 pb-0 pt-3 flex-nowrap gap-2">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
            <button type="submit" class="btn btn-primary ud-btn ud-btn--shine flex-grow-1">Envoyer ma candidature</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<script>
(() => {
  const root = document.querySelector('.ud-page-offres');
  if (!root) return;

  const tabs = root.querySelectorAll('[data-offres-tab]');
  const panels = root.querySelectorAll('[data-offres-panel]');

  const activate = (id) => {
    tabs.forEach((tab) => {
      const on = tab.dataset.offresTab === id;
      tab.classList.toggle('is-active', on);
      tab.setAttribute('aria-selected', on ? 'true' : 'false');
    });
    panels.forEach((panel) => {
      const on = panel.dataset.offresPanel === id;
      panel.hidden = !on;
    });
  };

  tabs.forEach((tab) => {
    tab.addEventListener('click', () => activate(tab.dataset.offresTab));
  });

  const hash = window.location.hash.replace(/^#/, '');
  if (hash === 'section-recrutement' || hash === 'recrutement') {
    activate('job');
  }

  const modalEl = document.getElementById('udJobModal');
  if (!modalEl || typeof bootstrap === 'undefined') return;

  const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
  const titleEl = document.getElementById('udJobModalLabel');
  const idInput = document.getElementById('udJobAnnouncementId');

  root.querySelectorAll('[data-job-id]').forEach((btn) => {
    btn.addEventListener('click', () => {
      const id = btn.getAttribute('data-job-id') || '';
      const title = btn.getAttribute('data-job-title') || 'Postuler';
      if (idInput) idInput.value = id;
      if (titleEl) titleEl.textContent = title;
    });
  });

  const focusId = parseInt(modalEl.dataset.applyFocus || '0', 10);
  if (focusId > 0) {
    activate('job');
    const poste = document.getElementById('poste-' + focusId);
    if (poste) poste.scrollIntoView({ behavior: 'smooth', block: 'center' });
    modal.show();
  }
})();
</script>
<?php
$content = ob_get_clean();

$view = [
    'title' => 'Offres & recrutement — Univers Diaspora',
    'meta_description' => 'Partenariats et offres commerciales d’un côté, recrutement et offres d’emploi de l’autre — Univers Diaspora. Candidature en ligne (CV et lettre PDF).',
    'active' => 'offres-recrutement',
    'content' => $content,
    'flash' => $flash ?? [],
];

require __DIR__ . '/_layout.php';
