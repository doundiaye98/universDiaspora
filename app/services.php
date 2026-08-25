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

/**
 * Fichiers d’icône par slug quand le nom ne suit pas le motif univers-diasporas-icone-{slug}.png
 * (ex. « Développement web » → même visuel que la fiche Informatiques).
 *
 * @return array<string, string> slug (minuscules) => nom de fichier dans public/assets/img/
 */
function service_icon_slug_aliases(): array
{
    $informatiques = 'icon-informatiques.jpg';
    return [
        'developpement-web' => $informatiques,
        'developpement' => $informatiques,
        'web' => $informatiques,
        'site-web' => $informatiques,
        'sites-internet' => $informatiques,
        'informatique' => $informatiques,
        'conseils-accompagnements' => 'icon-conseils-accompagnements.jpg',
        'immobilier-btp' => 'icon-immobilier-btp.jpg',
        'voyages' => 'icon-voyages.jpg',
        'creation-gestion-d-entreprises' => 'icon-creation-gestion-d-entreprises.jpg',
        'transports' => 'icon-transports.jpg',
        'assistances-administratives' => 'icon-assistances-administratives.jpg',
        'formations-emplois' => 'icon-formations-emplois.jpg',
        'services-a-la-personne' => 'icon-services-a-la-personne.jpg',
        'assurances-credits' => 'icon-assurances-credits.jpg',
        'informatiques' => 'icon-informatiques.jpg',
        'supermarket' => 'icon-supermarket.jpg',
        'pompes-funebres' => 'icon-pompes-funebres.jpg',
        'bien-d-autres-services' => 'icon-bien-d-autres-services.jpg',
    ];
}

/** Icône SVG de secours (toujours affichable, même sans fichier PNG sur le serveur). */
function service_icon_placeholder_url(): string
{
    static $url = null;
    if ($url !== null) {
        return $url;
    }
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 64 64"><rect fill="#e8ecf4" width="64" height="64" rx="14"/><path fill="rgba(11,42,111,.35)" d="M32 20c-4.5 0-8 3.5-8 8 0 5 3 9 8 14 5-5 8-9 8-14 0-4.5-3.5-8-8-8zm0 24c-8 0-15 4-15 8v4h30v-4c0-4-7-8-15-8z"/></svg>';
    return $url = 'data:image/svg+xml;charset=utf-8,' . rawurlencode($svg);
}

/**
 * Attributs HTML pour une balise img d’icône service (repli automatique si 404).
 */
function service_icon_img_attrs(string $iconFilename, string $baseUrl, ?string $serviceSlug = null, int $width = 24, int $height = 24, string $class = ''): string
{
    $src = service_icon_url($iconFilename, $baseUrl, $serviceSlug);
    $fallback = service_icon_placeholder_url();
    $attrs = 'src="' . h($src) . '" alt="" width="' . $width . '" height="' . $height . '" loading="lazy"';
    if ($class !== '') {
        $attrs .= ' class="' . h($class) . '"';
    }
    if ($src !== $fallback) {
        $attrs .= ' onerror="this.onerror=null;this.src=\'' . h($fallback) . '\'"';
    }
    return $attrs;
}

/**
 * URL publique de l’icône, ou image SVG inline si aucun fichier ne convient.
 *
 * @param string|null $serviceSlug Slug du service (ex. developpement-web) : permet un repli si icon est vide ou fichier manquant.
 */
