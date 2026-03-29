# PRD: Task 2.3 Authenticated layout and dashboard shell

## Goal

Add the first authenticated application shell with navigation and a dashboard entry point that matches the MVP UI direction.

## Decomposition

1. Add feature coverage for the authenticated dashboard shell and guest protection on the dashboard route.
2. Introduce a dedicated authenticated layout with primary navigation and a session/logout area.
3. Expand the dashboard page into a real shell entry point with placeholder sections for projects, assigned work, and deadlines.
4. Keep existing auth flows intact and reuse the same dashboard route.
5. Run quality checks, update roadmap status, and record decisions in `AssumptionLog.md`.

## Acceptance Criteria

- Guests are redirected to login when opening the dashboard route.
- Authenticated users see a dashboard shell with primary navigation and their identity.
- The dashboard includes placeholder sections for personal work, deadlines, and projects.
- Automated tests cover the authenticated shell entry point.
