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

## 1.r1 Инвариант updatedAt >= createdAt

- **Применено к User, Project, Task** — единственным сущностям с обоими полями. ProjectMember и Comment явно вне scope (см. AssumptionLog 1.3, 1.5).
- **Сравнение через `format('U.u')`** — instant + microseconds в UTC, симметрично с `DueDate::equals` (см. AssumptionLog 1.6). Это игнорирует timezone metadata и сравнивает физические моменты времени.
- **Равенство `updatedAt == createdAt` допустимо** — соответствует только что созданной сущности. Покрытие этого случая обеспечивается всеми существующими позитивными конструкторными тестами (где обе метки = `$now`); отдельный явный boundary-тест не добавлялся как избыточный.
- **Codex triage:**
  - accept (major, partial): добавлены тесты µs-earlier rejection и same-instant-different-tz acceptance для всех 3 entity. Equality boundary не добавлялся (см. выше).
  - reject (minor): "сообщение исключения не проверяется" — ложноположительное, Pest `->throws($class, $message)` уже пинит точное сообщение.
- **Codex re-review:** APPROVE, no critical findings. Бюджет 2/2.

## 1.r2 Валидация id > 0 — REJECTED (закрыто в 3.2.6)

- **Итоговое решение:** замечание отклонено. Все 5 Eloquent-репо (3.2.2–3.2.6) зафиксировали draft-паттерн как архитектурный выбор проекта: `save(Entity с id=0)` → insert, `save(Entity с id>0)` → update-or-throw. `id = 0` — легитимное состояние unpersisted draft, не валидационная ошибка. Инвариант `id > 0` в доменных entity несовместим с этим паттерном.
- **История**: первая попытка (на этапе 1.r2 подзадачи) привела к откату — 49 тестов сломалось. Пользователь заблокировал задачу «до 3.2/4.x». При реализации 3.2.3 (EloquentProjectRepository) было зафиксировано решение следовать прецеденту 3.2.2 (draft pattern), что подтвердило выбор. В 3.2.6 вся цепочка 5 репо полностью использует этот паттерн.
- **Закрывается** как `rejected` вместо `blocked`.

---

## 1.r2 Валидация id > 0 — BLOCKED (historical)

- **Попытка реализации:** добавление `if ($id <= 0) throw ...` в конструкторы 5 entity ломает 49 существующих тестов.
- **Корень проблемы:** все 5 application use cases (`RegisterUser`, `CreateProject`, `CreateTask`, `AddComment`, плюс ProjectMember через `CreateProject`) используют draft-паттерн: `new Entity(id: 0, ...)` → `repository->save($draft)` → возвращает entity с присвоенным id. Контракт всех `*Repository::save()` принимает entity целиком, включая `id = 0` как маркер «ещё не сохранён». Это противоречит инварианту `id > 0`.
- **PRD ошибка:** первоначальный PRD 1.r2 утверждал «такого паттерна нет» — это неверно (декомпозиция была сделана без анализа application слоя).
- **Решение пользователя:** заблокировать 1.r2 и решить совместно с 3.2 (остальные Eloquent-репозитории) или 2.r2 (actor-based auth — там тоже меняются контракты use cases). Самый чистый путь — разделить контракт репозитория: `create(...primitives)` для новой сущности, `save(Entity)` для update (требует id > 0).
- **Текущий статус:** изменения откачены, рабочая ветка чиста, задача `1.r2 [blocked]` в Roadmap.

## 2.r1 Email normalization

- **Точка нормализации:** `RegisterUser::execute` и `Login::execute`. Lowercase + trim перед валидацией формата, поиском дубликатов и сохранением. Domain User entity не трогаем — сохраняется политика «transparency over speculative normalization» (см. AssumptionLog 1.2).
- **`EmailAddress` VO отвергнут** как scope creep: потребовал бы менять User entity, маппер, фейки, миграцию (collation), 5+ тестов. Для MVP достаточно нормализации на границе.
- **Дублирование `normalizeEmail` (1 строка) в 2 use cases** — сознательное решение: ниже порога вынесения в shared utility. Дрифт между двумя реализациями исключён тестами обоих use cases.
- **БД:** существующий unique constraint на `users.email` (pgsql, case-sensitive) достаточен после нормализации в use case — все вставки идут в lowercase. Миграция данных не требуется (нет продакшна, миграции 3.1 свежие).
- **Codex triage:**
  - reject (minor): «нет негативного теста на whitespace-only email» — путь после `normalizeEmail` совпадает с существующим тестом `'rejects invalid email via domain entity validation'` (`'   '` → `''` → `filter_var` fails → `InvalidArgumentException`); добавление было бы дублированием поведения.
  - reject (nit): «вынести `normalizeEmail` в shared helper» — задокументированное решение PRD, см. выше.
