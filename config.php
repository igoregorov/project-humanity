<?php
declare(strict_types=1);

// config.php

use App\Infrastructure\ConfigLoader;

$configLoader = new ConfigLoader();
$config = $configLoader->load();

return $config;

