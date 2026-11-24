<?php

namespace Tests\Feature\Http\Controllers;

use App\Enums\SiteArea;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TourControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_first_time_visitors_get_the_tour_automatically(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('data-area="landing"', false)
            ->assertSee('data-auto-start="1"', false)
            ->assertSee('data-guest="1"', false)
            ->assertSee('data-tour="property-search"', false)
            ->assertSee('Take a tour');
    }

    public function test_finishing_the_tour_is_remembered_on_the_account(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('account.index'))->assertSee('data-auto-start="1"', false);

        $this->actingAs($user)->postJson(route('tour.complete', SiteArea::Client))->assertNoContent();

        $this->assertTrue($user->fresh()->hasSeenTour(SiteArea::Client));
        $this->assertFalse($user->fresh()->hasSeenTour(SiteArea::Landing));
        $this->actingAs($user)->get(route('account.index'))->assertSee('data-auto-start="0"', false);
    }

    public function test_seen_tours_can_still_be_replayed_from_the_profile_menu(): void
    {
        $admin = User::factory()->admin()->create(['completed_tours' => ['admin' => now()->toIso8601String()]]);

        $this->actingAs($admin)->get(route('admin.dashboard'))
            ->assertSee('data-auto-start="0"', false)
            ->assertSee('data-tour-start', false)
            ->assertSee('Replay any time')
            ->assertSee('<meta name="csrf-token"', false);
    }

    public function test_guests_cannot_save_tour_progress(): void
    {
        $this->postJson(route('tour.complete', SiteArea::Landing))->assertUnauthorized();
    }

    public function test_clients_cannot_mark_the_admin_tour(): void
    {
        $this->actingAs(User::factory()->create())->postJson(route('tour.complete', SiteArea::Admin))->assertForbidden();
    }

    public function test_unknown_area_is_not_found(): void
    {
        $this->actingAs(User::factory()->create())->postJson('/tour/checkout')->assertNotFound();
    }

    public function test_site_manager_can_switch_the_automatic_tour_off(): void
    {
        SiteSetting::create(['area' => SiteArea::Landing, 'settings' => ['tour' => false]]);

        $this->get(route('home'))
            ->assertSee('data-auto-start="0"', false)
            ->assertSee('data-tour-start', false);
    }

    public function test_every_area_has_a_tour_with_a_welcome_step(): void
    {
        foreach (SiteArea::cases() as $area) {
            $steps = $area->tourSteps();

            $this->assertGreaterThanOrEqual(4, count($steps), $area->value);
            $this->assertNull($steps[0]['target']);
        }
    }
}
