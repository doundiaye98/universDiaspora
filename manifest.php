<?php
declare(strict_types=1);

/**
 * Web App Manifest — chemins relatifs à la base du site (WAMP sous-dossier / Hostinger racine).
 */
require __DIR__ . '/app/bootstrap.php';
require __DIR__ . '/app/http.php';

$baseUrl = rtrim(ud_site_base_url(), '/');
$icon192 = ud_public_asset_url('img/pwa/icon-192.png', $baseUrl);
$icon512 = ud_public_asset_url('img/pwa/icon-512.png', $baseUrl);
$maskable = ud_public_asset_url('img/pwa/maskable-512.png', $baseUrl);

// Retirer le cache-bust ?v=… des icônes (manifest plus stable pour l’install)
$stripV = static function (string $url): string {
    $clean = preg_replace('/[?&]v=\d+/', '', $url) ?? $url;
    return rtrim($clean, '?&');
};

$manifest = [
    'id' => $baseUrl . '/',
    'name' => 'Univers Diaspora',
    'short_name' => 'UD Diaspora',
    'description' => 'Conseil et accompagnement pour la diaspora — Paris 18e, Paris 17e et Colombes. Prendre rendez-vous et découvrir nos 12 pôles.',
    'lang' => 'fr-FR',
    'dir' => 'ltr',
    'start_url' => $baseUrl . '/?utm_source=pwa',
    'scope' => $baseUrl . '/',
    'display' => 'standalone',
    'display_override' => ['standalone', 'browser'],
    'orientation' => 'portrait-primary',
    'background_color' => '#0c1730',
    'theme_color' => '#1a3462',
    'categories' => ['business', 'lifestyle'],
    'icons' => [
        [
            'src' => $stripV($icon192),
            'sizes' => '192x192',
            'type' => 'image/png',
            'purpose' => 'any',
        ],
        [
            'src' => $stripV($icon512),
            'sizes' => '512x512',
            'type' => 'image/png',
            'purpose' => 'any',
        ],
        [
            'src' => $stripV($maskable),
            'sizes' => '512x512',
            'type' => 'image/png',
            'purpose' => 'maskable',
        ],
    ],
    'shortcuts' => [
        [
            'name' => 'Prendre rendez-vous',
            'short_name' => 'RDV',
            'description' => 'Réserver un créneau en agence',
            'url' => $baseUrl . '/rendez-vous?utm_source=pwa_shortcut',
            'icons' => [['src' => $stripV($icon192), 'sizes' => '192x192']],
        ],
        [
            'name' => 'Nos services',
            'short_name' => 'Services',
            'url' => $baseUrl . '/?utm_source=pwa_shortcut#services',
            'icons' => [['src' => $stripV($icon192), 'sizes' => '192x192']],
        ],
    ],
];

header('Content-Type: application/manifest+json; charset=utf-8');
header('Cache-Control: public, max-age=86400');
echo json_encode($manifest, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
