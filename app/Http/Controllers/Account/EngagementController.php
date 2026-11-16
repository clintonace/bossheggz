<?php

namespace App\Http\Controllers\Account;

use App\Enums\EngagementStatus;
use App\Http\Controllers\Controller;
use App\Models\Engagement;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EngagementController extends Controller
{
    public function index(Request $request): View
    {
        $filter = in_array($request->query('filter'), ['open', 'completed'], true) ? $request->query('filter') : 'all';

        $engagements = $request->user()
            ->engagements()
            ->with(['property.coverImage', 'updates' => fn ($query) => $query->limit(1)])
            ->latest()
            ->get();

        $counts = [
            'all' => $engagements->count(),
            'open' => $engagements->filter(fn (Engagement $engagement) => $engagement->status->isOpen())->count(),
            'completed' => $engagements->reject(fn (Engagement $engagement) => $engagement->status->isOpen())->count(),
        ];

        $visible = match ($filter) {
            'open' => $engagements->filter(fn (Engagement $engagement) => $engagement->status->isOpen()),
            'completed' => $engagements->reject(fn (Engagement $engagement) => $engagement->status->isOpen()),
            default => $engagements,
        };

        return view('account.engagements', [
            'engagements' => $visible,
            'filter' => $filter,
            'counts' => $counts,
        ]);
    }

    public function show(Request $request, Engagement $engagement): View
    {
        abort_unless($engagement->user_id === $request->user()->id, 404);

        $engagement->load(['property.images', 'updates']);

        return view('account.engagement', [
            'engagement' => $engagement,
            'isComplete' => $engagement->status === EngagementStatus::Closed,
        ]);
    }
}
