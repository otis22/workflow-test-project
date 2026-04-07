# Changelog

## Этап 2: Application-слой (Use Cases)

**Статус:** завершён.

### Добавлено

#### Общая инфраструктура application-слоя

- **`App\Application\Clock\Clock`** — порт для времени (`now(): DateTimeImmutable`).
- **`App\Application\Hashing\PasswordHasher`** — порт для хэширования (`hash`, `verify`).
- **`App\Application\Auth\SessionGuard`** — порт управления сессией (`login`, `logout`, `currentUserId`).
- **Repository интерфейсы в домене:**
  - `App\Domain\User\UserRepository` (findByEmail, findById, save)
  - `App\Domain\Project\ProjectRepository` (findById, save)
  - `App\Domain\Project\ProjectMemberRepository` (findByProjectAndUser, projectIdsForUser, save)
  - `App\Domain\Task\TaskRepository` (findById, save, listByProject с фильтрами status/dueBefore, listByAssignee)
  - `App\Domain\Task\CommentRepository` (save, listByTask)

#### Use cases

- **2.1 RegisterUser** (`App\Application\User\RegisterUser`) — регистрация с проверкой минимальной длины пароля (8), pre-валидацией name/email (trim + filter_var) до repository и хэширования, защитой от дубликатов email. Исключения: `WeakPasswordException`, `EmailAlreadyTakenException`.
- **2.2 Login/Logout** (`App\Application\Auth\Login`, `Logout`) — login с одинаковым сообщением ошибки для unknown email и wrong password (no user enumeration); logout идемпотентен. `InvalidCredentialsException`.
- **2.3 CreateProject** (`App\Application\Project\CreateProject`) — создание проекта + автоматическое добавление владельца как ProjectMember с тем же timestamp. `OwnerNotFoundException`.
- **2.4 ListUserProjects** (`App\Application\Project\ListUserProjects`) — список проектов где пользователь участник, стабильный порядок по id.
- **2.5 CreateTask** (`App\Application\Task\CreateTask`) — создание задачи с валидацией "project exists", "creator is member", "assignee (if set) is member". `ProjectNotFoundException`, `NotAProjectMemberException`.
- **2.6 UpdateTask** (`App\Application\Task\UpdateTask`) — partial update с boolean-флагами `changeDueDate`/`changeAssignee` для корректного различения "don't touch" vs "set to null" на nullable полях. `TaskNotFoundException`.
- **2.7 ListProjectTasks** — фильтрация по `?Status` и `?DateTimeImmutable $dueBefore`, тонкий делегат.
- **2.8 ListUserTasks** — задачи где пользователь assignee, тонкий делегат.
- **2.9 AddComment** — с проверкой "author is project member" (membership получаем через task.projectId).
- **2.10 ListTaskComments** — тонкий делегат.

#### Test fakes (tests/Support/Fakes/)

`FakeClock`, `FakePasswordHasher`, `InMemoryUserRepository`, `InMemorySessionGuard`, `InMemoryProjectRepository`, `InMemoryProjectMemberRepository`, `InMemoryTaskRepository`, `InMemoryCommentRepository`. Все с auto-incrementing id, reconstruct при save для сохранения immutability, фильтры совместимы с domain типами.

### Принятые решения

- **Все use cases — `final readonly class`** с constructor-injected портами (clean architecture, никаких Laravel facades).
- **Один Clock, один PasswordHasher на весь application-слой** — избегаем переизобретения и простой DI в Infrastructure-слое (3.x).
- **Доменные правила, требующие membership** ("creator/author is member", "assignee is member") реализованы в use cases, где есть контекст aggregate. Entity не имеют ссылок на UserRepository/MemberRepository.
- **Pre-validation name/email в RegisterUser** до findByEmail и hash — по результатам Codex review. Domain User constructor остаётся ultimate guarantee (DRY нарушена сознательно в пользу защиты границ).
- **No user enumeration в Login** — одинаковое сообщение и тип исключения для "unknown email" и "wrong password".
- **`changeDueDate`/`changeAssignee` флаги в UpdateTask** вместо sentinel-объектов. PHPMD `BooleanArgumentFlag` явно исключён в `phpmd.xml` с комментарием. Решение о полном CQRS-split на отдельные use cases (SetDueDate/Unassign) отложено — MVP UI имеет единую форму редактирования.
- **Placeholder `id: 0` до save** — текущий User/Project/Task/Comment entity позволяет id=0, repository присваивает реальный. После реализации 1.r2 (`id > 0`) придётся переделать на явный "DraftX" тип или save-возвращающий-id контракт.

### Отложенные замечания (Codex review)

- **2.r1** `[review]` — Email normalization (lowercase+trim) для case-insensitive поиска дубликатов и login. Источник: Codex review 2.1.

