<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

use Logio\Driver\IElasticSearchDriver;

final readonly class ElasticSearchProductRepository implements ProductRepositoryInterface
{
    public function __construct(private IElasticSearchDriver $elasticSearchDriver)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function findById(string $id): array
    {
        /** @var array<string, mixed> $product */
        $product = $this->elasticSearchDriver->findById($id);

        return $product;
    }
}
