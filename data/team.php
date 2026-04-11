<?php
declare(strict_types=1);

/**
 * Membres de l'équipe affichés sur /?page=equipe
 * - photo : nom de fichier dans public/assets/img/ (ex. equipe-marie.jpg), ou null pour afficher les initiales
 */
return [
    [
        'name' => 'Exemple — Prénom Nom',
        'role' => 'Direction & stratégie',
        'bio' => 'Remplacez ce texte par une courte présentation : parcours, missions au sein d’Univers Diaspora, ce qui vous anime.',
        'photo' => null,
    ],
    [
        'name' => 'Exemple — Collaborateur·rice',
        'role' => 'Conseil & accompagnement',
        'bio' => 'Ajoutez d’autres fiches dans ce fichier (data/team.php) et placez les portraits dans public/assets/img/ si vous utilisez des photos.',
        'photo' => null,
    ],
];
