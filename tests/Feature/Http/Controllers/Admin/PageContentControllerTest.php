<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Enums\LandingPage;
use App\Models\PageSection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PageContentControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
    }

    /**
     * The form exactly as the edit page submits it with nothing changed, plus overrides.
     *
     * @param  array<string, array<string, mixed>>  $sections
     * @param  array<string, string>  $visibility
     * @return array<string, mixed>
     */
    private function payload(LandingPage $page, array $sections = [], array $visibility = []): array
    {
        $current = $page->content()->map(fn (PageSection $section) => collect($section->fields())->map(fn (array $field, string $name) => match ($field['type']) {
            'lines' => implode("\n", $section->raw($name) ?? []),
            'items' => collect($section->raw($name))->map(fn (array $row) => collect($row)
                ->map(fn (mixed $value) => is_array($value) ? implode("\n", $value) : $value)
                ->all())->all(),
            default => $section->raw($name),
        })->all())->all();

        return [
            'sections' => array_replace_recursive($current, $sections),
            'visibility' => [...$page->content()->filter->isHideable()->map(fn () => '1')->all(), ...$visibility],
        ];
    }

    public function test_index_lists_every_landing_page(): void
    {
        PageSection::factory()->section(LandingPage::About, 'hero')->create();

        $response = $this->actingAs($this->admin)->get(route('admin.pages.index'));

        $response->assertOk();
        foreach (LandingPage::cases() as $page) {
            $response->assertSee(route('admin.pages.edit', $page));
        }
        $response->assertSee('1 edited');
    }

    public function test_pages_are_admin_only(): void
    {
        $this->get(route('admin.pages.edit', LandingPage::Home))->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create())->get(route('admin.pages.index'))->assertForbidden();
        $this->actingAs(User::factory()->create())->put(route('admin.pages.update', LandingPage::Home), [])->assertForbidden();
    }

    public function test_edit_shows_sections_with_their_default_content(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.pages.edit', LandingPage::Home));

        $response->assertOk()
            ->assertSee('How it works')
            ->assertSee('value="Strategic property."', false)
            ->assertSee('Track progress')
            ->assertSee('name="sections[guarantee][promises][0][title]"', false);
    }

    public function test_unknown_page_is_not_found(): void
    {
        $this->actingAs($this->admin)->get('/admin/pages/blog')->assertNotFound();
    }

    public function test_saving_without_changes_stores_nothing(): void
    {
        $this->actingAs($this->admin)
            ->put(route('admin.pages.update', LandingPage::Home), $this->payload(LandingPage::Home))
            ->assertRedirect(route('admin.pages.edit', LandingPage::Home))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('page_sections', 0);
    }

    public function test_only_changed_fields_are_stored(): void
    {
        $this->actingAs($this->admin)
            ->put(route('admin.pages.update', LandingPage::Home), $this->payload(LandingPage::Home, [
                'hero' => ['title' => 'Homes worth keeping.', 'description' => ''],
            ]))
            ->assertSessionHasNoErrors();

        $hero = PageSection::sole();
        $this->assertSame('hero', $hero->section);
        $this->assertSame(['title' => 'Homes worth keeping.', 'description' => null], $hero->content);
        $this->assertSame('Lasting wealth.', $hero->raw('title_highlight'));
    }

    public function test_putting_a_field_back_to_its_default_removes_the_row(): void
    {
        PageSection::factory()->section(LandingPage::Home, 'hero')->create(['content' => ['title' => 'Old']]);

        $this->actingAs($this->admin)
            ->put(route('admin.pages.update', LandingPage::Home), $this->payload(LandingPage::Home, ['hero' => ['title' => 'Strategic property.']]))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('page_sections', 0);
    }

    public function test_sections_can_be_hidden_but_required_ones_cannot(): void
    {
        $this->actingAs($this->admin)
            ->put(route('admin.pages.update', LandingPage::Home), $this->payload(LandingPage::Home, visibility: ['guarantee' => '0', 'hero' => '0']))
            ->assertSessionHasNoErrors();

        $content = LandingPage::Home->content();
        $this->assertFalse($content['guarantee']->isShown());
        $this->assertTrue($content['hero']->isShown());
        $this->assertDatabaseMissing('page_sections', ['section' => 'hero']);
    }

    public function test_repeatable_items_are_saved_in_order_without_empty_rows(): void
    {
        $payload = $this->payload(LandingPage::Investments);
        $payload['sections']['how_it_works']['steps'] = [
            'n2' => ['icon' => 'phone', 'title' => 'Call us', 'text' => ''],
            'n1' => ['icon' => 'sparkles', 'title' => '', 'text' => ''],
            '0' => ['icon' => 'search', 'title' => 'Browse', 'text' => 'Look around.'],
        ];

        $this->actingAs($this->admin)
            ->put(route('admin.pages.update', LandingPage::Investments), $payload)
            ->assertSessionHasNoErrors();

        $this->assertSame([
            ['icon' => 'phone', 'title' => 'Call us', 'text' => null],
            ['icon' => 'search', 'title' => 'Browse', 'text' => 'Look around.'],
        ], LandingPage::Investments->content()['how_it_works']->raw('steps'));
    }

    public function test_a_list_can_be_emptied(): void
    {
        $payload = $this->payload(LandingPage::About);
        $payload['sections']['values']['values'] = '';

        $this->actingAs($this->admin)
            ->put(route('admin.pages.update', LandingPage::About), $payload)
            ->assertSessionHasNoErrors();

        $this->assertSame([], LandingPage::About->content()['values']->raw('values'));
    }

    public function test_line_lists_are_split_into_entries(): void
    {
        $this->actingAs($this->admin)
            ->put(route('admin.pages.update', LandingPage::Services), $this->payload(LandingPage::Services, [
                'management' => ['items' => "Tenant sourcing\r\n\r\n  Facility management  \nRent collection"],
            ]))
            ->assertSessionHasNoErrors();

        $this->assertSame(['Tenant sourcing', 'Facility management', 'Rent collection'], LandingPage::Services->content()['management']->raw('items'));
    }

    public function test_uploading_an_image_replaces_the_previous_upload(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('page-content/old.jpg', 'old');
        PageSection::factory()->section(LandingPage::About, 'overview')->create(['content' => ['image' => 'page-content/old.jpg']]);

        $this->actingAs($this->admin)
            ->put(route('admin.pages.update', LandingPage::About), [
                ...$this->payload(LandingPage::About),
                'uploads' => ['overview' => ['image' => UploadedFile::fake()->image('team.jpg', 1200, 900)]],
            ])
            ->assertSessionHasNoErrors();

        $path = LandingPage::About->content()['overview']->raw('image');
        $this->assertStringStartsWith('page-content/', $path);
        Storage::disk('public')->assertExists($path);
        Storage::disk('public')->assertMissing('page-content/old.jpg');
    }

    public function test_invalid_content_is_rejected(): void
    {
        $this->actingAs($this->admin)
            ->from(route('admin.pages.edit', LandingPage::Investments))
            ->put(route('admin.pages.update', LandingPage::Investments), $this->payload(LandingPage::Investments, [
                'hero' => ['title' => '', 'primary_url' => 'javascript:alert(1)', 'image' => 'ftp://example.com/a.jpg'],
                'how_it_works' => ['steps' => [['icon' => 'not-an-icon', 'title' => 'Browse']]],
            ]))
            ->assertRedirect(route('admin.pages.edit', LandingPage::Investments))
            ->assertSessionHasErrors([
                'sections.hero.title',
                'sections.hero.primary_url',
                'sections.hero.image',
                'sections.how_it_works.steps.0.icon',
            ]);

        $this->assertDatabaseCount('page_sections', 0);
    }

    public function test_a_section_can_be_reset_to_default(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('page-content/team.jpg', 'image');
        PageSection::factory()->section(LandingPage::About, 'overview')->hidden()->create(['content' => ['title' => 'Changed', 'image' => 'page-content/team.jpg']]);

        $this->actingAs($this->admin)
            ->delete(route('admin.pages.reset', [LandingPage::About, 'overview']))
            ->assertRedirect(route('admin.pages.edit', LandingPage::About).'#section-overview');

        $this->assertDatabaseCount('page_sections', 0);
        Storage::disk('public')->assertMissing('page-content/team.jpg');
        $this->assertTrue(LandingPage::About->content()['overview']->isShown());
    }

    public function test_resetting_an_unknown_section_is_not_found(): void
    {
        $this->actingAs($this->admin)->delete(route('admin.pages.reset', [LandingPage::About, 'nope']))->assertNotFound();
    }

    public function test_sidebar_has_a_pages_dropdown(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.pages.edit', LandingPage::LandBanking))
            ->assertSee('id="nav-pages"', false)
            ->assertSee(route('admin.pages.edit', LandingPage::Contact));
    }
}
