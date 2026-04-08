# Assumption Log

## 0.1 Docker Compose

- **PostgreSQL 16** выбран как БД — стабильная LTS-версия, совместима с Laravel
- **Nginx 1.25 Alpine** — легковесный образ для dev-окружения
- **Порт 8080** для nginx, **5432** для PostgreSQL — пробрасываются на хост для удобства разработки
- Пароли БД захардкожены в docker-compose.yml — допустимо для dev, будет вынесено в .env при инициализации Laravel (задача 0.2)
- Healthcheck для app использует `kill -0 1` (проверка PID 1 / php-fpm master) — простой и надёжный без дополнительных пакетов

## 0.2 Инициализация Laravel

- **Laravel 13** (v13.3.0) — последняя стабильная, соответствует требованию "PHP 8.3+ + Laravel"
- **Композитор устанавливался внутри контейнера**, затем файлы перенесены в bind-mount — т.к. composer create-project на хосте не имел окружения PHP
- **Entrypoint-скрипт** `docker/php/entrypoint.sh` исправляет права на `storage/` и `bootstrap/cache/` при старте контейнера — bind-mount с хоста приводит к user-mismatch с www-data
- **`|| true` в entrypoint** — намеренно для fallback на rootless-docker / chmod-restricted FS
- **Codex triage:**
  - reject: раздельная проверка storage/bootstrap/cache — Laravel всегда создаёт обе
  - reject: `|| true` глушит ошибки — намеренно для dev fallback
  - reject: `exec $@` без кавычек — ложноположительное, в коде `exec "$@"`

## 0.3 Инструменты качества

- **Pest 4.x** как test framework (вместо PHPUnit). `tests/Pest.php` байндит TestCase только для Feature-тестов; Unit-тесты используют чистый PHPUnit\TestCase.
- **PHPStan уровень 6** + **larastan 3.x** — разумный baseline для Laravel-проекта. Повышение уровня — не обязательное улучшение, будет рассмотрено в финальном ревью (этап 10).
- **phpstan покрывает только `app/`**, не `tests/` — Pest-магия с `pest()->extend()` не понимается PHPStan без спец-расширения.
- **PHPCPD → phpmd**: sebastian/phpcpd архивирован и не совместим с PHP 8.3. phpmd установлен как общий quality-checker (cleancode, codesize, design, naming, unusedcode). Детектирование дубликатов частично покрывает Rector с prepared sets `deadCode` + `codeQuality`. Компромисс зафиксирован, при финальном ревью оценить необходимость полноценного CPD-инструмента.
- **pcov** добавлен в Dockerfile PHP как coverage driver (лёгче xdebug).
- **Infection установлен, но первый полный прогон отложен до этапа 9** (Мутационное тестирование). Причины:
  1. Pest hijack'ает phpunit binary — Infection падает при запуске initial test через `./vendor/bin/phpunit`. Требуется либо infection/pest-plugin, либо обходной путь через `--test-framework-options`.
  2. `app/` пока практически пуст (только Laravel boilerplate) — нечего мутировать.
- **Coverage gate `--min=0`** сейчас отключён, будет поднят до 80% для domain/application в этапе 9.
- **composer check:all** — shortcut-скрипт, запускающий format:check, stan, rector:check, md, test, composer audit. Будет использоваться в CI (задачи 0.4-0.5).
- **Codex triage:**
  - reject (critical): «rector.php синтаксически невалиден» — ложноположительное, Codex парсил plain-text моего сообщения, а не реальный файл. Rector dry-run проходит успешно.
  - defer: coverage `--min=0` → этап 9
  - defer: Infection testFramework конфликт с Pest → этап 9
  - defer: phpmd vs PHPCPD полнота → переоценить в этапе 10
  - reject: PHPStan level 6 консервативен — достаточен для MVP
  - reject: pcov без pin версии — преждевременная оптимизация для dev

## 1.1 Доменная сущность User

