<?php
declare(strict_types=1);

// config.php

class DotEnv
{
    public function __construct(string $path)
    {
        echo "\n<!-- DEBUG INFO (DotEnv start): Loading file: $path -->\n"; // Отладочный комментарий

        if (!file_exists($path)) {
            echo "\n<!-- DEBUG INFO (DotEnv error): File not found: $path -->\n"; // Отладочный комментарий
            throw new InvalidArgumentException("Файл .env не найден в $path");
        }
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        echo "\n<!-- DEBUG INFO (DotEnv): Found " . count($lines) . " lines in file -->\n"; // Отладочный комментарий

        foreach ($lines as $line) {
            echo "\n<!-- DEBUG INFO (DotEnv loop): Processing line: " . htmlspecialchars($line) . " -->\n"; // Отладочный комментарий
            if (str_starts_with($line, '#')) {
                echo "\n<!-- DEBUG INFO (DotEnv loop): Skipping comment line -->\n"; // Отладочный комментарий
                continue;
            }

            // --- ИСПРАВЛЕННАЯ ЛОГИКА DotEnv ---
            // Пропускаем пустые строки (на всякий случай)
            if (trim($line) === '') {
                echo "\n<!-- DEBUG INFO (DotEnv loop): Skipping empty line -->\n"; // Отладочный комментарий
                continue;
            }

            $parts = explode('=', $line, 2);

            // Проверяем, есть ли и имя, и значение (должно быть 2 части)
            if (count($parts) !== 2) {
                echo "\n<!-- DEBUG INFO (DotEnv error): Invalid line format: " . htmlspecialchars($line) . " -->\n"; // Отладочный комментарий
                error_log("Неверный формат строки в .env файле: $line");
                continue; // Пропускаем некорректную строку, как будто её не было
            }

            [$name, $value] = $parts;
            $name = trim($name);
            $value = trim($value);

            // Пропускаем строки, где имя переменной пустое
            if ($name === '') {
                echo "\n<!-- DEBUG INFO (DotEnv error): Empty name in line: " . htmlspecialchars($line) . " -->\n"; // Отладочный комментарий
                error_log("Пустое имя переменной в .env файле: $line");
                continue;
            }

            echo "\n<!-- DEBUG INFO (DotEnv loop): Parsed name: " . htmlspecialchars($name) . ", value: " . htmlspecialchars($value) . " -->\n"; // Отладочный комментарий

            if (str_starts_with($value, '"') && str_ends_with($value, '"')) {
                $value = substr($value, 1, -1);
            } elseif (str_starts_with($value, "'") && str_ends_with($value, "'")) {
                $value = substr($value, 1, -1);
            }

            putenv("$name=$value");
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
        echo "\n<!-- DEBUG INFO (DotEnv end): Finished loading .env file -->\n"; // Отладочный комментарий
    }
}

$currentHostname = gethostname();
echo "\n<!-- DEBUG INFO (config.php): Hostname is: $currentHostname -->\n"; // Отладочный комментарий
if (str_ends_with($currentHostname, 'hoster.ru')) {
    $envFile = __DIR__ . '/../config/.env.production';
    $environment = 'production';
} else {
    $envFile = __DIR__ . '/.env.local';
    $environment = 'local';
}
echo "\n<!-- DEBUG INFO (config.php): Environment is: $environment, .env file is: $envFile -->\n"; // Отладочный комментарий

try {
    /** @throws InvalidArgumentException */
    /** @throws RuntimeException */
    (new DotEnv($envFile));
    echo "\n<!-- DEBUG INFO (config.php): DotEnv loaded successfully -->\n"; // Отладочный комментарий
} catch (Exception $e) {
    echo "\n<!-- DEBUG INFO (config.php catch): DotEnv failed with message: " . htmlspecialchars($e->getMessage()) . " -->\n"; // Отладочный комментарий
    // Не выводим echo здесь, так как это может мешать sendCleanErrorPage в index.php
    // $debugMode = 'true'; // Не нужно, $debugMode определяется в index.php
    throw $e; // Бросаем исключение дальше
}

// Загружаем список поддерживаемых языков
$languagesFile = __DIR__ . '/config/languages.json'; // Путь должен быть относительно index.php, если config.php вызывается из него
// Возможно, правильный путь: $languagesFile = __DIR__ . '/../config/languages.json'; или 'config/languages.json';
echo "\n<!-- DEBUG INFO (config.php): Loading languages file: $languagesFile -->\n"; // Отладочный комментарий
if (!file_exists($languagesFile)) {
    echo "\n<!-- DEBUG INFO (config.php error): Languages file not found: $languagesFile -->\n"; // Отладочный комментарий
    throw new RuntimeException("Файл языков не найден: $languagesFile");
}
$languagesData = json_decode(file_get_contents($languagesFile), true);
if (!is_array($languagesData)) {
    echo "\n<!-- DEBUG INFO (config.php error): Languages file format is invalid -->\n"; // Отладочный комментарий
    throw new RuntimeException("Неверный формат файла языков: $languagesFile");
}
echo "\n<!-- DEBUG INFO (config.php): Languages file loaded successfully -->\n"; // Отладочный комментарий

// --- Выводим значение APP_DEBUG из $_ENV ---
echo "\n<!-- DEBUG INFO (config.php): APP_DEBUG from \$_ENV is: " . ($_ENV['APP_DEBUG'] ?? 'NOT SET') . " -->\n"; // Отладочный комментарий

// --- СОБИРАЕМ КОНФИГ ПОЭЛЕМЕНТНО С ПРОВЕРКАМИ ---

// 1. Собираем секцию 'database'
try {
    $databaseConfig = [
        'host' => $_ENV['DB_HOST'] ?? 'localhost',
        'port' => (int) ($_ENV['DB_PORT'] ?? 3306),
        'dbname' => $_ENV['DB_NAME'] ?? '',
        'user' => $_ENV['DB_USER'] ?? '',
        'password' => $_ENV['DB_PASS'] ?? '',
    ];
} catch (Throwable $e) {
    error_log("Ошибка при создании секции 'database': " . $e->getMessage());
    throw $e; // Бросаем дальше
}

// 2. Собираем секцию 'app'
try {
    $appDebug = filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN);
    $appLocales = [
        'main'    => __DIR__ . '/' . ($_ENV['LOCALES_MAIN_PATH'] ?? 'locales/main'),
        'about'   => __DIR__ . '/' . ($_ENV['LOCALES_ABOUT_PATH'] ?? 'locales/about'),
        'events'  => __DIR__ . '/' . ($_ENV['LOCALES_EVENTS_PATH'] ?? 'locales/events'),
        'news'    => __DIR__ . '/' . ($_ENV['LOCALES_NEWS_PATH'] ?? 'locales/news'),
        'principles' => __DIR__ . '/' . ($_ENV['LOCALES_PRINCIPLES_PATH'] ?? 'locales/principles'),
    ];

