<?php
declare(strict_types=1);

//src/Infrastructure/ConfigLoader.php

namespace App\Infrastructure;

use RuntimeException;

class ConfigLoader
{
    private string $envSettingPath;
    private string $languagesPath;
    private string $settingsPath;

    public function __construct(
        ?string $envSettingPath = null,
        ?string $languagesPath = null,
        ?string $settingsPath = null
    ) {
        $projectRoot = dirname(__DIR__, 2);

        $this->envSettingPath = $envSettingPath ?? $projectRoot . '/.env.setting';
        $this->languagesPath = $languagesPath ?? $projectRoot . '/config/languages.json';
        $this->settingsPath = $settingsPath ?? $projectRoot . '/config/.settings.config';
    }

    public function load(): array
    {
        if (!is_file($this->envSettingPath) || !is_readable($this->envSettingPath)) {
            throw new RuntimeException("Файл настроек не найден или недоступен: $this->envSettingPath");
        }

        new DotEnv($this->envSettingPath);

        $environmentDetector = new EnvironmentDetector();
        $environment = $environmentDetector->detect();

        $languagesData = $this->loadLanguagesData($this->languagesPath);
        $settingsData = $this->loadSettingsData($this->settingsPath);

        return $this->buildConfig($environment, $languagesData, $settingsData);
    }

    private function loadLanguagesData(string $path): array
    {
        if (!is_file($path) || !is_readable($path)) {
            throw new RuntimeException("Файл языков не найден или недоступен для чтения: $path");
        }

        $contents = file_get_contents($path);

        if ($contents === false) {
            throw new RuntimeException("Не удалось прочитать файл языков: $path");
        }

        $languagesData = json_decode($contents, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException(
                "Неверный JSON в файле языков: $path. Ошибка: " . json_last_error_msg()
            );
        }

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

        if (!is_file($path) || !is_readable($path)) {
            error_log("Файл настроек не найден или недоступен для чтения: $path. Используются настройки по умолчанию.");
            return $defaultSettings;
        }

        $settingsContent = file_get_contents($path);

        if ($settingsContent === false) {
            error_log("Не удалось прочитать файл настроек: $path. Используются настройки по умолчанию.");
            return $defaultSettings;
        }

        $settings = [];

        $lines = explode("\n", $settingsContent);

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '' || str_starts_with($line, ';')) {
                continue;
            }

            if (str_contains($line, '=')) {
                [$key, $value] = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);

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

        $requiredEnvVars = ['APP_ENV', 'APP_DEBUG'];
        $missingVars = array_filter(
            $requiredEnvVars,
            static fn (string $var): bool => !isset($_ENV[$var])
        );

        if ($missingVars !== []) {
            throw new RuntimeException(
                "Отсутствуют обязательные переменные окружения: " . implode(', ', $missingVars)
            );
        }

        $allowedHosts = self::parseAllowedHosts(
            (string) ($_ENV['ALLOWED_HOSTS'] ?? $_SERVER['ALLOWED_HOSTS'] ?? '')
        );

        $projectRoot = dirname(__DIR__, 2);

        return [
            'database' => [
                'host' => $_ENV['DB_HOST'] ?? '',
                'port' => (int) ($_ENV['DB_PORT'] ?? '8080'),
                'dbname' => $_ENV['DB_NAME'] ?? '',
                'user' => $_ENV['DB_USER'] ?? '',
                'password' => $_ENV['DB_PASS'] ?? '',
                'is_active' => self::resolveDatabaseIsActive($settingsData),
            ],
            'app' => [
                'debug' => $appDebug,
                'environment' => $environment,
                'allowed_hosts' => $allowedHosts,
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

    public static function resolveDatabaseIsActive(array $settings, bool $default = true): bool
    {
        return self::resolveBool($settings, $default);
    }

    private static function resolveBool(array $settings, bool $default): bool
    {
        $keys = ['DB_IS_ACTIVE', 'db_is_active'];
        foreach ($keys as $key) {
            if (!array_key_exists($key, $settings)) {
                continue;
            }

            $value = filter_var(
                $settings[$key],
                FILTER_VALIDATE_BOOLEAN,
                FILTER_NULL_ON_FAILURE
            );

            if ($value !== null) {
                return $value;
            }
        }

        return $default;
    }

    /**
     * Нормализует хост для сравнения.
     *
     * Поддерживает:
     * - example.com
     * - example.com:8080
     * - localhost:8000
     * - 127.0.0.1:8080
     * - [::1]:8000
     * - ::1
     * - http://example.com/path
     * - https://example.com:443/path
     */
    public static function normalizeHost(string $host): string
    {
        $host = trim(strtolower($host));

        if ($host === '') {
            return '';
        }

        // Удаляем схему, если вдруг пришла вместе с хостом.
        $host = (string) preg_replace('#^[a-z][a-z0-9+.-]*://#i', '', $host);

        // Удаляем path/query/fragment.
        $host = (string) preg_replace('~[/?#].*$~', '', $host);

        // IPv6 в квадратных скобках: [::1]:8000 -> ::1
        if (str_starts_with($host, '[')) {
            $closePos = strpos($host, ']');

            if ($closePos !== false) {
                $host = substr($host, 1, $closePos - 1);
            }
        } else {
            if (substr_count($host, ':') === 1) {
                $host = (string) preg_replace('/:\d+$/', '', $host);
            }
        }

        // Убираем завершающую точку FQDN, если она есть.
        return rtrim($host, '.');
    }

    /**
     * Парсит список разрешённых хостов.
     *
     * Ожидается формат:
     * ALLOWED_HOSTS=localhost,127.0.0.1,::1
     *
     * Также допускается разделение пробелами:
     * ALLOWED_HOSTS=localhost 127.0.0.1 ::1
     */
    public static function parseAllowedHosts(string $raw): array
    {
        $raw = trim($raw);

        if ($raw === '') {
            return [];
        }

        $parts = preg_split('/[\s,]+/', $raw, -1, PREG_SPLIT_NO_EMPTY);

        if (!is_array($parts)) {
            return [];
        }

        $hosts = [];

        foreach ($parts as $part) {
            $host = self::normalizeHost((string) $part);

            if ($host !== '' && !in_array($host, $hosts, true)) {
                $hosts[] = $host;
            }
        }

        return $hosts;
    }
}