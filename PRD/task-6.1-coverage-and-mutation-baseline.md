# PRD: Task 6.1 Coverage and mutation baseline

## Goal

Lock the current automated coverage and mutation-testing baseline so future changes cannot silently reduce the quality floor already achieved by the MVP codebase.

## Acceptance Criteria

- The coverage gate is raised from the temporary bootstrap threshold to a ratcheted baseline based on the current measured project coverage.
- The mutation test command enforces the current measured covered-MSI baseline instead of allowing `0`.
- The CI workflows continue to pass with the new thresholds.
- The measured baseline and ratchet decision are recorded in `AssumptionLog.md`.
