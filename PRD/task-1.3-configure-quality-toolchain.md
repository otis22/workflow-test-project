# PRD: Task 1.3 Configure quality toolchain

## Goal

Add the required quality tooling and stable project commands so the repository can enforce formatting, static analysis, duplication detection, coverage, mutation testing, and dependency audit checks locally and later in CI.

## Scope

- Add the missing development dependencies for the required quality tools.
- Introduce project-level configuration for static analysis, Rector, and mutation testing.
- Add consistent Composer scripts for quality checks.
- Keep the baseline Laravel application passing tests inside Docker Compose.

## Out of Scope

- GitHub Actions workflows.
- End-to-end browser tests.
- Product feature implementation.

## Decomposition

1. Capture the red state by verifying the quality scripts are not yet defined.
2. Add development dependencies for analysis, refactoring, duplication detection, and mutation testing.
3. Add configuration files and Composer scripts for the required checks.
4. Run the new quality commands that are feasible at the current project stage and fix baseline issues.
5. Update project artifacts to reflect the completed toolchain task.

## Acceptance Criteria

- Composer scripts exist for linting, static analysis, duplication detection, Rector dry-run, mutation testing, coverage gate, and dependency audit.
- Required tool configuration files are committed.
- The baseline test suite and the feasible quality commands pass in the Docker environment.
- The task status and assumptions are recorded in the project artifacts.
