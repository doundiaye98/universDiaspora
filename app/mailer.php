<?php
declare(strict_types=1);

require_once __DIR__ . '/smtp_mail.php';
require_once __DIR__ . '/http.php';

/**
 * Nom de fichier ASCII sûr pour pièces jointes e-mail.
 */
function ud_mail_safe_filename(string $name, string $fallback = 'document'): string
{
    $name = trim($name);
    if ($name === '') {
        return $fallback;
    }
    $name = preg_replace('~[^\w.\- ]+~u', '_', $name) ?? $fallback;
    $name = preg_replace('~\s+~', '_', $name) ?? $fallback;
    $name = trim($name, '._-');
    return $name !== '' ? $name : $fallback;
}

/**
 * Corps MIME multipart/mixed (texte + pièces jointes).
 *
 * @param list<array{path:string, filename:string, mime?:string}> $attachments
 * @return array{body:string, content_type:string}
 */
function ud_mail_build_multipart(string $textBody, array $attachments): array
{
    $boundary = 'ud_' . bin2hex(random_bytes(12));
    $lines = [];

    $lines[] = '--' . $boundary;
    $lines[] = 'Content-Type: text/plain; charset=UTF-8';
    $lines[] = 'Content-Transfer-Encoding: 8bit';
    $lines[] = '';
    $lines[] = $textBody;
    $lines[] = '';

    foreach ($attachments as $att) {
        $path = (string)($att['path'] ?? '');
        $filename = (string)($att['filename'] ?? 'piece-jointe.pdf');
        if ($path === '' || !is_readable($path)) {
            continue;
        }
        $raw = @file_get_contents($path);
        if ($raw === false) {
            continue;
        }
        $mime = (string)($att['mime'] ?? 'application/octet-stream');
        $encodedName = '=?UTF-8?B?' . base64_encode($filename) . '?=';
        $lines[] = '--' . $boundary;
        $lines[] = 'Content-Type: ' . $mime . '; name="' . $encodedName . '"';
        $lines[] = 'Content-Transfer-Encoding: base64';
        $lines[] = 'Content-Disposition: attachment; filename="' . $encodedName . '"';
        $lines[] = '';
        $lines[] = chunk_split(base64_encode($raw), 76, "\r\n");
        $lines[] = '';
    }

    $lines[] = '--' . $boundary . '--';
    $lines[] = '';

    return [
        'body' => implode("\r\n", $lines),
        'content_type' => 'multipart/mixed; boundary="' . $boundary . '"',
    ];
}

/**
 * @param list<array{path:string, filename:string, mime?:string}> $attachments
 * @param array{reply_to?:string} $opts
 */
function ud_mail_try_send(string $to, string $subject, string $body, array $attachments = [], array $opts = []): bool
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

    $replyTo = trim((string)($opts['reply_to'] ?? ''));
    $contentType = 'text/plain; charset=UTF-8';
    $payloadBody = $body;

    if ($attachments !== []) {
        $built = ud_mail_build_multipart($body, $attachments);
        $payloadBody = $built['body'];
        $contentType = $built['content_type'];
    }

    $transport = strtolower((string)($mail['transport'] ?? 'mail'));
    $smtpOpts = [
        'content_type' => $contentType,
        'reply_to' => $replyTo,
    ];

    if ($transport === 'smtp') {
        return ud_mail_via_smtp($mail, $to, $subject, $payloadBody, $smtpOpts);
    }

    $from = (string)($mail['from'] ?? 'no-reply@localhost');
    $subjectEnc = '=?UTF-8?B?' . base64_encode($subject) . '?=';
    $headers = 'From: ' . $from . "\r\n";
    if ($replyTo !== '' && filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
        $headers .= 'Reply-To: ' . $replyTo . "\r\n";
    }
    $headers .= "MIME-Version: 1.0\r\n" .
        'Content-Type: ' . $contentType . "\r\n";
    if (!str_starts_with($contentType, 'multipart/')) {
        $headers .= "Content-Transfer-Encoding: 8bit\r\n";
    }

    return @mail($to, $subjectEnc, $payloadBody, $headers);
}

/**
 * Envoie la candidature au service RH avec CV et lettre de motivation en pièces jointes.
 *
 * @return bool true si l’e-mail est parti
 */
function ud_mail_job_application_to_rh(
    array $config,
    array $announcement,
    string $fullName,
    string $candidateEmail,
    string $phone,
    string $message,
    string $cvAbsPath,
    string $coverAbsPath,
    string $baseUrl
): bool {
    $mailTo = ud_offres_recrutement_notify_email($config);
    if ($mailTo === '') {
        return false;
    }

    $postTitle = (string)($announcement['title'] ?? 'Recrutement');
    $appName = (string)($config['app']['name'] ?? 'Univers Diaspora');
    $subject = '[' . $appName . ' — Candidature] ' . $postTitle;

    $safeName = ud_mail_safe_filename($fullName, 'candidat');
    $body =
        "Nouvelle candidature reçue sur le site.\n\n" .
        "Poste : " . $postTitle . "\n" .
        "Nom : " . $fullName . "\n" .
        "E-mail : " . $candidateEmail . "\n" .
        "Téléphone : " . ($phone !== '' ? $phone : '—') . "\n\n" .
        ($message !== '' ? "Message du candidat :\n" . $message . "\n\n" : '') .
        "Pièces jointes : CV et lettre de motivation (PDF).\n\n" .
        "Copie de secours dans l’administration :\n" .
        $baseUrl . '/?page=admin-job-applications' . "\n";

    $attachments = [];
    if (is_readable($cvAbsPath)) {
        $attachments[] = [
            'path' => $cvAbsPath,
            'filename' => 'CV_' . $safeName . '.pdf',
            'mime' => 'application/pdf',
        ];
    }
    if (is_readable($coverAbsPath)) {
        $attachments[] = [
            'path' => $coverAbsPath,
            'filename' => 'Lettre_motivation_' . $safeName . '.pdf',
            'mime' => 'application/pdf',
        ];
    }
    if ($attachments === []) {
        return false;
    }

    return ud_mail_try_send($mailTo, $subject, $body, $attachments, [
        'reply_to' => $candidateEmail,
    ]);
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
