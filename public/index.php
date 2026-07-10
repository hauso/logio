<?php

declare(strict_types=1);

use App\Infrastructure\Cache\FileProductCache;
use App\Infrastructure\Cache\ProductCacheFactory;
use App\Config\AppConfig;
use App\Http\ProductController;
use App\Infrastructure\Counter\FileProductQueryCounter;
use App\Infrastructure\Counter\ProductQueryCounterFactory;
use App\Infrastructure\Repository\ElasticSearchProductRepository;
use App\Infrastructure\Repository\MysqlProductRepository;
use App\Infrastructure\Repository\ProductRepositoryFactory;
use App\Application\ProductDetailService;
use Logio\Driver\IElasticSearchDriver;
use Logio\Driver\IMySQLDriver;

$projectRoot = dirname(__DIR__);

require $projectRoot . '/vendor/autoload.php';

header('Content-Type: application/json');

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

if ($path === '/' || $path === '/health') {
    echo json_encode(['status' => 'ok'], JSON_THROW_ON_ERROR);
    return;
}

if (preg_match('#^/products/([^/]+)$#', $path, $matches) === 1) {
    $appConfig = AppConfig::fromEnvironment($projectRoot);
    $elasticSearchDriver = new class implements IElasticSearchDriver {
        /**
         * @return array<string, mixed>
         */
        public function findById(string $id): array
        {
            return [
                'id' => $id,
                'name' => 'Demo product ' . $id,
                'source' => 'elasticsearch-dev-stub',
            ];
        }
    };
    $mysqlDriver = new class implements IMySQLDriver {
        /**
         * @return array<string, mixed>
         */
        public function findProduct(string $id): array
        {
            return [
                'id' => $id,
                'name' => 'Demo product ' . $id,
                'source' => 'mysql-dev-stub',
            ];
        }
    };
    $repositoryFactory = new ProductRepositoryFactory(
        new ElasticSearchProductRepository($elasticSearchDriver),
        new MysqlProductRepository($mysqlDriver),
    );
    $service = new ProductDetailService(
        new ProductCacheFactory(
            new FileProductCache($appConfig->getProductCacheDir()),
        )->create($appConfig->getProductCacheEnum()),
        $repositoryFactory->create($appConfig->getProductSourceEnum()),
        new ProductQueryCounterFactory(
            new FileProductQueryCounter($appConfig->getProductCounterFile()),
        )->create($appConfig->getProductCounterEnum()),
    );
    $controller = new ProductController($service);

    try {
        echo $controller->detail(urldecode($matches[1]));
    } catch (Throwable $ex) {
        error_log((string) $ex);
        http_response_code(500);
        echo json_encode(['error' => 'Internal Server Error'], JSON_THROW_ON_ERROR);
    }

    return;
}

http_response_code(404);
echo json_encode(['error' => 'Not found'], JSON_THROW_ON_ERROR);
