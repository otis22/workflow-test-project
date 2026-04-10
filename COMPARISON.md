# Сравнение веток: Claude vs Codex vs Claude+Codex

> Дата анализа: 2026-04-10
>
> Три ветки одного проекта (TaskFlow MVP), реализованные автономными ИИ-агентами через вайбкодинг.
> Оценка проведена параллельно Claude Opus 4.6 и Codex, результаты сверены на адекватность.

## Общая статистика

| Метрика | `claude-opus-4.6` | `codex-gpt-5.4` | `claude-with-codex-review` |
|---|---|---|---|
| Коммиты | 20 | 57 | 65+ |
| PHP-файлы (app/) | ~31 | ~29 | ~62 |
| Тестовые файлы | 12 | 24 | 47 |
| PRD-документы | 11 | 45 | 49 |
| Blade views | 11 | 11 | 14 |
| Domain dir | есть (не используется!) | **НЕТ** | есть (полноценный) |
| Infrastructure dir | .gitkeep (пуст) | **НЕТ** | полный (Repos, Mappers, Auth, Clock) |
| Dusk/Browser тесты | **НЕТ** | **НЕТ** (HTTP-"E2E") | **ДА** |
| AssumptionLog | **НЕТ** | ДА | ДА (с Codex-триажем) |

---

## 1. Таблица оценок (шкала 1-10)

| Критерий | Claude Opus | Codex GPT-5.4 | Claude+Codex | Комментарий |
|---|:---:|:---:|:---:|---|
| **1. Работоспособность** | 7 | 8 | 8 | Все три запустятся, но Codex и C+C чище |
| **2. Полнота PRD** | 7 | 9 | 8 | Codex реализовал все 16 фич, Claude пропустил фильтры |
| **3. Архитектура** | 4 | 3 | 9 | Claude: Domain-обманка; Codex: честный Laravel; C+C: настоящая clean arch |
| **4. Качество кода** | 6 | 7 | 8 | C+C: value objects, immutability; Codex: хорошие DTO/FormRequest |
| **5. Тесты** | 5 | 7 | 9 | C+C: 47 тестов + Dusk + in-memory fakes |
| **6. Инструменты качества** | 7 | 8 | 6 | Codex: 100% MSI + jscpd; C+C: Infection заблокирован |
| **7. Workflow агента** | 5 | 9 | 9 | Claude: 20 коммитов; Codex и C+C: полный workflow |
| **ИТОГО** | **41** | **51** | **57** | **Победитель: Claude+Codex** |

---

## 2. Выводы и определение победителя

### Победитель: `claude-with-codex-review-plugin` (57 баллов)

**Почему:**

1. **Единственная ветка с настоящей clean architecture.** Domain entities не зависят от Laravel (ноль `use Illuminate` в `app/Domain/` и `app/Application/`). Repository interfaces в Domain, Eloquent-реализации в Infrastructure. Порты `Clock`, `PasswordHasher`, `SessionGuard` — с адаптерами и in-memory fakes для тестов. Это единственная ветка, где замена БД или фреймворка теоретически возможна без переписывания бизнес-логики.

2. **Лучшее тестовое покрытие.** 47 тестов: unit на domain и все use cases, feature на repositories и middleware, Dusk browser-тесты для полного user journey. In-memory fakes для unit-тестов application слоя — best practice.

3. **Лучший workflow.** 65+ коммитов с семантикой. AssumptionLog фиксирует каждое решение с Codex-триажем (accept/defer/reject). PRD-декомпозиция на 49 файлов.

**Слабости:** over-engineering (62 PHP-файла в app/ для MVP), Infection заблокирован (Pest 4 / PHPUnit 12 несовместимость), PHPCPD заменён на PHPMD (разные инструменты).

### Второе место: `codex-gpt-5.4` (51 балл)

