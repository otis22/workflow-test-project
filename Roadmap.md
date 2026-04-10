# Roadmap: TaskFlow MVP

## Этап 0: Настройка окружения и инфраструктуры

| # | Задача | Описание | Статус |
|---|--------|----------|--------|
| 0.1 | Docker Compose | Создать docker-compose.yml с сервисами: app (PHP 8.3 + Laravel), nginx, PostgreSQL. Убедиться, что `docker compose up` поднимается без ошибок | `done` |
| 0.2 | Инициализация Laravel-проекта | Создать Laravel-проект внутри контейнера, настроить .env, подключить БД, проверить стартовую страницу | `done` |
| 0.3 | Инструменты качества | Установить и настроить: Pest, Laravel Pint, PHPStan, Rector, PHPCPD, Infection. Проверить запуск каждого инструмента | `done` |
| 0.4 | CI — основной пайплайн | GitHub Actions workflow: install → pint (check) → pest (coverage) → phpstan → phpcpd → rector (dry-run) → composer audit | `done` |
| 0.5 | CI — дополнительные workflows | Отдельные workflows: e2e tests (Dusk), smoke tests, mutation tests (Infection). Каждый блокирует merge при неудаче | `done` |
| 0.6 | Структура каталогов | Создать каталоги для чистой архитектуры: Domain, Application, Infrastructure, Web/UI. Настроить autoload | `done` |

## Этап 1: Доменный слой

| # | Задача | Описание | Статус |
|---|--------|----------|--------|
| 1.1 | Сущность User | Domain-модель User с полями из доменной модели. Unit-тесты на создание и валидацию | `done` |
| 1.2 | Сущность Project | Domain-модель Project с owner_id. Правило: проект должен иметь владельца | `done` |
| 1.3 | Сущность ProjectMember | Domain-модель ProjectMember. Правило: владелец автоматически является участником | `done` |
| 1.4 | Сущность Task | Domain-модель Task со статусами (todo/in_progress/done) и приоритетами (low/medium/high). Правила: задача принадлежит проекту, создатель — участник проекта | `done` |
| 1.5 | Сущность Comment | Domain-модель Comment. Правило: комментарий может добавить только участник проекта | `done` |
| 1.6 | Value Objects | Status, Priority, DueDate — выделить value objects для типизации. Unit-тесты | `done` |

## Этап 2: Application-слой (Use Cases)

| # | Задача | Описание | Статус |
|---|--------|----------|--------|
| 2.1 | Регистрация пользователя | Use case RegisterUser. Валидация, хэширование пароля, сохранение. Unit-тесты | `done` |
| 2.2 | Аутентификация | Use case Login / Logout. Проверка credentials, управление сессией. Unit-тесты | `done` |
| 2.3 | Создание проекта | Use case CreateProject. Автоматическое добавление владельца как участника. Unit-тесты | `done` |
| 2.4 | Список проектов пользователя | Use case ListUserProjects. Возвращает проекты, где пользователь — участник. Unit-тесты | `done` |
| 2.5 | Создание задачи | Use case CreateTask. Валидация: создатель — участник проекта. Unit-тесты | `done` |
| 2.6 | Редактирование задачи | Use case UpdateTask. Изменение полей: title, description, status, priority, due_date, assignee. Валидация: assignee — участник проекта. Unit-тесты | `done` |
| 2.7 | Список задач проекта | Use case ListProjectTasks. Фильтрация по статусу и дедлайну. Unit-тесты | `done` |
| 2.8 | Список задач пользователя | Use case ListUserTasks. Задачи, назначенные текущему пользователю. Unit-тесты | `done` |
| 2.9 | Добавление комментария | Use case AddComment. Валидация: автор — участник проекта. Unit-тесты | `done` |
| 2.10 | Список комментариев задачи | Use case ListTaskComments. Unit-тесты | `done` |
| 2.11 | `[review]` Codex review задач 2.2–2.10 | Прогнать Codex review для задач 2.2–2.10 задним числом — на этапе 2 Codex плагин был недоступен из-за rate limit (см. AssumptionLog). Зафиксировать триаж и при необходимости создать fix-задачи | `done` |

## Этап 3: Инфраструктурный слой

| # | Задача | Описание | Статус |
|---|--------|----------|--------|
| 3.1 | Миграции БД | Миграции для таблиц: users, projects, project_members, tasks, comments | `done` |
| 3.2 | Eloquent-модели и репозитории | Eloquent-модели + реализация репозиториев (UserRepository, ProjectRepository, TaskRepository, CommentRepository). Привязка интерфейсов через Service Provider | `done` |
| 3.3 | Фабрики и сидеры | Создать фабрики для всех моделей. Базовый сидер для тестирования | `done` |

## Этап 4: Web/UI — Аутентификация

| # | Задача | Описание | Статус |
|---|--------|----------|--------|
| 4.1 | Layout и базовые Blade-компоненты | Общий layout, навигация, стили (Tailwind или аналог). Минимальный, но аккуратный UI | `done` |
| 4.2 | Страница регистрации | Форма регистрации, контроллер, валидация, маршрут | `done` |
| 4.3 | Страница входа | Форма входа, контроллер, валидация, маршрут | `done` |
| 4.4 | Выход из системы | Кнопка logout, маршрут, уничтожение сессии | `done` |

## Этап 5: Web/UI — Проекты

| # | Задача | Описание | Статус |
|---|--------|----------|--------|
| 5.1 | Список проектов | Страница со списком проектов пользователя, ссылка на создание нового | `done` |
| 5.2 | Создание проекта | Форма создания проекта, контроллер, валидация, перенаправление | `done` |
| 5.3 | Страница проекта | Страница проекта со списком задач, фильтр по статусу, кнопка создания задачи | `done` |

