<?php

namespace Tests\Feature\Http\Controllers;

use App\Enums\EnquiryStatus;
use App\Models\Enquiry;
use App\Models\EnquiryType;
use App\Models\InvestmentOpportunity;
use App\Models\Property;
use App\Models\User;
use App\Notifications\NewEnquiryReceived;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class EnquiryControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_send_an_enquiry_and_admins_are_notified(): void
    {
        Notification::fake();
        $admin = User::factory()->admin()->create();

        $this->from(route('contact'))
            ->post(route('enquiries.store'), [
                'type' => 'consultation',
                'name' => 'Ada Obi',
                'email' => 'ada@example.com',
                'phone' => '08030000000',
                'interest' => 'Land in Lekki',
                'message' => 'Please call me.',
            ])
            ->assertRedirect(route('contact').'#enquire')
            ->assertSessionHas('enquiry_sent', 'ada@example.com');

        $enquiry = Enquiry::sole();
        $this->assertSame(EnquiryType::Consultation, $enquiry->enquiryType->slug);
        $this->assertSame(EnquiryStatus::New, $enquiry->status);
        $this->assertSame('Land in Lekki', $enquiry->interest);
        $this->assertNull($enquiry->user_id);

        Notification::assertSentTo($admin, NewEnquiryReceived::class);
    }

    public function test_guest_must_give_name_and_email(): void
    {
        $this->from(route('contact'))
            ->post(route('enquiries.store'), ['type' => 'general'])
            ->assertRedirect(route('contact').'#enquire')
            ->assertSessionHasErrors(['name', 'email']);

        $this->assertDatabaseCount('enquiries', 0);
    }

    public function test_signed_in_user_details_are_used(): void
    {
        $user = User::factory()->create(['name' => 'Tunde Bello', 'email' => 'tunde@example.com']);

        $this->actingAs($user)->post(route('enquiries.store'), ['type' => 'general', 'message' => 'Hi'])->assertRedirect();

        $enquiry = Enquiry::sole();
        $this->assertSame('Tunde Bello', $enquiry->name);
        $this->assertSame('tunde@example.com', $enquiry->email);
        $this->assertTrue($enquiry->user->is($user));
    }

    public function test_enquiry_is_linked_to_the_chosen_opportunity(): void
    {
        $opportunity = InvestmentOpportunity::factory()->land()->create(['title' => 'Asaba Plots']);

        $this->post(route('enquiries.store'), [
            'type' => 'land_banking',
            'name' => 'Ada',
            'email' => 'ada@example.com',
            'opportunity' => $opportunity->slug,
        ])->assertSessionHasNoErrors();

        $enquiry = Enquiry::sole();
        $this->assertTrue($enquiry->enquirable->is($opportunity));
        $this->assertSame('Asaba Plots', $enquiry->subject());
    }

    public function test_closed_opportunities_do_not_accept_enquiries(): void
    {
        $opportunity = InvestmentOpportunity::factory()->closed()->create();

        $this->post(route('enquiries.store'), [
            'type' => 'investment',
            'name' => 'Ada',
            'email' => 'ada@example.com',
            'opportunity' => $opportunity->slug,
        ])->assertSessionHasErrors('opportunity');
    }

    public function test_visitor_can_pick_an_available_property_and_describe_more(): void
    {
        $property = Property::factory()->create(['title' => 'Lekki Duplex']);

        $this->post(route('enquiries.store'), [
            'type' => 'consultation',
            'name' => 'Ada',
            'email' => 'ada@example.com',
            'property' => $property->slug,
            'interest' => 'Also open to Ajah',
        ])->assertSessionHasNoErrors();

        $enquiry = Enquiry::sole();
        $this->assertTrue($enquiry->enquirable->is($property));
        $this->assertSame('Lekki Duplex — Also open to Ajah', $enquiry->subject());
    }

    public function test_sold_properties_cannot_be_chosen(): void
    {
        $property = Property::factory()->sold()->create();

        $this->post(route('enquiries.store'), [
            'type' => 'consultation',
            'name' => 'Ada',
            'email' => 'ada@example.com',
            'property' => $property->slug,
        ])->assertSessionHasErrors('property');
    }

    public function test_admin_added_types_can_be_used_but_switched_off_types_cannot(): void
    {
        $custom = EnquiryType::factory()->create(['name' => 'Mortgage advice']);
        $off = EnquiryType::factory()->inactive()->create(['name' => 'Valuation request']);

        $this->post(route('enquiries.store'), ['type' => $custom->slug, 'name' => 'Ada', 'email' => 'ada@example.com'])->assertSessionHasNoErrors();
        $this->post(route('enquiries.store'), ['type' => $off->slug, 'name' => 'Ada', 'email' => 'ada@example.com'])->assertSessionHasErrors('type');

        $this->assertTrue(Enquiry::sole()->enquiryType->is($custom));
    }
}
