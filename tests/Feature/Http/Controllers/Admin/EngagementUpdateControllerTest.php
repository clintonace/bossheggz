<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Enums\EngagementStatus;
use App\Models\Engagement;
use App\Models\User;
use App\Notifications\EngagementProgressUpdated;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class EngagementUpdateControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_progress_update_notifies_registered_engager(): void
    {
        Notification::fake();
        $admin = User::factory()->admin()->create();
        $member = User::factory()->create();
        $engagement = Engagement::factory()->forUser($member)->create();

        $response = $this->actingAs($admin)->post(route('admin.engagements.updates.store', $engagement), [
            'status' => 'viewing_scheduled',
            'note' => 'Saturday 11am',
            'notify' => '1',
        ]);

        $response->assertRedirect();
        $this->assertSame(EngagementStatus::ViewingScheduled, $engagement->fresh()->status);
        $this->assertDatabaseHas('engagement_updates', [
            'engagement_id' => $engagement->id,
            'author_id' => $admin->id,
            'status' => 'viewing_scheduled',
            'note' => 'Saturday 11am',
        ]);
        Notification::assertSentTo($member, EngagementProgressUpdated::class, fn ($notification, array $channels) => $channels === ['mail', 'database']);
    }

    public function test_progress_update_without_notify_does_not_notify(): void
    {
        Notification::fake();
        $engagement = Engagement::factory()->forUser()->create();

        $this->actingAs(User::factory()->admin()->create())->post(route('admin.engagements.updates.store', $engagement), [
            'status' => 'contacted',
            'notify' => '0',
        ]);

        Notification::assertNothingSent();
        $this->assertSame(EngagementStatus::Contacted, $engagement->fresh()->status);
    }

    public function test_guest_engagement_update_sends_nothing_and_reminds_admin_to_follow_up(): void
    {
        Notification::fake();
        $engagement = Engagement::factory()->create(['email' => 'guest@example.com']);

        $response = $this->actingAs(User::factory()->admin()->create())->post(route('admin.engagements.updates.store', $engagement), [
            'status' => 'contacted',
            'notify' => '1',
        ]);

        $response->assertSessionHas('success', 'Progress saved. This is a guest — remember to reach out to guest@example.com manually.');
        Notification::assertNothingSent();
    }

    public function test_invalid_status_is_rejected(): void
    {
        $engagement = Engagement::factory()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->post(route('admin.engagements.updates.store', $engagement), ['status' => 'teleported'])
            ->assertSessionHasErrors('status');
    }

    public function test_regular_user_cannot_post_updates(): void
    {
        $engagement = Engagement::factory()->forUser()->create();

        $this->actingAs($engagement->user)
            ->post(route('admin.engagements.updates.store', $engagement), ['status' => 'closed'])
            ->assertForbidden();

        $this->assertSame(EngagementStatus::New, $engagement->fresh()->status);
    }
}
