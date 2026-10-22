<?php
declare(strict_types=1);

namespace App\Infrastructure;

use RuntimeException;

class ConfigLoader
{
    private string $envLocalPath;
    private string $envProductionPath;
    private string $languagesPath;
    private string $settingsPath;

    public function __construct(
        ?string $envLocalPath = null,
        ?string $envProductionPath = null,
        ?string $languagesPath = null,
        ?string $settingsPath = null
    ) {
        $projectRoot = dirname(__DIR__, 2); 

        $this->envLocalPath = $envLocalPath ?? $projectRoot . '/.env.local';
        $this->envProductionPath = $envProductionPath ?? $projectRoot . '/config/.env.production';
        $this->languagesPath = $languagesPath ?? $projectRoot . '/config/languages.json';
        $this->settingsPath = $settingsPath ?? $projectRoot . '/config/.settings.config';
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

        // 4. Загружаем настройки приложения
        $settingsData = $this->loadSettingsData($this->settingsPath);

        // 5. Собираем конфигурацию
        return $this->buildConfig($environment, $languagesData, $settingsData);
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

    private function loadSettingsData(string $path): array
    {
        $defaultSettings = [
            'db_is_active' => true,
            'feature_auth' => true,
            'feature_comments' => false
        ];

        if (!file_exists($path)) {
            error_log("Файл настроек не найден: $path. Используются настройки по умолчанию.");
            return $defaultSettings;
        }

        $settingsContent = file_get_contents($path);
        $settings = [];

        // Парсим INI-подобный формат
        $lines = explode("\n", $settingsContent);
        foreach ($lines as $line) {
            $line = trim($line);

            // Пропускаем комментарии и пустые строки
            if (empty($line) || str_starts_with($line, ';')) {
                continue;
            }

            // Разбираем ключ=значение
            if (str_contains($line, '=')) {
                [$key, $value] = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);

                // Преобразуем булевы значения
                if ($value === 'true') {
                    $value = true;
                } elseif ($value === 'false') {
                    $value = false;
                } elseif (is_numeric($value)) {
                    $value = (int) $value;
                }

                $settings[$key] = $value;
            }
        }

        return array_merge($defaultSettings, $settings);
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
    private function buildConfig(string $environment, array $languagesData, array $settingsData): array
    {
        $appDebug = filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN);
        
        $requiredEnvVars = ['APP_DEBUG'];
        $missingVars = array_filter($requiredEnvVars, fn($var) => !isset($_ENV[$var]));
        if (!empty($missingVars)) {
            throw new RuntimeException("Отсутствуют обязательные переменные окружения: " . implode(', ', $missingVars));
        }

        $projectRoot = dirname(__DIR__, 2);

        return [
            'database' => [
                'host' => $_ENV['DB_HOST'] ?? '',
                'port' => (int) ($_ENV['DB_PORT'] ?? '8080'),
                'dbname' => $_ENV['DB_NAME'] ?? '',
                'user' => $_ENV['DB_USER'] ?? '',
                'password' => $_ENV['DB_PASS'] ?? '',
                'is_active' => $settingsData['DB_IS_ACTIVE'] ?? true
            ],
            'app' => [
                'debug' => $appDebug,
                'environment' => $environment,
                'allowedPages' => ['home', 'about', 'principles'],
                'locales' => [
                    'main'       => $projectRoot . '/locales/main',
                    'about'      => $projectRoot . '/locales/about',
                    'events'     => $projectRoot . '/locales/events',
                    'news'       => $projectRoot . '/locales/news',
                    'principles' => $projectRoot . '/locales/principles',
                    'timeline'   => $projectRoot . '/locales/timeline',
                    'social'     => $projectRoot . '/locales/social',
                    'telegram'   => $projectRoot . '/locales/telegram',
                ],
                'allowed_languages' => array_keys($languagesData),
                'languages' => $languagesData,
                'storage_driver' => $_ENV['STORAGE_DRIVER'] ?? 'json',
                'settings' => $settingsData
            ],
            'sidebars' => $this->sidebarConfig(),
            'version' => $_ENV['SITE_VERSION'] ?? '1.0'
        ];
    }
}
