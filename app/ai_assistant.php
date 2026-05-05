<?php
declare(strict_types=1);

function ud_ai_assistant_config(): array
{
    try {
        $config = require __DIR__ . '/../config/config.php';
        $ai = $config['ai_assistant'] ?? [];
        return [
            'enabled' => (bool)($ai['enabled'] ?? false),
            'provider' => (string)($ai['provider'] ?? 'openai'),
            'api_key' => (string)($ai['api_key'] ?? ''),
            'model' => (string)($ai['model'] ?? 'gpt-4o-mini'),
            'max_input_chars' => max(120, (int)($ai['max_input_chars'] ?? 700)),
            'max_output_tokens' => max(120, (int)($ai['max_output_tokens'] ?? 260)),
            'temperature' => (float)($ai['temperature'] ?? 0.4),
            'timeout_seconds' => max(5, (int)($ai['timeout_seconds'] ?? 18)),
        ];
    } catch (Throwable $e) {
        return [
            'enabled' => false,
            'provider' => 'openai',
            'api_key' => '',
            'model' => 'gpt-4o-mini',
            'max_input_chars' => 700,
            'max_output_tokens' => 260,
            'temperature' => 0.4,
            'timeout_seconds' => 18,
        ];
    }
}

function ud_ai_assistant_system_prompt(string $baseUrl): string
{
    $services = function_exists('services_all') ? services_all() : [];
    $serviceLines = [];
    foreach ($services as $s) {
        $title = trim((string)($s['title'] ?? ''));
        if ($title !== '') {
            $serviceLines[] = '- ' . $title;
        }
    }
    if (empty($serviceLines)) {
        $serviceLines[] = '- Services Univers Diaspora (voir la page Services)';
    }

    return implode("\n", [
        'Tu es l assistant virtuel de Univers Diaspora.',
        'Langue: francais naturel, poli, clair, concret.',
        'Mission: guider les visiteurs vers le bon service, prise de rendez-vous, ou formulaire de contact.',
        'Reponds en 2 a 6 phrases, courtes et utiles.',
        'Ne jamais inventer des tarifs, delais ou garanties. Si information inconnue, proposer un rendez-vous.',
        'Toujours orienter vers une action concrète (service, rdv, contact).',
        'Site web: ' . rtrim($baseUrl, '/') . '/',
        'Page rendez-vous: ' . rtrim($baseUrl, '/') . '/?page=rendez-vous',
        'Services connus:',
        implode("\n", $serviceLines),
    ]);
}

function ud_ai_assistant_fallback_answer(string $question, string $baseUrl): string
{
    $q = mb_strtolower(trim($question), 'UTF-8');
    $rdvUrl = rtrim($baseUrl, '/') . '/?page=rendez-vous';
    $servicesUrl = rtrim($baseUrl, '/') . '/#services';
    $contactUrl = rtrim($baseUrl, '/') . '/#contact';
    $homeUrl = rtrim($baseUrl, '/') . '/';

    $services = function_exists('services_all') ? services_all() : [];
    $immobilierUrl = $servicesUrl;
    foreach ($services as $s) {
        $slug = trim((string)($s['slug'] ?? ''));
        $title = mb_strtolower(trim((string)($s['title'] ?? '')), 'UTF-8');
        if (
            $slug !== ''
            && (
                str_contains($slug, 'immob')
                || str_contains($title, 'immob')
                || str_contains($title, 'maison')
                || str_contains($title, 'achat')
            )
        ) {
            $immobilierUrl = $homeUrl . '?page=' . rawurlencode($slug);
            break;
        }
    }

    if (str_contains($q, 'rendez') || str_contains($q, 'rdv') || str_contains($q, 'appointment')) {
        return 'Vous pouvez prendre rendez-vous en ligne en 1 minute ici : ' . $rdvUrl . '. Si vous voulez, je peux aussi vous orienter vers le bon service avant la prise de rendez-vous.';
    }
    if (str_contains($q, 'prix') || str_contains($q, 'tarif') || str_contains($q, 'cout')) {
        return 'Les tarifs dépendent de votre besoin et de votre dossier. Pour obtenir une réponse claire et adaptée, le plus simple est de réserver un rendez-vous ici : ' . $rdvUrl . '.';
    }
    if (
        str_contains($q, 'maison')
        || str_contains($q, 'appartement')
        || str_contains($q, 'acheter')
        || str_contains($q, 'achat')
        || str_contains($q, 'immobilier')
        || str_contains($q, 'logement')
    ) {
        return 'Très bon projet. Pour un achat immobilier, nous pouvons vous accompagner de façon structurée: définition du budget, vérification des pièces, sécurisation administrative et suivi jusqu’à la finalisation. Vous pouvez consulter le service dédié ici : ' . $immobilierUrl . '. Ensuite, réservez un rendez-vous pour une orientation personnalisée : ' . $rdvUrl . '.';
    }
    if (str_contains($q, 'service') || str_contains($q, 'aide') || str_contains($q, 'accompagnement')) {
        return 'Univers Diaspora propose plusieurs pôles d’accompagnement (administratif, entreprise, emploi, etc.). Vous pouvez découvrir tous nos services ici : ' . $servicesUrl . ', puis réserver un rendez-vous si besoin : ' . $rdvUrl . '.';
    }
    if (str_contains($q, 'contact') || str_contains($q, 'email') || str_contains($q, 'telephone')) {
        return 'Pour nous contacter rapidement, utilisez le formulaire : ' . $contactUrl . '. Si votre besoin est urgent, je vous recommande de réserver directement un rendez-vous : ' . $rdvUrl . '.';
    }

    return 'Merci pour votre message. Je peux vous orienter vers le service le plus adapté et vous accompagner jusqu’à la prise de rendez-vous : ' . $rdvUrl . '. Dites-moi simplement votre besoin principal en une phrase.';
}

