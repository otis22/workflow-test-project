# Assumption Log

## 2026-03-29

- Roadmap was created from the four source artifacts in `artifacts/` and ordered as a single execution queue from environment setup to MVP hardening.
- Stage boundaries were chosen to keep work aligned with product slices: platform foundation, authentication, projects, tasks, collaboration, and release readiness.
- Review, smoke test, and changelog tasks were included explicitly in the roadmap because the agent workflow requires them at the end of each stage.
- The roadmap stays implementation-agnostic and contains only task names, short descriptions, and statuses, as required by `AGENTS.md`.
- Task 1.1 was implemented with Laravel 12 on PHP 8.4 as the initial application baseline, which satisfies the technical requirement of PHP 8.3+ and Laravel.
- Bootstrap verification was executed through the existing `taskflow-app` container because the host machine does not have local `php` or `composer`.
- The repository keeps workflow artifacts at the root and overlays the Laravel skeleton around them instead of replacing repository-level documentation files.
- Task 1.2 uses Docker Compose with three required services: `app` (PHP-FPM), `web` (Nginx), and `db` (PostgreSQL 17).
- The application container runs with host UID/GID mapping to keep Laravel writable directories compatible with the bind-mounted workspace during local development.
- The default landing page was simplified to avoid a false dependency on built Vite assets during infrastructure smoke tests.
- Task 1.3 uses Laravel Pint, PHPStan with Larastan, Rector, Infection, and Composer audit as the PHP quality baseline.
- Copy-paste detection was switched from abandoned `sebastian/phpcpd` to `jscpd`, because the former is incompatible with the current Symfony Console stack.
- Duplicate detection currently targets `app`, `tests`, and `routes`; framework-heavy config boilerplate is intentionally excluded to keep the signal focused on project-owned code.
