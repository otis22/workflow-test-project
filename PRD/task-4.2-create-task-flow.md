# PRD: Task 4.2 Create task flow

## Goal

Implement task creation inside a project for authenticated project members, including the server-rendered creation form, validation, and persistence through the task domain rules introduced in Task 4.1.

## Acceptance Criteria

- A project member can open a task creation form from the project workspace.
- A project member can create a task with title, description, status, priority, due date, and optional assignee.
- The task creator is set to the authenticated user and the assignee, when present, must belong to the project.
- Non-members cannot access the task creation form or submit a task for the project.
- Automated tests cover the main create-task path and access restrictions.
