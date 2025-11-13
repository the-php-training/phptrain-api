# PHPTrain API - Makefile for Hyperf Framework
# Variables
DC=USERID=$(USERID) GROUPID=$(GROUPID) docker compose --file docker-compose.yml --env-file ./src/.env
HYPERF=php bin/hyperf.php

.PHONY: help up down restart sh logs install test db-migrate db-migrate-create db-migrate-rollback db-migrate-fresh db-seed cache-clear autoload start

USERID := $(shell id -u)
GROUPID := $(shell id -g)

# Default target - show help
help:
	@echo "PHPTrain API - Available Commands"
	@echo ""
	@echo "Docker Management:"
	@echo "  make up                  - Start containers in background"
	@echo "  make down                - Stop and remove containers"
	@echo "  make restart             - Restart containers"
	@echo "  make sh                  - Open shell in app container"
	@echo "  make logs                - Show container logs (follow)"
	@echo ""
	@echo "Application:"
	@echo "  make install             - Install composer dependencies"
	@echo "  make autoload            - Regenerate composer autoload"
	@echo "  make cache-clear         - Clear Hyperf runtime cache"
	@echo ""
	@echo "Database (if migrations installed):"
	@echo "  make db-migrate          - Run database migrations"
	@echo "  make db-migrate-create   - Create new migration file"
	@echo "  make db-migrate-rollback - Rollback last migration"
	@echo "  make db-migrate-fresh    - Drop all tables and re-run migrations"
	@echo "  make db-seed             - Run database seeders"
	@echo ""
	@echo "Testing:"
	@echo "  make test                - Run tests"
	@echo ""
	@echo "Development:"
	@echo "  make go                  - Full setup (install + build + start + migrate)"
	@echo "  make show-vars           - Show user/group IDs"
	@echo "  make log                 - Show Hyperf logs"

show-vars:
	@echo "USERID: $(USERID)"
	@echo "GROUPID: $(GROUPID)"

# Full setup: install dependencies, build, start, and migrate
go: down install
	$(DC) up -d --build
	@echo ""
	@echo "✅ Containers started!"
	@echo "⏳ Running migrations..."
	@sleep 3
	@$(MAKE) db-migrate
	@echo ""
	@echo "📍 API running at: http://localhost:9501"
	@echo "📖 Health check: curl http://localhost:9501/"

# Docker commands
up:
	$(DC) up -d
	@echo "✅ Containers started"

down:
	$(DC) down
	@echo "✅ Containers stopped"

stop: down

restart: down up
	@echo "✅ Containers restarted"

sh:
	$(DC) exec ptrain-api sh

logs:
	$(DC) logs -f --tail=50

# Application commands
install:
	$(DC) run --rm ptrain-api composer install --ignore-platform-req=php
	@echo "✅ Composer dependencies installed"

autoload:
	$(DC) exec ptrain-api composer dump-autoload
	@echo "✅ Autoload regenerated"

cache-clear:
	$(DC) exec ptrain-api rm -rf runtime/container
	@echo "✅ Runtime cache cleared"

# Database commands (Hyperf Migrations)
# Note: Requires hyperf/database or phinx to be installed
db-migrate:
	@echo "Running migrations..."
	@$(DC) exec ptrain-api $(HYPERF) migrate || \
	$(DC) exec ptrain-api vendor/bin/phinx migrate -c phinx.php || \
	echo "⚠️  No migration tool found. Install hyperf/database or phinx."

db-migrate-create:
	@read -p "Enter migration name: " name; \
	$(DC) exec ptrain-api $(HYPERF) gen:migration $$name || \
	$(DC) exec ptrain-api vendor/bin/phinx create $$name -c phinx.php || \
	echo "⚠️  No migration tool found."

db-migrate-rollback:
	@echo "Rolling back migrations..."
	@$(DC) exec ptrain-api $(HYPERF) migrate:rollback || \
	$(DC) exec ptrain-api vendor/bin/phinx rollback -c phinx.php || \
	echo "⚠️  No migration tool found."

db-rollback: db-migrate-rollback

db-migrate-fresh:
	@echo "⚠️  WARNING: This will drop all tables!"
	@read -p "Are you sure? (yes/no): " confirm; \
	if [ "$$confirm" = "yes" ]; then \
		$(DC) exec ptrain-api $(HYPERF) migrate:fresh || \
		echo "⚠️  No migration tool found."; \
	else \
		echo "❌ Cancelled"; \
	fi

db-seed:
	@echo "Seeding database..."
	@$(DC) exec ptrain-api php -r "define('BASE_PATH', '/var/www'); require '/var/www/vendor/autoload.php'; \$$seeder = new \Database\Seeders\TenantSeeder(); \$$seeder->run();" && \
	$(DC) exec ptrain-api php -r "define('BASE_PATH', '/var/www'); require '/var/www/vendor/autoload.php'; \$$seeder = new \Database\Seeders\StudentSeeder(); \$$seeder->run();" && \
	$(DC) exec ptrain-api php -r "define('BASE_PATH', '/var/www'); require '/var/www/vendor/autoload.php'; \$$seeder = new \Database\Seeders\CourseSeeder(); \$$seeder->run();" && \
	echo "✅ Database seeded successfully"

# Testing
test:
	$(DC) exec ptrain-api composer test || \
	$(DC) exec ptrain-api vendor/bin/co-phpunit --colors=always

test-report:
	$(DC) exec ptrain-api vendor/bin/pest --coverage-html=report || \
	echo "⚠️  Pest not installed"

# Application logs
log:
	$(DC) exec ptrain-api tail -f runtime/logs/hyperf.log -n 50 || \
	echo "⚠️  Log file not found. Try: make logs"

# Quick access to common tasks
rebuild: down
	$(DC) build --no-cache
	$(DC) up -d
	@echo "✅ Containers rebuilt"

clean: down
	$(DC) down -v
	@echo "✅ Containers and volumes removed"
