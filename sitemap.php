<?php
declare(strict_types=1);

/**
 * Sitemap XML dynamique listant : accueil, pages institutionnelles et services.
 *
 * Servi en /sitemap.xml via la règle de routing dans index.php.
 * Mis à jour automatiquement quand un service est ajouté ou modifié.
 */

require_once __DIR__ . '/app/http.php';
require_once __DIR__ . '/app/services.php';

$config = require __DIR__ . '/config/config.php';
$baseUrl = rtrim((string)($config['app']['base_url'] ?? ''), '/');
if ($baseUrl === '') {
    $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https://' : 'http://')
        . (string)($_SERVER['HTTP_HOST'] ?? 'localhost');
}

header('Content-Type: application/xml; charset=utf-8');
header('Cache-Control: public, max-age=3600');

$today = date('Y-m-d');

$urls = [
    ['loc' => $baseUrl . '/',                                     'priority' => '1.0', 'changefreq' => 'weekly'],
    ['loc' => $baseUrl . '/?page=apropos',                        'priority' => '0.7', 'changefreq' => 'monthly'],
    ['loc' => $baseUrl . '/?page=equipe',                         'priority' => '0.6', 'changefreq' => 'monthly'],
    ['loc' => $baseUrl . '/?page=offres-recrutement',             'priority' => '0.6', 'changefreq' => 'weekly'],
    ['loc' => $baseUrl . '/rendez-vous',                         'priority' => '0.8', 'changefreq' => 'monthly'],
    ['loc' => $baseUrl . '/?page=demarrer-maintenant',            'priority' => '0.7', 'changefreq' => 'monthly'],
    ['loc' => $baseUrl . '/?page=mentions-legales',               'priority' => '0.3', 'changefreq' => 'yearly'],
    ['loc' => $baseUrl . '/?page=politique-confidentialite',      'priority' => '0.3', 'changefreq' => 'yearly'],
];

$services = function_exists('services_all') ? services_all() : (require __DIR__ . '/data/services.php');
foreach ($services as $s) {
    if (!is_array($s)) {
        continue;
    }
    $slug = trim((string)($s['slug'] ?? ''));
    if ($slug === '' || !empty($s['external_url'])) {
        continue;
    }
    $urls[] = [
        'loc' => $baseUrl . '/?page=' . rawurlencode($slug),
        'priority' => '0.8',
        'changefreq' => 'monthly',
    ];
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($urls as $u) {
    echo "  <url>\n";
    echo '    <loc>' . htmlspecialchars((string)$u['loc'], ENT_XML1 | ENT_QUOTES, 'UTF-8') . "</loc>\n";
    echo '    <lastmod>' . $today . "</lastmod>\n";
    echo '    <changefreq>' . htmlspecialchars((string)$u['changefreq'], ENT_XML1 | ENT_QUOTES, 'UTF-8') . "</changefreq>\n";
    echo '    <priority>' . htmlspecialchars((string)$u['priority'], ENT_XML1 | ENT_QUOTES, 'UTF-8') . "</priority>\n";
    echo "  </url>\n";
}
echo '</urlset>' . "\n";
