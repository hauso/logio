.PHONY: install up down shell wait-db import test stan style

install:
	@if [ ! -f .env ]; then cp .env.example .env; fi
	docker compose up -d --build
	docker compose exec php composer install
	$(MAKE) wait-db
	@echo ""
	@echo "Next steps:"
	@echo "  make test        # run PHPUnit"
	@echo "  Adminer: http://localhost:8081"

wait-db:
	@echo "Waiting for MySQL..."
	@i=0; \
	until docker compose exec -T mysql mysqladmin ping -h localhost -ulogio -plogio --silent >/dev/null 2>&1; do \
		i=$$((i + 1)); \
		if [ $$i -ge 30 ]; then \
			echo "MySQL is not ready after 60 seconds."; \
			exit 1; \
		fi; \
		sleep 2; \
	done
	@echo "MySQL is ready."

test:
	docker compose exec php composer test

stan:
	docker compose exec php composer phpstan

style:
	docker compose exec php composer phpcs

style-fix:
	docker compose exec php composer phpcbf

check: test stan style
