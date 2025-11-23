<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "hostname = " . gethostname() . "\n";

echo "1. Попытка загрузить container.php...\n";
$container = require_once 'container.php';
echo "2. Успешно!\n";

echo "3. Попытка получить site_data...\n";
$siteData = $container->get('site_data');
echo "4. Успешно! Язык: " . $siteData['lang_code'] . "\n";