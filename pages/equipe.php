<?php
declare(strict_types=1);

$config = require __DIR__ . '/../config/config.php';
$baseUrl = rtrim($config['app']['base_url'], '/');

$pdo = db();
$team = team_members_all($pdo);

ob_start();
?>
<section class="ud-about-hero ud-page-equipe py-3 py-md-4 py-lg-5">
  <div class="container px-3 px-sm-4">
    <nav aria-label="Fil d’ariane" class="ud-breadcrumb mb-3">
      <a href="<?= h($baseUrl) ?>/">Accueil</a>
      <span class="ud-breadcrumb__sep">/</span>
      <span>Notre équipe</span>
    </nav>

    <div class="ud-section-title text-center mb-4 mb-lg-5">
      <div class="ud-section-kicker">Univers Diaspora</div>
      <h1 class="ud-title mb-2">Notre équipe</h1>
      <div class="ud-subtitle">Des professionnels à votre écoute pour vous accompagner dans vos projets.</div>
      <div class="ud-section-divider mt-3" aria-hidden="true"></div>
    </div>

    <div class="row g-4 justify-content-center">
      <?php foreach ($team as $member): ?>
        <?php
          if (!is_array($member)) {
              continue;
          }
          $name = trim((string)($member['name'] ?? ''));
          $role = trim((string)($member['role'] ?? ''));
          $bio = trim((string)($member['bio'] ?? ''));
          $photo = isset($member['photo']) && is_string($member['photo']) && $member['photo'] !== ''
              ? basename(trim($member['photo']))
              : null;
          if ($name === '') {
              continue;
          }
          $initials = '';
          $parts = preg_split('~\s+~u', $name, -1, PREG_SPLIT_NO_EMPTY);
          if (is_array($parts)) {
              foreach ($parts as $p) {
                  if (function_exists('mb_substr')) {
                      $initials .= mb_strtoupper(mb_substr($p, 0, 1));
                  } else {
                      $initials .= strtoupper(substr($p, 0, 1));
                  }
                  if (strlen($initials) >= 2) {
                      break;
                  }
              }
          }
          if ($initials === '') {
              $initials = '?';
          }
          $imgSrc = $photo !== null ? ud_public_asset_url('img/team/' . basename($photo), $baseUrl) : null;
        ?>
        <div class="col-12 col-sm-6 col-xl-4">
          <article class="ud-team-card ud-surface h-100 d-flex flex-column">
            <div class="ud-team-card__visual">
              <?php if ($imgSrc !== null): ?>
                <img class="ud-team-card__photo" src="<?= h($imgSrc) ?>" alt="" width="400" height="400" loading="lazy">
              <?php else: ?>
                <div class="ud-team-card__placeholder" aria-hidden="true"><?= h($initials) ?></div>
              <?php endif; ?>
            </div>
            <div class="ud-team-card__body flex-grow-1 d-flex flex-column">
              <h2 class="ud-team-card__name"><?= h($name) ?></h2>
              <?php if ($role !== ''): ?>
                <p class="ud-team-card__role"><?= h($role) ?></p>
              <?php endif; ?>
              <?php if ($bio !== ''): ?>
                <p class="ud-team-card__bio mb-0"><?= h($bio) ?></p>
              <?php endif; ?>
            </div>
          </article>
        </div>
      <?php endforeach; ?>
    </div>

    <?php if (empty($team)): ?>
      <p class="text-center text-muted py-5">L’équipe sera présentée prochainement.</p>
    <?php endif; ?>

    <div class="ud-about-cta ud-surface text-center mt-4 mt-lg-5">
      <h2 class="ud-about-cta__title mb-2">Travailler avec Univers Diaspora</h2>
      <p class="ud-about-cta__text mx-auto mb-4">
        En savoir plus sur notre mission, explorer nos services ou prendre directement contact avec l’équipe.
      </p>
      <div class="d-flex flex-wrap justify-content-center gap-2">
        <a class="btn btn-primary ud-btn ud-btn--cta" href="<?= h($baseUrl) ?>/#contact">Nous contacter</a>
        <a class="btn btn-outline-primary ud-btn ud-btn--ghost" href="<?= h($baseUrl) ?>/?page=apropos">À propos</a>
        <a class="btn btn-outline-primary ud-btn ud-btn--ghost" href="<?= h($baseUrl) ?>/#services">Nos services</a>
        <a class="btn btn-outline-primary ud-btn ud-btn--ghost" href="<?= h($baseUrl) ?>/?page=demarrer-maintenant">Démarrer un projet</a>
      </div>
    </div>
  </div>
</section>
<?php
$content = ob_get_clean();

$view = [
    'title' => 'Notre équipe — Univers Diaspora',
    'meta_description' => 'Découvrez l’équipe d’Univers Diaspora : conseil et accompagnement pour la diaspora.',
    'active' => 'equipe',
    'content' => $content,
    'flash' => $flash ?? [],
];

require __DIR__ . '/_layout.php';
