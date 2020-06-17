<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthSystemTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function logging_in_with_valid_credentials()
    {
        $user = factory(User::class)->create([
            'email'    => 'test@example.com',
            'password' => Hash::make('Test Password')
        ]);

        $response = $this->post('/login', [
            'email'    => 'test@example.com',
            'password' => 'Test Password'
        ]);

        $response->assertRedirect('/');
        $this->assertTrue(Auth::check());
        $this->assertTrue(Auth::user()->is($user));
    }

    /** @test */
    public function login_attempt_with_invalid_credentials()
    {
        factory(User::class)->create([
            'email'    => 'test@example.com',
            'password' => Hash::make('Test Password')
        ]);

        $response = $this->post('/login', [
            'email'    => 'test@example.com',
            'password' => 'wrong password'
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertFalse(Auth::check());
    }

    /** @test */
    public function login_attempt_with_account_that_does_not_exist()
    {
        factory(User::class)->create([
            'email'    => 'test@example.com',
            'password' => Hash::make('Test Password')
        ]);

        $response = $this->post('/login', [
            'email'    => 'nobody@example.com',
            'password' => 'random password'
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertFalse(Auth::check());
    }
}
