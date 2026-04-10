<?php

declare(strict_types=1);

use Laravel\Dusk\Browser;

test('8.2 — register a new user, login, and land on dashboard', function (): void {
    $this->browse(function (Browser $browser): void {
        $browser->visit('/register')
            ->type('name', 'E2E Alice')
            ->type('email', 'e2e-alice@example.com')
            ->type('password', 'super-secret')
            ->type('password_confirmation', 'super-secret')
            ->press('Register')
            ->assertPathIs('/dashboard')
            ->assertSee('Dashboard')
            ->assertSee('E2E Alice');
    });
});

test('8.3 — create a project and see it in the projects list', function (): void {
    $this->browse(function (Browser $browser): void {
        // Register + login
        $browser->visit('/register')
            ->type('name', 'E2E Bob')
            ->type('email', 'e2e-bob@example.com')
            ->type('password', 'super-secret')
            ->type('password_confirmation', 'super-secret')
            ->press('Register')
            ->assertPathIs('/dashboard');

        // Create project
        $browser->visit('/projects/create')
            ->type('name', 'E2E Project')
            ->type('description', 'E2E project description')
            ->press('Create project')
            ->assertPathIs('/projects')
            ->assertSee('E2E Project');
    });
});

test('8.4 — create a task and see it in the project task list', function (): void {
    $this->browse(function (Browser $browser): void {
        // Register + login
        $browser->visit('/register')
            ->type('name', 'E2E Charlie')
            ->type('email', 'e2e-charlie@example.com')
            ->type('password', 'super-secret')
            ->type('password_confirmation', 'super-secret')
            ->press('Register');

        // Create project
        $browser->visit('/projects/create')
            ->type('name', 'E2E Task Project')
            ->press('Create project');

        // Navigate to project (click the project name link)
        $browser->clickLink('E2E Task Project');

        // Create task
        $browser->clickLink('Create task')
            ->type('title', 'E2E Task')
            ->type('description', 'E2E task description')
            ->press('Create task')
            ->assertSee('E2E Task');
    });
});

test('8.5 — change the status of a task and verify the new status is displayed', function (): void {
    $this->browse(function (Browser $browser): void {
        // Register + login
        $browser->visit('/register')
            ->type('name', 'E2E Dave')
            ->type('email', 'e2e-dave@example.com')
            ->type('password', 'super-secret')
            ->type('password_confirmation', 'super-secret')
            ->press('Register');

        // Create project + task
        $browser->visit('/projects/create')
            ->type('name', 'Status Project')
            ->press('Create project');
        $browser->clickLink('Status Project')
            ->clickLink('Create task')
            ->type('title', 'Change my status')
            ->press('Create task');

        // Edit task — change status to Done
        $browser->clickLink('Change my status')
            ->clickLink('Edit')
            ->select('status', 'done')
            ->press('Save changes')
            ->assertSee('done');
    });
});

test('8.6 — assigned tasks appear on the dashboard', function (): void {
    $this->browse(function (Browser $browser): void {
        // Register + login
        $browser->visit('/register')
            ->type('name', 'E2E Eve')
            ->type('email', 'e2e-eve@example.com')
            ->type('password', 'super-secret')
            ->type('password_confirmation', 'super-secret')
            ->press('Register');

        // Create project + task (assignee = null by default from the form)
        $browser->visit('/projects/create')
            ->type('name', 'Dashboard Project')
            ->press('Create project');
        $browser->clickLink('Dashboard Project')
            ->clickLink('Create task')
            ->type('title', 'Dashboard visible task')
            ->press('Create task');

        // The task is NOT assigned to anyone, so dashboard should not show it.
        $browser->visit('/dashboard')
            ->assertDontSee('Dashboard visible task');

        // Note: since we can't set assignee via the web form (deferred),
        // this test verifies the negative case: unassigned tasks don't
        // appear on dashboard. The positive case (assigned task shows)
        // requires the assignee feature which is in backlog.
    });
});
