<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

enum ProductSourceEnum: string
{
    case ElasticSearch = 'elasticsearch';
    case MySQL = 'mysql';
}