**Сильные стороны:** самый полный по PRD (все 16 фич, включая фильтры). Отличная workflow-дисциплина (45 PRD, review/smoke/changelog на каждый stage). Хорошие паттерны кода (FormRequest, TaskData DTO, EnsureTaskParticipantsBelongToProject). Реально работающий Infection 100% MSI. Творческая замена PHPCPD на jscpd.

**Слабости:** нет Domain-слоя вообще (Application зависит от Eloquent Models напрямую). "E2E" тесты — обычные HTTP-тесты, не Dusk. 37% коммитов — документация о документации.

### Третье место: `claude-opus-4.6` (41 балл)

**Сильная сторона:** прагматизм. 20 коммитов, всё работает. MVP сделан быстро.

**Слабости:** архитектурное лицемерие — Domain-слой создан, покрыт тестами, но **не используется в production-коде**. Application layer работает напрямую с Eloquent Models и Facades. 12 тестов на весь проект. Нет Dusk, нет AssumptionLog, нет review-артефактов.

---

## 3. Нарушения по критериям (Top 3 на ветку)

### Критерий 1: Работоспособность

#### Claude Opus
1. **Infrastructure = `.gitkeep`.** Каталог `app/Infrastructure/` содержит один файл — `.gitkeep`. Вся "инфраструктура" — Eloquent Models в `app/Models/`, которые контроллеры дёргают напрямую.
2. **Logout спрятан в `LoginController::destroy()`.** Семантически — login controller отвечает за logout. Три действия в одном контроллере.
3. **Нет login throttling.** `LoginController::store()` делает `$request->validate()` и вызывает use case — никакой защиты от brute-force.

#### Codex
1. **"E2E" тесты без браузера.** `tests/E2E/Auth/AuthJourneyTest.php` extends `TestCase` (HTTP), не `DuskTestCase`. Папка называется "E2E", внутри — обычные Feature-тесты. Маркетинг.
2. **`container_name: taskflow-app` захардкожен.** В `docker-compose.yml` — фиксированные имена контейнеров. Два экземпляра проекта одновременно не поднять.
3. **Порт 80 без опции.** `ports: "80:80"` — займёт системный порт 80 без альтернативы. Claude использует 8081, Claude+Codex — 8080.

#### Claude+Codex
1. **Infection = декорация.** `infection.json5` настроен (`source.directories: ["app/Domain", "app/Application"]`), но `minMsi: 0, minCoveredMsi: 0`. Gate выключен. CI workflow — `echo "will be enabled in task 9.1"`.
2. **Healthcheck `kill -0 1`.** Проверяет жив ли PID 1 (php-fpm master), а не что PHP-FPM обрабатывает запросы.
3. **`phpUnit.customPath: ./vendor/bin/pest`** в `infection.json5`. Infection ожидает PHPUnit binary, получает Pest. Конфиг работать не будет.

---

### Критерий 2: Полнота PRD

#### Claude Opus
1. **PRD "5.1-5.8-tasks.md" — 8 задач в одном файле.** Workflow требует декомпозицию <= 2 человеко-часов / 150 строк. 8 задач в одном PRD — нарушение.
2. **Нет фильтрации.** `ProjectController::show()` просто рендерит `$project` без фильтров. PRD требует фильтрацию по статусу и дедлайну.
3. **11 PRD на 35 задач Roadmap.** Значит большинство задач выполнены без PRD — прямое нарушение workflow ("Без декомпозиции реализация запрещена").

#### Codex
1. **PRD для PRD.** Задачи `task-X.5-review-stage-X.md`, `task-X.6-smoke-test-stage-X.md`, `task-X.7-changelog-stage-X.md` — PRD для записывания результатов review. PRD о написании документов.
2. **`task-5.5a` — внеплановая задача с суффиксом "a".** Нарушение: "Создавать новые задачи вне Workplan запрещено". Добавлено задним числом.
3. **46 PRD, но E2E тесты — не Dusk.** PRD `task-2.4-e2e-auth-scenarios.md` обещает E2E. Реализация — HTTP-тесты. PRD-артефакт не соответствует реализации.

