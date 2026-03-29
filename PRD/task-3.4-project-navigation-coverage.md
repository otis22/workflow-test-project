# PRD: Task 3.4 Project navigation coverage

## Goal

Add dedicated automated coverage for the main project navigation path: authenticate, open projects, create a project, and enter the project workspace.

## Decomposition

1. Add a dedicated e2e-style project navigation journey test.
2. Cover login, projects index access, project creation, and project workspace entry in one scenario.
3. Keep the current feature tests focused on individual screens and use the e2e suite for the end-to-end navigation path.
4. Run quality checks, update roadmap status, and record decisions in `AssumptionLog.md`.

## Acceptance Criteria

- A dedicated project navigation journey exists in the e2e suite.
- The journey covers opening the projects list, creating a project, and opening the project workspace.
- Existing e2e and feature suites continue to pass.
