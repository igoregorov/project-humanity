<?php

declare(strict_types=1);

namespace Tests\Infrastructure;

use App\Infrastructure\ConfigLoader;
use PHPUnit\Framework\TestCase;

final class ConfigLoaderTest extends TestCase
{
    public function testResolveDatabaseIsActiveReadsUpperKey(): void
    {
        self::assertTrue(
            ConfigLoader::resolveDatabaseIsActive(['DB_IS_ACTIVE' => 'true'])
        );

        self::assertFalse(
            ConfigLoader::resolveDatabaseIsActive(['DB_IS_ACTIVE' => 'false'])
        );
    }

    public function testResolveDatabaseIsActiveReadsLowerKey(): void
    {
        self::assertTrue(
            ConfigLoader::resolveDatabaseIsActive(['db_is_active' => 'on'])
        );

        self::assertFalse(
            ConfigLoader::resolveDatabaseIsActive(['db_is_active' => 'off'])
        );
    }

    public function testResolveDatabaseIsActiveUsesDefaultWhenKeyMissing(): void
    {
        self::assertTrue(
            ConfigLoader::resolveDatabaseIsActive([])
        );

        self::assertFalse(
            ConfigLoader::resolveDatabaseIsActive([], false)
        );
    }

    public function testResolveDatabaseIsActiveFallsBackOnInvalidValue(): void
    {
        self::assertTrue(
            ConfigLoader::resolveDatabaseIsActive(['DB_IS_ACTIVE' => 'maybe'])
        );

        self::assertFalse(
            ConfigLoader::resolveDatabaseIsActive(['DB_IS_ACTIVE' => 'maybe'], false)
        );
    }

    public function testResolveDatabaseIsActivePrefersUpperKeyWhenBothPresent(): void
    {
        self::assertTrue(
            ConfigLoader::resolveDatabaseIsActive([
                'DB_IS_ACTIVE' => 'true',
                'db_is_active' => 'false',
            ])
        );
    }
}