- **Чистый домен без Eloquent**: `final readonly class User` с public readonly properties — нативная PHP 8.3 иммутабельность, валидация в конструкторе.
- **Поле `passwordHash` (camelCase)** на уровне домена вместо `password_hash` из доменной модели — на уровне домена используем PHP-конвенцию, snake_case останется на уровне БД/маппинга (этап 3.2).
- **Иммутабельность через `withName`/`withEmail`/`withPasswordHash`**: каждый метод принимает новое значение и `DateTimeImmutable $updatedAt`, возвращает новый экземпляр через `new self(...)`. Валидация автоматически срабатывает заново через конструктор.
- **Валидация пустых строк через `trim($value) === ''`** (после Codex review) — отсекает whitespace-only значения, корректное толкование "не может быть пустым".
- **`phpmd.xml` ruleset** добавлен на уровне проекта: `id` объявлен исключением для правила `ShortVariable`, иначе любой entity identifier ловит false-positive PHPMD. Альтернатива (PHPDoc `@SuppressWarnings`) отвергнута — PHPStan не парсит дотированный синтаксис.
- **Codex triage:**
  - accept: trim() для name/passwordHash вместо `=== ''`
  - accept: добавлены негативные тесты для withName/withPasswordHash
  - accept: withEmail/withPasswordHash тесты дополнены проверкой bumped updatedAt и неизменности оригинала
  - accept: positive-path тест проверяет createdAt/updatedAt
  - reject: phpmd.xml "malformed" — false positive (Codex видел рендер markdown, не реальный файл; check:all зелёный)
  - reject: md scoped to app/ only — соответствует stage 0 (stan/rector тоже на app/), tests исключены сознательно (Pest magic, см. 0.3); вне scope 1.1
  - re-review: APPROVE

## 1.2 Доменная сущность Project

- **`ownerId: int`** на уровне домена вместо `owner_id`. Инвариант "проект должен иметь владельца" реализован как `ownerId > 0`.
- **`description` может быть пустой строкой** — описание необязательно по доменной модели.
- **Метод `withName`** перепроверяет валидность через конструктор автоматически. Метод `withDescription` без валидации (description безусловно валиден).
- **Codex triage:**
  - defer: инвариант `updatedAt >= createdAt` — реальное доменное правило, но не в PRD 1.2 и отсутствует в User 1.1; должен применяться симметрично → вынесено в Roadmap как 1.r1 [review]
  - reject: trim-and-store вместо trim-only — PRD не требует нормализации, поведение симметрично User 1.1 (transparency over speculative normalization)
  - reject: тест на trim-vs-store policy — нет правила для проверки

## 1.3 Доменная сущность ProjectMember

- **Минимальная entity** без мутаторов: членство либо создаётся, либо удаляется на уровне репозитория. Изменение полей участника не имеет смысла.
- **Правило "владелец автоматически — участник"** не реализуется в entity, а вынесено в use case `CreateProject` (этап 2.3): на уровне entity это требовало бы инжекта Project и нарушило бы изоляцию.
- **Размещение в `app/Domain/Project/`** (а не в отдельном `app/Domain/ProjectMember/`) — ProjectMember концептуально часть aggregate Project.
- **Codex review:** ship, no findings.

## 1.4 Доменная сущность Task

- **Status и Priority как строки + const массивы STATUSES/PRIORITIES** на этапе 1.4. В 1.6 заменятся на типизированные value objects.
- **`assigneeId`, `dueDate` — nullable** по доменной модели (задача может быть без исполнителя и без дедлайна).
- **`description: string` (не nullable)** — пустая строка = "нет описания". Симметрично с Project.
- **`copyWith` private helper** для всех 6 мутаторов вместо повторения 11 named-arg вызовов. Использует `array_key_exists` для nullable-полей (`assigneeId`, `dueDate`), чтобы явный `null` не схлопывался через `??`.
- **phpmd `ExcessiveParameterList` поднят до 12** — domain entity с promotion-конструктором легитимно имеет 11 параметров. После 1.6 (введение VO) количество снизится.
- **Правило "creator — участник проекта"** не в entity, а в use case `CreateTask` (этап 2.5).
- **Codex triage:**
  - defer: `id <= 0` валидация — отсутствует во всех 4 entities, должна применяться симметрично → Roadmap 1.r2 [review]
  - reject: description должен быть `?string` — PRD явно `string (может быть пустой)`, не nullable; симметрично с Project
  - reject: тесты на null description — нет правила

## 1.5 Доменная сущность Comment

