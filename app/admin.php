<?php
declare(strict_types=1);

function admin_is_logged_in(): bool
{
    return !empty($_SESSION['admin']['id']);
}

function admin_session_timeout_seconds(): int
{
    try {
        $config = require __DIR__ . '/../config/config.php';
        $v = (int)($config['admin_security']['session_timeout'] ?? 1800);
        return $v > 0 ? $v : 1800;
    } catch (Throwable $e) {
        return 1800;
    }
}

function admin_touch_session(): bool
{
    if (!admin_is_logged_in()) {
        return false;
    }
    $timeout = admin_session_timeout_seconds();
    $now = time();
    $lastSeen = (int)($_SESSION['admin']['last_seen'] ?? $now);
    if ($lastSeen > 0 && ($now - $lastSeen) > $timeout) {
        admin_logout();
        return false;
    }
    $_SESSION['admin']['last_seen'] = $now;
    return true;
}

function admin_require_login(string $baseUrl): void
{
    if (!admin_is_logged_in() || !admin_touch_session()) {
        redirect($baseUrl . '/?page=admin-login');
    }
}

function admin_role(): string
{
    $r = (string)($_SESSION['admin']['role'] ?? 'viewer');
    if (!in_array($r, ['super_admin', 'editor', 'viewer'], true)) {
        return 'viewer';
    }
    return $r;
}

function admin_role_rank(string $role): int
{
    return match ($role) {
        'super_admin' => 30,
        'editor' => 20,
        default => 10,
    };
}

function admin_has_min_role(string $role): bool
{
    return admin_role_rank(admin_role()) >= admin_role_rank($role);
}

function admin_require_min_role(string $baseUrl, string $role): void
{
    admin_require_login($baseUrl);
    if (!admin_has_min_role($role)) {
        $_SESSION['flash'] = ['error' => 'Accès refusé: permissions insuffisantes.'];
        redirect($baseUrl . '/?page=admin');
    }
}

/**
 * @return int 0 = succès, 1 = identifiants incorrects, 2 = compte désactivé
 */
function admin_login(PDO $pdo, string $username, string $password): int
{
    $stmt = $pdo->prepare('SELECT id, username, password_hash, role, is_active FROM admin_users WHERE username = :u LIMIT 1');
    $stmt->execute([':u' => $username]);
    $row = $stmt->fetch();
    if (!is_array($row)) {
        return 1;
    }
    if (empty($row['is_active'])) {
        return 2;
    }
    if (!password_verify($password, (string)$row['password_hash'])) {
        return 1;
    }
    $_SESSION['admin'] = [
        'id' => (int)$row['id'],
        'username' => (string)$row['username'],
        'role' => in_array((string)($row['role'] ?? ''), ['super_admin', 'editor', 'viewer'], true) ? (string)$row['role'] : 'viewer',
        'last_seen' => time(),
    ];
    return 0;
}

function admin_logout(): void
{
    unset($_SESSION['admin']);
}

function admin_login_security_config(): array
{
    try {
        $config = require __DIR__ . '/../config/config.php';
        $maxAttempts = (int)($config['admin_security']['max_login_attempts'] ?? 5);
        $window = (int)($config['admin_security']['login_attempt_window'] ?? 900);
        return [
            'max_attempts' => $maxAttempts > 0 ? $maxAttempts : 5,
            'window' => $window > 0 ? $window : 900,
        ];
    } catch (Throwable $e) {
        return ['max_attempts' => 5, 'window' => 900];
    }
}

function admin_login_attempt_is_limited(PDO $pdo, string $username, ?string $ip = null): bool
{
    $sec = admin_login_security_config();
    $sql = 'SELECT COUNT(*) FROM admin_login_attempts WHERE username = :u AND attempted_at >= (NOW() - INTERVAL :w SECOND)';
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':u', $username);
    $stmt->bindValue(':w', $sec['window'], PDO::PARAM_INT);
    $stmt->execute();
    $userAttempts = (int)$stmt->fetchColumn();
    if ($userAttempts >= $sec['max_attempts']) {
        return true;
    }
    if ($ip !== null && $ip !== '') {
        $sqlIp = 'SELECT COUNT(*) FROM admin_login_attempts WHERE ip = :ip AND attempted_at >= (NOW() - INTERVAL :w SECOND)';
        $stmtIp = $pdo->prepare($sqlIp);
        $stmtIp->bindValue(':ip', $ip);
        $stmtIp->bindValue(':w', $sec['window'], PDO::PARAM_INT);
        $stmtIp->execute();
        $ipAttempts = (int)$stmtIp->fetchColumn();
        if ($ipAttempts >= ($sec['max_attempts'] * 2)) {
            return true;
        }
    }
    return false;
}

function admin_login_attempt_record_failure(PDO $pdo, string $username, ?string $ip = null): void
{
    $stmt = $pdo->prepare('INSERT INTO admin_login_attempts (username, ip) VALUES (:u, :ip)');
    $stmt->execute([
        ':u' => $username,
        ':ip' => ($ip !== '' ? $ip : null),
    ]);
}

function admin_login_attempt_clear(PDO $pdo, string $username, ?string $ip = null): void
{
    $stmt = $pdo->prepare('DELETE FROM admin_login_attempts WHERE username = :u');
    $stmt->execute([':u' => $username]);
    if ($ip !== null && $ip !== '') {
        $stmtIp = $pdo->prepare('DELETE FROM admin_login_attempts WHERE ip = :ip');
        $stmtIp->execute([':ip' => $ip]);
    }
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

