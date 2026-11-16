<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEngagementRequest;
use App\Models\Engagement;
use App\Models\Property;
use App\Models\User;
use App\Notifications\NewEngagementReceived;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Notification;

class EngagementController extends Controller
{
    public function store(StoreEngagementRequest $request, Property $property): RedirectResponse
    {
        abort_unless($property->status->isPublic(), 404);

        if (! $property->acceptsEngagements()) {
            return back()->with('error', 'This property is no longer accepting new engagements.');
        }

        $user = $request->user();

        if ($user) {
            $existing = $user->engagements()->open()->whereBelongsTo($property)->first();

            if ($existing) {
                return to_route('account.engagements.show', $existing)
                    ->with('info', 'You are already engaged with this property — here is your progress.');
            }
        }

        $engagement = $property->engagements()->create([
            'user_id' => $user?->id,
            'name' => $user->name ?? $request->validated('name'),
            'email' => $user->email ?? $request->validated('email'),
            'phone' => $request->validated('phone') ?: $user?->phone,
            'message' => $request->validated('message'),
        ]);

        $this->notifyAdmins($engagement);

        if ($user) {
            return to_route('account.engagements.show', $engagement)
                ->with('success', 'Thank you! We have received your interest and will keep you updated here and by email.');
        }

        return back()
            ->with('success', "Thank you, {$engagement->name}! Our team will reach out to you at {$engagement->email} shortly.")
            ->with('engaged_email', $engagement->email);
    }

    private function notifyAdmins(Engagement $engagement): void
    {
        $admins = User::where('is_admin', true)->get();

        if ($admins->isNotEmpty()) {
            Notification::send($admins, new NewEngagementReceived($engagement));
        }
    }
}
