# PRD: Task 7.3 [review] Cover login throttling branches directly

## Goal

Add direct automated coverage for the remaining login throttling branches so the request object is exercised beyond the main feature-path scenarios.

## Acceptance Criteria

- Automated tests directly cover the `throttleKey()` normalization behavior.
- Automated tests directly cover the rate-limited lockout branch.
- Automated tests directly cover failed-auth hit behavior and successful-auth clear behavior.
- The task result is recorded in `AssumptionLog.md`, `Roadmap.md` marks the task as completed, and Stage 7 is closed if no tasks remain.
