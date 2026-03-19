<?php
declare(strict_types=1);

function admin_is_logged_in(): bool
{
    return !empty($_SESSION['admin']['id']);
}

function admin_require_login(string $baseUrl): void
{
    if (!admin_is_logged_in()) {
        redirect($baseUrl . '/?page=admin-login');
    }
}

function admin_login(PDO $pdo, string $username, string $password): bool
{
    $stmt = $pdo->prepare('SELECT id, username, password_hash, is_active FROM admin_users WHERE username = :u LIMIT 1');
    $stmt->execute([':u' => $username]);
    $row = $stmt->fetch();
    if (!is_array($row) || empty($row['is_active'])) {
        return false;
    }
    if (!password_verify($password, (string)$row['password_hash'])) {
        return false;
    }
    $_SESSION['admin'] = [
        'id' => (int)$row['id'],
        'username' => (string)$row['username'],
    ];
    return true;
}

function admin_logout(): void
{
    unset($_SESSION['admin']);
}

function admin_csrf_token(): string
{
    if (empty($_SESSION['admin_csrf'])) {
        $_SESSION['admin_csrf'] = bin2hex(random_bytes(16));
    }
    return (string)$_SESSION['admin_csrf'];
}

function admin_csrf_verify(): void
{
    $sent = $_POST['_csrf'] ?? '';
    $ok = is_string($sent) && !empty($_SESSION['admin_csrf']) && hash_equals((string)$_SESSION['admin_csrf'], $sent);
    if (!$ok) {
        http_response_code(400);
        exit('Bad Request');
    }
}

