<?php

namespace Tests\Feature\Http\Controllers\Account;

use App\Enums\SiteArea;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class AppearanceControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_saves_dark_mode(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->put(route('account.appearance.update'), ['mode' => 'dark'])
            ->assertSessionHas('success', 'Appearance updated.');

        $this->assertSame('dark', $user->fresh()->theme_mode);
    }

    public function test_instant_switch_saves_without_reloading(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->putJson(route('account.appearance.update'), ['mode' => 'system'])->assertNoContent();

        $this->assertSame('system', $user->fresh()->theme_mode);
    }

    public function test_unknown_mode_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->put(route('account.appearance.update'), ['mode' => 'sepia'])->assertSessionHasErrors('mode');

        $this->assertNull($user->fresh()->theme_mode);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->put(route('account.appearance.update'), ['mode' => 'dark'])->assertRedirect(route('login'));
    }

    #[TestWith(['home', false])]
    #[TestWith(['account.index', false])]
    #[TestWith(['admin.dashboard', true])]
    public function test_users_choice_overrides_the_site_default_everywhere(string $route, bool $needsAdmin): void
    {
        SiteSetting::create(['area' => SiteArea::Landing, 'settings' => ['mode' => 'light']]);
        $user = User::factory()->state(['theme_mode' => 'dark'])->when($needsAdmin, fn ($factory) => $factory->admin())->create();

        $this->actingAs($user)->get(route($route))
            ->assertSee('<html lang="en" data-mode="dark"', false)
            ->assertSee('data-appearance-option aria-pressed="true"', false);
    }

    public function test_without_a_choice_the_site_default_applies(): void
    {
        SiteSetting::create(['area' => SiteArea::Client, 'settings' => ['mode' => 'system']]);

        $this->actingAs(User::factory()->create())->get(route('account.index'))
            ->assertSee('data-mode="system"', false);
    }

    #[TestWith(['home'])]
    #[TestWith(['account.index'])]
    #[TestWith(['admin.dashboard'])]
    public function test_profile_menu_with_appearance_switch_is_in_every_area(string $route): void
    {
        $this->actingAs(User::factory()->admin()->create())->get(route($route))
            ->assertSee('id="profile-menu"', false)
            ->assertSee('data-appearance-form', false)
            ->assertSeeTextInOrder(['Appearance', 'Light', 'Dark', 'Auto', 'Sign out']);
    }
}
