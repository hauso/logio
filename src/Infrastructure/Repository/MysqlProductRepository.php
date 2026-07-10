<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

use Logio\Driver\IMySQLDriver;

final readonly class MysqlProductRepository implements ProductRepositoryInterface
{
    public function __construct(private IMySQLDriver $mysqlDriver)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function findById(string $id): array
    {
        /** @var array<string, mixed> $product */
        $product = $this->mysqlDriver->findProduct($id);

        return $product;
    }
}
