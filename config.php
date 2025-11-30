<?php
declare(strict_types=1);

// config.php

require_once __DIR__ . '/src/Infrastructure/ConfigLoader.php';
require_once __DIR__ . '/src/Infrastructure/EnvironmentDetector.php';
// require_once 'vendor/autoload.php'; // Если используешь Composer, это нужно. Если нет - autoload для своих классов.

use App\Infrastructure\ConfigLoader;

$configLoader = new ConfigLoader();
$config = $configLoader->load();

return $config;