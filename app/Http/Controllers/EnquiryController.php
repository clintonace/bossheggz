<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEnquiryRequest;
use App\Models\Enquiry;
use App\Models\EnquiryType;
use App\Models\InvestmentOpportunity;
use App\Models\Property;
use App\Models\User;
use App\Notifications\NewEnquiryReceived;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Notification;

class EnquiryController extends Controller
{
    public function store(StoreEnquiryRequest $request): RedirectResponse
    {
        $user = $request->user();
        $subject = match (true) {
            $request->filled('opportunity') => InvestmentOpportunity::where('slug', $request->validated('opportunity'))->first(),
            $request->filled('property') => Property::where('slug', $request->validated('property'))->first(),
            default => null,
        };

        $enquiry = new Enquiry([
            'user_id' => $user?->id,
            'enquiry_type_id' => EnquiryType::where('slug', $request->validated('type'))->value('id'),
            'name' => $request->validated('name') ?: $user?->name,
            'email' => $request->validated('email') ?: $user?->email,
            'phone' => $request->validated('phone') ?: $user?->phone,
            'interest' => $request->validated('interest'),
            'message' => $request->validated('message'),
        ]);
        $enquiry->enquirable()->associate($subject);
        $enquiry->save();

        $admins = User::where('is_admin', true)->get();

        if ($admins->isNotEmpty()) {
            Notification::send($admins, new NewEnquiryReceived($enquiry));
        }

        return back()
            ->with('success', "Thank you, {$enquiry->name}! An adviser will contact you at {$enquiry->email} shortly.")
            ->with('enquiry_sent', $enquiry->email)
            ->withFragment('enquire');
    }
}
