# PRD: Task 4.3 Edit task flow

## Goal

Implement editing an existing task inside a project so project members can change the main task fields, including reassignment, status, priority, and due date.

## Acceptance Criteria

- A project member can open an edit form for an existing project task.
- A project member can update title, description, status, priority, due date, and assignee.
- The update flow preserves the task domain rule that the assignee, when present, must belong to the project.
- Non-members cannot access the edit form or submit task updates.
- Automated tests cover the main update path and access restrictions.
