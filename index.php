<?php
declare(strict_types=1);

require __DIR__ . '/app/http.php';
require __DIR__ . '/app/db.php';
require __DIR__ . '/app/admin.php';
require __DIR__ . '/app/services.php';

session_start();

$flash = $_SESSION['flash'] ?? [];
unset($_SESSION['flash']);

// Appointment POST handler
if (($_GET['action'] ?? '') === 'appointment' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $old = [
        'office' => post('office'),
        'date' => post('date'),
        'time' => post('time'),
        'name' => post('name'),
        'email' => post('email'),
        'phone' => post('phone'),
        'message' => post('message'),
        'website' => post('website'), // honeypot
    ];

    $errors = [];
    if ($old['website'] !== '') $errors['website'] = 'Spam détecté.';
    if ($old['office'] === '') $errors['office'] = 'Veuillez choisir un bureau.';
    if ($old['date'] === '') $errors['date'] = 'Veuillez choisir une date.';
    if ($old['time'] === '') $errors['time'] = 'Veuillez choisir une heure.';
    if ($old['name'] === '') $errors['name'] = 'Veuillez renseigner votre nom.';
    if ($old['email'] === '' || !filter_var($old['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Veuillez renseigner un email valide.';

    $dt = null;
    if ($old['date'] !== '' && $old['time'] !== '') {
        $dt = DateTime::createFromFormat('Y-m-d H:i', $old['date'] . ' ' . $old['time']);
        if (!$dt) $errors['date'] = 'Date/heure invalide.';
    }

    if (!empty($errors)) {
        require __DIR__ . '/pages/appointment.php';
        exit;
    }

    try {
        $pdo = db();
        $stmt = $pdo->prepare('INSERT INTO appointments (office, appointment_at, name, email, phone, message, ip, user_agent) VALUES (:office, :appointment_at, :name, :email, :phone, :message, :ip, :ua)');
        $stmt->execute([
            ':office' => $old['office'],
            ':appointment_at' => $dt ? $dt->format('Y-m-d H:i:s') : null,
            ':name' => $old['name'],
            ':email' => $old['email'],
            ':phone' => $old['phone'],
            ':message' => $old['message'],
            ':ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            ':ua' => substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255),
        ]);

        $_SESSION['flash'] = ['success' => 'Rendez-vous envoyé avec succès. Merci !'];
        redirect('./?page=rendez-vous');
    } catch (Throwable $e) {
        $_SESSION['flash'] = ['error' => "Impossible d'envoyer votre demande de rendez-vous. Réessayez plus tard."];
        redirect('./?page=rendez-vous');
    }
}

// Admin login/logout handlers (no visible link on site)
if (($_GET['action'] ?? '') === 'admin-login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $config = require __DIR__ . '/config/config.php';
    $baseUrl = rtrim($config['app']['base_url'], '/');
    $old = [
        'username' => post('username'),
    ];
    $errors = [];
    if ($old['username'] === '') $errors['username'] = 'Utilisateur requis.';
    $password = post('password');
    if ($password === '') $errors['password'] = 'Mot de passe requis.';

    if (!empty($errors)) {
        require __DIR__ . '/pages/admin/login.php';
        exit;
    }

    try {
        $pdo = db();
        if (!admin_login($pdo, $old['username'], $password)) {
            $errors['password'] = 'Identifiants invalides.';
            require __DIR__ . '/pages/admin/login.php';
            exit;
        }
        $_SESSION['flash'] = ['success' => 'Connexion réussie.'];
        redirect('./?page=admin');
    } catch (Throwable $e) {
        $_SESSION['flash'] = ['error' => 'Impossible de se connecter.'];
        redirect('./?page=admin-login');
    }
}

if (($_GET['action'] ?? '') === 'admin-logout') {
    $config = require __DIR__ . '/config/config.php';
    $baseUrl = rtrim($config['app']['base_url'], '/');
    admin_logout();
    $_SESSION['flash'] = ['success' => 'Déconnecté.'];
    redirect($baseUrl . '/');
}

// Admin: Services CRUD
if (($_GET['action'] ?? '') === 'admin-service-save' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $config = require __DIR__ . '/config/config.php';
    $baseUrl = rtrim($config['app']['base_url'], '/');
    admin_require_login($baseUrl);
    admin_csrf_verify();

    $old = [
        'id' => post('id'),
        'slug' => post('slug'),
        'title' => post('title'),
        'description' => post('description'),
        'details' => post('details'),
        'icon' => post('icon'),
        'external_url' => post('external_url'),
        'sort_order' => post('sort_order'),
        'coming_soon' => post('coming_soon'),
        'bullets_text' => post('bullets_text'),
    ];
    $errors = [];
    if ($old['slug'] === '') $errors['slug'] = 'Slug requis.';
    if ($old['title'] === '') $errors['title'] = 'Titre requis.';
    if (!preg_match('~^[a-z0-9\\-]+$~', $old['slug'])) $errors['slug'] = 'Slug: minuscules, chiffres et tirets uniquement.';

    if (!empty($old['external_url']) && !filter_var($old['external_url'], FILTER_VALIDATE_URL)) {
        $errors['external_url'] = 'URL invalide.';
    }

    if (!empty($errors)) {
        require __DIR__ . '/pages/admin/services_edit.php';
        exit;
    }

    $payload = [
        'id' => (int)$old['id'],
        'slug' => $old['slug'],
        'title' => $old['title'],
        'description' => $old['description'],
        'details' => $old['details'],
        'icon' => $old['icon'],
        'external_url' => $old['external_url'],
        'sort_order' => (int)($old['sort_order'] === '' ? 0 : $old['sort_order']),
        'coming_soon' => ($old['coming_soon'] === '1'),
        'bullets_text' => $old['bullets_text'],
    ];
    $id = services_upsert($payload);
    $_SESSION['flash'] = ['success' => 'Service enregistré.'];
    redirect('./?page=admin-services&edit=' . $id);
}

if (($_GET['action'] ?? '') === 'admin-service-delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $config = require __DIR__ . '/config/config.php';
    $baseUrl = rtrim($config['app']['base_url'], '/');
    admin_require_login($baseUrl);
    admin_csrf_verify();
    $id = (int)post('id');
    if ($id > 0) {
        services_delete($id);
        $_SESSION['flash'] = ['success' => 'Service supprimé.'];
    }
    redirect('./?page=admin-services');
}

// Admin: Manage admin users
if (($_GET['action'] ?? '') === 'admin-user-save' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $config = require __DIR__ . '/config/config.php';
    $baseUrl = rtrim($config['app']['base_url'], '/');
    admin_require_login($baseUrl);
    admin_csrf_verify();

    $id = (int)post('id');
    $username = post('username');
    $password = post('password');
    $isActive = post('is_active') === '1' ? 1 : 0;

    $errors = [];
    if ($username === '') $errors['username'] = 'Utilisateur requis.';
    if ($id === 0 && $password === '') $errors['password'] = 'Mot de passe requis.';
    if ($password !== '' && strlen($password) < 6) $errors['password'] = 'Min 6 caractères.';

    if (!empty($errors)) {
        $old = ['id' => (string)$id, 'username' => $username, 'is_active' => (string)$isActive];
        require __DIR__ . '/pages/admin/admins.php';
        exit;
    }

    $pdo = db();
    if ($id > 0) {
        if ($password !== '') {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('UPDATE admin_users SET username=:u, password_hash=:h, is_active=:a WHERE id=:id');
            $stmt->execute([':u' => $username, ':h' => $hash, ':a' => $isActive, ':id' => $id]);
        } else {
            $stmt = $pdo->prepare('UPDATE admin_users SET username=:u, is_active=:a WHERE id=:id');
            $stmt->execute([':u' => $username, ':a' => $isActive, ':id' => $id]);
        }
        $_SESSION['flash'] = ['success' => 'Administrateur mis à jour.'];
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('INSERT INTO admin_users (username, password_hash, is_active) VALUES (:u, :h, :a)');
        $stmt->execute([':u' => $username, ':h' => $hash, ':a' => $isActive]);
        $_SESSION['flash'] = ['success' => 'Administrateur ajouté.'];
    }
    redirect('./?page=admin-admins');
}

// Contact POST handler
if (($_GET['action'] ?? '') === 'contact' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $old = [
        'last_name' => post('last_name'),
        'first_name' => post('first_name'),
        'email' => post('email'),
        'phone' => post('phone'),
        'message' => post('message'),
        'consent' => post('consent'),
        'privacy' => post('privacy'),
    ];

    $errors = [];
    if ($old['last_name'] === '') $errors['last_name'] = 'Veuillez renseigner votre nom.';
    if ($old['first_name'] === '') $errors['first_name'] = 'Veuillez renseigner votre prénom.';
    if ($old['email'] === '' || !filter_var($old['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Veuillez renseigner un email valide.';
    if ($old['message'] === '') $errors['message'] = 'Veuillez écrire un message.';
    if ($old['consent'] !== '1') $errors['consent'] = 'Consentement obligatoire.';
    if ($old['privacy'] !== '1') $errors['privacy'] = 'Confirmation obligatoire.';

    if (!empty($errors)) {
        require __DIR__ . '/pages/home.php';
        exit;
    }

    try {
        $pdo = db();
        $stmt = $pdo->prepare('INSERT INTO contact_messages (last_name, first_name, email, phone, message, ip, user_agent) VALUES (:last_name, :first_name, :email, :phone, :message, :ip, :ua)');
        $stmt->execute([
            ':last_name' => $old['last_name'],
            ':first_name' => $old['first_name'],
            ':email' => $old['email'],
            ':phone' => $old['phone'],
            ':message' => $old['message'],
            ':ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            ':ua' => substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255),
        ]);

        $_SESSION['flash'] = ['success' => 'Message envoyé. Merci !'];
        redirect('./#contact');
    } catch (Throwable $e) {
        $_SESSION['flash'] = ['error' => "Impossible d'envoyer le message (DB). Vérifiez la configuration MySQL puis réessayez."];
        redirect('./#contact');
    }
}

// Simple router: home or service page by slug
$page = (string)($_GET['page'] ?? '');
if ($page === '' || $page === 'home') {
    require __DIR__ . '/pages/home.php';
    exit;
}

$specialPages = [
    'demarrer-maintenant' => __DIR__ . '/pages/start.php',
    'rendez-vous' => __DIR__ . '/pages/appointment.php',
    'admin-login' => __DIR__ . '/pages/admin/login.php',
    'admin' => __DIR__ . '/pages/admin/dashboard.php',
    'admin-services' => __DIR__ . '/pages/admin/services.php',
    'admin-admins' => __DIR__ . '/pages/admin/admins.php',
];
if (isset($specialPages[$page])) {
    require $specialPages[$page];
    exit;
}

$slug = $page;
require __DIR__ . '/pages/service.php';

