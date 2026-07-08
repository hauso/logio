# Logio Product Information Search

Framework-less PHP 8.4 application skeleton for the „Vyhledání informací o produktu“ assignment.

## Docker setup

The local development environment contains PHP-FPM with Composer, Nginx, MySQL 8, and Elasticsearch 8. All services, named volumes, and the bridge network use the `logio-` prefix.

### Services

- `logio-php` — PHP 8.4 FPM container with Composer and required PHP extensions.
- `logio-nginx` — HTTP entrypoint on <http://localhost:8080> with document root `public/`.
- `logio-mysql` — MySQL 8 available on host port `33061`.
- `logio-elasticsearch` — Elasticsearch 8 single-node instance available on host port `9200`. Elasticsearch can take a short while to become ready after the containers start.

### Useful commands

```bash
docker compose up -d --build
docker compose exec logio-php composer install
docker compose exec logio-php composer check
docker compose exec logio-php composer test
docker compose exec logio-php composer phpstan
docker compose exec logio-php composer phpcs
docker compose exec logio-php vendor/bin/phpunit
docker compose exec logio-php vendor/bin/phpstan analyse
docker compose exec logio-php vendor/bin/phpcs
```

### Linux file ownership

If `vendor/` ownership causes permission issues on Linux, run Composer with your host UID/GID:

```bash
docker compose exec --user "$(id -u):$(id -g)" logio-php composer install
```
