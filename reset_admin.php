<?php
declare(strict_types=1);

/**
 * Réinitialisation admin (usage ponctuel sur Hostinger ou en local).
 *
 * Lit username/password depuis config/config.local.php (via config.php).
 *
 * 1) Vérifiez la section `admin` dans config/config.local.php
 * 2) Ouvrez une fois : https://universdiaspora.com/reset_admin.php
 * 3) Connectez-vous sur /?page=admin-login
 * 4) Supprimez immédiatement ce fichier du serveur
 */

require __DIR__ . '/app/db.php';

$config = require __DIR__ . '/config/config.php';
$newUsername = trim((string)($config['admin']['username'] ?? ''));
$newPassword = (string)($config['admin']['password'] ?? '');

if ($newUsername === '' || $newPassword === '' || strlen($newPassword) < 8) {
    http_response_code(400);
    header('Content-Type: text/plain; charset=utf-8');
    echo "Erreur: définissez admin.username et un mot de passe (≥ 8 caractères) dans config/config.local.php.\n";
    exit;
}

try {
    $pdo = db();
    $hash = password_hash($newPassword, PASSWORD_DEFAULT);
    if ($hash === false) {
        throw new RuntimeException('Impossible de générer le hash.');
    }

    $pdo->beginTransaction();

    $stmt = $pdo->prepare('SELECT id FROM admin_users WHERE username = :u LIMIT 1');
    $stmt->execute([':u' => $newUsername]);
    $existingId = (int)($stmt->fetchColumn() ?: 0);

    if ($existingId > 0) {
        $upd = $pdo->prepare('UPDATE admin_users SET password_hash = :h, role = COALESCE(NULLIF(role, \'\'), \'super_admin\'), is_active = 1 WHERE id = :id');
        $upd->execute([
            ':h' => $hash,
            ':id' => $existingId,
        ]);
        $action = 'Compte admin mis à jour (id=' . $existingId . ', username=' . $newUsername . ').';
    } else {
        $first = $pdo->query('SELECT id FROM admin_users ORDER BY id ASC LIMIT 1');
        $firstId = (int)($first->fetchColumn() ?: 0);

        if ($firstId > 0) {
            $upd = $pdo->prepare('UPDATE admin_users SET username = :u, password_hash = :h, role = COALESCE(NULLIF(role, \'\'), \'super_admin\'), is_active = 1 WHERE id = :id');
            $upd->execute([
                ':u' => $newUsername,
                ':h' => $hash,
                ':id' => $firstId,
            ]);
            $action = 'Compte admin existant renommé/mis à jour (id=' . $firstId . ').';
        } else {
            $ins = $pdo->prepare('INSERT INTO admin_users (username, password_hash, role, is_active) VALUES (:u, :h, :r, 1)');
            $ins->execute([
                ':u' => $newUsername,
                ':h' => $hash,
                ':r' => 'super_admin',
            ]);
            $action = 'Nouveau compte admin créé (id=' . (int)$pdo->lastInsertId() . ').';
        }
    }

    $pdo->commit();

    // Débloque un éventuel rate-limit après plusieurs échecs.
    try {
        $pdo->exec('DELETE FROM admin_login_attempts');
    } catch (Throwable $ignored) {
    }

    header('Content-Type: text/plain; charset=utf-8');
    echo "OK\n";
    echo $action . "\n";
    echo "Username: {$newUsername}\n";
    echo "Password: {$newPassword}\n\n";
    echo "Connectez-vous: /?page=admin-login\n";
    echo "IMPORTANT: supprimez ce fichier reset_admin.php maintenant.\n";
} catch (Throwable $e) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Erreur: ' . $e->getMessage();
}
