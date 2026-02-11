<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PasswordConfirmationTest extends TestCase
{
    use RefreshDatabase;

    public function test_confirm_password_screen_can_be_rendered(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('password.confirm'));

        $response->assertOk();
    }

    public function test_user_can_confirm_password_with_valid_password(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('password.confirm.store'), [
            'password' => 'password',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertNotNull(session('auth.password_confirmed_at'));
    }

    public function test_user_cannot_confirm_password_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('password.confirm.store'), [
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors(['password']);
    }
}
