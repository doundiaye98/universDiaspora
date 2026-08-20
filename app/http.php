<?php
declare(strict_types=1);

require_once __DIR__ . '/env.php';

/**
 * En-têtes de sécurité légers pour les réponses HTTP (front et admin).
 */
function ud_security_headers(): void
{
    if (headers_sent()) {
        return;
    }
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('X-Frame-Options: SAMEORIGIN');
    /* Signature de plateforme (remplace l'éventuel X-Powered-By PHP par défaut). */
    header('X-Powered-By: Univers Diaspora — Studio interne');
    header('X-Designed-By: Studio Univers Diaspora <https://universdiaspora.com>');
}

function redirect(string $to): never
{
    header('Location: ' . $to, true, 302);
    exit;
}

function h(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * URL de base du site (détectée automatiquement si installé dans un sous-dossier).
 */
function ud_site_base_url(): string
{
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }

    $config = require dirname(__DIR__) . '/config/config.php';
    $configured = rtrim((string)($config['app']['base_url'] ?? ''), '/');

    if (PHP_SAPI === 'cli' || empty($_SERVER['HTTP_HOST'])) {
        return $cache = $configured;
    }

    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')
        || ((int)($_SERVER['SERVER_PORT'] ?? 0) === 443);
    $scheme = $https ? 'https' : 'http';
    $host = (string)$_SERVER['HTTP_HOST'];

    $dir = dirname((string)($_SERVER['SCRIPT_NAME'] ?? '/index.php'));
    $dir = str_replace('\\', '/', $dir);
    if ($dir === '/' || $dir === '.') {
        $dir = '';
    } else {
        $dir = rtrim($dir, '/');
    }

    $detected = $scheme . '://' . $host . $dir;

    /*
     * Toujours l’URL de la requête HTTP courante (local WAMP, domaine custom, etc.).
     * Évite de charger le CSS/JS depuis une ancienne base_url (ex. hostingersite.com).
     * config.app.base_url reste utile en CLI / e-mails / sitemap.
     */
    return $cache = $detected;
}

/**
 * Préfixe URL des assets (ex. « public » → /public/assets/…). Détecté sur disque si possible.
 */
function ud_assets_public_prefix(): string
{
    static $prefix = null;
    if ($prefix !== null) {
        return $prefix;
    }

    $configured = 'public';
    try {
        $cfg = require dirname(__DIR__) . '/config/config.php';
        $configured = trim(str_replace('\\', '/', (string)($cfg['app']['assets_public_prefix'] ?? 'public')), '/');
    } catch (Throwable $e) {
        $configured = 'public';
    }

    $root = dirname(__DIR__);
    $probeRel = 'assets/img/logo-univers-diaspora.jpg';
    $candidates = array_values(array_unique([$configured, 'public', ''], SORT_STRING));

    foreach ($candidates as $p) {
        $fs = $root . '/' . ($p === '' ? '' : $p . '/') . $probeRel;
        if (is_file($fs)) {
            return $prefix = $p;
        }
    }

    return $prefix = $configured;
}

/**
 * URL vers un fichier sous public/assets/ ($path relatif, ex. « img/logo.jpg » ou « css/style.css »).
 * Respecte config app.assets_public_prefix (défaut « public » ; vide si la racine web = dossier public/).
 */
function ud_public_asset_url(string $pathUnderPublicAssets, string $baseUrl): string
{
    $prefix = ud_assets_public_prefix();
    $rel = ltrim(str_replace('\\', '/', $pathUnderPublicAssets), '/');
    $seg = $prefix === '' ? 'assets/' : $prefix . '/assets/';
    $url = rtrim($baseUrl, '/') . '/' . $seg . $rel;

    // Cache-bust : force le navigateur / CDN Hostinger à reprendre le CSS/JS à jour
    $fs = dirname(__DIR__) . '/public/assets/' . $rel;
    if (is_file($fs)) {
        $mtime = @filemtime($fs);
        if ($mtime !== false) {
            $url .= (strpos($url, '?') !== false ? '&' : '?') . 'v=' . $mtime;
        }
    }

    return $url;
}

/**
 * URL de prise de rendez-vous (chemin lisible /rendez-vous/… — mieux accepté par Hostinger / WAF).
 */
