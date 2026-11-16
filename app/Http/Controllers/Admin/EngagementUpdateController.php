<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreEngagementUpdateRequest;
use App\Models\Engagement;
use App\Notifications\EngagementProgressUpdated;
use Illuminate\Http\RedirectResponse;

class EngagementUpdateController extends Controller
{
    public function store(StoreEngagementUpdateRequest $request, Engagement $engagement): RedirectResponse
    {
        $update = $engagement->updates()->create([
            'author_id' => $request->user()->id,
            'status' => $request->validated('status'),
            'note' => $request->validated('note'),
        ]);

        $engagement->update(['status' => $update->status]);

        if ($engagement->isGuest()) {
            return back()->with('success', "Progress saved. This is a guest — remember to reach out to {$engagement->email} manually.");
        }

        if ($request->validated('notify') && $engagement->user) {
            $engagement->user->notify(new EngagementProgressUpdated($update->setRelation('engagement', $engagement)));

            return back()->with('success', "Progress saved and {$engagement->name} was notified by email and in-app.");
        }

        return back()->with('success', 'Progress saved.');
    }
}
