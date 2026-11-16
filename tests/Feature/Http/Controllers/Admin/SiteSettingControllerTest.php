<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Enums\SiteArea;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class SiteSettingControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return [
            'accent' => '#0F8A6C',
            'primary' => '#0f1f1b',
            'background' => '#f5f8f6',
            'heading_font' => 'playfair-display',
            'body_font' => 'inter',
            'font_size' => '17',
            'mode' => 'dark',
            'footer' => 'light',
            'animations' => '0',
            ...$overrides,
        ];
    }

    #[TestWith(['landing', 'Footer style'])]
    #[TestWith(['client', 'Sidebar style'])]
    #[TestWith(['admin', 'Sidebar style'])]
    public function test_edit_page_renders_for_each_area(string $area, string $chromeLabel): void
    {
        $this->actingAs($this->admin)->get(route('admin.site.edit', $area))
            ->assertOk()
            ->assertSeeText($chromeLabel)
            ->assertSeeText('Live preview');
    }

    public function test_unknown_area_returns_404(): void
    {
        $this->actingAs($this->admin)->get('/admin/site/checkout')->assertNotFound();
    }

    public function test_regular_user_cannot_change_the_site(): void
    {
        $this->actingAs(User::factory()->create())
            ->put(route('admin.site.update', 'landing'), $this->payload())
            ->assertForbidden();

        $this->assertDatabaseCount('site_settings', 0);
    }

    public function test_admin_saves_landing_appearance(): void
    {
        $this->actingAs($this->admin)->put(route('admin.site.update', 'landing'), $this->payload())
            ->assertSessionHas('success', 'Landing page appearance saved.');

        $setting = SiteSetting::for(SiteArea::Landing);
        $this->assertTrue($setting->exists);
        $this->assertSame('#0f8a6c', $setting->value('accent'));
        $this->assertSame('playfair-display', $setting->value('heading_font'));
        $this->assertSame(17, $setting->value('font_size'));
        $this->assertSame('dark', $setting->mode());
        $this->assertFalse($setting->footerIsDark());
        $this->assertFalse($setting->animationsEnabled());
    }

    public function test_invalid_values_are_rejected(): void
    {
        $this->actingAs($this->admin)->put(route('admin.site.update', 'landing'), $this->payload([
            'accent' => 'red',
            'heading_font' => 'comic-sans',
            'font_size' => '40',
            'mode' => 'sepia',
        ]))->assertSessionHasErrors([
            'accent' => 'Use a 6-digit hex colour like #b88a41.',
            'heading_font',
            'font_size',
            'mode',
        ]);

        $this->assertDatabaseCount('site_settings', 0);
    }

    public function test_reset_restores_the_default_look(): void
    {
        SiteSetting::create(['area' => SiteArea::Client, 'settings' => ['mode' => 'dark']]);

        $this->actingAs($this->admin)->delete(route('admin.site.reset', 'client'))
            ->assertSessionHas('success', 'Client dashboard reset to the default look.');

        $this->assertDatabaseCount('site_settings', 0);
    }

    public function test_landing_theme_is_applied_to_public_pages_only(): void
    {
        SiteSetting::create(['area' => SiteArea::Landing, 'settings' => $this->payload(['accent' => '#0f8a6c'])]);

        $this->get(route('home'))
            ->assertSee('data-mode="dark" data-motion="reduced"', false)
            ->assertSee('--color-brass-500:#0f8a6c', false)
            ->assertSee('https://fonts.bunny.net/css?family=playfair-display:400,500,600,400i|inter:400,500,600,700&amp;display=swap', false)
            ->assertSee('font-size:17px', false)
            ->assertSee('<footer data-palette="inverted"', false);

        $this->actingAs(User::factory()->create())->get(route('account.index'))
            ->assertSee('data-mode="light" data-motion="full"', false)
            ->assertDontSee('#0f8a6c', false);
    }

    public function test_default_fonts_need_no_external_stylesheet(): void
    {
        $this->get(route('home'))->assertDontSee('fonts.bunny.net', false);
    }

    public function test_sidebars_follow_the_colour_mode_by_default(): void
    {
        $this->actingAs($this->admin)->get(route('admin.dashboard'))->assertSee('<aside id="admin-sidebar" data-toggle-panel  class=', false);
        $this->actingAs($this->admin)->get(route('account.index'))->assertSee('<aside id="client-sidebar" data-toggle-panel  class=', false);
    }

    public function test_sidebar_can_be_pinned_light_or_dark_per_dashboard(): void
    {
        SiteSetting::create(['area' => SiteArea::Admin, 'settings' => ['sidebar' => 'dark']]);
        SiteSetting::create(['area' => SiteArea::Client, 'settings' => ['sidebar' => 'light']]);

        $this->actingAs($this->admin)->get(route('admin.dashboard'))->assertSee('id="admin-sidebar" data-toggle-panel  data-palette="inverted"', false);
        $this->actingAs($this->admin)->get(route('account.index'))->assertSee('id="client-sidebar" data-toggle-panel  data-palette="normal"', false);
    }

    public function test_sidebar_option_accepts_match_mode(): void
    {
        $this->actingAs($this->admin)->put(route('admin.site.update', 'admin'), [...$this->payload(), 'sidebar' => 'auto'])
            ->assertSessionHasNoErrors();

        $this->actingAs($this->admin)->put(route('admin.site.update', 'admin'), [...$this->payload(), 'sidebar' => 'purple'])
            ->assertSessionHasErrors('sidebar');
    }

    public function test_match_device_mode_uses_the_system_preference(): void
    {
        SiteSetting::create(['area' => SiteArea::Landing, 'settings' => ['mode' => 'system']]);

        $this->get(route('home'))
            ->assertSee('data-mode="system"', false)
            ->assertSee('@media (prefers-color-scheme:dark){:root[data-mode=system]{', false);
    }

    public function test_live_preview_receives_valid_font_and_colour_data(): void
    {
        $html = $this->actingAs($this->admin)->get(route('admin.site.edit', 'landing'))->getContent();

        preg_match("/data-fonts='([^']*)'/", $html, $fonts);
        preg_match("/data-recipes='([^']*)'/", $html, $recipes);

        $this->assertSame("'Playfair Display', ui-serif, Georgia, serif", json_decode(html_entity_decode($fonts[1]), true)['playfair-display']['stack']);
        $this->assertArrayHasKey('--color-brass-500', json_decode(html_entity_decode($recipes[1]), true)['light']);
    }
}
