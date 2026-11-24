<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Enums\InvestmentCategory;
use App\Enums\LandUse;
use App\Enums\OpportunityStatus;
use App\Models\InvestmentOpportunity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class InvestmentOpportunityControllerTest extends TestCase
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
    private function validPayload(array $overrides = []): array
    {
        return [
            'title' => 'Asaba Gardens',
            'category' => 'land_banking',
            'status' => 'open',
            'location' => 'Ibusa Road, Asaba',
            'state' => 'Delta State',
            'summary' => 'Gated residential plots.',
            'description' => 'A longer description.',
            'verification_status' => 'verified',
            'documentation' => 'Certificate of Occupancy',
            'land_use' => 'residential',
            'land_sizes' => '300 m², 500 m²',
            'land_size_sqm' => '300',
            'acquisition_price' => '₦4,500,000',
            'required_investment' => '',
            'is_featured' => '1',
            ...$overrides,
        ];
    }

    public function test_non_admins_cannot_manage_opportunities(): void
    {
        $this->get(route('admin.opportunities.index'))->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create())->get(route('admin.opportunities.index'))->assertForbidden();
    }

    public function test_index_lists_drafts_and_filters_land_banking(): void
    {
        InvestmentOpportunity::factory()->land()->draft()->create(['title' => 'Draft Land']);
        InvestmentOpportunity::factory()->create(['title' => 'Rental Block']);

        $this->actingAs($this->admin)->get(route('admin.opportunities.index', ['category' => 'land_banking']))
            ->assertOk()
            ->assertSeeText('Draft Land')
            ->assertDontSeeText('Rental Block');

        $this->actingAs($this->admin)->get(route('admin.opportunities.index', ['category' => 'investments']))
            ->assertSeeText('Rental Block')
            ->assertDontSeeText('Draft Land');
    }

    public function test_create_form_preselects_land_banking(): void
    {
        $this->actingAs($this->admin)->get(route('admin.opportunities.create', ['category' => 'land_banking']))
            ->assertOk()
            ->assertSeeText('New land opportunity')
            ->assertSee('<option value="land_banking" selected>', false);
    }

    public function test_store_creates_land_opportunity_with_photo(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin)
            ->post(route('admin.opportunities.store'), $this->validPayload(['image' => UploadedFile::fake()->image('site.jpg')]))
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $opportunity = InvestmentOpportunity::sole();
        $this->assertSame(InvestmentCategory::LandBanking, $opportunity->category);
        $this->assertSame(LandUse::Residential, $opportunity->land_use);
        $this->assertSame(4_500_000, $opportunity->acquisition_price);
        $this->assertNull($opportunity->required_investment);
        $this->assertSame('asaba-gardens', $opportunity->slug);
        $this->assertTrue($opportunity->is_featured);
        $this->assertNotNull($opportunity->published_at);
        Storage::disk('public')->assertExists($opportunity->image_path);
    }

    public function test_store_validates_required_fields(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.opportunities.store'), $this->validPayload(['title' => '', 'category' => 'crypto']))
            ->assertSessionHasErrors(['title', 'category']);
    }

    public function test_update_can_close_an_opportunity_and_replace_its_photo(): void
    {
        Storage::fake('public');
        $opportunity = InvestmentOpportunity::factory()->land()->create(['image_path' => UploadedFile::fake()->image('old.jpg')->store('opportunities', 'public')]);
        $oldPath = $opportunity->image_path;

        $this->actingAs($this->admin)
            ->put(route('admin.opportunities.update', $opportunity), $this->validPayload(['status' => 'closed', 'image' => UploadedFile::fake()->image('new.jpg')]))
            ->assertSessionHasNoErrors();

        $opportunity->refresh();
        $this->assertSame(OpportunityStatus::Closed, $opportunity->status);
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($opportunity->image_path);
    }

    public function test_destroy_deletes_the_opportunity(): void
    {
        $opportunity = InvestmentOpportunity::factory()->land()->create();

        $this->actingAs($this->admin)
            ->delete(route('admin.opportunities.destroy', $opportunity))
            ->assertRedirect(route('admin.opportunities.index', ['category' => 'land_banking']));

        $this->assertModelMissing($opportunity);
    }
}
