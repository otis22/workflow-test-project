<?php

namespace Tests\Unit\Http\Requests\Auth;

use App\Http\Requests\Auth\LoginUserRequest;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class LoginUserRequestTest extends TestCase
{
    public function test_throttle_key_normalizes_the_email_and_ip(): void
    {
        $request = $this->makeRequest([
            'email' => 'ÄDA@Example.COM',
        ], '127.0.0.1');

        $this->assertSame('ada@example.com|127.0.0.1', $request->throttleKey());
    }

    public function test_it_throws_a_validation_exception_when_rate_limited(): void
    {
        Event::fake();

        $request = $this->makeRequest([
            'email' => 'ada@example.com',
        ], '127.0.0.1');

        RateLimiter::shouldReceive('tooManyAttempts')
            ->once()
            ->with('ada@example.com|127.0.0.1', 5)
            ->andReturnTrue();

        RateLimiter::shouldReceive('availableIn')
            ->once()
            ->with('ada@example.com|127.0.0.1')
            ->andReturn(60);

        try {
            $request->ensureIsNotRateLimited();
            $this->fail('Expected a ValidationException to be thrown.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('email', $exception->errors());
            $this->assertStringContainsString('Too many login attempts', $exception->errors()['email'][0]);
        }

        Event::assertDispatched(Lockout::class);
    }

    public function test_it_hits_the_rate_limiter_when_authentication_fails(): void
    {
        $request = $this->makeRequest([
            'email' => 'ada@example.com',
            'password' => 'wrong-password',
        ], '127.0.0.1');

        $this->expectRequestNotToBeRateLimited('ada@example.com|127.0.0.1');

        Auth::shouldReceive('attempt')
            ->once()
            ->with([
                'email' => 'ada@example.com',
                'password' => 'wrong-password',
            ], false)
            ->andReturnFalse();

        RateLimiter::shouldReceive('hit')
            ->once()
            ->with('ada@example.com|127.0.0.1');

        try {
            $request->authenticate();
            $this->fail('Expected a ValidationException to be thrown.');
        } catch (ValidationException $exception) {
            $this->assertSame(
                ['The provided credentials do not match our records.'],
                $exception->errors()['email'],
            );
        }
    }

    public function test_it_clears_the_rate_limiter_after_successful_authentication(): void
    {
        $request = $this->makeRequest([
            'email' => 'ada@example.com',
            'password' => 'password123',
            'remember' => '1',
        ], '127.0.0.1');

        $this->expectRequestNotToBeRateLimited('ada@example.com|127.0.0.1');

        Auth::shouldReceive('attempt')
            ->once()
            ->with([
                'email' => 'ada@example.com',
                'password' => 'password123',
            ], true)
            ->andReturnTrue();

        RateLimiter::shouldReceive('clear')
            ->once()
            ->with('ada@example.com|127.0.0.1');

        $request->authenticate();
    }

    /**
     * @param  array<string, mixed>  $input
     */
    private function makeRequest(array $input, string $ip): LoginUserRequest
    {
        /** @var LoginUserRequest $request */
        $request = LoginUserRequest::create(
            '/login',
            'POST',
            $input,
            server: ['REMOTE_ADDR' => $ip],
        );

        $request->setContainer($this->app);
        $request->setRedirector($this->app['redirect']);

        return $request;
    }

    private function expectRequestNotToBeRateLimited(string $throttleKey): void
    {
        RateLimiter::shouldReceive('tooManyAttempts')
            ->once()
            ->with($throttleKey, 5)
            ->andReturnFalse();
    }
}
