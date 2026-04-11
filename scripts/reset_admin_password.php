<?php
declare(strict_types=1);

/**
 * Réinitialise le mot de passe administrateur (ligne de commande uniquement).
 *
 * Usage :
 *   php scripts/reset_admin_password.php "VotreNouveauMotDePasse"
 *
 * Si plusieurs comptes admin existent, précisez le nom d'utilisateur :
 *   php scripts/reset_admin_password.php "VotreNouveauMotDePasse" "admin@gmail.com"
 *
 * Windows (WAMP), exemple :
 *   C:\wamp64\bin\php\php8.2.13\php.exe scripts\reset_admin_password.php "MonMotDePasseSecurise9"
 */

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Ce script doit être exécuté en ligne de commande (CLI).\n");
    exit(1);
}

$baseDir = dirname(__DIR__);
require $baseDir . '/app/db.php';

$config = require $baseDir . '/config/config.php';
$configUser = trim((string)($config['admin']['username'] ?? 'admin'));
if ($configUser === '') {
    $configUser = 'admin';
}

$password = $argv[1] ?? '';
$explicitUser = isset($argv[2]) ? trim((string)$argv[2]) : '';

if ($password === '') {
    fwrite(STDERR, "Usage: php scripts/reset_admin_password.php \"NouveauMotDePasse\" [username]\n");
    fwrite(STDERR, "Le mot de passe doit faire au moins 8 caractères.\n");
    exit(1);
}

if (strlen($password) < 8) {
    fwrite(STDERR, "Erreur : le mot de passe doit contenir au moins 8 caractères.\n");
    exit(1);
}

try {
    $pdo = db();
} catch (Throwable $e) {
    fwrite(STDERR, "Erreur base de données : " . $e->getMessage() . "\n");
    exit(1);
}

$hash = password_hash($password, PASSWORD_DEFAULT);
if ($hash === false) {
    fwrite(STDERR, "Erreur : impossible de générer le hash du mot de passe.\n");
    exit(1);
}

$stmt = $pdo->query('SELECT id, username FROM admin_users ORDER BY id ASC');
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (!is_array($rows)) {
    $rows = [];
}

if (count($rows) === 0) {
    $ins = $pdo->prepare('INSERT INTO admin_users (username, password_hash, is_active) VALUES (:u, :h, 1)');
    $ins->execute([':u' => $configUser, ':h' => $hash]);
    echo "Compte créé.\n";
    echo "Nom d'utilisateur : {$configUser}\n";
    echo "Connectez-vous sur : (votre site)/?page=admin-login\n";
    exit(0);
}

if (count($rows) === 1) {
    $id = (int)$rows[0]['id'];
    $uname = (string)$rows[0]['username'];
    $upd = $pdo->prepare('UPDATE admin_users SET password_hash = :h, is_active = 1 WHERE id = :id');
    $upd->execute([':h' => $hash, ':id' => $id]);
    echo "Mot de passe mis à jour pour l'utilisateur : {$uname}\n";
    echo "Connectez-vous sur : (votre site)/?page=admin-login\n";
    exit(0);
}

// Plusieurs comptes : le username est obligatoire en 2e argument
if ($explicitUser === '') {
    fwrite(STDERR, "Plusieurs comptes administrateurs trouvés. Précisez le nom d'utilisateur :\n");
    foreach ($rows as $r) {
        fwrite(STDERR, "  - " . (string)($r['username'] ?? '') . "\n");
    }
    fwrite(STDERR, "\nExemple :\n  php scripts/reset_admin_password.php \"MotDePasse\" \"admin@gmail.com\"\n");
    exit(1);
}

$found = false;
foreach ($rows as $r) {
    if ((string)($r['username'] ?? '') === $explicitUser) {
        $found = true;
        $id = (int)$r['id'];
        break;
    }
}

if (!$found) {
    fwrite(STDERR, "Aucun compte avec le nom d'utilisateur : {$explicitUser}\n");
    exit(1);
}

$upd = $pdo->prepare('UPDATE admin_users SET password_hash = :h, is_active = 1 WHERE id = :id');
$upd->execute([':h' => $hash, ':id' => $id]);
echo "Mot de passe mis à jour pour : {$explicitUser}\n";
echo "Connectez-vous sur : (votre site)/?page=admin-login\n";
exit(0);
