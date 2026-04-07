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
