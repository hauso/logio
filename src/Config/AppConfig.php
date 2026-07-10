<?php

declare(strict_types=1);

namespace App\Config;

use App\Infrastructure\Cache\ProductCacheEnum;
use App\Infrastructure\Counter\ProductQueryCounterEnum;
use App\Infrastructure\Repository\ProductSourceEnum;

final readonly class AppConfig
{
    public function __construct(
        private string $productSourceEnum,
        private string $productCacheEnum,
        private string $productCounterEnum,
        private string $productCacheDir,
        private string $productCounterFile,
    ) {
    }

    public static function fromEnvironment(string $projectRoot): self
    {
        return new self(
            self::getEnvironmentValue('PRODUCT_SOURCE', ProductSourceEnum::ElasticSearch->value),
            self::getEnvironmentValue('PRODUCT_CACHE_BACKEND', ProductCacheEnum::File->value),
            self::getEnvironmentValue('PRODUCT_COUNTER_BACKEND', ProductQueryCounterEnum::File->value),
            self::resolvePath(
                $projectRoot,
                self::getEnvironmentValue('PRODUCT_CACHE_DIR', 'var/cache/products'),
            ),
            self::resolvePath(
                $projectRoot,
                self::getEnvironmentValue('PRODUCT_COUNTER_FILE', 'var/storage/counters/product-query-counts.csv'),
            ),
        );
    }

    public function getProductSourceEnum(): string
    {
        return $this->productSourceEnum;
    }

    public function getProductCacheDir(): string
    {
        return $this->productCacheDir;
    }

    public function getProductCacheEnum(): string
    {
        return $this->productCacheEnum;
    }

    public function getProductCounterEnum(): string
    {
        return $this->productCounterEnum;
    }

    public function getProductCounterFile(): string
    {
        return $this->productCounterFile;
    }

    private static function resolvePath(string $projectRoot, string $path): string
    {
        if (str_starts_with($path, '/')) {
            return $path;
        }

        return $projectRoot . '/' . $path;
    }

    private static function getEnvironmentValue(string $name, string $default): string
    {
        $value = getenv($name);

        if ($value === false || $value === '') {
            return $default;
        }

        return $value;
    }
}
