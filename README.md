# TaskFlow

Task management web application built with Laravel 13, PHP 8.3, PostgreSQL.

## Architecture

Clean architecture with pragmatic Laravel structure:

- `app/Domain/` — domain entities, value objects, business rules (no framework deps)
- `app/Application/` — use cases / application services
- `app/Http/` — controllers, web layer (Blade views)
- `app/Models/` — Eloquent models (persistence)

## Setup

```bash
docker compose up -d
docker compose exec app composer install
docker compose exec app cp .env.example .env
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
```

Application: http://localhost:8081

## Quality checks

```bash
# All tests
docker compose exec app php artisan test

# Lint
docker compose exec app vendor/bin/pint --test

# Static analysis
docker compose exec app vendor/bin/phpstan analyse --no-progress

# Copy-paste detection
docker compose exec app php vendor/systemsdk/phpcpd/phpcpd app/

# Rector (dry-run)
docker compose exec app vendor/bin/rector process --dry-run

# Dependency audit
docker compose exec app composer audit

# Mutation tests
docker compose exec app vendor/bin/infection --min-msi=0 --min-covered-msi=0 --threads=4
```

## CI

GitHub Actions runs on every push/PR:
- **CI workflow**: lint, PHPStan, PHPCPD, Rector, composer audit, PHPUnit with coverage
- **Mutation Tests workflow**: Infection on domain/application layers
- **Smoke Tests workflow**: manual dispatch, verifies app starts and responds
