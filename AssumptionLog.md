# Assumption Log

## Задачи 1.1 + 1.2: Инициализация Laravel + Docker Compose

**Дата:** 2026-03-26

### Решения

- Задачи 1.1 и 1.2 объединены, т.к. PHP/Composer не установлены локально — Laravel создаётся через Docker.
- Выбран PostgreSQL 16 (alpine) как реляционная БД — tech requirements не фиксируют конкретную СУБД.
- Nginx как веб-сервер (альтернатива — Caddy, но Nginx более стандартен для Laravel).
- Порт 8081 для веб-сервера (8080 был занят на хост-машине).
- Xdebug установлен в контейнере для будущего coverage.
- Архитектурные каталоги: `app/Domain`, `app/Application`, `app/Infrastructure` — namespace `App\Domain\*`, `App\Application\*`, `App\Infrastructure\*` через стандартный PSR-4 autoload Laravel (`App\\` → `app/`).
- `app/Http` сохранён как web/UI слой (стандарт Laravel).
- `app/Models` пока содержит стандартный User model — будет перенесён в Domain слой при задаче 2.1.

### Допущения

- Docker Compose `docker compose up` требует предварительного `chmod 777 storage bootstrap/cache` — нужно автоматизировать в Dockerfile или entrypoint.
- SQLite удалён, проект использует только PostgreSQL.
