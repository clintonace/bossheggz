<?php

namespace App\Http\Controllers;

use App\Enums\LandingPage;
use App\Enums\ListingType;
use App\Enums\PropertyStatus;
use App\Models\EnquiryType;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PageController extends Controller
{
    public function services(): View
    {
        $shortlet = Property::visible()
            ->where('listing_type', ListingType::Shortlet)
            ->with('coverImage')
            ->orderByDesc('is_featured')
            ->latest('published_at')
            ->first();

        return view('pages.services', ['shortlet' => $shortlet, 'content' => LandingPage::Services->content()]);
    }

    public function about(): View
    {
        return view('pages.about', ['content' => LandingPage::About->content()]);
    }

    public function contact(Request $request): View
    {
        $types = EnquiryType::active()->ordered()->with('whatsappNumber')->get();

        return view('pages.contact', [
            'content' => LandingPage::Contact->content(),
            'types' => $types,
            'selectedType' => $types->firstWhere('slug', $request->string('type')->toString())?->slug ?? EnquiryType::General,
            'interest' => $request->string('interest')->limit(200, '')->toString(),
            'properties' => Property::where('status', PropertyStatus::Published)->orderBy('title')->get(['id', 'title', 'slug', 'city', 'state', 'price', 'listing_type', 'rent_period']),
            'selectedProperty' => $request->string('property')->toString() ?: null,
        ]);
    }
}
