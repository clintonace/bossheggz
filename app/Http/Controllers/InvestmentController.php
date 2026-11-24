<?php

namespace App\Http\Controllers;

use App\Enums\InvestmentCategory;
use App\Enums\LandingPage;
use App\Enums\OpportunityStatus;
use App\Models\InvestmentOpportunity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvestmentController extends Controller
{
    public function index(Request $request): View
    {
        $category = InvestmentCategory::tryFrom($request->string('category')->toString());

        $opportunities = InvestmentOpportunity::visible()
            ->when($category, fn ($query, InvestmentCategory $category) => $query->where('category', $category))
            ->orderByRaw('status = ? desc', [OpportunityStatus::Open->value])
            ->orderByDesc('is_featured')
            ->latest('published_at')
            ->paginate(9)
            ->withQueryString();

        return view('investments.index', [
            'content' => LandingPage::Investments->content(),
            'opportunities' => $opportunities,
            'category' => $category,
            'openOpportunities' => InvestmentOpportunity::where('status', OpportunityStatus::Open)->orderBy('title')->get(['id', 'title', 'slug']),
        ]);
    }

    public function show(InvestmentOpportunity $opportunity): View|RedirectResponse
    {
        abort_unless($opportunity->status->isPublic(), 404);

        if ($opportunity->isLand()) {
            return redirect()->route('land-banking.show', $opportunity, 301);
        }

        return $this->detail($opportunity);
    }

    /**
     * The shared opportunity detail page (also used for land banking).
     */
    public static function detail(InvestmentOpportunity $opportunity): View
    {
        $related = InvestmentOpportunity::visible()
            ->whereKeyNot($opportunity->id)
            ->where('category', $opportunity->category)
            ->latest('published_at')
            ->take(3)
            ->get();

        return view('investments.show', [
            'opportunity' => $opportunity,
            'related' => $related,
        ]);
    }
}
