<?php

namespace App\Http\Controllers;

use App\Enums\AdvertPage;
use App\Enums\AdvertPosition;
use App\Enums\LandingPage;
use App\Models\Advert;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PropertyController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->only(['q', 'listing', 'type', 'state', 'city', 'min_price', 'max_price', 'beds', 'size', 'availability', 'featured', 'verified']);

        $sort = $request->string('sort')->toString();

        $adverts = Advert::slotsFor(AdvertPage::Listings);
        $gridAdverts = $adverts->get('grid:'.AdvertPosition::Inside->value, collect())->take(2);

        $properties = Property::visible()
            ->filter($filters)
            ->with('coverImage')
            ->when($sort === 'price_asc', fn ($query) => $query->orderBy('price'))
            ->when($sort === 'price_desc', fn ($query) => $query->orderByDesc('price'))
            ->latest('published_at')
            ->paginate(9 - $gridAdverts->count())
            ->withQueryString();

        return view('properties.index', [
            'content' => LandingPage::Properties->content(),
            'properties' => $properties,
            'filters' => $filters,
            'sort' => $sort,
            'cities' => Property::visible()->distinct()->orderBy('city')->pluck('city'),
            'states' => Property::visible()->whereNotNull('state')->distinct()->orderBy('state')->pluck('state'),
            'adverts' => $adverts,
            'gridAdverts' => $gridAdverts,
        ]);
    }

    public function show(Request $request, Property $property): View
    {
        abort_unless($property->status->isPublic(), 404);

        $property->load('images');

        $similar = Property::visible()
            ->whereKeyNot($property->id)
            ->where(fn ($query) => $query->where('city', $property->city)->orWhere('property_type', $property->property_type))
            ->with('coverImage')
            ->latest('published_at')
            ->take(3)
            ->get();

        $existingEngagement = $request->user()?->engagements()->open()->whereBelongsTo($property)->first();

        return view('properties.show', [
            'property' => $property,
            'similar' => $similar,
            'existingEngagement' => $existingEngagement,
            'adverts' => Advert::slotsFor(AdvertPage::Property),
        ]);
    }
}
