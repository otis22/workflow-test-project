# PRD: Repeat review follow-ups

## Goal

Run an additional mandatory review pass after roadmap completion and place any newly discovered problems back into the shared roadmap queue as explicit `[review]` tasks.

## Acceptance Criteria

- The repeated review checks artifacts, architecture, security, CI/infrastructure, and dependency health against `AGENTS.md`.
- Any newly discovered implementation or process gaps are added to `Roadmap.md` as new `[review]` tasks with `todo` status.
- The repeated review result and the created follow-up tasks are recorded in `AssumptionLog.md`.
