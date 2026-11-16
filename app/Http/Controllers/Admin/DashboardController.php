<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EngagementStatus;
use App\Enums\ListingType;
use App\Enums\PropertyStatus;
use App\Http\Controllers\Controller;
use App\Models\Engagement;
use App\Models\Property;
use App\Models\User;
use Illuminate\Support\Carbon;
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
            'topProperties' => Property::has('engagements')->withCount('engagements')->with('coverImage')->orderByDesc('engagements_count')->take(5)->get(),
        ]);
    }
}
