<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Engagement;
use App\Models\Property;
use App\Models\User;
use App\Notifications\NewEngagementReceived;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class EngagementControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_engagement_is_stored_with_contact_details_and_admins_are_notified(): void
    {
        Notification::fake();
        $admin = User::factory()->admin()->create();
        $property = Property::factory()->create();

        $response = $this->from(route('properties.show', $property))->post(route('properties.engage', $property), [
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.com',
            'phone' => '08012345678',
            'message' => 'Can I view it on Saturday?',
        ]);

        $response->assertRedirect(route('properties.show', $property));
        $response->assertSessionHas('engaged_email', 'ada@example.com');
        $this->assertDatabaseHas('engagements', [
            'property_id' => $property->id,
            'user_id' => null,
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.com',
            'status' => 'new',
        ]);
        Notification::assertSentTo($admin, NewEngagementReceived::class);
    }

    public function test_guest_must_provide_name_and_email(): void
    {
        $property = Property::factory()->create();

        $response = $this->post(route('properties.engage', $property), []);

        $response->assertSessionHasErrors([
            'name',
            'email' => 'Please enter your email so our team can reach you.',
        ]);
        $this->assertDatabaseCount('engagements', 0);
    }

    public function test_signed_in_user_engagement_is_linked_to_their_account(): void
    {
        $user = User::factory()->create(['phone' => '0700000000']);
        $property = Property::factory()->create();

        $response = $this->actingAs($user)->post(route('properties.engage', $property), ['message' => 'Interested']);

        $engagement = Engagement::sole();
        $response->assertRedirect(route('account.engagements.show', $engagement));
        $this->assertSame($user->id, $engagement->user_id);
        $this->assertSame($user->email, $engagement->email);
        $this->assertSame('0700000000', $engagement->phone);
    }

    public function test_signed_in_user_with_open_engagement_is_redirected_instead_of_duplicating(): void
    {
        $user = User::factory()->create();
        $property = Property::factory()->create();
        $existing = Engagement::factory()->forUser($user)->for($property)->create();

        $response = $this->actingAs($user)->post(route('properties.engage', $property));

        $response->assertRedirect(route('account.engagements.show', $existing));
        $this->assertDatabaseCount('engagements', 1);
    }

    public function test_sold_property_rejects_new_engagements(): void
    {
        $property = Property::factory()->sold()->create();

        $response = $this->post(route('properties.engage', $property), ['name' => 'Ada', 'email' => 'ada@example.com']);

        $response->assertSessionHas('error');
        $this->assertDatabaseCount('engagements', 0);
    }

    public function test_draft_property_returns_404(): void
    {
        $property = Property::factory()->draft()->create();

        $this->post(route('properties.engage', $property), ['name' => 'Ada', 'email' => 'ada@example.com'])->assertNotFound();
    }
}