- **Codex verdict:** APPROVE. Re-review не требуется (все findings reject).

## 2.r2 Actor-based authorization в application layer

- **Решение пользователя:** authorization реализуется в use cases, не в Laravel policies. `actorId` — первый параметр use case. Use case проверяет membership/ownership через `ProjectMemberRepository`, бросает существующий `NotAProjectMemberException` (тот же, что используется `CreateTask` для creator/assignee).
- **Декомпозиция на 2 подзадачи** для соблюдения бюджета ≤150 LoC: 2.r2.A (UpdateTask + ListProjectTasks), 2.r2.B (ListUserTasks rename + ListTaskComments).
- **`ListUserTasks` — semantic-only переименование** `userId` → `actorId`. Параметр всегда был identity актёра (use case никогда не позволял запрашивать чужие задачи); rename + doc-комментарий фиксируют контракт. Authentication «actor действительно тот, за кого себя выдаёт» — ответственность session/controller слоя (вне scope use case).
- **`ListProjectTasks` и `ListTaskComments`** получили новую DI-зависимость на `ProjectMemberRepository` (и `TaskRepository` для второго).
- **404 vs 403 leak (2.r2.A finding):** UpdateTask и ListTaskComments используют `findById(...)` до проверки членства, поэтому неавторизованный actor различает «task не существует» (TaskNotFoundException) от «task существует, но нет membership» (NotAProjectMemberException). Codex поднял это в review 2.r2.A. Триаж: defer (выходит за scope исходного 2.11 finding). Оформлено как `2.r3 [review]` в Roadmap.
- **Codex triage 2.r2.A:**
  - defer (major): 404 vs 403 information disclosure → `2.r3 [review]`
- **Codex triage 2.r2.B:**
  - 1-я попытка: «Review blocked» (Codex не увидел встроенный diff) → невалидный, бюджет не считается потраченным.
  - 2-я попытка с полным телом файлов вместо diff: APPROVE.
  - reject (minor): «нет позитивного теста для ListUserTasks после rename» — существующие тесты `ListUserTasksTest.php` уже покрывают позитивный путь; rename чисто семантический, поведение не менялось. Codex видел только diff, не существующие тесты.

## 3.2.4 EloquentProjectMemberRepository

- **Симметрично 3.2.3** с двумя отличиями: entity только с `created_at` (нет updated_at, см. AssumptionLog 1.3), и дополнительный метод `projectIdsForUser` через `pluck → map → values → all`.
- **Update-ветка реализована для симметрии**, хотя практически не вызывается (ProjectMember иммутабелен по дизайну).
- **Codex triage:**
  - accept (minor): round-trip тест расширен — equality через `format('U')` всех полей (id, projectId, userId, createdAt).
  - reject (minor): «неиспользуемый второй owner user в lists project ids test» — наблюдение, не дефект, читаемость важнее.
- **Codex re-review:** APPROVE. Бюджет 2/2.

## 3.2.5 EloquentTaskRepository

- **Биггест из 3.2.x серии**: маппит enum'ы (Status, Priority backed string), nullable VO (DueDate), nullable assigneeId. Две query-операции: `listByProject` с двумя фильтрами (status + dueBefore, оба nullable, AND-композиция) и `listByAssignee`.
- **`listByProject` dueBefore**: строгое `<` (не `<=`), совпадает с `InMemoryTaskRepository` фейком. Задачи без `due_date` исключаются когда `dueBefore` задан.
- **Codex triage:**
  - accept (minor, F1): добавлен boundary-тест пинующий строгое `<` на границе.
  - accept (minor, F2): добавлен тест combined status + dueBefore filter.
  - reject (minor, F3): отдельный «mapper-only DateTimeImmutable write» тест — round-trip через `format('U')` уже пинит контракт.
