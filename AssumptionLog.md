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

## Задача 1.3: Инструменты качества

**Дата:** 2026-03-26

### Решения

- `sebastian/phpcpd` abandoned и несовместим с Symfony Console 7 — заменён на `systemsdk/phpcpd` v8.3.0 (совместимый форк).
- PHPStan уровень 5 — достаточный для MVP, можно повысить позже.
- Rector настроен с `php83`, `deadCode`, `codeQuality`, `typeDeclarations` — применил `#[Override]` атрибуты.
- Infection MSI пороги 0/0 — будут подняты после появления доменного кода (задача 8.2).
- Laravel Pint уже был в проекте из коробки — конфигурация по умолчанию (Laravel preset).
