<?php

namespace App\Http\Controllers\Account;

use App\Enums\InterestArea;
use App\Http\Controllers\Controller;
use App\Models\Enquiry;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EnquiryController extends Controller
{
    /**
     * The client's enquiries, grouped by interest area (land banking, investments, properties, advice)
     * and filtered by in progress / completed.
     */
    public function index(Request $request): View
    {
        $area = InterestArea::tryFrom((string) $request->query('area'));
        $filter = in_array($request->query('filter'), ['open', 'completed'], true) ? $request->query('filter') : 'all';

        $enquiries = $request->user()->enquiries()
            ->with(['enquirable', 'enquiryType', 'latestUpdate'])
            ->latest()
            ->get();

        $inArea = $area ? $enquiries->filter(fn (Enquiry $enquiry) => $enquiry->area() === $area) : $enquiries;

        return view('account.enquiries', [
            'area' => $area,
            'filter' => $filter,
            'enquiries' => $inArea->filter(fn (Enquiry $enquiry) => match ($filter) {
                'open' => $enquiry->status->isOpen(),
                'completed' => ! $enquiry->status->isOpen(),
                default => true,
            })->values(),
            'areaCounts' => collect(InterestArea::cases())->mapWithKeys(fn (InterestArea $case) => [
                $case->value => $enquiries->filter(fn (Enquiry $enquiry) => $enquiry->area() === $case)->count(),
            ]),
            'counts' => [
                'all' => $inArea->count(),
                'open' => $inArea->filter(fn (Enquiry $enquiry) => $enquiry->status->isOpen())->count(),
                'completed' => $inArea->reject(fn (Enquiry $enquiry) => $enquiry->status->isOpen())->count(),
            ],
            'total' => $enquiries->count(),
        ]);
    }

    public function show(Request $request, Enquiry $enquiry): View
    {
        abort_unless($enquiry->user_id === $request->user()->id, 404);

        $enquiry->load(['enquirable', 'enquiryType.whatsappNumber', 'updates']);

        // Opening the enquiry counts as reading its notifications.
        $request->user()->unreadNotifications()
            ->where('data->url', route('account.enquiries.show', $enquiry))
            ->update(['read_at' => now()]);

        return view('account.enquiry', ['enquiry' => $enquiry]);
    }
}
