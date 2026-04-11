<?php
declare(strict_types=1);

/**
 * Offres commerciales et annonces de recrutement (table `announcements`).
 */
function announcements_all(PDO $pdo = null, bool $onlyPublished = false, ?string $category = null): array
{
    $pdo = $pdo ?? db();
    $sql = 'SELECT * FROM announcements WHERE 1=1';
    $params = [];
    if ($onlyPublished) {
        $sql .= ' AND is_published = 1';
    }
    if ($category !== null && in_array($category, ['offre', 'recrutement'], true)) {
        $sql .= ' AND category = :c';
        $params[':c'] = $category;
    }
    $sql .= ' ORDER BY sort_order ASC, id DESC';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();
    if (!is_array($rows)) {
        return [];
    }
    return array_map(static function (array $r): array {
        return [
            'id' => (int)$r['id'],
            'category' => (string)$r['category'],
            'title' => (string)$r['title'],
            'summary' => (string)($r['summary'] ?? ''),
            'content' => (string)($r['content'] ?? ''),
            'sort_order' => (int)($r['sort_order'] ?? 0),
            'is_published' => !empty($r['is_published']),
            'created_at' => (string)($r['created_at'] ?? ''),
            'updated_at' => (string)($r['updated_at'] ?? ''),
        ];
    }, $rows);
}

/**
 * Annonce recrutement publique (pour candidatures).
 */
function announcements_find_public_recruitment(int $id, PDO $pdo = null): ?array
{
    if ($id <= 0) {
        return null;
    }
    $pdo = $pdo ?? db();
    $stmt = $pdo->prepare(
        'SELECT * FROM announcements WHERE id = :id AND category = \'recrutement\' AND is_published = 1 LIMIT 1'
    );
    $stmt->execute([':id' => $id]);
    $r = $stmt->fetch();
    if (!is_array($r)) {
        return null;
    }
    return [
        'id' => (int)$r['id'],
        'category' => (string)$r['category'],
        'title' => (string)$r['title'],
        'summary' => (string)($r['summary'] ?? ''),
        'content' => (string)($r['content'] ?? ''),
        'sort_order' => (int)($r['sort_order'] ?? 0),
        'is_published' => !empty($r['is_published']),
    ];
}

function announcements_find(int $id, PDO $pdo = null): ?array
{
    if ($id <= 0) {
        return null;
    }
    $pdo = $pdo ?? db();
    $stmt = $pdo->prepare('SELECT * FROM announcements WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $id]);
    $r = $stmt->fetch();
    if (!is_array($r)) {
        return null;
    }
    return [
        'id' => (int)$r['id'],
        'category' => (string)$r['category'],
        'title' => (string)$r['title'],
        'summary' => (string)($r['summary'] ?? ''),
        'content' => (string)($r['content'] ?? ''),
        'sort_order' => (int)($r['sort_order'] ?? 0),
        'is_published' => !empty($r['is_published']),
    ];
}

function announcements_upsert(array $input, PDO $pdo = null): int
{
    $pdo = $pdo ?? db();
    $id = (int)($input['id'] ?? 0);
    $category = (string)($input['category'] ?? 'offre');
    if (!in_array($category, ['offre', 'recrutement'], true)) {
        $category = 'offre';
    }
    $title = trim((string)($input['title'] ?? ''));
    $summary = trim((string)($input['summary'] ?? ''));
    $content = trim((string)($input['content'] ?? ''));
    $sortOrder = (int)($input['sort_order'] ?? 0);
    $isPublished = !empty($input['is_published']);

    if ($id > 0) {
        $stmt = $pdo->prepare(
            'UPDATE announcements SET category=:c, title=:t, summary=:s, content=:body, sort_order=:so, is_published=:pub WHERE id=:id'
        );
        $stmt->execute([
            ':c' => $category,
            ':t' => $title,
            ':s' => $summary !== '' ? $summary : null,
            ':body' => $content !== '' ? $content : null,
            ':so' => $sortOrder,
            ':pub' => $isPublished ? 1 : 0,
            ':id' => $id,
        ]);
        return $id;
    }

    $stmt = $pdo->prepare(
        'INSERT INTO announcements (category, title, summary, content, sort_order, is_published) VALUES (:c, :t, :s, :body, :so, :pub)'
    );
    $stmt->execute([
        ':c' => $category,
        ':t' => $title,
        ':s' => $summary !== '' ? $summary : null,
        ':body' => $content !== '' ? $content : null,
        ':so' => $sortOrder,
        ':pub' => $isPublished ? 1 : 0,
    ]);
    return (int)$pdo->lastInsertId();
}

function announcements_delete(int $id, PDO $pdo = null): void
{
    if ($id <= 0) {
        return;
    }
    $pdo = $pdo ?? db();
    $stmt = $pdo->prepare('DELETE FROM announcements WHERE id = :id');
    $stmt->execute([':id' => $id]);
}
