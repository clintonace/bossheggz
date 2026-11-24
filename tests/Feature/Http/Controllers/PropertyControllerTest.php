<?php

namespace Tests\Feature\Http\Controllers;

use App\Enums\AdvertPage;
use App\Enums\AdvertPosition;
use App\Enums\AdvertWidth;
use App\Models\Advert;
use App\Models\Engagement;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PropertyControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_filters_by_listing_type(): void
    {
        Property::factory()->create(['title' => 'House For Sale']);
        Property::factory()->forRent()->create(['title' => 'Flat For Rent']);

        $response = $this->get(route('properties.index', ['listing' => 'rent']));

        $response->assertOk();
        $response->assertSeeText('Flat For Rent');
        $response->assertDontSeeText('House For Sale');
    }

    public function test_index_filters_by_search_term_and_minimum_bedrooms(): void
    {
        Property::factory()->create(['title' => 'Lekki Mansion', 'city' => 'Lekki', 'bedrooms' => 5]);
        Property::factory()->create(['title' => 'Lekki Studio', 'city' => 'Lekki', 'bedrooms' => 1]);
        Property::factory()->create(['title' => 'Abuja Duplex', 'city' => 'Abuja', 'bedrooms' => 5]);

        $response = $this->get(route('properties.index', ['q' => 'Lekki', 'beds' => 3]));

        $response->assertSeeText('Lekki Mansion');
        $response->assertDontSeeText('Lekki Studio');
        $response->assertDontSeeText('Abuja Duplex');
    }

    public function test_index_hides_draft_properties(): void
    {
        Property::factory()->draft()->create(['title' => 'Hidden Draft']);

        $this->get(route('properties.index'))->assertOk()->assertDontSeeText('Hidden Draft');
    }

    public function test_show_renders_property_with_engage_form_for_guests(): void
    {
        $property = Property::factory()->create(['title' => 'Ocean View Penthouse']);

        $response = $this->get(route('properties.show', $property));

        $response->assertOk();
        $response->assertSeeText('Ocean View Penthouse');
        $response->assertSee(route('properties.engage', $property), false);
        $response->assertSeeText('Track every step');
    }

    public function test_show_returns_404_for_draft_property(): void
    {
        $property = Property::factory()->draft()->create();

        $this->get(route('properties.show', $property))->assertNotFound();
    }

    public function test_show_links_signed_in_user_to_their_existing_engagement(): void
    {
        $user = User::factory()->create();
        $property = Property::factory()->create();
        $engagement = Engagement::factory()->forUser($user)->for($property)->create();

        $response = $this->actingAs($user)->get(route('properties.show', $property));

        $response->assertSeeText("You're engaged");
        $response->assertSee(route('account.engagements.show', $engagement), false);
    }

    public function test_show_tells_visitors_a_sold_property_is_no_longer_available(): void
    {
        $property = Property::factory()->sold()->create();

        $response = $this->get(route('properties.show', $property));

        $response->assertOk();
        $response->assertSeeText('no longer accepting new engagements');
        $response->assertDontSee(route('properties.engage', $property), false);
    }

    public function test_index_shows_live_listings_advert_as_sponsored_card_after_third_property(): void
    {
        Property::factory()->count(4)->sequence(fn ($sequence) => ['title' => 'Home '.$sequence->index, 'published_at' => now()->subDays($sequence->index)])->create();
        Advert::factory()->inListingsGrid()->create(['title' => 'Partner Offer']);

        $this->get(route('properties.index'))
            ->assertSeeTextInOrder(['Home 0', 'Home 1', 'Home 2', 'Sponsored', 'Partner Offer', 'Home 3']);
    }

    public function test_index_hides_paused_scheduled_and_expired_adverts(): void
    {
        Property::factory()->create();
        Advert::factory()->inListingsGrid()->paused()->create(['title' => 'Paused Ad']);
        Advert::factory()->inListingsGrid()->scheduled()->create(['title' => 'Future Ad']);
        Advert::factory()->inListingsGrid()->expired()->create(['title' => 'Old Ad']);

        $this->get(route('properties.index'))
            ->assertDontSeeText('Paused Ad')
            ->assertDontSeeText('Future Ad')
            ->assertDontSeeText('Old Ad');
    }

    public function test_show_displays_only_property_page_adverts(): void
    {
        $property = Property::factory()->create();
        Advert::factory()->at(AdvertPage::Property, 'about', AdvertPosition::After)->create(['title' => 'Moving Service']);
        Advert::factory()->create(['title' => 'Banner Only']);

        $this->get(route('properties.show', $property))
            ->assertSeeText('Moving Service')
            ->assertDontSeeText('Banner Only');
    }

    public function test_index_gives_up_property_slots_to_inside_adverts_so_rows_stay_full(): void
    {
        Property::factory()->count(10)->create();

        $this->get(route('properties.index'))->assertViewHas('properties', fn ($paginator) => $paginator->count() === 9);

        Advert::factory()->inListingsGrid()->create();

        $this->get(route('properties.index'))->assertOk()->assertViewHas('properties', fn ($paginator) => $paginator->count() === 8);

        Advert::factory()->inListingsGrid()->create();

        $this->get(route('properties.index'))->assertOk()->assertViewHas('properties', fn ($paginator) => $paginator->count() === 7);
    }

    public function test_two_half_width_adverts_share_a_row_after_the_results(): void
    {
        Property::factory()->create();
        Advert::factory()->at(AdvertPage::Listings, 'grid', AdvertPosition::After, AdvertWidth::Half)->create(['title' => 'Left Half', 'sort_order' => 1]);
        Advert::factory()->at(AdvertPage::Listings, 'grid', AdvertPosition::After, AdvertWidth::Half)->create(['title' => 'Right Half', 'sort_order' => 2]);

        $response = $this->get(route('properties.index'));

        $response->assertSeeInOrder(['data-advert-slot="grid:after"', 'md:col-span-6', 'Left Half', 'md:col-span-6', 'Right Half'], false);
    }

    public function test_index_filters_shortlets_verified_featured_state_and_size(): void
    {
        $match = Property::factory()->shortlet()->verified()->featured()->create(['state' => 'Lagos', 'area_sqm' => 300]);
        $unverified = Property::factory()->shortlet()->featured()->create(['state' => 'Lagos', 'area_sqm' => 300]);
        $otherState = Property::factory()->shortlet()->verified()->featured()->create(['state' => 'Abuja', 'area_sqm' => 300]);
        $tooBig = Property::factory()->shortlet()->verified()->featured()->create(['state' => 'Lagos', 'area_sqm' => 2000]);
        $forSale = Property::factory()->verified()->featured()->create(['state' => 'Lagos', 'area_sqm' => 300]);

        $response = $this->get(route('properties.index', ['listing' => 'shortlet', 'verified' => 1, 'featured' => 1, 'state' => 'Lagos', 'size' => '250-500']));

        $response->assertOk()->assertSeeText('Shortlet apartments');
        $response->assertSee(route('properties.show', $match), false);
        foreach ([$unverified, $otherState, $tooBig, $forSale] as $excluded) {
            $response->assertDontSee(route('properties.show', $excluded), false);
        }
    }

    public function test_shortlet_price_is_shown_per_night_and_verification_on_the_page(): void
    {
        $property = Property::factory()->shortlet()->verified()->create(['price' => 120_000]);

        $this->get(route('properties.show', $property))
            ->assertOk()
            ->assertSeeText('₦120,000 /night')
            ->assertSeeText('Title verified');
    }
}
