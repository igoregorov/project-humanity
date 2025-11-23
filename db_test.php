<?php
declare(strict_types=1);

echo "<pre>";
echo "=== Тест подключения к БД ===\n";

// Попробуем загрузить .env вручную
$envFile = __DIR__ . '/.env.production';
if (!file_exists($envFile)) {
    echo " Файл .env.production не найден!\n";
    exit(1);
}

// Простая загрузка .env
$env = [];
foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
    if (str_starts_with(trim($line), '#')) continue;
    [$key, $value] = explode('=', $line, 2);
    $env[trim($key)] = trim($value, " \t\n\r\0\x0B\"'");
}

// Печать конфига (без пароля)
echo "DB_HOST: " . ($env['DB_HOST'] ?? 'не задан') . "\n";
echo "DB_PORT: " . ($env['DB_PORT'] ?? 'не задан') . "\n";
echo "DB_NAME: " . ($env['DB_NAME'] ?? 'не задан') . "\n";
echo "DB_USER: " . ($env['DB_USER'] ?? 'не задан') . "\n";
// echo "DB_PASS: " . ($env['DB_PASS'] ?? 'не задан') . "\n"; // ← не печатай пароль!

// Попытка подключения
try {
    $dsn = "mysql:host={$env['DB_HOST']};port={$env['DB_PORT']};dbname={$env['DB_NAME']};charset=utf8mb4";
    echo "Попытка подключения к: $dsn\n";

    $pdo = new PDO($dsn, $env['DB_USER'], $env['DB_PASS'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 5, // таймаут 5 сек
    ]);

    echo " Подключение успешно!\n";

    // Проверим версию MySQL/MariaDB
    $version = $pdo->query("SELECT VERSION()")->fetchColumn();
    echo "Версия БД: $version\n";

    // Проверим кодировку
    $charset = $pdo->query("SHOW VARIABLES LIKE 'character_set_connection'")->fetchColumn(1);
    echo "Кодировка соединения: $charset\n";

} catch (PDOException $e) {
    echo " Ошибка PDO: " . $e->getMessage() . "\n";
    echo "Код ошибки: " . $e->getCode() . "\n";
} catch (Exception $e) {
    echo " Общая ошибка: " . $e->getMessage() . "\n";
}

echo "</pre>";
?>