<?php
declare(strict_types=1);

/**
 * Volets détaillés par service.
 *
 * Chaque entrée correspond à un slug de `data/services.php`. Les volets
 * apparaissent à la fois comme boutons d'ancre dans le hero et comme
 * sections détaillées dans le corps de la page service.
 *
 * Format de chaque volet :
 *  - id    : ancre HTML stable (sans #), unique sur la page
 *  - label : intitulé court (réutilise généralement le bullet existant)
 *  - lead  : phrase d'accroche (1 ligne)
 *  - text  : description détaillée (2 à 4 phrases, ton conseil/cadrage)
 */

return [

    'conseils-accompagnements' => [
        [
            'id' => 'conseils-coaching',
            'label' => 'Coaching personnel',
            'lead' => 'Un cadre clair pour avancer sur vos priorités personnelles ou professionnelles.',
            'text' => 'Nous prenons le temps d’écouter votre situation, de poser des objectifs réalistes et de définir des étapes concrètes. Le coaching reste confidentiel et adapté à votre rythme.',
        ],
        [
            'id' => 'conseils-suivi-projets',
            'label' => 'Suivi projets',
            'lead' => 'Garder le cap sur vos projets, sans vous disperser.',
            'text' => 'Nous structurons les jalons, identifions les points de blocage et organisons des points de suivi réguliers. Vous gardez la main sur les décisions, nous facilitons l’avancement.',
        ],
        [
            'id' => 'conseils-orientation-clients',
            'label' => 'Orientation clients',
            'lead' => 'Trouver le bon interlocuteur, au bon moment.',
            'text' => 'Selon votre besoin, nous vous orientons vers les services internes adaptés ou vers nos partenaires de confiance. L’objectif est de vous éviter les détours inutiles.',
        ],
        [
            'id' => 'conseils-assistance-privee',
            'label' => 'Assistance privée',
            'lead' => 'Un appui discret pour vos démarches sensibles.',
            'text' => 'Pour les sujets personnels, familiaux ou patrimoniaux, nous proposons un cadre confidentiel, un suivi par référent unique et une coordination avec les professionnels concernés.',
        ],
        [
            'id' => 'conseils-experts',
            'label' => 'Conseils experts',
            'lead' => 'Des éclairages métiers pour décider en connaissance de cause.',
            'text' => 'Nous mobilisons notre réseau d’experts (juridique, fiscal, immobilier, business) pour vous donner une lecture claire de votre situation avant toute décision engageante.',
        ],
    ],

    'immobilier-btp' => [
        [
            'id' => 'immobilier-achats',
            'label' => 'Achats immobiliers',
            'lead' => 'Sécuriser votre projet d’acquisition : du cadrage du besoin à la signature.',
            'text' => 'Nous vous aidons à structurer votre budget, à rassembler les pièces utiles et à comprendre les étapes (offre, compromis, acte, financement le cas échéant). Vous gardez la visibilité sur les décisions à prendre et les délais à anticiper.',
        ],
        [
            'id' => 'immobilier-ventes',
            'label' => 'Ventes immobilières',
            'lead' => 'Mettre votre bien en valeur et sécuriser la vente.',
            'text' => 'Accompagnement pour préparer le dossier de vente, clarifier les obligations et suivre les étapes avec les interlocuteurs concernés. Nous restons sur une logique de conseil et d’organisation des démarches.',
        ],
        [
            'id' => 'immobilier-locations',
            'label' => 'Locations logements',
            'lead' => 'Location : cadre, documents et bonnes pratiques.',
            'text' => 'Aide à la lecture du marché locatif, préparation des pièces (bailleur ou locataire) et compréhension des formalités. Chaque situation est traitée au cas par cas lors d’un premier échange.',
        ],
        [
            'id' => 'immobilier-travaux',
            'label' => 'Travaux constructions',
            'lead' => 'Structurer un projet de construction ou d’extension.',
            'text' => 'Nous vous orientons sur la séquence des études, les démarches à anticiper et la coordination des pièces administratives liées au chantier. Les prestations techniques restent du ressort des professionnels du BTP ; notre rôle est de sécuriser le parcours global.',
        ],
        [
            'id' => 'immobilier-renovations',
            'label' => 'Rénovations bâtiments',
            'lead' => 'Rénovation : cadrage, devis et pilotage des étapes.',
            'text' => 'Accompagnement pour définir le périmètre des travaux, comparer les devis de façon éclairée et suivre les étapes administratives utiles. Nous privilégions la clarté et la traçabilité pour limiter les imprévus.',
        ],
    ],

    'voyages' => [
        [
            'id' => 'voyages-billets-avion',
            'label' => "Billets d'avions",
            'lead' => 'Trouver le bon vol au bon prix, sans y passer des heures.',
            'text' => 'Nous comparons les options compatibles avec vos contraintes (dates, escales, bagages, budget) et vous présentons les meilleures combinaisons. La réservation reste à votre nom, en toute transparence.',
        ],
        [
            'id' => 'voyages-hotels',
            'label' => 'Réservations hôtels',
            'lead' => 'Un hébergement adapté à votre séjour et à votre budget.',
            'text' => 'Nous sélectionnons les hôtels selon le quartier, le confort et les avis vérifiés. Idéal pour les déplacements professionnels, familiaux ou les voyages au pays.',
        ],
        [
            'id' => 'voyages-sejours',
            'label' => 'Séjours organisés',
            'lead' => 'Un séjour clé en main, pensé pour la diaspora.',
            'text' => 'Nous proposons des formules incluant transport, hébergement et activités, en lien avec nos partenaires locaux. Vous voyagez sereinement, avec un interlocuteur unique avant et pendant le séjour.',
        ],
        [
            'id' => 'voyages-circuits',
            'label' => 'Circuits touristiques',
            'lead' => 'Découvrir une région avec un programme structuré.',
            'text' => 'Circuits thématiques (culture, gastronomie, nature, retour aux sources) avec un parcours préparé en amont. Vous profitez du voyage, la logistique est cadrée.',
        ],
        [
            'id' => 'voyages-bus',
            'label' => 'Transports bus',
            'lead' => 'Trajets longue distance et navettes organisées.',
            'text' => 'Information sur les liaisons en autocar, comparaison des opérateurs et appui à la réservation. Une alternative économique et pratique pour certains trajets.',
        ],
    ],

    'creation-gestion-d-entreprises' => [
        [
            'id' => 'entreprises-creation',
            'label' => "Création d'entreprise",
            'lead' => 'Choisir la bonne structure et démarrer sur des bases solides.',
            'text' => 'Cadrage du projet, comparaison des statuts (auto‑entreprise, SASU, SARL, etc.) et accompagnement dans les démarches d’immatriculation. Nous travaillons en lien avec les professionnels habilités si besoin.',
        ],
        [
            'id' => 'entreprises-gestion-comptable',
            'label' => 'Gestion comptable',
            'lead' => 'Un suivi clair de votre activité au quotidien.',
            'text' => 'Mise en place d’outils simples, organisation des pièces et coordination avec votre expert‑comptable. Vous restez maître des chiffres, nous facilitons leur lisibilité.',
        ],
        [
            'id' => 'entreprises-administratif',
            'label' => 'Démarches administratives',
            'lead' => 'Tenir vos obligations sans vous noyer dans la paperasse.',
            'text' => 'Préparation des dossiers récurrents, suivi des échéances et points de coordination réguliers. Vous gagnez du temps et limitez les oublis.',
        ],
        [
            'id' => 'entreprises-juridique',
            'label' => 'Conseils juridiques',
            'lead' => 'Comprendre vos droits et obligations avant d’engager une décision.',
            'text' => 'Nous vous aidons à formuler la question juridique et à identifier le bon interlocuteur (avocat, notaire, expert‑comptable). Notre rôle est d’organiser et d’éclairer, pas de remplacer un professionnel du droit.',
        ],
        [
            'id' => 'entreprises-developpement',
            'label' => 'Développement business',
            'lead' => 'Faire grandir votre activité avec une feuille de route claire.',
            'text' => 'Diagnostic rapide, priorisation des actions et suivi des indicateurs clés. Nous restons pragmatiques : ce qui compte, c’est ce qui fait avancer votre chiffre d’affaires.',
        ],
    ],

    'transports' => [
        [
            'id' => 'transports-achat-vehicules',
            'label' => 'Achat véhicules',
            'lead' => 'Acheter un véhicule en limitant les mauvaises surprises.',
            'text' => 'Aide à la recherche selon votre besoin et votre budget, vérification des points clés du dossier et accompagnement dans les démarches administratives associées.',
        ],
        [
            'id' => 'transports-vente-vehicules',
            'label' => 'Ventes véhicules',
            'lead' => 'Vendre votre véhicule dans un cadre clair.',
            'text' => 'Préparation du dossier de vente, conseils sur l’estimation et accompagnement pour la cession en règle. Vous restez décisionnaire à chaque étape.',
        ],
        [
            'id' => 'transports-location',
            'label' => 'Location voiture',
            'lead' => 'Trouver une location adaptée à votre déplacement.',
            'text' => 'Comparaison d’offres selon la durée, le type de véhicule et la zone, avec lecture attentive des conditions (kilométrage, assurance, franchise).',
        ],
        [
            'id' => 'transports-demenagement',
            'label' => 'Déménagement pro',
            'lead' => 'Organiser un déménagement sans stress.',
            'text' => 'Mise en relation avec des prestataires sérieux, comparaison des devis et suivi du planning. Pour les déménagements internationaux, nous vous orientons sur les formalités utiles.',
        ],
        [
            'id' => 'transports-colis',
            'label' => 'Envois colis',
            'lead' => 'Envoyer un colis en France ou à l’international.',
            'text' => 'Information sur les options d’expédition, préparation du colis et suivi jusqu’à destination. Nous restons attentifs aux contraintes douanières selon les pays.',
        ],
    ],

    'assistances-administratives' => [
        [
            'id' => 'admin-depots-dossiers',
            'label' => 'Dépôts dossiers',
            'lead' => 'Préparer et déposer un dossier complet, du premier coup.',
            'text' => 'Nous vérifions la liste des pièces, leur conformité et les délais. Le dépôt se fait dans le respect de la procédure de l’administration concernée.',
        ],
        [
            'id' => 'admin-redaction-documents',
            'label' => 'Rédaction documents',
            'lead' => 'Rédiger des courriers et documents clairs et bien formulés.',
            'text' => 'Lettres administratives, attestations, recours simples : nous vous aidons à formuler une demande lisible et structurée, en évitant les formulations à risque.',
        ],
        [
            'id' => 'admin-rendez-vous',
            'label' => 'Prises rendez-vous',
            'lead' => 'Obtenir un rendez‑vous auprès des bons interlocuteurs.',
            'text' => 'Aide à la prise de rendez‑vous (préfecture, mairie, organismes sociaux, banque, santé) et préparation des documents à apporter le jour J.',
        ],
        [
            'id' => 'admin-accompagnement',
            'label' => 'Accompagnement officiel',
            'lead' => 'Être accompagné lors d’un rendez‑vous important.',
            'text' => 'Sur demande, nous pouvons vous accompagner ou préparer un brief précis avant un rendez‑vous administratif sensible, dans le respect des règles de chaque organisme.',
        ],
        [
            'id' => 'admin-formalites',
            'label' => 'Aide formalités',
            'lead' => 'Comprendre une formalité avant de s’engager.',
            'text' => 'Nous expliquons les formalités, leurs implications et les pièces requises, pour que vous décidiez en connaissance de cause. Les actes engageants restent du ressort des professionnels habilités.',
        ],
    ],

    'formations-emplois' => [
        [
            'id' => 'formations-orientation',
            'label' => 'Orientation scolaire',
            'lead' => 'Aider à choisir une voie cohérente avec son profil.',
            'text' => 'Échange avec l’élève (et la famille) pour clarifier les centres d’intérêt, les contraintes et les options réalistes. Nous présentons les filières sans imposer un choix.',
        ],
        [
            'id' => 'formations-pro',
            'label' => 'Formation pro',
            'lead' => 'Identifier la formation utile à votre projet.',
            'text' => 'Nous vous aidons à comparer les programmes, à comprendre les financements possibles (CPF, employeur, organismes) et à anticiper la charge de travail réelle.',
        ],
        [
            'id' => 'formations-emploi',
            'label' => 'Insertion emploi',
            'lead' => 'Reprendre une recherche d’emploi structurée.',
            'text' => 'Diagnostic du parcours, plan d’action hebdomadaire, ciblage des entreprises et préparation aux entretiens. Nous restons concrets et orientés résultats.',
        ],
        [
            'id' => 'formations-cv',
            'label' => 'Aide CV',
            'lead' => 'Un CV clair, lisible et honnête.',
            'text' => 'Mise en forme, hiérarchisation des expériences et formulation des compétences. Le CV reste fidèle à votre parcours, sans embellissement trompeur.',
        ],
        [
            'id' => 'formations-coaching',
            'label' => 'Coaching carrière',
            'lead' => 'Décider de la prochaine étape de votre carrière.',
            'text' => 'Bilan rapide, scénarios possibles (évolution interne, mobilité, reconversion) et plan d’action. Vous gardez la décision, nous structurons la réflexion.',
        ],
    ],

    'services-a-la-personne' => [
        [
            'id' => 'personne-conciergerie',
            'label' => 'Conciergerie privée',
            'lead' => 'Déléguer les tâches du quotidien à un référent de confiance.',
            'text' => 'Réservations, démarches courantes, coordination de prestataires : nous prenons en charge ce qui peut l’être pour vous libérer du temps utile.',
        ],
        [
            'id' => 'personne-aide-menagere',
            'label' => 'Aide ménagère',
            'lead' => 'Mettre en place un soutien ménager régulier.',
            'text' => 'Mise en relation avec des intervenants sérieux, définition d’un planning adapté et suivi de la prestation. Toujours dans un cadre déclaré et sécurisé.',
        ],
        [
            'id' => 'personne-evenements',
            'label' => 'Organisation événements',
            'lead' => 'Préparer un événement familial ou privé sereinement.',
            'text' => 'Aide à la définition du besoin (lieu, prestataires, budget), comparaison des devis et coordination le jour J selon votre niveau d’implication souhaité.',
        ],
        [
            'id' => 'personne-courses',
            'label' => 'Courses livrées',
            'lead' => 'Recevoir vos courses sans bouger de chez vous.',
            'text' => 'Service de courses ponctuel ou régulier, avec liste personnalisée et livraison à domicile. Pratique pour les personnes actives, en mobilité ou à mobilité réduite.',
        ],
        [
            'id' => 'personne-domicile',
            'label' => 'Assistance domicile',
            'lead' => 'Un appui pour les gestes du quotidien.',
            'text' => 'Petits travaux, accompagnement aux rendez‑vous, aide administrative à domicile : nous coordonnons des intervenants adaptés à votre situation et à votre budget.',
        ],
    ],

    'assurances-credits' => [
        [
            'id' => 'assurances-credit-bancaire',
            'label' => 'Crédit bancaire',
            'lead' => 'Préparer sereinement une demande de crédit auprès d’un professionnel habilité.',
            'text' => 'Univers Diaspora n’est ni banque, ni courtier en opérations bancaires (IOBSP) : nous n’apportons aucun conseil de financement réglementé. Notre rôle est de vous aider à organiser votre dossier (pièces, budget, lecture des indicateurs : taux, durée, mensualité, coût total) et à vous mettre en relation avec un établissement bancaire ou un courtier IOBSP enregistré à l’ORIAS, qui restera votre seul interlocuteur pour toute proposition de crédit.',
        ],
        [
            'id' => 'assurances-sante',
            'label' => 'Assurance santé',
            'lead' => 'Mieux comprendre votre couverture santé avant de souscrire.',
            'text' => 'Nous vous aidons à lire vos garanties existantes, à formuler vos besoins (consultations, hospitalisation, optique, dentaire) et à préparer vos questions. La souscription, le conseil personnalisé et la comparaison contractuelle relèvent d’un courtier en assurance ou d’une compagnie immatriculée à l’ORIAS.',
        ],
        [
            'id' => 'assurances-auto',
            'label' => 'Assurance auto',
            'lead' => 'Lire votre contrat auto en toute clarté.',
            'text' => 'Explication pédagogique des garanties habituelles (responsabilité civile, vol, bris de glace, tous risques), des franchises et des exclusions courantes. Univers Diaspora ne vend ni ne distribue de contrats d’assurance ; pour souscrire, modifier ou résilier, vous êtes orienté(e) vers un assureur ou un courtier habilité.',
        ],
        [
            'id' => 'assurances-courtage',
            'label' => 'Courtage financier',
            'lead' => 'Mise en relation avec des courtiers indépendants enregistrés.',
            'text' => 'Selon votre projet (immobilier, regroupement de crédits, professionnel, assurance), nous vous orientons vers des courtiers tiers, immatriculés à l’ORIAS, qui prennent le temps d’expliquer leurs honoraires et leurs offres. Univers Diaspora ne perçoit pas de commission de souscription et n’endosse aucune responsabilité sur les contrats finalement signés.',
        ],
        [
            'id' => 'assurances-budget',
            'label' => 'Conseils budget',
            'lead' => 'Reprendre la main sur votre budget personnel.',
            'text' => 'Cadrage des recettes et dépenses, identification des postes ajustables et plan simple sur quelques mois. Une démarche d’organisation et de pédagogie : nous ne vendons aucun produit financier et n’avons pas vocation à remplacer un conseiller bancaire ou un conseiller en gestion de patrimoine (CGP) lorsque la situation le justifie.',
        ],
    ],

    'informatiques' => [
        [
            'id' => 'info-creation-sites',
            'label' => 'Création sites',
            'lead' => 'Un site web utile, sobre et facile à maintenir.',
            'text' => 'Cadrage des objectifs, choix de la solution adaptée (vitrine, boutique, prise de rendez‑vous) et accompagnement dans la mise en ligne. Nous privilégions ce qui sert vraiment votre activité.',
        ],
        [
            'id' => 'info-assistance',
            'label' => 'Assistance technique',
            'lead' => 'Une aide rapide quand quelque chose coince.',
            'text' => 'Diagnostic à distance ou sur site, orientation vers la bonne action et explications claires. Vous comprenez ce qui a été fait et pourquoi.',
        ],
        [
            'id' => 'info-reparations-pc',
            'label' => 'Réparations PC',
            'lead' => 'Donner une seconde vie à vos ordinateurs.',
            'text' => 'Nettoyage, optimisation, remplacement de pièces ou réinstallation système. Nous vous indiquons quand une réparation est pertinente, et quand un changement est plus raisonnable.',
        ],
        [
            'id' => 'info-reseau',
            'label' => 'Maintenance réseau',
            'lead' => 'Un réseau stable pour travailler sereinement.',
            'text' => 'Audit rapide du réseau (Wi‑Fi, accès Internet, équipements), petites corrections et bonnes pratiques de sécurité. Adapté aux petites structures et indépendants.',
        ],
        [
            'id' => 'info-digitalisation',
            'label' => 'Digitalisation entreprise',
            'lead' => 'Mettre du numérique là où c’est utile.',
            'text' => 'Identification des tâches automatisables, sélection d’outils adaptés et accompagnement à la prise en main. Pas de complexité inutile : nous outillons ce qui fait gagner du temps.',
        ],
    ],

    'supermarket' => [
        [
            'id' => 'supermarket-alimentaires',
            'label' => 'Produits alimentaires',
            'lead' => 'Une sélection de produits du quotidien.',
            'text' => 'Référentiel de produits courants et spécialités utiles à la diaspora. Le service est en cours de mise en place : la liste s’étoffera progressivement.',
        ],
        [
            'id' => 'supermarket-cosmetiques',
            'label' => 'Produits cosmétiques',
            'lead' => 'Hygiène et beauté, marques accessibles.',
            'text' => 'Soins, hygiène et beauté : nous prévoyons une gamme accessible et lisible. Vos suggestions sont les bienvenues pour orienter le futur catalogue.',
        ],
        [
            'id' => 'supermarket-livraison',
            'label' => 'Livraison domicile',
            'lead' => 'Recevoir vos achats chez vous, simplement.',
            'text' => 'Livraison à domicile selon les zones desservies, avec créneaux clairs et suivi de commande. Les détails opérationnels seront communiqués au lancement.',
        ],
        [
            'id' => 'supermarket-prix',
            'label' => 'Prix abordables',
            'lead' => 'Une politique de prix lisible et raisonnable.',
            'text' => 'Nous travaillons à une grille tarifaire transparente, sans frais cachés. Le rapport qualité/prix sera un critère central du référencement.',
        ],
        [
            'id' => 'supermarket-en-ligne',
            'label' => 'Boutique en ligne',
            'lead' => 'Une boutique simple à utiliser.',
            'text' => 'La boutique en ligne est en préparation. Vous serez informé de l’ouverture et des premières fonctionnalités via votre compte ou notre formulaire de contact.',
        ],
    ],

    'bien-d-autres-services' => [
        [
            'id' => 'autres-fetes',
            'label' => 'Organisation fêtes',
            'lead' => 'Une fête réussie, sans surcharge mentale.',
            'text' => 'Préparation du brief, recherche de prestataires fiables, comparaison des devis et coordination le jour J selon votre niveau d’implication.',
        ],
        [
            'id' => 'autres-coiffure-beaute',
            'label' => 'Coiffure beauté',
            'lead' => 'Mise en relation avec des professionnels de confiance.',
            'text' => 'Coiffure, esthétique, beauté : nous orientons vers des professionnels sérieux, dans le respect de votre budget et de vos préférences.',
        ],
        [
            'id' => 'autres-traiteur',
            'label' => 'Service traiteur',
            'lead' => 'Un traiteur adapté à l’événement et au budget.',
            'text' => 'Sélection de traiteurs (cuisine du monde, événementiel, professionnel), comparaison des prestations et coordination logistique avec le lieu choisi.',
        ],
        [
            'id' => 'autres-location-materiel',
            'label' => 'Location matériel',
            'lead' => 'Trouver le bon matériel pour votre événement.',
            'text' => 'Location de mobilier, vaisselle, sonorisation, éclairage : nous vous aidons à dimensionner correctement le besoin pour éviter le sur‑coût ou le manque.',
        ],
        [
            'id' => 'autres-animations',
            'label' => 'Animations diverses',
            'lead' => 'Donner du rythme à vos événements.',
            'text' => 'DJ, MC, artistes, ateliers : nous proposons des animations adaptées à l’ambiance souhaitée et au public présent.',
        ],
    ],

];
