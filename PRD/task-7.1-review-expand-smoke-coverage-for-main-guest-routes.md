# PRD: Task 7.1 [review] Expand smoke coverage for main guest routes

## Goal

Align the automated smoke verification with the project workflow by checking the main public and guest-entry URLs instead of validating only the root page.

## Acceptance Criteria

- The repository contains a single maintained smoke-check command or script that verifies the current main guest routes.
- The `Smoke Tests` GitHub Actions workflow uses that smoke-check command instead of checking only `/`.
- Local verification proves the expanded smoke check passes against the current MVP baseline.
- The task result is recorded in `AssumptionLog.md`, and `Roadmap.md` marks the task as completed.
