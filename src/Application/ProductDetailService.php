<?php

declare(strict_types=1);

namespace App\Application;

use App\Infrastructure\Cache\ProductCacheInterface;
use App\Infrastructure\Counter\ProductQueryCounterInterface;
use App\Infrastructure\Repository\ProductRepositoryInterface;

final readonly class ProductDetailService
{
    public function __construct(
        private ProductCacheInterface $cache,
        private ProductRepositoryInterface $repository,
        private ProductQueryCounterInterface $counter,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function getProductDetail(string $id): array
    {
        $product = $this->cache->get($id);

        if ($product === null) {
            $product = $this->repository->findById($id);
            $this->cache->set($id, $product);
        }

        $this->counter->increment($id);

        return $product;
    }
}
