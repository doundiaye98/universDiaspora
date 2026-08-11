<?php
declare(strict_types=1);

require __DIR__ . '/app/bootstrap.php';
require __DIR__ . '/app/http.php';
require __DIR__ . '/app/db.php';
require __DIR__ . '/app/admin.php';
require __DIR__ . '/app/transit.php';
require __DIR__ . '/app/services.php';
require __DIR__ . '/app/announcements.php';
require __DIR__ . '/app/job_applications.php';
require __DIR__ . '/app/team_members.php';
require __DIR__ . '/app/testimonials.php';
require __DIR__ . '/app/mailer.php';
require __DIR__ . '/app/ai_assistant.php';

session_start();

ud_security_headers();

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
        'service_slug' => post('service_slug'),
        'volet_id' => post('volet_id'),
        'website' => post('website'), // honeypot
    ];

    if ($old['service_slug'] !== '' && preg_match('/^[a-z0-9-]{1,120}$/', $old['service_slug']) !== 1) {
        $old['service_slug'] = '';
    }
    if ($old['volet_id'] !== '' && preg_match('/^[a-z0-9-]{1,120}$/', $old['volet_id']) !== 1) {
        $old['volet_id'] = '';
    }

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

    if (trim($old['message']) === '' && $old['service_slug'] !== '') {
        $apptCtx = appointment_service_context($old['service_slug'], $old['volet_id']);
        if ($apptCtx !== null) {
            $old['message'] = appointment_build_message(
                $apptCtx['service_title'],
                $apptCtx['volet_label'],
                $old['office'],
                $old['date'],
                $old['time']
            );
        }
    }

    try {
        $pdo = db();
        $stmt = $pdo->prepare(
            'INSERT INTO appointments (office, appointment_at, name, email, phone, message, service_slug, volet_id, ip, user_agent)
             VALUES (:office, :appointment_at, :name, :email, :phone, :message, :service_slug, :volet_id, :ip, :ua)'
        );
        $stmt->execute([
            ':office' => $old['office'],
            ':appointment_at' => $dt ? $dt->format('Y-m-d H:i:s') : null,
            ':name' => $old['name'],
            ':email' => $old['email'],
            ':phone' => $old['phone'],
            ':message' => $old['message'],
            ':service_slug' => $old['service_slug'] !== '' ? $old['service_slug'] : null,
            ':volet_id' => $old['volet_id'] !== '' ? $old['volet_id'] : null,
            ':ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            ':ua' => substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255),
        ]);

        // Optional notification email to admins.
        try {
            $config = require __DIR__ . '/config/config.php';
            $mailTo = (string)($config['mail']['to'] ?? '');
            if ($mailTo !== '') {
                $office = (string)$old['office'];
                $subject = '[RDV] Nouveau rendez-vous - ' . $office;
                $contextLine = '';
                if ($old['service_slug'] !== '') {
                    $contextLine = 'Service: ' . $old['service_slug'];
                    if ($old['volet_id'] !== '') {
                        $contextLine .= ' / Volet: ' . $old['volet_id'];
                    }
                    $contextLine .= "\n";
                }
                $body =
                    "Nouveau rendez-vous reçu.\n\n" .
                    'Bureau: ' . $office . "\n" .
                    'Date/heure: ' . ($dt ? $dt->format('Y-m-d H:i') : '') . "\n" .
                    $contextLine . "\n" .
                    'Nom: ' . (string)$old['name'] . "\n" .
                    'Email: ' . (string)$old['email'] . "\n" .
                    'Téléphone: ' . (string)$old['phone'] . "\n\n" .
                    'Message: ' . (string)$old['message'] . "\n";
                ud_mail_try_send($mailTo, $subject, $body);
            }
        } catch (Throwable $e) {
            // Email is optional; DB success must still redirect.
        }

        $_SESSION['flash'] = ['success' => 'Rendez-vous envoyé avec succès. Merci !'];
        redirect(ud_appointment_url(ud_site_base_url()));
    } catch (Throwable $e) {
        $_SESSION['flash'] = ['error' => "Impossible d'envoyer votre demande de rendez-vous. Réessayez plus tard."];
        redirect(ud_appointment_url(ud_site_base_url()));
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
        $clientIp = (string)($_SERVER['REMOTE_ADDR'] ?? '');
        if (admin_login_attempt_is_limited($pdo, $old['username'], $clientIp)) {
            $errors['password'] = 'Trop de tentatives. Réessayez dans quelques minutes.';
            require __DIR__ . '/pages/admin/login.php';
            exit;
        }
        $loginCode = admin_login($pdo, $old['username'], $password);
        if ($loginCode !== 0) {
            admin_login_attempt_record_failure($pdo, $old['username'], $clientIp);
            $errors['password'] = $loginCode === 2
                ? 'Ce compte est désactivé. Réactivez-le dans la base (is_active = 1) ou via un autre admin.'
                : 'Identifiants invalides.';
            require __DIR__ . '/pages/admin/login.php';
            exit;
        }
        admin_login_attempt_clear($pdo, $old['username'], $clientIp);
        session_regenerate_id(true);
        $_SESSION['flash'] = ['success' => 'Connexion réussie.'];
        redirect('./?page=admin');
    } catch (Throwable $e) {
        @error_log('[admin-login] ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
        $msg = $e->getMessage();
        $isDbAuth = str_contains($msg, '1045') || stripos($msg, 'Access denied') !== false;
        $_SESSION['flash'] = [
            'error' => $isDbAuth
                ? 'Base MySQL inaccessible (identifiants DB incorrects dans config/config.local.php). Corrigez db.user / db.pass via hPanel, puis rechargez /health.php.'
                : 'Impossible de se connecter (erreur serveur). Vérifiez /health.php puis relancez /reset_admin.php.',
        ];
        redirect('./?page=admin-login');
    }
}

