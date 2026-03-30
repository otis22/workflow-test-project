# PRD: Task 5.3 Commenting flow

## Goal

Extend the task detail page with the MVP commenting flow so project members can leave discussion history directly on a task.

## Acceptance Criteria

- A project member can add a comment from the task detail page.
- Submitted comments are persisted with the task and comment author.
- The task detail page shows existing comments in chronological order.
- A non-member cannot submit a comment for a task in another project.
- Comment submission validates the body and redisplays errors on invalid input.
- Automated tests cover the main comment creation path, validation, membership protection, and the application-level membership rule.
