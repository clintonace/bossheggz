<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\PropertyImage;
use Illuminate\Http\RedirectResponse;

class PropertyImageController extends Controller
{
    /**
     * Make the image the property's cover (first in the gallery).
     */
    public function cover(Property $property, PropertyImage $image): RedirectResponse
    {
        $image->update(['sort_order' => 0]);

        $property->images()->whereKeyNot($image->id)->orderBy('sort_order')->orderBy('id')->get()
            ->each(fn (PropertyImage $other, int $index) => $other->update(['sort_order' => $index + 1]));

        return back()->with('success', 'Cover image updated.');
    }

    public function destroy(Property $property, PropertyImage $image): RedirectResponse
    {
        $image->delete();

        return back()->with('success', 'Image removed.');
    }
}
