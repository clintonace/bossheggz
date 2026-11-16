<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DevAccountSwitchControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_switching_to_an_admin_signs_in_and_opens_admin_dashboard(): void
    {
        config(['listings.dev_account_switcher' => true]);
        $admin = User::factory()->admin()->create();

        $this->post(route('dev.switch-account', $admin))->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin);
    }

    public function test_switching_to_a_client_replaces_the_current_user_and_opens_client_dashboard(): void
    {
        config(['listings.dev_account_switcher' => true]);
        $client = User::factory()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->post(route('dev.switch-account', $client))
            ->assertRedirect(route('account.index'));

        $this->assertAuthenticatedAs($client);
    }

    public function test_returns_404_and_signs_nobody_in_when_disabled(): void
    {
        config(['listings.dev_account_switcher' => false]);
        $admin = User::factory()->admin()->create();

        $this->post(route('dev.switch-account', $admin))->assertNotFound();

        $this->assertGuest();
    }

    public function test_switcher_lists_accounts_only_when_enabled(): void
    {
        $admin = User::factory()->admin()->create(['email' => 'boss@example.com']);

        config(['listings.dev_account_switcher' => true]);
        $this->get(route('home'))->assertSeeText('Test accounts')->assertSee(route('dev.switch-account', $admin), false);

        config(['listings.dev_account_switcher' => false]);
        $this->get(route('home'))->assertDontSeeText('Test accounts');
    }

    public function test_switcher_is_off_by_default_outside_local_environment(): void
    {
        $this->assertFalse(config('listings.dev_account_switcher'));
    }
}
