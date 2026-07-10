<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Cache;

use App\Infrastructure\Cache\FileProductCache;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class FileProductCacheTest extends TestCase
{
    public function testCacheMissReturnsNull(): void
    {
        self::assertNull($this->createCache()->get('missing'));
    }

    public function testSetAndGetReturnsSameData(): void
    {
        $cache = $this->createCache();
        $product = ['id' => '123', 'name' => 'Demo product 123', 'nested' => ['available' => true]];

        $cache->set('123', $product);

        self::assertSame($product, $cache->get('123'));
    }

    public function testSetCreatesCacheDirectoryWhenMissing(): void
    {
        $directory = sys_get_temp_dir() . '/product-cache-' . uniqid('', true) . '/nested';
        $cache = new FileProductCache($directory);

        self::assertDirectoryDoesNotExist($directory);

        $cache->set('123', ['id' => '123']);

        self::assertDirectoryExists($directory);
        self::assertSame(['id' => '123'], $cache->get('123'));
    }

    public function testBrokenSerializedCacheReturnsNull(): void
    {
        $directory = $this->createTemporaryDirectory();
        $cache = new FileProductCache($directory);
        file_put_contents($this->cacheFilePath($directory, '123'), 'broken serialized payload');

        self::assertNull($cache->get('123'));
    }

    public function testNonArraySerializedPayloadReturnsNull(): void
    {
        $directory = $this->createTemporaryDirectory();
        $cache = new FileProductCache($directory);
        file_put_contents($this->cacheFilePath($directory, '123'), serialize('not-an-array'));

        self::assertNull($cache->get('123'));
    }

    public function testSetThrowsWhenCacheDirectoryCannotBeCreated(): void
    {
        $blockingFile = (string) tempnam(sys_get_temp_dir(), 'product-cache-block');
        $cache = new FileProductCache($blockingFile . '/unreachable');

        set_error_handler(static fn (): bool => true);

        try {
            $this->expectException(RuntimeException::class);
            $this->expectExceptionMessage('Unable to create product cache directory.');

            $cache->set('123', ['id' => '123']);
        } finally {
            restore_error_handler();
            unlink($blockingFile);
        }
    }

    private function createCache(): FileProductCache
    {
        return new FileProductCache($this->createTemporaryDirectory());
    }

    private function createTemporaryDirectory(): string
    {
        $directory = sys_get_temp_dir() . '/product-cache-' . uniqid('', true);
        mkdir($directory, 0775, true);

        return $directory;
    }

    private function cacheFilePath(string $directory, string $productId): string
    {
        return sprintf('%s/%s.cache', $directory, hash('sha256', $productId));
    }
}
