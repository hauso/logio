<?php

declare(strict_types=1);

namespace App\Infrastructure\Cache;

use RuntimeException;

final readonly class FileProductCache implements ProductCacheInterface
{
    public function __construct(private string $cacheDir)
    {
    }

    /**
     * @return array<string, mixed>|null
     */
    public function get(string $productId): ?array
    {
        $filePath = $this->getFilePath($productId);

        if (!file_exists($filePath)) {
            return null;
        }

        $contents = file_get_contents($filePath);

        if ($contents === false) {
            return null;
        }

        $product = @unserialize($contents, ['allowed_classes' => false]);

        if ($product === false && $contents !== serialize(false)) {
            return null;
        }

        if (!is_array($product)) {
            return null;
        }

        /** @var array<string, mixed> $product */
        return $product;
    }

    /**
     * @param array<string, mixed> $product
     */
    public function set(string $productId, array $product): void
    {
        if (!is_dir($this->cacheDir) && !mkdir($this->cacheDir, 0775, true) && !is_dir($this->cacheDir)) {
            throw new RuntimeException('Unable to create product cache directory.');
        }

        $temporaryFile = tempnam($this->cacheDir, 'product-cache-');

        if ($temporaryFile === false) {
            throw new RuntimeException('Unable to create temporary cache file.');
        }

        try {
            $bytesWritten = file_put_contents($temporaryFile, serialize($product), LOCK_EX);

            if ($bytesWritten === false) {
                throw new RuntimeException('Unable to write product cache file.');
            }

            if (!rename($temporaryFile, $this->getFilePath($productId))) {
                throw new RuntimeException('Unable to move product cache file into place.');
            }
        } finally {
            if (file_exists($temporaryFile)) {
                unlink($temporaryFile);
            }
        }
    }

    private function getFilePath(string $productId): string
    {
        return sprintf('%s/%s.cache', $this->cacheDir, hash('sha256', $productId));
    }
}
