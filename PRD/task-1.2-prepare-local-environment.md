# PRD: Task 1.2 Prepare local environment

## Goal

Add a reproducible Docker Compose setup for local development with application, web server, and relational database services.

## Scope

- Create Docker Compose configuration for `app`, `web`, and `db`.
- Add the PHP application image definition with the required extensions and Composer.
- Add the Nginx virtual host configuration for Laravel public entrypoint routing.
- Align Laravel environment defaults with PostgreSQL-based local development.
- Verify the stack boots successfully and the root URL responds.

## Out of Scope

- CI workflow definitions.
- Quality tool installation beyond what is necessary for the application container.
- Feature implementation beyond what is required for a successful smoke test.

## Decomposition

1. Capture the red state by validating that no compose configuration exists.
2. Add Docker assets for the PHP application image and Nginx configuration.
3. Add `docker-compose.yml` with app, web, and database services plus persistent database storage.
4. Adjust Laravel defaults needed for the stack smoke test.
5. Bring the stack up and verify the main URL responds successfully.

## Acceptance Criteria

- `docker compose config` succeeds.
- `docker compose up -d --build` starts the required services without errors.
- The root URL served through Nginx returns a successful HTTP response.
- Local database defaults target PostgreSQL in the containerized environment.
