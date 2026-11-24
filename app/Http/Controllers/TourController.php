<?php

namespace App\Http\Controllers;

use App\Enums\SiteArea;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TourController extends Controller
{
    /**
     * Remember that the signed-in user has finished or skipped an area's guided tour.
     * Guests are remembered in their browser instead.
     */
    public function __invoke(Request $request, SiteArea $area): Response
    {
        abort_if($area === SiteArea::Admin && ! $request->user()->isAdmin(), 403);

        $request->user()->markTourSeen($area);

        return response()->noContent();
    }
}
