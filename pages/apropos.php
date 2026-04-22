<?php
declare(strict_types=1);

$config = require __DIR__ . '/../config/config.php';
$baseUrl = rtrim($config['app']['base_url'], '/');

ob_start();
?>
<section class="ud-about-page ud-about-hero py-3 py-md-4 py-lg-5">
  <div class="container px-3 px-sm-4">
    <div class="row align-items-center g-4 g-lg-5 mb-4 mb-lg-5">
      <div class="col-12 col-lg-6">
        <div class="ud-section-title text-center text-lg-start mb-0">
          <div class="ud-section-kicker">Univers Diaspora</div>
          <h1 class="ud-title mb-2">Qui sommes-nous ?</h1>
          <div class="ud-subtitle mx-auto mx-lg-0" style="max-width: 32rem;">
            Conseil, accompagnement et services pensés pour la diaspora — avec une approche humaine, concrète et sur mesure.
          </div>
          <div class="ud-section-divider mt-3 mx-auto mx-lg-0" aria-hidden="true"></div>
        </div>
      </div>
      <div class="col-12 col-lg-6">
        <div class="ud-about-intro__visual text-center">
          <div class="ud-about-intro__frame mx-auto">
            <img
              class="ud-about-intro__img img-fluid"
              src="<?= h(ud_public_asset_url('img/logo-univers-diaspora.jpg', $baseUrl)) ?>"
              alt="Logo Univers Diaspora"
              width="420"
              height="420"
              loading="eager"
            >
          </div>
        </div>
      </div>
    </div>

    <div class="ud-about-statband row g-3 g-md-4 mb-4 mb-lg-5">
      <div class="col-12 col-md-4">
        <div class="ud-about-stat ud-surface h-100 text-center text-md-start">
          <div class="ud-about-stat__label">Depuis</div>
          <div class="ud-about-stat__value">Mars 2024</div>
          <p class="ud-about-stat__hint mb-0">Une structure jeune, agile et tournée vers l’avenir.</p>
        </div>
      </div>
      <div class="col-12 col-md-4">
        <div class="ud-about-stat ud-surface h-100 text-center text-md-start">
          <div class="ud-about-stat__label">Notre méthode</div>
          <div class="ud-about-stat__value">Écoute &amp; proximité</div>
          <p class="ud-about-stat__hint mb-0">Chaque dossier est pris au sérieux, avec du temps et de la clarté.</p>
        </div>
      </div>
      <div class="col-12 col-md-4">
        <div class="ud-about-stat ud-surface h-100 text-center text-md-start">
          <div class="ud-about-stat__label">Ambition</div>
          <div class="ud-about-stat__value">Projets qui avancent</div>
          <p class="ud-about-stat__hint mb-0">Personnel, administratif, immobilier, voyages ou entrepreneuriat.</p>
        </div>
      </div>
    </div>

    <div class="row g-4 justify-content-center mb-4 mb-lg-5">
      <div class="col-12">
        <h2 class="ud-about-section-title text-center mb-4">Ce qui nous guide</h2>
        <div class="row g-4">
          <div class="col-12 col-md-4">
            <article class="ud-about-pillar ud-surface h-100">
              <div class="ud-about-pillar__icon" aria-hidden="true">01</div>
              <h3 class="ud-about-pillar__title">Notre mission</h3>
              <p class="ud-about-pillar__text mb-0">
                Relier et accompagner les membres de la diaspora avec des services adaptés à la réalité de leurs projets,
                là où ils vivent comme là où ils construisent l’avenir.
              </p>
            </article>
          </div>
          <div class="col-12 col-md-4">
            <article class="ud-about-pillar ud-surface h-100">
              <div class="ud-about-pillar__icon" aria-hidden="true">02</div>
              <h3 class="ud-about-pillar__title">Nos valeurs</h3>
              <p class="ud-about-pillar__text mb-0">
                Solidarité, entraide et mise en valeur des talents : nous croyons qu’une communauté forte naît de gestes concrets
                et de relations de confiance.
              </p>
            </article>
          </div>
          <div class="col-12 col-md-4">
            <article class="ud-about-pillar ud-surface h-100">
              <div class="ud-about-pillar__icon" aria-hidden="true">03</div>
              <h3 class="ud-about-pillar__title">Qualité de service</h3>
              <p class="ud-about-pillar__text mb-0">
                Des réponses claires, un suivi attentif et le souci du détail — pour que chaque étape soit comprise et maîtrisée.
              </p>
            </article>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-4 justify-content-center mb-4 mb-lg-5">
      <div class="col-12 col-xl-10">
        <div class="ud-about-highlight">
          <p class="ud-about-highlight__lead mb-0">
            <strong>Univers Diaspora</strong>, c’est avant tout un partenaire de confiance : nous vous aidons à structurer vos démarches,
            à anticiper les questions et à avancer sereinement, sans vous laisser seul face à la complexité.
          </p>
        </div>
      </div>
    </div>

    <div class="row g-4 justify-content-center mb-4 mb-lg-5">
      <div class="col-12 col-lg-10">
        <h2 class="ud-about-section-title text-center mb-3">Ce que nous proposons</h2>
        <p class="text-center ud-about-lead mx-auto mb-4" style="max-width: 40rem;">
          Une gamme complète de solutions pour vous accompagner au quotidien et dans vos grands projets.
        </p>
        <div class="ud-about-card ud-surface">
          <ul class="ud-about-gridlist row g-3 list-unstyled mb-4">
            <li class="col-12 col-sm-6">
              <div class="ud-about-gridlist__item">
                <span class="ud-about-gridlist__dot" aria-hidden="true"></span>
                <span>Conseil et accompagnement pour vos projets personnels et professionnels</span>
              </div>
            </li>
            <li class="col-12 col-sm-6">
              <div class="ud-about-gridlist__item">
                <span class="ud-about-gridlist__dot" aria-hidden="true"></span>
                <span>Assistance administrative</span>
              </div>
            </li>
            <li class="col-12 col-sm-6">
              <div class="ud-about-gridlist__item">
                <span class="ud-about-gridlist__dot" aria-hidden="true"></span>
                <span>Services liés à l’immobilier et aux voyages</span>
              </div>
            </li>
            <li class="col-12 col-sm-6">
              <div class="ud-about-gridlist__item">
                <span class="ud-about-gridlist__dot" aria-hidden="true"></span>
                <span>Soutien aux initiatives entrepreneuriales</span>
              </div>
            </li>
          </ul>
          <p class="ud-about-p mb-0">
            Nous nous engageons à offrir un service de qualité, basé sur la proximité et l’écoute,
            afin de permettre à chaque membre de la diaspora de concrétiser ses projets et de profiter pleinement
            des opportunités qui s’offrent à lui. Chez Univers Diaspora, nous mettons tout en œuvre pour créer un environnement
            propice à la réussite et à l’épanouissement de notre communauté.
          </p>
        </div>
      </div>
    </div>

    <div class="ud-about-cta ud-surface text-center">
      <h2 class="ud-about-cta__title mb-2">Envie d’aller plus loin ?</h2>
      <p class="ud-about-cta__text mx-auto mb-4">
        Découvrez nos services, rencontrez l’équipe ou écrivez-nous : nous répondons avec attention à chaque message.
      </p>
      <div class="d-flex flex-wrap justify-content-center gap-2">
        <a class="btn btn-primary ud-btn ud-btn--cta" href="<?= h($baseUrl) ?>/#services">Découvrir nos services</a>
        <a class="btn btn-outline-primary ud-btn ud-btn--ghost" href="<?= h($baseUrl) ?>/?page=equipe">Notre équipe</a>
        <a class="btn btn-outline-primary ud-btn ud-btn--ghost" href="<?= h($baseUrl) ?>/#contact">Nous contacter</a>
      </div>
    </div>
  </div>
</section>
<?php
$content = ob_get_clean();

$view = [
    'title' => 'À propos — Univers Diaspora',
    'meta_description' => 'Univers Diaspora : mission, valeurs et accompagnement sur mesure pour la diaspora — conseil, administratif, immobilier, voyages et entrepreneuriat.',
    'active' => 'apropos',
    'content' => $content,
];

require __DIR__ . '/_layout.php';
