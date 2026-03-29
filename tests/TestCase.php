<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Testing\TestResponse;

abstract class TestCase extends BaseTestCase
{
    protected function registerUser(string $name, string $email, string $password): TestResponse
    {
        return $this
            ->withSession(['_token' => 'register-token'])
            ->post(route('register.store'), [
                '_token' => 'register-token',
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'password_confirmation' => $password,
            ]);
    }

    protected function loginUser(string $email, string $password): TestResponse
    {
        return $this
            ->withSession(['_token' => 'login-token'])
            ->post(route('login.store'), [
                '_token' => 'login-token',
                'email' => $email,
                'password' => $password,
            ]);
    }

    protected function createProject(string $name, string $description): TestResponse
    {
        return $this
            ->withSession(['_token' => 'project-token'])
            ->post(route('projects.store'), [
                '_token' => 'project-token',
                'name' => $name,
                'description' => $description,
            ]);
    }
}
