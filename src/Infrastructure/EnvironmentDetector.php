<?php
declare(strict_types=1);

namespace App\Infrastructure;

class EnvironmentDetector
{
    public const ENV_LOCAL = 'local';
    public const ENV_PRODUCTION = 'production';

    private array $productionHostPatterns;

    public function __construct(array $hostPatterns = ['hoster.ru']) // Можно настроить через конструктор или конфиг
    {
        $this->productionHostPatterns = $hostPatterns;
    }

    public function detect(): string
    {
        $currentHostname = gethostname();

        foreach ($this->productionHostPatterns as $pattern) {
            if (str_ends_with($currentHostname, $pattern)) {
                return self::ENV_PRODUCTION;
            }
        }

        return self::ENV_LOCAL;
    }
}