// GET ?action=admin-login → même page que ?page=admin-login (sinon on tombe sur l'accueil car "page" est vide)
if (($_GET['action'] ?? '') === 'admin-login' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    redirect('./?page=admin-login');
}

if (($_GET['action'] ?? '') === 'admin-logout') {
    $config = require __DIR__ . '/config/config.php';
    $baseUrl = rtrim($config['app']['base_url'], '/');
    admin_logout();
    $_SESSION['flash'] = ['success' => 'Déconnecté.'];
    redirect($baseUrl . '/');
}

// Admin: Confirm / cancel appointments
if (($_GET['action'] ?? '') === 'admin-appointment-status' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $config = require __DIR__ . '/config/config.php';
    $baseUrl = rtrim($config['app']['base_url'], '/');
    admin_require_min_role($baseUrl, 'editor');
    admin_csrf_verify();

    $id = (int) post('id');
    $status = post('status');

    $allowed = ['pending', 'confirmed', 'cancelled'];
    if ($id <= 0 || !in_array($status, $allowed, true)) {
        $_SESSION['flash'] = ['error' => 'Requête invalide.'];
        redirect('./?page=admin-messages');
    }

    try {
        $pdo = db();
        $prevStmt = $pdo->prepare('SELECT status, name, email, office, appointment_at FROM appointments WHERE id = :id LIMIT 1');
        $prevStmt->execute([':id' => $id]);
        $prev = $prevStmt->fetch();
        if (!is_array($prev)) {
            $_SESSION['flash'] = ['error' => 'Rendez-vous introuvable.'];
            redirect('./?page=admin-messages');
        }

        $confirmedAt = $status === 'confirmed' ? date('Y-m-d H:i:s') : null;
        $confirmedBy = $status === 'confirmed' ? ($_SESSION['admin']['username'] ?? null) : null;

        $stmt = $pdo->prepare(
            'UPDATE appointments
             SET status = :s,
                 confirmed_at = :ca,
                 confirmed_by = :cb
             WHERE id = :id'
        );
        $stmt->execute([
            ':s' => $status,
            ':ca' => $confirmedAt,
            ':cb' => $confirmedBy,
            ':id' => $id,
        ]);

        $flashSuccess = 'Statut du rendez-vous mis à jour.';
        $becomesConfirmed = $status === 'confirmed' && (($prev['status'] ?? '') !== 'confirmed');
        if ($becomesConfirmed) {
            try {
                $sent = ud_mail_appointment_confirmed_to_client($prev, $config);
                $flashSuccess = $sent
                    ? 'Rendez-vous confirmé. Un e-mail de confirmation a été envoyé au client.'
                    : 'Rendez-vous confirmé. L’e-mail n’a pas pu être envoyé (activez mail.enable et SMTP dans la configuration, ou vérifiez l’adresse e-mail du client).';
            } catch (Throwable $e) {
                $flashSuccess = 'Rendez-vous confirmé. L’envoi de l’e-mail au client a échoué.';
            }
        }

        $_SESSION['flash'] = ['success' => $flashSuccess];
        redirect('./?page=admin-messages');
    } catch (Throwable $e) {
        $_SESSION['flash'] = ['error' => 'Impossible de mettre à jour le statut.'];
        redirect('./?page=admin-messages');
    }
}

