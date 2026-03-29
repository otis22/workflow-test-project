<?php

namespace Tests\Unit;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\AppServiceProvider;
use Illuminate\Foundation\Application;
use PHPUnit\Framework\TestCase;

class ApplicationSkeletonTest extends TestCase
{
    public function test_user_model_exposes_expected_fillable_hidden_and_casts(): void
    {
        $user = new User;

        $this->assertSame(['name', 'email', 'password'], $user->getFillable());
        $this->assertSame(['password', 'remember_token'], $user->getHidden());
        $this->assertSame(
            [
                'id' => 'int',
                'email_verified_at' => 'datetime',
                'password' => 'hashed',
            ],
            $user->getCasts(),
        );
    }

    public function test_app_service_provider_register_and_boot_are_callable(): void
    {
        $provider = new AppServiceProvider(new Application(dirname(__DIR__, 2)));

        $provider->register();
        $provider->boot();

        $this->addToAssertionCount(1);
    }

    public function test_base_controller_can_be_extended_by_application_controllers(): void
    {
        $controller = new class extends Controller {};

        $this->assertInstanceOf(Controller::class, $controller);
    }
}
