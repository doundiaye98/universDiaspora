<?php
declare(strict_types=1);

/**
 * Agences Univers Diaspora — coordonnées & accès (affiche officielle).
 *
 * @return list<array{
 *   id:string,
 *   name:string,
 *   short_name:string,
 *   address:string,
 *   city:string,
 *   lat:float,
 *   lon:float,
 *   phone_fixe:?string,
 *   phone_mobile:?string,
 *   phone:string,
 *   access:list<array{type:string,lines:string,stops:string}>
 * }>
 */
return [
    [
        'id' => 'paris-18',
        'name' => 'Paris 18e — Rue Richomme',
        'short_name' => 'Paris 18e',
        'address' => '19, Rue Richomme, 75018 Paris',
        'city' => 'Paris 18e',
        'lat' => 48.8861978,
        'lon' => 2.3511478,
        'phone_fixe' => '09 70 70 70 59',
        'phone_mobile' => '06 31 27 33 76',
        'phone' => 'Fixe: 09 70 70 70 59 • Tél: 06 31 27 33 76',
        'access' => [
            ['type' => 'Métro', 'lines' => '4', 'stops' => 'Château Rouge'],
            ['type' => 'Métro', 'lines' => '4 · 12', 'stops' => 'Marcadet – Poissonniers'],
            ['type' => 'Métro', 'lines' => '2 · 4', 'stops' => 'Barbès – Rochechouart'],
            ['type' => 'Métro', 'lines' => '2', 'stops' => 'La Chapelle'],
        ],
    ],
    [
        'id' => 'paris-17',
        'name' => 'Paris 17e — Rue des Moines',
        'short_name' => 'Paris 17e',
        'address' => '75, Rue des Moines, 75017 Paris',
        'city' => 'Paris 17e',
        'lat' => 48.8913495,
        'lon' => 2.3215895,
        'phone_fixe' => '01 42 29 41 44',
        'phone_mobile' => '06 59 40 89 56',
        'phone' => 'Fixe: 01 42 29 41 44 • Tél: 06 59 40 89 56',
        'access' => [
            ['type' => 'Métro', 'lines' => '13', 'stops' => 'Brochant'],
            ['type' => 'Métro', 'lines' => '13', 'stops' => 'Guy Môquet'],
            ['type' => 'Métro', 'lines' => '13', 'stops' => 'La Fourche'],
            ['type' => 'Métro', 'lines' => '14', 'stops' => 'Pont Cardinet'],
            ['type' => 'Bus', 'lines' => '74 · 45 · 31', 'stops' => 'Arrêts à proximité'],
        ],
    ],
    [
        'id' => 'colombes',
        'name' => 'Colombes — Rue M. Berteaux',
        'short_name' => 'Colombes',
        'address' => '21, Rue M. Berteaux, 92700 Colombes',
        'city' => 'Colombes',
        'lat' => 48.9289708,
        'lon' => 2.2571219,
        'phone_fixe' => null,
        'phone_mobile' => null,
        'phone' => '',
        'access' => [
            ['type' => 'Transilien', 'lines' => 'J', 'stops' => 'Gare de Colombes'],
            ['type' => 'Métro', 'lines' => '13', 'stops' => 'Les Agnettes'],
            ['type' => 'Bus', 'lines' => '140 · 235 · 276 · 340 · 366', 'stops' => 'Arrêts à proximité'],
        ],
    ],
];
