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
- создание проектов
- список доступных пользователю проектов
- переход в project workspace
- создание задач внутри проекта
- редактирование задач, включая статус, приоритет, дедлайн и исполнителя
- фильтрация project task list по статусу и дедлайну

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
docker compose exec -T app sh -lc 'npm install --cache /tmp/npm-cache'
```

После этого приложение доступно на `http://127.0.0.1/`.

## Проверки качества

Базовый агрегированный прогон:

```bash
docker compose exec -T app sh -lc 'export COMPOSER_HOME=/tmp/composer && composer run quality'
```

Отдельные команды:

```bash
docker compose exec -T app vendor/bin/pint --test
docker compose exec -T app vendor/bin/phpstan analyse --memory-limit=1G
docker compose exec -T app php artisan test
docker compose exec -T app php -d pcov.enabled=1 artisan test --coverage --min=80
docker compose exec -T app composer run test:e2e
docker compose exec -T app npx jscpd app tests routes --silent --config .jscpd.json
docker compose exec -T app vendor/bin/rector process --dry-run
docker compose exec -T app sh -lc 'export COMPOSER_HOME=/tmp/composer && composer audit'
```
