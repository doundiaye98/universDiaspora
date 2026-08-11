<?php
declare(strict_types=1);

/**
 * Initialisation du runtime PHP : timezone, gestion d'erreurs, sessions sécurisées.
 *
 * À inclure une seule fois, en tête d'index.php (avant tout autre code applicatif).
 * Lit la valeur app.env depuis config/config.php :
 *   - 'production' : erreurs masquées, journalisées dans storage/logs/php-errors.log
 *   - 'dev' (par défaut) : erreurs affichées
 */

require_once __DIR__ . '/env.php';

date_default_timezone_set('Europe/Paris');
mb_internal_encoding('UTF-8');

$cfgFile = __DIR__ . '/../config/config.php';
$config = is_file($cfgFile) ? (require $cfgFile) : [];
$env = is_array($config) ? (string)($config['app']['env'] ?? 'dev') : 'dev';
$isLocalDev = ud_is_local_dev_request();
$isProduction = ($env === 'production') && !$isLocalDev;

/* Journal d'erreurs persistant (toujours actif) */
$logDir = __DIR__ . '/../storage/logs';
if (!is_dir($logDir)) {
    @mkdir($logDir, 0775, true);
    @file_put_contents($logDir . '/.htaccess', "Require all denied\n");
    @file_put_contents($logDir . '/index.html', '');
}
$logFile = $logDir . '/php-errors.log';
@ini_set('log_errors', '1');
@ini_set('error_log', $logFile);
@ini_set('expose_php', '0');

if ($isProduction) {
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
    @ini_set('display_errors', '0');
    @ini_set('display_startup_errors', '0');
} else {
    error_reporting(E_ALL);
    @ini_set('display_errors', '1');
    @ini_set('display_startup_errors', '1');
}

/* Session sécurisée — appelée AVANT session_start() dans index.php */
if (PHP_SESSION_NONE === session_status()) {
    $isHttps = (
        (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')
        || ((int)($_SERVER['SERVER_PORT'] ?? 0) === 443)
    );
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $isProduction || $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    @ini_set('session.use_only_cookies', '1');
    @ini_set('session.use_strict_mode', '1');
    @ini_set('session.cookie_httponly', '1');
    @ini_set('session.cookie_samesite', 'Lax');
    if ($isProduction || $isHttps) {
        @ini_set('session.cookie_secure', '1');
    }
}

/* Gestionnaire d'exception global : journalise puis affiche la page d'erreur. */
set_exception_handler(function (Throwable $e) use ($isProduction): void {
    error_log('[unhandled] ' . $e::class . ': ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
    if (!headers_sent()) {
        http_response_code(500);
    }
    if ($isProduction) {
        $errorPage = __DIR__ . '/../error.php';
        if (is_file($errorPage)) {
            $_GET['code'] = 500;
            include $errorPage;
            exit;
        }
        echo 'Une erreur est survenue. Veuillez réessayer plus tard.';
        exit;
    }
    throw $e;
});
