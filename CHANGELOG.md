# Changelog

## Этап 0: Настройка окружения и инфраструктуры

**Статус:** завершён.

### Добавлено

- **Docker Compose окружение** (0.1): PHP 8.3 FPM, Nginx 1.25, PostgreSQL 16. Healthchecks для app и db, entrypoint-скрипт для прав на storage/bootstrap/cache.
- **Laravel 13 проект** (0.2): базовая установка, PostgreSQL-подключение, `APP_KEY`, миграции Laravel по умолчанию.
- **Инструменты качества** (0.3): Pest 4, Laravel Pint, PHPStan + Larastan (level 6), Rector 2, PHPMD, Infection 0.29, pcov для coverage. Composer-скрипты: `test`, `test:coverage`, `format`, `format:check`, `stan`, `rector`, `rector:check`, `md`, `infection`, `check:all`.
- **CI основной пайплайн** (0.4): GitHub Actions workflow `ci.yml` — composer install → audit → pint → stan → rector → md → migrate → pest coverage. Postgres service container. PHP 8.3 + pcov.
- **Дополнительные CI workflows** (0.5): `smoke.yml` (реальный docker compose prop), `e2e.yml` и `mutation.yml` как скелеты (workflow_dispatch).
- **Структура каталогов чистой архитектуры** (0.6): `app/Domain`, `app/Application`, `app/Infrastructure`.

### Принятые решения

- Пароли БД захардкожены в docker-compose.yml — допустимо для dev.
- PHPCPD заменён на PHPMD (sebastian/phpcpd архивирован, не совместим с PHP 8.3). Дубликаты частично покрывает Rector.
- Infection установлен, но первый полный прогон отложен до этапа 9 — Pest hijack'ает phpunit binary, нужна спец-интеграция.
- Coverage gate временно `--min=0`, поднимется до 80% в этапе 9.
- GitHub Actions закреплены по major tag (`@v4`), не по SHA — оставлено для финального ревью безопасности (этап 10).

### Проверки

- CI зелёный на всех задачах 0.1–0.6.
- Smoke test локально: все контейнеры healthy, HTTP 200 на http://localhost:8080.
