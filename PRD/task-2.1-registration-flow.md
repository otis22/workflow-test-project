# PRD: Task 2.1 Registration flow

## Goal

Implement guest registration for TaskFlow with server-rendered UI, validation, automatic sign-in, and redirect to the authenticated entry point.

## Decomposition

1. Add feature coverage for the guest registration page, successful registration flow, and validation errors.
2. Add an application action for creating a user and isolate user creation from the controller.
3. Add web request validation, guest routes, and controller endpoints for registration.
4. Add minimal guest and authenticated views required for the registration flow.
5. Run quality checks, update roadmap status, and record decisions in `AssumptionLog.md`.

## Acceptance Criteria

- Guests can open the registration page.
- Valid registration creates a user, starts a session, and redirects to the dashboard route.
- Invalid registration returns validation errors and does not create a user.
- The main path is covered by automated tests.
