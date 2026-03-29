# 2026-03-29 — Этап 1. Базовое окружение и каркас проекта

## Допущения

- Для локальной разработки выбран Laravel 13 на PHP 8.4, так как стек из артефактов допускает совместимые стабильные минорные версии поверх требования `PHP 8.3+`.
- Для локальной базы данных выбран PostgreSQL как реляционная СУБД для dev и CI окружения.
- Для duplicate detection выбран `jscpd` как совместимый аналог `PHPCPD`, потому что `sebastian/phpcpd` не совместим с текущей связкой PHP 8.4 и PHPUnit 12.

## Неясности

- Полный mutation/e2e контур остаётся отдельной задачей следующих этапов roadmap и в этапе 1 не доводился до рабочего состояния.

## Архитектурные решения

- Локальная среда собрана из трёх сервисов: `app` (PHP-FPM + Composer + Node), `web` (Nginx) и `db` (PostgreSQL).
- Базовые quality checks вынесены в `composer` scripts и основной workflow `.github/workflows/ci.yml`.
- Для smoke test на этапе bootstrap корневой маршрут упрощён до текстового ответа, чтобы не зависеть от дальнейшей UI-реализации MVP.

## Итог review этапа

- Артефакты этапа синхронизированы: `Roadmap.md`, `PRD/001-stage-1-bootstrap.md`, `README.md`, `CHANGELOG.md`.
- Локальный smoke test зелёный: `docker compose up -d`, `curl -fsS http://localhost`.
- Локальный quality contour зелёный: `docker compose exec -T app composer qa:ci`.
