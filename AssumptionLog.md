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
- Task 1.4 uses a main `ci.yml` workflow plus separate `smoke.yml`, `mutation.yml`, and `e2e.yml` workflows to match the technical requirements for additional pipelines.
- The current e2e workflow is wired to the feature test suite as a temporary executable placeholder until browser-level scenarios are implemented later in the roadmap.
- Stage 1 review confirmed that the implemented foundation still matches the source artifacts: Laravel on PHP 8.3+, Docker Compose local environment, PostgreSQL, and explicit quality gates in CI.
- The review found one documentation gap: `README.md` did not yet describe the actual local bootstrap and verification commands, so it was updated immediately within the review task.
- Stage 1 dependency review was executed with `composer audit`; no known security advisories were reported at the time of review.
- Stage 1 smoke validation was executed with `docker compose up -d --build` and `curl -I http://127.0.0.1/`; the stack started successfully and the root URL returned `HTTP/1.1 200 OK`.
- `CHANGELOG.md` was introduced at the end of Stage 1 because the workflow requires a stage-level progress log in the repository root.
- Task 2.1 implements registration with a small application action (`App\Application\Auth\RegisterUser`) so user creation is not embedded in the web controller.
- A minimal authenticated `/dashboard` page was added in Task 2.1 as the required post-registration destination; Task 2.3 will expand it into the real application shell instead of replacing the route.
- Test commands were made explicit with `APP_ENV=testing` and in-memory SQLite overrides because local containerized development keeps a Postgres `.env`, and stable automated tests must not depend on the developer database state.
- Task 2.2 implements login and logout directly with Laravel session auth; the first session controller stays framework-thin while credential validation and attempt throttling live in `LoginUserRequest`.
- The login request uses Laravel rate limiting on the email/IP throttle key to keep the MVP auth flow safer without introducing extra infrastructure.
