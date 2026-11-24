<?php

namespace App\Enums;

enum ArticleCategory: string
{
    case RealEstateInvestment = 'real_estate_investment';
    case LandBanking = 'land_banking';
    case MarketUpdates = 'market_updates';
    case DueDiligence = 'due_diligence';
    case FirstTimeBuyers = 'first_time_buyers';
    case WealthCreation = 'wealth_creation';
    case PropertyManagement = 'property_management';
    case NigerianRealEstate = 'nigerian_real_estate';

    public function label(): string
    {
        return match ($this) {
            self::RealEstateInvestment => 'Real Estate Investment',
            self::LandBanking => 'Land Banking',
            self::MarketUpdates => 'Property Market Updates',
            self::DueDiligence => 'Property Due Diligence',
            self::FirstTimeBuyers => 'First-Time Property Buyers',
            self::WealthCreation => 'Wealth Creation',
            self::PropertyManagement => 'Property Management',
            self::NigerianRealEstate => 'Nigerian Real Estate',
        };
    }
}