// Admin: Services CRUD
if (($_GET['action'] ?? '') === 'admin-service-save' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $config = require __DIR__ . '/config/config.php';
    $baseUrl = rtrim($config['app']['base_url'], '/');
    admin_require_min_role($baseUrl, 'editor');
    admin_csrf_verify();

    $old = [
        'id' => post('id'),
        'slug' => post('slug'),
        'title' => post('title'),
        'description' => post('description'),
        'details' => post('details'),
        'details_is_html' => post('details_is_html'),
        'step1_title' => post('step1_title'),
        'step1_text' => post('step1_text'),
        'step2_title' => post('step2_title'),
        'step2_text' => post('step2_text'),
        'step3_title' => post('step3_title'),
        'step3_text' => post('step3_text'),
        'faq1_q' => post('faq1_q'),
        'faq1_a' => post('faq1_a'),
        'faq2_q' => post('faq2_q'),
        'faq2_a' => post('faq2_a'),
        'faq3_q' => post('faq3_q'),
        'faq3_a' => post('faq3_a'),
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

    $iconUploadErr = service_icon_validate_upload($_FILES['icon_upload'] ?? null);
    if ($iconUploadErr !== null) {
        $errors['icon_upload'] = $iconUploadErr;
    }

    if (!empty($errors)) {
        require __DIR__ . '/pages/admin/services.php';
        exit;
    }

    $iconFile = $_FILES['icon_upload'] ?? null;
    $iconUploadedName = null;
    if (
        $iconFile !== null
        && isset($iconFile['error'])
        && (int)$iconFile['error'] === UPLOAD_ERR_OK
    ) {
        try {
            $old['icon'] = service_icon_store_upload($iconFile, $old['slug']);
            $iconUploadedName = $old['icon'];
        } catch (Throwable $e) {
            $errors['icon_upload'] = 'Photo du service : enregistrement impossible.';
            require __DIR__ . '/pages/admin/services.php';
            exit;
        }
    }

    $payload = [
        'id' => (int)$old['id'],
        'slug' => $old['slug'],
        'title' => $old['title'],
        'description' => $old['description'],
        'details' => $old['details'],
        'details_is_html' => ($old['details_is_html'] === '1'),
        'step1_title' => $old['step1_title'],
        'step1_text' => $old['step1_text'],
        'step2_title' => $old['step2_title'],
        'step2_text' => $old['step2_text'],
        'step3_title' => $old['step3_title'],
        'step3_text' => $old['step3_text'],
        'faq1_q' => $old['faq1_q'],
        'faq1_a' => $old['faq1_a'],
        'faq2_q' => $old['faq2_q'],
        'faq2_a' => $old['faq2_a'],
        'faq3_q' => $old['faq3_q'],
        'faq3_a' => $old['faq3_a'],
        'icon' => $old['icon'],
        'external_url' => $old['external_url'],
        'sort_order' => (int)($old['sort_order'] === '' ? 0 : $old['sort_order']),
        'coming_soon' => ($old['coming_soon'] === '1'),
        'bullets_text' => $old['bullets_text'],
    ];
    $id = services_upsert($payload);
    $flashSuccess = 'Service enregistré.';
    if ($iconUploadedName !== null && $iconUploadedName !== '') {
        $flashSuccess .= ' Nouvelle photo : ' . $iconUploadedName;
    }
    $_SESSION['flash'] = ['success' => $flashSuccess];
    redirect('./?page=admin-services&edit=' . $id);
}

if (($_GET['action'] ?? '') === 'admin-service-delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $config = require __DIR__ . '/config/config.php';
    $baseUrl = rtrim($config['app']['base_url'], '/');
    admin_require_min_role($baseUrl, 'editor');
    admin_csrf_verify();
    $id = (int)post('id');
    if ($id > 0) {
        services_delete($id);
        $_SESSION['flash'] = ['success' => 'Service supprimé.'];
    }
    redirect('./?page=admin-services');
}

// Admin: Offres & recrutement
if (($_GET['action'] ?? '') === 'admin-announcement-save' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $config = require __DIR__ . '/config/config.php';
    $baseUrl = rtrim($config['app']['base_url'], '/');
    admin_require_min_role($baseUrl, 'editor');
    admin_csrf_verify();

    $old = [
        'id' => post('id'),
        'category' => post('category'),
        'title' => post('title'),
        'summary' => post('summary'),
        'content' => post('content'),
        'sort_order' => post('sort_order'),
        'is_published' => post('is_published'),
    ];
    $errors = [];
    if (trim((string)$old['title']) === '') {
        $errors['title'] = 'Titre requis.';
    }
    $cat = (string)$old['category'];
    if (!in_array($cat, ['offre', 'recrutement'], true)) {
        $errors['category'] = 'Catégorie invalide.';
    }

    if (!empty($errors)) {
        require __DIR__ . '/pages/admin/announcements.php';
        exit;
    }

    $payload = [
        'id' => (int)$old['id'],
        'category' => $cat,
        'title' => trim((string)$old['title']),
        'summary' => trim((string)$old['summary']),
        'content' => trim((string)$old['content']),
        'sort_order' => (int)($old['sort_order'] === '' ? 0 : $old['sort_order']),
        'is_published' => ($old['is_published'] === '1'),
    ];
    $id = announcements_upsert($payload);
    $_SESSION['flash'] = ['success' => 'Annonce enregistrée.'];
    redirect('./?page=admin-announcements&edit=' . $id);
}

if (($_GET['action'] ?? '') === 'admin-announcement-delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $config = require __DIR__ . '/config/config.php';
    $baseUrl = rtrim($config['app']['base_url'], '/');
    admin_require_min_role($baseUrl, 'editor');
    admin_csrf_verify();
    $id = (int)post('id');
    if ($id > 0) {
        announcements_delete($id);
        $_SESSION['flash'] = ['success' => 'Annonce supprimée.'];
    }
    redirect('./?page=admin-announcements');
}

// Admin: membres de l'équipe (CRUD + photo)
if (($_GET['action'] ?? '') === 'admin-team-member-save' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $config = require __DIR__ . '/config/config.php';
    $baseUrl = rtrim($config['app']['base_url'], '/');
    admin_require_min_role($baseUrl, 'editor');
    admin_csrf_verify();

    $pdo = db();
    $id = (int)post('id');
    $old = [
        'id' => $id,
        'name' => post('name'),
        'role' => post('role'),
        'bio' => post('bio'),
        'sort_order' => post('sort_order'),
    ];
    $errors = [];
    if (trim((string)$old['name']) === '') {
        $errors['name'] = 'Nom requis.';
    }

    $file = isset($_FILES['photo']) && is_array($_FILES['photo']) ? $_FILES['photo'] : null;
    $hasNewUpload = $file !== null && (int)($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;
    if ($hasNewUpload) {
        $imgErr = team_members_validate_image_upload($file);
        if ($imgErr !== null) {
            $errors['photo'] = $imgErr;
        }
    }

    if (!empty($errors)) {
        require __DIR__ . '/pages/admin/team_members.php';
        exit;
    }

    $removePhoto = post('remove_photo') === '1';
    $existing = $id > 0 ? team_members_find($id, $pdo) : null;
    $photoFilename = $existing && !empty($existing['photo']) ? (string)$existing['photo'] : null;

    if ($removePhoto) {
        if ($photoFilename !== null) {
            team_members_delete_photo_file($photoFilename);
        }
        $photoFilename = null;
    }

    if ($hasNewUpload && ($file['error'] ?? 0) === UPLOAD_ERR_OK) {
        try {
            $newName = team_members_store_image($file);
            if ($photoFilename !== null) {
                team_members_delete_photo_file($photoFilename);
            }
            $photoFilename = $newName;
        } catch (Throwable $e) {
            $_SESSION['flash'] = ['error' => 'Impossible d’enregistrer la photo.'];
            redirect('./?page=admin-team-members&edit=' . ($id > 0 ? $id : 'new'));
        }
    }

    try {
        $newId = team_members_upsert([
            'id' => $id,
            'name' => trim((string)$old['name']),
            'role' => trim((string)$old['role']),
            'bio' => trim((string)$old['bio']),
            'sort_order' => (int)($old['sort_order'] === '' ? 0 : $old['sort_order']),
            'photo' => $photoFilename,
        ], $pdo);
        $_SESSION['flash'] = ['success' => 'Membre enregistré.'];
        redirect('./?page=admin-team-members&edit=' . $newId);
    } catch (Throwable $e) {
        $_SESSION['flash'] = ['error' => 'Impossible d’enregistrer le membre.'];
        redirect('./?page=admin-team-members');
    }
}

if (($_GET['action'] ?? '') === 'admin-team-member-delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $config = require __DIR__ . '/config/config.php';
    $baseUrl = rtrim($config['app']['base_url'], '/');
    admin_require_min_role($baseUrl, 'editor');
    admin_csrf_verify();
    $id = (int)post('id');
    if ($id > 0) {
        team_members_delete($id);
        $_SESSION['flash'] = ['success' => 'Membre supprimé.'];
    }
    redirect('./?page=admin-team-members');
}

// Admin: témoignages (CRUD)
if (($_GET['action'] ?? '') === 'admin-testimonial-save' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $config = require __DIR__ . '/config/config.php';
    $baseUrl = rtrim($config['app']['base_url'], '/');
    admin_require_min_role($baseUrl, 'editor');
    admin_csrf_verify();
    $id = (int)post('id');
    $old = [
        'id' => $id,
        'quote' => post('quote'),
        'author' => post('author'),
        'location' => post('location'),
        'case_label' => post('case_label'),
        'case_value' => post('case_value'),
        'sort_order' => post('sort_order'),
        'is_published' => post('is_published'),
    ];
    $errors = [];
    if (trim((string)$old['quote']) === '') $errors['quote'] = 'Témoignage requis.';
    if (trim((string)$old['author']) === '') $errors['author'] = 'Auteur requis.';
    if (!empty($errors)) {
        require __DIR__ . '/pages/admin/testimonials.php';
        exit;
    }
    try {
        $newId = testimonials_upsert([
            'id' => $id,
            'quote' => trim((string)$old['quote']),
            'author' => trim((string)$old['author']),
            'location' => trim((string)$old['location']),
            'case_label' => trim((string)$old['case_label']),
            'case_value' => trim((string)$old['case_value']),
            'sort_order' => (int)($old['sort_order'] === '' ? 0 : $old['sort_order']),
            'is_published' => ((string)$old['is_published'] === '1'),
        ]);
        $_SESSION['flash'] = ['success' => 'Témoignage enregistré.'];
        redirect('./?page=admin-testimonials&edit=' . $newId);
    } catch (Throwable $e) {
        $_SESSION['flash'] = ['error' => 'Impossible d’enregistrer le témoignage.'];
        redirect('./?page=admin-testimonials');
    }
}

if (($_GET['action'] ?? '') === 'admin-testimonial-delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $config = require __DIR__ . '/config/config.php';
    $baseUrl = rtrim($config['app']['base_url'], '/');
    admin_require_min_role($baseUrl, 'editor');
    admin_csrf_verify();
    $id = (int)post('id');
    if ($id > 0) {
        testimonials_delete($id);
        $_SESSION['flash'] = ['success' => 'Témoignage supprimé.'];
    }
    redirect('./?page=admin-testimonials');
}

// Admin: télécharger un PDF de candidature (CV ou lettre)
if (($_GET['action'] ?? '') === 'admin-job-application-file') {
    $config = require __DIR__ . '/config/config.php';
    $baseUrl = rtrim($config['app']['base_url'], '/');
    admin_require_min_role($baseUrl, 'editor');
    $id = (int)($_GET['id'] ?? 0);
    $kind = (string)($_GET['kind'] ?? '');
    if ($id <= 0 || !in_array($kind, ['cv', 'cover'], true)) {
        http_response_code(404);
        echo 'Not found';
        exit;
    }
    $pdo = db();
    $row = job_applications_find($id, $pdo);
    if (!is_array($row)) {
        http_response_code(404);
        echo 'Not found';
        exit;
    }
    $rel = $kind === 'cv' ? (string)($row['cv_path'] ?? '') : (string)($row['cover_path'] ?? '');
    $abs = job_applications_abs_path($rel);
    if ($abs === null) {
        http_response_code(404);
        echo 'Not found';
        exit;
    }
    $filename = $kind === 'cv' ? 'cv.pdf' : 'lettre-motivation.pdf';
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . (string)filesize($abs));
    readfile($abs);
    exit;
}

// Admin: Manage admin users
if (($_GET['action'] ?? '') === 'admin-user-save' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $config = require __DIR__ . '/config/config.php';
    $baseUrl = rtrim($config['app']['base_url'], '/');
    admin_require_min_role($baseUrl, 'super_admin');
    admin_csrf_verify();

    $id = (int)post('id');
    $username = post('username');
    $password = post('password');
    $role = post('role');
    $isActive = post('is_active') === '1' ? 1 : 0;

    $errors = [];
    if ($username === '') $errors['username'] = 'Utilisateur requis.';
    if ($id === 0 && $password === '') $errors['password'] = 'Mot de passe requis.';
    if ($password !== '' && strlen($password) < 6) $errors['password'] = 'Min 6 caractères.';
    if (!in_array($role, ['super_admin', 'editor', 'viewer'], true)) $errors['role'] = 'Rôle invalide.';
    if ($id > 0 && (int)($_SESSION['admin']['id'] ?? 0) === $id && $role !== 'super_admin') {
        $errors['role'] = 'Votre propre compte doit rester super_admin.';
    }

    if (!empty($errors)) {
        $old = ['id' => (string)$id, 'username' => $username, 'role' => $role, 'is_active' => (string)$isActive];
        require __DIR__ . '/pages/admin/admins.php';
        exit;
    }

    $pdo = db();
    if ($id > 0) {
        if ($password !== '') {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('UPDATE admin_users SET username=:u, password_hash=:h, role=:r, is_active=:a WHERE id=:id');
            $stmt->execute([':u' => $username, ':h' => $hash, ':r' => $role, ':a' => $isActive, ':id' => $id]);
        } else {
            $stmt = $pdo->prepare('UPDATE admin_users SET username=:u, role=:r, is_active=:a WHERE id=:id');
            $stmt->execute([':u' => $username, ':r' => $role, ':a' => $isActive, ':id' => $id]);
        }
        if ((int)($_SESSION['admin']['id'] ?? 0) === $id) {
            $_SESSION['admin']['role'] = $role;
        }
        $_SESSION['flash'] = ['success' => 'Administrateur mis à jour.'];
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('INSERT INTO admin_users (username, password_hash, role, is_active) VALUES (:u, :h, :r, :a)');
        $stmt->execute([':u' => $username, ':h' => $hash, ':r' => $role, ':a' => $isActive]);
        $_SESSION['flash'] = ['success' => 'Administrateur ajouté.'];
    }
    redirect('./?page=admin-admins');
}

// Témoignage visiteur (modération admin avant publication)
if (($_GET['action'] ?? '') === 'testimonial-submit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $old = [
        'quote' => post('quote'),
        'author' => post('author'),
        'location' => post('location'),
        'email' => post('email'),
        'consent' => post('consent'),
        'privacy' => post('privacy'),
        'website' => post('website'),
    ];

    $errors = [];
    if ($old['website'] !== '') {
        redirect('./#temoignages');
    }
    if ($old['quote'] === '' || mb_strlen($old['quote']) < 20) {
        $errors['quote'] = 'Votre commentaire doit contenir au moins 20 caractères.';
    } elseif (mb_strlen($old['quote']) > 2000) {
        $errors['quote'] = 'Commentaire trop long (2000 caractères max.).';
    }
    if ($old['author'] === '' || mb_strlen($old['author']) > 120) {
        $errors['author'] = 'Veuillez indiquer votre prénom ou pseudo (120 caractères max.).';
    }
    if ($old['location'] !== '' && mb_strlen($old['location']) > 120) {
        $errors['location'] = 'Localisation trop longue.';
    }
    if ($old['email'] !== '' && !filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'E-mail invalide.';
    }
    if ($old['consent'] !== '1') {
        $errors['consent'] = 'Consentement obligatoire.';
    }
    if ($old['privacy'] !== '1') {
        $errors['privacy'] = 'Confirmation obligatoire.';
    }

    if (!empty($errors)) {
        $_SESSION['testimonial_errors'] = $errors;
        $_SESSION['testimonial_old'] = $old;
        redirect('./#temoignages');
    }

    try {
        $pdo = db();
        testimonials_submit_visitor(
            $old['quote'],
            $old['author'],
            $old['location'],
            $old['email'],
            $_SERVER['REMOTE_ADDR'] ?? null,
            substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255),
            $pdo
        );
        try {
            $config = require __DIR__ . '/config/config.php';
            $mailTo = (string)($config['mail']['to'] ?? '');
            if ($mailTo !== '') {
                $subject = '[Témoignage] Nouveau commentaire à modérer';
                $body =
                    "Un visiteur a laissé un témoignage (en attente de publication).\n\n" .
                    'Auteur affiché : ' . $old['author'] . "\n" .
                    ($old['location'] !== '' ? 'Localisation : ' . $old['location'] . "\n" : '') .
                    ($old['email'] !== '' ? 'E-mail : ' . $old['email'] . "\n" : '') .
                    "\nCommentaire :\n" . $old['quote'] . "\n\n" .
                    'Modération : ' . rtrim((string)($config['app']['base_url'] ?? ''), '/') . '/?page=admin-testimonials' . "\n";
                ud_mail_try_send($mailTo, $subject, $body);
            }
        } catch (Throwable $e) {
            // Email optionnel
        }
        $_SESSION['flash'] = ['success' => 'Merci ! Votre témoignage a été reçu. Il sera publié après validation par notre équipe.'];
    } catch (Throwable $e) {
        $_SESSION['flash'] = ['error' => 'Impossible d’enregistrer votre témoignage. Réessayez plus tard.'];
    }
    redirect('./#temoignages');
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

        // Optional notification email to admins.
        try {
            $config = require __DIR__ . '/config/config.php';
            $mailTo = (string)($config['mail']['to'] ?? '');
            if ($mailTo !== '') {
                $fullName = trim((string)($old['first_name'] ?? '') . ' ' . (string)($old['last_name'] ?? ''));
                $subject = '[Contact] Nouveau message - ' . $fullName;
                $body =
                    "Nouveau message reçu.\n\n" .
                    'Nom: ' . $fullName . "\n" .
                    'Email: ' . (string)$old['email'] . "\n" .
                    'Téléphone: ' . (string)$old['phone'] . "\n\n" .
                    'Message: ' . (string)$old['message'] . "\n";
                ud_mail_try_send($mailTo, $subject, $body);
            }
        } catch (Throwable $e) {
            // Email is optional; DB success must still redirect.
        }

        $_SESSION['flash'] = ['success' => 'Message envoyé. Merci !'];
        redirect('./#contact');
    } catch (Throwable $e) {
        $_SESSION['flash'] = ['error' => "Impossible d'envoyer le message (DB). Vérifiez la configuration MySQL puis réessayez."];
        redirect('./#contact');
    }
}

