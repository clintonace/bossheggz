<?php

namespace Tests\Feature\Http\Controllers\Account;

use App\Enums\EngagementStatus;
use App\Models\Engagement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EngagementControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_sees_progress_and_timeline_notes(): void
    {
        $user = User::factory()->create();
        $engagement = Engagement::factory()->forUser($user)->status(EngagementStatus::ViewingScheduled)->create();
        $engagement->updates()->create(['status' => EngagementStatus::ViewingScheduled, 'note' => 'Viewing booked for Saturday']);

        $response = $this->actingAs($user)->get(route('account.engagements.show', $engagement));

        $response->assertOk();
        $response->assertSeeText($engagement->property->title);
        $response->assertSeeText('Viewing booked for Saturday');
    }

    public function test_another_users_engagement_returns_404(): void
    {
        $engagement = Engagement::factory()->forUser()->create();

        $this->actingAs(User::factory()->create())
            ->get(route('account.engagements.show', $engagement))
            ->assertNotFound();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $engagement = Engagement::factory()->forUser()->create();

        $this->get(route('account.engagements.show', $engagement))->assertRedirect(route('login'));
    }

    public function test_index_lists_only_the_users_engagements(): void
    {
        $user = User::factory()->create();
        $mine = Engagement::factory()->forUser($user)->create();
        $theirs = Engagement::factory()->forUser()->create();

        $response = $this->actingAs($user)->get(route('account.engagements.index'));

        $response->assertOk();
        $response->assertSeeText($mine->property->title);
        $response->assertDontSeeText($theirs->property->title);
    }

    public function test_index_completed_filter_shows_only_finished_engagements(): void
    {
        $user = User::factory()->create();
        $open = Engagement::factory()->forUser($user)->status(EngagementStatus::Negotiating)->create();
        $closed = Engagement::factory()->forUser($user)->status(EngagementStatus::Closed)->create();

        $response = $this->actingAs($user)->get(route('account.engagements.index', ['filter' => 'completed']));

        $response->assertSeeText($closed->property->title);
        $response->assertDontSeeText($open->property->title);
    }

    public function test_closed_engagement_shows_congratulations(): void
    {
        $user = User::factory()->create();
        $engagement = Engagement::factory()->forUser($user)->status(EngagementStatus::Closed)->create();

        $this->actingAs($user)->get(route('account.engagements.show', $engagement))
            ->assertSeeText('Congratulations — this deal is complete!');
    }
}
