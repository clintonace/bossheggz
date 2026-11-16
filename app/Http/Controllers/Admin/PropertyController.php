<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PropertyStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PropertyRequest;
use App\Models\Property;
use App\Models\User;
use App\Notifications\PropertyUpdated;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\View\View;

class PropertyController extends Controller
{
    public function index(Request $request): View
    {
        $properties = Property::query()
            ->with('coverImage')
            ->withCount('engagements')
            ->when($request->string('q')->trim()->toString(), fn ($query, string $term) => $query->where(
                fn ($query) => $query->where('title', 'like', "%{$term}%")->orWhere('city', 'like', "%{$term}%")->orWhere('address', 'like', "%{$term}%")
            ))
            ->when(PropertyStatus::tryFrom($request->string('status')->toString()), fn ($query, PropertyStatus $status) => $query->where('status', $status))
            ->when($request->string('listing')->toString(), fn ($query, string $listing) => $query->where('listing_type', $listing))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.properties.index', ['properties' => $properties]);
    }

    public function create(): View
    {
        return view('admin.properties.create', ['property' => new Property([
            'status' => PropertyStatus::Published,
        ])]);
    }

    public function store(PropertyRequest $request): RedirectResponse
    {
        $property = Property::create($request->propertyAttributes());

        $this->storeImages($property, $request->file('images', []));

        return to_route('admin.properties.edit', $property)->with('success', 'Property created successfully.');
    }

    public function edit(Property $property): View
    {
        $property->load('images')->loadCount('engagements');

        return view('admin.properties.edit', ['property' => $property]);
    }

    public function update(PropertyRequest $request, Property $property): RedirectResponse
    {
        $originalPrice = $property->formatted_price;
        $originalStatus = $property->status;

        $property->update($request->propertyAttributes());

        $this->storeImages($property, $request->file('images', []));

        $changes = [];

        if ($property->wasChanged(['price', 'rent_period', 'listing_type']) && $property->formatted_price !== $originalPrice) {
            $changes[] = "Price changed from {$originalPrice} to {$property->formatted_price}";
        }

        if ($property->wasChanged('status') && $property->status->isPublic()) {
            $changes[] = "Status changed from {$originalStatus->label()} to {$property->status->label()}";
        }

        $notified = $changes ? $this->notifyEngagers($property, $changes) : 0;

        return back()->with('success', $notified
            ? "Property updated. {$notified} engaged ".str('user')->plural($notified).' notified.'
            : 'Property updated.');
    }

    public function destroy(Property $property): RedirectResponse
    {
        $property->images->each->delete();
        $property->delete();

        return to_route('admin.properties.index')->with('success', 'Property deleted.');
    }

    /**
     * @param  array<int, UploadedFile>  $files
     */
    private function storeImages(Property $property, array $files): void
    {
        $nextOrder = (int) $property->images()->max('sort_order') + ($property->images()->exists() ? 1 : 0);

        foreach ($files as $file) {
            $property->images()->create([
                'path' => $file->store('properties', 'public'),
                'sort_order' => $nextOrder++,
            ]);
        }
    }

    /**
     * Tell every registered engager (one message per user) that the property changed.
     *
     * @param  array<int, string>  $changes
     */
    private function notifyEngagers(Property $property, array $changes): int
    {
        $engagements = $property->engagements()
            ->whereNotNull('user_id')
            ->with('user')
            ->latest()
            ->get()
            ->unique('user_id')
            ->filter(fn ($engagement) => $engagement->user instanceof User);

        $engagements->each(fn ($engagement) => $engagement->user->notify(new PropertyUpdated($engagement->setRelation('property', $property), $changes)));

        return $engagements->count();
    }
}
