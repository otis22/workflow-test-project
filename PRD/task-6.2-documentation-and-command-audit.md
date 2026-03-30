# PRD: Task 6.2 Documentation and command audit

## Goal

Ensure the repository documentation and workflow artifacts describe the current verification commands, local setup steps, and GitHub Actions pipelines exactly as they exist in the MVP codebase.

## Acceptance Criteria

- `README.md` documents the current local bootstrap steps required to run the MVP, including database migration and frontend dependency installation.
- `README.md` lists the maintained verification commands using the actual `composer` scripts, including the current coverage and mutation baselines.
- `README.md` explains which GitHub Actions workflows gate the project and what each workflow verifies.
- The task result and any documentation-related decisions are recorded in `AssumptionLog.md`.
