<?php
declare(strict_types=1);

/**
 * Témoignages (DB-first) avec fallback data/testimonials.php.
 */

/**
 * @return list<array<string,mixed>>
 */
function testimonials_all(PDO $pdo = null, bool $publishedOnly = true): array
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
            $sql = 'SELECT * FROM testimonials';
            if ($publishedOnly) {
                $sql .= ' WHERE is_published = 1';
            }
            $sql .= ' ORDER BY sort_order ASC, id DESC';
            $stmt = $pdo->query($sql);
            $rows = $stmt->fetchAll();
            if (is_array($rows)) {
                return $rows;
            }
        } catch (Throwable $e) {
            // fallback below
        }
    }
    if ($publishedOnly) {
        // suite du fallback fichier seed…
    } else {
        return [];
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

    $submitterEmail = trim((string)($input['submitter_email'] ?? ''));
    $ip = isset($input['ip']) ? (string)$input['ip'] : null;
    $ua = isset($input['user_agent']) ? substr((string)$input['user_agent'], 0, 255) : null;

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

    $hasVisitorCols = testimonials_has_visitor_columns($pdo);
    if ($hasVisitorCols) {
        $stmt = $pdo->prepare(
            'INSERT INTO testimonials (quote, author, location, case_label, case_value, sort_order, is_published, submitter_email, ip, user_agent)
             VALUES (:q, :a, :l, :cl, :cv, :so, :p, :se, :ip, :ua)'
        );
        $stmt->execute([
            ':q' => $quote,
            ':a' => $author,
            ':l' => $location !== '' ? $location : null,
            ':cl' => $caseLabel !== '' ? $caseLabel : null,
            ':cv' => $caseValue !== '' ? $caseValue : null,
            ':so' => $sortOrder,
            ':p' => $isPublished,
            ':se' => $submitterEmail !== '' ? $submitterEmail : null,
            ':ip' => $ip !== '' ? $ip : null,
            ':ua' => $ua,
        ]);
    } else {
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
    }
    return (int)$pdo->lastInsertId();
}

function testimonials_has_visitor_columns(PDO $pdo): bool
{
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }
    try {
        $cache = (int)$pdo->query(
            "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'testimonials' AND COLUMN_NAME = 'submitter_email'"
        )->fetchColumn() > 0;
    } catch (Throwable $e) {
        $cache = false;
    }
    return $cache;
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

/**
 * Témoignage soumis par un visiteur (non publié tant qu’un admin ne valide pas).
 */
function testimonials_submit_visitor(
    string $quote,
    string $author,
    string $location = '',
    string $submitterEmail = '',
    ?string $ip = null,
    ?string $userAgent = null,
    PDO $pdo = null
): int {
    $pdo = $pdo ?? db();
    return testimonials_upsert([
        'id' => 0,
        'quote' => $quote,
        'author' => $author,
        'location' => $location,
        'case_label' => '',
        'case_value' => '',
        'sort_order' => 0,
        'is_published' => 0,
        'submitter_email' => $submitterEmail,
        'ip' => $ip,
        'user_agent' => $userAgent,
    ], $pdo);
}

