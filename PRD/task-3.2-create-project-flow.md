# PRD: Task 3.2 Create project flow

## Goal

Implement authenticated project creation for TaskFlow users.

## Decomposition

1. Add feature coverage for the project creation form, successful project creation, and guest protection.
2. Add request validation and a controller that uses the existing `CreateProject` application action.
3. Add server-rendered views for the project creation form and a minimal projects index destination.
4. Wire authenticated routes and shell navigation to the new project flow.
5. Run quality checks, update roadmap status, and record decisions in `AssumptionLog.md`.

## Acceptance Criteria

- Authenticated users can open the create-project form.
- Valid submission creates a project and redirects to the projects list.
- The authenticated creator becomes the owner and a member of the created project.
- Guests cannot access the project creation endpoints.
