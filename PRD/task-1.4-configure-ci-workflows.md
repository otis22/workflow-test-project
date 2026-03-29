# PRD: Task 1.4 Configure CI workflows

## Goal

Add GitHub Actions workflows that execute the project quality gates and supporting checks required by the technical requirements.

## Scope

- Add the main CI workflow for formatting, static analysis, unit and feature tests with coverage gate, Rector dry-run, duplicate detection, and dependency audit.
- Add separate workflows for smoke tests, mutation tests, and the e2e placeholder path required by the roadmap.
- Reuse the project Docker and Composer scripts instead of duplicating logic in CI.
- Keep the workflows compatible with the current Laravel and Docker baseline.

## Out of Scope

- Implementing the e2e test suite itself.
- Production deployment or CD.
- Feature development outside CI/infrastructure.

## Decomposition

1. Capture the red state by verifying that no GitHub Actions workflows exist.
2. Add the main CI workflow for the mandatory push/PR checks.
3. Add dedicated workflows for smoke tests, mutation testing, and e2e execution scaffolding.
4. Add repository commands or placeholder scripts needed to keep the workflows executable at the current project stage.
5. Validate the workflow YAML and project commands locally where feasible.

## Acceptance Criteria

- `.github/workflows/` contains the required CI workflows.
- The main workflow runs the agreed project quality commands.
- Smoke testing is represented as a dedicated workflow that uses Docker Compose.
- Mutation and e2e checks exist as dedicated workflows and are wired to executable project commands.
