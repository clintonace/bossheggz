<?php

namespace App\Http\Controllers\Account;

use App\Enums\EngagementStatus;
use App\Http\Controllers\Controller;
use App\Models\Engagement;
use App\Models\EngagementUpdate;
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

        return view('account.dashboard', [
            'stats' => [
                'total' => $engagements->count(),
                'open' => $engagements->filter(fn (Engagement $engagement) => $engagement->status->isOpen())->count(),
                'viewings' => $engagements->where('status', EngagementStatus::ViewingScheduled)->count(),
                'closed' => $engagements->where('status', EngagementStatus::Closed)->count(),
            ],
            'activeEngagements' => $engagements->filter(fn (Engagement $engagement) => $engagement->status->isOpen())->take(3),
            'activity' => $this->recentActivity($engagements),
            'recommended' => $this->recommendedProperties($engagements),
        ]);
    }

    /**
     * Latest progress updates and engagements, newest first.
     *
     * @param  Collection<int, Engagement>  $engagements
     * @return Collection<int, array{date: Carbon, status: EngagementStatus|null, title: string, body: ?string, engagement: Engagement}>
     */
    private function recentActivity(Collection $engagements): Collection
    {
        $byId = $engagements->keyBy('id');

        $updates = EngagementUpdate::whereIn('engagement_id', $byId->keys())
            ->latest()
            ->take(8)
            ->get()
            ->map(fn (EngagementUpdate $update) => [
                'date' => $update->created_at,
                'status' => $update->status,
                'title' => $byId[$update->engagement_id]->property->title,
                'body' => $update->note ?: $update->status->description(),
                'engagement' => $byId[$update->engagement_id],
            ]);

        $engaged = $engagements->map(fn (Engagement $engagement) => [
            'date' => $engagement->created_at,
            'status' => null,
            'title' => $engagement->property->title,
            'body' => 'You engaged with this property.',
            'engagement' => $engagement,
        ]);

        return $updates->concat($engaged)->sortByDesc('date')->take(6)->values();
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
}
