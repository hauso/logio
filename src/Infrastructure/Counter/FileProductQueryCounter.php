<?php

declare(strict_types=1);

namespace App\Infrastructure\Counter;

use RuntimeException;

final readonly class FileProductQueryCounter implements ProductQueryCounterInterface
{
    private const DELIMITER = ',';
    private const ENCLOSURE = '"';
    private const ESCAPE = '';

    public function __construct(private string $counterFile)
    {
    }

    public function increment(string $productId): void
    {
        $this->ensureDirectoryExists();
        $handle = fopen($this->counterFile, 'c+');

        if ($handle === false) {
            throw new RuntimeException('Unable to open product counter file.');
        }

        try {
            if (!flock($handle, LOCK_EX)) {
                throw new RuntimeException('Unable to lock product counter file.');
            }

            $temporaryHandle = tmpfile();

            if ($temporaryHandle === false) {
                throw new RuntimeException('Unable to create temporary product counter file.');
            }

            try {
                $countWasIncremented = $this->writeIncrementedCounts($handle, $temporaryHandle, $productId);

                if (!$countWasIncremented) {
                    $this->writeCounterLine($temporaryHandle, $productId, 1);
                }

                $this->replaceCounterFileContents($handle, $temporaryHandle);
            } finally {
                fclose($temporaryHandle);
            }

            flock($handle, LOCK_UN);
        } finally {
            fclose($handle);
        }
    }

    public function get(string $productId): int
    {
        if (!is_file($this->counterFile)) {
            return 0;
        }

        $handle = fopen($this->counterFile, 'r');

        if ($handle === false) {
            return 0;
        }

        try {
            if (!flock($handle, LOCK_SH)) {
                return 0;
            }

            $count = $this->findCountInHandle($handle, $productId);
            flock($handle, LOCK_UN);
        } finally {
            fclose($handle);
        }

        return $count;
    }

    private function ensureDirectoryExists(): void
    {
        $directory = dirname($this->counterFile);

        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new RuntimeException('Unable to create product counter directory.');
        }
    }

    /**
     * @param resource $counterHandle
     * @param resource $temporaryHandle
     */
    private function writeIncrementedCounts($counterHandle, $temporaryHandle, string $searchedProductId): bool
    {
        $countWasIncremented = false;
        rewind($counterHandle);

        while (($counterLine = $this->readCounterLine($counterHandle)) !== null) {
            [$productId, $count] = $counterLine;

            if ($productId === $searchedProductId) {
                ++$count;
                $countWasIncremented = true;
            }

            $this->writeCounterLine($temporaryHandle, $productId, $count);
        }

        return $countWasIncremented;
    }

    /**
     * @param resource $counterHandle
     */
    private function findCountInHandle($counterHandle, string $searchedProductId): int
    {
        rewind($counterHandle);

        while (($counterLine = $this->readCounterLine($counterHandle)) !== null) {
            [$productId, $count] = $counterLine;

            if ($productId === $searchedProductId) {
                return $count;
            }
        }

        return 0;
    }

    /**
     * @param resource $handle
     * @return array{string, int}|null
     */
    private function readCounterLine($handle): ?array
    {
        while (($row = fgetcsv($handle, null, self::DELIMITER, self::ENCLOSURE, self::ESCAPE)) !== false) {
            if (count($row) !== 2) {
                continue;
            }

            [$productId, $count] = $row;

            if (!is_string($productId) || $productId === '' || !is_string($count) || !ctype_digit($count)) {
                continue;
            }

            return [$productId, (int) $count];
        }

        return null;
    }

    /**
     * @param resource $handle
     */
    private function writeCounterLine($handle, string $productId, int $count): void
    {
        $bytesWritten = fputcsv($handle, [$productId, (string) $count], self::DELIMITER, self::ENCLOSURE, self::ESCAPE);

        if ($bytesWritten === false) {
            throw new RuntimeException('Unable to write product counter file.');
        }
    }

    /**
     * @param resource $counterHandle
     * @param resource $temporaryHandle
     */
    private function replaceCounterFileContents($counterHandle, $temporaryHandle): void
    {
        rewind($temporaryHandle);
        rewind($counterHandle);

        if (!ftruncate($counterHandle, 0)) {
            throw new RuntimeException('Unable to truncate product counter file.');
        }

        if (stream_copy_to_stream($temporaryHandle, $counterHandle) === false) {
            throw new RuntimeException('Unable to replace product counter file contents.');
        }

        fflush($counterHandle);
    }
}
