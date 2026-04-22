<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/legal.php';

$config = require __DIR__ . '/../config/config.php';
$baseUrl = rtrim($config['app']['base_url'], '/');
$appName = (string)($config['app']['name'] ?? 'Univers Diaspora');
$legal = legal_config($config);
$pub = is_array($legal['publisher'] ?? null) ? $legal['publisher'] : [];
$priv = is_array($legal['privacy'] ?? null) ? $legal['privacy'] : [];

$controllerName = trim((string)($pub['legal_name'] ?? ''));
$controllerAddr = legal_format_address($pub);
$controllerEmail = trim((string)($pub['email'] ?? ''));
$dpoEmail = trim((string)($pub['email_dpo'] ?? ''));
$retention = trim((string)($priv['retention_summary'] ?? ''));
$cookiesCustom = trim((string)($priv['cookies_summary'] ?? ''));
$subproc = trim((string)($priv['subprocessors_summary'] ?? ''));
$usesAnalytics = !empty($priv['uses_audience_measurement']);

ob_start();
?>
<section class="ud-about-hero ud-page-privacy py-3 py-md-4 py-lg-5">
  <div class="container px-3 px-sm-4">
    <nav aria-label="Fil d’Ariane" class="ud-breadcrumb mb-3">
      <a href="<?= h($baseUrl) ?>/">Accueil</a>
      <span class="ud-breadcrumb__sep">/</span>
      <span>Politique de confidentialité</span>
    </nav>

    <div class="ud-section-title text-center mb-4">
      <div class="ud-section-kicker">Protection des données</div>
      <h1 class="ud-title mb-2">Politique de confidentialité</h1>
      <div class="ud-subtitle mx-auto" style="max-width: 42rem;">
        Comment <?= h($appName) ?> traite les données personnelles collectées via ce site. Document à adapter avec votre conseil ou référent conformité selon vos traitements réels.
      </div>
      <div class="ud-section-divider mt-3" aria-hidden="true"></div>
      <p class="small text-muted mb-0 mt-3">Dernière mise à jour : <?= legal_last_updated_display($legal) ?></p>
    </div>

    <div class="row g-3 justify-content-center mb-4 mb-lg-5">
      <div class="col-6 col-lg-3">
        <a class="ud-legal-jump" href="#privacy-responsable">
          <div class="ud-legal-jump__kicker">§ 1</div>
          <div class="ud-legal-jump__title">Responsable</div>
          <p class="ud-legal-jump__text">Qui traite vos données et comment nous contacter.</p>
        </a>
      </div>
      <div class="col-6 col-lg-3">
        <a class="ud-legal-jump" href="#privacy-collecte">
          <div class="ud-legal-jump__kicker">§ 2</div>
          <div class="ud-legal-jump__title">Collecte</div>
          <p class="ud-legal-jump__text">Formulaires, rendez-vous, candidatures et journaux techniques.</p>
        </a>
      </div>
      <div class="col-6 col-lg-3">
        <a class="ud-legal-jump" href="#privacy-finalites">
          <div class="ud-legal-jump__kicker">§ 3</div>
          <div class="ud-legal-jump__title">Finalités</div>
          <p class="ud-legal-jump__text">Pourquoi nous utilisons les données et sur quelles bases.</p>
        </a>
      </div>
      <div class="col-6 col-lg-3">
        <a class="ud-legal-jump" href="#privacy-droits">
          <div class="ud-legal-jump__kicker">§ 7</div>
          <div class="ud-legal-jump__title">Vos droits</div>
          <p class="ud-legal-jump__text">RGPD : accès, rectification, effacement, réclamation CNIL…</p>
        </a>
      </div>
    </div>

    <div class="row g-4 justify-content-center">
      <div class="col-12 col-lg-9">
        <div class="ud-surface ud-about-card">
          <p class="ud-about-p">
            La présente politique décrit les principes appliqués par <strong><?= h($appName) ?></strong> concernant les données personnelles traitées dans le cadre du site
            <strong><?= h($baseUrl) ?>/</strong>. Elle complète les <a href="<?= h($baseUrl) ?>/?page=mentions-legales">mentions légales</a>.
          </p>

          <h2 class="h5 mt-4" id="privacy-responsable">1. Responsable du traitement</h2>
          <p class="ud-about-p mb-2">
            <strong>Identité :</strong> <?= $controllerName !== '' ? h($controllerName) : legal_placeholder() ?><br>
            <strong>Adresse :</strong> <?= $controllerAddr !== '' ? h($controllerAddr) : legal_placeholder() ?><br>
            <strong>Contact général :</strong>
            <?php if ($controllerEmail !== ''): ?>
              <a href="mailto:<?= h($controllerEmail) ?>"><?= h($controllerEmail) ?></a>
            <?php else: ?>
              <?= legal_placeholder() ?>
            <?php endif; ?>
            <br>
            <strong>Contact données personnelles (DPO ou référent, si désigné) :</strong>
            <?php if ($dpoEmail !== ''): ?>
              <a href="mailto:<?= h($dpoEmail) ?>"><?= h($dpoEmail) ?></a>
            <?php elseif ($controllerEmail !== ''): ?>
              <span class="text-muted">Identique au contact général :</span>
              <a href="mailto:<?= h($controllerEmail) ?>"><?= h($controllerEmail) ?></a>
            <?php else: ?>
              <?= legal_placeholder() ?>
            <?php endif; ?>
          </p>

          <h2 class="h5 mt-4" id="privacy-collecte">2. Données collectées</h2>
          <p class="ud-about-p mb-2">Selon les formulaires que vous utilisez sur le site, les données peuvent inclure notamment :</p>
          <ul class="ud-about-list">
            <li><strong>Formulaire de contact :</strong> nom, prénom, adresse e-mail, téléphone, message.</li>
            <li><strong>Demande de rendez-vous :</strong> bureau choisi, date et heure, nom, e-mail, téléphone, message éventuel.</li>
            <li><strong>Candidature (offres / recrutement) :</strong> nom, e-mail, téléphone, message éventuel, curriculum vitæ et lettre de motivation au format PDF.</li>
            <li><strong>Données techniques :</strong> adresse IP et user-agent, collectés de façon limitée lors de l’envoi des formulaires, à des fins de sécurité, de prévention des abus et de preuve en cas de litige.</li>
          </ul>

          <h2 class="h5 mt-4" id="privacy-finalites">3. Finalités et bases légales</h2>
          <ul class="ud-about-list">
            <li><strong>Répondre à votre demande</strong> (contact, rendez-vous, candidature) — exécution de mesures précontractuelles ou contractuelles, et le cas échéant intérêt légitime à gérer la relation.</li>
            <li><strong>Gestion administrative et suivi</strong> — intérêt légitime ou exécution d’un contrat, selon le cas.</li>
            <li><strong>Sécurité du site et lutte contre le spam</strong> — intérêt légitime.</li>
            <li><strong>Obligations légales</strong> — lorsque la loi l’impose (conservation, réponse à une autorité, etc.).</li>
          </ul>
          <p class="ud-about-p small text-muted mb-0">Les bases légales exactes dépendent de votre situation (professionnel / association / particulier) : faites-vous accompagner pour les ajuster.</p>

          <h2 class="h5 mt-4" id="privacy-conservation">4. Durée de conservation</h2>
          <p class="ud-about-p mb-0">
            <?php if ($retention !== ''): ?>
              <?= h($retention) ?>
            <?php else: ?>
              <?= legal_placeholder('Rédigez un paragraphe précis dans legal.privacy.retention_summary (config.local.php), selon vos obligations et votre politique interne.') ?>
            <?php endif; ?>
          </p>

          <h2 class="h5 mt-4" id="privacy-destinataires">5. Destinataires des données</h2>
          <p class="ud-about-p mb-2">
            Les données sont destinées aux personnes habilitées au sein de <?= h($appName) ?>, et le cas échéant à des prestataires de confiance
            (par exemple hébergeur, prestataire d’e-mailing) agissant sur instruction et dans le respect du RGPD.
          </p>
          <?php if ($subproc !== ''): ?>
            <p class="ud-about-p mb-0"><?= nl2br(h($subproc)) ?></p>
          <?php else: ?>
            <p class="ud-about-p mb-0 text-muted small">Vous pouvez lister vos sous-traitants types dans <code>legal.privacy.subprocessors_summary</code> (config.local.php).</p>
          <?php endif; ?>

          <h2 class="h5 mt-4" id="privacy-transferts">6. Transferts hors de l’Union européenne</h2>
          <p class="ud-about-p mb-0">
            Sauf mention contraire de votre part dans vos contrats ou outils, les traitements décrits ici sont en principe réalisés au sein de l’Espace économique européen.
            Si vous utilisez des outils impliquant un transfert hors UE, vous devez l’indiquer et préciser les garanties (clauses types, etc.) — à ajouter ici après analyse.
          </p>

          <h2 class="h5 mt-4" id="privacy-droits">7. Vos droits (RGPD)</h2>
          <p class="ud-about-p mb-0">
            Vous disposez d’un droit d’accès, de rectification, d’effacement, de limitation du traitement, d’opposition et de portabilité lorsque le droit applicable le prévoit.
            Pour exercer vos droits, contactez-nous aux adresses indiquées à la section 1. Vous pouvez également introduire une réclamation auprès de la CNIL :
            <a href="https://www.cnil.fr" target="_blank" rel="noopener noreferrer">www.cnil.fr</a>.
          </p>

          <h2 class="h5 mt-4" id="privacy-cookies">8. Cookies et traceurs</h2>
          <p class="ud-about-p mb-0">
            <?php if ($cookiesCustom !== ''): ?>
              <?= nl2br(h($cookiesCustom)) ?>
            <?php else: ?>
              Des cookies ou traceurs <strong>strictement nécessaires</strong> au fonctionnement du site (par exemple maintien de session pour l’espace d’administration, sécurité) peuvent être déposés sur votre terminal.
              <?php if ($usesAnalytics): ?>
                Des outils de <strong>mesure d’audience</strong> peuvent également être utilisés ; dans ce cas, le consentement préalable doit être recueilli conformément aux recommandations de la CNIL, sauf exemption applicable.
              <?php else: ?>
                Indiquez dans <code>legal.privacy.cookies_summary</code> si vous ajoutez d’autres traceurs (vidéos, statistiques, réseaux sociaux, etc.).
              <?php endif; ?>
            <?php endif; ?>
          </p>

          <h2 class="h5 mt-4" id="privacy-modifications">9. Modifications</h2>
          <p class="ud-about-p mb-0">
            Cette politique peut être mise à jour. La date de « dernière mise à jour » en tête de page reflète la version en ligne.
            En cas de changement substantiel, une information sur le site ou par e-mail peut être prévue selon le contexte.
          </p>

          <p class="ud-about-p mb-0 mt-4">
            <a href="<?= h($baseUrl) ?>/?page=mentions-legales">Mentions légales</a>
            <span class="text-muted">&nbsp;·&nbsp;</span>
            <a href="<?= h($baseUrl) ?>/#contact">Contact</a>
          </p>
        </div>
      </div>
    </div>

    <div class="ud-about-cta ud-surface text-center mt-4 mt-lg-5">
      <h2 class="ud-about-cta__title mb-2">Exercer vos droits ou nous écrire</h2>
      <p class="ud-about-cta__text mx-auto mb-4">
        Pour une demande liée à vos données ou pour toute autre question, privilégiez le formulaire de contact ou les coordonnées indiquées en section 1.
      </p>
      <div class="d-flex flex-wrap justify-content-center gap-2">
        <a class="btn btn-primary ud-btn ud-btn--cta" href="<?= h($baseUrl) ?>/#contact">Formulaire de contact</a>
        <a class="btn btn-outline-primary ud-btn ud-btn--ghost" href="<?= h($baseUrl) ?>/?page=mentions-legales">Mentions légales</a>
        <a class="btn btn-outline-primary ud-btn ud-btn--ghost" href="<?= h($baseUrl) ?>/">Accueil</a>
      </div>
    </div>
  </div>
</section>
<?php
$content = ob_get_clean();

$view = [
    'title' => 'Politique de confidentialité — Univers Diaspora',
    'meta_description' => 'Politique de confidentialité et traitement des données personnelles sur le site ' . $appName . '.',
    'active' => 'politique-confidentialite',
    'content' => $content,
];

require __DIR__ . '/_layout.php';
