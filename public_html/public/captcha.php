<?php
// public_html/public/captcha.php
declare(strict_types=1);

// bootstrap.php уже подключает container.php и возвращает контейнер.
$container = require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

/** @var App\Infrastructure\Security\CaptchaService $captchaService */
$captchaService = $container->get('captcha_service');

$code = $captchaService->generateCode();

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

$captchaService->generateImage($code);