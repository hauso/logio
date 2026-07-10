<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

use InvalidArgumentException;

final readonly class ProductRepositoryFactory
{
    public function __construct(
        private ProductRepositoryInterface $elasticSearchRepository,
        private ProductRepositoryInterface $mysqlRepository,
    ) {
    }

    public function create(string $productSource): ProductRepositoryInterface
    {
        return match (ProductSourceEnum::tryFrom($productSource)) {
            ProductSourceEnum::ElasticSearch => $this->elasticSearchRepository,
            ProductSourceEnum::MySQL => $this->mysqlRepository,
            default => throw new InvalidArgumentException(sprintf('Unknown product source "%s".', $productSource)),
        };
    }
}
