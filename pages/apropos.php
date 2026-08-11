<?php
declare(strict_types=1);

$config = require __DIR__ . '/../config/config.php';
$baseUrl = ud_site_base_url();
$offices = is_file(__DIR__ . '/../data/offices.php') ? require __DIR__ . '/../data/offices.php' : [];
if (!is_array($offices)) {
    $offices = [];
}

$bgSlides = [];
foreach (['g1.jpg', 'g2.jpg', 'g3.jpg', 'g4.jpg'] as $bgFile) {
    $bgPath = __DIR__ . '/../public/assets/img/' . $bgFile;
    if (is_file($bgPath)) {
        $bgSlides[] = ud_public_asset_url('img/' . $bgFile, $baseUrl);
    }
}

ob_start();
?>
<section class="ud-apropos"<?= $bgSlides !== [] ? ' data-ud-apropos-bg' : '' ?>>
  <?php if ($bgSlides !== []): ?>
    <div class="ud-apropos__bg" aria-hidden="true">
      <?php foreach ($bgSlides as $i => $src): ?>
        <div
          class="ud-apropos__bg-slide<?= $i === 0 ? ' is-active' : '' ?>"
          style="background-image:url('<?= h($src) ?>')"
        ></div>
      <?php endforeach; ?>
      <div class="ud-apropos__bg-veil"></div>
    </div>
  <?php endif; ?>

  <div class="ud-apropos__inner">
    <header class="ud-apropos__hero">
      <div class="container px-3 px-sm-4">
        <p class="ud-apropos__mark">Univers Diaspora</p>
        <p class="ud-apropos__promise">Faire de vos rêves une réalité</p>
        <h1 class="ud-apropos__title">
          Votre partenaire de confiance<br>
          <span>pour la diaspora</span>
        </h1>
        <p class="ud-apropos__lead">
          Tous vos services réunis en un seul lieu — conseil, démarches et projets,
          avec une équipe à Paris 18<sup>e</sup>, Paris 17<sup>e</sup> et Colombes.
        </p>
        <div class="ud-apropos__actions">
          <a class="btn btn-primary ud-btn ud-btn--cta" href="<?= h(ud_appointment_url($baseUrl)) ?>">Prendre rendez-vous</a>
          <a class="btn btn-outline-light ud-btn ud-btn--ghost ud-btn--on-dark" href="<?= h($baseUrl) ?>/#services">Voir les 12 pôles</a>
        </div>
      </div>
    </header>

    <div class="ud-apropos__body">
      <div class="container px-3 px-sm-4">
        <section class="ud-apropos__story" aria-labelledby="ud-apropos-story">
          <div class="ud-apropos__story-grid">
            <div>
              <p class="ud-apropos__kicker">Notre rôle</p>
              <h2 id="ud-apropos-story" class="ud-apropos__h2">Structurer vos projets, sans jargon inutile</h2>
              <p class="ud-apropos__p">
                Depuis mars 2024, Univers Diaspora accompagne les membres de la diaspora
                dans leurs démarches personnelles, administratives, professionnelles et patrimoniales.
                Nous clarifions les étapes, préparons les dossiers et restons à vos côtés jusqu’au résultat.
              </p>
              <p class="ud-apropos__p">
                Pas de promesses irréalistes : un cadre professionnel, des interlocuteurs identifiés
                et un suivi adapté à votre échéancier.
              </p>
            </div>
            <aside class="ud-apropos__aside" aria-label="En bref">
              <div class="ud-apropos__aside-item">
                <span class="ud-apropos__aside-label">Depuis</span>
                <strong>Mars 2024</strong>
              </div>
              <div class="ud-apropos__aside-item">
                <span class="ud-apropos__aside-label">Agences</span>
                <strong>3 bureaux en Île-de-France</strong>
              </div>
              <div class="ud-apropos__aside-item">
                <span class="ud-apropos__aside-label">Contact</span>
                <strong><a href="mailto:contact@universdiaspora.com">contact@universdiaspora.com</a></strong>
              </div>
            </aside>
          </div>
        </section>

        <section class="ud-apropos__trust" aria-labelledby="ud-apropos-trust">
          <p class="ud-apropos__kicker ud-apropos__kicker--on-dark">Engagement</p>
          <h2 id="ud-apropos-trust" class="ud-apropos__h2 ud-apropos__h2--on-dark">Pourquoi nous faire confiance&nbsp;?</h2>
          <ul class="ud-apropos__trust-grid">
            <li>
              <strong>Équipe expérimentée</strong>
              <span>À votre écoute, de Paris à votre pays d’origine.</span>
            </li>
            <li>
              <strong>Accompagnement A à Z</strong>
              <span>Un parcours clair, du premier échange au suivi.</span>
            </li>
            <li>
              <strong>Solutions adaptées</strong>
              <span>Chaque dossier est traité selon votre situation réelle.</span>
            </li>
            <li>
              <strong>Paiement flexible</strong>
              <span>Mensualités possibles selon les projets.</span>
            </li>
          </ul>
        </section>

        <?php if ($offices !== []): ?>
          <section class="ud-apropos__offices" aria-labelledby="ud-apropos-offices">
            <p class="ud-apropos__kicker">Présence</p>
            <h2 id="ud-apropos-offices" class="ud-apropos__h2">Nos agences</h2>
            <div class="ud-apropos__office-grid">
              <?php foreach ($offices as $office): ?>
                <?php if (!is_array($office)) {
                    continue;
                } ?>
                <article class="ud-apropos__office">
                  <h3 class="ud-apropos__office-name"><?= h((string)($office['short_name'] ?? $office['name'] ?? '')) ?></h3>
                  <p class="ud-apropos__office-addr"><?= h((string)($office['address'] ?? '')) ?></p>
                  <?php if (!empty($office['phone_fixe'])): ?>
                    <p class="ud-apropos__office-tel">Fixe <?= h((string)$office['phone_fixe']) ?></p>
                  <?php endif; ?>
                  <?php if (!empty($office['phone_mobile'])): ?>
                    <p class="ud-apropos__office-tel">Tél <?= h((string)$office['phone_mobile']) ?></p>
                  <?php endif; ?>
                </article>
              <?php endforeach; ?>
            </div>
          </section>
        <?php endif; ?>

        <section class="ud-apropos__cta" aria-labelledby="ud-apropos-cta">
          <h2 id="ud-apropos-cta" class="ud-apropos__cta-title">Prêt à avancer sur votre projet&nbsp;?</h2>
          <p class="ud-apropos__cta-text">
            Un premier échange suffit pour cadrer le besoin et vous orienter vers le bon pôle.
          </p>
          <div class="ud-apropos__actions ud-apropos__actions--center">
            <a class="btn btn-primary ud-btn ud-btn--cta" href="<?= h(ud_appointment_url($baseUrl)) ?>">Prendre rendez-vous</a>
            <a class="btn btn-outline-primary ud-btn ud-btn--ghost" href="<?= h($baseUrl) ?>/?page=equipe">L’équipe</a>
            <a class="btn btn-outline-primary ud-btn ud-btn--ghost" href="<?= h($baseUrl) ?>/#contact">Contact</a>
          </div>
        </section>
      </div>
    </div>
  </div>
</section>
<?php if ($bgSlides !== []): ?>
<script src="<?= h(ud_public_asset_url('js/ud-apropos-bg.js', $baseUrl)) ?>?v=<?= (int) @filemtime(__DIR__ . '/../public/assets/js/ud-apropos-bg.js') ?>" defer></script>
<?php endif; ?>
<?php
$content = ob_get_clean();

$view = [
    'title' => 'À propos — Univers Diaspora',
    'meta_description' => 'Univers Diaspora : partenaire de confiance pour la diaspora. Conseil, démarches et projets — Paris 18ᵉ, Paris 17ᵉ et Colombes. Faire de vos rêves une réalité.',
    'active' => 'apropos',
    'content' => $content,
];

require __DIR__ . '/_layout.php';
