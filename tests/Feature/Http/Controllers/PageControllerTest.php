<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\EnquiryType;
use App\Models\Property;
use App\Models\WhatsappNumber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_services_page_lists_all_services_and_a_featured_shortlet(): void
    {
        Property::factory()->shortlet()->create(['title' => 'Ocean View Shortlet']);

        $this->get(route('services'))
            ->assertOk()
            ->assertSeeText('Real Estate Brokerage')
            ->assertSeeText('Property Management')
            ->assertSeeText('Ocean View Shortlet')
            ->assertSeeText('/night');
    }

    public function test_about_page_shows_mission_vision_and_growth_strategy(): void
    {
        $this->get(route('about'))
            ->assertOk()
            ->assertSeeText('Mission')
            ->assertSeeText('Vision')
            ->assertSeeText('Three phases, one direction');
    }

    public function test_contact_page_shows_company_details_and_preselects_enquiry_type(): void
    {
        config(['listings.company.phone' => '+2340000000000']);

        $response = $this->get(route('contact', ['type' => 'list_property', 'interest' => 'My flat in Yaba']))
            ->assertOk()
            ->assertSeeText('+2340000000000')
            ->assertSee('value="My flat in Yaba"', false);

        $this->assertMatchesRegularExpression('/<option value="list_property"[^>]*selected>/', $response->getContent());
    }

    public function test_speak_to_an_adviser_offers_available_properties_and_a_free_text_field(): void
    {
        $available = Property::factory()->create(['title' => 'Available Villa']);
        Property::factory()->sold()->create(['title' => 'Already Sold House']);
        Property::factory()->draft()->create(['title' => 'Hidden Draft']);

        $this->get(route('contact', ['type' => 'consultation', 'property' => $available->slug]))
            ->assertOk()
            ->assertSee('name="property"', false)
            ->assertSee('<option value="'.$available->slug.'" selected>', false)
            ->assertDontSeeText('Already Sold House')
            ->assertDontSeeText('Hidden Draft')
            ->assertSeeText('Or describe what you are looking for');
    }

    public function test_contact_form_lists_active_enquiry_types_only(): void
    {
        EnquiryType::factory()->create(['name' => 'Mortgage advice']);
        EnquiryType::factory()->inactive()->create(['name' => 'Valuation request']);

        $this->get(route('contact'))
            ->assertSeeText('Mortgage advice')
            ->assertDontSeeText('Valuation request');
    }

    public function test_visitors_choose_between_whatsapp_numbers_and_each_type_routes_to_its_own(): void
    {
        WhatsappNumber::query()->delete();
        $main = WhatsappNumber::factory()->default()->create(['label' => 'Main line', 'number' => '2348000000001']);
        $land = WhatsappNumber::factory()->create(['label' => 'Land desk', 'number' => '2348000000002']);
        EnquiryType::where('slug', 'land_banking')->update(['whatsapp_number_id' => $land->id]);

        $this->get(route('contact'))
            ->assertOk()
            ->assertSee('data-whatsapp-number', false)
            ->assertSeeText('Land desk · +2348000000002')
            ->assertSee('data-whatsapp="2348000000002"', false)
            ->assertSee('https://wa.me/'.$main->number, false);
    }
}
