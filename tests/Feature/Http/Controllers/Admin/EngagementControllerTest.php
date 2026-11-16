<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Enums\EngagementStatus;
use App\Models\Engagement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EngagementControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_filters_by_status(): void
    {
        Engagement::factory()->create(['name' => 'Brand New Lead']);
        Engagement::factory()->status(EngagementStatus::Negotiating)->create(['name' => 'Hot Negotiation']);

        $response = $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.engagements.index', ['status' => 'negotiating']));

        $response->assertOk();
        $response->assertSeeText('Hot Negotiation');
        $response->assertDontSeeText('Brand New Lead');
    }

    public function test_index_filters_guests_only(): void
    {
        Engagement::factory()->create(['name' => 'Guest Person']);
        Engagement::factory()->forUser(User::factory()->create(['name' => 'Member Person']))->create();

        $response = $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.engagements.index', ['audience' => 'guest']));

        $response->assertSeeText('Guest Person');
        $response->assertDontSeeText('Member Person');
    }

    public function test_show_flags_guest_engagements_for_manual_follow_up(): void
    {
        $engagement = Engagement::factory()->create(['email' => 'guest@example.com']);

        $response = $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.engagements.show', $engagement));

        $response->assertOk();
        $response->assertSeeText('Guest engagement.');
        $response->assertSee('mailto:guest@example.com', false);
    }
}
