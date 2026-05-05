<?php
declare(strict_types=1);

/**
 * Script de réinitialisation admin (usage ponctuel).
 *
 * 1) Modifie les 2 variables ci-dessous.
 * 2) Ouvre: http://localhost/universDiaspora/reset_admin.php
 * 3) Connecte-toi sur ?page=admin-login
 * 4) Supprime immédiatement ce fichier.
 */

$newUsername = 'admin';
$newPassword = 'ChangeMeNow!123!';

if ($newUsername === '' || $newPassword === '') {
    http_response_code(400);
    echo 'Erreur: username/password vides.';
    exit;
}

require __DIR__ . '/app/db.php';

try {
    $pdo = db();
    $hash = password_hash($newPassword, PASSWORD_DEFAULT);
    if ($hash === false) {
        throw new RuntimeException('Impossible de générer le hash.');
    }

    $pdo->beginTransaction();

    $stmt = $pdo->query('SELECT id FROM admin_users ORDER BY id ASC LIMIT 1');
    $firstId = (int)($stmt->fetchColumn() ?: 0);

    if ($firstId > 0) {
        $upd = $pdo->prepare('UPDATE admin_users SET username = :u, password_hash = :h, is_active = 1 WHERE id = :id');
        $upd->execute([
            ':u' => $newUsername,
            ':h' => $hash,
            ':id' => $firstId,
        ]);
        $action = 'Compte admin existant mis à jour (id=' . $firstId . ').';
    } else {
        $ins = $pdo->prepare('INSERT INTO admin_users (username, password_hash, is_active) VALUES (:u, :h, 1)');
        $ins->execute([
            ':u' => $newUsername,
            ':h' => $hash,
        ]);
        $action = 'Nouveau compte admin créé (id=' . (int)$pdo->lastInsertId() . ').';
    }

    $pdo->commit();

    header('Content-Type: text/plain; charset=utf-8');
    echo "OK\n";
    echo $action . "\n";
    echo "Username: {$newUsername}\n";
    echo "Password: {$newPassword}\n\n";
    echo "IMPORTANT: supprime ce fichier reset_admin.php maintenant.\n";
} catch (Throwable $e) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo "Erreur: " . $e->getMessage();
}

