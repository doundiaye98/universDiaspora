<?php
declare(strict_types=1);

$src = __DIR__ . '/../public/assets/img/logo-univers-diaspora.jpg';
$out = __DIR__ . '/../public/assets/img/pwa';

if (!is_dir($out) && !mkdir($out, 0755, true) && !is_dir($out)) {
    fwrite(STDERR, "Cannot create $out\n");
    exit(1);
}

$im = @imagecreatefromjpeg($src);
if ($im === false) {
    fwrite(STDERR, "Cannot load logo\n");
    exit(1);
}

$w = imagesx($im);
$h = imagesy($im);

/**
 * @param resource|\GdImage $im
 */
function ud_make_pwa_icon($im, int $w, int $h, int $size, string $path, float $padRatio = 0.12): void
{
    $canvas = imagecreatetruecolor($size, $size);
    if ($canvas === false) {
        throw new RuntimeException('imagecreatetruecolor failed');
    }
    imagealphablending($canvas, false);
    imagesavealpha($canvas, true);
    $navy = imagecolorallocate($canvas, 26, 52, 98);
    imagefilledrectangle($canvas, 0, 0, $size, $size, $navy);
    imagealphablending($canvas, true);

    $pad = (int) round($size * $padRatio);
    $box = $size - (2 * $pad);
    $scale = min($box / $w, $box / $h);
    $dw = (int) round($w * $scale);
    $dh = (int) round($h * $scale);
    $dx = (int) round(($size - $dw) / 2);
    $dy = (int) round(($size - $dh) / 2);
    imagecopyresampled($canvas, $im, $dx, $dy, 0, 0, $dw, $dh, $w, $h);
    imagepng($canvas, $path, 6);
    imagedestroy($canvas);
}

ud_make_pwa_icon($im, $w, $h, 192, $out . '/icon-192.png');
ud_make_pwa_icon($im, $w, $h, 512, $out . '/icon-512.png');
ud_make_pwa_icon($im, $w, $h, 180, $out . '/apple-touch-icon.png');
ud_make_pwa_icon($im, $w, $h, 512, $out . '/maskable-512.png', 0.18);
imagedestroy($im);

echo "PWA icons generated in $out\n";