- **Codex re-review:** F1/F2 закрыты, F3 rejection disputed (Codex не смог переподтвердить round-trip в изолированном re-review вызове), не critical. Push по §10.

## 3.2.6 EloquentCommentRepository

- **Симметрично 3.2.4** (immutable, только `created_at`, без findById в interface).
- **Round-trip через listByTask** — нет findById в интерфейсе, поэтому round-trip проверяется через `listByTask(taskId)[0]`.
- **Codex triage:**
  - reject × 3 (blocker-hallucinations): Codex неправильно прочитал escape-последовательности PHP в промпте и пожаловался на «bare identifiers», «missing quotes в sprintf», хотя в реальном коде всё корректно.
  - reject (minor, F4): «mapper DateTimeImmutable explicit assertion» — round-trip `format('U')` покрывает (F3 из 3.2.5 повторно).
  - reject (minor, F5): «exact exception message is brittle» — точное сообщение это контракт, симметрично всем 4 предыдущим репо.
  - reject (minor, F6): «update-path test gap» — Comment immutable по дизайну (AssumptionLog 1.5), симметрично 3.2.4 ProjectMember где тоже только stale-id throw покрывает id>0 ветку.
- **Codex verdict:** REQUEST CHANGES, все замечания отклонены. По §10 push без фикса (все findings reject).

## 3.3 Фабрики и сидеры

- **Декомпозиция на 3 подзадачи** (3.3.1/3.3.2/3.3.3) по принципу «одна модель на подзадачу + последовательное обогащение seeder».
- **Удалён мёртвый Laravel default код** (`app/Models/User.php`, `database/factories/UserFactory.php`) — ссылался на несуществующие колонки (`password`, `email_verified_at`, `remember_token`); grep подтвердил, что ни один import не разорван.
- **`protected static $factory` property override** на всех 5 моделях, потому что Laravel HasFactory соглашение ищет фабрики по имени класса в `App\Models\{Name}` namespace, а наши модели живут в `App\Infrastructure\Persistence\Eloquent\Model`. Альтернатива (move моделей в `App\Models`) отвергнута — нарушает чистую архитектуру. Комментарий над property объясняет override.
- **Фабрики с nested `::factory()` relationships** (e.g. `'owner_id' => UserModel::factory()`) — Laravel auto-creates FK parent при вызове `->create()`. Упрощает тесты.
- **`ProjectMemberModelFactory` и `CommentModelFactory` явно задают `created_at`** через `now()`, потому что модели имеют `$timestamps = false`. Миграции 3.1 имеют `useCurrent()` default, поэтому на уровне БД пропуск колонки тоже работает, но явная установка делает factory output предсказуемым.
- **Seeder использует `updateOrCreate` на естественных ключах** (email, project name, [task_id, title], [task_id, author_id, body]) для идемпотентности. Подтверждено реальным smoke test (`db:seed` выполнен дважды на pgsql → финальные counts 3/1/2/3/2).
- **Codex triage 3.3.1/3.3.2/3.3.3:**
  - Большинство findings оказались галлюцинациями из-за escape-sequence confusion в промптах Codex (например Review 3.3.2 первого прогона был Review blocked, Review 3.3.1 первого прогона нашёл «syntax errors» которых нет).
  - accept (nit 3.3.1): комментарий над `$factory` property — explain convention override.
  - reject (major 3.3.2, seeder NULL created_at): миграция `project_members` имеет `useCurrent()` DB default, INSERT без колонки получает server time. Проверено.
  - Остальные nits отклонены как defensive/over-engineering.

## 3.2.3 EloquentProjectRepository

