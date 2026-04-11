<?php
declare(strict_types=1);

function team_members_storage_dir(): string
{
    return dirname(__DIR__) . '/public/assets/img/team';
}

function team_members_max_image_bytes(): int
{
    return 4 * 1024 * 1024;
}

/**
 * @return string|null Message d’erreur ou null si OK.
 */
function team_members_validate_image_upload(?array $file): ?string
{
    if ($file === null || !isset($file['error'])) {
        return null;
    }
    $err = (int)$file['error'];
    if ($err === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($err !== UPLOAD_ERR_OK) {
        return 'Photo : erreur d’envoi (' . $err . ').';
    }
    $tmp = (string)($file['tmp_name'] ?? '');
    if ($tmp === '' || !is_uploaded_file($tmp)) {
        return 'Photo : envoi invalide.';
    }
    $size = (int)($file['size'] ?? 0);
    if ($size <= 0 || $size > team_members_max_image_bytes()) {
        return 'Photo : fichier trop volumineux (max. 4 Mo).';
    }
    $okMime = false;
    if (function_exists('finfo_open')) {
        $f = finfo_open(FILEINFO_MIME_TYPE);
        if ($f) {
            $mime = finfo_file($f, $tmp);
            finfo_close($f);
            $okMime = in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true);
        }
    } else {
        $okMime = true;
    }
    if (!$okMime) {
        return 'Photo : utilisez JPG, PNG ou WebP.';
    }
    $head = @file_get_contents($tmp, false, null, 0, 12);
    if ($head === false || strlen($head) < 4) {
        return 'Photo : fichier image invalide.';
    }
    return null;
}

/**
 * Enregistre l’image et retourne le nom de fichier (ex. abcd1234.jpg).
 */
function team_members_store_image(array $file): string
{
    $dir = team_members_storage_dir();
    if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
        throw new RuntimeException('Impossible de créer le dossier des photos.');
    }
    $tmp = (string)$file['tmp_name'];
    $mime = 'image/jpeg';
    if (function_exists('finfo_open')) {
        $f = finfo_open(FILEINFO_MIME_TYPE);
        if ($f) {
            $m = finfo_file($f, $tmp);
            finfo_close($f);
            if ($m !== false) {
                $mime = $m;
            }
        }
    }
    $ext = 'jpg';
    if ($mime === 'image/png') {
        $ext = 'png';
    } elseif ($mime === 'image/webp') {
        $ext = 'webp';
    }
    $name = bin2hex(random_bytes(16)) . '.' . $ext;
    $dest = $dir . DIRECTORY_SEPARATOR . $name;
    if (!@move_uploaded_file($tmp, $dest)) {
        throw new RuntimeException('Impossible d’enregistrer la photo.');
    }
    return $name;
}

function team_members_delete_photo_file(?string $filename): void
{
    if ($filename === null || $filename === '') {
        return;
    }
    $base = basename($filename);
    if ($base === '' || strpos($base, '..') !== false) {
        return;
    }
    $path = team_members_storage_dir() . DIRECTORY_SEPARATOR . $base;
    if (is_file($path)) {
        @unlink($path);
    }
}

/**
 * @return list<array<string,mixed>>
 */
function team_members_all(PDO $pdo = null): array
{
    $pdo = $pdo ?? db();
    $stmt = $pdo->query('SELECT * FROM team_members ORDER BY sort_order ASC, id ASC');
    $rows = $stmt->fetchAll();
    return is_array($rows) ? $rows : [];
}

function team_members_find(int $id, PDO $pdo = null): ?array
{
    if ($id <= 0) {
        return null;
    }
    $pdo = $pdo ?? db();
    $stmt = $pdo->prepare('SELECT * FROM team_members WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $id]);
    $r = $stmt->fetch();
    return is_array($r) ? $r : null;
}

function team_members_upsert(array $input, PDO $pdo = null): int
{
    $pdo = $pdo ?? db();
    $id = (int)($input['id'] ?? 0);
    $name = trim((string)($input['name'] ?? ''));
    $role = trim((string)($input['role'] ?? ''));
    $bio = trim((string)($input['bio'] ?? ''));
    $sortOrder = (int)($input['sort_order'] ?? 0);
    $photo = isset($input['photo']) ? ($input['photo'] !== '' ? (string)$input['photo'] : null) : null;

    if ($id > 0) {
        $stmt = $pdo->prepare(
            'UPDATE team_members SET name = :n, role = :r, bio = :b, sort_order = :so, photo = :ph WHERE id = :id'
        );
        $stmt->execute([
            ':n' => $name,
            ':r' => $role !== '' ? $role : null,
            ':b' => $bio !== '' ? $bio : null,
            ':so' => $sortOrder,
            ':ph' => $photo,
            ':id' => $id,
        ]);
        return $id;
    }

    $stmt = $pdo->prepare(
        'INSERT INTO team_members (name, role, bio, photo, sort_order) VALUES (:n, :r, :b, :ph, :so)'
    );
    $stmt->execute([
        ':n' => $name,
        ':r' => $role !== '' ? $role : null,
        ':b' => $bio !== '' ? $bio : null,
        ':ph' => $photo,
        ':so' => $sortOrder,
    ]);
    return (int)$pdo->lastInsertId();
}

function team_members_delete(int $id, PDO $pdo = null): void
{
    if ($id <= 0) {
        return;
    }
    $pdo = $pdo ?? db();
    $row = team_members_find($id, $pdo);
    if ($row !== null && !empty($row['photo'])) {
        team_members_delete_photo_file((string)$row['photo']);
    }
    $stmt = $pdo->prepare('DELETE FROM team_members WHERE id = :id');
    $stmt->execute([':id' => $id]);
}

function team_members_seed_from_data_file(PDO $pdo): void
{
    $count = (int)$pdo->query('SELECT COUNT(*) FROM team_members')->fetchColumn();
    if ($count > 0) {
        return;
    }
    $path = dirname(__DIR__) . '/data/team.php';
    if (!is_file($path)) {
        return;
    }
    $seed = require $path;
    if (!is_array($seed)) {
        return;
    }
    $order = 0;
    foreach ($seed as $row) {
        if (!is_array($row)) {
            continue;
        }
        $name = trim((string)($row['name'] ?? ''));
        if ($name === '') {
            continue;
        }
        $order += 10;
        team_members_upsert([
            'id' => 0,
            'name' => $name,
            'role' => trim((string)($row['role'] ?? '')),
            'bio' => trim((string)($row['bio'] ?? '')),
            'photo' => null,
            'sort_order' => $order,
        ], $pdo);
    }
}
