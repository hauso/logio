<?php

declare(strict_types=1);

namespace App\Infrastructure\Cache;

use InvalidArgumentException;

final readonly class ProductCacheFactory
{
    public function __construct(private ProductCacheInterface $fileProductCache)
    {
    }

    public function create(string $cacheEnum): ProductCacheInterface
    {
        return match (ProductCacheEnum::tryFrom($cacheEnum)) {
            ProductCacheEnum::File => $this->fileProductCache,
            default => throw new InvalidArgumentException(sprintf('Unknown product cache backend "%s".', $cacheEnum)),
        };
    }
}
