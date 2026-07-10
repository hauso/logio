<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Repository;

use App\Infrastructure\Repository\ElasticSearchProductRepository;
use Logio\Driver\IElasticSearchDriver;
use PHPUnit\Framework\TestCase;

final class ElasticSearchProductRepositoryTest extends TestCase
{
    public function testRepositoryCallsElasticSearchDriverFindById(): void
    {
        $driver = new ElasticSearchTestDriver('elasticsearch');
        $repository = new ElasticSearchProductRepository($driver);

        self::assertSame(['id' => '123', 'source' => 'elasticsearch'], $repository->findById('123'));
        self::assertSame(['123'], $driver->calledWithIds);
    }
}

final class ElasticSearchTestDriver implements IElasticSearchDriver
{
    /** @var list<string> */
    public array $calledWithIds = [];

    public function __construct(private readonly string $source)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function findById(string $id): array
    {
        $this->calledWithIds[] = $id;

        return ['id' => $id, 'source' => $this->source];
    }
}
