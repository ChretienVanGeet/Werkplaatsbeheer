<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Livewire\Auth\Register;
use Livewire\Livewire;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    public function test_registration_screen_is_disabled(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(404);
    }

    public function test_new_users_can_register(): void
    {
        $response = Livewire::test(Register::class)
            ->set('name', 'Test User')
            ->set('email', 'test@example.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->call('register');

        $response
            ->assertHasNoErrors()
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticated();
    }
}
