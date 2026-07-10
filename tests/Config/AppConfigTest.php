<?php

declare(strict_types=1);

namespace App\Tests\Config;

use App\Config\AppConfig;
use PHPUnit\Framework\TestCase;

final class AppConfigTest extends TestCase
{
    private const ENV_VARS = [
        'PRODUCT_SOURCE',
        'PRODUCT_CACHE_BACKEND',
        'PRODUCT_COUNTER_BACKEND',
        'PRODUCT_CACHE_DIR',
        'PRODUCT_COUNTER_FILE',
    ];

    protected function setUp(): void
    {
        $this->clearEnvironment();
    }

    protected function tearDown(): void
    {
        $this->clearEnvironment();
    }

    public function testUsesDefaultsWhenEnvironmentIsNotSet(): void
    {
        $config = AppConfig::fromEnvironment('/project');

        self::assertSame('elasticsearch', $config->getProductSourceEnum());
        self::assertSame('file', $config->getProductCacheEnum());
        self::assertSame('file', $config->getProductCounterEnum());
        self::assertSame('/project/var/cache/products', $config->getProductCacheDir());
        self::assertSame(
            '/project/var/storage/counters/product-query-counts.csv',
            $config->getProductCounterFile(),
        );
    }

    public function testEnvironmentValuesOverrideDefaultsAndRelativePathsResolveAgainstRoot(): void
    {
        putenv('PRODUCT_SOURCE=mysql');
        putenv('PRODUCT_CACHE_BACKEND=redis');
        putenv('PRODUCT_COUNTER_BACKEND=redis');
        putenv('PRODUCT_CACHE_DIR=custom/cache');
        putenv('PRODUCT_COUNTER_FILE=custom/counts.csv');

        $config = AppConfig::fromEnvironment('/project');

        self::assertSame('mysql', $config->getProductSourceEnum());
        self::assertSame('redis', $config->getProductCacheEnum());
        self::assertSame('redis', $config->getProductCounterEnum());
        self::assertSame('/project/custom/cache', $config->getProductCacheDir());
        self::assertSame('/project/custom/counts.csv', $config->getProductCounterFile());
    }

    public function testAbsolutePathsAreUsedAsIs(): void
    {
        putenv('PRODUCT_CACHE_DIR=/absolute/cache');
        putenv('PRODUCT_COUNTER_FILE=/absolute/counts.csv');

        $config = AppConfig::fromEnvironment('/project');

        self::assertSame('/absolute/cache', $config->getProductCacheDir());
        self::assertSame('/absolute/counts.csv', $config->getProductCounterFile());
    }

    public function testEmptyEnvironmentValueFallsBackToDefault(): void
    {
        putenv('PRODUCT_SOURCE=');

        self::assertSame('elasticsearch', AppConfig::fromEnvironment('/project')->getProductSourceEnum());
    }

    private function clearEnvironment(): void
    {
        foreach (self::ENV_VARS as $name) {
            putenv($name);
        }
    }
}
