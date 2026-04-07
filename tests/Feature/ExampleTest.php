<?php

it('returns a successful response from the home page', function (): void {
    $response = $this->get('/');

    $response->assertStatus(200);
});
