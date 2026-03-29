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
