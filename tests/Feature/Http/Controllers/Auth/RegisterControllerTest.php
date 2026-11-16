<?php

namespace Tests\Feature\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_visitor_creates_a_regular_account(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'Grace Hopper',
            'email' => 'grace@example.com',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
            'is_admin' => true,
        ]);

        $response->assertRedirect(route('account.index'));
        $user = User::where('email', 'grace@example.com')->sole();
        $this->assertAuthenticatedAs($user);
        $this->assertFalse($user->isAdmin());
    }

    public function test_email_must_be_unique(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $this->post(route('register'), [
            'name' => 'Someone',
            'email' => 'taken@example.com',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
        ])->assertSessionHasErrors(['email' => 'The email has already been taken.']);
    }
}
