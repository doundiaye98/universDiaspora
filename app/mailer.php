<?php
declare(strict_types=1);

require_once __DIR__ . '/smtp_mail.php';

function ud_mail_try_send(string $to, string $subject, string $body): bool
{
    $config = require __DIR__ . '/../config/config.php';
    $mail = $config['mail'] ?? [];
    if (empty($mail['enable'])) {
        return false;
    }
    $to = trim($to);
    if ($to === '') {
        return false;
    }

    $transport = strtolower((string)($mail['transport'] ?? 'mail'));
    if ($transport === 'smtp') {
        return ud_mail_via_smtp($mail, $to, $subject, $body);
    }

    $from = (string)($mail['from'] ?? 'no-reply@localhost');
    $headers = 'From: ' . $from . "\r\n" .
        'Reply-To: ' . $from . "\r\n" .
        'Content-Type: text/plain; charset=UTF-8';

    return @mail($to, $subject, $body, $headers);
}

/**
 * E-mail au client lorsque l’admin confirme le rendez-vous (nécessite mail.enable dans la config).
 *
 * @param array{name?:string,email?:string,office?:string,appointment_at?:string} $row
 */
function ud_mail_appointment_confirmed_to_client(array $row, array $config): bool
{
    $email = trim((string)($row['email'] ?? ''));
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    $name = trim((string)($row['name'] ?? ''));
    $greeting = $name !== '' ? ('Bonjour ' . $name . ',') : 'Bonjour,';
    $office = trim((string)($row['office'] ?? ''));
    if ($office === '') {
        $office = '—';
    }
    $rawAt = (string)($row['appointment_at'] ?? '');
    $dtLabel = $rawAt;
    $ts = strtotime($rawAt);
    if ($ts !== false) {
        $dtLabel = date('d/m/Y', $ts) . ' à ' . date('H:i', $ts);
    }
    $appName = (string)($config['app']['name'] ?? 'Univers Diaspora');
    $baseUrl = rtrim((string)($config['app']['base_url'] ?? ''), '/');
    $subject = '[' . $appName . '] Votre rendez-vous est confirmé';
    $body =
        $greeting . "\n\n" .
        "Votre demande de rendez-vous a bien été confirmée par notre équipe.\n\n" .
        "Détails :\n" .
        "— Bureau / lieu : " . $office . "\n" .
        "— Date et heure : " . $dtLabel . "\n\n" .
        "Pour toute question ou pour modifier ce rendez-vous, vous pouvez répondre à cet e-mail ou nous contacter via notre site :\n" .
        $baseUrl . "\n\n" .
        "Cordialement,\n" .
        $appName . "\n";
    return ud_mail_try_send($email, $subject, $body);
}
