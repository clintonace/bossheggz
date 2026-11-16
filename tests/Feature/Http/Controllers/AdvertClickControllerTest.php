<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Advert;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdvertClickControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_click_is_counted_and_redirects_to_advertiser(): void
    {
        $advert = Advert::factory()->create(['link_url' => 'https://example.com/offer']);

        $this->get(route('adverts.click', $advert))->assertRedirect('https://example.com/offer');

        $this->assertSame(1, $advert->fresh()->clicks);
    }

    public function test_paused_advert_returns_404_without_counting(): void
    {
        $advert = Advert::factory()->paused()->create();

        $this->get(route('adverts.click', $advert))->assertNotFound();

        $this->assertSame(0, $advert->fresh()->clicks);
    }

    public function test_expired_advert_returns_404(): void
    {
        $this->get(route('adverts.click', Advert::factory()->expired()->create()))->assertNotFound();
    }
}
