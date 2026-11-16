<?php

namespace Tests\Feature\Http\Controllers\Account;

use App\Enums\EngagementStatus;
use App\Models\Engagement;
use App\Models\User;
use App\Notifications\EngagementProgressUpdated;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class NotificationControllerTest extends TestCase
{
    use RefreshDatabase;

    private function notifiedUser(): User
    {
        $user = User::factory()->create();
        $engagement = Engagement::factory()->forUser($user)->create();
        $update = $engagement->updates()->create(['status' => EngagementStatus::Contacted, 'note' => 'We called you']);
        $user->notify(new EngagementProgressUpdated($update));

        return $user;
    }

    public function test_index_lists_notifications(): void
    {
        $user = $this->notifiedUser();

        $this->actingAs($user)->get(route('account.notifications.index'))
            ->assertOk()
            ->assertSeeText('We called you');
    }

    public function test_opening_a_notification_marks_it_read_and_follows_its_link(): void
    {
        $user = $this->notifiedUser();
        $notification = $user->notifications()->sole();

        $response = $this->actingAs($user)->get(route('account.notifications.show', $notification->id));

        $response->assertRedirect($notification->data['url']);
        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_cannot_open_another_users_notification(): void
    {
        $notification = $this->notifiedUser()->notifications()->sole();

        $this->actingAs(User::factory()->create())
            ->get(route('account.notifications.show', $notification->id))
            ->assertNotFound();
    }

    public function test_read_all_marks_every_notification_read(): void
    {
        $user = $this->notifiedUser();

        $this->actingAs($user)->post(route('account.notifications.read-all'))->assertRedirect();

        $this->assertSame(0, $user->unreadNotifications()->count());
    }

    public function test_unknown_notification_returns_404(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('account.notifications.show', (string) Str::uuid()))
            ->assertNotFound();
    }
}
