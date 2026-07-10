<?php

declare(strict_types=1);

namespace Logio\Driver;

/**
 * External driver contract provided by the host framework at runtime.
 * Declared here only so static analysis knows its shape; not shipped in src/.
 */
interface IElasticSearchDriver
{
    /**
     * @return array<string, mixed>
     */
    public function findById(string $id): array;
}

/**
 * External driver contract provided by the host framework at runtime.
 * Declared here only so static analysis knows its shape; not shipped in src/.
 */
interface IMySQLDriver
{
    /**
     * @return array<string, mixed>
     */
    public function findProduct(string $id): array;
}
