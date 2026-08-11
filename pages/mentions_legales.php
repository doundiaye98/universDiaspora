<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/legal.php';

$config = require __DIR__ . '/../config/config.php';
$baseUrl = ud_site_base_url();
$appName = (string)($config['app']['name'] ?? 'Univers Diaspora');
$legal = legal_config($config);
$pub = is_array($legal['publisher'] ?? null) ? $legal['publisher'] : [];
$host = is_array($legal['hosting'] ?? null) ? $legal['hosting'] : [];

ob_start();
?>
<section class="ud-about-hero ud-page-legal py-3 py-md-4 py-lg-5">
  <div class="container px-3 px-sm-4">
    <nav aria-label="Fil d’Ariane" class="ud-breadcrumb mb-3">
      <a href="<?= h($baseUrl) ?>/">Accueil</a>
      <span class="ud-breadcrumb__sep">/</span>
      <span>Mentions légales</span>
    </nav>

    <div class="ud-section-title text-center mb-4">
      <div class="ud-section-kicker">Informations légales</div>
      <h1 class="ud-title mb-2">Mentions légales</h1>
      <div class="ud-subtitle mx-auto" style="max-width: 40rem;">
        Transparence sur l’éditeur du site, l’hébergement et le cadre juridique. Complétez les champs dans la configuration
        (<code class="small">legal</code>) pour personnaliser ce texte.
      </div>
      <div class="ud-section-divider mt-3" aria-hidden="true"></div>
      <p class="small text-muted mb-0 mt-3">Dernière mise à jour : <?= legal_last_updated_display($legal) ?></p>
    </div>

    <div class="row g-3 justify-content-center mb-4 mb-lg-5">
      <div class="col-12 col-md-4">
        <a class="ud-legal-jump" href="#legal-editeur">
          <div class="ud-legal-jump__kicker">Section 1</div>
          <div class="ud-legal-jump__title">Éditeur du site</div>
          <p class="ud-legal-jump__text">Identité et coordonnées de la structure responsable du site.</p>
        </a>
      </div>
      <div class="col-12 col-md-4">
        <a class="ud-legal-jump" href="#legal-hebergement">
          <div class="ud-legal-jump__kicker">Section 2</div>
          <div class="ud-legal-jump__title">Hébergement</div>
          <p class="ud-legal-jump__text">Informations sur l’hébergeur et la localisation des données techniques.</p>
        </a>
      </div>
      <div class="col-12 col-md-4">
        <a class="ud-legal-jump" href="#legal-donnees">
          <div class="ud-legal-jump__kicker">Section 4</div>
          <div class="ud-legal-jump__title">Données personnelles</div>
          <p class="ud-legal-jump__text">Renvoi vers la politique de confidentialité et vos droits.</p>
        </a>
      </div>
      <div class="col-12 col-md-4">
        <a class="ud-legal-jump" href="#legal-conception">
          <div class="ud-legal-jump__kicker">Section 6</div>
          <div class="ud-legal-jump__title">Conception &amp; développement</div>
          <p class="ud-legal-jump__text">Site conçu, développé et maintenu en interne par Univers Diaspora.</p>
        </a>
      </div>
    </div>

    <div class="row g-4 justify-content-center">
      <div class="col-12 col-lg-9">
        <div class="ud-surface ud-about-card">
          <p class="ud-about-p">
            Conformément aux dispositions en vigueur en France, les présentes mentions légales identifient l’éditeur du site
            <strong><?= h($appName) ?></strong>, accessible à l’adresse <strong><?= h($baseUrl) ?>/</strong>.
          </p>

          <h2 class="h5 mt-4" id="legal-editeur">1. Éditeur du site</h2>
          <?= legal_publisher_block_html($pub) ?>

          <h2 class="h5 mt-4" id="legal-hebergement">2. Hébergement</h2>
          <?= legal_hosting_block_html($host) ?>

          <h2 class="h5 mt-4" id="legal-propriete">3. Propriété intellectuelle</h2>
          <p class="ud-about-p mb-0">
            L’ensemble des contenus présents sur ce site (textes, images, logos, éléments graphiques, structure) est protégé par le droit de la propriété intellectuelle.
            Toute reproduction, représentation ou exploitation non autorisée, totale ou partielle, est interdite sans autorisation écrite préalable de l’éditeur, sauf usage strictement privé ou exceptions prévues par la loi.
          </p>

          <h2 class="h5 mt-4" id="legal-donnees">4. Données personnelles</h2>
          <p class="ud-about-p mb-0">
            Le traitement des données collectées via les formulaires du site est décrit dans la
            <a href="<?= h($baseUrl) ?>/?page=politique-confidentialite">politique de confidentialité</a>.
          </p>

          <h2 class="h5 mt-4" id="legal-droit">5. Droit applicable</h2>
          <p class="ud-about-p mb-0">
            Les présentes mentions sont régies par le droit français. En cas de litige, et à défaut de résolution amiable,
            les tribunaux français seront seuls compétents, sous réserve des règles de compétence impératives.
          </p>

          <h2 class="h5 mt-4" id="legal-conception">6. Conception et développement</h2>
          <p class="ud-about-p mb-0">
            Le présent site internet, son arborescence, sa charte graphique, son interface, ses interactions et l’ensemble du code source applicatif (front‑end et back‑end)
            ont été <strong>conçus, développés, intégrés et déployés en interne par l’équipe d’Univers Diaspora</strong>
            (« Studio Univers Diaspora »). Aucune prestation de conception ou de développement n’a été sous‑traitée.
            Les éventuels composants tiers (frameworks, bibliothèques, polices typographiques, cartographie) sont utilisés conformément à leurs licences respectives.
          </p>

          <h2 class="h5 mt-4" id="legal-liens">7. Liens utiles</h2>
          <p class="ud-about-p mb-0">
            <a href="<?= h($baseUrl) ?>/?page=politique-confidentialite">Politique de confidentialité</a>
            <span class="text-muted">&nbsp;·&nbsp;</span>
            <a href="<?= h($baseUrl) ?>/#contact">Contact</a>
          </p>
        </div>
      </div>
    </div>

    <div class="ud-about-cta ud-surface text-center mt-4 mt-lg-5">
      <h2 class="ud-about-cta__title mb-2">Site vitrine Univers Diaspora</h2>
      <p class="ud-about-cta__text mx-auto mb-4">
        Pour toute question sur nos services ou une demande liée à vos données, contactez-nous depuis l’accueil du site.
      </p>
      <div class="d-flex flex-wrap justify-content-center gap-2">
        <a class="btn btn-primary ud-btn ud-btn--cta" href="<?= h($baseUrl) ?>/#contact">Contact</a>
        <a class="btn btn-outline-primary ud-btn ud-btn--ghost" href="<?= h($baseUrl) ?>/?page=politique-confidentialite">Confidentialité</a>
        <a class="btn btn-outline-primary ud-btn ud-btn--ghost" href="<?= h($baseUrl) ?>/">Accueil</a>
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
