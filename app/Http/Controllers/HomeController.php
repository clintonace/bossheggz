<?php

namespace App\Http\Controllers;

use App\Enums\AdvertPage;
use App\Enums\ListingType;
use App\Models\Advert;
use App\Models\Property;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $featured = Property::visible()->where('is_featured', true)->with('coverImage')->latest('published_at')->take(3)->get();

        $latest = Property::visible()->with('coverImage')->latest('published_at')->take(6)->get();

        return view('home', [
            'featured' => $featured,
            'latest' => $latest,
            'cities' => Property::visible()->distinct()->orderBy('city')->pluck('city'),
            'adverts' => Advert::slotsFor(AdvertPage::Home),
            'stats' => [
                'sale' => Property::visible()->where('listing_type', ListingType::Sale)->count(),
                'rent' => Property::visible()->where('listing_type', ListingType::Rent)->count(),
                'cities' => Property::visible()->distinct('city')->count('city'),
            ],
        ]);
    }
}
