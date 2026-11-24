<?php

namespace App\Enums;

enum InvestmentCategory: string
{
    case LandBanking = 'land_banking';
    case StrategicLandAcquisition = 'strategic_land_acquisition';
    case JointVenture = 'joint_venture';
    case PropertyInvestment = 'property_investment';
    case RentalIncome = 'rental_income';
    case PropertyAcquisition = 'property_acquisition';

    public function label(): string
    {
        return match ($this) {
            self::LandBanking => 'Land Banking',
            self::StrategicLandAcquisition => 'Strategic Land Acquisition',
            self::JointVenture => 'Joint Venture',
            self::PropertyInvestment => 'Property Investment',
            self::RentalIncome => 'Rental Income',
            self::PropertyAcquisition => 'Property Acquisition',
        };
    }

    public function isLand(): bool
    {
        return in_array($this, [self::LandBanking, self::StrategicLandAcquisition], true);
    }
}
