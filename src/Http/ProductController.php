<?php

declare(strict_types=1);

namespace App\Http;

use App\Application\ProductDetailService;

use const JSON_THROW_ON_ERROR;

final readonly class ProductController
{
    public function __construct(private ProductDetailService $productDetailService)
    {
    }

    public function detail(string $id): string
    {
        return json_encode($this->productDetailService->getProductDetail($id), JSON_THROW_ON_ERROR);
    }
}
