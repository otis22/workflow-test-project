# PRD: Task 4.1 Model task domain

## Goal

Introduce the task domain model for TaskFlow, including task storage, relationships, allowed statuses and priorities, due-date handling, and reusable membership rules for creator and assignee validation.

## Acceptance Criteria

- The application has a `Task` model and database table aligned with the source domain artifact.
- `Project` and `User` expose the relationships needed for project tasks, created tasks, and assigned tasks.
- The task domain defines the MVP statuses and priorities in a reusable way.
- A reusable domain-level guard validates that the task creator belongs to the project and that the assignee, when present, also belongs to the project.
- Unit tests cover the new relationships and the membership rules.
