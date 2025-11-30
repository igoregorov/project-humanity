<?php
// captcha.php
declare(strict_types=1);

require_once __DIR__ . '/app/bootstrap.php';

$container = require_once __DIR__ . '/container.php';
$captchaService = $container->get('captcha_service');

$code = $captchaService->generateCode();
$captchaService->generateImage($code);