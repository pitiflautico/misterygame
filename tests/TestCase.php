<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Additional setup for all tests
        $this->withoutExceptionHandling();
    }

    /**
     * Create an authenticated user with token
     */
    protected function authenticatedUser($attributes = [])
    {
        $user = \App\Models\User::factory()->create($attributes);
        $token = $user->createToken('test')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
            'headers' => [
                'Authorization' => "Bearer {$token}",
                'Accept' => 'application/json',
            ],
        ];
    }

    /**
     * Create an admin user with token
     */
    protected function authenticatedAdmin()
    {
        return $this->authenticatedUser(['role' => 'admin']);
    }
}
