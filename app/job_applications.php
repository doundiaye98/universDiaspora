<?php
declare(strict_types=1);

function job_applications_storage_dir(): string
{
    return dirname(__DIR__) . '/storage/candidatures';
}

function job_applications_max_bytes(): int
{
    return 5 * 1024 * 1024;
}

/**
 * @return string|null Error message or null if OK.
 */
function job_applications_validate_pdf_field(array $file, string $label): ?string
{
    if (!isset($file['error']) || !isset($file['tmp_name']) || !is_array($file)) {
        return $label . ' : fichier manquant.';
    }
    $err = (int)$file['error'];
    if ($err === UPLOAD_ERR_NO_FILE) {
        return $label . ' : fichier requis.';
    }
    if ($err !== UPLOAD_ERR_OK) {
        $map = [
            UPLOAD_ERR_INI_SIZE => 'Fichier trop volumineux (limite serveur).',
            UPLOAD_ERR_FORM_SIZE => 'Fichier trop volumineux.',
            UPLOAD_ERR_PARTIAL => 'Envoi incomplet.',
        ];
        if (isset($map[$err])) {
            return $label . ' : ' . $map[$err];
        }
        return $label . ' : erreur d’envoi (' . $err . ').';
    }
    $tmp = (string)$file['tmp_name'];
    if ($tmp === '' || !is_uploaded_file($tmp)) {
        return $label . ' : envoi invalide.';
    }
    $size = (int)($file['size'] ?? 0);
    if ($size <= 0) {
        return $label . ' : fichier vide.';
    }
    if ($size > job_applications_max_bytes()) {
        return $label . ' : PDF trop volumineux (max. 5 Mo).';
    }
    $name = (string)($file['name'] ?? '');
    if (!preg_match('~\\.pdf$~i', $name)) {
        return $label . ' : uniquement des fichiers PDF.';
    }
    if (function_exists('finfo_open')) {
        $f = finfo_open(FILEINFO_MIME_TYPE);
        if ($f) {
            $mime = finfo_file($f, $tmp);
            finfo_close($f);
            if ($mime !== false && $mime !== 'application/pdf') {
                return $label . ' : le fichier doit être un PDF.';
            }
        }
    }
    $head = @file_get_contents($tmp, false, null, 0, 5);
    if ($head === false || $head !== '%PDF-') {
        return $label . ' : fichier PDF invalide.';
    }
    return null;
}

/**
 * Stocke un PDF validé et retourne le chemin relatif (sous storage/candidatures).
 */
function job_applications_store_pdf(array $file, string $suffix): string
{
    $dir = job_applications_storage_dir();
    $subdir = date('Y/m');
    $targetDir = $dir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $subdir);
    if (!is_dir($targetDir) && !mkdir($targetDir, 0755, true) && !is_dir($targetDir)) {
        throw new RuntimeException('Impossible de créer le dossier de stockage.');
    }
    $token = bin2hex(random_bytes(16));
    $rel = $subdir . '/' . $token . '_' . $suffix . '.pdf';
    $dest = $dir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $rel);
    $tmp = (string)$file['tmp_name'];
    if (!@move_uploaded_file($tmp, $dest)) {
        throw new RuntimeException('Impossible d’enregistrer le fichier.');
    }
    return str_replace('\\', '/', $rel);
}

function job_applications_abs_path(string $rel): ?string
{
    $rel = trim(str_replace('\\', '/', $rel), '/');
    if ($rel === '' || strpos($rel, '..') !== false) {
        return null;
    }
    $base = realpath(job_applications_storage_dir());
    if ($base === false) {
        return null;
    }
    $full = $base . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $rel);
    $real = realpath($full);
    if ($real === false || !is_file($real)) {
        return null;
    }
    $baseNorm = rtrim(str_replace('\\', '/', $base), '/');
    $realNorm = str_replace('\\', '/', $real);
    if (!str_starts_with($realNorm, $baseNorm . '/') && $realNorm !== $baseNorm) {
        return null;
    }
    return $real;
}

/**
 * @param array{cv_path:string,cover_path:string}|null $pathsRollback si insert échoue
 */
function job_applications_insert(
    PDO $pdo,
    ?int $announcementId,
    string $fullName,
    string $email,
    string $phone,
    string $message,
    string $cvRel,
    string $coverRel
): int {
    $stmt = $pdo->prepare(
        'INSERT INTO job_applications (announcement_id, full_name, email, phone, message, cv_path, cover_path, ip, user_agent)
         VALUES (:aid, :fn, :em, :ph, :msg, :cv, :cov, :ip, :ua)'
    );
    $stmt->execute([
        ':aid' => $announcementId !== null && $announcementId > 0 ? $announcementId : null,
        ':fn' => $fullName,
        ':em' => $email,
        ':ph' => $phone !== '' ? $phone : null,
        ':msg' => $message !== '' ? $message : null,
        ':cv' => $cvRel,
        ':cov' => $coverRel,
        ':ip' => $_SERVER['REMOTE_ADDR'] ?? null,
        ':ua' => substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255),
    ]);
    return (int)$pdo->lastInsertId();
}

function job_applications_delete_files(array $paths): void
{
    foreach ($paths as $rel) {
        if (!is_string($rel) || $rel === '') {
            continue;
        }
        $abs = job_applications_abs_path($rel);
        if ($abs !== null && is_file($abs)) {
            @unlink($abs);
        }
    }
}

/**
 * @return list<array<string,mixed>>
 */
function job_applications_all(PDO $pdo, int $limit = 200): array
{
    $limit = max(1, min(500, $limit));
    $stmt = $pdo->prepare(
        'SELECT j.*, a.title AS announcement_title
         FROM job_applications j
         LEFT JOIN announcements a ON a.id = j.announcement_id
         ORDER BY j.id DESC
         LIMIT :lim'
    );
    $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
    $stmt->execute();
    $rows = $stmt->fetchAll();
    return is_array($rows) ? $rows : [];
}

function job_applications_find(int $id, PDO $pdo = null): ?array
{
    if ($id <= 0) {
        return null;
    }
    $pdo = $pdo ?? db();
    $stmt = $pdo->prepare(
        'SELECT j.*, a.title AS announcement_title
         FROM job_applications j
         LEFT JOIN announcements a ON a.id = j.announcement_id
         WHERE j.id = :id LIMIT 1'
    );
    $stmt->execute([':id' => $id]);
    $r = $stmt->fetch();
    return is_array($r) ? $r : null;
}