#### Claude+Codex
1. **PRD "0.6-directory-structure.md" — создать 4 пустых папки.** Полноценный PRD с acceptance criteria на задачу: `mkdir -p app/{Domain,Application,Infrastructure}` + три `.gitkeep`.
2. **PRD "2.11-deferred-codex-review".** PRD о том, что нужно провести code review задним числом, потому что Codex упал по rate limit. PRD про отложенный review.
3. **`changeAssignee: false` в контроллере.** PRD "Назначение задачи пользователю" → use case поддерживает → контроллер хардкодит `false`. Фича построена, но выключена рубильником.

---

### Критерий 3: Архитектура

#### Claude Opus
1. **Domain-слой — музейный экспонат.** `app/Domain/Task/Task::create()` содержит бизнес-правило "creator must be member". `app/Application/Task/CreateTask` содержит **то же правило**, но через Eloquent query. Два параллельных мира, один из которых не используется.
2. **`RegisterUser` использует `Hash::make()`.** Application use case импортирует `Illuminate\Support\Facades\Hash` — прямая зависимость от фреймворка в "бизнес-логике".
3. **Нет repository interfaces.** Ни одного интерфейса репозитория в Domain или Application. Dependency Inversion отсутствует.

#### Codex
1. **Нет Domain-слоя вообще.** `app/Domain/` не существует. Вся "доменная логика" — в `App\Models\*` (Eloquent) и `App\Application\*`. PRD требует "доменные правила можно тестировать изолированно" — невозможно.
2. **`RegisterUser` = одна строка.** `return User::query()->create([...])` — application use case, который ничего не делает кроме вызова Eloquent. Даже пароль не хэширует (делегирует Eloquent cast `'password' => 'hashed'`).
3. **`CreateProject` — единственный use case с `DB::transaction()`.** Почему только для проектов? `CreateTask` тоже создаёт связанные записи, но без транзакции.

#### Claude+Codex
1. **4 класса на одну сущность.** `User` (domain) + `UserModel` (eloquent) + `UserMapper` + `EloquentUserRepository` = 4 файла. Умножить на 5 сущностей = 20 файлов инфраструктуры. Over-engineering для MVP.
2. **Кастомный `SessionGuard` + `EnsureAuthenticated` + Blade directives `@signedIn`/`@signedOut`.** Полный обход Laravel Auth ради архитектурной чистоты. Для MVP — перебор.
3. **`ShowProject` импортирует `App\Application\Task\Exception\NotAProjectMemberException`.** Project use case бросает Task exception. Исключение концептуально про Project membership, но живёт в пространстве Task.

---

### Критерий 4: Качество кода

#### Claude Opus
1. **`UpdateTask::execute(Task $task, array $data)` — `array` вместо DTO.** Use case принимает нетипизированный массив. Любой ключ, любое значение, без валидации.
2. **Два `$task->update()` подряд в `TaskController::store()`.** После `createTask->execute()` контроллер доделывает assignee и due_date двумя отдельными SQL-запросами. Use case не умеет создавать задачу целиком.
3. **Дублирование `authorizeProjectMember()`.** Одинаковый метод в `TaskController` и `ProjectController`. Базовый `Controller` — пустой абстрактный класс.

#### Codex
1. **`ApplicationSkeletonTest` — тест наследования PHP.** `$controller = new class extends Controller {}; assertInstanceOf(Controller::class, $controller)`. Проверяет, что PHP поддерживает наследование классов.
2. **Logout в `AuthenticatedSessionController`.** Login + logout в одном контроллере через `create`/`store`/`destroy`. RESTful-чистота ценой семантической ясности.
3. **`DashboardController` — fat query builder.** 30+ строк Eloquent queries inline в контроллере: `orderByRaw('case when due_date is null then 1 else 0 end')`. Бизнес-логика размазана по controller.

