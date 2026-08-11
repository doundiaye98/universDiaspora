<?php
declare(strict_types=1);

if (!function_exists('team_members_all')) {
    require_once __DIR__ . '/../app/team_members.php';
}

$baseUrl = function_exists('ud_site_base_url')
    ? ud_site_base_url()
    : rtrim((string)((require __DIR__ . '/../config/config.php')['app']['base_url'] ?? ''), '/');

$team = [];
try {
    $team = team_members_all();
} catch (Throwable $e) {
    error_log('[equipe] ' . $e->getMessage());
    $team = function_exists('team_members_fallback_data') ? team_members_fallback_data() : [];
}

ob_start();
?>
<section class="ud-equipe">
  <header class="ud-equipe__hero">
    <div class="container px-3 px-sm-4">
      <p class="ud-equipe__mark">Univers Diaspora</p>
      <p class="ud-equipe__promise">Faire de vos rêves une réalité</p>
      <h1 class="ud-equipe__title">
        Une équipe à votre écoute<br>
        <span>pour vos projets</span>
      </h1>
      <p class="ud-equipe__lead">
        Des interlocuteurs identifiés, disponibles à Paris 18<sup>e</sup>, Paris 17<sup>e</sup> et Colombes —
        pour clarifier vos démarches et avancer concrètement.
      </p>
      <div class="ud-equipe__actions">
        <a class="btn btn-primary ud-btn ud-btn--cta" href="<?= h(ud_appointment_url($baseUrl)) ?>">Prendre rendez-vous</a>
        <a class="btn btn-outline-light ud-btn ud-btn--ghost ud-btn--on-dark" href="<?= h($baseUrl) ?>/?page=apropos">À propos</a>
      </div>
    </div>
  </header>

  <div class="ud-equipe__body">
    <div class="container px-3 px-sm-4">
      <?php if ($team !== []): ?>
        <section class="ud-equipe__grid-wrap" aria-labelledby="ud-equipe-grid">
          <p class="ud-equipe__kicker">L’équipe</p>
          <h2 id="ud-equipe-grid" class="ud-equipe__h2">Qui vous accompagne</h2>
          <div class="ud-equipe__grid">
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
                $imgSrc = null;
                if ($photo !== null && function_exists('ud_public_asset_url')) {
                    $imgSrc = ud_public_asset_url('img/team/' . $photo, $baseUrl);
                }
              ?>
              <article class="ud-equipe__card">
                <div class="ud-equipe__visual">
                  <?php if ($imgSrc !== null): ?>
                    <img class="ud-equipe__photo" src="<?= h($imgSrc) ?>" alt="" width="400" height="400" loading="lazy">
                  <?php else: ?>
                    <div class="ud-equipe__placeholder" aria-hidden="true"><?= h($initials) ?></div>
                  <?php endif; ?>
                </div>
                <div class="ud-equipe__card-body">
                  <h3 class="ud-equipe__name"><?= h($name) ?></h3>
                  <?php if ($role !== ''): ?>
                    <p class="ud-equipe__role"><?= h($role) ?></p>
                  <?php endif; ?>
                  <?php if ($bio !== ''): ?>
                    <p class="ud-equipe__bio"><?= h($bio) ?></p>
                  <?php endif; ?>
                </div>
              </article>
            <?php endforeach; ?>
          </div>
        </section>
      <?php else: ?>
        <p class="ud-equipe__empty">L’équipe sera présentée prochainement.</p>
      <?php endif; ?>

      <section class="ud-equipe__cta" aria-labelledby="ud-equipe-cta">
        <h2 id="ud-equipe-cta" class="ud-equipe__cta-title">Envie d’échanger avec nous&nbsp;?</h2>
        <p class="ud-equipe__cta-text">
          Un premier rendez-vous pour cadrer votre besoin et vous orienter vers le bon pôle.
        </p>
        <div class="ud-equipe__actions ud-equipe__actions--center">
          <a class="btn btn-primary ud-btn ud-btn--cta" href="<?= h(ud_appointment_url($baseUrl)) ?>">Prendre rendez-vous</a>
          <a class="btn btn-outline-primary ud-btn ud-btn--ghost" href="<?= h($baseUrl) ?>/#services">Nos services</a>
          <a class="btn btn-outline-primary ud-btn ud-btn--ghost" href="<?= h($baseUrl) ?>/#contact">Contact</a>
        </div>
      </section>
    </div>
  </div>
</section>
<?php
$content = ob_get_clean();

$view = [
    'title' => 'Notre équipe — Univers Diaspora',
    'meta_description' => 'L’équipe Univers Diaspora : interlocuteurs à Paris 18ᵉ, Paris 17ᵉ et Colombes pour accompagner vos projets.',
    'active' => 'equipe',
    'content' => $content,
    'flash' => $flash ?? [],
];

require __DIR__ . '/_layout.php';
