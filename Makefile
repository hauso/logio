.PHONY: install up down shell wait-db migrate import test phpstan cs-check cs-fix

install:
	@if [ ! -f .env ]; then cp .env.example .env; fi
	docker compose up -d --build
	docker compose exec php composer install
	$(MAKE) wait-db
	docker compose exec php composer migrate
	@echo ""
	@echo "Next steps:"
	@echo "  make test        # run PHPUnit"
	@echo "  Adminer: http://localhost:8081"

up:
	docker compose up -d --build

down:
	docker compose down

shell:
	docker compose exec php sh

wait-db:
	@echo "Waiting for MariaDB..."
	@i=0; \
	until docker compose exec -T mysql mariadb-admin ping -h 127.0.0.1 -ulogio -plogio --silent >/dev/null 2>&1; do \
		i=$$((i + 1)); \
		if [ $$i -ge 30 ]; then \
			echo "MariaDB is not ready after 60 seconds."; \
			exit 1; \
		fi; \
		sleep 2; \
	done
	@echo "MariaDB is ready."

migrate:
	docker compose exec php composer migrate

test:
	docker compose exec php composer test

phpstan:
	docker compose exec php composer phpstan

phpcs:
	docker compose exec php composer phpcs
