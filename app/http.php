<?php
declare(strict_types=1);

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
 * URL vers un fichier sous public/assets/ ($path relatif, ex. « img/logo.jpg » ou « css/style.css »).
 * Respecte config app.assets_public_prefix (défaut « public » ; vide si la racine web = dossier public/).
 */
function ud_public_asset_url(string $pathUnderPublicAssets, string $baseUrl): string
{
    static $prefix = null;
    if ($prefix === null) {
        try {
            $cfg = require dirname(__DIR__) . '/config/config.php';
            $p = trim(str_replace('\\', '/', (string)($cfg['app']['assets_public_prefix'] ?? 'public')), '/');
            $prefix = $p;
        } catch (Throwable $e) {
            $prefix = 'public';
        }
    }
    $rel = ltrim(str_replace('\\', '/', $pathUnderPublicAssets), '/');
    $seg = $prefix === '' ? 'assets/' : $prefix . '/assets/';
    return rtrim($baseUrl, '/') . '/' . $seg . $rel;
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