// Candidature (recrutement) — CV + lettre de motivation en PDF
if (($_GET['action'] ?? '') === 'job-application' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $config = require __DIR__ . '/config/config.php';
    $baseUrl = rtrim($config['app']['base_url'], '/');

    $announcementId = (int)($_POST['announcement_id'] ?? 0);
    $old = [
        'announcement_id' => $announcementId,
        'full_name' => trim((string)($_POST['full_name'] ?? '')),
        'email' => trim((string)($_POST['email'] ?? '')),
        'phone' => trim((string)($_POST['phone'] ?? '')),
        'message' => trim((string)($_POST['message'] ?? '')),
        'website' => trim((string)($_POST['website'] ?? '')),
    ];

    $errors = [];
    if ($old['website'] !== '') {
        redirect('./?page=offres-recrutement');
    }

    $pdo = db();
    $ann = announcements_find_public_recruitment($announcementId, $pdo);
    if (!$ann) {
        $errors['announcement'] = 'Cette offre n’est plus disponible ou est introuvable.';
    }

    if ($old['full_name'] === '' || mb_strlen($old['full_name']) > 200) {
        $errors['full_name'] = 'Veuillez renseigner votre nom et prénom (200 caractères max.).';
    }
    if ($old['email'] === '' || !filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Veuillez renseigner un email valide.';
    }
    if ($old['phone'] !== '' && mb_strlen($old['phone']) > 50) {
        $errors['phone'] = 'Téléphone trop long.';
    }
    if (mb_strlen($old['message']) > 4000) {
        $errors['message'] = 'Message trop long.';
    }

    $cvFile = isset($_FILES['cv']) && is_array($_FILES['cv']) ? $_FILES['cv'] : null;
    $coverFile = isset($_FILES['cover_letter']) && is_array($_FILES['cover_letter']) ? $_FILES['cover_letter'] : null;

    if (!is_array($cvFile)) {
        $errors['cv'] = 'CV (PDF) requis.';
    } else {
        $e = job_applications_validate_pdf_field($cvFile, 'CV');
        if ($e !== null) {
            $errors['cv'] = $e;
        }
    }
    if (!is_array($coverFile)) {
        $errors['cover_letter'] = 'Lettre de motivation (PDF) requise.';
    } else {
        $e = job_applications_validate_pdf_field($coverFile, 'Lettre de motivation');
        if ($e !== null) {
            $errors['cover_letter'] = $e;
        }
    }

    if (!empty($errors)) {
        $_SESSION['job_apply_errors'] = $errors;
        $_SESSION['job_apply_old'] = $old;
        $q = $announcementId > 0 ? '&apply=' . $announcementId : '';
        redirect('./?page=offres-recrutement' . $q);
    }

    try {
        $cvRel = job_applications_store_pdf($cvFile, 'cv');
        $coverRel = job_applications_store_pdf($coverFile, 'lm');
    } catch (Throwable $e) {
        $_SESSION['job_apply_errors'] = ['global' => 'Impossible d’enregistrer les fichiers. Réessayez.'];
        $_SESSION['job_apply_old'] = $old;
        redirect('./?page=offres-recrutement&apply=' . $announcementId);
    }

    try {
        job_applications_insert(
            $pdo,
            $announcementId,
            $old['full_name'],
            $old['email'],
            $old['phone'],
            $old['message'],
            $cvRel,
            $coverRel
        );
        $mailRhOk = false;
        try {
            if ($ann !== null) {
                $cvAbs = job_applications_abs_path($cvRel);
                $coverAbs = job_applications_abs_path($coverRel);
                if ($cvAbs !== null && $coverAbs !== null) {
                    $mailRhOk = ud_mail_job_application_to_rh(
                        $config,
                        $ann,
                        $old['full_name'],
                        $old['email'],
                        $old['phone'],
                        $old['message'],
                        $cvAbs,
                        $coverAbs,
                        $baseUrl
                    );
                }
            }
        } catch (Throwable $e) {
            $mailRhOk = false;
        }
    } catch (Throwable $e) {
        job_applications_delete_files([$cvRel, $coverRel]);
        $_SESSION['flash'] = ['error' => 'Impossible d’enregistrer la candidature. Réessayez plus tard.'];
        redirect('./?page=offres-recrutement&apply=' . $announcementId);
    }

    if ($mailRhOk) {
        $_SESSION['flash'] = ['success' => 'Votre candidature a bien été envoyée au service RH (CV et lettre de motivation). Merci !'];
    } else {
        $_SESSION['flash'] = [
            'success' => 'Votre candidature a été enregistrée. Si vous ne recevez pas de confirmation, contactez-nous : l’envoi automatique au service RH n’a pas abouti (vérifiez la configuration e-mail du site).',
        ];
    }
    redirect('./?page=offres-recrutement');
}

