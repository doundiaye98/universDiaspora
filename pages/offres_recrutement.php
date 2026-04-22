<?php
declare(strict_types=1);

$config = require __DIR__ . '/../config/config.php';
$baseUrl = rtrim($config['app']['base_url'], '/');

$pdo = db();
$offres = announcements_all($pdo, true, 'offre');
$recrutements = announcements_all($pdo, true, 'recrutement');

$jobErrors = $_SESSION['job_apply_errors'] ?? [];
$jobOld = $_SESSION['job_apply_old'] ?? [];
unset($_SESSION['job_apply_errors'], $_SESSION['job_apply_old']);

$applyFocus = (int)($_GET['apply'] ?? 0);
if (!empty($jobErrors) && $applyFocus === 0) {
    $applyFocus = (int)($jobOld['announcement_id'] ?? 0);
}

ob_start();
?>
<section class="ud-about-hero ud-page-offres py-3 py-md-4 py-lg-5">
  <div class="container px-3 px-sm-4">
    <nav aria-label="Fil d’Ariane" class="ud-breadcrumb mb-3">
      <a href="<?= h($baseUrl) ?>/">Accueil</a>
      <span class="ud-breadcrumb__sep">/</span>
      <span>Offres &amp; recrutement</span>
    </nav>
    <div class="ud-about-highlight mb-4 mb-lg-5">
      <p class="ud-about-highlight__lead mb-0">
        Partenariats, opportunités commerciales et carrières : retrouvez ici les annonces publiées par Univers Diaspora.
        Les candidatures se font en ligne, avec envoi sécurisé de vos documents au format PDF.
      </p>
    </div>
    <div class="ud-section-title text-center mb-4 mb-lg-5">
      <div class="ud-section-kicker">À la une</div>
      <h1 class="ud-title mb-2">Offres &amp; recrutement</h1>
      <div class="ud-subtitle">Découvrez nos opportunités commerciales et les postes ouverts au sein d’Univers Diaspora.</div>
      <div class="ud-section-divider mt-3" aria-hidden="true"></div>
    </div>

    <?php if (!empty($jobErrors['announcement']) || !empty($jobErrors['global'])): ?>
      <div class="alert alert-danger ud-job-apply-alert shadow-sm border-0 mb-4">
        <?= h((string)($jobErrors['global'] ?? $jobErrors['announcement'] ?? 'Une erreur est survenue.')) ?>
      </div>
    <?php endif; ?>

    <div class="row g-3 g-lg-4">
      <div class="col-12 col-lg-6">
        <div class="ud-announce-section">
          <h2 class="h4 ud-announce-section__title mb-3">
            <span class="ud-announce-section__icon" aria-hidden="true">◆</span>
            Offres
          </h2>
          <?php if (empty($offres)): ?>
            <div class="ud-surface ud-announce-empty">Aucune offre publiée pour le moment. Revenez bientôt.</div>
          <?php else: ?>
            <div class="d-flex flex-column gap-3">
              <?php foreach ($offres as $a): ?>
                <article class="ud-announce-card ud-surface">
                  <h3 class="ud-announce-card__title"><?= h($a['title']) ?></h3>
                  <?php if (trim((string)($a['summary'] ?? '')) !== ''): ?>
                    <p class="ud-announce-card__summary mb-2"><?= h($a['summary']) ?></p>
                  <?php endif; ?>
                  <?php if (trim((string)($a['content'] ?? '')) !== ''): ?>
                    <div class="ud-announce-card__body"><?= nl2br(h($a['content'])) ?></div>
                  <?php endif; ?>
                </article>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
      <div class="col-12 col-lg-6">
        <div class="ud-announce-section">
          <h2 class="h4 ud-announce-section__title mb-3">
            <span class="ud-announce-section__icon ud-announce-section__icon--job" aria-hidden="true">◎</span>
            Recrutement
          </h2>
          <?php if (empty($recrutements)): ?>
            <div class="ud-surface ud-announce-empty">Aucune offre d’emploi publiée pour le moment.</div>
          <?php else: ?>
            <div class="d-flex flex-column gap-3">
              <?php foreach ($recrutements as $a): ?>
                <?php
                  $aid = (int)$a['id'];
                  $collapseId = 'jobApply' . $aid;
                  $hasErr = !empty($jobErrors) && $applyFocus === $aid;
                  $collapseShow = $hasErr ? ' show' : '';
                  $oldA = ($applyFocus === $aid) ? $jobOld : [];
                ?>
                <article class="ud-announce-card ud-surface ud-announce-card--job" id="candidature-<?= $aid ?>">
                  <h3 class="ud-announce-card__title"><?= h($a['title']) ?></h3>
                  <?php if (trim((string)($a['summary'] ?? '')) !== ''): ?>
                    <p class="ud-announce-card__summary mb-2"><?= h($a['summary']) ?></p>
                  <?php endif; ?>
                  <?php if (trim((string)($a['content'] ?? '')) !== ''): ?>
                    <div class="ud-announce-card__body"><?= nl2br(h($a['content'])) ?></div>
                  <?php endif; ?>

                  <div class="mt-3 pt-3 border-top border-light-subtle">
                    <button class="btn btn-primary ud-btn ud-btn--shine btn-sm w-100 w-md-auto" type="button" data-bs-toggle="collapse" data-bs-target="#<?= h($collapseId) ?>" aria-expanded="<?= $hasErr ? 'true' : 'false' ?>" aria-controls="<?= h($collapseId) ?>">
                      Postuler
                    </button>
                    <div class="collapse<?= $collapseShow ?>" id="<?= h($collapseId) ?>">
                      <div class="ud-job-apply mt-3">
                        <p class="small text-muted mb-3">Envoyez votre CV et votre lettre de motivation au format <strong>PDF</strong> (5&nbsp;Mo max. chacun), ainsi que vos coordonnées.</p>
                        <form method="post" action="?action=job-application" enctype="multipart/form-data" novalidate>
                          <input type="hidden" name="announcement_id" value="<?= $aid ?>">
                          <div class="visually-hidden" aria-hidden="true">
                            <label for="web-<?= $aid ?>">Ne pas remplir</label>
                            <input type="text" name="website" id="web-<?= $aid ?>" tabindex="-1" autocomplete="off">
                          </div>
                          <div class="mb-3">
                            <label class="form-label" for="fn-<?= $aid ?>">Nom et prénom <span class="text-danger">*</span></label>
                            <input class="form-control<?= isset($jobErrors['full_name']) && $applyFocus === $aid ? ' is-invalid' : '' ?>" type="text" name="full_name" id="fn-<?= $aid ?>" required maxlength="200" value="<?= h((string)($oldA['full_name'] ?? '')) ?>">
                            <?php if (isset($jobErrors['full_name']) && $applyFocus === $aid): ?>
                              <div class="invalid-feedback"><?= h($jobErrors['full_name']) ?></div>
                            <?php endif; ?>
                          </div>
                          <div class="mb-3">
                            <label class="form-label" for="em-<?= $aid ?>">Email <span class="text-danger">*</span></label>
                            <input class="form-control<?= isset($jobErrors['email']) && $applyFocus === $aid ? ' is-invalid' : '' ?>" type="email" name="email" id="em-<?= $aid ?>" required value="<?= h((string)($oldA['email'] ?? '')) ?>">
                            <?php if (isset($jobErrors['email']) && $applyFocus === $aid): ?>
                              <div class="invalid-feedback"><?= h($jobErrors['email']) ?></div>
                            <?php endif; ?>
                          </div>
                          <div class="mb-3">
                            <label class="form-label" for="ph-<?= $aid ?>">Téléphone</label>
                            <input class="form-control<?= isset($jobErrors['phone']) && $applyFocus === $aid ? ' is-invalid' : '' ?>" type="tel" name="phone" id="ph-<?= $aid ?>" maxlength="50" value="<?= h((string)($oldA['phone'] ?? '')) ?>">
                            <?php if (isset($jobErrors['phone']) && $applyFocus === $aid): ?>
                              <div class="invalid-feedback"><?= h($jobErrors['phone']) ?></div>
                            <?php endif; ?>
                          </div>
                          <div class="mb-3">
                            <label class="form-label" for="msg-<?= $aid ?>">Message (facultatif)</label>
                            <textarea class="form-control<?= isset($jobErrors['message']) && $applyFocus === $aid ? ' is-invalid' : '' ?>" name="message" id="msg-<?= $aid ?>" rows="3" maxlength="4000" placeholder="Quelques lignes sur votre motivation…"><?= h((string)($oldA['message'] ?? '')) ?></textarea>
                            <?php if (isset($jobErrors['message']) && $applyFocus === $aid): ?>
                              <div class="invalid-feedback"><?= h($jobErrors['message']) ?></div>
                            <?php endif; ?>
                          </div>
                          <div class="mb-3">
                            <label class="form-label" for="cv-<?= $aid ?>">CV (PDF) <span class="text-danger">*</span></label>
                            <input class="form-control<?= isset($jobErrors['cv']) && $applyFocus === $aid ? ' is-invalid' : '' ?>" type="file" name="cv" id="cv-<?= $aid ?>" accept=".pdf,application/pdf" required>
                            <?php if (isset($jobErrors['cv']) && $applyFocus === $aid): ?>
                              <div class="invalid-feedback d-block"><?= h($jobErrors['cv']) ?></div>
                            <?php endif; ?>
                          </div>
                          <div class="mb-3">
                            <label class="form-label" for="lm-<?= $aid ?>">Lettre de motivation (PDF) <span class="text-danger">*</span></label>
                            <input class="form-control<?= isset($jobErrors['cover_letter']) && $applyFocus === $aid ? ' is-invalid' : '' ?>" type="file" name="cover_letter" id="lm-<?= $aid ?>" accept=".pdf,application/pdf" required>
                            <?php if (isset($jobErrors['cover_letter']) && $applyFocus === $aid): ?>
                              <div class="invalid-feedback d-block"><?= h($jobErrors['cover_letter']) ?></div>
                            <?php endif; ?>
                          </div>
                          <button type="submit" class="btn btn-primary ud-btn ud-btn--shine w-100 w-sm-auto">Envoyer ma candidature</button>
                        </form>
                      </div>
                    </div>
                  </div>
                </article>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div class="ud-about-cta ud-surface text-center mt-4 mt-md-5">
      <h2 class="ud-about-cta__title mb-2">Autres démarches</h2>
      <p class="ud-about-cta__text mx-auto mb-4">
        Pour toute question générale, un rendez-vous en bureau ou un premier échange sur un projet, utilisez les liens ci-dessous.
      </p>
      <div class="d-flex flex-wrap justify-content-center gap-2">
        <a class="btn btn-primary ud-btn ud-btn--cta" href="<?= h($baseUrl) ?>/#contact">Nous contacter</a>
        <a class="btn btn-outline-primary ud-btn ud-btn--ghost" href="<?= h($baseUrl) ?>/?page=rendez-vous">Prendre rendez-vous</a>
        <a class="btn btn-outline-primary ud-btn ud-btn--ghost" href="<?= h($baseUrl) ?>/?page=equipe">Notre équipe</a>
        <a class="btn btn-outline-primary ud-btn ud-btn--ghost" href="<?= h($baseUrl) ?>/?page=politique-confidentialite">Confidentialité</a>
      </div>
    </div>
  </div>
</section>
<?php
if (!empty($jobErrors) && $applyFocus > 0) {
    ?>
<script>
(() => {
  const el = document.getElementById('candidature-<?= (int)$applyFocus ?>');
  if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
})();
</script>
<?php
}
$content = ob_get_clean();

$view = [
    'title' => 'Offres & recrutement — Univers Diaspora',
    'meta_description' => 'Offres commerciales et offres d’emploi Univers Diaspora : candidature en ligne (CV et lettre PDF), coordonnées.',
    'active' => 'offres-recrutement',
    'content' => $content,
    'flash' => $flash ?? [],
];

require __DIR__ . '/_layout.php';
