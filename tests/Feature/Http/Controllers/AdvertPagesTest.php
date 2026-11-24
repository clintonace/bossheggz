<?php

namespace Tests\Feature\Http\Controllers;

use App\Enums\AdvertFormat;
use App\Enums\AdvertPage;
use App\Enums\AdvertPosition;
use App\Enums\SiteArea;
use App\Models\Advert;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AdvertPagesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, array{AdvertPage}>
     */
    public static function newPages(): array
    {
        return collect([AdvertPage::Investments, AdvertPage::LandBanking, AdvertPage::Services, AdvertPage::Insights, AdvertPage::About, AdvertPage::Contact])
            ->mapWithKeys(fn (AdvertPage $page) => [$page->value => [$page]])
            ->all();
    }

    #[DataProvider('newPages')]
    public function test_every_section_of_the_new_pages_can_hold_adverts(AdvertPage $page): void
    {
        foreach (array_keys($page->sections()) as $section) {
            foreach ([AdvertPosition::Before, AdvertPosition::After] as $position) {
                Advert::factory()->at($page, $section, $position)->create(['title' => "Ad {$section} {$position->value}"]);
            }
        }

        $response = $this->get($page->url())->assertOk();

        foreach (array_keys($page->sections()) as $section) {
            $response->assertSee("data-advert-slot=\"{$section}:before\"", false)
                ->assertSee("data-advert-slot=\"{$section}:after\"", false)
                ->assertSee("Ad {$section} before");
        }
    }

    #[DataProvider('newPages')]
    public function test_pop_ups_can_target_the_new_pages(AdvertPage $page): void
    {
        Advert::factory()->create([
            'format' => AdvertFormat::Popup,
            'page' => $page->value,
            'section' => null,
            'position' => null,
            'display_seconds' => 6,
            'title' => 'Page pop-up',
        ]);

        $this->get($page->url())->assertSee('data-advert-popup=', false)->assertSee('Page pop-up');
        $this->get(route('home'))->assertDontSee('Page pop-up');
    }

    public function test_admin_page_map_lists_the_new_pages(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('admin.adverts.index', ['view' => 'land_banking']))
            ->assertOk()
            ->assertSee('Land banking page layout')
            ->assertSee('Filters &amp; land opportunities', false)
            ->assertSee(route('land-banking.index'));

        $this->actingAs($admin)->post(route('admin.adverts.store'), [
            'title' => 'Survey services',
            'cta_label' => 'Book a survey',
            'link_url' => 'https://example.com/survey',
            'format' => 'inline',
            'page' => 'land_banking',
            'section' => 'why',
            'position' => 'after',
            'width' => 'full',
            'is_active' => '1',
        ])->assertSessionHasNoErrors();

        $this->get(route('land-banking.index'))->assertSee('Survey services');
    }

    public function test_preloader_shows_on_the_public_site_only(): void
    {
        $this->get(route('home'))
            ->assertSee('data-site-preloader', false)
            ->assertSee('site-preloader-seen', false);

        $this->get(route('login'))->assertDontSee('data-site-preloader', false);
        $this->actingAs(User::factory()->create())->get(route('account.index'))->assertDontSee('data-site-preloader', false);
        $this->actingAs(User::factory()->admin()->create())->get(route('admin.dashboard'))->assertDontSee('data-site-preloader', false);
    }

    public function test_preloader_can_be_switched_off_and_skips_when_animations_are_off(): void
    {
        SiteSetting::create(['area' => SiteArea::Landing, 'settings' => ['preloader' => false]]);
        $this->get(route('home'))->assertDontSee('data-site-preloader', false);

        SiteSetting::where('area', SiteArea::Landing)->update(['settings' => ['preloader' => true, 'animations' => false]]);
        $this->get(route('about'))->assertDontSee('data-site-preloader', false);
    }

    public function test_site_manager_saves_the_preloader_setting(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->put(route('admin.site.update', 'landing'), [
            'accent' => '#b88a41', 'primary' => '#121825', 'background' => '#faf7f2',
            'heading_font' => 'fraunces', 'body_font' => 'instrument-sans', 'font_size' => '16',
            'mode' => 'light', 'footer' => 'dark', 'animations' => '1', 'glass' => 'frosted',
            'preloader' => '0',
        ])->assertSessionHasNoErrors();

        $this->assertFalse(SiteSetting::for(SiteArea::Landing)->preloaderEnabled());
        $this->actingAs($admin)->get(route('admin.site.edit', 'landing'))->assertSeeText('Branded preloader');
        $this->actingAs($admin)->get(route('admin.site.edit', 'admin'))->assertDontSeeText('Branded preloader');
    }
}
