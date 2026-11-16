<?php

namespace App\Http\Controllers;

use App\Models\Advert;
use Illuminate\Http\RedirectResponse;

class AdvertClickController extends Controller
{
    /**
     * Count the click and send the visitor to the advertiser.
     */
    public function __invoke(Advert $advert): RedirectResponse
    {
        abort_unless($advert->state() === 'live', 404);

        $advert->increment('clicks');

        return redirect()->away($advert->link_url);
    }
}