function service_icon_url(string $iconFilename, string $baseUrl, ?string $serviceSlug = null): string
{
    $raw = trim($iconFilename);
    if ($raw !== '' && preg_match('~^https?://~i', $raw)) {
        return $raw;
    }
    $raw = str_replace('\\', '/', $raw);
    if (preg_match('~(?:^|/)(?:public/)?assets/img/(.+)$~i', $raw, $m)) {
        $raw = $m[1];
    }
    $base = basename($raw);
    $root = dirname(__DIR__);
    $dirFs = $root . '/public/assets/img/';

    $tryFile = static function (string $filename) use ($dirFs, $baseUrl): ?string {
        $name = basename($filename);
        if ($name === '' || $name === '.' || $name === '..') {
            return null;
        }
        $fs = $dirFs . $name;
        if (is_file($fs)) {
            $url = ud_public_asset_url('img/' . $name, $baseUrl);
            $mtime = @filemtime($fs);
            if ($mtime !== false) {
                $url .= (strpos($url, '?') !== false ? '&' : '?') . 'v=' . $mtime;
            }
            return $url;
        }
        return null;
    };

    // Préférer les nouvelles icônes photo par slug (icon-{slug}.jpg|.svg) si présentes
    $slugKey = strtolower(trim((string)($serviceSlug ?? '')));
    if ($slugKey !== '') {
        $safe = preg_replace('~[^a-z0-9-]~', '', $slugKey) ?? '';
        if ($safe !== '') {
            foreach (['jpg', 'svg', 'png', 'webp'] as $ext) {
                $u = $tryFile('icon-' . $safe . '.' . $ext);
                if ($u !== null) {
                    return $u;
                }
            }
        }
        $aliases = service_icon_slug_aliases();
        if (isset($aliases[$slugKey])) {
            $u = $tryFile($aliases[$slugKey]);
            if ($u !== null) {
                return $u;
            }
        }
    }

    if ($base !== '') {
        $u = $tryFile($base);
        if ($u !== null) {
            return $u;
        }
    }

    if ($slugKey !== '') {
        $safe = preg_replace('~[^a-z0-9-]~', '', $slugKey) ?? '';
        if ($safe !== '') {
            $u = $tryFile('univers-diasporas-icone-' . $safe . '.png');
            if ($u !== null) {
                return $u;
            }
            $svcMatches = glob($dirFs . 'svc-' . $safe . '-*.jpg') ?: [];
            if ($svcMatches !== []) {
                $u = $tryFile(basename((string)$svcMatches[0]));
                if ($u !== null) {
                    return $u;
                }
            }
        }
    }

    return service_icon_placeholder_url();
}

function service_icon_public_img_dir(): string
{
    return dirname(__DIR__) . '/public/assets/img';
}

function service_icon_max_upload_bytes(): int
{
    return 2 * 1024 * 1024;
}

/**
 * Message utilisateur selon le code d’erreur PHP d’upload (UPLOAD_ERR_*).
 */
function service_icon_upload_error_message(int $code): string
{
    $prefix = 'Photo du service : ';
    switch ($code) {
        case UPLOAD_ERR_INI_SIZE:
            return $prefix . 'le fichier dépasse la limite PHP upload_max_filesize. '
                . 'Réduisez l’image (ou dans WAMP : PHP → php.ini, augmentez upload_max_filesize et post_max_size, puis redémarrez Apache).';
        case UPLOAD_ERR_FORM_SIZE:
            return $prefix . 'le fichier dépasse la limite du formulaire.';
        case UPLOAD_ERR_PARTIAL:
            return $prefix . 'transfert incomplet. Réessayez.';
        case UPLOAD_ERR_NO_TMP_DIR:
            return $prefix . 'dossier temporaire manquant sur le serveur (voir upload_tmp_dir dans php.ini).';
        case UPLOAD_ERR_CANT_WRITE:
            return $prefix . 'impossible d’écrire sur le disque (droits ou espace disque).';
        case UPLOAD_ERR_EXTENSION:
            return $prefix . 'une extension PHP bloque l’envoi du fichier.';
        default:
            return $prefix . 'erreur d’envoi (code ' . $code . ').';
    }
}

/**
 * @return string|null Erreur utilisateur, ou null si OK (y compris « pas de fichier »).
 */
