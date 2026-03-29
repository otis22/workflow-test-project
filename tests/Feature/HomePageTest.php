<?php

namespace Tests\Feature;

use Tests\TestCase;

class HomePageTest extends TestCase
{
    public function test_the_home_page_responds_successfully(): void
    {
        $response = $this->get('/');

        $response->assertOk();
    }
}
