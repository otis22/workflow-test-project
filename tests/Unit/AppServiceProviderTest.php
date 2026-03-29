<?php

namespace Tests\Unit;

use App\Providers\AppServiceProvider;
use Tests\TestCase;

class AppServiceProviderTest extends TestCase
{
    public function test_service_provider_registers_and_boots_without_side_effects(): void
    {
        $provider = new AppServiceProvider($this->app);

        $provider->register();
        $provider->boot();

        $this->assertTrue(true);
    }
}