function service_icon_validate_upload(?array $file): ?string
{
    if ($file === null || !isset($file['error'])) {
        return null;
    }
    if ((int)$file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ((int)$file['error'] !== UPLOAD_ERR_OK) {
        return service_icon_upload_error_message((int)$file['error']);
    }
    $tmp = (string)($file['tmp_name'] ?? '');
    if ($tmp === '' || !is_uploaded_file($tmp)) {
        return 'Photo du service : envoi invalide.';
    }
    $size = (int)($file['size'] ?? 0);
    if ($size <= 0) {
        return 'Photo du service : fichier vide ou incomplet.';
    }
    if ($size > service_icon_max_upload_bytes()) {
        return 'Photo du service : fichier trop volumineux (max. 2 Mo).';
    }
    $okMime = false;
    if (function_exists('finfo_open')) {
        $f = finfo_open(FILEINFO_MIME_TYPE);
        if ($f) {
            $mime = finfo_file($f, $tmp);
            finfo_close($f);
            $okMime = in_array($mime, ['image/jpeg', 'image/png', 'image/webp', 'image/gif'], true);
        }
    } else {
        $okMime = true;
    }
    if (!$okMime) {
        return 'Photo du service : JPG, PNG, WebP ou GIF uniquement.';
    }
    $head = @file_get_contents($tmp, false, null, 0, 12);
    if ($head === false || strlen($head) < 4) {
        return 'Photo du service : fichier image invalide.';
    }
    return null;
}

/**
 * Enregistre l’icône dans public/assets/img/ et retourne le nom de fichier (ex. svc-mon-slug-a1b2c3d4e.png).
 */
function service_icon_store_upload(array $file, string $slug): string
{
    $slugPart = preg_replace('~[^a-z0-9-]+~', '-', strtolower(trim($slug)));
    $slugPart = trim($slugPart, '-');
    if ($slugPart === '') {
        $slugPart = 'service';
    }
    if (strlen($slugPart) > 48) {
        $slugPart = substr($slugPart, 0, 48);
    }
    $dir = service_icon_public_img_dir();
    if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
        throw new RuntimeException('Dossier img inaccessible.');
    }
    $tmp = (string)$file['tmp_name'];
    $mime = 'image/png';
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
    $ext = 'png';
    if ($mime === 'image/jpeg') {
        $ext = 'jpg';
    } elseif ($mime === 'image/webp') {
        $ext = 'webp';
    } elseif ($mime === 'image/gif') {
        $ext = 'gif';
    }
    $name = 'svc-' . $slugPart . '-' . bin2hex(random_bytes(5)) . '.' . $ext;
    $dest = $dir . DIRECTORY_SEPARATOR . $name;
    if (!@move_uploaded_file($tmp, $dest)) {
        throw new RuntimeException('Enregistrement impossible.');
    }
    if (!is_file($dest)) {
        throw new RuntimeException('Enregistrement impossible.');
    }
    return $name;
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
        $mapped = array_map(static function (array $r) use ($bulletsByService): array {
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
                'faq1_q' => (string)($r['faq1_q'] ?? ''),
                'faq1_a' => (string)($r['faq1_a'] ?? ''),
                'faq2_q' => (string)($r['faq2_q'] ?? ''),
                'faq2_a' => (string)($r['faq2_a'] ?? ''),
                'faq3_q' => (string)($r['faq3_q'] ?? ''),
                'faq3_a' => (string)($r['faq3_a'] ?? ''),
                'icon' => (string)($r['icon'] ?? ''),
                'external_url' => $r['external_url'] ? (string)$r['external_url'] : null,
                'coming_soon' => !empty($r['coming_soon']),
                'sort_order' => (int)($r['sort_order'] ?? 0),
                'bullets' => $bulletsByService[$id] ?? [],
            ];
        }, $rows);
        return array_map('ud_service_apply_immobilier_url', $mapped);
    } catch (Throwable $e) {
        $fallback = require __DIR__ . '/../data/services.php';
        $list = is_array($fallback) ? $fallback : [];
        return array_map('ud_service_apply_immobilier_url', $list);
    }
}

