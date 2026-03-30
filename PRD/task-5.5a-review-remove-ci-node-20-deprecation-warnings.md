# PRD: Task 5.5a [review] Remove CI Node 20 deprecation warnings

## Goal

Update the GitHub Actions workflows so the current CI pipelines no longer emit the Node 20 deprecation warnings discovered during the Stage 5 review.

## Acceptance Criteria

- The workflows use supported official GitHub Actions versions for checkout and Node setup.
- The CI, E2E, Mutation, and Smoke workflows remain functionally unchanged apart from the warning-removal update.
- Local verification covers workflow syntax or at least a targeted diff review plus a full pipeline run after push.
- The task result and decision are recorded in `AssumptionLog.md`.