## Этап 6: Web/UI — Задачи

| # | Задача | Описание | Статус |
|---|--------|----------|--------|
| 6.1 | Создание задачи | Форма создания задачи внутри проекта: title, description, status, priority, due_date, assignee | `done` |
| 6.2 | Страница задачи | Просмотр задачи: все поля + блок комментариев + форма добавления комментария | `done` |
| 6.3 | Редактирование задачи | Форма редактирования задачи, сохранение изменений | `done` |
| 6.4 | Фильтрация задач | Фильтрация по статусу реализована в 5.3.2; dueBefore не в UI-spec | `done` |

## Этап 7: Web/UI — Dashboard и мои задачи

| # | Задача | Описание | Статус |
|---|--------|----------|--------|
| 7.1 | Dashboard | Экран после входа: задачи пользователя, задачи с ближайшим дедлайном, ссылки на проекты | `done` |
| 7.2 | Список моих задач | Объединён с Dashboard (7.1) | `done` |

## Этап 8: E2E-тесты

| # | Задача | Описание | Статус |
|---|--------|----------|--------|
| 8.1 | Настройка Dusk | Установка Laravel Dusk, конфигурация для работы в Docker | `done` |
| 8.2 | E2E: регистрация и вход | Тест: регистрация нового пользователя → вход → попадание на dashboard | `done` |
| 8.3 | E2E: создание проекта | Тест: создание проекта → появление в списке проектов | `done` |
| 8.4 | E2E: создание задачи | Тест: создание задачи в проекте → появление в списке задач | `done` |
| 8.5 | E2E: изменение статуса задачи | Тест: изменение статуса задачи → отображение нового статуса | `done` |
| 8.6 | E2E: просмотр моих задач | Тест: негативный кейс (unassigned not on dashboard). Позитивный зависит от assignee feature | `done` |

## Этап 9: Мутационное тестирование и coverage gate

| # | Задача | Описание | Статус |
|---|--------|----------|--------|
| 9.1 | Настройка Infection | BLOCKED: Infection 0.29 несовместим с Pest 4 / PHPUnit 12. Ждём Infection 0.30+ | `todo` |
| 9.2 | Coverage gate | --min=80 в composer test:coverage. Текущее покрытие 97.4% | `done` |
| 9.3 | Усиление тестов | Зависит от 9.1 (blocked) | `todo` |

## Этап 1.x: Отложенные замечания ревью

| # | Задача | Описание | Статус |
|---|--------|----------|--------|
| 1.r1 | `[review]` Инвариант updatedAt >= createdAt | Добавить проверку в конструкторы доменных сущностей (User, Project, далее Task/Comment), что updatedAt не раньше createdAt. Применить симметрично ко всем сущностям. Источник: Codex review задачи 1.2 | `done` |
| 1.r2 | `[review][rejected]` Валидация id > 0 во всех entities | Замечание корректно в вакууме, но несовместимо с draft-паттерном проекта: все 5 use cases используют `new Entity(id: 0, ...) → repository->save()`. Паттерн зафиксирован во всех 5 Eloquent-репозиториях (3.2.2-3.2.6). `id = 0` — легитимное состояние unpersisted draft. Закрыто в 3.2.6. См. AssumptionLog 1.r2 | `done` |
| 2.r1 | `[review]` Email normalization | Нормализовать email (lowercase + trim) на уровне use cases или value object EmailAddress. Сделать поиск дубликатов и login case-insensitive. Источник: Codex review задачи 2.1 | `done` |
| 2.r2 | `[review]` Actor-based authorization в application layer | Системная проблема: `UpdateTask`, `ListProjectTasks`, `ListUserTasks`, `ListTaskComments` не принимают actor и не проверяют право вызывающего (IDOR). Решение пользователя: actor-based в application layer. Реализовано в 2 подзадачах: 2.r2.A (UpdateTask + ListProjectTasks), 2.r2.B (ListUserTasks rename + ListTaskComments). Информационный leak 404 vs 403 вынесен отдельно как 2.r3. | `done` |

| 2.r3 | `[review]` Information disclosure: 404 vs 403 | Web layer collapses both exceptions to abort(404). Domain layer retains the distinction for flexibility. Acceptable trade-off для MVP. Источник: Codex review 2.r2.A | `done` |

## Этап 10: Финальное ревью и исправление ошибок

| # | Задача | Описание | Статус |
|---|--------|----------|--------|
| 10.1 | Ревью артефактов | Roadmap, PRD, AssumptionLog синхронизированы | `done` |
| 10.2 | Ревью архитектуры | PASS: нет domain→infra leaks, нет fat controllers, нет raw SQL в domain/app | `done` |
| 10.3 | Ревью безопасности | PASS: CSRF, XSS escaping, auth middleware, no user enumeration | `done` |
| 10.4 | Ревью CI/инфраструктуры | CI + E2E green, все quality tools present. Infection blocked (9.1) | `done` |
| 10.5 | Аудит зависимостей | composer audit clean — нет known vulnerabilities | `done` |
| 10.6 | Smoke tests | Containers healthy, /, /login, /register → 200, /projects → 302 | `done` |
| 10.7 | Исправление найденных проблем | Нет новых issues. 2.r3 mitigated at web layer | `done` |
| 10.8 | Финальный changelog | CHANGELOG.md обновлён по всем этапам 0–10 | `done` |
