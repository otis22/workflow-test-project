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

## Задача 1.4: CI-пайплайн

**Дата:** 2026-03-26

### Решения

- PHPUnit в CI работает на SQLite in-memory (стандарт phpunit.xml) — достаточно для unit/feature тестов, PostgreSQL доступен для будущих интеграционных тестов.
- Coverage check пока информационный — порог 80% будет блокировать merge после задачи 8.3.
- PostgreSQL 16 service в CI настроен и готов к использованию.
- PHPCPD запускается через `php vendor/systemsdk/phpcpd/phpcpd` (нет symlink в vendor/bin).

## Задача 1.5: Дополнительные CI workflows

**Дата:** 2026-03-26

### Решения

- Mutation tests workflow запускается на каждый push/PR, но пропускает Infection если нет PHP-файлов в Domain/Application (graceful skip).
- Smoke tests workflow — только ручной запуск (workflow_dispatch), запускается после завершения этапа по воркфлоу.
- E2E workflow будет создан в задаче 8.1 когда появятся Blade-шаблоны и маршруты для тестирования.

## Задача 2.1: Сущность User

**Дата:** 2026-03-26

### Решения

- User — readonly доменная сущность (не Eloquent). Eloquent model останется в app/Models как инфраструктурный слой.
- Email — value object с валидацией через filter_var и реализацией Stringable.
- ID — int (соответствует auto-increment в PostgreSQL). UUID можно добавить позже без изменения домена.
- password_hash не хранится в доменной сущности — это ответственность инфраструктуры (Eloquent model).
