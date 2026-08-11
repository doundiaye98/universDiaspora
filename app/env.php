<?php
declare(strict_types=1);

/**
 * Détection de l'environnement d'exécution (WAMP local vs production).
 */
function ud_request_host(): string
{
    $raw = (string)($_SERVER['HTTP_HOST'] ?? '');
    if ($raw === '') {
        return '';
    }
    $host = parse_url('http://' . $raw, PHP_URL_HOST);

    return is_string($host) && $host !== '' ? strtolower($host) : strtolower($raw);
}

function ud_is_local_dev_request(): bool
{
    if (PHP_SAPI === 'cli') {
        return true;
    }

    $host = ud_request_host();
    if ($host === '' || $host === 'localhost') {
        return true;
    }
    if (str_starts_with($host, '127.0.0.1')) {
        return true;
    }
    if (str_starts_with($host, '192.168.') || str_starts_with($host, '10.')) {
        return true;
    }
    if (str_ends_with($host, '.local') || str_ends_with($host, '.test') || str_ends_with($host, '.localhost')) {
        return true;
    }

    return false;
}

/**
 * Chemin HTTP relatif à la racine du site (sans sous-dossier WAMP).
 */
function ud_request_path(): string
{
    $path = (string)(parse_url((string)($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH) ?: '/');
    $base = dirname((string)($_SERVER['SCRIPT_NAME'] ?? '/index.php'));
    $base = str_replace('\\', '/', $base);
    if ($base !== '/' && $base !== '.' && str_starts_with($path, $base)) {
        $path = substr($path, strlen(rtrim($base, '/')));
    }
    if ($path === '' || $path[0] !== '/') {
        $path = '/' . ltrim($path, '/');
    }

    return $path;
}
