<?php

namespace Tests\Feature\Http\Controllers\Account;

use App\Enums\EnquiryStatus;
use App\Enums\InterestArea;
use App\Models\Enquiry;
use App\Models\EnquiryType;
use App\Models\EnquiryUpdate;
use App\Models\InvestmentOpportunity;
use App\Models\User;
use App\Notifications\EnquiryProgressUpdated;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ClientEnquiriesTest extends TestCase
{
    use RefreshDatabase;

    private User $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = User::factory()->create();
    }

    private function landEnquiry(array $attributes = []): Enquiry
    {
        $land = InvestmentOpportunity::factory()->land()->create(['title' => 'Asaba Gardens plots']);

        return Enquiry::factory()->ofType(EnquiryType::LandBanking)->create([
            'user_id' => $this->client->id,
            'enquirable_type' => 'investment_opportunity',
            'enquirable_id' => $land->id,
            ...$attributes,
        ]);
    }

    public function test_enquiries_are_grouped_into_interest_areas(): void
    {
        $land = $this->landEnquiry();
        $investment = Enquiry::factory()->ofType(EnquiryType::Investment)->create(['user_id' => $this->client->id, 'interest' => 'Rental income']);
        $advice = Enquiry::factory()->ofType(EnquiryType::Consultation)->create(['user_id' => $this->client->id]);

        $this->assertSame(InterestArea::LandBanking, $land->area());
        $this->assertSame(InterestArea::Investments, $investment->area());
        $this->assertSame(InterestArea::Advice, $advice->area());
    }

    public function test_client_sees_their_own_enquiries_filtered_by_area(): void
    {
        $this->landEnquiry();
        Enquiry::factory()->ofType(EnquiryType::Investment)->create(['user_id' => $this->client->id, 'interest' => 'Joint venture in Lekki']);
        Enquiry::factory()->create(['interest' => 'Someone else’s enquiry']);

        $this->actingAs($this->client)->get(route('account.enquiries.index'))
            ->assertOk()
            ->assertSee('Asaba Gardens plots')
            ->assertSee('Joint venture in Lekki')
            ->assertDontSee('Someone else’s enquiry');

        $this->actingAs($this->client)->get(route('account.enquiries.index', ['area' => 'land_banking']))
            ->assertOk()
            ->assertSee('Asaba Gardens plots')
            ->assertDontSee('Joint venture in Lekki');
    }

    public function test_in_progress_and_completed_tabs(): void
    {
        Enquiry::factory()->status(EnquiryStatus::Interested)->create(['user_id' => $this->client->id, 'interest' => 'Still talking']);
        Enquiry::factory()->status(EnquiryStatus::Converted)->create(['user_id' => $this->client->id, 'interest' => 'All done']);

        $this->actingAs($this->client)->get(route('account.enquiries.index', ['filter' => 'open']))
            ->assertSee('Still talking')->assertDontSee('All done');
        $this->actingAs($this->client)->get(route('account.enquiries.index', ['filter' => 'completed']))
            ->assertSee('All done')->assertDontSee('Still talking');
    }

    public function test_enquiry_page_shows_progress_and_adviser_messages(): void
    {
        $enquiry = $this->landEnquiry(['status' => EnquiryStatus::Contacted]);
        EnquiryUpdate::factory()->for($enquiry)->create(['status' => EnquiryStatus::Contacted, 'message' => 'I have sent you the survey plan.']);

        $this->actingAs($this->client)->get(route('account.enquiries.show', $enquiry))
            ->assertOk()
            ->assertSee('Asaba Gardens plots')
            ->assertSee('Adviser in touch')
            ->assertSee('I have sent you the survey plan.')
            ->assertSee('You sent this enquiry');
    }

    public function test_clients_cannot_open_other_peoples_enquiries(): void
    {
        $enquiry = Enquiry::factory()->create(['user_id' => User::factory()->create()->id]);

        $this->actingAs($this->client)->get(route('account.enquiries.show', $enquiry))->assertNotFound();
        $this->actingAs($this->client)->get(route('account.enquiries.show', Enquiry::factory()->create()))->assertNotFound();
    }

    public function test_guests_are_sent_to_sign_in(): void
    {
        $this->get(route('account.enquiries.index'))->assertRedirect(route('login'));
    }

    public function test_admin_update_notifies_the_client(): void
    {
        Notification::fake();
        $enquiry = $this->landEnquiry();
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->put(route('admin.enquiries.update', $enquiry), [
            'status' => 'contacted',
            'admin_notes' => 'Private: prefers calls after 5pm',
            'client_message' => 'Thanks — the title documents are on their way.',
        ])->assertSessionHas('success', "Enquiry updated and {$enquiry->name} has been notified.");

        $update = $enquiry->updates()->sole();
        $this->assertSame(EnquiryStatus::Contacted, $update->status);
        $this->assertSame('Thanks — the title documents are on their way.', $update->message);
        $this->assertSame($admin->id, $update->user_id);

        Notification::assertSentTo($this->client, EnquiryProgressUpdated::class, function (EnquiryProgressUpdated $notification) use ($enquiry) {
            $data = $notification->toArray($this->client);

            return $data['url'] === route('account.enquiries.show', $enquiry)
                && $data['body'] === 'Thanks — the title documents are on their way.'
                && ! str_contains(json_encode($data), 'Private');
        });
    }

    public function test_saving_notes_only_does_not_notify(): void
    {
        Notification::fake();
        $enquiry = $this->landEnquiry();

        $this->actingAs(User::factory()->admin()->create())->put(route('admin.enquiries.update', $enquiry), [
            'status' => 'new',
            'admin_notes' => 'Left a voicemail',
        ]);

        $this->assertSame(0, $enquiry->updates()->count());
        Notification::assertNothingSent();
    }

    public function test_guest_enquiries_get_a_timeline_but_no_notification(): void
    {
        Notification::fake();
        $enquiry = Enquiry::factory()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->put(route('admin.enquiries.update', $enquiry), ['status' => 'follow_up'])
            ->assertSessionHas('success', fn (string $message) => str_contains($message, 'guest enquiry'));

        $this->assertSame(1, $enquiry->updates()->count());
        Notification::assertNothingSent();
    }

    public function test_opening_an_enquiry_marks_its_notifications_read(): void
    {
        $enquiry = $this->landEnquiry();
        $update = $enquiry->updates()->create(['status' => EnquiryStatus::Contacted, 'message' => 'Hello']);
        $this->client->notify(new EnquiryProgressUpdated($update));

        $this->assertSame(1, $this->client->unreadNotifications()->count());

        $this->actingAs($this->client)->get(route('account.enquiries.show', $enquiry))->assertOk();

        $this->assertSame(0, $this->client->fresh()->unreadNotifications()->count());
    }

    public function test_dashboard_shows_enquiries_interests_and_matching_land(): void
    {
        $this->landEnquiry(['status' => EnquiryStatus::Interested]);
        InvestmentOpportunity::factory()->land()->create(['title' => 'Ibusa riverside plots']);

        $this->actingAs($this->client)->get(route('account.index'))
            ->assertOk()
            ->assertSee('Enquiries in progress')
            ->assertSee('Asaba Gardens plots')
            ->assertSee('Exploring options')
            ->assertSee('Your interests')
            ->assertSee('Land and investments for you')
            ->assertSee('Ibusa riverside plots');
    }

    public function test_sidebar_has_a_my_enquiries_dropdown(): void
    {
        $this->landEnquiry();

        $this->actingAs($this->client)->get(route('account.enquiries.index', ['area' => 'land_banking']))
            ->assertSee('id="nav-my-enquiries"', false)
            ->assertSee(route('account.enquiries.index', ['area' => 'investments']))
            ->assertSee('aria-current="page"', false);
    }
}
