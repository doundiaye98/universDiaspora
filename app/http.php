<?php
declare(strict_types=1);

function redirect(string $to): never
{
    header('Location: ' . $to, true, 302);
    exit;
}

function h(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function post(string $key, string $default = ''): string
{
    if (!isset($_POST[$key])) {
        return $default;
    }
    $v = $_POST[$key];
    if (!is_string($v)) {
        return $default;
    }
    return trim($v);
}

