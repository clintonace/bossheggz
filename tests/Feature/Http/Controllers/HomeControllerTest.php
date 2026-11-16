<?php

namespace Tests\Feature\Http\Controllers;

use App\Enums\AdvertPage;
use App\Enums\AdvertPosition;
use App\Models\Advert;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_renders_published_and_featured_properties(): void
    {
        Property::factory()->featured()->create(['title' => 'Featured Villa']);
        Property::factory()->create(['title' => 'Regular Flat']);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSeeText('Featured Villa');
        $response->assertSeeText('Regular Flat');
    }

    public function test_does_not_show_draft_properties(): void
    {
        Property::factory()->draft()->featured()->create(['title' => 'Secret Draft']);

        $this->get(route('home'))->assertOk()->assertDontSeeText('Secret Draft');
    }

    public function test_renders_home_advert_with_tracked_sponsored_link(): void
    {
        $advert = Advert::factory()->create(['title' => 'Home Loans Partner']);

        $this->get(route('home'))
            ->assertSeeText('Home Loans Partner')
            ->assertSee(route('adverts.click', $advert), false)
            ->assertSee('rel="sponsored noopener"', false);
    }

    public function test_places_adverts_before_or_after_the_chosen_section(): void
    {
        Property::factory()->featured()->create();
        Advert::factory()->at(AdvertPage::Home, 'featured', AdvertPosition::Before)->create(['title' => 'Above Featured']);
        Advert::factory()->at(AdvertPage::Home, 'how_it_works', AdvertPosition::After)->create(['title' => 'Below How It Works']);

        $this->get(route('home'))->assertSeeTextInOrder(['Above Featured', 'Featured properties', 'How it works', 'Below How It Works', 'Latest on the market']);
    }

    public function test_full_width_advert_spans_the_whole_row(): void
    {
        Advert::factory()->create(['title' => 'Wide Banner']);

        $this->get(route('home'))->assertSeeInOrder(['data-advert-slot="latest:after"', 'class="col-span-12"', 'Wide Banner'], false);
    }

    public function test_shows_popup_targeted_at_home_or_all_pages(): void
    {
        Advert::factory()->popup('home', 12)->create(['title' => 'Home Popup']);

        $this->get(route('home'))
            ->assertSee('data-advert-popup', false)
            ->assertSee('data-seconds="12"', false)
            ->assertSeeText('Home Popup')
            ->assertSee('aria-label="Close advert"', false);
    }

    public function test_does_not_show_popup_targeted_at_another_page_or_paused(): void
    {
        Advert::factory()->popup('listings')->create(['title' => 'Listings Popup']);
        Advert::factory()->popup('all')->paused()->create(['title' => 'Paused Popup']);

        $this->get(route('home'))
            ->assertDontSeeText('Listings Popup')
            ->assertDontSeeText('Paused Popup')
            ->assertDontSee('data-advert-popup', false);
    }

    public function test_all_pages_popup_is_not_shown_inside_the_client_dashboard(): void
    {
        Advert::factory()->popup('all')->create(['title' => 'Everywhere Popup']);

        $this->get(route('properties.index'))->assertSeeText('Everywhere Popup');
        $this->actingAs(User::factory()->create())->get(route('account.index'))->assertDontSeeText('Everywhere Popup');
    }
}
