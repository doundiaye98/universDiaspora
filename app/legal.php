<?php
declare(strict_types=1);

require_once __DIR__ . '/http.php';

/**
 * Bloc « legal » dans config (config.example.php + surcharge config.local.php).
 *
 * @param array<string,mixed> $config
 * @return array<string,mixed>
 */
function legal_config(array $config): array
{
    $defaults = [
        'documents_last_updated' => '',
        'publisher' => [
            'legal_name' => '',
            'legal_form' => '',
            'address_line1' => '',
            'address_line2' => '',
            'postal_code' => '',
            'city' => '',
            'country' => 'France',
            'siret' => '',
            'rcs_number' => '',
            'rcs_city' => '',
            'share_capital' => '',
            'vat_number' => '',
            'director_title' => 'Directeur ou directrice de la publication',
            'director_name' => '',
            'phone' => '',
            'email' => '',
            'email_dpo' => '',
        ],
        'hosting' => [
            'name' => '',
            'address' => '',
            'website' => '',
            'phone' => '',
        ],
        'privacy' => [
            'retention_summary' => '',
            'cookies_summary' => '',
            'subprocessors_summary' => '',
            'uses_audience_measurement' => false,
        ],
    ];
    $legal = $config['legal'] ?? [];
    if (!is_array($legal)) {
        $legal = [];
    }
    /** @var array<string,mixed> */
    return array_replace_recursive($defaults, $legal);
}

function legal_placeholder(string $hint = 'À renseigner dans config.local.php (section « legal »).'): string
{
    return '<span class="ud-legal-placeholder">' . h($hint) . '</span>';
}

function legal_line(?string $value, string $hint = 'À renseigner dans config.local.php (section « legal »).'): string
{
    $v = trim((string)($value ?? ''));
    return $v !== '' ? h($v) : legal_placeholder($hint);
}

/**
 * @param array<string,mixed> $pub
 */
function legal_publisher_block_html(array $pub): string
{
    $lines = [
        ['Raison sociale', (string)($pub['legal_name'] ?? '')],
        ['Forme juridique', (string)($pub['legal_form'] ?? '')],
        ['Adresse', legal_format_address($pub)],
        ['SIRET', (string)($pub['siret'] ?? '')],
        ['RCS', legal_format_rcs($pub)],
        ['Capital social', (string)($pub['share_capital'] ?? '')],
        ['TVA intracommunautaire', (string)($pub['vat_number'] ?? '')],
        [(string)($pub['director_title'] ?? 'Directeur ou directrice de la publication'), (string)($pub['director_name'] ?? '')],
        ['Téléphone', (string)($pub['phone'] ?? '')],
        ['E-mail', (string)($pub['email'] ?? '')],
        ['Contact données personnelles (DPO / référent)', (string)($pub['email_dpo'] ?? '')],
    ];
    $out = '';
    foreach ($lines as [$label, $val]) {
        $out .= '<p class="ud-legal-kv mb-2"><strong>' . h($label) . ' :</strong> ';
        $out .= (trim($val) !== '' ? h($val) : legal_placeholder()) . '</p>';
    }
    return $out;
}

/**
 * @param array<string,mixed> $pub
 */
function legal_format_address(array $pub): string
{
    $parts = array_filter([
        trim((string)($pub['address_line1'] ?? '')),
        trim((string)($pub['address_line2'] ?? '')),
        trim((string)($pub['postal_code'] ?? '') . ' ' . (string)($pub['city'] ?? '')),
        trim((string)($pub['country'] ?? '')),
    ], static fn(string $s): bool => $s !== '');
    return implode(', ', $parts);
}

/**
 * @param array<string,mixed> $pub
 */
function legal_format_rcs(array $pub): string
{
    $n = trim((string)($pub['rcs_number'] ?? ''));
    $c = trim((string)($pub['rcs_city'] ?? ''));
    if ($n === '' && $c === '') {
        return '';
    }
    if ($c !== '' && $n !== '') {
        return 'RCS ' . $c . ' ' . $n;
    }
    return $n !== '' ? $n : $c;
}

/**
 * @param array<string,mixed> $host
 */
function legal_hosting_block_html(array $host): string
{
    $rows = [
        ['Hébergeur', (string)($host['name'] ?? '')],
        ['Adresse', (string)($host['address'] ?? '')],
        ['Site web', (string)($host['website'] ?? '')],
        ['Téléphone', (string)($host['phone'] ?? '')],
    ];
    $out = '';
    foreach ($rows as [$label, $val]) {
        $out .= '<p class="ud-legal-kv mb-2"><strong>' . h($label) . ' :</strong> ';
        if (trim($val) === '') {
            $out .= legal_placeholder();
        } elseif ($label === 'Site web' && filter_var($val, FILTER_VALIDATE_URL)) {
            $out .= '<a href="' . h($val) . '" target="_blank" rel="noopener noreferrer">' . h($val) . '</a>';
        } else {
            $out .= h($val);
        }
        $out .= '</p>';
    }
    return $out;
}

/**
 * @param array<string,mixed> $legal
 */
function legal_last_updated_display(array $legal): string
{
    $raw = trim((string)($legal['documents_last_updated'] ?? ''));
    if ($raw === '') {
        return legal_placeholder('Indiquez legal.documents_last_updated (format AAAA-MM-JJ) dans config.local.php.');
    }
    $ts = strtotime($raw);
    if ($ts === false) {
        return h($raw);
    }
    return h(date('d/m/Y', $ts));
}
