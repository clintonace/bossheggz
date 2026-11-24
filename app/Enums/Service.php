<?php

namespace App\Enums;

use App\Models\EnquiryType;

enum Service: string
{
    case Brokerage = 'brokerage';
    case SalesAndAcquisition = 'sales_and_acquisition';
    case LandBanking = 'land_banking';
    case LeasingAndRentals = 'leasing_and_rentals';
    case ShortletManagement = 'shortlet_management';
    case InvestmentAdvisory = 'investment_advisory';
    case PropertySourcing = 'property_sourcing';
    case JointVentures = 'joint_ventures';
    case PropertyManagement = 'property_management';

    public function label(): string
    {
        return match ($this) {
            self::Brokerage => 'Real Estate Brokerage',
            self::SalesAndAcquisition => 'Property Sales and Acquisition',
            self::LandBanking => 'Land Banking',
            self::LeasingAndRentals => 'Property Leasing and Rentals',
            self::ShortletManagement => 'Airbnb and Shortlet Management',
            self::InvestmentAdvisory => 'Real Estate Investment Advisory',
            self::PropertySourcing => 'Property Sourcing',
            self::JointVentures => 'Joint Venture Opportunities',
            self::PropertyManagement => 'Property Management',
        };
    }

    /**
     * One-line summary for the home page.
     */
    public function summary(): string
    {
        return match ($this) {
            self::Brokerage => 'Connecting buyers, sellers, landlords and tenants.',
            self::SalesAndAcquisition => 'Guided purchases and sales from enquiry to completion.',
            self::LandBanking => 'Strategically located land for long-term wealth.',
            self::LeasingAndRentals => 'Residential and commercial lettings.',
            self::ShortletManagement => 'Listing, guest coordination and occupancy.',
            self::InvestmentAdvisory => 'Informed guidance for individual and institutional investors.',
            self::PropertySourcing => 'Opportunities matched to your brief.',
            self::JointVentures => 'Selected partnerships and co-investments.',
            self::PropertyManagement => 'Tenant sourcing, inspections and maintenance.',
        };
    }

    /**
     * Fuller description for the services page.
     */
    public function description(): string
    {
        return match ($this) {
            self::Brokerage => 'Connecting buyers, sellers, landlords, tenants and investors to suitable opportunities.',
            self::SalesAndAcquisition => 'Guided buying and selling, from property search to documentation.',
            self::LandBanking => 'Strategically located land for long-term wealth creation.',
            self::LeasingAndRentals => 'Residential and commercial lettings for landlords and tenants.',
            self::ShortletManagement => 'Listing, guest enquiries and occupancy management.',
            self::InvestmentAdvisory => 'Informed guidance for individual and institutional investors.',
            self::PropertySourcing => 'Finding properties that match your brief.',
            self::JointVentures => 'Selected partnerships and investment opportunities.',
            self::PropertyManagement => 'Tenant sourcing, inspections and maintenance coordination.',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Brokerage => 'handshake',
            self::SalesAndAcquisition => 'tag',
            self::LandBanking => 'leaf',
            self::LeasingAndRentals => 'key',
            self::ShortletManagement => 'calendar',
            self::InvestmentAdvisory => 'trending',
            self::PropertySourcing => 'target',
            self::JointVentures => 'briefcase',
            self::PropertyManagement => 'building',
        };
    }

    /**
     * Where "Learn more" leads for this service.
     */
    public function url(): string
    {
        return match ($this) {
            self::Brokerage, self::SalesAndAcquisition => route('properties.index'),
            self::LandBanking => route('land-banking.index'),
            self::LeasingAndRentals => route('properties.index', ['listing' => ListingType::Rent->value]),
            self::ShortletManagement => route('properties.index', ['listing' => ListingType::Shortlet->value]),
            self::InvestmentAdvisory => route('contact', ['type' => EnquiryType::Consultation]),
            self::PropertySourcing => route('contact', ['type' => EnquiryType::Property, 'interest' => 'Property sourcing']),
            self::JointVentures => route('investments.index', ['category' => InvestmentCategory::JointVenture->value]),
            self::PropertyManagement => route('services').'#property-management',
        };
    }
}
