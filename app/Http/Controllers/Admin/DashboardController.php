<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EngagementStatus;
use App\Enums\ListingType;
use App\Enums\PropertyStatus;
use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Engagement;
use App\Models\Enquiry;
use App\Models\InvestmentOpportunity;
use App\Models\Property;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $chartDays = 14;
        $since = now()->subDays($chartDays - 1)->startOfDay();

        $dailyCounts = Engagement::where('created_at', '>=', $since)
            ->get(['created_at'])
            ->countBy(fn (Engagement $engagement) => $engagement->created_at->toDateString());

        $chart = collect(range(0, $chartDays - 1))->map(function (int $offset) use ($since, $dailyCounts): array {
            $day = $since->copy()->addDays($offset);

            return [
                'date' => $day,
                'label' => $day->format('M j'),
                'count' => $dailyCounts->get($day->toDateString(), 0),
            ];
        });

        $statusCounts = Engagement::toBase()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        return view('admin.dashboard', [
            'stats' => [
                'properties' => Property::count(),
                'live' => Property::where('status', PropertyStatus::Published)->count(),
                'sale' => Property::visible()->where('listing_type', ListingType::Sale)->count(),
                'rent' => Property::visible()->where('listing_type', ListingType::Rent)->count(),
                'engagements' => Engagement::count(),
                'new' => (int) $statusCounts->get(EngagementStatus::New->value, 0),
                'open' => Engagement::open()->count(),
                'closed_this_month' => Engagement::where('status', EngagementStatus::Closed)->where('updated_at', '>=', Carbon::now()->startOfMonth())->count(),
                'users' => User::where('is_admin', false)->count(),
            ],
            'chart' => $chart,
            'chartMax' => max(1, $chart->max('count')),
            'chartTotal' => $chart->sum('count'),
            'statusCounts' => $statusCounts,
            'recentEngagements' => Engagement::with(['property.coverImage', 'user'])->latest()->take(6)->get(),
            'overview' => [
                'listings' => Property::count(),
                'active' => Property::whereIn('status', [PropertyStatus::Published, PropertyStatus::UnderOffer, PropertyStatus::Reserved])->count(),
                'sold' => Property::where('status', PropertyStatus::Sold)->count(),
                'rented' => Property::where('status', PropertyStatus::Rented)->count(),
                'opportunities' => InvestmentOpportunity::visible()->count(),
                'enquiries' => Enquiry::count(),
            ],
            'recentEnquiries' => Enquiry::with('enquirable')->latest()->take(5)->get(),
            'activity' => $this->recentActivity(),
            'topProperties' => Property::has('engagements')->withCount('engagements')->with('coverImage')->orderByDesc('engagements_count')->take(5)->get(),
        ]);
    }

    /**
     * The latest things that happened on the website, newest first.
     *
     * @return Collection<int, array{icon: string, text: string, detail: string, at: Carbon, url: string}>
     */
    private function recentActivity(): Collection
    {
        $properties = Property::latest('updated_at')->take(5)->get()->map(fn (Property $property) => [
            'icon' => 'building',
            'text' => $property->created_at->eq($property->updated_at) ? 'Property listing added' : 'Property listing updated',
            'detail' => $property->title,
            'at' => $property->updated_at,
            'url' => route('admin.properties.edit', $property),
        ]);

        $enquiries = Enquiry::with('enquiryType')->latest()->take(5)->get()->map(fn (Enquiry $enquiry) => [
            'icon' => 'mail',
            'text' => 'New enquiry received',
            'detail' => "{$enquiry->name} · {$enquiry->typeLabel()}",
            'at' => $enquiry->created_at,
            'url' => route('admin.enquiries.show', $enquiry),
        ]);

        $engagements = Engagement::with('property')->latest()->take(5)->get()->map(fn (Engagement $engagement) => [
            'icon' => 'inbox',
            'text' => 'New engagement',
            'detail' => "{$engagement->name} · {$engagement->property?->title}",
            'at' => $engagement->created_at,
            'url' => route('admin.engagements.show', $engagement),
        ]);

        $articles = Article::whereNotNull('published_at')->latest('updated_at')->take(5)->get()->map(fn (Article $article) => [
            'icon' => 'book',
            'text' => $article->isScheduled() ? 'Article scheduled' : 'Article published',
            'detail' => $article->title,
            'at' => $article->updated_at,
            'url' => route('admin.articles.edit', $article),
        ]);

        $opportunities = InvestmentOpportunity::visible()->latest('updated_at')->take(5)->get()->map(fn (InvestmentOpportunity $opportunity) => [
            'icon' => $opportunity->isLand() ? 'leaf' : 'briefcase',
            'text' => $opportunity->isLand() ? 'Land opportunity published' : 'Investment opportunity published',
            'detail' => $opportunity->title,
            'at' => $opportunity->updated_at,
            'url' => route('admin.opportunities.edit', $opportunity),
        ]);

        return $properties->concat($enquiries)->concat($engagements)->concat($articles)->concat($opportunities)
            ->sortByDesc('at')
            ->take(8)
            ->values();
    }
}
