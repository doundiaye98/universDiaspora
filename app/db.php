<?php
declare(strict_types=1);

function ensureContactSchema(PDO $pdo): void
{
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS `contact_messages` (
          `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
          `last_name` VARCHAR(100) NOT NULL,
          `first_name` VARCHAR(100) NOT NULL,
          `email` VARCHAR(190) NOT NULL,
          `phone` VARCHAR(50) NULL,
          `message` TEXT NOT NULL,
          `ip` VARCHAR(45) NULL,
          `user_agent` VARCHAR(255) NULL,
          `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          KEY `idx_created_at` (`created_at`),
          KEY `idx_email` (`email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;'
    );

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS `appointments` (
          `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
          `office` VARCHAR(190) NOT NULL,
          `appointment_at` DATETIME NOT NULL,
          `name` VARCHAR(120) NOT NULL,
          `email` VARCHAR(190) NOT NULL,
          `phone` VARCHAR(50) NULL,
          `message` TEXT NULL,
          `service_slug` VARCHAR(120) NULL,
          `volet_id` VARCHAR(120) NULL,
          `status` VARCHAR(20) NOT NULL DEFAULT \'pending\',
          `confirmed_at` DATETIME NULL,
          `confirmed_by` VARCHAR(80) NULL,
          `ip` VARCHAR(45) NULL,
          `user_agent` VARCHAR(255) NULL,
          `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          KEY `idx_created_at` (`created_at`),
          KEY `idx_appointment_at` (`appointment_at`),
          KEY `idx_email` (`email`),
          KEY `idx_appointments_service_slug` (`service_slug`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;'
    );

    // If appointments table already exists (created earlier), add missing columns.
    $apptCols = [
        'status' => "VARCHAR(20) NOT NULL DEFAULT 'pending'",
        'confirmed_at' => 'DATETIME NULL',
        'confirmed_by' => 'VARCHAR(80) NULL',
        'service_slug' => 'VARCHAR(120) NULL',
        'volet_id' => 'VARCHAR(120) NULL',
    ];
    foreach ($apptCols as $colName => $colDef) {
        $has = (int)$pdo->query(
            "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'appointments' AND COLUMN_NAME = " . $pdo->quote($colName)
        )->fetchColumn();
        if ($has === 0) {
            $pdo->exec('ALTER TABLE `appointments` ADD COLUMN `' . $colName . '` ' . $colDef);
        }
    }

    $apptIdx = [
        'idx_appointments_status' => '(status)',
        'idx_appointments_service_slug' => '(service_slug)',
    ];
    foreach ($apptIdx as $idxName => $cols) {
        $has = (int)$pdo->query(
            "SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'appointments' AND INDEX_NAME = " . $pdo->quote($idxName)
        )->fetchColumn();
        if ($has === 0) {
            $pdo->exec('ALTER TABLE appointments ADD INDEX ' . $idxName . ' ' . $cols);
        }
    }

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS `admin_users` (
          `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
          `username` VARCHAR(80) NOT NULL,
          `password_hash` VARCHAR(255) NOT NULL,
          `role` VARCHAR(20) NOT NULL DEFAULT \'super_admin\',
          `is_active` TINYINT(1) NOT NULL DEFAULT 1,
          `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          UNIQUE KEY `uniq_username` (`username`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;'
    );
    $hasAdminRole = (int)$pdo->query(
        "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'admin_users' AND COLUMN_NAME = 'role'"
    )->fetchColumn();
    if ($hasAdminRole === 0) {
        $pdo->exec("ALTER TABLE admin_users ADD COLUMN role VARCHAR(20) NOT NULL DEFAULT 'super_admin' AFTER password_hash");
    }

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS `admin_login_attempts` (
          `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
          `username` VARCHAR(80) NOT NULL,
          `ip` VARCHAR(45) NULL,
          `attempted_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          KEY `idx_admin_login_attempts_user_time` (`username`, `attempted_at`),
          KEY `idx_admin_login_attempts_ip_time` (`ip`, `attempted_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;'
    );

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS `services` (
          `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
          `slug` VARCHAR(120) NOT NULL,
          `title` VARCHAR(190) NOT NULL,
          `description` VARCHAR(255) NULL,
          `details` TEXT NULL,
          `details_is_html` TINYINT(1) NOT NULL DEFAULT 0,
          `step1_title` VARCHAR(120) NULL,
          `step1_text` TEXT NULL,
          `step2_title` VARCHAR(120) NULL,
          `step2_text` TEXT NULL,
          `step3_title` VARCHAR(120) NULL,
          `step3_text` TEXT NULL,
          `faq1_q` VARCHAR(255) NULL,
          `faq1_a` TEXT NULL,
          `faq2_q` VARCHAR(255) NULL,
          `faq2_a` TEXT NULL,
          `faq3_q` VARCHAR(255) NULL,
          `faq3_a` TEXT NULL,
          `icon` VARCHAR(190) NULL,
          `external_url` VARCHAR(255) NULL,
          `coming_soon` TINYINT(1) NOT NULL DEFAULT 0,
          `sort_order` INT NOT NULL DEFAULT 0,
          `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          UNIQUE KEY `uniq_slug` (`slug`),
          KEY `idx_sort_order` (`sort_order`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;'
    );

    $serviceCols = [
        'details_is_html' => 'TINYINT(1) NOT NULL DEFAULT 0',
        'step1_title' => 'VARCHAR(120) NULL',
        'step1_text' => 'TEXT NULL',
        'step2_title' => 'VARCHAR(120) NULL',
        'step2_text' => 'TEXT NULL',
        'step3_title' => 'VARCHAR(120) NULL',
        'step3_text' => 'TEXT NULL',
        'faq1_q' => 'VARCHAR(255) NULL',
        'faq1_a' => 'TEXT NULL',
        'faq2_q' => 'VARCHAR(255) NULL',
        'faq2_a' => 'TEXT NULL',
        'faq3_q' => 'VARCHAR(255) NULL',
        'faq3_a' => 'TEXT NULL',
    ];
    foreach ($serviceCols as $colName => $colDef) {
        $has = (int) $pdo->query(
            "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'services' AND COLUMN_NAME = " . $pdo->quote($colName)
        )->fetchColumn();
        if ($has === 0) {
            $pdo->exec('ALTER TABLE `services` ADD COLUMN `' . str_replace('`', '``', $colName) . '` ' . $colDef);
        }
    }

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS `service_bullets` (
          `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
          `service_id` BIGINT UNSIGNED NOT NULL,
          `bullet` VARCHAR(255) NOT NULL,
          `sort_order` INT NOT NULL DEFAULT 0,
          PRIMARY KEY (`id`),
          KEY `idx_service_sort` (`service_id`, `sort_order`),
          CONSTRAINT `fk_service_bullets_service` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;'
    );

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS `announcements` (
          `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
          `category` ENUM(\'offre\',\'recrutement\') NOT NULL DEFAULT \'offre\',
          `title` VARCHAR(190) NOT NULL,
          `summary` VARCHAR(255) NULL,
          `content` TEXT NULL,
          `sort_order` INT NOT NULL DEFAULT 0,
          `is_published` TINYINT(1) NOT NULL DEFAULT 1,
          `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
          `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          KEY `idx_category_sort` (`category`, `sort_order`),
          KEY `idx_ann_published` (`is_published`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;'
    );

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS `job_applications` (
          `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
          `announcement_id` BIGINT UNSIGNED NULL,
          `full_name` VARCHAR(200) NOT NULL,
          `email` VARCHAR(190) NOT NULL,
          `phone` VARCHAR(50) NULL,
          `message` TEXT NULL,
          `cv_path` VARCHAR(500) NOT NULL,
          `cover_path` VARCHAR(500) NOT NULL,
          `ip` VARCHAR(45) NULL,
          `user_agent` VARCHAR(255) NULL,
          `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          KEY `idx_job_ann` (`announcement_id`),
          KEY `idx_job_created` (`created_at`),
          CONSTRAINT `fk_job_announcement` FOREIGN KEY (`announcement_id`) REFERENCES `announcements` (`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;'
    );

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS `team_members` (
          `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
          `name` VARCHAR(200) NOT NULL,
          `role` VARCHAR(255) NULL,
          `bio` TEXT NULL,
          `photo` VARCHAR(255) NULL,
          `sort_order` INT NOT NULL DEFAULT 0,
          `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
          `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          KEY `idx_team_sort` (`sort_order`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;'
    );

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS `ai_conversations` (
          `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
          `session_id` VARCHAR(64) NULL,
          `ip` VARCHAR(45) NULL,
          `user_agent` VARCHAR(255) NULL,
          `question` VARCHAR(2000) NOT NULL,
          `answer` VARCHAR(5000) NOT NULL,
          `intent` VARCHAR(80) NULL,
          `matched_service_slug` VARCHAR(120) NULL,
          `matched_volet_id` VARCHAR(120) NULL,
          `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          KEY `idx_ai_conv_created` (`created_at`),
          KEY `idx_ai_conv_session` (`session_id`),
          KEY `idx_ai_conv_service` (`matched_service_slug`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;'
    );

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS `testimonials` (
          `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
          `quote` TEXT NOT NULL,
          `author` VARCHAR(190) NOT NULL,
          `location` VARCHAR(190) NULL,
          `case_label` VARCHAR(190) NULL,
          `case_value` VARCHAR(255) NULL,
          `sort_order` INT NOT NULL DEFAULT 0,
          `is_published` TINYINT(1) NOT NULL DEFAULT 1,
          `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
          `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          KEY `idx_testimonials_sort` (`sort_order`),
          KEY `idx_testimonials_published` (`is_published`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;'
    );

    $testimonialCols = [
        'submitter_email' => 'VARCHAR(190) NULL',
        'ip' => 'VARCHAR(45) NULL',
        'user_agent' => 'VARCHAR(255) NULL',
    ];
    foreach ($testimonialCols as $colName => $colDef) {
        $has = (int)$pdo->query(
            "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'testimonials' AND COLUMN_NAME = " . $pdo->quote($colName)
        )->fetchColumn();
        if ($has === 0) {
            $pdo->exec('ALTER TABLE `testimonials` ADD COLUMN `' . $colName . '` ' . $colDef);
        }
    }

    // Seed initial admin user if missing (credentials from config) — jamais avec mot de passe par défaut faible.
    $config = require __DIR__ . '/../config/config.php';
    $adminUser = trim((string)($config['admin']['username'] ?? 'admin'));
    $adminPass = (string)($config['admin']['password'] ?? '');
    $weak = ($adminPass === '' || strcasecmp($adminPass, 'CHANGEME') === 0 || strlen($adminPass) < 8);
    $stmt = $pdo->prepare('SELECT id FROM admin_users WHERE username = :u LIMIT 1');
    $stmt->execute([':u' => $adminUser !== '' ? $adminUser : 'admin']);
    $existing = $stmt->fetchColumn();
    if (!$existing && $adminUser !== '' && !$weak) {
        $hash = password_hash($adminPass, PASSWORD_DEFAULT);
        $ins = $pdo->prepare('INSERT INTO admin_users (username, password_hash, role, is_active) VALUES (:u, :h, :r, 1)');
        $ins->execute([':u' => $adminUser, ':h' => $hash, ':r' => 'super_admin']);
    }

    // Seed services from data/services.php if DB is empty
    $svcCount = (int) $pdo->query('SELECT COUNT(*) FROM services')->fetchColumn();
    if ($svcCount === 0) {
        $seed = require __DIR__ . '/../data/services.php';
        if (is_array($seed)) {
            $order = 0;
            foreach ($seed as $s) {
                if (!is_array($s)) continue;
                $order += 10;
                $stmt = $pdo->prepare('INSERT INTO services (slug, title, description, details, icon, external_url, coming_soon, sort_order) VALUES (:slug, :title, :description, :details, :icon, :external_url, :coming_soon, :sort_order)');
                $stmt->execute([
                    ':slug' => (string)($s['slug'] ?? ''),
                    ':title' => (string)($s['title'] ?? ''),
                    ':description' => (string)($s['description'] ?? ''),
                    ':details' => null,
                    ':icon' => (string)($s['icon'] ?? ''),
                    ':external_url' => isset($s['external_url']) ? (string)$s['external_url'] : null,
                    ':coming_soon' => !empty($s['coming_soon']) ? 1 : 0,
                    ':sort_order' => $order,
                ]);
                $serviceId = (int) $pdo->lastInsertId();
                $bullets = $s['bullets'] ?? [];
                if (is_array($bullets)) {
                    $bOrder = 0;
                    foreach ($bullets as $b) {
                        $b = trim((string)$b);
                        if ($b === '') continue;
                        $bOrder += 10;
                        $pdo->prepare('INSERT INTO service_bullets (service_id, bullet, sort_order) VALUES (:sid, :bullet, :sort_order)')
                            ->execute([':sid' => $serviceId, ':bullet' => $b, ':sort_order' => $bOrder]);
                    }
                }
            }
        }
    }

    // Liens externes canoniques (mises à jour même si la table services est déjà peuplée)
    $pdo->prepare(
        'UPDATE services SET external_url = :url, coming_soon = 0 WHERE slug = :slug'
    )->execute([
        ':url' => 'https://www.terangavoyages.com/',
        ':slug' => 'voyages',
    ]);
    $pdo->prepare(
        'UPDATE services
         SET external_url = :url, coming_soon = 0,
             description = :desc
         WHERE slug = :slug'
    )->execute([
        ':url' => 'https://yombalmarket.com/',
        ':desc' => 'Boutique en ligne Yombal Market : produits locaux, épicerie et livraison.',
        ':slug' => 'supermarket',
    ]);

    // Titre canonique « Conseils et accompagnement »
    $pdo->prepare(
        'UPDATE services SET title = :title WHERE slug = :slug'
    )->execute([
        ':title' => 'Conseils et accompagnement',
        ':slug' => 'conseils-accompagnements',
    ]);

    // Description Informatiques (projets réalisés)
    $pdo->prepare(
        'UPDATE services SET description = :desc WHERE slug = :slug'
    )->execute([
        ':desc' => 'Sites web, maintenance et assistance — réalisations : Yombal Market, Sunuru Fisquest, Univers Diaspora.',
        ':slug' => 'informatiques',
    ]);

    // Ajoute Pompes funèbres si absent (bases déjà peuplées)
    $funStmt = $pdo->prepare('SELECT id FROM services WHERE slug = :slug LIMIT 1');
    $funStmt->execute([':slug' => 'pompes-funebres']);
    if (!$funStmt->fetchColumn()) {
        $maxOrder = (int) $pdo->query('SELECT COALESCE(MAX(sort_order), 0) FROM services')->fetchColumn();
        $ins = $pdo->prepare(
            'INSERT INTO services (slug, title, description, details, icon, external_url, coming_soon, sort_order)
             VALUES (:slug, :title, :description, NULL, :icon, NULL, 0, :sort_order)'
        );
        $ins->execute([
            ':slug' => 'pompes-funebres',
            ':title' => 'Pompes funèbres',
            ':description' => 'Organisation des obsèques, rapatriement et accompagnement des familles avec dignité.',
            ':icon' => 'icon-pompes-funebres.jpg',
            ':sort_order' => $maxOrder + 10,
        ]);
        $funId = (int) $pdo->lastInsertId();
        $bIns = $pdo->prepare('INSERT INTO service_bullets (service_id, bullet, sort_order) VALUES (:sid, :bullet, :sort_order)');
        $bOrder = 0;
        foreach ([
            'Organisation des obsèques',
            'Rapatriement de corps',
            'Démarches administratives',
            'Cérémonies & hommages',
            'Accompagnement des familles',
        ] as $bullet) {
            $bOrder += 10;
            $bIns->execute([':sid' => $funId, ':bullet' => $bullet, ':sort_order' => $bOrder]);
        }
        // Placer juste avant « Autres services » si présent
        $autres = $pdo->prepare('SELECT sort_order FROM services WHERE slug = :slug LIMIT 1');
        $autres->execute([':slug' => 'bien-d-autres-services']);
        $autresOrder = $autres->fetchColumn();
        if ($autresOrder !== false) {
            $pdo->prepare('UPDATE services SET sort_order = :so WHERE slug = :slug')->execute([
                ':so' => ((int) $autresOrder) - 1,
                ':slug' => 'pompes-funebres',
            ]);
        }
    }

    // Icônes photo canoniques (icon-{slug}.jpg) — sync même si la table est déjà peuplée
    $iconSync = [
        'conseils-accompagnements' => 'icon-conseils-accompagnements.jpg',
        'immobilier-btp' => 'icon-immobilier-btp.jpg',
        'voyages' => 'icon-voyages.jpg',
        'creation-gestion-d-entreprises' => 'icon-creation-gestion-d-entreprises.jpg',
        'transports' => 'icon-transports.jpg',
        'assistances-administratives' => 'icon-assistances-administratives.jpg',
        'formations-emplois' => 'icon-formations-emplois.jpg',
        'services-a-la-personne' => 'icon-services-a-la-personne.jpg',
        'assurances-credits' => 'icon-assurances-credits.jpg',
        'informatiques' => 'icon-informatiques.jpg',
        'supermarket' => 'icon-supermarket.jpg',
        'pompes-funebres' => 'icon-pompes-funebres.jpg',
        'bien-d-autres-services' => 'icon-bien-d-autres-services.jpg',
    ];
    $iconStmt = $pdo->prepare('UPDATE services SET icon = :icon WHERE slug = :slug');
    foreach ($iconSync as $slug => $iconFile) {
        $iconStmt->execute([':icon' => $iconFile, ':slug' => $slug]);
    }

    require_once __DIR__ . '/team_members.php';
    team_members_seed_from_data_file($pdo);

    // Seed testimonials from file if table is empty
    $tCount = (int)$pdo->query('SELECT COUNT(*) FROM testimonials')->fetchColumn();
    if ($tCount === 0) {
        $tPath = dirname(__DIR__) . '/data/testimonials.php';
        if (is_file($tPath)) {
            $seed = require $tPath;
            if (is_array($seed)) {
                foreach ($seed as $row) {
                    if (!is_array($row)) {
                        continue;
                    }
                    $q = trim((string)($row['quote'] ?? ''));
                    $a = trim((string)($row['author'] ?? ''));
                    if ($q === '' || $a === '') {
                        continue;
                    }
                    $stmt = $pdo->prepare(
                        'INSERT INTO testimonials (quote, author, location, case_label, case_value, sort_order, is_published)
                         VALUES (:q, :a, :l, :cl, :cv, :so, :p)'
                    );
                    $stmt->execute([
                        ':q' => $q,
                        ':a' => $a,
                        ':l' => ($row['location'] ?? '') !== '' ? (string)$row['location'] : null,
                        ':cl' => ($row['case_label'] ?? '') !== '' ? (string)$row['case_label'] : null,
                        ':cv' => ($row['case_value'] ?? '') !== '' ? (string)$row['case_value'] : null,
                        ':so' => (int)($row['sort_order'] ?? 0),
                        ':p' => !empty($row['is_published']) ? 1 : 0,
                    ]);
                }
            }
        }
    }
}

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $config = require __DIR__ . '/../config/config.php';
    $db = $config['db'];

    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];

    $dsnWithDb = sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=%s',
        $db['host'],
        (int) $db['port'],
        $db['name'],
        $db['charset']
    );

    try {
        $pdo = new PDO($dsnWithDb, $db['user'], $db['pass'], $options);
        ensureContactSchema($pdo);
        return $pdo;
    } catch (PDOException $e) {
        $env = (string)($config['app']['env'] ?? 'dev');
        // Hostinger : la base est créée dans hPanel ; CREATE DATABASE est interdit (#1044).
        if ($env === 'production') {
            throw $e;
        }

        // Développement local : créer la base si elle n'existe pas encore.
        $dsnServer = sprintf(
            'mysql:host=%s;port=%d;charset=%s',
            $db['host'],
            (int) $db['port'],
            $db['charset']
        );

        $serverPdo = new PDO($dsnServer, $db['user'], $db['pass'], $options);
        $dbName = (string) $db['name'];
        $serverPdo->exec(
            'CREATE DATABASE IF NOT EXISTS `' . str_replace('`', '``', $dbName) . '` ' .
            'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;'
        );

        $pdo = new PDO($dsnWithDb, $db['user'], $db['pass'], $options);
        ensureContactSchema($pdo);
        return $pdo;
    }
}

