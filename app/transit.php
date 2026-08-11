<?php
declare(strict_types=1);

/**
 * Couleurs officielles (approx. RATP / Île-de-France Mobilités) pour pastilles de lignes.
 *
 * @return array{bg:string, fg:string}
 */
function ud_transit_line_style(string $type, string $line): array
{
    $line = strtoupper(trim($line));
    $typeNorm = mb_strtolower(trim($type), 'UTF-8');

    $metro = [
        '1' => ['bg' => '#FFBE00', 'fg' => '#1a1a1a'],
        '2' => ['bg' => '#A0006D', 'fg' => '#fff'],
        '3' => ['bg' => '#9F9825', 'fg' => '#fff'],
        '3BIS' => ['bg' => '#98D4E2', 'fg' => '#1a1a1a'],
        '4' => ['bg' => '#C04191', 'fg' => '#fff'],
        '5' => ['bg' => '#F28E42', 'fg' => '#1a1a1a'],
        '6' => ['bg' => '#83C491', 'fg' => '#1a1a1a'],
        '7' => ['bg' => '#F3A4B6', 'fg' => '#1a1a1a'],
        '7BIS' => ['bg' => '#82C8E6', 'fg' => '#1a1a1a'],
        '8' => ['bg' => '#CEADD2', 'fg' => '#1a1a1a'],
        '9' => ['bg' => '#D5C900', 'fg' => '#1a1a1a'],
        '10' => ['bg' => '#E0B03B', 'fg' => '#1a1a1a'],
        '11' => ['bg' => '#8D5E2A', 'fg' => '#fff'],
        '12' => ['bg' => '#007852', 'fg' => '#fff'],
        '13' => ['bg' => '#6EC4E8', 'fg' => '#0d2a44'],
        '14' => ['bg' => '#62259D', 'fg' => '#fff'],
        '15' => ['bg' => '#B2006B', 'fg' => '#fff'],
        '16' => ['bg' => '#F3C300', 'fg' => '#1a1a1a'],
        '17' => ['bg' => '#E5A100', 'fg' => '#1a1a1a'],
        '18' => ['bg' => '#00A88F', 'fg' => '#fff'],
    ];

    $transilien = [
        'H' => ['bg' => '#8D5E2A', 'fg' => '#fff'],
        'J' => ['bg' => '#D2A063', 'fg' => '#1a1a1a'],
        'K' => ['bg' => '#9B933F', 'fg' => '#fff'],
        'L' => ['bg' => '#C5A3CD', 'fg' => '#1a1a1a'],
        'N' => ['bg' => '#00A88F', 'fg' => '#fff'],
        'P' => ['bg' => '#F3C300', 'fg' => '#1a1a1a'],
        'R' => ['bg' => '#F3A4B6', 'fg' => '#1a1a1a'],
        'U' => ['bg' => '#E01933', 'fg' => '#fff'],
    ];

    if (str_contains($typeNorm, 'métro') || str_contains($typeNorm, 'metro')) {
        return $metro[$line] ?? ['bg' => '#1e3a6e', 'fg' => '#fff'];
    }
    if (str_contains($typeNorm, 'transilien') || str_contains($typeNorm, 'train')) {
        return $transilien[$line] ?? ['bg' => '#8D5E2A', 'fg' => '#fff'];
    }
    if (str_contains($typeNorm, 'bus')) {
        return ['bg' => '#3D7C47', 'fg' => '#fff'];
    }
    if (str_contains($typeNorm, 'rer')) {
        $rer = [
            'A' => ['bg' => '#E3051C', 'fg' => '#fff'],
            'B' => ['bg' => '#4B92DB', 'fg' => '#fff'],
            'C' => ['bg' => '#F3C300', 'fg' => '#1a1a1a'],
            'D' => ['bg' => '#00A88F', 'fg' => '#fff'],
            'E' => ['bg' => '#DE81D3', 'fg' => '#1a1a1a'],
        ];
        return $rer[$line] ?? ['bg' => '#1e3a6e', 'fg' => '#fff'];
    }

    return ['bg' => '#1e3a6e', 'fg' => '#fff'];
}

/**
 * Découpe "4 · 12" / "140 · 235" en pastilles colorées.
 *
 * @return list<array{label:string, bg:string, fg:string}>
 */
function ud_transit_line_badges(string $type, string $linesCsv): array
{
    $parts = preg_split('/\s*[·•|,\/]\s*/u', $linesCsv) ?: [];
    $badges = [];
    foreach ($parts as $part) {
        $label = trim((string)$part);
        if ($label === '') {
            continue;
        }
        $style = ud_transit_line_style($type, $label);
        $badges[] = [
            'label' => $label,
            'bg' => $style['bg'],
            'fg' => $style['fg'],
        ];
    }
    return $badges;
}

/**
 * HTML des pastilles (échappé).
 */
function ud_transit_line_badges_html(string $type, string $linesCsv): string
{
    $html = '';
    foreach (ud_transit_line_badges($type, $linesCsv) as $b) {
        $html .= '<span class="ud-line-badge" style="background:'
            . h($b['bg']) . ';color:' . h($b['fg']) . '">'
            . h($b['label']) . '</span>';
    }
    return $html;
}
