<?php
declare(strict_types=1);

/**
 * Charge config.example.php puis fusionne config.local.php si présent.
 * En production : copiez config.local.php.example vers config.local.php et renseignez les vraies valeurs.
 * Ne commitez jamais config.local.php.
 */
$defaults = require __DIR__ . '/config.example.php';
$localPath = __DIR__ . '/config.local.php';
if (is_file($localPath)) {
    $local = require $localPath;
    if (is_array($local)) {
        return array_replace_recursive($defaults, $local);
    }
}

return $defaults;
