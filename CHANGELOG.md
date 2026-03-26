# Changelog

## MVP (Stages 1–8)

### Stage 1: Infrastructure
- Laravel 13.2.0 on PHP 8.3 with PostgreSQL 16
- Docker Compose (app, web, db)
- Quality tools: Pint, PHPStan, Rector, PHPCPD, Infection, composer audit
- CI pipeline: GitHub Actions with all quality checks
- Mutation and smoke test workflows

### Stage 2: Domain Layer
- Domain entities: User, Email (value object), Project, Task, Comment
- Enums: TaskStatus (todo/in_progress/done), TaskPriority (low/medium/high)
- Business rules: creator/assignee/commenter must be project member
- 25 unit tests for domain layer

### Stage 3: Authentication
- Registration, login, logout
- Blade UI: register, login forms, layout with nav
- Guest/auth middleware on routes

### Stage 4: Projects
- Create project with auto-membership for owner
- Project list filtered by membership
- Project detail page with task list
- Membership authorization on project routes

### Stage 5: Tasks
- Full CRUD: create, view, edit tasks
- Status change, priority, due date, assignee
- Membership check on assignee (must be project member)
- Status filter on project page

### Stage 6: Comments
- Add comments on task page
- Membership check on comment author
- Comment history display

### Stage 7: Dashboard & Filtering
- Dashboard: my tasks, upcoming deadlines, project links
- Due date filters on project page (upcoming/overdue)

### Stage 8: Quality & Finalization
- Mutation testing: MSI 91%, threshold 90%
- Domain coverage: 97%+
- All 6 E2E scenarios covered
- Smoke test verified
- Periodic reviews with security fixes