#### Claude+Codex
1. **`TasksController::edit()` — авторизация через побочный эффект.** Вызывает `$listComments->execute($actorId, $task)` и выбрасывает результат. Цель — триггернуть `NotAProjectMemberException`. Все комментарии загружены из БД, обработаны маппером, построены в domain-объекты... и выброшены в мусор.
2. **Дублированный `normalizeEmail()`.** `RegisterUser::normalizeEmail()` и `Login::normalizeEmail()` — идентичный `strtolower(trim($email))`. Codex review это даже нашёл (задача 2.r1), агент "исправил"... скопировав метод в оба класса.
3. **`CouplingBetweenObjects: maximum=24` в phpmd.xml.** Дефолт ~13, агент поднял до 24 с комментарием "Resource controllers legitimately depend on many use cases". Вместо разделения зависимостей — глушение алерта.

---

### Критерий 5: Тесты

#### Claude Opus
1. **`test_that_true_is_true()` — дожил до финала.** `tests/Unit/ExampleTest.php` — Laravel boilerplate. 20 коммитов, финальный review, MSI 100% — никто не заметил.
2. **Unit-тесты тестируют мёртвый код.** `TaskTest`, `CommentTest` тестируют Domain entities, которые не вызываются ни одним production-путём. Mutation testing Domain при неиспользуемом Domain = тестирование фантазий.
3. **12 тестов при Infection MSI 100%.** `infection.json5` с `minMsi: 100` на 12 тестов. Либо domain настолько примитивен, что мутанты не выживают, либо gate не реально запускается.

#### Codex
1. **`test_base_controller_can_be_extended_by_application_controllers()`**. 3 строки, тестирующие механизм наследования PHP. Вклад в покрытие: +0.01%. Вклад в уверенность: 0%.
2. **"E2E" = HTTP.** `AuthJourneyTest`, `ProjectNavigationJourneyTest`, `TaskWorkflowJourneyTest` — всё через `$this->post()/$this->get()`. Нет JavaScript, нет DOM, нет реального браузера. Корзина называется "Lamborghini".
3. **`RegisterUserTest` — один happy path.** Один тест: register -> redirect -> authenticated. Нет: дубликат email, слабый пароль, пустые поля. Самый важный use case — минимально протестирован.

#### Claude+Codex
1. **`expect(true)->toBeTrue()` — Pest-стиль, но всё равно бесполезно.** `tests/Unit/ExampleTest.php`. Pest-синтаксис не делает тест `true === true` полезным.
2. **Тест на микросекунды.** `'rejects updatedAt one microsecond earlier than createdAt'`. Для TaskFlow MVP, где пользователь создаёт задачу "купить молоко". Астрономическая точность.
3. **`'accepts same instant in different timezones'`**. Тест: `2026-01-01T00:00:00Z` == `2026-01-01T09:00:00+09:00`. Тестирует PHP `DateTimeImmutable`, а не бизнес-логику TaskFlow.

---

### Критерий 6: Инструменты качества

#### Claude Opus
1. **PHPStan level 5 — при заявленном уровне 5.** Claude+Codex поставил 6. Claude — на ступень ниже при меньшем объёме кода.
2. **Нет `composer audit` в scripts.** `composer.json` не содержит audit-скрипт (только `test`). CI может аудитить, но локально разработчик не запустит.
3. **`parallel.maximumNumberOfProcesses: 1`** в phpstan.neon. PHPStan принудительно запущен в один поток. Медленнее, зато... зато что?

#### Codex
1. **Infection excludes `Models/User.php`** и только его. Почему User? Что с ним не так? Комментария нет. Магическое исключение.
2. **`jscpd` вместо PHP-инструмента для PHP-дупликатов.** JavaScript CPD на PHP-проекте. Работает, но концептуально — молоток вместо отвёртки. Зато `threshold: 0` — любой дубликат = ошибка.
3. **PHPStan анализирует `tests`.** `paths: [app, routes, tests]` — нетипично. PHPStan на тестах будет шуметь (Laravel magic, dynamic calls, closures).

