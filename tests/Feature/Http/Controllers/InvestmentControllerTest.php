<?php

namespace Tests\Feature\Http\Controllers;

use App\Enums\InvestmentCategory;
use App\Models\InvestmentOpportunity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvestmentControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_lists_published_opportunities_but_not_drafts(): void
    {
        InvestmentOpportunity::factory()->create(['title' => 'Rental Block Open']);
        InvestmentOpportunity::factory()->closed()->create(['title' => 'Joint Venture Closed']);
        InvestmentOpportunity::factory()->draft()->create(['title' => 'Secret Draft Deal']);

        $this->get(route('investments.index'))
            ->assertOk()
            ->assertSeeText('Rental Block Open')
            ->assertSeeText('Joint Venture Closed')
            ->assertDontSeeText('Secret Draft Deal')
            ->assertSeeText('projections, not guaranteed returns');
    }

    public function test_index_filters_by_category(): void
    {
        $jointVenture = InvestmentOpportunity::factory()->create(['category' => InvestmentCategory::JointVenture]);
        $rentalIncome = InvestmentOpportunity::factory()->create(['category' => InvestmentCategory::RentalIncome]);

        $this->get(route('investments.index', ['category' => 'joint_venture']))
            ->assertOk()
            ->assertSee(route('investments.show', $jointVenture), false)
            ->assertDontSee(route('investments.show', $rentalIncome), false);
    }

    public function test_show_renders_structure_risks_and_interest_form(): void
    {
        $opportunity = InvestmentOpportunity::factory()->create([
            'title' => 'Harbour Apartments',
            'structure' => 'Quarterly distributions after costs.',
            'risks' => 'Values can fall as well as rise.',
            'minimum_participation' => 5_000_000,
        ]);

        $this->get(route('investments.show', $opportunity))
            ->assertOk()
            ->assertSeeText('Harbour Apartments')
            ->assertSeeText('Quarterly distributions after costs.')
            ->assertSeeText('Values can fall as well as rise.')
            ->assertSeeText('₦5,000,000')
            ->assertSee('action="'.route('enquiries.store').'"', false);
    }

    public function test_show_closed_opportunity_does_not_offer_the_enquiry_form(): void
    {
        $opportunity = InvestmentOpportunity::factory()->closed()->create();

        $this->get(route('investments.show', $opportunity))
            ->assertOk()
            ->assertSeeText('This opportunity is closed')
            ->assertDontSee('action="'.route('enquiries.store').'"', false);
    }

    public function test_show_returns_404_for_drafts(): void
    {
        $opportunity = InvestmentOpportunity::factory()->draft()->create();

        $this->get(route('investments.show', $opportunity))->assertNotFound();
    }

    public function test_show_redirects_land_opportunities_to_the_land_banking_page(): void
    {
        $opportunity = InvestmentOpportunity::factory()->land()->create();

        $this->get(route('investments.show', $opportunity))->assertRedirect(route('land-banking.show', $opportunity));
    }
}
