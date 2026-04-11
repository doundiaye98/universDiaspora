<?php
declare(strict_types=1);

/**
 * Service repository (DB-first). Falls back to data/services.php if DB unavailable.
 */

function services_default_steps_vous(): array
{
    return [
        ['title' => 'Analyse', 'text' => 'Nous comprenons votre besoin et vous conseillons la meilleure option.'],
        ['title' => 'Proposition', 'text' => 'Nous vous présentons une solution claire, avec un plan d’action.'],
        ['title' => 'Accompagnement', 'text' => 'Nous avançons avec vous jusqu’à la réalisation.'],
    ];
}

/**
 * Étapes affichées sur la page service (titres / textes éditables ; valeurs vides = défaut « vous »).
 *
 * @return list<array{title:string,text:string}>
 */
function service_steps_for_display(array $service): array
{
    $defaults = services_default_steps_vous();
    $out = [];
    for ($i = 0; $i < 3; $i++) {
        $n = $i + 1;
        $t = trim((string)($service['step' . $n . '_title'] ?? ''));
        $x = trim((string)($service['step' . $n . '_text'] ?? ''));
        if ($t === '') {
            $t = $defaults[$i]['title'];
        }
        if ($x === '') {
            $x = $defaults[$i]['text'];
        }
        $out[] = ['title' => $t, 'text' => $x];
    }
    return $out;
}

function services_sanitize_details_html(string $html): string
{
    $allowed = '<p><br><strong><b><em><i><ul><ol><li><h2><h3><h4>';
    return strip_tags($html, $allowed);
}

/** URL publique de l’icône, ou image SVG inline si le fichier est absent. */
function service_icon_url(string $iconFilename, string $baseUrl): string
{
    $base = basename(trim($iconFilename));
    $root = dirname(__DIR__);
    if ($base !== '' && is_file($root . '/public/assets/img/' . $base)) {
        return rtrim($baseUrl, '/') . '/public/assets/img/' . rawurlencode($base);
    }
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 64 64"><rect fill="#e8ecf4" width="64" height="64" rx="14"/><path fill="rgba(11,42,111,.35)" d="M32 20c-4.5 0-8 3.5-8 8 0 5 3 9 8 14 5-5 8-9 8-14 0-4.5-3.5-8-8-8zm0 24c-8 0-15 4-15 8v4h30v-4c0-4-7-8-15-8z"/></svg>';
    return 'data:image/svg+xml;charset=utf-8,' . rawurlencode($svg);
}

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
                'details_is_html' => !empty($r['details_is_html']),
                'step1_title' => (string)($r['step1_title'] ?? ''),
                'step1_text' => (string)($r['step1_text'] ?? ''),
                'step2_title' => (string)($r['step2_title'] ?? ''),
                'step2_text' => (string)($r['step2_text'] ?? ''),
                'step3_title' => (string)($r['step3_title'] ?? ''),
                'step3_text' => (string)($r['step3_text'] ?? ''),
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
            'details_is_html' => !empty($r['details_is_html']),
            'step1_title' => (string)($r['step1_title'] ?? ''),
            'step1_text' => (string)($r['step1_text'] ?? ''),
            'step2_title' => (string)($r['step2_title'] ?? ''),
            'step2_text' => (string)($r['step2_text'] ?? ''),
            'step3_title' => (string)($r['step3_title'] ?? ''),
            'step3_text' => (string)($r['step3_text'] ?? ''),
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
    $detailsIsHtml = !empty($input['details_is_html']) ? 1 : 0;
    if ($details !== '' && $detailsIsHtml) {
        $details = services_sanitize_details_html($details);
    }
    $step1Title = trim((string)($input['step1_title'] ?? ''));
    $step1Text = trim((string)($input['step1_text'] ?? ''));
    $step2Title = trim((string)($input['step2_title'] ?? ''));
    $step2Text = trim((string)($input['step2_text'] ?? ''));
    $step3Title = trim((string)($input['step3_title'] ?? ''));
    $step3Text = trim((string)($input['step3_text'] ?? ''));
    $icon = trim((string)($input['icon'] ?? ''));
    $external = trim((string)($input['external_url'] ?? ''));
    $external = $external === '' ? null : $external;
    $comingSoon = !empty($input['coming_soon']) ? 1 : 0;
    $sort = (int)($input['sort_order'] ?? 0);

    if ($id > 0) {
        $stmt = $pdo->prepare(
            'UPDATE services SET slug=:slug,title=:title,description=:description,details=:details,details_is_html=:dih,' .
            'step1_title=:s1t,step1_text=:s1x,step2_title=:s2t,step2_text=:s2x,step3_title=:s3t,step3_text=:s3x,' .
            'icon=:icon,external_url=:external_url,coming_soon=:coming_soon,sort_order=:sort_order WHERE id=:id'
        );
        $stmt->execute([
            ':slug' => $slug,
            ':title' => $title,
            ':description' => ($description === '' ? null : $description),
            ':details' => ($details === '' ? null : $details),
            ':dih' => $detailsIsHtml,
            ':s1t' => ($step1Title === '' ? null : $step1Title),
            ':s1x' => ($step1Text === '' ? null : $step1Text),
            ':s2t' => ($step2Title === '' ? null : $step2Title),
            ':s2x' => ($step2Text === '' ? null : $step2Text),
            ':s3t' => ($step3Title === '' ? null : $step3Title),
            ':s3x' => ($step3Text === '' ? null : $step3Text),
            ':icon' => ($icon === '' ? null : $icon),
            ':external_url' => $external,
            ':coming_soon' => $comingSoon,
            ':sort_order' => $sort,
            ':id' => $id,
        ]);
        services_replace_bullets($pdo, $id, (string)($input['bullets_text'] ?? ''));
        return $id;
    }

    $stmt = $pdo->prepare(
        'INSERT INTO services (slug,title,description,details,details_is_html,step1_title,step1_text,step2_title,step2_text,step3_title,step3_text,icon,external_url,coming_soon,sort_order) ' .
        'VALUES (:slug,:title,:description,:details,:dih,:s1t,:s1x,:s2t,:s2x,:s3t,:s3x,:icon,:external_url,:coming_soon,:sort_order)'
    );
    $stmt->execute([
        ':slug' => $slug,
        ':title' => $title,
        ':description' => ($description === '' ? null : $description),
        ':details' => ($details === '' ? null : $details),
        ':dih' => $detailsIsHtml,
        ':s1t' => ($step1Title === '' ? null : $step1Title),
        ':s1x' => ($step1Text === '' ? null : $step1Text),
        ':s2t' => ($step2Title === '' ? null : $step2Title),
        ':s2x' => ($step2Text === '' ? null : $step2Text),
        ':s3t' => ($step3Title === '' ? null : $step3Title),
        ':s3x' => ($step3Text === '' ? null : $step3Text),
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

