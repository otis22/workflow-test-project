# PRD: Task 2.2 Login and logout flow

## Goal

Implement session-based login and logout for TaskFlow guests and authenticated users.

## Decomposition

1. Add feature coverage for the login page, successful login, invalid credentials, and logout.
2. Add request validation for login credentials and encapsulate session authentication in a dedicated controller.
3. Add guest/auth routes and minimal UI for login and logout entry points.
4. Update the authenticated dashboard view with a logout action.
5. Run quality checks, update roadmap status, and record decisions in `AssumptionLog.md`.

## Acceptance Criteria

- Guests can open the login page.
- Valid credentials authenticate the user and redirect to the dashboard.
- Invalid credentials return a validation error and keep the user unauthenticated.
- Authenticated users can log out and are redirected to the login page.
