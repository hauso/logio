<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Counter;

use App\Infrastructure\Counter\FileProductQueryCounter;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class FileProductQueryCounterTest extends TestCase
{
    public function testFirstIncrementSetsCountToOne(): void
    {
        $counter = $this->createCounter();
        $counter->increment('123');

        self::assertSame(1, $counter->get('123'));
    }

    public function testSecondIncrementSetsCountToTwo(): void
    {
        $counter = $this->createCounter();
        $counter->increment('123');
        $counter->increment('123');

        self::assertSame(2, $counter->get('123'));
    }

    public function testMissingProductIdHasCountZero(): void
    {
        self::assertSame(0, $this->createCounter()->get('missing'));
    }

    public function testDifferentProductIdsHaveSeparatedValues(): void
    {
        $counter = $this->createCounter();
        $counter->increment('123');
        $counter->increment('456');
        $counter->increment('456');

        self::assertSame(1, $counter->get('123'));
        self::assertSame(2, $counter->get('456'));
    }

    public function testCounterFileStoresPlainProductIdAndCountPerCsvRecordOutsideJson(): void
    {
        $counterFile = sys_get_temp_dir() . '/counter-' . uniqid('', true) . '/product-query-counts.csv';
        $counter = new FileProductQueryCounter($counterFile);

        $counter->increment('123');
        $counter->increment('123');
        $counter->increment('id-with-comma,inside');

        $contents = file_get_contents($counterFile);

        self::assertIsString($contents);
        self::assertFalse(str_starts_with(trim($contents), '{'));
        self::assertStringContainsString('123,2', $contents);
        self::assertStringContainsString('"id-with-comma,inside",1', $contents);
        self::assertSame(2, $counter->get('123'));
        self::assertSame(1, $counter->get('id-with-comma,inside'));
    }

    public function testMalformedCounterLinesAreIgnored(): void
    {
        $counterFile = $this->createCounterFilePath();
        mkdir(dirname($counterFile), 0775, true);
        file_put_contents($counterFile, "incomplete-line\n\"123\",5\n\"bad\",notanumber\n");

        $counter = new FileProductQueryCounter($counterFile);

        self::assertSame(5, $counter->get('123'));
        self::assertSame(0, $counter->get('bad'));
    }

    public function testIncrementThrowsWhenCounterDirectoryCannotBeCreated(): void
    {
        $blockingFile = (string) tempnam(sys_get_temp_dir(), 'counter-block');
        $counter = new FileProductQueryCounter($blockingFile . '/unreachable/product-query-counts.csv');

        set_error_handler(static fn (): bool => true);

        try {
            $this->expectException(RuntimeException::class);
            $this->expectExceptionMessage('Unable to create product counter directory.');

            $counter->increment('123');
        } finally {
            restore_error_handler();
            unlink($blockingFile);
        }
    }

    private function createCounter(): FileProductQueryCounter
    {
        return new FileProductQueryCounter($this->createCounterFilePath());
    }

    private function createCounterFilePath(): string
    {
        return sys_get_temp_dir() . '/counter-' . uniqid('', true) . '/product-query-counts.csv';
    }
}
