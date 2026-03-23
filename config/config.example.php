<?php
declare(strict_types=1);

return [
    'app' => [
        'name' => 'Univers Diaspora',
        'base_url' => 'http://localhost/universDiaspora',
    ],
    'db' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'name' => 'univers_diaspora',
        'user' => 'root',
        'pass' => 'root',
        'charset' => 'utf8mb4',
    ],
    'admin' => [
        // Accès admin caché: http://localhost/universDiaspora/?page=admin
        // Identifiants initiaux (à changer après installation)
        'username' => 'admin',
        'password' => 'admin123',
    ],
    'mail' => [
        // Active l'envoi d’emails via PHP `mail()`.
        // Par défaut désactivé pour éviter toute erreur si la conf SMTP n'est pas OK.
        'enable' => false,
        'to' => 'contact@universdiaspora.com',
        'from' => 'no-reply@universdiaspora.com',
    ],
];

