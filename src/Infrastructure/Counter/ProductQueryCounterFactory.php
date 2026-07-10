<?php

declare(strict_types=1);

namespace App\Infrastructure\Counter;

use InvalidArgumentException;

final readonly class ProductQueryCounterFactory
{
    public function __construct(private ProductQueryCounterInterface $fileProductQueryCounter)
    {
    }

    public function create(string $counterEnum): ProductQueryCounterInterface
    {
        return match (ProductQueryCounterEnum::tryFrom($counterEnum)) {
            ProductQueryCounterEnum::File => $this->fileProductQueryCounter,
            default => throw new InvalidArgumentException(
                sprintf('Unknown product counter backend "%s".', $counterEnum),
            ),
        };
    }
}