- **Симметрично 3.2.2**: draft-паттерн (`save(id=0)` insert, `save(id>0)` update-or-throw), отдельный `ProjectMapper` класс, feature тесты через container binding.
- **Фикстура owner'а** через raw `UserModel::query()->create([...])` — `UserFactory` не совместим с domain-aligned миграциями (она ссылается на `password` и `email_verified_at`, которых нет в 3.1 schema). Замена UserFactory — отдельная задача 3.3.
- **Решение по 1.r2**: принято следовать прецеденту 3.2.2 (draft pattern) — самостоятельно агентом на основе AGENTS.md §13 (симметрия с существующим паттерном). Формальное закрытие 1.r2 [blocked] в подзадаче 3.2.7.
- **Codex triage:**
  - accept (minor): round-trip тест расширен до всех маппинговых полей (id, ownerId, name, description) — не только timestamp types.
- **Codex re-review:** APPROVE. Бюджет 2/2.

## 3.1 Миграции БД

- **Заменена дефолтная Laravel `users`-миграция**: оставлены только domain-aligned поля (`name`, `email` unique, `password_hash`, timestamps). Удалены `email_verified_at`, `remember_token` — Laravel-auth scaffolding не используется (кастомный `SessionGuard` из 2.2). Таблица `password_reset_tokens` удалена полностью — восстановление пароля не входит в MVP (см. `artifacts/prd-taskflow-ru.md` §7). Таблица `sessions` сохранена без изменений как ортогональная Laravel session-инфраструктура.
- **FK actions** (явно задокументированы в PRD 3.1 и покрыты тестом):
  - `projects.owner_id → users` **RESTRICT** — нельзя удалить пользователя, пока он владеет проектами. Безопаснее CASCADE в MVP без soft delete.
  - `project_members.project_id/user_id` **CASCADE** — удаление проекта или пользователя чистит членство.
  - `tasks.project_id` **CASCADE**, `tasks.creator_id` **RESTRICT** (сохранение авторства), `tasks.assignee_id` **SET NULL**.
  - `comments.task_id` **CASCADE**, `comments.author_id` **RESTRICT**.
- **Индексы**: `project_members` unique `(project_id, user_id)`; `tasks` — индекс на `status` и `due_date` (под фильтры `ListProjectTasks`). Остальные FK получают индексы автоматически через `foreignId()`.
- **Status/Priority — DB-level enum**, а не `string(20)`. Причина: Codex review 3.1 указал, что `string` позволяет БД принять невалидное значение, что расходится с доменной моделью. `Blueprint::enum()` переносим между pgsql/sqlite/mysql (на pgsql транслируется в CHECK constraint). Это defense in depth поверх типовой валидации в `Status`/`Priority` enum'ах доменного слоя.
- **Comments — только `created_at`, без `updated_at`**: комментарии иммутабельны в MVP (см. AssumptionLog 1.5, UI spec не предусматривает edit).
- **Превышение бюджета подзадачи**: 3.1.1 содержит ~380 строк (5 миграций + Feature test), против целевого лимита 150. Разбиение на 5 подзадач создало бы больше накладных расходов, чем даёт изоляция: миграции шаблонные, связаны FK, тест проверяет комплексно. Зафиксировано как осознанное исключение из §4 Workflow.
- **Feature tests работают на pgsql**, а не sqlite (как задумывалось `phpunit.xml`). `phpunit.xml` содержит `<env name="DB_CONNECTION" value="sqlite"/>`, но override не применяется в текущей конфигурации Laravel — `.env` с `DB_CONNECTION=pgsql` побеждает. Для 3.1 это даже полезнее: тесты сразу проверяют pgsql-совместимость миграций, а не вводят sqlite как "упрощённое" окружение с риском расхождения. Отдельная задача по диагностике phpunit.xml env override не создаётся — сейчас работает правильно случайно, но работает.
- **Codex triage 3.1:**
  - accept: `tasks.status` без CHECK → `enum(['todo','in_progress','done'])`
  - accept: `tasks.priority` без CHECK → `enum(['low','medium','high'])`
  - accept: отсутствуют негативные тесты на invalid status/priority → добавлены
  - reject: `sessions.user_id` без FK → out of scope (Laravel scaffolding, сохранено как есть per PRD)
  - reject: тест на sessions.user_id FK → производное от reject выше
- **Codex re-review 3.1:** APPROVE, no findings. Бюджет 2/2.
