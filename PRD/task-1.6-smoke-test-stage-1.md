# PRD: Task 1.6 Smoke test stage 1

## Goal

Execute the stage 1 smoke test by starting the application stack and verifying the main URL responds successfully.

## Acceptance Criteria

- `docker compose up -d --build` completes successfully.
- The Laravel application is initialized in the app container.
- The root URL returns a successful HTTP response.
- Smoke test outcome is recorded in project artifacts.