function ud_ai_assistant_call_openai(string $question, string $baseUrl, array $cfg): ?string
{
    if (!function_exists('curl_init')) {
        return null;
    }
    $apiKey = trim((string)$cfg['api_key']);
    if ($apiKey === '') {
        return null;
    }

    $payload = [
        'model' => (string)$cfg['model'],
        'messages' => [
            ['role' => 'system', 'content' => ud_ai_assistant_system_prompt($baseUrl)],
            ['role' => 'user', 'content' => trim($question)],
        ],
        'temperature' => (float)$cfg['temperature'],
        'max_tokens' => (int)$cfg['max_output_tokens'],
    ];

    $ch = curl_init('https://api.openai.com/v1/chat/completions');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json',
        ],
        CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        CURLOPT_CONNECTTIMEOUT => (int)$cfg['timeout_seconds'],
        CURLOPT_TIMEOUT => (int)$cfg['timeout_seconds'],
    ]);
    $raw = curl_exec($ch);
    $status = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);

    if (!is_string($raw) || $status < 200 || $status >= 300) {
        return null;
    }
    $data = json_decode($raw, true);
    $content = trim((string)($data['choices'][0]['message']['content'] ?? ''));
    return $content !== '' ? $content : null;
}

function ud_ai_assistant_handle_http(string $baseUrl): void
{
    header('Content-Type: application/json; charset=utf-8');
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['ok' => false, 'error' => 'method_not_allowed']);
        exit;
    }

    $cfg = ud_ai_assistant_config();
    if (empty($cfg['enabled'])) {
        http_response_code(503);
        echo json_encode(['ok' => false, 'error' => 'assistant_disabled']);
        exit;
    }

    $raw = file_get_contents('php://input');
    $json = is_string($raw) ? json_decode($raw, true) : null;
    $question = trim((string)($json['message'] ?? ''));
    if ($question === '') {
        http_response_code(422);
        echo json_encode(['ok' => false, 'error' => 'empty_message']);
        exit;
    }

    if (function_exists('mb_substr')) {
        $question = mb_substr($question, 0, (int)$cfg['max_input_chars']);
    } else {
        $question = substr($question, 0, (int)$cfg['max_input_chars']);
    }

    $answer = null;
    if (($cfg['provider'] ?? 'openai') === 'openai') {
        $answer = ud_ai_assistant_call_openai($question, $baseUrl, $cfg);
    }
    if ($answer === null || trim($answer) === '') {
        $answer = ud_ai_assistant_fallback_answer($question, $baseUrl);
    }

    echo json_encode([
        'ok' => true,
        'answer' => trim($answer),
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

