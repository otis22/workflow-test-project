# TaskFlow

Демонстрационный веб-сервис для управления задачами на Laravel 13.

Проект развивается автономным агентом по workflow, описанному в `AGENTS.md`.

## Запуск

```bash
docker compose up -d
```

Приложение доступно на http://localhost:8080

При первом запуске:

```bash
cp .env.example .env
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
```

## Команды качества

Все команды запускаются внутри app-контейнера:
`docker compose exec app composer <command>`

| Команда | Что делает |
|---------|-----------|
| `test` | Unit + feature тесты (Pest) |
| `test:coverage` | Тесты с coverage отчётом |
| `format` | Форматирование кода (Laravel Pint) |
| `format:check` | Проверка форматирования без правок |
| `stan` | Статический анализ (PHPStan + Larastan, level 6) |
| `rector` | Применить рефакторинги (Rector) |
| `rector:check` | Dry-run Rector'а |
| `md` | Проверка code smells (PHPMD) |
| `infection` | Мутационное тестирование (Infection) |
| `check:all` | Запуск всех проверок подряд |

## Артефакты проекта

- `AGENTS.md` — workflow для ИИ-агентов
- `Roadmap.md` — очередь задач
- `PRD/` — PRD по задачам
- `AssumptionLog.md` — журнал допущений и решений
- `artifacts/` — исходные продуктовые и технические требования

## Как начать работу с агентом

1. В первом промпте направьте агента на `AGENTS.md` и попросите написать `Roadmap.md`, опираясь на артефакты из `artifacts/`.
2. Дальше работайте в цикле: агент берёт следующую задачу из `Roadmap.md` и выполняет её по workflow.
