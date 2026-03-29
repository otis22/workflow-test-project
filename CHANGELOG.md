# CHANGELOG

## 2026-03-29 — Stage 1

- Bootstrapped a Laravel 13 application in the repository root.
- Added Docker Compose services for PHP-FPM, Nginx, and PostgreSQL.
- Added documented local commands for setup, smoke checks, and quality gates.
- Added baseline quality tooling for formatting, coverage, static analysis, rector, duplicate detection, and dependency audit.
- Added the primary GitHub Actions workflow for quality checks.

## 2026-03-29 — Stage 2

- Added explicit `Domain`, `Application`, and `Infrastructure` layers.
- Implemented pure domain entities and rules for user accounts, projects, membership, tasks, and comments.
- Added application services and repository contracts for project creation, task creation, and comment creation.
- Added in-memory infrastructure adapters for isolated application tests.
- Added unit tests for domain invariants and application services with coverage above the required threshold.
