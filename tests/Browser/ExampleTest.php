<?php

use Laravel\Dusk\Browser;

test('welcome page shows TaskFlow brand', function (): void {
    $this->browse(function (Browser $browser): void {
        $browser->visit('/')
            ->assertSee('TaskFlow');
    });
});
