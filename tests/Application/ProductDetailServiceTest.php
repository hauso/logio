<?php

declare(strict_types=1);

namespace App\Tests\Application;

use App\Infrastructure\Cache\ProductCacheInterface;
use App\Infrastructure\Counter\ProductQueryCounterInterface;
use App\Infrastructure\Repository\ProductRepositoryInterface;
use App\Application\ProductDetailService;
use PHPUnit\Framework\TestCase;

final class ProductDetailServiceTest extends TestCase
{
    public function testCacheHitDoesNotCallRepositoryIncrementsCounterAndReturnsCachedProduct(): void
    {
        $cache = new InMemoryProductCache(['123' => ['id' => '123', 'source' => 'cache']]);
        $repository = new CountingProductRepository(['id' => '123', 'source' => 'repository']);
        $counter = new InMemoryProductQueryCounter();
        $service = new ProductDetailService($cache, $repository, $counter);

        self::assertSame(['id' => '123', 'source' => 'cache'], $service->getProductDetail('123'));
        self::assertSame(0, $repository->calls);
        self::assertSame(1, $counter->get('123'));
    }

    public function testCacheMissCallsRepositoryStoresProductIncrementsCounterAndReturnsRepositoryProduct(): void
    {
        $cache = new InMemoryProductCache();
        $repository = new CountingProductRepository(['id' => '123', 'source' => 'repository']);
        $counter = new InMemoryProductQueryCounter();
        $service = new ProductDetailService($cache, $repository, $counter);

        self::assertSame(['id' => '123', 'source' => 'repository'], $service->getProductDetail('123'));
        self::assertSame(1, $repository->calls);
        self::assertSame(['id' => '123', 'source' => 'repository'], $cache->get('123'));
        self::assertSame(1, $counter->get('123'));
    }
}

final class InMemoryProductCache implements ProductCacheInterface
{
    /**
     * @param array<array-key, array<string, mixed>> $products
     */
    public function __construct(private array $products = [])
    {
    }

    /**
     * @return array<string, mixed>|null
     */
    public function get(string $productId): ?array
    {
        return $this->products[$productId] ?? null;
    }

    /**
     * @param array<string, mixed> $product
     */
    public function set(string $productId, array $product): void
    {
        $this->products[$productId] = $product;
    }
}

final class CountingProductRepository implements ProductRepositoryInterface
{
    public int $calls = 0;

    /**
     * @param array<string, mixed> $product
     */
    public function __construct(private readonly array $product)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function findById(string $id): array
    {
        $this->calls++;

        return $this->product;
    }
}

final class InMemoryProductQueryCounter implements ProductQueryCounterInterface
{
    /** @var array<string, int> */
    private array $counts = [];

    public function increment(string $productId): void
    {
        $this->counts[$productId] = ($this->counts[$productId] ?? 0) + 1;
    }

    public function get(string $productId): int
    {
        return $this->counts[$productId] ?? 0;
    }
}
