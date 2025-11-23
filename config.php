<?php
declare(strict_types=1);

// config.php

class DotEnv
{
    public function __construct(string $path)
    {
        if (!file_exists($path)) {
            throw new InvalidArgumentException("Файл .env не найден в $path");
        }
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (str_starts_with($line, '#')) {
                continue;
            }
            [$name, $value] = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);

            if (str_starts_with($value, '"') && str_ends_with($value, '"')) {
                $value = substr($value, 1, -1);
            } elseif (str_starts_with($value, "'") && str_ends_with($value, "'")) {
                $value = substr($value, 1, -1);
            }

            putenv("$name=$value");
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}

$currentHostname = gethostname();
if (str_ends_with($currentHostname, 'hoster.ru')) {
    $envFile = __DIR__ . '/.env.production';
    $environment = 'production';
} else {
    $envFile = __DIR__ . '/.env.local';
    $environment = 'local';
}
try {
    (new DotEnv($envFile));
} catch (Exception $e) {
    echo "Ошибка в DotEnv: " . $e->getMessage();
    die();
}

// Загружаем список поддерживаемых языков
$languagesFile = __DIR__ . '/config/languages.json';
if (!file_exists($languagesFile)) {
    throw new RuntimeException("Файл языков не найден: $languagesFile");
}
$languagesData = json_decode(file_get_contents($languagesFile), true);
if (!is_array($languagesData)) {
    throw new RuntimeException("Неверный формат файла языков: $languagesFile");
}

$config = [
    'database' => [
        'host' => $_ENV['DB_HOST'] ?? 'localhost',
        'port' => (int) ($_ENV['DB_PORT'] ?? 3306),
        'dbname' => $_ENV['DB_NAME'],
        'user' => $_ENV['DB_USER'],
        'password' => $_ENV['DB_PASS'],
    ],
    'app' => [
        'debug' => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),
        'environment' => $environment,
        'locales' => [
            'main'    => __DIR__ . '/' . ($_ENV['LOCALES_MAIN_PATH'] ?? 'locales/main'),
            'events'  => __DIR__ . '/' . ($_ENV['LOCALES_EVENTS_PATH'] ?? 'locales/events'),
            'news'    => __DIR__ . '/' . ($_ENV['LOCALES_NEWS_PATH'] ?? 'locales/news'),
        ],
        'allowed_languages' => array_keys($languagesData),
        'languages' => $languagesData,
        'storage_driver' => $_ENV['STORAGE_DRIVER'] ?? 'json',
    ]
];

return $config;
