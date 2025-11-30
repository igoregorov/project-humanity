<?php
declare(strict_types=1);

namespace App\Infrastructure;

use RuntimeException;

class ConfigLoader
{
    private string $envLocalPath;
    private string $envProductionPath;
    private string $languagesPath;

    public function __construct(
        string $envLocalPath = './.env.local',
        string $envProductionPath = './../config/.env.production',
        string $languagesPath = './config/languages.json'
    ) {
        $this->envLocalPath = $envLocalPath;
        $this->envProductionPath = $envProductionPath;
        $this->languagesPath = $languagesPath;
    }

    public function load(): array
    {
        // 1. Определяем окружение
        $environmentDetector = new EnvironmentDetector();
        $environment = $environmentDetector->detect();

        // 2. Загружаем .env файл в зависимости от окружения
        $envFile = $environment === EnvironmentDetector::ENV_PRODUCTION ? $this->envProductionPath : $this->envLocalPath;
        new DotEnv($envFile); // Загружает переменные в $_ENV

        // 3. Загружаем список поддерживаемых языков
        $languagesData = $this->loadLanguagesData($this->languagesPath);

        // 4. Собираем конфигурацию
        return $this->buildConfig($environment, $languagesData);
    }

    private function loadLanguagesData(string $path): array
    {
        if (!file_exists($path)) {
            throw new RuntimeException("Файл языков не найден: $path");
        }
        $languagesData = json_decode(file_get_contents($path), true);
        if (!is_array($languagesData)) {
            throw new RuntimeException("Неверный формат файла языков: $path");
        }
        return $languagesData;
    }

    private function sidebarConfig(): array
    {
        return [
            'left' => [
                'home' => [
                    'widget' => 'events',
                    'max_items' => 5
                ],
                'about' => [
                    'widget' => 'timeline',
                    'display_mode' => 'compact'
                ],
                'principles' => [
                    'widget' => 'news',
                    'max_items' => 1
                ],
                'contacts' => [
                    'widget' => 'off'
                ]
            ],
            'right' => [
                'home' => [
                    'widget' => 'news',
                    'max_items' => 3
                ],
                'about' => [
                    'widget' => 'off'
                ],
                'principles' => [
                    'widget' => 'off'
                ],
                'contacts' => [
                    'widget' => 'off'
                ]
            ]
        ];
    }

    private function buildConfig(string $environment, array $languagesData): array
    {
        $appDebug = filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN);

        // --- ВАЛИДАЦИЯ ---
        $requiredEnvVars = ['APP_DEBUG'];
        $missingVars = array_filter($requiredEnvVars, fn($var) => !isset($_ENV[$var]));
        if (!empty($missingVars)) {
            throw new RuntimeException("Отсутствуют обязательные переменные окружения: " . implode(', ', $missingVars));
        }
        // --- /ВАЛИДАЦИЯ ---

        return [
            'database' => [
                'host' => $_ENV['DB_HOST'] ?? '',
                'port' => (int) ($_ENV['DB_PORT'] ?? '8080'),
                'dbname' => $_ENV['DB_NAME'] ?? '',
                'user' => $_ENV['DB_USER'] ?? '',
                'password' => $_ENV['DB_PASS'] ?? '',
            ],
            'app' => [
                'debug' => $appDebug,
                'environment' => $environment,
                'allowedPages' => ['home', 'about', 'principles'],
                'locales' => [
                    'main'    => $_ENV['LOCALES_MAIN_PATH'] ?? './locales/main',
                    'about'   => $_ENV['LOCALES_ABOUT_PATH'] ?? './locales/about',
                    'events'  => $_ENV['LOCALES_EVENTS_PATH'] ?? './locales/events',
                    'news'    => $_ENV['LOCALES_NEWS_PATH'] ?? './locales/news',
                    'principles' => $_ENV['LOCALES_PRINCIPLES_PATH'] ?? './locales/principles',
                    'timeline' => $_ENV['LOCALES_TIMELINE_PATH'] ?? './locales/timeline',
                    'social' => $_ENV['LOCALES_SOCIAL_PATH'] ?? './locales/social',
                    'telegram' => $_ENV['LOCALES_TELEGRAM_PATH'] ?? './locales/telegram',
                ],
                'allowed_languages' => array_keys($languagesData),
                'languages' => $languagesData,
                'storage_driver' => $_ENV['STORAGE_DRIVER'] ?? 'json',
            ],
            'sidebars' => $this->sidebarConfig(),
            'version' => $_ENV['SITE_VERSION'] ?? '1.0'
        ];
    }
}