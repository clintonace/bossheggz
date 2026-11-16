<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EngagementStatus;
use App\Http\Controllers\Controller;
use App\Models\Engagement;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EngagementController extends Controller
{
    public function index(Request $request): View
    {
        $status = EngagementStatus::tryFrom($request->string('status')->toString());
        $audience = $request->string('audience')->toString();

        $engagements = Engagement::query()
            ->with(['property.coverImage', 'user'])
            ->when($status, fn ($query, EngagementStatus $status) => $query->where('status', $status))
            ->when($audience === 'guest', fn ($query) => $query->whereNull('user_id'))
            ->when($audience === 'registered', fn ($query) => $query->whereNotNull('user_id'))
            ->when($request->string('q')->trim()->toString(), fn ($query, string $term) => $query->where(
                fn ($query) => $query->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhereHas('property', fn ($query) => $query->where('title', 'like', "%{$term}%"))
            ))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.engagements.index', [
            'engagements' => $engagements,
            'status' => $status,
            'statusCounts' => Engagement::toBase()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
            'total' => Engagement::count(),
        ]);
    }

    public function show(Engagement $engagement): View
    {
        $engagement->load(['property.coverImage', 'user', 'updates.author']);

        $otherEngagements = Engagement::where('email', $engagement->email)
            ->whereKeyNot($engagement->id)
            ->with('property')
            ->latest()
            ->get();

        return view('admin.engagements.show', [
            'engagement' => $engagement,
            'otherEngagements' => $otherEngagements,
        ]);
    }
}
