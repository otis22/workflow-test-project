<?php

declare(strict_types=1);

use Laravel\Dusk\Browser;

test('8.2 — register a new user, login, and land on dashboard', function (): void {
    $this->browse(function (Browser $browser): void {
        $browser->visit('/register')
            ->waitFor('#name')
            ->type('#name', 'E2E Alice')
            ->type('#email', 'e2e-alice@example.com')
            ->type('#password', 'super-secret')
            ->type('#password_confirmation', 'super-secret')
            ->press('Register')
            ->waitForLocation('/dashboard')
            ->assertSee('Dashboard');
    });
});

test('8.3 — create a project and see it in the projects list', function (): void {
    $this->browse(function (Browser $browser): void {
        $browser->visit('/register')
            ->waitFor('#name')
            ->type('#name', 'E2E Bob')
            ->type('#email', 'e2e-bob@example.com')
            ->type('#password', 'super-secret')
            ->type('#password_confirmation', 'super-secret')
            ->press('Register')
            ->waitForLocation('/dashboard');

        $browser->visit('/projects/create')
            ->waitFor('#name')
            ->type('#name', 'E2E Project')
            ->type('#description', 'E2E project description')
            ->press('Create project')
            ->waitForLocation('/projects')
            ->assertSee('E2E Project');
    });
});

test('8.4 — create a task and see it in the project task list', function (): void {
    $this->browse(function (Browser $browser): void {
        $browser->visit('/register')
            ->waitFor('#name')
            ->type('#name', 'E2E Charlie')
            ->type('#email', 'e2e-charlie@example.com')
            ->type('#password', 'super-secret')
            ->type('#password_confirmation', 'super-secret')
            ->press('Register')
            ->waitForLocation('/dashboard');

        $browser->visit('/projects/create')
            ->waitFor('#name')
            ->type('#name', 'E2E Task Project')
            ->press('Create project')
            ->waitForLocation('/projects');

        $browser->clickLink('E2E Task Project')
            ->waitForText('E2E Task Project');

        $browser->clickLink('Create task')
            ->waitFor('#title')
            ->type('#title', 'E2E Task')
            ->type('#description', 'E2E task description')
            ->press('Create task')
            ->waitForText('E2E Task')
            ->assertSee('E2E Task');
    });
});

test('8.5 — change the status of a task and verify the new status is displayed', function (): void {
    $this->browse(function (Browser $browser): void {
        $browser->visit('/register')
            ->waitFor('#name')
            ->type('#name', 'E2E Dave')
            ->type('#email', 'e2e-dave@example.com')
            ->type('#password', 'super-secret')
            ->type('#password_confirmation', 'super-secret')
            ->press('Register')
            ->waitForLocation('/dashboard');

        $browser->visit('/projects/create')
            ->waitFor('#name')
            ->type('#name', 'Status Project')
            ->press('Create project')
            ->waitForLocation('/projects');

        $browser->clickLink('Status Project')
            ->waitForText('Status Project')
            ->clickLink('Create task')
            ->waitFor('#title')
            ->type('#title', 'Change my status')
            ->press('Create task')
            ->waitForText('Change my status');

        $browser->clickLink('Change my status')
            ->waitForText('Change my status')
            ->clickLink('Edit')
            ->waitFor('#status')
            ->select('#status', 'done')
            ->press('Save changes')
            ->waitForText('done')
            ->assertSee('done');
    });
});

test('8.6 — unassigned tasks do not appear on the dashboard', function (): void {
    $this->browse(function (Browser $browser): void {
        $browser->visit('/register')
            ->waitFor('#name')
            ->type('#name', 'E2E Eve')
            ->type('#email', 'e2e-eve@example.com')
            ->type('#password', 'super-secret')
            ->type('#password_confirmation', 'super-secret')
            ->press('Register')
            ->waitForLocation('/dashboard');

        $browser->visit('/projects/create')
            ->waitFor('#name')
            ->type('#name', 'Dashboard Project')
            ->press('Create project')
            ->waitForLocation('/projects');

        $browser->clickLink('Dashboard Project')
            ->waitForText('Dashboard Project')
            ->clickLink('Create task')
            ->waitFor('#title')
            ->type('#title', 'Dashboard visible task')
            ->press('Create task')
            ->waitForText('Dashboard visible task');

        $browser->visit('/dashboard')
            ->waitForText('Dashboard')
            ->assertDontSee('Dashboard visible task');
    });
});
