# PRD: Task 7.2 [review] Kill escaped application-layer mutants

## Goal

Eliminate the remaining escaped Infection mutants in the application layer by removing unnecessary mutable behavior and strengthening direct unit coverage where persistence mapping is still meaningful.

## Acceptance Criteria

- The application services no longer contain unnecessary state refresh or eager-loading that is not required by the current web flow.
- Direct unit coverage exists for persistence mapping in `TaskData`.
- Local mutation testing passes without the previously reported escaped mutants in the application-layer slice.
- The task result is recorded in `AssumptionLog.md`, and `Roadmap.md` marks the task as completed.
