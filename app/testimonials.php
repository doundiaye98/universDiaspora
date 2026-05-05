<?php
declare(strict_types=1);

/**
 * Témoignages (DB-first) avec fallback data/testimonials.php.
 */

/**
 * @return list<array<string,mixed>>
 */
function testimonials_all(PDO $pdo = null): array
{
    if ($pdo === null) {
        try {
            $pdo = db();
        } catch (Throwable $e) {
            $pdo = null;
        }
    }
    if ($pdo instanceof PDO) {
        try {
            $stmt = $pdo->query('SELECT * FROM testimonials WHERE is_published = 1 ORDER BY sort_order ASC, id DESC');
            $rows = $stmt->fetchAll();
            if (is_array($rows)) {
                return $rows;
            }
        } catch (Throwable $e) {
            // fallback below
        }
    }
    $path = dirname(__DIR__) . '/data/testimonials.php';
    if (is_file($path)) {
        $seed = require $path;
        if (is_array($seed)) {
            return array_values(array_filter($seed, static fn($r): bool => is_array($r)));
        }
    }
    return [];
}

function testimonials_find(int $id, PDO $pdo = null): ?array
{
    if ($id <= 0) {
        return null;
    }
    $pdo = $pdo ?? db();
    $stmt = $pdo->prepare('SELECT * FROM testimonials WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $id]);
    $r = $stmt->fetch();
    return is_array($r) ? $r : null;
}

function testimonials_upsert(array $input, PDO $pdo = null): int
{
    $pdo = $pdo ?? db();
    $id = (int)($input['id'] ?? 0);
    $quote = trim((string)($input['quote'] ?? ''));
    $author = trim((string)($input['author'] ?? ''));
    $location = trim((string)($input['location'] ?? ''));
    $caseLabel = trim((string)($input['case_label'] ?? ''));
    $caseValue = trim((string)($input['case_value'] ?? ''));
    $sortOrder = (int)($input['sort_order'] ?? 0);
    $isPublished = !empty($input['is_published']) ? 1 : 0;

    if ($id > 0) {
        $stmt = $pdo->prepare(
            'UPDATE testimonials
             SET quote = :q, author = :a, location = :l, case_label = :cl, case_value = :cv, sort_order = :so, is_published = :p
             WHERE id = :id'
        );
        $stmt->execute([
            ':q' => $quote,
            ':a' => $author,
            ':l' => $location !== '' ? $location : null,
            ':cl' => $caseLabel !== '' ? $caseLabel : null,
            ':cv' => $caseValue !== '' ? $caseValue : null,
            ':so' => $sortOrder,
            ':p' => $isPublished,
            ':id' => $id,
        ]);
        return $id;
    }

    $stmt = $pdo->prepare(
        'INSERT INTO testimonials (quote, author, location, case_label, case_value, sort_order, is_published)
         VALUES (:q, :a, :l, :cl, :cv, :so, :p)'
    );
    $stmt->execute([
        ':q' => $quote,
        ':a' => $author,
        ':l' => $location !== '' ? $location : null,
        ':cl' => $caseLabel !== '' ? $caseLabel : null,
        ':cv' => $caseValue !== '' ? $caseValue : null,
        ':so' => $sortOrder,
        ':p' => $isPublished,
    ]);
    return (int)$pdo->lastInsertId();
}

function testimonials_delete(int $id, PDO $pdo = null): void
{
    if ($id <= 0) {
        return;
    }
    $pdo = $pdo ?? db();
    $stmt = $pdo->prepare('DELETE FROM testimonials WHERE id = :id');
    $stmt->execute([':id' => $id]);
}

