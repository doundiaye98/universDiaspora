<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/legal.php';

$config = require __DIR__ . '/../config/config.php';
$baseUrl = rtrim($config['app']['base_url'], '/');
$appName = (string)($config['app']['name'] ?? 'Univers Diaspora');
$legal = legal_config($config);
$pub = is_array($legal['publisher'] ?? null) ? $legal['publisher'] : [];
$host = is_array($legal['hosting'] ?? null) ? $legal['hosting'] : [];

ob_start();
?>
<section class="ud-about-hero py-5">
  <div class="container">
    <div class="ud-section-title text-center mb-4">
      <div class="ud-section-kicker">Informations légales</div>
      <h1 class="ud-title mb-2">Mentions légales</h1>
      <div class="ud-subtitle">Informations sur l’éditeur du site, l’hébergement et le cadre juridique. Les données sont à compléter dans le fichier de configuration (section <code class="small">legal</code>).</div>
      <div class="ud-section-divider mt-3" aria-hidden="true"></div>
      <p class="small text-muted mb-0 mt-3">Dernière mise à jour : <?= legal_last_updated_display($legal) ?></p>
    </div>

    <div class="row g-4 justify-content-center">
      <div class="col-12 col-lg-9">
        <div class="ud-surface ud-about-card">
          <p class="ud-about-p">
            Conformément aux dispositions en vigueur en France, les présentes mentions légales identifient l’éditeur du site
            <strong><?= h($appName) ?></strong>, accessible à l’adresse <strong><?= h($baseUrl) ?>/</strong>.
          </p>

          <h2 class="h5 mt-4">1. Éditeur du site</h2>
          <?= legal_publisher_block_html($pub) ?>

          <h2 class="h5 mt-4">2. Hébergement</h2>
          <?= legal_hosting_block_html($host) ?>

          <h2 class="h5 mt-4">3. Propriété intellectuelle</h2>
          <p class="ud-about-p mb-0">
            L’ensemble des contenus présents sur ce site (textes, images, logos, éléments graphiques, structure) est protégé par le droit de la propriété intellectuelle.
            Toute reproduction, représentation ou exploitation non autorisée, totale ou partielle, est interdite sans autorisation écrite préalable de l’éditeur, sauf usage strictement privé ou exceptions prévues par la loi.
          </p>

          <h2 class="h5 mt-4">4. Données personnelles</h2>
          <p class="ud-about-p mb-0">
            Le traitement des données collectées via les formulaires du site est décrit dans la
            <a href="<?= h($baseUrl) ?>/?page=politique-confidentialite">politique de confidentialité</a>.
          </p>

          <h2 class="h5 mt-4">5. Droit applicable</h2>
          <p class="ud-about-p mb-0">
            Les présentes mentions sont régies par le droit français. En cas de litige, et à défaut de résolution amiable,
            les tribunaux français seront seuls compétents, sous réserve des règles de compétence impératives.
          </p>

          <h2 class="h5 mt-4">6. Liens utiles</h2>
          <p class="ud-about-p mb-0">
            <a href="<?= h($baseUrl) ?>/?page=politique-confidentialite">Politique de confidentialité</a>
            <span class="text-muted">&nbsp;·&nbsp;</span>
            <a href="<?= h($baseUrl) ?>/#contact">Contact</a>
          </p>
        </div>
      </div>
    </div>
  </div>
</section>
<?php
$content = ob_get_clean();

$view = [
    'title' => 'Mentions légales — Univers Diaspora',
    'meta_description' => 'Mentions légales du site ' . $appName . ' : éditeur, hébergement, propriété intellectuelle et données personnelles.',
    'active' => 'mentions-legales',
    'content' => $content,
];

require __DIR__ . '/_layout.php';
