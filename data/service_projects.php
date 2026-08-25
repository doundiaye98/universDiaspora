<?php
declare(strict_types=1);

/**
 * Réalisations web affichées sur la page service Informatiques.
 *
 * @return array<string, list<array{name:string,url:string,label:string,text:string,tag?:string,tone?:string}>>
 */
return [
    'informatiques' => [
        [
            'name' => 'Yombal Market',
            'url' => 'https://yombalmarket.com/',
            'label' => 'Boutique en ligne',
            'tag' => 'E-commerce',
            'tone' => 'market',
            'text' => 'Marketplace diaspora : produits locaux, épicerie et livraison — du cadrage à la mise en ligne.',
        ],
        [
            'name' => 'Sunuru Fisquest',
            'url' => 'https://sunurufisquest.com/',
            'label' => 'Site vitrine',
            'tag' => 'Rufisque-Est',
            'tone' => 'civic',
            'text' => 'Site institutionnel de la commune de Rufisque-Est : identité, clarté et présence publique.',
        ],
        [
            'name' => 'Univers Diaspora',
            'url' => 'https://universdiaspora.com/',
            'label' => 'Plateforme multi-services',
            'tag' => 'Maison mère',
            'tone' => 'flagship',
            'text' => 'Plateforme officielle : 13 services, rendez-vous et parcours d’accompagnement pour la diaspora.',
        ],
    ],
];
