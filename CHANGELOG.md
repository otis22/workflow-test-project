# Changelog

## Stage 1. Environment and skeleton

- Bootstrapped the Laravel 12 application baseline for TaskFlow.
- Added Docker Compose services for PHP-FPM, Nginx, and PostgreSQL.
- Configured the baseline quality toolchain with Pint, PHPStan, Rector, coverage, duplication detection, mutation testing, and dependency audit commands.
- Added GitHub Actions workflows for CI, smoke checks, mutation checks, and the temporary e2e pipeline.
- Reviewed the stage deliverables, updated the project README with the actual bootstrap and verification commands, and verified the stack with a smoke test.

## Stage 2. Authentication and shell UI

- Added guest registration with validation, automatic sign-in, and redirect into the authenticated area.
- Added login and logout flows with session auth and request-level throttling for invalid credentials.
- Introduced the first authenticated application shell with dashboard navigation and placeholder sections for projects, personal work, and deadlines.
- Split auth journey coverage into dedicated feature tests and a dedicated `E2E` testsuite wired into GitHub Actions.
- Reviewed the stage deliverables, updated the README with the dedicated e2e command, and revalidated the running stack with a smoke test.

## Stage 3. Projects

- Added the project domain model with explicit ownership and membership tables plus automatic owner membership on creation.
- Implemented authenticated project creation with validation and a dedicated application action reused by the web layer.
- Added the projects index and project workspace entry points, filtered to projects available through membership.
- Expanded coverage with dedicated feature scenarios and a project navigation e2e journey for create and open flows.
- Reviewed the stage against the source artifacts, updated the README to reflect the current MVP progress, and revalidated the running stack with a stage smoke test.

## Stage 4. Tasks core

- Added the task domain model with project ownership, creator and assignee relationships, MVP statuses and priorities, and due-date handling.
- Implemented project-scoped task creation and editing with shared form handling, reassignment, status updates, priority updates, and deadline changes.
- Turned the project workspace into the main project task list with server-rendered filters for task status and deadline state.
- Expanded automated coverage across task domain rules, create and edit flows, and project task-list filtering scenarios.
- Reviewed the stage against the source artifacts, updated the README to reflect task-management progress, and revalidated the running stack with a dedicated stage smoke test.

## Stage 5. Personal work view and comments

- Turned the dashboard into a personal work overview with assigned-task snapshots, near-term deadlines, and quick project links.
- Added a dedicated task detail page with the main task fields, project context, and direct navigation from the dashboard and project workspace.
- Implemented task comments with project-membership checks, chronological discussion history, and request-level validation on the task page.
- Expanded end-to-end coverage with a dedicated task workflow journey that exercises project creation, task creation, dashboard visibility, status changes, and commenting.
- Reviewed the stage against the source artifacts, fixed documentation drift, removed CI Node 20 deprecation warnings by upgrading official GitHub Actions majors, and revalidated the running stack with a stage smoke test.

## Stage 6. Hardening and release readiness

- Ratcheted the coverage gate to `94%` and aligned mutation testing to the domain and application slice with an enforced CI baseline of `89` MSI / covered MSI.
- Audited the repository documentation so `README.md` now reflects the real local bootstrap flow, maintained verification commands, current quality baselines, and the four gating GitHub Actions workflows.
- Completed the final review across artifacts, architecture, authorization, CI stability, and dependency health with no additional implementation follow-up tasks required.
- Revalidated the MVP on a clean Docker Compose volume with fresh dependency installation, successful migrations, and successful smoke responses for the public and guest-entry routes.
- Closed the MVP roadmap with all planned stages and review follow-ups completed in the `codex` branch baseline.

## Stage 7. Review follow-ups

- Expanded smoke automation so the shared smoke command and GitHub Actions workflow verify `/`, `/login`, `/register`, `/dashboard`, and `/projects` instead of only the root page.
- Removed unnecessary application-service refresh and eager-loading steps, added direct persistence-mapping coverage for `TaskData`, and ratcheted the scoped mutation floor back to `100/100`.
- Added direct unit coverage for login throttling normalization, lockout, failure-hit, and success-clear branches, bringing `LoginUserRequest` coverage to `100%`.
