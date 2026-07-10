# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Overview

Framework-less PHP 8.4 application for the "Vyhledání informací o produktu" (product information search) assignment. No framework, no DI container — the object graph is wired by hand in `public/index.php`. Application code is under the `App\` namespace (PSR-4: `src/` → `App\`, `tests/` → `App\Tests\`).

## Commands

Everything runs inside the `php` container (which runs as **root**, so it can write repo files regardless of host ownership). `make install` copies `.env.example` → `.env`, builds the stack, runs `composer install`, and waits for the DB.

```bash
make install                    # first-time setup
make test                       # composer test  → phpunit
make stan                       # composer phpstan → phpstan analyse (level 8)
make style                      # composer phpcs
make style-fix                  # composer phpcbf
make check                      # test + stan + style  (Makefile target; composer check is separate but equivalent)

# Single test / one method:
docker compose exec php vendor/bin/phpunit tests/Application/ProductDetailServiceTest.php
docker compose exec php vendor/bin/phpunit --filter testCacheHitDoesNotCallRepository

# Auto-fix coding-standard violations:
docker compose exec php vendor/bin/phpcbf
```

Service/container names: `php`, `nginx`, `mysql`, `adminer`, `elasticsearch`. App at http://localhost:8080, Adminer at http://localhost:8081, MySQL host port `33061`, Elasticsearch `9200`.

**After changing namespaces or autoload config, run `composer dump-autoload`** — a stale autoloader shows up as `Interface/Class "…" not found` at runtime.

## Architecture

The code is organised in **layers** (namespace mirrors directory):

```
src/
  Http/            ProductController                       (App\Http)
  Application/     ProductDetailService                    (App\Application)
  Infrastructure/  Cache/ Counter/ Repository/             (App\Infrastructure\*)  — outbound adapters
  Config/          AppConfig                               (App\Config)
```

Dependencies point inward: `Http` → `Application` → interfaces implemented by `Infrastructure`. Keep each layer's concern where it is — e.g. new storage backends are `Infrastructure`, request handling is `Http`.

Single entrypoint `public/index.php` does manual routing (`/`, `/health`, `/products/{id}`) and builds the graph by hand per request:

```
ProductController → ProductDetailService → { ProductCache, ProductRepository, ProductQueryCounter }
```

`ProductDetailService::getProductDetail()` is the core orchestration and defines the contract: **cache hit → return; on miss → fetch via repository and populate cache; always increment the counter; return the product.** Any change to lookup/caching/counting semantics belongs here.

Each collaborator sits behind an interface (swappable + mockable) and is selected by a factory + string-backed enum, so the backend is chosen purely by configuration — exactly what the assignment asks for ("switch technology by config"):

- **Repository** (`src/Infrastructure/Repository/`) — the data source. `ProductRepositoryFactory` maps `PRODUCT_SOURCE` via `ProductSourceEnum` to `ElasticSearchProductRepository` (calls driver `findById()`) or `MysqlProductRepository` (calls driver `findProduct()`); unknown value throws. Each repository wraps an injected driver.
- **Cache** (`src/Infrastructure/Cache/FileProductCache`) — one file per product under `PRODUCT_CACHE_DIR`, name `sha256(id).cache`, payload PHP-`serialize`d. Atomic writes (temp file + `rename`); a corrupt file degrades to a miss (`@unserialize` + `=== false` guard), never throws. `ProductCacheFactory` + `ProductCacheEnum` select the backend (`file`).
- **Counter** (`src/Infrastructure/Counter/FileProductQueryCounter`) — a CSV file at `PRODUCT_COUNTER_FILE` of `id,count` rows, mutated under an exclusive `flock` (read-modify-write via a temp handle). `ProductQueryCounterFactory` + `ProductQueryCounterEnum` select the backend.

**Config** (`src/Config/AppConfig::fromEnvironment()`) is the only place env vars are read; relative paths resolve against the project root. Vars: `PRODUCT_SOURCE`, `PRODUCT_CACHE_BACKEND`, `PRODUCT_COUNTER_BACKEND`, `PRODUCT_CACHE_DIR`, `PRODUCT_COUNTER_FILE` (see `.env.example`).

### Imaginary drivers (`Logio\Driver\*`) — important

The ES/MySQL drivers are **external interfaces the host framework provides at runtime** (`Logio\Driver\IElasticSearchDriver::findById()`, `Logio\Driver\IMySQLDriver::findProduct()`), per the assignment — they are **not** part of `src/`. They are declared once in `stubs/drivers.php` and wired in for tooling only:

- **PHPStan**: `scanFiles: - stubs/drivers.php` in `phpstan.neon` (scanned for symbols, not analysed).
- **Tests**: `autoload-dev.classmap: ["stubs/"]` in `composer.json` so PHPUnit can load them.

They are intentionally absent from the production autoloader. Do not move them into `src/`.

## Conventions

Enforced by phpcs (`phpcs.xml`) and phpstan level 8 — all three of test/stan/style must stay green:

- `declare(strict_types=1);` in every file (Slevomat, **no spaces** around `=`).
- PSR-12 + Slevomat: full parameter, return, and property type hints required.
- Domain classes are `final readonly`, dependencies constructor-promoted and typed against interfaces.
- Test doubles live in the same file as the test that uses them — `PSR1…MultipleClasses` is excluded for `tests/*` in `phpcs.xml`.

## Notes

- Runtime state lives under `var/` (`var/cache/products`, `var/storage/counters`, `var/cache/phpunit`); directories are created on demand.
