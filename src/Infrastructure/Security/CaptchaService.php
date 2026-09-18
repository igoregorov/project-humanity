<?php
declare(strict_types=1);
// src/Infrastructure/Security/CaptchaService.php

namespace App\Infrastructure\Security;

use Exception;

class CaptchaService
{
    private const CHARS = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789';
    private const LENGTH = 6;

    /**
     * @throws Exception
     */
    public function generateCode(): string
    {
        $code = '';
        $max = strlen(self::CHARS) - 1;

        for ($i = 0; $i < self::LENGTH; $i++) {
            $code .= self::CHARS[random_int(0, $max)];
        }

        $_SESSION['captcha_code'] = $code;
        $_SESSION['captcha_time'] = time();

        return $code;
    }

    public function generateImage(string $code): void
    {
        $width = 200;
        $height = 60;

        $image = imagecreate($width, $height);

        // Цвета
        $bgColor = imagecolorallocate($image, 255, 255, 255);
        $textColor = imagecolorallocate($image, 0, 0, 0);
        $noiseColor = imagecolorallocate($image, 200, 200, 200);

        // Заполняем фон
        imagefill($image, 0, 0, $bgColor);

        // Добавляем шум
        for ($i = 0; $i < ($width * $height) / 3; $i++) {
            imagesetpixel($image, random_int(0, $width), random_int(0, $height), $noiseColor);
        }

        // Линии
        for ($i = 0; $i < 5; $i++) {
            imageline($image, random_int(0, $width), random_int(0, $height), random_int(0, $width), random_int(0, $height), $noiseColor);
        }

        // Текст
        $fontFile = __DIR__ . '/arial.ttf';
        $useTtf = file_exists($fontFile);

        if ($useTtf) {
            // Используем TTF шрифт, если он есть. Приводим координаты к int для PHP 8.1+
            $charWidth = 20; // Примерная ширина для размера 20
            $textWidth = $charWidth * self::LENGTH;
            $x = (int)(($width - $textWidth) / 2);
            $y = (int)(($height - 20) / 2) + 15; // Корректировка базовой линии TTF

            for ($i = 0; $i < self::LENGTH; $i++) {
                $char = $code[$i];
                $angle = random_int(-10, 10);
                imagettftext($image, 20, $angle, (int)($x + ($i * $charWidth)), (int)$y, $textColor, $fontFile, $char);
            }
        } else {
            // Фоллбэк на встроенный шрифт, если arial.ttf не найден
            $font = 5;
            $charWidth = imagefontwidth($font);
            $textWidth = $charWidth * self::LENGTH;
            $x = (int)(($width - $textWidth) / 2);
            $y = (int)(($height - imagefontheight($font)) / 2);

            for ($i = 0; $i < self::LENGTH; $i++) {
                $char = $code[$i];
                imagestring($image, $font, (int)($x + ($i * $charWidth)), (int)$y, $char, $textColor);
            }
        }

        header('Content-Type: image/png');
        imagepng($image);
        imagedestroy($image);
    }

    public function validate(string $userCode): bool
    {
        if (!isset($_SESSION['captcha_code']) || !isset($_SESSION['captcha_time'])) {
            return false;
        }

        $storedCode = $_SESSION['captcha_code'];
        $captchaTime = $_SESSION['captcha_time'];

        // CAPTCHA действительна 10 минут
        if (time() - $captchaTime > 600) {
            unset($_SESSION['captcha_code'], $_SESSION['captcha_time']);
            return false;
        }

        // Удаляем использованную CAPTCHA
        unset($_SESSION['captcha_code'], $_SESSION['captcha_time']);

        return strtolower($userCode) === strtolower($storedCode);
    }
}
