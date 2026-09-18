<?php
// captcha.php
declare(strict_types=1);

// bootstrap.php УЖЕ подключает container.php и возвращает экземпляр контейнера!
$container = require_once __DIR__ . '/app/bootstrap.php';

// Убираем повторный require_once, который возвращал true вместо объекта
$captchaService = $container->get('captcha_service');

$code = $captchaService->generateCode();
$captchaService->generateImage($code);
