<?php

namespace App\Enums;

use App\Models\Enquiry;
use App\Models\EnquiryType;
use App\Models\InvestmentOpportunity;
use App\Models\Property;

/**
 * What a client's enquiry is about, used to group their enquiries in the client dashboard.
 */
enum InterestArea: string
{
    case LandBanking = 'land_banking';
    case Investments = 'investments';
    case Property = 'property';
    case Advice = 'advice';

    public function label(): string
    {
        return match ($this) {
            self::LandBanking => 'Land banking',
            self::Investments => 'Investments',
            self::Property => 'Properties',
            self::Advice => 'Advice & consultations',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::LandBanking => 'leaf',
            self::Investments => 'briefcase',
            self::Property => 'building',
            self::Advice => 'message',
        };
    }

    /**
     * Tailwind classes for the area's icon tile.
     */
    public function tileClasses(): string
    {
        return match ($this) {
            self::LandBanking => 'bg-emerald-50 text-emerald-600 ring-emerald-100',
            self::Investments => 'bg-brass-50 text-brass-600 ring-brass-100',
            self::Property => 'bg-sky-50 text-sky-600 ring-sky-100',
            self::Advice => 'bg-violet-50 text-violet-600 ring-violet-100',
        };
    }

    /**
     * Where to find more of this kind of opportunity.
     */
    public function browseUrl(): string
    {
        return match ($this) {
            self::LandBanking => route('land-banking.index'),
            self::Investments => route('investments.index'),
            self::Property => route('properties.index'),
            self::Advice => route('services'),
        };
    }

    public function browseLabel(): string
    {
        return match ($this) {
            self::LandBanking => 'Browse land',
            self::Investments => 'Browse opportunities',
            self::Property => 'Browse properties',
            self::Advice => 'Our services',
        };
    }

    /**
     * The Speak to an Adviser form, pre-set for a new enquiry in this area.
     */
    public function newEnquiryUrl(): string
    {
        return route('contact', ['type' => match ($this) {
            self::LandBanking => EnquiryType::LandBanking,
            self::Investments => EnquiryType::Investment,
            self::Property => EnquiryType::Property,
            self::Advice => EnquiryType::Consultation,
        }]);
    }

    /**
     * Work out the area from what the enquiry is attached to, then from its type.
     */
    public static function forEnquiry(Enquiry $enquiry): self
    {
        $subject = $enquiry->enquirable;

        if ($subject instanceof InvestmentOpportunity) {
            return $subject->isLand() ? self::LandBanking : self::Investments;
        }

        if ($subject instanceof Property) {
            return self::Property;
        }

        return match ($enquiry->enquiryType?->slug) {
            EnquiryType::LandBanking => self::LandBanking,
            EnquiryType::Investment => self::Investments,
            EnquiryType::Property, EnquiryType::ListProperty, EnquiryType::ShortletBooking => self::Property,
            default => self::Advice,
        };
    }
}
