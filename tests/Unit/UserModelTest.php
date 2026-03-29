<?php

namespace Tests\Unit;

use App\Models\User;
use PHPUnit\Framework\TestCase;

class UserModelTest extends TestCase
{
    public function test_user_model_exposes_expected_fillable_and_hidden_attributes(): void
    {
        $user = new User;

        $this->assertSame(['name', 'email', 'password'], $user->getFillable());
        $this->assertSame(['password', 'remember_token'], $user->getHidden());
    }

    public function test_user_model_exposes_expected_casts(): void
    {
        $user = new User;

        $this->assertSame([
            'id' => 'int',
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ], $user->getCasts());
    }
}
