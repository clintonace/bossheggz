<?php

namespace App\Http\Controllers;

use App\Enums\AdvertPage;
use App\Enums\LandingPage;
use App\Enums\OpportunityStatus;
use App\Models\Advert;
use App\Models\InvestmentOpportunity;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LandBankingController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->only(['state', 'location', 'size', 'price']);

        $opportunities = InvestmentOpportunity::visible()
            ->land()
            ->filterLand($filters)
            ->orderByRaw('status = ? desc', [OpportunityStatus::Open->value])
            ->orderByDesc('is_featured')
            ->latest('published_at')
            ->paginate(9)
            ->withQueryString();

        return view('land-banking.index', [
            'content' => LandingPage::LandBanking->content(),
            'adverts' => Advert::slotsFor(AdvertPage::LandBanking),
            'opportunities' => $opportunities,
            'filters' => $filters,
            'states' => InvestmentOpportunity::visible()->land()->whereNotNull('state')->distinct()->orderBy('state')->pluck('state'),
            'openOpportunities' => InvestmentOpportunity::land()->where('status', OpportunityStatus::Open)->orderBy('title')->get(['id', 'title', 'slug']),
        ]);
    }

    public function show(InvestmentOpportunity $opportunity): View
    {
        abort_unless($opportunity->status->isPublic() && $opportunity->isLand(), 404);

        return InvestmentController::detail($opportunity);
    }
}
