<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Counter;

use App\Infrastructure\Counter\ProductQueryCounterFactory;
use App\Infrastructure\Counter\ProductQueryCounterInterface;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class ProductQueryCounterFactoryTest extends TestCase
{
    public function testCreatesFileCounterBackend(): void
    {
        $fileCounter = new FactoryTestProductQueryCounter();
        $factory = new ProductQueryCounterFactory($fileCounter);

        self::assertSame($fileCounter, $factory->create('file'));
    }

    public function testUnknownBackendThrowsException(): void
    {
        $factory = new ProductQueryCounterFactory(new FactoryTestProductQueryCounter());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown product counter backend "redis".');

        $factory->create('redis');
    }
}

final class FactoryTestProductQueryCounter implements ProductQueryCounterInterface
{
    public function increment(string $productId): void
    {
    }

    public function get(string $productId): int
    {
        return 0;
    }
}
