<?php

namespace Tests\Feature\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_signs_in_and_lands_on_their_account(): void
    {
        $user = User::factory()->create();

        $response = $this->post(route('login'), ['email' => $user->email, 'password' => 'password']);

        $response->assertRedirect(route('account.index'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_admin_signs_in_and_lands_on_admin_dashboard(): void
    {
        $admin = User::factory()->admin()->create();

        $this->post(route('login'), ['email' => $admin->email, 'password' => 'password'])
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_wrong_password_is_rejected(): void
    {
        $user = User::factory()->create();

        $response = $this->post(route('login'), ['email' => $user->email, 'password' => 'wrong-password']);

        $response->assertSessionHasErrors(['email' => 'These credentials do not match our records.']);
        $this->assertGuest();
    }

    public function test_return_path_sends_user_back_to_property_after_sign_in(): void
    {
        $user = User::factory()->create();

        $this->get(route('login', ['return' => '/properties/some-home']))->assertOk();

        $this->post(route('login'), ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect(url('/properties/some-home'));
    }

    public function test_external_return_path_is_ignored(): void
    {
        $user = User::factory()->create();

        $this->get(route('login', ['return' => '//evil.example.com']))->assertOk();

        $this->post(route('login'), ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect(route('account.index'));
    }

    public function test_user_signs_out(): void
    {
        $this->actingAs(User::factory()->create())->post(route('logout'))->assertRedirect(route('home'));

        $this->assertGuest();
    }
}
