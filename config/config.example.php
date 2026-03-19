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
];

