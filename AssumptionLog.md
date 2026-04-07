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
