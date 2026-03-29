# TaskFlow

TaskFlow is a Laravel-based demo project for a strict agent workflow. The repository combines product artifacts with an executable application bootstrap so the roadmap can be implemented top-down without breaking the process described in `AGENTS.md`.

## Project Artifacts

- `AGENTS.md` defines the mandatory workflow for roadmap execution.
- `Roadmap.md` is the only queue of work.
- `PRD/` stores per-task PRD documents and decomposition.
- `AssumptionLog.md` records assumptions and architectural decisions.
- `artifacts/` contains the source product, technical, domain, and UI documents.

## Local Start

1. Create the local environment file:

   ```bash
   cp .env.example .env
   ```

2. Build and start the stack:

   ```bash
   docker compose up -d --build
   ```

3. Install PHP and frontend dependencies inside the app container:

   ```bash
   docker compose exec app composer install
   docker compose exec app npm install
   ```

4. Generate the app key and run migrations:

   ```bash
   docker compose exec app php artisan key:generate
   docker compose exec app php artisan migrate
   ```

5. Build frontend assets:

   ```bash
   docker compose exec app npm run build
   ```

6. Open the application:

   `http://localhost`

## Quality Commands

Run commands inside the `app` container:

```bash
docker compose exec app composer test
docker compose exec app composer test:coverage
docker compose exec app composer lint
docker compose exec app composer analyse
docker compose exec app composer rector
docker compose exec app composer cpd
docker compose exec app composer audit:deps
docker compose exec app composer qa
docker compose exec app composer qa:ci
```

## Smoke Check

```bash
docker compose up -d --build
curl -fsS http://localhost
```

The expected response body is:

```text
TaskFlow bootstrap ready
```
