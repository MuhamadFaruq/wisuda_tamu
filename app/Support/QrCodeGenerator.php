<?php

namespace App\Support;

use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\SvgWriter;
use GdImage;

class QrCodeGenerator
{
    private const SIZE = 540;
    private const MARGIN = 30;

    public static function svg(string $value): string
    {
        return (new SvgWriter)->write(self::make($value), options: [
            SvgWriter::WRITER_OPTION_EXCLUDE_XML_DECLARATION => true,
        ])->getString();
    }

    public static function image(string $value): GdImage
    {
        $image = imagecreatefromstring(self::png($value));
        if (! $image instanceof GdImage) {
            throw new \RuntimeException('Gambar QR Code gagal dibuat.');
        }
        imageresolution($image, 300, 300);

        return $image;
    }

    public static function png(string $value): string
    {
        return (new PngWriter)->write(self::make($value))->getString();
    }

    private static function make(string $value): QrCode
    {
        return new QrCode(
            data: strtoupper(trim($value)),
            encoding: new Encoding('ISO-8859-1'),
            errorCorrectionLevel: ErrorCorrectionLevel::Medium,
            size: self::SIZE,
            margin: self::MARGIN,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
        );
    }
}
