<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\InvestmentOpportunity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandBankingControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_lists_only_land_banking_opportunities(): void
    {
        $land = InvestmentOpportunity::factory()->land()->create(['title' => 'Asaba Gardens Plots']);
        InvestmentOpportunity::factory()->create(['title' => 'Office Rental Income']);

        $this->get(route('land-banking.index'))
            ->assertOk()
            ->assertSee(route('land-banking.show', $land), false)
            ->assertDontSeeText('Office Rental Income')
            ->assertSeeText('Land Banking Enquiry');
    }

    public function test_index_filters_by_state_price_and_size(): void
    {
        $match = InvestmentOpportunity::factory()->land()->create(['state' => 'Delta State', 'acquisition_price' => 3_000_000, 'land_size_sqm' => 300]);
        $tooExpensive = InvestmentOpportunity::factory()->land()->create(['state' => 'Delta State', 'acquisition_price' => 80_000_000, 'land_size_sqm' => 10_000]);
        $wrongState = InvestmentOpportunity::factory()->land()->create(['state' => 'Lagos', 'acquisition_price' => 3_000_000, 'land_size_sqm' => 300]);

        $this->get(route('land-banking.index', ['state' => 'Delta State', 'price' => 'under-5m', 'size' => 'up-to-500']))
            ->assertOk()
            ->assertSee(route('land-banking.show', $match), false)
            ->assertDontSee(route('land-banking.show', $tooExpensive), false)
            ->assertDontSee(route('land-banking.show', $wrongState), false);
    }

    public function test_show_renders_land_details_and_map(): void
    {
        $opportunity = InvestmentOpportunity::factory()->land()->create([
            'land_sizes' => '300 m², 600 m²',
            'estate_status' => 'Gate and fencing complete',
            'rationale' => 'Early-phase pricing near the new airport road.',
        ]);

        $this->get(route('land-banking.show', $opportunity))
            ->assertOk()
            ->assertSeeText('300 m², 600 m²')
            ->assertSeeText('Gate and fencing complete')
            ->assertSeeText('Early-phase pricing near the new airport road.')
            ->assertSee('maps?q=', false);
    }

    public function test_show_returns_404_for_non_land_opportunities(): void
    {
        $opportunity = InvestmentOpportunity::factory()->create();

        $this->get(route('land-banking.show', $opportunity))->assertNotFound();
    }
}
