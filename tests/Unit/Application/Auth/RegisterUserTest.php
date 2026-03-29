<?php

namespace Tests\Unit\Application\Auth;

use App\Application\Auth\RegisterUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegisterUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_user_with_a_hashed_password(): void
    {
        $user = app(RegisterUser::class)(
            name: 'Grace Hopper',
            email: 'grace@example.com',
            password: 'password123',
        );

        $this->assertSame('Grace Hopper', $user->name);
        $this->assertSame('grace@example.com', $user->email);
        $this->assertTrue(Hash::check('password123', $user->password));
    }
}
