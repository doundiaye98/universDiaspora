<?php
declare(strict_types=1);

/**
 * Modèle de configuration prêt pour Hostinger (hébergement partagé / Business / Cloud).
 *
 * 1. Connectez-vous à hPanel : https://hpanel.hostinger.com
 * 2. Créez la base MySQL via "Bases de données > MySQL" :
 *      - Nom de la base : par exemple "universdiaspora"
 *      - Utilisateur dédié + mot de passe fort
 *    Hostinger préfixe automatiquement avec "u<numéro_compte>_" :
 *      Nom complet : u528552725_udiaspora  (suffixe max. 14 caractères)
 *      Utilisateur : u528552725_ud
 * 3. Activez SSL Let's Encrypt : hPanel > Sécurité > SSL > Installer.
 * 4. Récupérez les paramètres SMTP de votre boîte mail (hPanel > Emails) ou utilisez
 *    un service tiers (Brevo, SendGrid, OVH...).
 * 5. Renommez ce fichier en `config.local.php` (sans .example) et remplissez les valeurs.
 *
 * IMPORTANT : ne JAMAIS versionner config.local.php (déjà dans .gitignore).
 */
return [
    'app' => [
        'name' => 'Univers Diaspora',
        // Sans slash final. Doit correspondre à votre domaine canonique (HTTPS, sans www).
        'base_url' => 'https://universdiaspora.com',
        // En production : 'production'. Bloque l'affichage des erreurs PHP côté visiteur
        // et active les en-têtes HSTS / cookies Secure.
        'env' => 'production',
        // Si la racine web Hostinger pointe sur public_html/ (le cas par défaut),
        // laissez 'public'. Si vous avez créé un sous-domaine pointant directement
        // sur le dossier public/, mettez '' (chaîne vide).
        'assets_public_prefix' => 'public',
    ],

    'db' => [
        // Sur Hostinger : "127.0.0.1" ou "localhost" fonctionne dans 99% des cas.
        // Vérifiez dans hPanel > MySQL le nom d'hôte exact si la connexion échoue.
        'host' => 'localhost',
        'port' => 3306,
        // Nom complet (préfixe Hostinger inclus) — copier EXACTEMENT depuis hPanel.
        'name' => 'u528552725_udiaspora',
        // Si vous avez saisi un suffixe déjà préfixé, hPanel peut créer u528552725_u528552725_ud.
        'user' => 'u528552725_u528552725_ud',
        'pass' => 'MOT_DE_PASSE_BASE_DE_DONNEES',
        'charset' => 'utf8mb4',
    ],

    'admin' => [
        // Identifiants panneau admin (/?page=admin-login).
        // Après déploiement : ouvrir une fois /reset_admin.php puis supprimer ce fichier.
        'username' => 'ud_admin',
        // Mot de passe fort : 12+ caractères, mélange casse/chiffres/symboles.
        'password' => 'A_REMPLACER_PAR_UN_MOT_DE_PASSE_FORT',
    ],

    'admin_security' => [
        'session_timeout' => 1800,
        'max_login_attempts' => 5,
        'login_attempt_window' => 900,
    ],

    'legal' => [
        'documents_last_updated' => '2026-05-09',
        'publisher' => [
            'legal_name' => 'Univers Diaspora',
            'legal_form' => 'SAS',
            'address_line1' => '19, Rue Richomme',
            'postal_code' => '75018',
            'city' => 'Paris',
            'country' => 'France',
            'siret' => 'A_COMPLETER',
            'rcs_number' => 'A_COMPLETER',
            'rcs_city' => 'Paris',
            'share_capital' => 'A_COMPLETER',
            'director_name' => 'A_COMPLETER',
            'phone' => '+33 ...',
            'email' => 'contact@universdiaspora.com',
            'email_dpo' => 'dpo@universdiaspora.com',
        ],
        'hosting' => [
            'name' => 'Hostinger International Ltd.',
            'address' => '61 Lordou Vironos Street, 6023 Larnaca, Chypre',
            'website' => 'https://www.hostinger.fr',
            'phone' => '',
        ],
        'privacy' => [
            'retention_summary' => 'Les demandes de contact et rendez-vous sont conservées 3 ans après le dernier échange à des fins de preuve, puis supprimées.',
            'cookies_summary' => 'Cookies strictement nécessaires au fonctionnement (session, sécurité, panneau d’administration). Aucun cookie publicitaire.',
            'subprocessors_summary' => 'Hébergement Hostinger (UE) ; envoi d’e-mails transactionnels via votre fournisseur SMTP ; assistant IA via OpenAI lorsque activé.',
            'uses_audience_measurement' => false,
        ],
    ],

    'offres_recrutement' => [
        'email' => 'rh@universdiasporas.com',
        'phones' => [
            '0970707059',
            '0660288341',
        ],
    ],

    'mail' => [
        // Pour notifications RDV / contact (pas les candidatures : voir offres_recrutement.email).
        'enable' => true,
        // Hostinger fournit du SMTP pour les boîtes hébergées : smtp.hostinger.com:465 (SSL).
        // Sinon : Brevo (smtp-relay.brevo.com:587 TLS), SendGrid, OVH, etc.
        'transport' => 'smtp',
        'to' => 'contact@universdiaspora.com',
        'from' => 'no-reply@universdiaspora.com',
        'smtp' => [
            'host' => 'smtp.hostinger.com',
            'port' => 465,
            'encryption' => 'ssl',
            'username' => 'no-reply@universdiaspora.com',
            'password' => 'MOT_DE_PASSE_BOITE_EMAIL',
            'timeout' => 20,
            // true en production : Hostinger fournit des certificats valides.
            'verify_peer' => true,
        ],
    ],

    'ai_assistant' => [
        // true = widget visible + endpoint actif (réponses locales même sans clé OpenAI).
        'enabled' => true,
        // Afficher le bouton flottant (par défaut = même valeur que enabled).
        'show_widget' => true,
        'provider' => 'openai',
        // Optionnel : clé OpenAI pour des réponses enrichies (sinon réponses automatiques locales).
        'api_key' => '',
        'model' => 'gpt-4o-mini',
        'max_input_chars' => 700,
        'max_output_tokens' => 420,
        'temperature' => 0.25,
        'timeout_seconds' => 18,
    ],
];
