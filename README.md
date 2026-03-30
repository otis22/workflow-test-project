# Демонстрационный проект агентного workflow

Это демонстрационный проект для знакомства с работой автономного агента.

Демо-проект внутри репозитория — `TaskFlow`, небольшой веб-сервис для управления личными и командными задачами: с регистрацией пользователей, проектами, задачами, комментариями, дедлайнами и базовой фильтрацией.

Проект показывает базовый процесс работы через:

- `AGENTS.md` с правилами workflow
- `Roadmap.md` с очередью работ
- `PRD/` с описаниями задач
- `AssumptionLog.md` с допущениями и решениями
- `artifacts/` с исходными материалами проекта

Репозиторий intentionally минимален: структура создана для демонстрации процесса, а содержимое можно наполнять по мере развития сценария.

## Текущее состояние

В репозитории уже развёрнут базовый Laravel 12 skeleton для продукта `TaskFlow` и подготовлена локальная среда на Docker Compose:

- `app` — PHP-FPM контейнер приложения
- `web` — Nginx
- `db` — PostgreSQL

Текущий функциональный прогресс MVP:

- регистрация, вход и выход из системы
- authenticated dashboard shell
- персональный dashboard с assigned work, near-term deadlines и quick project links
- создание проектов
- список доступных пользователю проектов
- переход в project workspace
- создание задач внутри проекта
- редактирование задач, включая статус, приоритет, дедлайн и исполнителя
- фильтрация project task list по статусу и дедлайну
- отдельная task detail page с основными полями и discussion history
- commenting flow на task detail page с историей обсуждения
- request-driven e2e journey для полного task workflow: project -> task -> dashboard -> status update -> comment

## Как начать

1. В первом промпте направьте агента на `AGENTS.md` и попросите написать `Roadmap.md`, опираясь на артефакты из `artifacts/`, начиная с подготовки окружения и до завершения проекта.
2. Дальше работайте в цикле: просите агента брать следующую задачу из `Roadmap.md` и выполнять её по workflow.

## Локальный запуск

1. Скопируйте `.env.example` в `.env`, если файл ещё не создан.
2. Поднимите окружение:

```bash
docker compose up -d --build
```

3. Установите PHP-зависимости:

```bash
docker compose exec -T app sh -lc 'export COMPOSER_HOME=/tmp/composer && composer install --no-interaction'
```

4. Сгенерируйте ключ приложения:

```bash
docker compose exec -T app php artisan key:generate --ansi
```

5. Установите frontend/npm-зависимости для инфраструктурных проверок:

```bash
docker compose exec -T app sh -lc 'npm ci --cache /tmp/npm-cache'
```

6. Примените миграции:

```bash
docker compose exec -T app php artisan migrate --force --ansi
```

После этого приложение доступно на `http://127.0.0.1/`.

## Проверки качества

Базовый агрегированный прогон:

```bash
docker compose exec -T app sh -lc 'export COMPOSER_HOME=/tmp/composer && composer run quality'
```

Он включает `lint`, `analyse`, основной test suite, coverage gate `94%`, duplicate detection, rector и dependency audit.

Отдельные команды проекта:

```bash
docker compose exec -T app sh -lc 'export COMPOSER_HOME=/tmp/composer && composer run lint'
docker compose exec -T app sh -lc 'export COMPOSER_HOME=/tmp/composer && composer run analyse'
docker compose exec -T app sh -lc 'export COMPOSER_HOME=/tmp/composer && composer run test'
docker compose exec -T app sh -lc 'export COMPOSER_HOME=/tmp/composer && composer run test:unit'
docker compose exec -T app sh -lc 'export COMPOSER_HOME=/tmp/composer && composer run test:feature'
docker compose exec -T app sh -lc 'export COMPOSER_HOME=/tmp/composer && composer run test:e2e'
docker compose exec -T app sh -lc 'export COMPOSER_HOME=/tmp/composer && composer run test:coverage'
docker compose exec -T app sh -lc 'export COMPOSER_HOME=/tmp/composer && composer run test:coverage:gate'
docker compose exec -T app sh -lc 'export COMPOSER_HOME=/tmp/composer && composer run cpd'
docker compose exec -T app sh -lc 'export COMPOSER_HOME=/tmp/composer && composer run rector'
docker compose exec -T app sh -lc 'export COMPOSER_HOME=/tmp/composer && composer run mutate'
docker compose exec -T app sh -lc 'export COMPOSER_HOME=/tmp/composer && composer run deps:audit'
```

Текущие quality baselines:

- `composer run test:coverage:gate` требует не менее `94%` общего покрытия.
- `composer run mutate` проверяет `app/Application` и `app/Models` с порогами `89` для `MSI` и `covered MSI`.

## CI Workflows

Репозиторий блокируется четырьмя GitHub Actions workflows на каждый push и pull request:

- `CI`:
  запускает `composer run lint`, `composer run analyse`, `composer run test:coverage:gate`, `composer run cpd`, `composer run rector`, `composer run deps:audit`.
- `E2E Tests`:
  запускает `composer run test:e2e`.
- `Mutation Tests`:
  запускает `composer run mutate`.
- `Smoke Tests`:
  поднимает `docker compose`, подготавливает приложение и проверяет `http://127.0.0.1/`.
