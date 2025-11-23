<?php
declare(strict_types=1);

$container = require_once 'container.php';
$pdo = $container->get('pdo');

$migrationsDir = __DIR__ . '/migrations';

// Убедимся, что таблица миграций существует
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS migrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            migration_name VARCHAR(255) NOT NULL UNIQUE,
            applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
} catch (Exception $e) {
    die("ОШИБКА: Не удалось создать таблицу миграций: " . $e->getMessage() . "\n");
}

// Получим список уже применённых миграций
try {
    $stmt = $pdo->query("SELECT migration_name FROM migrations ORDER BY applied_at ASC");
    $appliedMigrations = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    die("ОШИБКА: Не удалось получить список миграций: " . $e->getMessage() . "\n");
}

// Найдём все .sql файлы
$migrationFiles = [];
if (is_dir($migrationsDir)) {
    foreach (scandir($migrationsDir) as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
            $migrationFiles[] = $file;
        }
    }
    sort($migrationFiles);
} else {
    die("ОШИБКА: Директория миграций '$migrationsDir' не найдена\n");
}

$pendingMigrations = array_diff($migrationFiles, $appliedMigrations);

if (empty($pendingMigrations)) {
    echo "Все миграции уже применены.\n";
    exit(0);
}

echo "Найдены " . count($pendingMigrations) . " новых миграций для применения:\n";

foreach ($pendingMigrations as $migrationFile) {
    echo "  - Применяем $migrationFile... ";

    $sqlFile = $migrationsDir . '/' . $migrationFile;

    if (!file_exists($sqlFile)) {
        echo "ОШИБКА: Файл не найден\n";
        exit(1);
    }

    try {
        $pdo->beginTransaction();

        $sqlContent = file_get_contents($sqlFile);
        if (empty($sqlContent)) {
            throw new Exception("Файл миграции пуст");
        }

        $pdo->exec($sqlContent);

        $stmt = $pdo->prepare("INSERT INTO migrations (migration_name) VALUES (?)");
        $stmt->execute([$migrationFile]);

        $pdo->commit();
        echo "success\n";

    } catch (Exception $e) {
        $pdo->rollBack();
        echo "error: " . $e->getMessage() . "\n";
        exit(1);
    }
}

echo "Все миграции успешно применены!\n";
