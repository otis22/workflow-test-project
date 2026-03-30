# PRD: Task 6.4 Final smoke test

## Goal

Verify that the completed MVP still boots cleanly in Docker Compose and that the main public and guest-entry URLs respond correctly before the final changelog is published.

## Acceptance Criteria

- The local Docker Compose stack starts successfully from the current `codex` branch state.
- The Laravel application can be prepared in the containers without errors.
- The main smoke URLs return the expected response classes for the finished MVP baseline.
- The smoke result is recorded in `AssumptionLog.md`, and `Roadmap.md` marks the task as completed.
