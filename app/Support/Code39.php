<?php

namespace App\Support;

use GdImage;

class Code39
{
    private const PATTERNS = [
        '0' => 'nnnwwnwnn', '1' => 'wnnwnnnnw', '2' => 'nnwwnnnnw', '3' => 'wnwwnnnnn', '4' => 'nnnwwnnnw', '5' => 'wnnwwnnnn', '6' => 'nnwwwnnnn', '7' => 'nnnwnnwnw', '8' => 'wnnwnnwnn', '9' => 'nnwwnnwnn',
        'A' => 'wnnnnwnnw', 'B' => 'nnwnnwnnw', 'C' => 'wnwnnwnnn', 'D' => 'nnnnwwnnw', 'E' => 'wnnnwwnnn', 'F' => 'nnwnwwnnn', 'G' => 'nnnnnwwnw', 'H' => 'wnnnnwwnn', 'I' => 'nnwnnwwnn', 'J' => 'nnnnwwwnn',
        'K' => 'wnnnnnnww', 'L' => 'nnwnnnnww', 'M' => 'wnwnnnnwn', 'N' => 'nnnnwnnww', 'O' => 'wnnnwnnwn', 'P' => 'nnwnwnnwn', 'Q' => 'nnnnnnwww', 'R' => 'wnnnnnwwn', 'S' => 'nnwnnnwwn', 'T' => 'nnnnwnwwn',
        'U' => 'wwnnnnnnw', 'V' => 'nwwnnnnnw', 'W' => 'wwwnnnnnn', 'X' => 'nwnnwnnnw', 'Y' => 'wwnnwnnnn', 'Z' => 'nwwnwnnnn', '-' => 'nwnnnnwnw', '.' => 'wwnnnnwnn', ' ' => 'nwwnnnwnn', '*' => 'nwnnwnwnn',
    ];

    public static function svg(string $value, int $height = 58): string
    {
        $encoded = '*'.strtoupper($value).'*';
        $x = 10;
        $rects = '';
        foreach (str_split($encoded) as $char) {
            foreach (str_split(self::PATTERNS[$char] ?? self::PATTERNS['-']) as $index => $width) {
                $barWidth = $width === 'w' ? 3 : 1;
                if ($index % 2 === 0) {
                    $rects .= "<rect x=\"{$x}\" y=\"4\" width=\"{$barWidth}\" height=\"{$height}\"/>";
                }
                $x += $barWidth;
            }
            $x++;
        }
        $safe = htmlspecialchars(trim($encoded, '*'), ENT_QUOTES);
        $width = $x + 10;

        return "<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 {$width} ".($height + 24)."\" role=\"img\" aria-label=\"Barcode {$safe}\"><g fill=\"#111827\">{$rects}</g><text x=\"50%\" y=\"".($height + 19)."\" text-anchor=\"middle\" font-family=\"monospace\" font-size=\"10\" letter-spacing=\"2\">{$safe}</text></svg>";
    }

    public static function image(string $value, int $scale = 3, int $barHeight = 70): GdImage
    {
        $encoded = '*'.strtoupper($value).'*';
        $modules = 20;

        foreach (str_split($encoded) as $char) {
            foreach (str_split(self::PATTERNS[$char] ?? self::PATTERNS['-']) as $width) {
                $modules += $width === 'w' ? 3 : 1;
            }
            $modules++;
        }

        $textHeight = 24;
        $image = imagecreatetruecolor($modules * $scale, $barHeight + $textHeight);
        $white = imagecolorallocate($image, 255, 255, 255);
        $navy = imagecolorallocate($image, 17, 24, 39);
        imagefill($image, 0, 0, $white);

        $x = 10 * $scale;
        foreach (str_split($encoded) as $char) {
            foreach (str_split(self::PATTERNS[$char] ?? self::PATTERNS['-']) as $index => $width) {
                $barWidth = ($width === 'w' ? 3 : 1) * $scale;
                if ($index % 2 === 0) {
                    imagefilledrectangle($image, $x, 3, $x + $barWidth - 1, $barHeight, $navy);
                }
                $x += $barWidth;
            }
            $x += $scale;
        }

        $label = strtoupper($value);
        $font = 3;
        $labelX = max(0, (imagesx($image) - imagefontwidth($font) * strlen($label)) / 2);
        imagestring($image, $font, (int) $labelX, $barHeight + 5, $label, $navy);

        return $image;
    }

    public static function png(string $value): string
    {
        $image = self::image($value);
        ob_start();
        imagepng($image);
        $contents = (string) ob_get_clean();
        imagedestroy($image);

        return $contents;
    }
}
