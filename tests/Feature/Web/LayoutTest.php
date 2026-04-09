<?php

declare(strict_types=1);

it('renders the home page with layout brand and guest navigation', function (): void {
    $response = $this->get('/');

    $response->assertStatus(200)
        ->assertSee('TaskFlow')
        ->assertSee('Sign in')
        ->assertSee('Register');
});

it('includes the vite-built stylesheet in the layout head', function (): void {
    $response = $this->get('/');

    // Vite directive injects either a <link> to a built asset or a hot-dev link.
    expect($response->getContent())
        ->toMatch('/<link[^>]+rel="stylesheet"[^>]*>/i');
});

it('renders the welcome page with primary call-to-action buttons', function (): void {
    $response = $this->get('/');

    $response->assertStatus(200)
        ->assertSee('TaskFlow')
        ->assertSeeText('Sign in')
        ->assertSeeText('Register');
});