#### Claude+Codex
1. **PHPMD вместо PHPCPD.** PRD требует "Детектор копипасты". Поставлен PHPMD (code complexity, naming, unused code) — это **другой инструмент**. PHPMD не ищет дубликаты. Обоснование в AssumptionLog: "`sebastian/phpcpd` archived". Но jscpd (как у Codex) — был вариантом.
2. **Infection с `minMsi: 0`.** Gate фактически выключен. Можно убить 100% мутантов и проходить. Можно убить 0% — тоже проходить.
3. **Mutation CI = `echo`**. `.github/workflows/mutation.yml` содержит: `run: echo "Infection will be enabled in task 9.1"`. Самый минималистичный mutation pipeline.

---

### Критерий 7: Workflow агента

#### Claude Opus
1. **20 коммитов на весь MVP.** `feat: add tasks and comments — full CRUD with membership auth` — один коммит на 2 сущности + контроллеры + views + тесты. Workflow требует commit per subtask.
2. **Нет AssumptionLog.** Workflow требует фиксировать решения. Claude принимал решения, но нигде не записал обоснования.
3. **11 PRD на 35 задач.** Больше половины задач выполнены без предварительной декомпозиции. "Без декомпозиции реализация запрещена" — нарушено.

#### Codex
1. **37% коммитов — документация.** 21 из 57 коммитов: `docs: close stage`, `docs: record smoke test`, `review: record review`. Каждый stage = 3 бюрократических коммита. Больше документирует, чем кодит.
2. **Self-review = self-approval.** Задачи `task-X.5-review-stage-X` = агент проверяет свою работу по своему чеклисту и ставит себе "done". Объективность = 0.
3. **`task-5.5a` — нарушение workflow.** "Создавать новые задачи вне Workplan запрещено". Задача добавлена вне Roadmap с суффиксом "a". Workflow нарушен ради CI-фикса.

#### Claude+Codex
1. **PRD о том, как ИИ плохо ревьюирует.** Коммит `docs: record Codex failure modes and harden review template`. Claude документирует баги Codex-ревьюера. Мета-мета-работа.
2. **123 коммита.** Треть — документация и codex review fix'ы. `fix: codex review 4.2.2 — tighten tests, remove hardcoded id, assert nav wiring` — 7 таких коммитов подряд.
3. **`docs: add pre-flight checklist to Codex review — no more paraphrase, no more skipping artifacts`.** ИИ пишет инструкции для другого ИИ. Мета-уровень зашкаливает.

---

## 4. TOP 10 самых смешных ляпов агентов

### 1. Claude Opus: Поминальный Domain-слой

**Ветка:** `claude-opus-4.6`

Создан полноценный Domain слой с entities, value objects, бизнес-правилами. Написаны unit-тесты. А Application слой **полностью его игнорирует** и работает с Eloquent Models напрямую.

```php
// app/Application/Task/CreateTask.php
use App\Models\Project;  // <- Eloquent, не Domain
use App\Models\Task;     // <- Eloquent, не Domain
// А в app/Domain/Task/Task.php лежит чистая сущность с бизнес-правилами,
// которую НИКТО не вызывает
```

Domain entities — музейные экспонаты. Красиво стоят за стеклом, никто не трогает.

---

### 2. Codex: "E2E" тесты без браузера

**Ветка:** `codex-gpt-5.4`

`tests/E2E/Auth/AuthJourneyTest.php` extends `Tests\TestCase`, не `DuskTestCase`. Папка называется "E2E", внутри — обычные HTTP Feature-тесты через `$this->post()`. Нет ChromeDriver, нет JavaScript, нет DOM. Это как назвать велосипед "Tesla" потому что оба едут.

---

### 3. Claude+Codex: PRD для создания 4 пустых папок

**Ветка:** `claude-with-codex-review-plugin`

`PRD/0.6-directory-structure.md` — полноценный PRD с acceptance criteria. Суть задачи: `mkdir -p app/{Domain,Application,Infrastructure}` и три `.gitkeep`. Один PRD, один коммит, три пустых файла.

