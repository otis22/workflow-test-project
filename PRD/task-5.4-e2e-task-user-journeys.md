# PRD: Task 5.4 E2E task user journeys

## Goal

Cover the MVP task-management journey with an end-to-end request-driven scenario that proves a user can move from authentication and project work into personal task visibility, task updates, and commenting.

## Acceptance Criteria

- The E2E suite includes a task-focused user journey in addition to the existing auth and project journeys.
- The scenario covers creating a project task, viewing it on the personal dashboard, changing its status, opening the task detail page, and adding a comment.
- The scenario executes through real HTTP requests and assertions against the rendered responses, matching the current server-rendered MVP approach.
- The dedicated E2E command and the full quality command both pass with the new scenario enabled.
