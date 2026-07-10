<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Cache;

use App\Infrastructure\Cache\ProductCacheFactory;
use App\Infrastructure\Cache\ProductCacheInterface;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class ProductCacheFactoryTest extends TestCase
{
    public function testCreatesFileCacheBackend(): void
    {
        $fileCache = new FactoryTestProductCache();
        $factory = new ProductCacheFactory($fileCache);

        self::assertSame($fileCache, $factory->create('file'));
    }

    public function testUnknownBackendThrowsException(): void
    {
        $factory = new ProductCacheFactory(new FactoryTestProductCache());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown product cache backend "redis".');

        $factory->create('redis');
    }
}

final class FactoryTestProductCache implements ProductCacheInterface
{
    /**
     * @return array<string, mixed>|null
     */
    public function get(string $productId): ?array
    {
        return null;
    }

    /**
     * @param array<string, mixed> $product
     */
    public function set(string $productId, array $product): void
    {
    }
}
