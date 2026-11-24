<?php

namespace Database\Seeders;

use App\Enums\InvestmentCategory;
use App\Enums\LandUse;
use App\Enums\OpportunityStatus;
use App\Enums\VerificationStatus;
use App\Models\InvestmentOpportunity;
use Illuminate\Database\Seeder;

class InvestmentOpportunitySeeder extends Seeder
{
    /**
     * Seed demo investment and land banking opportunities (idempotent).
     *
     * @param  bool|null  $land  true = land banking only, false = other investments only, null = both
     */
    public function run(?bool $land = null): void
    {
        $photo = fn (string $id): string => "https://images.unsplash.com/photo-{$id}?auto=format&fit=crop&w=1400&q=75";

        $risks = "Property values can fall as well as rise, and projected figures are not guaranteed.\n\nTimelines may change because of approvals, construction or market conditions. Review all documentation and speak to an adviser before committing.";

        $opportunities = [
            [
                'title' => 'Asaba Gardens Estate — Phase 2 Plots',
                'category' => InvestmentCategory::LandBanking, 'land_use' => LandUse::Residential,
                'location' => 'Ibusa Road, Asaba', 'state' => 'Delta State',
                'summary' => 'Dry, gated residential plots in a fast-developing corridor of Asaba, close to the new airport road.',
                'land_sizes' => '300 m², 500 m², 600 m²', 'land_size_sqm' => 300, 'acquisition_price' => 4_500_000,
                'access' => '10 minutes from Asaba airport', 'estate_status' => 'Perimeter fencing and estate gate complete',
                'documentation' => 'Certificate of Occupancy', 'verification_status' => VerificationStatus::Verified,
                'rationale' => 'Early-phase pricing in an area seeing new roads and residential demand.', 'is_featured' => true,
                'image_path' => $photo('1500382017468-9049fed747ef'),
            ],
            [
                'title' => 'Lekki Coastal Commercial Land',
                'category' => InvestmentCategory::LandBanking, 'land_use' => LandUse::Commercial,
                'location' => 'Lekki–Epe Expressway, Ibeju-Lekki', 'state' => 'Lagos',
                'summary' => 'Road-facing commercial land close to the Lekki Free Trade Zone and Dangote Refinery corridor.',
                'land_sizes' => '1,000 m², 2,000 m²', 'land_size_sqm' => 1000, 'acquisition_price' => 38_000_000,
                'access' => 'Directly off the Lekki–Epe Expressway', 'estate_status' => 'Sand-filled and surveyed',
                'documentation' => 'Registered Survey & Deed of Assignment', 'verification_status' => VerificationStatus::DocumentsAvailable,
                'rationale' => 'Commercial frontage in a corridor driven by industrial investment.',
                'image_path' => $photo('1628624747186-a941c476b7ef'),
            ],
            [
                'title' => 'Enugu Agro Land Holding',
                'category' => InvestmentCategory::LandBanking, 'land_use' => LandUse::Agricultural,
                'location' => 'Nkanu East', 'state' => 'Enugu State',
                'summary' => 'Fertile agricultural land suitable for long-term holding or managed farming partnerships.',
                'land_sizes' => '1 hectare, 2 hectares', 'land_size_sqm' => 10000, 'acquisition_price' => 6_000_000,
                'access' => '25 minutes from Enugu city centre', 'estate_status' => 'Community consent obtained',
                'documentation' => 'Survey plan', 'verification_status' => VerificationStatus::Pending,
                'rationale' => 'Low entry price per hectare with optional managed farming income.',
                'image_path' => $photo('1464226184884-fa280b87c399'),
            ],
            [
                'title' => 'Wuse II Serviced Apartments — Rental Income',
                'category' => InvestmentCategory::RentalIncome,
                'location' => 'Wuse II', 'state' => 'Abuja',
                'summary' => 'Fractional participation in a block of serviced apartments let to corporate tenants.',
                'required_investment' => 420_000_000, 'minimum_participation' => 5_000_000, 'timeline' => '5-year hold',
                'structure' => 'Participants hold units in a special-purpose vehicle that owns the building. Net rental income is distributed quarterly after management and maintenance costs.',
                'documentation' => 'Governor’s Consent', 'verification_status' => VerificationStatus::Verified, 'is_featured' => true,
                'image_path' => $photo('1545324418-cc1a3fa10c00'),
            ],
            [
                'title' => 'Port Harcourt Townhouse Joint Venture',
                'category' => InvestmentCategory::JointVenture,
                'location' => 'GRA Phase 2, Port Harcourt', 'state' => 'Rivers State',
                'summary' => 'A joint venture with a landowner to develop eight terraced townhouses for sale.',
                'required_investment' => 650_000_000, 'minimum_participation' => 25_000_000, 'timeline' => '24 – 30 months',
                'structure' => 'The landowner contributes the site; investors fund construction. Sale proceeds are shared under the joint venture agreement once units are sold.',
                'documentation' => 'Certificate of Occupancy & JV agreement', 'verification_status' => VerificationStatus::DocumentsAvailable,
                'image_path' => $photo('1600585154340-be6161a56a0c'),
            ],
            [
                'title' => 'Ikoyi Off-plan Apartment Acquisition',
                'category' => InvestmentCategory::PropertyInvestment,
                'location' => 'Old Ikoyi', 'state' => 'Lagos',
                'summary' => 'Pre-launch pricing on two- and three-bedroom apartments in a boutique Ikoyi development.',
                'required_investment' => 180_000_000, 'minimum_participation' => 36_000_000, 'timeline' => 'Completion in 30 months',
                'structure' => 'Staged payments: 20% deposit, then instalments tied to construction milestones.',
                'documentation' => 'Building approval available', 'verification_status' => VerificationStatus::DocumentsAvailable,
                'image_path' => $photo('1600607687939-ce8a6c25118c'),
            ],
            [
                'title' => 'Epe Strategic Land Acquisition',
                'category' => InvestmentCategory::StrategicLandAcquisition,
                'location' => 'Epe', 'state' => 'Lagos',
                'summary' => 'A large parcel for subdivision, held ahead of planned infrastructure in the Epe axis.',
                'required_investment' => 250_000_000, 'minimum_participation' => 10_000_000, 'timeline' => '3 – 5 years',
                'structure' => 'Investors co-own the parcel. Plots are released for resale in phases.',
                'documentation' => 'Gazette & survey', 'verification_status' => VerificationStatus::Pending,
                'status' => OpportunityStatus::Closed,
                'image_path' => $photo('1506905925346-21bda4d32df4'),
            ],
        ];

        foreach ($opportunities as $attributes) {
            if ($land !== null && ($attributes['category'] === InvestmentCategory::LandBanking) !== $land) {
                continue;
            }

            InvestmentOpportunity::updateOrCreate(
                ['title' => $attributes['title']],
                [
                    'status' => OpportunityStatus::Open,
                    'description' => "{$attributes['summary']}\n\nOur team has inspected the site and reviewed the available documents. Full documentation is shared with interested investors on request, and an adviser will walk you through the structure, timeline and exit options before you commit.",
                    'risks' => $risks,
                    ...$attributes,
                ],
            );
        }
    }
}
