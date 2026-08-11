<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/env.php';

/**
 * Charge config.example.php puis fusionne config.local.php si présent.
 * En production : copiez config.local.php.example vers config.local.php et renseignez les vraies valeurs.
 * Ne commitez jamais config.local.php.
 */
$defaults = require __DIR__ . '/config.example.php';
$config = $defaults;

$localPath = __DIR__ . '/config.local.php';
if (is_file($localPath)) {
    $local = require $localPath;
    if (is_array($local)) {
        $config = array_replace_recursive($config, $local);
    }
}

/* WAMP / dev local : config.wamp.php surcharge config.local.php (ex. base_url Hostinger). */
if (ud_is_local_dev_request()) {
    $wampPath = __DIR__ . '/config.wamp.php';
    if (is_file($wampPath)) {
        $wamp = require $wampPath;
        if (is_array($wamp)) {
            $config = array_replace_recursive($config, $wamp);
        }
    }
}

return $config;
