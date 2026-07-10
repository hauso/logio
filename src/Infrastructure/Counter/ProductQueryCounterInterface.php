<?php

declare(strict_types=1);

namespace App\Infrastructure\Counter;

interface ProductQueryCounterInterface
{
    public function increment(string $productId): void;

    public function get(string $productId): int;
}
