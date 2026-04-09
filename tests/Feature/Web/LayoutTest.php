<?php

declare(strict_types=1);

it('renders the home page with layout brand and guest navigation', function (): void {
    $response = $this->get('/');

    $response->assertStatus(200)
        ->assertSee('TaskFlow')
        ->assertSee('Sign in')
        ->assertSee('Register');
});

it('hides authenticated navigation links on the guest home page', function (): void {
    // When no user is logged in, the @auth branch of <x-nav> must not
    // render Dashboard/Projects/Logout. This locks the guest-only
    // branch until 4.3 wires real session middleware and adds a
    // dedicated @auth branch test.
    $response = $this->get('/');

    $response->assertStatus(200)
        ->assertDontSee('Dashboard')
        ->assertDontSee('Projects')
        ->assertDontSee('Logout');
});

it('includes the vite-built stylesheet in the layout head', function (): void {
    $response = $this->get('/');

    // Must reference the Vite-hashed asset under /build/assets/, not
    // just any <link rel="stylesheet"> (Bunny Fonts would satisfy that).
    expect($response->getContent())
        ->toMatch('#<link[^>]+href="[^"]*/build/assets/[^"]+\.css"#i');
});

it('renders the welcome page with primary call-to-action buttons', function (): void {
    $response = $this->get('/');

    $response->assertStatus(200)
        ->assertSee('TaskFlow')
        ->assertSeeText('Sign in')
        ->assertSeeText('Register');
});