function ud_appointment_url(string $baseUrl, ?string $serviceSlug = null, ?string $voletId = null): string
{
    $base = rtrim($baseUrl, '/');
    $serviceSlug = trim((string)$serviceSlug);
    $voletId = trim((string)$voletId);

    if ($serviceSlug === '' || preg_match('/^[a-z0-9-]{1,120}$/', $serviceSlug) !== 1) {
        return $base . '/rendez-vous';
    }

    $url = $base . '/rendez-vous/' . $serviceSlug;
    if ($voletId !== '' && preg_match('/^[a-z0-9-]{1,120}$/', $voletId) === 1) {
        $url .= '/' . $voletId;
    }

    return $url;
}

function post(string $key, string $default = ''): string
{
    if (!isset($_POST[$key])) {
        return $default;
    }
    $v = $_POST[$key];
    if (!is_string($v)) {
        return $default;
    }
    return trim($v);
}

/**
 * Coordonnées RH / offres & recrutement (config offres_recrutement).
 *
 * @return array{email:string,phones:list<string>}
 */
function ud_offres_recrutement_contact(?array $config = null): array
{
    $config ??= require dirname(__DIR__) . '/config/config.php';
    $block = $config['offres_recrutement'] ?? [];
    $email = trim((string)($block['email'] ?? ''));
    $phones = [];
    foreach ((array)($block['phones'] ?? []) as $p) {
        $p = trim((string)$p);
        if ($p !== '') {
            $phones[] = $p;
        }
    }
    return ['email' => $email, 'phones' => $phones];
}

/** E-mail de notification pour candidatures (offres & recrutement). */
function ud_offres_recrutement_notify_email(?array $config = null): string
{
    $contact = ud_offres_recrutement_contact($config);
    if ($contact['email'] !== '') {
        return $contact['email'];
    }
    $config ??= require dirname(__DIR__) . '/config/config.php';
    return trim((string)($config['mail']['to'] ?? ''));
}

function ud_phone_digits(string $phone): string
{
    return preg_replace('/\D+/', '', $phone) ?? '';
}

/** Affichage lisible d’un numéro français à 10 chiffres (ex. 09 70 70 70 59). */
function ud_phone_display_fr(string $phone): string
{
    $d = ud_phone_digits($phone);
    if (strlen($d) === 10 && $d[0] === '0') {
        return implode(' ', str_split($d, 2));
    }
    return trim($phone);
}

/** Lien tel: pour un numéro saisi (0X… ou +33…). */
function ud_phone_tel_href(string $phone): string
{
    $d = ud_phone_digits($phone);
    if ($d === '') {
        return '';
    }
    if (str_starts_with($d, '0') && strlen($d) === 10) {
        $d = '33' . substr($d, 1);
    } elseif (str_starts_with($d, '33')) {
        // déjà international
    } elseif (!str_starts_with($d, '+')) {
        $d = '33' . ltrim($d, '0');
    }
    return 'tel:+' . ltrim($d, '+');
}

/**
 * Site Immobilier & BTP (projet WAMP `www/immobiler`).
 */
function ud_immobilier_btp_url(): string
{
    $config = require dirname(__DIR__) . '/config/config.php';
    $configured = trim((string)($config['app']['immobilier_btp_url'] ?? ''));
    if ($configured !== '') {
        return rtrim($configured, '/');
    }

    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    $host = (string)($_SERVER['HTTP_HOST'] ?? 'localhost');
    if ($host === '') {
        $host = 'localhost';
    }
    $scheme = $https ? 'https' : 'http';

    $wwwRoot = dirname(__DIR__, 2);
    foreach (['immobiler', 'immobilier'] as $dir) {
        if (is_file($wwwRoot . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR . 'index.php')) {
            return $scheme . '://' . $host . '/' . $dir;
        }
    }

    return $scheme . '://' . $host . '/immobiler';
}

/** Liens vers un autre site (Voyages, Market) : nouvel onglet. Immobilier : même onglet. */
function ud_service_opens_new_tab(array $service): bool
{
    $url = trim((string)($service['external_url'] ?? ''));
    if ($url === '') {
        return false;
    }
    return ($service['slug'] ?? '') !== 'immobilier-btp';
}

