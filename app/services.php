<?php
declare(strict_types=1);

/**
 * Service repository (DB-first). Falls back to data/services.php if DB unavailable.
 */

function services_all(PDO $pdo = null): array
{
    try {
        $pdo = $pdo ?? db();
        $rows = $pdo->query('SELECT * FROM services ORDER BY sort_order ASC, id ASC')->fetchAll();
        if (!is_array($rows)) {
            return [];
        }
        $ids = array_map(static fn($r) => (int)$r['id'], $rows);
        $bulletsByService = services_bullets_map($pdo, $ids);
        return array_map(static function (array $r) use ($bulletsByService): array {
            $id = (int)$r['id'];
            return [
                'id' => $id,
                'slug' => (string)$r['slug'],
                'title' => (string)$r['title'],
                'description' => (string)($r['description'] ?? ''),
                'details' => (string)($r['details'] ?? ''),
                'icon' => (string)($r['icon'] ?? ''),
                'external_url' => $r['external_url'] ? (string)$r['external_url'] : null,
                'coming_soon' => !empty($r['coming_soon']),
                'sort_order' => (int)($r['sort_order'] ?? 0),
                'bullets' => $bulletsByService[$id] ?? [],
            ];
        }, $rows);
    } catch (Throwable $e) {
        $fallback = require __DIR__ . '/../data/services.php';
        return is_array($fallback) ? $fallback : [];
    }
}

function services_find_by_slug(string $slug, PDO $pdo = null): ?array
{
    $slug = trim($slug);
    if ($slug === '') return null;
    try {
        $pdo = $pdo ?? db();
        $stmt = $pdo->prepare('SELECT * FROM services WHERE slug = :slug LIMIT 1');
        $stmt->execute([':slug' => $slug]);
        $r = $stmt->fetch();
        if (!is_array($r)) return null;
        $id = (int)$r['id'];
        $bullets = services_bullets_map($pdo, [$id])[$id] ?? [];
        return [
            'id' => $id,
            'slug' => (string)$r['slug'],
            'title' => (string)$r['title'],
            'description' => (string)($r['description'] ?? ''),
            'details' => (string)($r['details'] ?? ''),
            'icon' => (string)($r['icon'] ?? ''),
            'external_url' => $r['external_url'] ? (string)$r['external_url'] : null,
            'coming_soon' => !empty($r['coming_soon']),
            'sort_order' => (int)($r['sort_order'] ?? 0),
            'bullets' => $bullets,
        ];
    } catch (Throwable $e) {
        $fallback = require __DIR__ . '/../data/services.php';
        if (!is_array($fallback)) return null;
        foreach ($fallback as $s) {
            if (is_array($s) && ($s['slug'] ?? '') === $slug) return $s;
        }
        return null;
    }
}

function services_upsert(array $input, PDO $pdo = null): int
{
    $pdo = $pdo ?? db();
    $id = (int)($input['id'] ?? 0);
    $slug = trim((string)($input['slug'] ?? ''));
    $title = trim((string)($input['title'] ?? ''));
    $description = trim((string)($input['description'] ?? ''));
    $details = trim((string)($input['details'] ?? ''));
    $icon = trim((string)($input['icon'] ?? ''));
    $external = trim((string)($input['external_url'] ?? ''));
    $external = $external === '' ? null : $external;
    $comingSoon = !empty($input['coming_soon']) ? 1 : 0;
    $sort = (int)($input['sort_order'] ?? 0);

    if ($id > 0) {
        $stmt = $pdo->prepare('UPDATE services SET slug=:slug,title=:title,description=:description,details=:details,icon=:icon,external_url=:external_url,coming_soon=:coming_soon,sort_order=:sort_order WHERE id=:id');
        $stmt->execute([
            ':slug' => $slug,
            ':title' => $title,
            ':description' => ($description === '' ? null : $description),
            ':details' => ($details === '' ? null : $details),
            ':icon' => ($icon === '' ? null : $icon),
            ':external_url' => $external,
            ':coming_soon' => $comingSoon,
            ':sort_order' => $sort,
            ':id' => $id,
        ]);
        services_replace_bullets($pdo, $id, (string)($input['bullets_text'] ?? ''));
        return $id;
    }

    $stmt = $pdo->prepare('INSERT INTO services (slug,title,description,details,icon,external_url,coming_soon,sort_order) VALUES (:slug,:title,:description,:details,:icon,:external_url,:coming_soon,:sort_order)');
    $stmt->execute([
        ':slug' => $slug,
        ':title' => $title,
        ':description' => ($description === '' ? null : $description),
        ':details' => ($details === '' ? null : $details),
        ':icon' => ($icon === '' ? null : $icon),
        ':external_url' => $external,
        ':coming_soon' => $comingSoon,
        ':sort_order' => $sort,
    ]);
    $newId = (int)$pdo->lastInsertId();
    services_replace_bullets($pdo, $newId, (string)($input['bullets_text'] ?? ''));
    return $newId;
}

function services_delete(int $id, PDO $pdo = null): void
{
    $pdo = $pdo ?? db();
    $stmt = $pdo->prepare('DELETE FROM services WHERE id = :id');
    $stmt->execute([':id' => $id]);
}

function services_bullets_map(PDO $pdo, array $serviceIds): array
{
    $serviceIds = array_values(array_filter(array_map('intval', $serviceIds), static fn($v) => $v > 0));
    if (empty($serviceIds)) return [];
    $in = implode(',', array_fill(0, count($serviceIds), '?'));
    $stmt = $pdo->prepare('SELECT service_id, bullet FROM service_bullets WHERE service_id IN (' . $in . ') ORDER BY sort_order ASC, id ASC');
    $stmt->execute($serviceIds);
    $rows = $stmt->fetchAll();
    $map = [];
    foreach ($rows as $r) {
        $sid = (int)$r['service_id'];
        $map[$sid] = $map[$sid] ?? [];
        $map[$sid][] = (string)$r['bullet'];
    }
    return $map;
}

function services_replace_bullets(PDO $pdo, int $serviceId, string $bulletsText): void
{
    $pdo->prepare('DELETE FROM service_bullets WHERE service_id = :sid')->execute([':sid' => $serviceId]);
    $lines = preg_split('~\R~u', $bulletsText) ?: [];
    $order = 0;
    foreach ($lines as $line) {
        $line = trim((string)$line);
        if ($line === '') continue;
        $order += 10;
        $pdo->prepare('INSERT INTO service_bullets (service_id, bullet, sort_order) VALUES (:sid, :b, :o)')
            ->execute([':sid' => $serviceId, ':b' => $line, ':o' => $order]);
    }
}

