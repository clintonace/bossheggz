<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Enums\AdvertFormat;
use App\Enums\AdvertPage;
use App\Enums\AdvertPosition;
use App\Enums\AdvertWidth;
use App\Models\Advert;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdvertControllerTest extends TestCase
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
            'title' => 'Low-rate home loans',
            'sponsor' => 'Acme Bank',
            'body' => 'Rates from 9%.',
            'cta_label' => 'Check eligibility',
            'link_url' => 'https://example.com/loans',
            'format' => 'inline',
            'page' => 'home',
            'section' => 'featured',
            'position' => 'before',
            'width' => 'half',
            'sort_order' => '2',
            'is_active' => '1',
            ...$overrides,
        ];
    }

    public function test_index_lists_adverts_with_their_state(): void
    {
        Advert::factory()->paused()->create(['title' => 'Paused Promo']);

        $this->actingAs($this->admin)->get(route('admin.adverts.index'))
            ->assertOk()
            ->assertSeeText('Paused Promo')
            ->assertSeeText('paused');
    }

    public function test_admin_creates_advert_with_image(): void
    {
        Storage::fake('public');

        $response = $this->actingAs($this->admin)->post(route('admin.adverts.store'), $this->payload([
            'image' => UploadedFile::fake()->image('banner.jpg', 1200, 600),
        ]));

        $advert = Advert::sole();
        $response->assertRedirect(route('admin.adverts.edit', $advert));
        $this->assertSame(AdvertFormat::Inline, $advert->format);
        $this->assertSame('home', $advert->page);
        $this->assertSame('featured', $advert->section);
        $this->assertSame(AdvertPosition::Before, $advert->position);
        $this->assertSame(AdvertWidth::Half, $advert->width);
        $this->assertSame(2, $advert->sort_order);
        $this->assertTrue($advert->is_active);
        Storage::disk('public')->assertExists($advert->image_path);
    }

    public function test_edit_page_shows_live_preview(): void
    {
        $advert = Advert::factory()->create(['title' => 'Preview Me']);

        $this->actingAs($this->admin)->get(route('admin.adverts.edit', $advert))
            ->assertOk()
            ->assertSeeTextInOrder(['Preview', 'Preview Me', 'Sponsored']);
    }

    public function test_link_must_be_a_full_web_address(): void
    {
        $this->actingAs($this->admin)->post(route('admin.adverts.store'), $this->payload(['link_url' => 'javascript:alert(1)']))
            ->assertSessionHasErrors(['link_url' => 'Enter a full web address starting with http:// or https://.']);

        $this->assertDatabaseCount('adverts', 0);
    }

    public function test_end_date_must_follow_start_date(): void
    {
        $this->actingAs($this->admin)->post(route('admin.adverts.store'), $this->payload([
            'starts_at' => '2026-12-10T09:00',
            'ends_at' => '2026-12-01T09:00',
        ]))->assertSessionHasErrors(['ends_at' => 'The end date must be after the start date.']);
    }

    public function test_end_date_alone_is_allowed(): void
    {
        $this->actingAs($this->admin)->post(route('admin.adverts.store'), $this->payload(['ends_at' => '2030-01-01T00:00']))
            ->assertSessionHasNoErrors();
    }

    public function test_replacing_image_deletes_the_old_file(): void
    {
        Storage::fake('public');
        $advert = Advert::factory()->create();
        $advert->image_path = UploadedFile::fake()->image('old.jpg')->store('adverts', 'public');
        $advert->save();
        $oldPath = $advert->image_path;

        $this->actingAs($this->admin)->put(route('admin.adverts.update', $advert), $this->payload([
            'image' => UploadedFile::fake()->image('new.jpg'),
        ]))->assertSessionHas('success', 'Advert updated.');

        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($advert->fresh()->image_path);
    }

    public function test_toggle_pauses_and_resumes(): void
    {
        $advert = Advert::factory()->create();

        $this->actingAs($this->admin)->patch(route('admin.adverts.toggle', $advert));
        $this->assertFalse($advert->fresh()->is_active);

        $this->actingAs($this->admin)->patch(route('admin.adverts.toggle', $advert));
        $this->assertTrue($advert->fresh()->is_active);
    }

    public function test_admin_deletes_advert(): void
    {
        $advert = Advert::factory()->create();

        $this->actingAs($this->admin)->delete(route('admin.adverts.destroy', $advert))
            ->assertRedirect(route('admin.adverts.index'));

        $this->assertModelMissing($advert);
    }

    public function test_regular_user_cannot_manage_adverts(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('admin.adverts.store'), $this->payload())
            ->assertForbidden();

        $this->assertDatabaseCount('adverts', 0);
    }

    public function test_section_must_belong_to_the_chosen_page(): void
    {
        $this->actingAs($this->admin)->post(route('admin.adverts.store'), $this->payload(['page' => 'listings', 'section' => 'featured']))
            ->assertSessionHasErrors(['section' => 'Choose a section that exists on the selected page.']);
    }

    public function test_inside_position_is_only_allowed_for_the_property_grid(): void
    {
        $this->actingAs($this->admin)->post(route('admin.adverts.store'), $this->payload(['position' => 'inside']))
            ->assertSessionHasErrors(['position' => 'That position isn’t available for the selected section.']);

        $this->actingAs($this->admin)->post(route('admin.adverts.store'), $this->payload(['page' => 'listings', 'section' => 'grid', 'position' => 'inside']))
            ->assertSessionHasNoErrors();
    }

    public function test_popup_is_stored_without_section_or_position(): void
    {
        $this->actingAs($this->admin)->post(route('admin.adverts.store'), $this->payload([
            'format' => 'popup',
            'page' => 'all',
            'display_seconds' => '10',
        ]))->assertSessionHasNoErrors();

        $advert = Advert::sole();
        $this->assertTrue($advert->isPopup());
        $this->assertSame('all', $advert->page);
        $this->assertNull($advert->section);
        $this->assertNull($advert->position);
        $this->assertSame(10, $advert->display_seconds);
    }

    public function test_popup_duration_must_be_between_3_and_60_seconds(): void
    {
        $this->actingAs($this->admin)->post(route('admin.adverts.store'), $this->payload(['format' => 'popup', 'page' => 'all', 'display_seconds' => '1']))
            ->assertSessionHasErrors(['display_seconds' => 'Show the pop-up for at least 3 seconds.']);
    }

    public function test_inline_advert_cannot_target_all_pages(): void
    {
        $this->actingAs($this->admin)->post(route('admin.adverts.store'), $this->payload(['page' => 'all']))
            ->assertSessionHasErrors('page');
    }

    public function test_page_map_shows_each_advert_in_its_spot(): void
    {
        Advert::factory()->at(AdvertPage::Home, 'featured', AdvertPosition::Before)->create(['title' => 'Before Featured Ad']);
        Advert::factory()->at(AdvertPage::Listings, 'grid', AdvertPosition::After)->create(['title' => 'Listings Ad']);

        $response = $this->actingAs($this->admin)->get(route('admin.adverts.index', ['view' => 'home']));

        $response->assertOk();
        $response->assertSeeInOrder(['data-admin-slot="featured:before"', 'Before Featured Ad', 'Featured properties', 'data-admin-slot="featured:after"'], false);
        $response->assertDontSeeText('Listings Ad');
        $response->assertSee(e(route('admin.adverts.create', ['format' => 'inline', 'page' => 'home', 'section' => 'latest', 'position' => 'after'])), false);
    }

    public function test_popup_view_lists_only_popups(): void
    {
        Advert::factory()->popup()->create(['title' => 'Big Launch']);
        Advert::factory()->create(['title' => 'Inline Banner']);

        $this->actingAs($this->admin)->get(route('admin.adverts.index', ['view' => 'popup']))
            ->assertSeeText('Big Launch')
            ->assertDontSeeText('Inline Banner');
    }

    public function test_create_form_is_prefilled_from_the_chosen_spot(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.adverts.create', ['format' => 'inline', 'page' => 'listings', 'section' => 'grid', 'position' => 'inside']));

        $response->assertOk();
        $response->assertSee('value="grid" data-page="listings" data-positions="before,inside,after" selected', false);
        $response->assertSee('<option value="inside" selected', false);
    }
}
