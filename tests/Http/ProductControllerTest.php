<?php

declare(strict_types=1);

namespace App\Tests\Http;

use App\Application\ProductDetailService;
use App\Http\ProductController;
use App\Infrastructure\Cache\ProductCacheInterface;
use App\Infrastructure\Counter\ProductQueryCounterInterface;
use App\Infrastructure\Repository\ProductRepositoryInterface;
use PHPUnit\Framework\TestCase;

final class ProductControllerTest extends TestCase
{
    public function testDetailReturnsValidJsonWithExpectedProductData(): void
    {
        $controller = new ProductController(
            new ProductDetailService(
                new ControllerTestProductCache(),
                new ControllerTestProductRepository(),
                new ControllerTestProductQueryCounter(),
            ),
        );

        $decodedJson = json_decode($controller->detail('123'), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame([
            'id' => '123',
            'name' => 'Demo product 123',
            'source' => 'test',
        ], $decodedJson);
    }
}

final class ControllerTestProductCache implements ProductCacheInterface
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

final class ControllerTestProductRepository implements ProductRepositoryInterface
{
    /**
     * @return array<string, mixed>
     */
    public function findById(string $id): array
    {
        return [
            'id' => $id,
            'name' => 'Demo product ' . $id,
            'source' => 'test',
        ];
    }
}

final class ControllerTestProductQueryCounter implements ProductQueryCounterInterface
{
    public function increment(string $productId): void
    {
    }

    public function get(string $productId): int
    {
        return 0;
    }
}
