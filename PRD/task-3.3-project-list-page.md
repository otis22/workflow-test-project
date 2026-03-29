# PRD: Task 3.3 Project list page

## Goal

Implement the authenticated project list page as the main entry point to user-accessible projects.

## Decomposition

1. Add feature coverage for showing only projects available to the current user and opening a project from the list.
2. Expand the project controller with a project show endpoint guarded by membership.
3. Enrich the projects index with project summary cards and explicit entry links.
4. Add a minimal project page placeholder that becomes the future task hub for the selected project.
5. Run quality checks, update roadmap status, and record decisions in `AssumptionLog.md`.

## Acceptance Criteria

- The projects list shows only projects available to the current user.
- Users can open a project from the list.
- Non-members cannot open another user's project.
- The list page remains the main navigation entry for project workspaces.
