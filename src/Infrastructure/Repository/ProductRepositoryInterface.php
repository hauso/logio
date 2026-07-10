<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

interface ProductRepositoryInterface
{
    /**
     * @return array<string, mixed>
     */
    public function findById(string $id): array;
}