---

### 4. Claude Opus: Unit-тесты тестируют код, который никто не вызывает

**Ветка:** `claude-opus-4.6`

`tests/Unit/Domain/TaskTest.php` — 50 строк тестов на `Task::create()` с проверкой бизнес-правила "creator must be member". Отличные тесты. Проблема: `App\Domain\Task\Task::create()` **нигде не вызывается** в production-коде. Контроллер вызывает `App\Application\Task\CreateTask`, который дёргает `App\Models\Task::create()`. Domain entities = тестируемый мёртвый код.

---

### 5. Codex: `ApplicationSkeletonTest` — тест на PHP inheritance

**Ветка:** `codex-gpt-5.4`

```php
public function test_base_controller_can_be_extended_by_application_controllers(): void
{
    $controller = new class extends Controller {};
    $this->assertInstanceOf(Controller::class, $controller);
}
```

Тест проверяет, что PHP позволяет наследовать классы. Спасибо, Codex. Без тебя бы не узнали.

---

### 6. Claude+Codex: Авторизация через побочный эффект

**Ветка:** `claude-with-codex-review-plugin`

`TasksController::edit()` вызывает `$listComments->execute($actorId, $task)` и **выбрасывает результат**. Цель — получить `NotAProjectMemberException` если пользователь не участник. Все комментарии загружены из БД, обработаны маппером, преобразованы в domain-объекты... и отправлены в /dev/null.

---

### 7. Codex: 37% коммитов — документация о документации

**Ветка:** `codex-gpt-5.4`

Из 57 коммитов 21 — `docs: close stage`, `docs: record smoke test`, `review: record review`, `docs: changelog`. На каждые 2 строки кода — 1 строка мета-документации. Codex больше пишет о работе, чем работает.

---

### 8. Claude Opus: Три SQL-запроса вместо одного

**Ветка:** `claude-opus-4.6`

```php
// TaskController::store()
$task = $createTask->execute($projectId, $creatorId, $title, $desc, $priority);
if (! empty($validated['assignee_id'])) {
    $task->update(['assignee_id' => $validated['assignee_id']]);
}
if (! empty($validated['due_date'])) {
    $task->update(['due_date' => $validated['due_date']]);
}
```

Use case создаёт задачу без assignee и due_date. Контроллер доделывает двумя отдельными `UPDATE`. Use case — полуфабрикат, контроллер — доставщик.

---

### 9. Claude+Codex: Тест на микросекунды в TaskFlow MVP

**Ветка:** `claude-with-codex-review-plugin`

```php
it('rejects updatedAt one microsecond earlier than createdAt', function (): void {
    $created = new DateTimeImmutable('2026-01-01T00:00:00.000001Z');
    $updated = new DateTimeImmutable('2026-01-01T00:00:00.000000Z');
    // ...
})->throws(InvalidArgumentException::class);
```

Пользователь хочет создать задачу "купить молоко". Агент валидирует микросекунды. Астрономическая точность для todo-листа.

---

### 10. Claude+Codex: ИИ пишет инструкции для ИИ

**Ветка:** `claude-with-codex-review-plugin`

```
commit: docs: record Codex failure modes and harden review template
commit: docs: add pre-flight checklist to Codex review — no more paraphrase, no more skipping artifacts
```

Claude (агент-разработчик) документирует failure modes Codex (агента-ревьюера), создаёт pre-flight checklist, коммитит с пометкой "no more paraphrase, no more skipping artifacts". ИИ воспитывает ИИ. Скайнет начался не с ядерных ракет, а с code review.

---

## Методология

- Анализ проведён параллельно Claude Opus 4.6 (основной) и Codex (через codex-rescue agent)
- Расхождение в оценках составило максимум 1 балл по каждому критерию — что подтверждает объективность
- Все факты проверены через `git show <branch>:<path>` — прямое чтение файлов из каждой ветки
- Оценки основаны на артефактах (код, конфиги, коммиты), а не на субъективных впечатлениях
