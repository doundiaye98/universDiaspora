<?php
declare(strict_types=1);

/**
 * Modèle de configuration (versionné). Pas de secrets réels ici.
 * Pour le développement local : copiez config.local.php.example vers config.local.php.
 */
return [
    'app' => [
        'name' => 'Univers Diaspora',
        'base_url' => 'http://localhost/universDiaspora',
        /**
         * Environnement courant : 'dev' (affiche les erreurs PHP) ou 'production'
         * (masque les erreurs et les journalise). À définir en production.
         */
        'env' => 'dev',
        /**
         * Segment d’URL avant /assets/ (fichiers physiques toujours dans public/assets/).
         * - Par défaut « public » → URLs du type {base_url}/public/assets/img/...
         * - Si la racine web pointe déjà vers le dossier public/ (Apache/Nginx), mettre '' :
         *   URLs {base_url}/assets/img/...
         */
        'assets_public_prefix' => 'public',
    ],
    'db' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'name' => 'univers_diaspora',
        'user' => 'root',
        'pass' => '',
        'charset' => 'utf8mb4',
    ],
    'admin' => [
        'username' => 'admin',
        // Mot de passe initial : à définir dans config.local.php (min. 8 caractères).
        'password' => 'CHANGEME',
    ],
    'admin_security' => [
        // Déconnexion automatique après inactivité (en secondes).
        'session_timeout' => 1800,
        // Anti brute-force: nombre d'échecs max dans la fenêtre ci-dessous.
        'max_login_attempts' => 5,
        // Fenêtre anti brute-force (en secondes).
        'login_attempt_window' => 900,
    ],
    // Informations éditeur / hébergeur / textes juridiques (surchargez dans config.local.php).
    'legal' => [
        // Date d’affichage « dernière mise à jour » des pages Mentions & Politique (AAAA-MM-JJ).
        'documents_last_updated' => '',
        'publisher' => [
            'legal_name' => '',
            'legal_form' => '',
            'address_line1' => '',
            'address_line2' => '',
            'postal_code' => '',
            'city' => '',
            'country' => 'France',
            'siret' => '',
            'rcs_number' => '',
            'rcs_city' => '',
            'share_capital' => '',
            'vat_number' => '',
            'director_title' => 'Directeur ou directrice de la publication',
            'director_name' => '',
            'phone' => '',
            'email' => '',
            'email_dpo' => '',
        ],
        'hosting' => [
            'name' => '',
            'address' => '',
            'website' => '',
            'phone' => '',
        ],
        'privacy' => [
            // Ex. : « Les messages sont conservés 3 ans après le dernier contact. »
            'retention_summary' => '',
            // Décrivez cookies réels (session, mesure d’audience, etc.).
            'cookies_summary' => '',
            'subprocessors_summary' => '',
            'uses_audience_measurement' => false,
        ],
    ],
    // Coordonnées page « Offres & recrutement » + notifications candidatures.
    'offres_recrutement' => [
        'email' => 'rh@universdiasporas.com',
        'phones' => [
            '0970707059',
            '0660288341',
        ],
    ],
    'mail' => [
        // Obligatoire pour recevoir les candidatures (CV + lettre PDF) sur l’e-mail RH.
        'enable' => false,
        // mail = PHP mail() | smtp = serveur SMTP (recommandé en production)
        'transport' => 'mail',
        'to' => 'contact@example.com',
        'from' => 'no-reply@example.com',
        'smtp' => [
            'host' => '',
            'port' => 587,
            // tls (STARTTLS sur 587) | ssl (SMTPS sur 465) | none
            'encryption' => 'tls',
            'username' => '',
            'password' => '',
            'timeout' => 20,
            // true en production si certificat SMTP valide
            'verify_peer' => false,
        ],
    ],
    'ai_assistant' => [
        // Active/desactive le widget et l endpoint /?action=ai-chat
        'enabled' => true,
        // openai uniquement dans cette version
        'provider' => 'openai',
        // Renseigner dans config.local.php uniquement (ne pas versionner un secret)
        'api_key' => '',
        'model' => 'gpt-4o-mini',
        'max_input_chars' => 700,
        'max_output_tokens' => 260,
        'temperature' => 0.4,
        'timeout_seconds' => 18,
    ],
];
