# Roadmap

| Stage / Task | Description | Status |
|---|---|---|
| Stage 1. Environment and skeleton | Prepare the Laravel project skeleton, local Docker Compose environment, and baseline CI quality pipeline. | done |
| Task 1.1 Bootstrap application | Create the base Laravel application structure and dependency management for TaskFlow. | done |
| Task 1.2 Prepare local environment | Add Docker Compose services for app, web server, and relational database for local development. | done |
| Task 1.3 Configure quality toolchain | Add linting, static analysis, coverage, duplication, rector, mutation, and dependency audit commands. | done |
| Task 1.4 Configure CI workflows | Add GitHub Actions workflows for the required checks and supporting pipelines. | done |
| Task 1.5 Review stage 1 | Review artifacts, architecture, CI stability, and documentation after the foundation stage. | done |
| Task 1.6 Smoke test stage 1 | Verify the application starts in Docker Compose and the main URLs respond correctly. | done |
| Task 1.7 Changelog stage 1 | Record the completed foundation work in `CHANGELOG.md`. | done |
| Stage 2. Authentication and shell UI | Deliver guest flows and the first authenticated application shell. | done |
| Task 2.1 Registration flow | Implement user registration with validation and automated coverage for the main path. | done |
| Task 2.2 Login and logout flow | Implement authentication session handling for sign-in and sign-out. | done |
| Task 2.3 Authenticated layout and dashboard shell | Add the first authenticated layout with navigation and dashboard entry point. | done |
| Task 2.4 E2E auth scenarios | Cover registration, login, and dashboard access with end-to-end tests. | done |
| Task 2.5 Review stage 2 | Review the authentication stage for security, architecture, and artifact accuracy. | done |
| Task 2.6 Smoke test stage 2 | Verify the authenticated shell works in the running application. | done |
| Task 2.7 Changelog stage 2 | Record the completed authentication work in `CHANGELOG.md`. | done |
| Stage 3. Projects | Deliver project creation and project membership foundations. | todo |
| Task 3.1 Model project domain | Implement project and project-member domain rules, including owner membership. | done |
| Task 3.2 Create project flow | Implement project creation for authenticated users. | done |
| Task 3.3 Project list page | Implement the list of projects available to the current user. | done |
| Task 3.4 Project navigation coverage | Add automated tests for creating and opening projects. | done |
| Task 3.5 Review stage 3 | Review the project stage for consistency with the domain model and UI spec. | done |
| Task 3.6 Smoke test stage 3 | Verify project creation and navigation in the running application. | done |
| Task 3.7 Changelog stage 3 | Record the completed project work in `CHANGELOG.md`. | todo |
| Stage 4. Tasks core | Deliver task creation, editing, assignment, and state management inside projects. | todo |
| Task 4.1 Model task domain | Implement task rules for project membership, assignee validity, status, priority, and deadlines. | todo |
| Task 4.2 Create task flow | Implement creating a task inside a project. | todo |
| Task 4.3 Edit task flow | Implement editing a task, including reassignment, status, priority, and deadline changes. | todo |
| Task 4.4 Project task list and filters | Implement the project task list with filtering by status and deadline. | todo |
| Task 4.5 Review stage 4 | Review the task stage for domain correctness, readability, and test strength. | todo |
| Task 4.6 Smoke test stage 4 | Verify core task workflows in the running application. | todo |
| Task 4.7 Changelog stage 4 | Record the completed task management work in `CHANGELOG.md`. | todo |
| Stage 5. Personal work view and comments | Deliver personal task visibility, task detail view, and discussion history. | todo |
| Task 5.1 My tasks dashboard section | Show tasks assigned to the current user and near-term deadlines on the dashboard. | todo |
| Task 5.2 Task detail page | Implement the task page with the main fields and activity context. | todo |
| Task 5.3 Commenting flow | Implement adding and viewing comments with project membership checks. | todo |
| Task 5.4 E2E task user journeys | Cover creating tasks, changing status, viewing personal tasks, and commenting with end-to-end tests. | todo |
| Task 5.5 Review stage 5 | Review the stage for workflow completeness, security, and artifact alignment. | todo |
| Task 5.6 Smoke test stage 5 | Verify the end-to-end MVP journey in the running application. | todo |
| Task 5.7 Changelog stage 5 | Record the completed MVP user workflow in `CHANGELOG.md`. | todo |
| Stage 6. Hardening and release readiness | Finalize quality gates, documentation, and readiness of the MVP baseline. | todo |
| Task 6.1 Coverage and mutation baseline | Lock the initial coverage gate and mutation baseline for domain and application layers. | todo |
| Task 6.2 Documentation and command audit | Ensure README and project artifacts describe the actual verification commands and workflows. | todo |
| Task 6.3 Final review | Run the required periodic review across artifacts, architecture, security, and dependencies. | todo |
| Task 6.4 Final smoke test | Verify the completed MVP boots and responds correctly through the main URLs. | todo |
| Task 6.5 Final changelog | Publish the cumulative MVP changelog in `CHANGELOG.md`. | todo |
