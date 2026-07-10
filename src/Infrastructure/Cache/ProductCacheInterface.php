<?php

declare(strict_types=1);

namespace App\Infrastructure\Cache;

interface ProductCacheInterface
{
    /**
     * @return array<string, mixed>|null
     */
    public function get(string $productId): ?array;

    /**
     * @param array<string, mixed> $product
     */
    public function set(string $productId, array $product): void;
}
