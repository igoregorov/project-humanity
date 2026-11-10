<?php
declare(strict_types=1);

// src/Infrastructure/EnvironmentDetector.php

namespace App\Infrastructure;

use RuntimeException;

class EnvironmentDetector
{
    public const string ENV_LOCAL = 'local';
    public const string ENV_PRODUCTION = 'production';
    public const string ENV_TEST = 'test';
    public const string ENV_ANALYZE = 'analyze';

    /**
     * @var string[]
     */
    private array $allowedEnvironments;

    /**
     * @param string[] $allowedEnvironments
     */
    public function __construct(
        array $allowedEnvironments = [
            self::ENV_LOCAL,
            self::ENV_PRODUCTION,
            self::ENV_TEST,
            self::ENV_ANALYZE,
        ]
    ) {
        $this->allowedEnvironments = array_values(
            array_unique(
                array_map(
                    static fn (string $environment): string => strtolower(trim($environment)),
                    $allowedEnvironments
                )
            )
        );
    }

    public function detect(): string
    {
        $environment = $_ENV['APP_ENV']
            ?? $_SERVER['APP_ENV']
            ?? getenv('APP_ENV');

        if (!is_string($environment) || trim($environment) === '') {
            throw new RuntimeException(
                'APP_ENV is not set. Set APP_ENV=local or APP_ENV=production in .env.setting.'
            );
        }

        $environment = strtolower(trim($environment));

        if (!in_array($environment, $this->allowedEnvironments, true)) {
            throw new RuntimeException(sprintf(
                'Unsupported APP_ENV "%s". Allowed values: %s.',
                $environment,
                implode(', ', $this->allowedEnvironments)
            ));
        }

        return $environment;
    }
}