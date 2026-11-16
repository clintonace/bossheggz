<?php

namespace Tests\Feature\Http\Controllers\Account;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_renders(): void
    {
        $this->actingAs(User::factory()->create(['name' => 'Bola Buyer']))
            ->get(route('account.profile.edit'))
            ->assertOk()
            ->assertSeeText('Personal details');
    }

    public function test_user_updates_their_details(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->put(route('account.profile.update'), [
            'name' => 'New Name',
            'email' => 'new@example.com',
            'phone' => '08099990000',
        ])->assertSessionHas('success', 'Your profile has been updated.');

        $user->refresh();
        $this->assertSame('New Name', $user->name);
        $this->assertSame('new@example.com', $user->email);
        $this->assertSame('08099990000', $user->phone);
    }

    public function test_email_already_used_by_someone_else_is_rejected(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);
        $user = User::factory()->create();

        $this->actingAs($user)->put(route('account.profile.update'), ['name' => 'Me', 'email' => 'taken@example.com'])
            ->assertSessionHasErrorsIn('profile', ['email' => 'The email has already been taken.']);
    }

    public function test_profile_update_cannot_grant_admin(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->put(route('account.profile.update'), ['name' => 'Me', 'email' => $user->email, 'is_admin' => true]);

        $this->assertFalse($user->fresh()->isAdmin());
    }

    public function test_user_changes_password_with_correct_current_password(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->put(route('account.profile.password'), [
            'current_password' => 'password',
            'password' => 'brand-new-secret',
            'password_confirmation' => 'brand-new-secret',
        ])->assertSessionHas('success', 'Your password has been changed.');

        $this->assertTrue(Hash::check('brand-new-secret', $user->fresh()->password));
    }

    public function test_wrong_current_password_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->put(route('account.profile.password'), [
            'current_password' => 'not-my-password',
            'password' => 'brand-new-secret',
            'password_confirmation' => 'brand-new-secret',
        ])->assertSessionHasErrorsIn('password', ['current_password' => 'The password is incorrect.']);

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }
}