### Отклонение от workflow (Codex rate limit)

Начиная с задачи 2.2 Codex плагин упёрся в rate limit. Для задач 2.2–2.10 Codex review заменён на расширенный self-review (перечитывание diff, проверка PRD, ручная проверка boundary и security). Рекомендуется повторить Codex review задним числом в рамках финального ревью (этап 10). Задача 2.1 прошла полный цикл Codex review + fix.

### Проверки

- **115 unit-тестов** (64 domain из этапа 1 + 51 новый application): 264 ассерта.
- CI зелёный на всех 11 коммитах 2.1–2.10.
- Smoke test локально: контейнеры healthy, HTTP 200.

## Этап 1: Доменный слой

**Статус:** завершён.

### Добавлено

- **User entity** (1.1) — `app/Domain/User/User.php`. Поля: id, name, email, passwordHash, createdAt, updatedAt. Валидация email через filter_var, name и passwordHash через `trim() === ''`. Иммутабельность через `withName`/`withEmail`/`withPasswordHash`, перепроверяющие конструктор.
- **Project entity** (1.2) — `app/Domain/Project/Project.php`. Поля: id, ownerId, name, description, createdAt, updatedAt. Инвариант "проект должен иметь владельца" (`ownerId > 0`), description может быть пустым. Мутаторы: `withName`, `withDescription`.
- **ProjectMember entity** (1.3) — `app/Domain/Project/ProjectMember.php`. Поля: id, projectId, userId, createdAt. Без мутаторов (членство либо создаётся, либо удаляется). Правило "владелец автоматически — участник" вынесено в use case `CreateProject` (этап 2.3).
- **Task entity** (1.4) — `app/Domain/Task/Task.php`. 11 полей: id, projectId, creatorId, assigneeId(?), title, description, status, priority, dueDate(?), createdAt, updatedAt. 6 мутаторов через приватный `copyWith` helper, корректно обрабатывающий nullable поля через `array_key_exists`.
- **Comment entity** (1.5) — `app/Domain/Task/Comment.php`. Поля: id, taskId, authorId, body, createdAt. Без мутаторов. Правило "автор — участник" в `AddComment` use case (этап 2.9).
- **Value Objects** (1.6) — `app/Domain/Task/{Status,Priority,DueDate}.php`:
  - `Status` — backed string enum: Todo, InProgress, Done.
  - `Priority` — backed string enum: Low, Medium, High.
  - `DueDate` — final readonly wrapper над DateTimeImmutable с `isOverdue(now)` и timezone-агностичным `equals` через `format('U.u')`.
  - Интеграция VO в Task (1.6.2): убраны массивы STATUSES/PRIORITIES и проверки `in_array` — невалидные значения непредставимы на уровне типов.
- **phpmd.xml ruleset** дополнен (1.4): `ExcessiveParameterList` поднят до 12 для domain entities с promotion-конструктором; `ShortVariable` сохраняет исключение для `id`.

### Принятые решения

- На уровне домена используется camelCase (`passwordHash`, `ownerId`, `dueDate`), snake_case останется для БД-маппинга в этапе 3.2.
- Доменные правила, требующие знания о других сущностях ("создатель — участник проекта", "автор комментария — участник"), вынесены из entity в use cases. Entity не знают про membership, чтобы сохранить изоляцию aggregate.
- Status/Priority как backed enums (PHP 8.1+) вместо отдельных VO-классов: идиоматично, исчерпывающее покрытие через `match`, дешевле сериализация.
- DueDate equality сравнивает instant, не timezone metadata — два DueDate, представляющих один UTC-момент в разных часовых поясах, считаются равными.
- Создан общий `copyWith` helper в Task: для остальных entities дублирование `new self(...)` оставлено как есть (мало полей, не оправдывает абстракции).

### Отложенные замечания (Codex review)

- **1.r1** `[review]` — добавить инвариант `updatedAt >= createdAt` симметрично во все entities (User, Project, Task, Comment).
- **1.r2** `[review]` — добавить валидацию `id > 0` симметрично во все entities.

Оба отложены, потому что должны применяться ко всем сущностям одновременно как самостоятельный refactor; вне scope отдельных задач 1.x.

### Проверки

- 64 unit-теста на домен (User: 12, Project: 9, ProjectMember: 5, Task: 17, Comment: 7, Status: 3, Priority: 3, DueDate: 7, остальные — feature/example).
- CI зелёный на всех коммитах 1.1–1.6.
- Smoke test локально: контейнеры healthy, http://localhost:8080 → 200.
- Все Codex review циклы завершены (APPROVE или все замечания триажированы accept/defer/reject).

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
