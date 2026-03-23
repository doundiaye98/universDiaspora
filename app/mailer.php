<?php
declare(strict_types=1);

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

    $from = (string)($mail['from'] ?? 'no-reply@localhost');
    $headers = 'From: ' . $from . "\r\n" .
        'Reply-To: ' . $from . "\r\n" .
        'Content-Type: text/plain; charset=UTF-8';

    // Suppress warnings to avoid breaking page when mail server is not configured.
    return @mail($to, $subject, $body, $headers);
}

