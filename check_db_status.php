<?php
// check_db_status.php
declare(strict_types=1);

$container = require_once __DIR__ . '/app/bootstrap.php';

try {
    $healthCheck = $container->get('database.health_check');
    $config = $container->get('config');

    echo "=== Статус базы данных ===\n";
    echo "Настройка DB_IS_ACTIVE: " . ($config['database']['is_active'] ? 'true' : 'false') . "\n";
    echo "Активна в системе: " . ($healthCheck->isActive() ? 'true' : 'false') . "\n";

    if ($healthCheck->isActive()) {
        echo "Состояние подключения: " . ($healthCheck->isHealthy() ? 'Подключено' : 'Не подключено') . "\n";

        if (!$healthCheck->isHealthy()) {
            echo "Ошибка: " . $healthCheck->getLastError() . "\n";
            echo "Используется файловое хранилище\n";
        }
    } else {
        echo "База данных отключена в настройках\n";
        echo "Используется файловое хранилище\n";
    }

} catch (Exception $e) {
    echo "Ошибка при проверке статуса: " . $e->getMessage() . "\n";
}