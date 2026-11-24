<?php

namespace App\Http\Controllers\Admin;

use App\Enums\InvestmentCategory;
use App\Enums\OpportunityStatus;
use App\Enums\VerificationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\InvestmentOpportunityRequest;
use App\Models\InvestmentOpportunity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class InvestmentOpportunityController extends Controller
{
    public function index(Request $request): View
    {
        $category = InvestmentCategory::tryFrom($request->string('category')->toString());

        $opportunities = InvestmentOpportunity::query()
            ->withCount('enquiries')
            ->when($category, fn ($query, InvestmentCategory $category) => $query->where('category', $category))
            ->when(! $category && $request->string('category')->toString() === 'investments', fn ($query) => $query->where('category', '!=', InvestmentCategory::LandBanking))
            ->when(OpportunityStatus::tryFrom($request->string('status')->toString()), fn ($query, OpportunityStatus $status) => $query->where('status', $status))
            ->when($request->string('q')->trim()->toString(), fn ($query, string $term) => $query->where(
                fn ($query) => $query->where('title', 'like', "%{$term}%")->orWhere('location', 'like', "%{$term}%")->orWhere('state', 'like', "%{$term}%")
            ))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.opportunities.index', [
            'opportunities' => $opportunities,
            'category' => $category,
        ]);
    }

    public function create(Request $request): View
    {
        return view('admin.opportunities.create', ['opportunity' => new InvestmentOpportunity([
            'category' => InvestmentCategory::tryFrom($request->string('category')->toString()) ?? InvestmentCategory::PropertyInvestment,
            'status' => OpportunityStatus::Open,
            'verification_status' => VerificationStatus::Pending,
        ])]);
    }

    public function store(InvestmentOpportunityRequest $request): RedirectResponse
    {
        $opportunity = new InvestmentOpportunity($request->opportunityAttributes());

        if ($request->hasFile('image')) {
            $opportunity->image_path = $request->file('image')->store('opportunities', 'public');
        }

        $opportunity->save();

        return to_route('admin.opportunities.edit', $opportunity)->with('success', 'Opportunity created successfully.');
    }

    public function edit(InvestmentOpportunity $opportunity): View
    {
        $opportunity->loadCount('enquiries');

        return view('admin.opportunities.edit', ['opportunity' => $opportunity]);
    }

    public function update(InvestmentOpportunityRequest $request, InvestmentOpportunity $opportunity): RedirectResponse
    {
        $opportunity->fill($request->opportunityAttributes());

        if ($request->hasFile('image') || $request->validated('remove_image')) {
            $this->deleteStoredImage($opportunity);
            $opportunity->image_path = $request->hasFile('image') ? $request->file('image')->store('opportunities', 'public') : null;
        }

        $opportunity->save();

        return back()->with('success', 'Opportunity updated.');
    }

    public function destroy(InvestmentOpportunity $opportunity): RedirectResponse
    {
        $isLand = $opportunity->isLand();

        $opportunity->delete();

        return to_route('admin.opportunities.index', $isLand ? ['category' => InvestmentCategory::LandBanking->value] : [])
            ->with('success', 'Opportunity deleted.');
    }

    private function deleteStoredImage(InvestmentOpportunity $opportunity): void
    {
        if ($opportunity->image_path && ! $opportunity->hasRemoteImage()) {
            Storage::disk('public')->delete($opportunity->image_path);
        }
    }
}
