<?php
declare(strict_types=1);

/**
 * Page Immobilier & BTP — programme YOMBAL KEUR (terrains diaspora).
 */

$config = require __DIR__ . '/../config/config.php';
$baseUrl = ud_site_base_url();
$catalog = require __DIR__ . '/../data/yombal_keur.php';
$program = is_array($catalog['program'] ?? null) ? $catalog['program'] : [];
$offers = is_array($catalog['offers'] ?? null) ? $catalog['offers'] : [];

$voletsAll = require __DIR__ . '/../data/service_volets.php';
$serviceVolets = isset($voletsAll['immobilier-btp']) && is_array($voletsAll['immobilier-btp'])
    ? $voletsAll['immobilier-btp']
    : [];

$imgBase = rtrim($baseUrl, '/') . '/public/img/immobilier/';
$rdvUrl = function_exists('ud_appointment_url')
    ? ud_appointment_url($baseUrl, 'immobilier-btp')
    : ($baseUrl . '/?page=rendez-vous&service=immobilier-btp');

$phone = (string)($program['phone'] ?? '');
$phoneTel = (string)($program['phone_tel'] ?? '');
$email = (string)($program['email'] ?? '');
$address = (string)($program['address'] ?? '');

ob_start();
?>
<section class="ud-yk">
  <header class="yk-hero">
    <div class="yk-hero__media" aria-hidden="true">
      <img
        class="yk-hero__img"
        src="<?= h($imgBase . rawurlencode('hero-immobilier-btp.jpg')) ?>"
        alt=""
        width="1600"
        height="900"
        fetchpriority="high"
      >
      <div class="yk-hero__veil"></div>
    </div>
    <div class="yk-hero__inner">
      <a class="yk-back" href="<?= h($baseUrl) ?>/#services"><span aria-hidden="true">&larr;</span> Tous les pôles</a>
      <p class="yk-eyebrow"><?= h((string)($program['tagline'] ?? '')) ?></p>
      <p class="yk-lead"><?= h((string)($program['lead'] ?? '')) ?></p>
      <h1 class="yk-title">
        <span class="yk-title__program"><?= h((string)($program['name'] ?? 'YOMBAL KEUR')) ?></span>
        <span class="yk-title__service">Immobilier &amp; BTP</span>
      </h1>
      <p class="yk-badge"><?= h((string)($program['badge'] ?? 'Terrains à céder')) ?></p>
      <div class="yk-hero__actions">
        <a class="btn btn-primary ud-btn ud-btn--cta" href="#yk-offres">Voir les terrains</a>
        <a class="btn btn-outline-light ud-btn" href="<?= h($rdvUrl) ?>">Prendre rendez-vous</a>
      </div>
    </div>
  </header>

  <div class="yk-strip" aria-label="Contact immobilier">
    <a class="yk-strip__item" href="tel:<?= h($phoneTel) ?>">
      <span class="yk-strip__label">Tél</span>
      <span class="yk-strip__value"><?= h($phone) ?></span>
    </a>
    <a class="yk-strip__item" href="mailto:<?= h($email) ?>">
      <span class="yk-strip__label">Email</span>
      <span class="yk-strip__value"><?= h($email) ?></span>
    </a>
    <div class="yk-strip__item yk-strip__item--static">
      <span class="yk-strip__label">Bureau</span>
      <span class="yk-strip__value"><?= h($address) ?></span>
    </div>
  </div>

  <div class="yk-body">
    <section class="yk-intro" aria-labelledby="yk-intro-heading">
      <div class="yk-intro__copy">
        <p class="yk-kicker">Programme diaspora</p>
        <h2 id="yk-intro-heading" class="yk-heading">Trois sites au Sénégal, un parcours clair</h2>
        <p class="yk-text">
          Choisissez un terrain de 150&nbsp;m² accessible dès 0&nbsp;€ d’apport, avec paiements échelonnés
          et accompagnement jusqu’à la régularisation. Partenaire terrain&nbsp;:
          <strong><?= h((string)($program['partner'] ?? '')) ?></strong>.
        </p>
      </div>
      <ul class="yk-intro__stats" role="list">
        <li><strong>0&nbsp;€</strong><span>Apport initial</span></li>
        <li><strong>150&nbsp;m²</strong><span>Parcelle type</span></li>
        <li><strong>3</strong><span>Localisations</span></li>
      </ul>
    </section>

    <section id="yk-offres" class="yk-offers" aria-labelledby="yk-offers-heading">
      <div class="yk-section-head">
        <p class="yk-kicker">Catalogue</p>
        <h2 id="yk-offers-heading" class="yk-heading">Terrains à céder</h2>
      </div>

      <div class="yk-offers__grid">
        <?php foreach ($offers as $i => $offer): ?>
          <?php
            if (!is_array($offer)) {
                continue;
            }
            $oid = (string)($offer['id'] ?? ('offre-' . $i));
            $img = (string)($offer['image'] ?? '');
            $imgUrl = $img !== '' ? $imgBase . rawurlencode($img) : '';
            $terms = is_array($offer['terms'] ?? null) ? $offer['terms'] : [];
            $process = is_array($offer['process'] ?? null) ? $offer['process'] : [];
          ?>
          <article class="yk-card" id="yk-<?= h($oid) ?>">
            <?php if ($imgUrl !== ''): ?>
              <figure class="yk-card__media">
                <a class="yk-card__poster" href="<?= h($imgUrl) ?>" target="_blank" rel="noopener noreferrer" title="Voir l’affiche complète">
                  <img
                    src="<?= h($imgUrl) ?>"
                    alt="Affiche <?= h((string)($offer['headline'] ?? $offer['location'] ?? '')) ?>"
                    width="720"
                    height="1280"
                    loading="<?= $i === 0 ? 'eager' : 'lazy' ?>"
                  >
                </a>
              </figure>
            <?php endif; ?>

            <div class="yk-card__body">
              <div class="yk-card__price">
                <span class="yk-card__area"><?= h((string)($offer['area'] ?? '')) ?></span>
                <span class="yk-card__amount"><?= h((string)($offer['price'] ?? '')) ?></span>
              </div>
              <p class="yk-card__loc"><?= h((string)($offer['location'] ?? '')) ?></p>
              <h3 class="yk-card__title"><?= h((string)($offer['headline'] ?? '')) ?></h3>
              <?php if (!empty($offer['price_note'])): ?>
                <p class="yk-card__note"><?= h((string)$offer['price_note']) ?></p>
              <?php endif; ?>

              <div class="yk-card__cols">
                <div>
                  <h4 class="yk-card__col-title">Conditions</h4>
                  <ul class="yk-list">
                    <?php foreach ($terms as $t): ?>
                      <li><?= h((string)$t) ?></li>
                    <?php endforeach; ?>
                  </ul>
                </div>
                <div>
                  <h4 class="yk-card__col-title">Parcours</h4>
                  <ul class="yk-list">
                    <?php foreach ($process as $p): ?>
                      <li><?= h((string)$p) ?></li>
                    <?php endforeach; ?>
                  </ul>
                </div>
              </div>

              <div class="yk-card__actions">
                <a class="btn btn-primary ud-btn ud-btn--cta" href="<?= h($rdvUrl) ?>">Demander ce terrain</a>
                <?php if ($imgUrl !== ''): ?>
                  <a class="btn btn-outline-primary ud-btn" href="<?= h($imgUrl) ?>" target="_blank" rel="noopener noreferrer">Affiche HD</a>
                <?php endif; ?>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    </section>

    <?php if (!empty($serviceVolets)): ?>
      <section class="yk-volets" aria-labelledby="yk-volets-heading">
        <div class="yk-section-head">
          <p class="yk-kicker">Accompagnement</p>
          <h2 id="yk-volets-heading" class="yk-heading">Autres volets Immobilier &amp; BTP</h2>
        </div>
        <div class="yk-volets__grid">
          <?php foreach ($serviceVolets as $v): ?>
            <?php if (!is_array($v)) {
                continue;
            } ?>
            <article class="yk-volet">
              <h3 class="yk-volet__title"><?= h((string)($v['label'] ?? '')) ?></h3>
              <p class="yk-volet__lead"><?= h((string)($v['lead'] ?? '')) ?></p>
              <a class="yk-volet__link" href="<?= h($rdvUrl) ?>">Prendre RDV →</a>
            </article>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endif; ?>

    <aside class="yk-disclaimer" role="note">
      <p>
        Univers Diaspora n’est ni notaire, ni agence immobilière au sens de la loi Hoguet, ni maître d’œuvre, ni entreprise de BTP.
        Nous facilitons le cadrage du projet et la coordination avec les professionnels du secteur.
        La signature des actes, la maîtrise d’œuvre et l’exécution des travaux relèvent de prestataires habilités.
      </p>
    </aside>

    <section class="yk-cta" aria-labelledby="yk-cta-heading">
      <h2 id="yk-cta-heading" class="yk-cta__title">Prêt à réserver votre parcelle&nbsp;?</h2>
      <p class="yk-cta__text">Un conseiller vous présente les disponibilités, les modalités et les prochaines étapes.</p>
      <div class="yk-cta__actions">
        <a class="btn btn-primary ud-btn ud-btn--cta" href="<?= h($rdvUrl) ?>">Prendre rendez-vous</a>
        <a class="btn btn-outline-light ud-btn" href="tel:<?= h($phoneTel) ?>"><?= h($phone) ?></a>
        <a class="btn btn-outline-light ud-btn" href="mailto:<?= h($email) ?>"><?= h($email) ?></a>
      </div>
    </section>
  </div>
</section>
<?php
$content = ob_get_clean();

$view = [
    'title' => 'YOMBAL KEUR — Immobilier & BTP | Univers Diaspora',
    'meta_description' => 'Programme YOMBAL KEUR : terrains à céder à Yenne Ndoukhoura, Ndayane et Sangalcam. 150 m², apport 0 €, paiement échelonné. Contact : ' . $phone . '.',
    'active' => 'immobilier-btp',
    'content' => $content,
    'ai_context_slug' => 'immobilier-btp',
    'ai_context_title' => 'Immobilier & BTP — YOMBAL KEUR',
];

require __DIR__ . '/_layout.php';
