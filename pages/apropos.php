<?php
declare(strict_types=1);

ob_start();
?>
<section class="ud-about-hero py-5">
  <div class="container">
    <div class="ud-section-title text-center mb-4">
      <div class="ud-section-kicker">À propos</div>
      <h1 class="ud-title mb-2">À propos</h1>
      <div class="ud-subtitle">Conseil, accompagnement et services pour la diaspora — en France et à l’international.</div>
      <div class="ud-section-divider mt-3" aria-hidden="true"></div>
    </div>

    <div class="row g-4 justify-content-center">
      <div class="col-12 col-lg-9">
        <div class="ud-surface ud-about-card">
          <p class="ud-about-p"> 
          Groupe YOMBAL est une entreprise française créée en mars 2024 et opérant sous le nom commercial d'Univers Diaspora.
            Notre mission est de connecter et d’accompagner les membres de la diaspora africaine, en France et à l’international,
            en leur offrant des services adaptés à leurs besoins.
          </p>

          <p class="ud-about-p mb-3">
          Nous proposons une gamme complète de solutions incluant :
          </p>

          <ul class="ud-about-list">
            <li>Le conseil et l’accompagnement pour les projets personnels et professionnels,</li>
            <li>L’assistance administrative,</li>
            <li>Les services liés à l’immobilier et aux voyages,</li>
            <li>Le soutien aux initiatives entrepreneuriales.</li>
          </ul>

          <p class="ud-about-p">
            Nous nous engageons à offrir un service de qualité, basé sur la proximité et l’écoute,
            afin de permettre à chaque membre de la diaspora de concrétiser ses projets et de profiter pleinement
            des opportunités qui s’offrent à lui.
          </p>

          <p class="ud-about-p mb-0">
            Chez Univers Diaspora, nous croyons en la solidarité, l’entraide et le développement des talents africains,
            et nous mettons tout en œuvre pour créer un environnement propice à la réussite et à l’épanouissement de notre communauté.
          </p>
        </div>
      </div>
    </div>
  </div>
</section>
<?php
$content = ob_get_clean();

$view = [
    'title' => 'À propos',
    'active' => 'apropos',
    'content' => $content,
];

require __DIR__ . '/_layout.php';

