<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Repository;

use App\Infrastructure\Repository\ProductRepositoryFactory;
use App\Infrastructure\Repository\ProductRepositoryInterface;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class ProductRepositoryFactoryTest extends TestCase
{
    public function testFactorySelectsElasticSearchRepository(): void
    {
        $elasticSearchRepository = new TestProductRepository('elasticsearch');
        $mysqlRepository = new TestProductRepository('mysql');
        $factory = new ProductRepositoryFactory($elasticSearchRepository, $mysqlRepository);

        self::assertSame($elasticSearchRepository, $factory->create('elasticsearch'));
    }

    public function testFactorySelectsMysqlRepository(): void
    {
        $elasticSearchRepository = new TestProductRepository('elasticsearch');
        $mysqlRepository = new TestProductRepository('mysql');
        $factory = new ProductRepositoryFactory($elasticSearchRepository, $mysqlRepository);

        self::assertSame($mysqlRepository, $factory->create('mysql'));
    }

    public function testFactoryRejectsUnknownProductSource(): void
    {
        $factory = new ProductRepositoryFactory(
            new TestProductRepository('elasticsearch'),
            new TestProductRepository('mysql'),
        );

        $this->expectException(InvalidArgumentException::class);

        $factory->create('unknown');
    }
}

final readonly class TestProductRepository implements ProductRepositoryInterface
{
    public function __construct(private string $source)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function findById(string $id): array
    {
        return ['id' => $id, 'source' => $this->source];
    }
}
