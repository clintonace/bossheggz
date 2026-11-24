<?php

namespace Tests\Feature\Http\Controllers;

use App\Enums\LandingPage;
use App\Models\PageSection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class LandingPageContentTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, array{LandingPage}>
     */
    public static function pages(): array
    {
        return collect(LandingPage::cases())->mapWithKeys(fn (LandingPage $page) => [$page->value => [$page]])->all();
    }

    #[DataProvider('pages')]
    public function test_every_page_renders_its_default_content(LandingPage $page): void
    {
        $heading = $page === LandingPage::Properties ? 'header' : 'hero';

        $this->get($page->url())
            ->assertOk()
            ->assertSee(PageSection::fillTokens(config("page_content.pages.{$page->value}.{$heading}.fields.title.default")));
    }

    #[DataProvider('pages')]
    public function test_every_page_shows_edited_text(LandingPage $page): void
    {
        $heading = $page === LandingPage::Properties ? 'header' : 'hero';
        PageSection::factory()->section($page, $heading)->create(['content' => ['title' => 'Edited heading for '.$page->value]]);

        $this->get($page->url())->assertOk()->assertSee('Edited heading for '.$page->value);
    }

    public function test_hidden_sections_are_not_shown(): void
    {
        PageSection::factory()->section(LandingPage::Home, 'guarantee')->hidden()->create(['content' => []]);
        PageSection::factory()->section(LandingPage::Home, 'cta')->hidden()->create(['content' => []]);

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('Property title verification')
            ->assertDontSee('Your Next Property Investment Starts Here.')
            ->assertSee('Everything property, in one place');
    }

    public function test_company_tokens_are_filled_in(): void
    {
        config(['listings.company.name' => 'Acme Realty', 'listings.company.tagline' => 'Build with Acme.']);
        PageSection::factory()->section(LandingPage::About, 'hero')->create(['content' => ['title' => 'Meet {company_short}', 'description' => '{company} — {tagline}']]);

        $this->get(route('about'))
            ->assertSee('Meet Acme')
            ->assertSee('Acme Realty — Build with Acme.')
            ->assertDontSee('{company}');
    }

    public function test_edited_lists_replace_the_defaults(): void
    {
        PageSection::factory()->section(LandingPage::About, 'values')->create(['content' => [
            'values' => [['icon' => 'star', 'title' => 'Excellence']],
        ]]);
        PageSection::factory()->section(LandingPage::Services, 'management')->create(['content' => [
            'items' => ['Facility management'],
        ]]);

        $this->get(route('about'))->assertSee('Excellence')->assertDontSee('Professionalism');
        $this->get(route('services'))->assertSee('Facility management')->assertDontSee('Occupancy management');
    }

    public function test_hero_buttons_and_images_follow_the_content(): void
    {
        PageSection::factory()->section(LandingPage::Investments, 'hero')->create(['content' => [
            'primary_label' => 'Book a call',
            'primary_url' => '/contact?type=consultation',
            'secondary_label' => null,
            'image' => 'https://example.com/hero.jpg',
        ]]);

        $this->get(route('investments.index'))
            ->assertSee('Book a call')
            ->assertDontSee('Express Interest</a>', false)
            ->assertSee('https://example.com/hero.jpg');
    }

    public function test_search_title_and_description_can_be_changed(): void
    {
        PageSection::factory()->section(LandingPage::Contact, 'meta')->create(['content' => ['title' => 'Talk to us', 'description' => 'Reach our advisers today.']]);

        $this->get(route('contact'))
            ->assertSee('<title>Talk to us · '.config('listings.company.name').'</title>', false)
            ->assertSee('content="Reach our advisers today."', false);
    }
}
