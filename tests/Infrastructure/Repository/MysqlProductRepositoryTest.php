<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Repository;

use App\Infrastructure\Repository\MysqlProductRepository;
use Logio\Driver\IMySQLDriver;
use PHPUnit\Framework\TestCase;

final class MysqlProductRepositoryTest extends TestCase
{
    public function testRepositoryCallsMysqlDriverFindProduct(): void
    {
        $driver = new MysqlTestDriver('mysql');
        $repository = new MysqlProductRepository($driver);

        self::assertSame(['id' => '456', 'source' => 'mysql'], $repository->findById('456'));
        self::assertSame(['456'], $driver->calledWithIds);
    }
}

final class MysqlTestDriver implements IMySQLDriver
{
    /** @var list<string> */
    public array $calledWithIds = [];

    public function __construct(private readonly string $source)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function findProduct(string $id): array
    {
        $this->calledWithIds[] = $id;

        return ['id' => $id, 'source' => $this->source];
    }
}
