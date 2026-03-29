# PRD: Task 2.6 Smoke test stage 2

## Goal

Execute the Stage 2 smoke test and confirm the authenticated shell stage starts successfully in Docker Compose.

## Acceptance Criteria

- `docker compose up -d --build` completes successfully.
- The root URL responds successfully.
- Authentication routes remain registered in the running application.
- The smoke outcome is recorded in project artifacts.
