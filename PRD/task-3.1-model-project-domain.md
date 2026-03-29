# PRD: Task 3.1 Model project domain

## Goal

Implement the foundational project domain model, including project ownership and project membership with automatic owner participation.

## Decomposition

1. Add unit coverage for project creation and automatic owner membership.
2. Add database schema for projects and project members.
3. Add Eloquent models and relationships for `Project` and `ProjectMember`.
4. Add an application action that creates a project and records the owner as a member.
5. Run quality checks, update roadmap status, and record decisions in `AssumptionLog.md`.

## Acceptance Criteria

- A project has an owner, name, and optional description.
- Project membership is stored separately from the project record.
- Creating a project automatically creates a membership row for the owner.
- Automated tests cover the owner-membership rule.