    // --- КРИТИЧЕСКАЯ ОПЕРАЦИЯ: array_keys($languagesData) ---
    $allowedLanguageKeys = array_keys($languagesData);

    $appConfig = [
        'debug' => $appDebug,
        'environment' => $environment,
        'allowedPages' => ['home', 'about', 'principles'],
        'locales' => $appLocales,
        'allowed_languages' => $allowedLanguageKeys,
        'languages' => $languagesData,
        'storage_driver' => $_ENV['STORAGE_DRIVER'] ?? 'json',
    ];

} catch (Throwable $e) {
    error_log("Ошибка при создании секции 'app': " . $e->getMessage());
    throw $e; // Бросаем дальше
}

// 3. Собираем весь конфиг
try {
    $config = [
        'database' => $databaseConfig,
        'app' => $appConfig,
        'version' => $_ENV['SITE_VERSION'] ?? '1.0'
    ];

    // --- КРИТИЧЕСКАЯ ПРОВЕРКА: УБЕДИМСЯ, ЧТО $config - МАССИВ ---
    if (!is_array($config)) {
        // Это маловероятно, но на всякий случай
        throw new RuntimeException("Финальный \$config не является массивом: " . gettype($config) . ". Значение: " . var_export($config, true));
    }

    // --- ПРОВЕРКА: УБЕДИМСЯ, ЧТО $config['app'] - МАССИВ ---
    if (!isset($config['app']) || !is_array($config['app'])) {
        throw new RuntimeException("Ключ 'app' в \$config не является массивом или отсутствует. Тип: " . (isset($config['app']) ? gettype($config['app']) : 'UNSET') . ". Значение: " . var_export($config['app'] ?? null, true));
    }

    // --- ПРОВЕРКА: УБЕДИМСЯ, ЧТО $config['app']['debug'] - BOOLEAN ---
    if (!isset($config['app']['debug']) || !is_bool($config['app']['debug'])) {
        throw new RuntimeException("Ключ 'app.debug' в \$config не является boolean или отсутствует. Тип: " . (isset($config['app']['debug']) ? gettype($config['app']['debug']) : 'UNSET') . ". Значение: " . var_export($config['app']['debug'] ?? null, true));
    }

} catch (Throwable $e) {
    error_log("Ошибка при создании финального \$config: " . $e->getMessage());
    throw $e; // Бросаем дальше
}

// --- Выводим значение $config['app']['debug'] ---
echo "\n<!-- DEBUG INFO (config.php): \$config['app']['debug'] is " . ($config['app']['debug'] ? 'TRUE' : 'FALSE') . " -->\n"; // Отладочный комментарий

return $config;