- **Без мутаторов** — комментарии не редактируются в MVP (UI spec не предусматривает edit). Создание/удаление на уровне репозитория.
- **Размещение в `app/Domain/Task/`** — Comment концептуально часть aggregate Task.
- **Правило "автор — участник проекта"** в use case `AddComment` (этап 2.9), не в entity.
- **Codex review:** APPROVE, no findings.

## Этап 2 — отклонение от workflow (Codex rate limit)

Начиная с задачи 2.2 Codex плагин вернул rate-limit (reset 5pm Europe/Moscow). Для задач 2.2–2.10 Codex review заменён на расширенный self-review (перечитывание diff, проверка соответствия PRD и артефактам, ручная проверка security/boundary кейсов). Задача 2.1 получила полный Codex review цикл. Все проверки check:all и CI остаются обязательными. Вернуться к полноценному Codex review рекомендуется на финальном ревью (этап 10) задним числом для задач 2.2–2.10.

## 1.6 Value Objects (Status, Priority, DueDate)

- **Status и Priority — backed string enum** (PHP 8.1+ idiomatic). Кейсы: Todo/InProgress/Done и Low/Medium/High. После интеграции в Task валидация через `in_array` удалена — невалидные значения непредставимы на уровне типов.
- **DueDate — final readonly value class** с методом `isOverdue(now): bool` и `equals(other)`. Семантика: `isOverdue` строгая (now > value, равенство — не overdue).
- **Equality DueDate** через `format('U.u')` — сравнивает instant + микросекунды в UTC, не timezone metadata. Это важно: одно и то же мгновение в разных таймзонах считается равным (см. тест "different timezones are equal").
- **Декомпозиция 1.6.1 → VO + tests, 1.6.2 → интеграция в Task** — два отдельных коммита и codex-review цикла, чтобы каждый был ≤150 строк и обозримым.
- **Размещение всех трёх VO в `app/Domain/Task/`** — они принадлежат aggregate Task.
- **phpmd `ExcessiveParameterList: 12`** оставлено: после VO интеграции количество параметров Task осталось 11 (11 семантических полей), хотя их типы ужесточились.
- **Codex triage 1.6.1:**
  - accept: `==` на DateTimeImmutable семантически неопределён → перешёл на `format('U.u')`
  - accept: тест на timezone-equality → добавлен
  - defer: `values()` helper на enum → не нужен в 1.6.2 (in_array удалён, не дублирован)
- **Codex review 1.6.2:** APPROVE, no findings.

## 2.11 Отложенный Codex review задач 2.2–2.10

- Выполнен Codex review на diff `375bf89..ad08bdd` (application layer задач 2.2–2.10).
- Первые две попытки провалились из-за sandbox Codex: `bwrap: loopback: Failed RTM_NEWADDR` блокировал чтение файлов и из рабочего каталога, и из `/tmp`. Решение — встраивание полного diff, PRD и artifacts непосредственно в текст промпта Codex (см. обновлённую инструкцию в `AGENTS.md` §5 шаг 8).
- Бюджет 2 review на PRD формально исчерпан первой парой, но содержательного прогона не было — третья попытка разовое исключение, задокументировано здесь.
- **Codex triage 2.11:**
  - defer (systemic, critical): `UpdateTask` не принимает actorId и не проверяет, что вызывающий имеет право менять задачу → **2.r2**
  - defer (systemic, critical): `ListProjectTasks` не проверяет membership вызывающего в проекте → **2.r2**
  - defer (systemic, critical): `ListUserTasks` позволяет запросить чужие задачи (нет `actorId == userId`) → **2.r2**
  - defer (systemic, critical): `ListTaskComments` не проверяет membership вызывающего в проекте задачи → **2.r2**
- Все 4 замечания сведены в одну системную задачу `2.r2 [review]`: требуется архитектурное решение по месту actor-based authorization (application-layer use cases vs controller/policy слой Laravel). Сейчас контроллеры отсутствуют, поэтому риск теоретический; вопрос должен быть закрыт до задач этапа 4–7 (Web/UI), где use cases начнут вызываться из HTTP-слоя.
- **Верт Codex:** CHANGES REQUESTED (строго по артефактному правилу "пользователь работает только со своими данными и проектами, в которых участвует"). Триаж переводит их в defer — scope 2.11 = только review, фактическое исправление — отдельная задача.
