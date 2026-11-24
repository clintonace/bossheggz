<?php

namespace App\Http\Controllers\Account;

use App\Enums\EngagementStatus;
use App\Enums\InterestArea;
use App\Enums\OpportunityStatus;
use App\Http\Controllers\Controller;
use App\Models\Engagement;
use App\Models\EngagementUpdate;
use App\Models\Enquiry;
use App\Models\EnquiryUpdate;
use App\Models\InvestmentOpportunity;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        $engagements = $user->engagements()->with('property.coverImage')->latest()->get();
        $enquiries = $user->enquiries()->with(['enquirable', 'enquiryType', 'latestUpdate'])->latest()->get();
        $openEnquiries = $enquiries->filter(fn (Enquiry $enquiry) => $enquiry->status->isOpen());

        return view('account.dashboard', [
            'stats' => [
                'total' => $engagements->count(),
                'open' => $engagements->filter(fn (Engagement $engagement) => $engagement->status->isOpen())->count(),
                'viewings' => $engagements->where('status', EngagementStatus::ViewingScheduled)->count(),
                'closed' => $engagements->where('status', EngagementStatus::Closed)->count(),
                'enquiries' => $enquiries->count(),
                'openEnquiries' => $openEnquiries->count(),
            ],
            'activeEngagements' => $engagements->filter(fn (Engagement $engagement) => $engagement->status->isOpen())->take(3),
            'activeEnquiries' => $openEnquiries->take(3),
            'interests' => collect(InterestArea::cases())->map(fn (InterestArea $area) => [
                'area' => $area,
                'total' => $enquiries->filter(fn (Enquiry $enquiry) => $enquiry->area() === $area)->count(),
                'open' => $openEnquiries->filter(fn (Enquiry $enquiry) => $enquiry->area() === $area)->count(),
            ]),
            'activity' => $this->recentActivity($engagements, $enquiries),
            'recommended' => $this->recommendedProperties($engagements),
            'opportunities' => $this->recommendedOpportunities($enquiries),
        ]);
    }

    /**
     * Latest updates and actions on engagements and enquiries, newest first.
     *
     * @param  Collection<int, Engagement>  $engagements
     * @param  Collection<int, Enquiry>  $enquiries
     * @return Collection<int, array{date: Carbon, label: string, badge: string, highlight: bool, title: string, body: ?string, url: string}>
     */
    private function recentActivity(Collection $engagements, Collection $enquiries): Collection
    {
        $engagementsById = $engagements->keyBy('id');
        $enquiriesById = $enquiries->keyBy('id');

        $engagementUpdates = EngagementUpdate::whereIn('engagement_id', $engagementsById->keys())->latest()->take(8)->get()
            ->map(fn (EngagementUpdate $update) => [
                'date' => $update->created_at,
                'label' => $update->status->label(),
                'badge' => $update->status->badgeClasses(),
                'highlight' => true,
                'title' => $engagementsById[$update->engagement_id]->property->title,
                'body' => $update->note ?: $update->status->description(),
                'url' => route('account.engagements.show', $update->engagement_id),
            ]);

        $engaged = $engagements->map(fn (Engagement $engagement) => [
            'date' => $engagement->created_at,
            'label' => 'Engaged',
            'badge' => 'bg-ink-50 text-ink-500 ring-ink-100',
            'highlight' => false,
            'title' => $engagement->property->title,
            'body' => 'You engaged with this property.',
            'url' => route('account.engagements.show', $engagement),
        ]);

        $enquiryUpdates = EnquiryUpdate::whereIn('enquiry_id', $enquiriesById->keys())->latest()->take(8)->get()
            ->map(fn (EnquiryUpdate $update) => [
                'date' => $update->created_at,
                'label' => $update->status->clientLabel(),
                'badge' => $update->status->badgeClasses(),
                'highlight' => true,
                'title' => $enquiriesById[$update->enquiry_id]->subject(),
                'body' => $update->message ?: $update->status->description(),
                'url' => route('account.enquiries.show', $update->enquiry_id),
            ]);

        $enquired = $enquiries->map(fn (Enquiry $enquiry) => [
            'date' => $enquiry->created_at,
            'label' => $enquiry->area()->label(),
            'badge' => 'bg-ink-50 text-ink-500 ring-ink-100',
            'highlight' => false,
            'title' => $enquiry->subject(),
            'body' => 'You sent an enquiry.',
            'url' => route('account.enquiries.show', $enquiry),
        ]);

        return $engagementUpdates->concat($engaged)->concat($enquiryUpdates)->concat($enquired)
            ->sortByDesc('date')
            ->take(7)
            ->values();
    }

    /**
     * Available properties similar to the ones already engaged (same city or type), else the newest listings.
     *
     * @param  Collection<int, Engagement>  $engagements
     * @return Collection<int, Property>
     */
    private function recommendedProperties(Collection $engagements): Collection
    {
        $engagedProperties = $engagements->pluck('property');

        $available = fn () => Property::visible()
            ->whereIn('status', ['published', 'under_offer'])
            ->whereNotIn('id', $engagedProperties->pluck('id'))
            ->with('coverImage')
            ->latest('published_at');

        $similar = $engagedProperties->isEmpty() ? collect() : $available()
            ->where(fn ($query) => $query
                ->whereIn('city', $engagedProperties->pluck('city')->unique())
                ->orWhereIn('property_type', $engagedProperties->pluck('property_type')->map->value->unique()))
            ->take(3)
            ->get();

        return $similar->isNotEmpty() ? $similar : $available()->take(3)->get();
    }

    /**
     * Open land banking / investment opportunities matching the areas the client has asked about
     * (land first when they've enquired about land), excluding ones they already enquired about.
     *
     * @param  Collection<int, Enquiry>  $enquiries
     * @return Collection<int, InvestmentOpportunity>
     */
    private function recommendedOpportunities(Collection $enquiries): Collection
    {
        $areas = $enquiries->map(fn (Enquiry $enquiry) => $enquiry->area());
        $alreadyAsked = $enquiries
            ->filter(fn (Enquiry $enquiry) => $enquiry->enquirable instanceof InvestmentOpportunity)
            ->map(fn (Enquiry $enquiry) => $enquiry->enquirable_id);

        $query = InvestmentOpportunity::visible()
            ->where('status', OpportunityStatus::Open)
            ->whereNotIn('id', $alreadyAsked)
            ->orderByDesc('is_featured')
            ->latest('published_at');

        $wantsLand = $areas->contains(InterestArea::LandBanking);
        $wantsInvestments = $areas->contains(InterestArea::Investments);

        if ($wantsLand xor $wantsInvestments) {
            $query->when($wantsLand, fn ($query) => $query->land(), fn ($query) => $query->whereNot(fn ($query) => $query->land()));
        }

        return $query->take(3)->get();
    }
}
