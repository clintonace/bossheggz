<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\EnquiryTypeRequest;
use App\Models\EnquiryType;
use App\Models\WhatsappNumber;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EnquiryTypeController extends Controller
{
    public function index(): View
    {
        return view('admin.enquiry-types.index', [
            'types' => EnquiryType::ordered()->with('whatsappNumber')->withCount('enquiries')->get(),
            'newType' => new EnquiryType(['is_active' => true]),
            'whatsappNumbers' => WhatsappNumber::orderByDesc('is_default')->orderBy('sort_order')->get(),
        ]);
    }

    public function store(EnquiryTypeRequest $request): RedirectResponse
    {
        $type = EnquiryType::create($request->validated());

        return to_route('admin.enquiry-types.index')->with('success', "“{$type->name}” added. It now appears in the contact form.");
    }

    public function edit(EnquiryType $enquiryType): View
    {
        $enquiryType->loadCount('enquiries');

        return view('admin.enquiry-types.edit', [
            'type' => $enquiryType,
            'whatsappNumbers' => WhatsappNumber::orderByDesc('is_default')->orderBy('sort_order')->get(),
        ]);
    }

    public function update(EnquiryTypeRequest $request, EnquiryType $enquiryType): RedirectResponse
    {
        $enquiryType->update($request->validated());

        return to_route('admin.enquiry-types.index')->with('success', "“{$enquiryType->name}” updated.");
    }

    public function destroy(EnquiryType $enquiryType): RedirectResponse
    {
        if ($enquiryType->is_system) {
            return back()->with('error', 'Built-in enquiry types are used by the website and cannot be deleted. You can rename them instead.');
        }

        if ($enquiryType->enquiries()->exists()) {
            return back()->with('error', 'This type already has enquiries. Turn it off instead so past enquiries keep their type.');
        }

        $enquiryType->delete();

        return to_route('admin.enquiry-types.index')->with('success', 'Enquiry type deleted.');
    }
}