function ud_service_apply_immobilier_url(array $service): array
{
    if (($service['slug'] ?? '') !== 'immobilier-btp') {
        return $service;
    }
    if (!function_exists('ud_immobilier_btp_url')) {
        return $service;
    }
    $url = ud_immobilier_btp_url();
    if ($url !== '') {
        $service['external_url'] = $url;
    }
    return $service;
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
        $found = [
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
            'faq1_q' => (string)($r['faq1_q'] ?? ''),
            'faq1_a' => (string)($r['faq1_a'] ?? ''),
            'faq2_q' => (string)($r['faq2_q'] ?? ''),
            'faq2_a' => (string)($r['faq2_a'] ?? ''),
            'faq3_q' => (string)($r['faq3_q'] ?? ''),
            'faq3_a' => (string)($r['faq3_a'] ?? ''),
            'icon' => (string)($r['icon'] ?? ''),
            'external_url' => $r['external_url'] ? (string)$r['external_url'] : null,
            'coming_soon' => !empty($r['coming_soon']),
            'sort_order' => (int)($r['sort_order'] ?? 0),
            'bullets' => $bullets,
        ];
        return ud_service_apply_immobilier_url($found);
    } catch (Throwable $e) {
        $fallback = require __DIR__ . '/../data/services.php';
        if (!is_array($fallback)) return null;
        foreach ($fallback as $s) {
            if (is_array($s) && ($s['slug'] ?? '') === $slug) {
                return ud_service_apply_immobilier_url($s);
            }
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
    $faq1Q = trim((string)($input['faq1_q'] ?? ''));
    $faq1A = trim((string)($input['faq1_a'] ?? ''));
    $faq2Q = trim((string)($input['faq2_q'] ?? ''));
    $faq2A = trim((string)($input['faq2_a'] ?? ''));
    $faq3Q = trim((string)($input['faq3_q'] ?? ''));
    $faq3A = trim((string)($input['faq3_a'] ?? ''));
    $icon = trim((string)($input['icon'] ?? ''));
    if ($icon !== '' && !preg_match('~^https?://~i', $icon)) {
        $icon = str_replace('\\', '/', $icon);
        if (preg_match('~(?:^|/)(?:public/)?assets/img/(.+)$~i', $icon, $im)) {
            $icon = $im[1];
        }
        $icon = basename($icon);
    }
    $external = trim((string)($input['external_url'] ?? ''));
    $external = $external === '' ? null : $external;
    $comingSoon = !empty($input['coming_soon']) ? 1 : 0;
    $sort = (int)($input['sort_order'] ?? 0);

    if ($id > 0) {
        $stmt = $pdo->prepare(
            'UPDATE services SET slug=:slug,title=:title,description=:description,details=:details,details_is_html=:dih,' .
            'step1_title=:s1t,step1_text=:s1x,step2_title=:s2t,step2_text=:s2x,step3_title=:s3t,step3_text=:s3x,' .
            'faq1_q=:f1q,faq1_a=:f1a,faq2_q=:f2q,faq2_a=:f2a,faq3_q=:f3q,faq3_a=:f3a,' .
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
            ':f1q' => ($faq1Q === '' ? null : $faq1Q),
            ':f1a' => ($faq1A === '' ? null : $faq1A),
            ':f2q' => ($faq2Q === '' ? null : $faq2Q),
            ':f2a' => ($faq2A === '' ? null : $faq2A),
            ':f3q' => ($faq3Q === '' ? null : $faq3Q),
            ':f3a' => ($faq3A === '' ? null : $faq3A),
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
        'INSERT INTO services (slug,title,description,details,details_is_html,step1_title,step1_text,step2_title,step2_text,step3_title,step3_text,faq1_q,faq1_a,faq2_q,faq2_a,faq3_q,faq3_a,icon,external_url,coming_soon,sort_order) ' .
        'VALUES (:slug,:title,:description,:details,:dih,:s1t,:s1x,:s2t,:s2x,:s3t,:s3x,:f1q,:f1a,:f2q,:f2a,:f3q,:f3a,:icon,:external_url,:coming_soon,:sort_order)'
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
        ':f1q' => ($faq1Q === '' ? null : $faq1Q),
        ':f1a' => ($faq1A === '' ? null : $faq1A),
        ':f2q' => ($faq2Q === '' ? null : $faq2Q),
        ':f2a' => ($faq2A === '' ? null : $faq2A),
        ':f3q' => ($faq3Q === '' ? null : $faq3Q),
        ':f3a' => ($faq3A === '' ? null : $faq3A),
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

/**
 * Libellé d’un volet pour un service (data/service_volets.php).
 */
function service_volet_label(string $serviceSlug, string $voletId): string
{
    $serviceSlug = trim($serviceSlug);
    $voletId = trim($voletId);
    if ($serviceSlug === '' || $voletId === '' || preg_match('/^[a-z0-9-]+$/', $serviceSlug) !== 1 || preg_match('/^[a-z0-9-]+$/', $voletId) !== 1) {
        return '';
    }
    $voletsAll = require __DIR__ . '/../data/service_volets.php';
    if (empty($voletsAll[$serviceSlug]) || !is_array($voletsAll[$serviceSlug])) {
        return '';
    }
    foreach ($voletsAll[$serviceSlug] as $v) {
        if (($v['id'] ?? '') === $voletId) {
            return trim((string)($v['label'] ?? ''));
        }
    }
    return '';
}

/**
 * Contexte service / volet pour une demande de rendez-vous.
 *
 * @return array{service_slug:string,volet_id:string,service_title:string,volet_label:string}|null
 */
function appointment_service_context(string $serviceSlug, string $voletId = ''): ?array
{
    $serviceSlug = trim($serviceSlug);
    $voletId = trim($voletId);
    if ($serviceSlug === '' || preg_match('/^[a-z0-9-]{1,120}$/', $serviceSlug) !== 1) {
        return null;
    }
    if ($voletId !== '' && preg_match('/^[a-z0-9-]{1,120}$/', $voletId) !== 1) {
        $voletId = '';
    }

    $service = services_find_by_slug($serviceSlug);
    if ($service === null) {
        foreach (services_all() as $s) {
            if (($s['slug'] ?? '') === $serviceSlug) {
                $service = $s;
                break;
            }
        }
    }
    if ($service === null) {
        return null;
    }

    $voletLabel = $voletId !== '' ? service_volet_label($serviceSlug, $voletId) : '';
    if ($voletId !== '' && $voletLabel === '') {
        $voletId = '';
    }

    return [
        'service_slug' => $serviceSlug,
        'volet_id' => $voletId,
        'service_title' => (string)($service['title'] ?? $serviceSlug),
        'volet_label' => $voletLabel,
    ];
}

function appointment_format_date_fr(string $dateYmd): string
{
    $dateYmd = trim($dateYmd);
    if ($dateYmd === '') {
        return '';
    }
    $dt = DateTime::createFromFormat('Y-m-d', $dateYmd);
    if (!$dt) {
        return $dateYmd;
    }
    static $months = [
        1 => 'janvier', 2 => 'février', 3 => 'mars', 4 => 'avril',
        5 => 'mai', 6 => 'juin', 7 => 'juillet', 8 => 'août',
        9 => 'septembre', 10 => 'octobre', 11 => 'novembre', 12 => 'décembre',
    ];
    $m = (int)$dt->format('n');
    return (int)$dt->format('j') . ' ' . ($months[$m] ?? $dt->format('m')) . ' ' . $dt->format('Y');
}

/**
 * Message type pour une demande de rendez-vous liée à un service.
 */
function appointment_build_message(
    string $serviceTitle,
    string $voletLabel = '',
    string $office = '',
    string $dateYmd = '',
    string $timeHi = ''
): string {
    $serviceTitle = trim($serviceTitle);
    if ($serviceTitle === '') {
        return '';
    }

    $lines = ['Bonjour,', '', 'Je souhaite prendre rendez-vous pour être accompagné(e) sur :'];
    $lines[] = '- Service : ' . $serviceTitle;
    if (trim($voletLabel) !== '') {
        $lines[] = '- Volet : ' . trim($voletLabel);
    }

    $office = trim($office);
    $dateYmd = trim($dateYmd);
    $timeHi = trim($timeHi);
    if ($timeHi !== '' && strlen($timeHi) >= 5) {
        $timeHi = substr($timeHi, 0, 5);
    }

    if ($office !== '' || $dateYmd !== '' || $timeHi !== '') {
        $lines[] = '';
        $lines[] = 'Créneau souhaité :';
        if ($office !== '') {
            $lines[] = '- Bureau : ' . $office;
        }
        if ($dateYmd !== '') {
            $lines[] = '- Date : ' . appointment_format_date_fr($dateYmd);
        }
        if ($timeHi !== '') {
            $lines[] = '- Heure : ' . $timeHi;
        }
    }

    $lines[] = '';
    $lines[] = 'Merci de me recontacter pour confirmer ce créneau et m’indiquer les documents à préparer.';

    return implode("\n", $lines);
}