// Public AI assistant endpoint
if (($_GET['action'] ?? '') === 'ai-chat') {
    $config = require __DIR__ . '/config/config.php';
    $baseUrl = rtrim((string)($config['app']['base_url'] ?? ''), '/');
    ud_ai_assistant_handle_http($baseUrl);
}

// Simple router: home or service page by slug
$page = (string)($_GET['page'] ?? '');

// Anciennes URLs ?page=rendez-vous&service=… → /rendez-vous/slug/volet
if ($page === 'rendez-vous') {
    if (!str_starts_with(ud_request_path(), '/rendez-vous')) {
        $svc = trim((string)($_GET['service'] ?? ''));
        $vol = trim((string)($_GET['volet'] ?? ''));
        $hasService = $svc !== '' && preg_match('/^[a-z0-9-]{1,120}$/', $svc) === 1;
        $hasVolet = $vol !== '' && preg_match('/^[a-z0-9-]{1,120}$/', $vol) === 1;
        redirect(ud_appointment_url(
            ud_site_base_url(),
            $hasService ? $svc : null,
            $hasVolet ? $vol : null
        ));
    }
}

if ($page === '' || $page === 'home') {
    require __DIR__ . '/pages/home.php';
    exit;
}

$specialPages = [
    'demarrer-maintenant' => __DIR__ . '/pages/start.php',
    'rendez-vous' => __DIR__ . '/pages/appointment.php',
    'apropos' => __DIR__ . '/pages/apropos.php',
    'equipe' => __DIR__ . '/pages/equipe.php',
    'mentions-legales' => __DIR__ . '/pages/mentions_legales.php',
    'politique-confidentialite' => __DIR__ . '/pages/politique_confidentialite.php',
    'offres-recrutement' => __DIR__ . '/pages/offres_recrutement.php',
    'immobilier-btp' => __DIR__ . '/pages/immobilier_btp.php',
    'admin-login' => __DIR__ . '/pages/admin/login.php',
    'admin' => __DIR__ . '/pages/admin/dashboard.php',
    'admin-services' => __DIR__ . '/pages/admin/services.php',
    'admin-admins' => __DIR__ . '/pages/admin/admins.php',
    'admin-messages' => __DIR__ . '/pages/admin/messages.php',
    'admin-ai-conversations' => __DIR__ . '/pages/admin/ai_conversations.php',
    'admin-announcements' => __DIR__ . '/pages/admin/announcements.php',
    'admin-job-applications' => __DIR__ . '/pages/admin/job_applications.php',
    'admin-team-members' => __DIR__ . '/pages/admin/team_members.php',
    'admin-testimonials' => __DIR__ . '/pages/admin/testimonials.php',
];
if (isset($specialPages[$page])) {
    require $specialPages[$page];
    exit;
}

$slug = $page;
require __DIR__ . '/pages/service.php';

