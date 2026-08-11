<?php
declare(strict_types=1);

/**
 * Envoi minimal SMTP (AUTH LOGIN, TLS STARTTLS ou SSL).
 * Sans dépendance Composer — adapté à Brevo, SendGrid, OVH, etc.
 */
function ud_smtp_read_response($fp): array
{
    $msg = '';
    $first = fgets($fp, 8192);
    if ($first === false) {
        return [0, ''];
    }
    $code = (int) substr($first, 0, 3);
    $msg .= $first;
    while (isset($first[3]) && $first[3] === '-') {
        $first = fgets($fp, 8192);
        if ($first === false) {
            break;
        }
        $msg .= $first;
    }
    return [$code, $msg];
}

function ud_smtp_send_command($fp, string $cmd): array
{
    fwrite($fp, $cmd . "\r\n");
    return ud_smtp_read_response($fp);
}

function ud_extract_email(string $from): string
{
    if (preg_match('/<([^>]+)>/', $from, $m)) {
        return trim($m[1]);
    }
    return trim($from);
}

function ud_smtp_dot_stuff(string $body): string
{
    $lines = preg_split("/\r\n|\n|\r/", $body);
    if (!is_array($lines)) {
        return $body;
    }
    $out = [];
    foreach ($lines as $line) {
        if (isset($line[0]) && $line[0] === '.') {
            $out[] = '.' . $line;
        } else {
            $out[] = $line;
        }
    }
    return implode("\r\n", $out);
}

/**
 * @param array{content_type?:string, reply_to?:string} $opts
 */
function ud_mail_via_smtp(array $mail, string $to, string $subject, string $body, array $opts = []): bool
{
    $smtp = $mail['smtp'] ?? [];
    $host = trim((string)($smtp['host'] ?? ''));
    if ($host === '') {
        return false;
    }
    $port = (int)($smtp['port'] ?? 587);
    $encryption = strtolower((string)($smtp['encryption'] ?? 'tls'));
    $timeout = (int)($smtp['timeout'] ?? 20);
    $user = (string)($smtp['username'] ?? '');
    $pass = (string)($smtp['password'] ?? '');
    $verifyPeer = !empty($smtp['verify_peer']);

    $from = (string)($mail['from'] ?? '');
    if ($from === '' || $to === '') {
        return false;
    }

    $remote = ($encryption === 'ssl')
        ? 'ssl://' . $host . ':' . $port
        : 'tcp://' . $host . ':' . $port;

    $ctx = stream_context_create([
        'ssl' => [
            'verify_peer' => $verifyPeer,
            'verify_peer_name' => $verifyPeer,
        ],
    ]);

    $fp = @stream_socket_client(
        $remote,
        $errno,
        $errstr,
        $timeout,
        STREAM_CLIENT_CONNECT,
        $ctx
    );
    if (!$fp) {
        return false;
    }
    stream_set_timeout($fp, $timeout);

    [$code] = ud_smtp_read_response($fp);
    if ($code !== 220) {
        fclose($fp);
        return false;
    }

    $ehloHost = 'localhost';
    [$code] = ud_smtp_send_command($fp, 'EHLO ' . $ehloHost);
    if ($code !== 250) {
        fclose($fp);
        return false;
    }

    if ($encryption === 'tls' && $port !== 465) {
        [$code] = ud_smtp_send_command($fp, 'STARTTLS');
        if ($code !== 220) {
            fclose($fp);
            return false;
        }
        $cryptoOk = @stream_socket_enable_crypto(
            $fp,
            true,
            STREAM_CRYPTO_METHOD_TLS_CLIENT
        );
        if (!$cryptoOk) {
            fclose($fp);
            return false;
        }
        [$code] = ud_smtp_send_command($fp, 'EHLO ' . $ehloHost);
        if ($code !== 250) {
            fclose($fp);
            return false;
        }
    }

    if ($user !== '' && $pass !== '') {
        [$code] = ud_smtp_send_command($fp, 'AUTH LOGIN');
        if ($code !== 334) {
            fclose($fp);
            return false;
        }
        [$code] = ud_smtp_send_command($fp, base64_encode($user));
        if ($code !== 334) {
            fclose($fp);
            return false;
        }
        [$code] = ud_smtp_send_command($fp, base64_encode($pass));
        if ($code !== 235) {
            fclose($fp);
            return false;
        }
    }

    $fromAddr = ud_extract_email($from);
    [$code] = ud_smtp_send_command($fp, 'MAIL FROM:<' . preg_replace('/[<>]/', '', $fromAddr) . '>');
    if ($code !== 250) {
        fclose($fp);
        return false;
    }

    $toAddr = ud_extract_email($to);
    [$code] = ud_smtp_send_command($fp, 'RCPT TO:<' . preg_replace('/[<>]/', '', $toAddr) . '>');
    if ($code !== 250 && $code !== 251) {
        fclose($fp);
        return false;
    }

    [$code] = ud_smtp_send_command($fp, 'DATA');
    if ($code !== 354) {
        fclose($fp);
        return false;
    }

    $subjectEnc = '=?UTF-8?B?' . base64_encode($subject) . '?=';
    $contentType = (string)($opts['content_type'] ?? 'text/plain; charset=UTF-8');
    $transferEnc = str_starts_with($contentType, 'multipart/')
        ? ''
        : "Content-Transfer-Encoding: 8bit\r\n";

    $headers =
        "From: {$from}\r\n" .
        "To: {$to}\r\n" .
        "Subject: {$subjectEnc}\r\n" .
        "MIME-Version: 1.0\r\n";
    $replyTo = trim((string)($opts['reply_to'] ?? ''));
    if ($replyTo !== '' && filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
        $headers .= 'Reply-To: ' . $replyTo . "\r\n";
    }
    $headers .= "Content-Type: {$contentType}\r\n" . $transferEnc;

    $payload = $headers . "\r\n" . ud_smtp_dot_stuff($body) . "\r\n.";
    fwrite($fp, $payload . "\r\n");

    [$code] = ud_smtp_read_response($fp);
    ud_smtp_send_command($fp, 'QUIT');
    fclose($fp);

    return $code === 250;
}
