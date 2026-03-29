# PRD: Task 2.4 E2E auth scenarios

## Goal

Provide dedicated end-to-end style coverage for the authentication journey: registration, login, dashboard access, and logout.

## Decomposition

1. Add a dedicated `E2E` PHPUnit suite separate from the broader feature suite.
2. Add an auth journey test that exercises registration, logout, login, and dashboard access in sequence.
3. Point the `test:e2e` Composer script and the GitHub Actions e2e workflow to the dedicated suite.
4. Keep the implementation browserless for now, while making the scope explicit and narrow.
5. Run quality checks, update roadmap status, and record the decision in `AssumptionLog.md`.

## Acceptance Criteria

- A dedicated `E2E` testsuite exists in the repository.
- The e2e suite covers registration, dashboard access, logout, and login.
- The e2e workflow runs the dedicated suite instead of the full feature suite.
