<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\WhatsappNumberRequest;
use App\Models\WhatsappNumber;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class WhatsappNumberController extends Controller
{
    public function index(): View
    {
        return view('admin.whatsapp-numbers.index', [
            'numbers' => WhatsappNumber::withCount('enquiryTypes')->orderByDesc('is_default')->orderBy('sort_order')->orderBy('id')->get(),
            'newNumber' => new WhatsappNumber(['is_active' => true, 'is_default' => ! WhatsappNumber::where('is_default', true)->exists()]),
        ]);
    }

    public function store(WhatsappNumberRequest $request): RedirectResponse
    {
        $whatsappNumber = WhatsappNumber::create($request->validated());

        $this->ensureDefaultExists();

        return to_route('admin.whatsapp-numbers.index')->with('success', "{$whatsappNumber->label} ({$whatsappNumber->display}) added.");
    }

    public function edit(WhatsappNumber $whatsappNumber): View
    {
        $whatsappNumber->loadCount('enquiryTypes');

        return view('admin.whatsapp-numbers.edit', ['whatsappNumber' => $whatsappNumber]);
    }

    public function update(WhatsappNumberRequest $request, WhatsappNumber $whatsappNumber): RedirectResponse
    {
        $whatsappNumber->update($request->validated());

        $this->ensureDefaultExists();

        return to_route('admin.whatsapp-numbers.index')->with('success', "{$whatsappNumber->label} updated.");
    }

    public function destroy(WhatsappNumber $whatsappNumber): RedirectResponse
    {
        if (WhatsappNumber::count() === 1) {
            return back()->with('error', 'Keep at least one WhatsApp number — every WhatsApp button on the website uses it.');
        }

        $whatsappNumber->delete();

        $this->ensureDefaultExists();

        return to_route('admin.whatsapp-numbers.index')->with('success', 'WhatsApp number removed. Enquiry types that used it now use the default number.');
    }

    /**
     * There must always be one default number while any number is active.
     */
    private function ensureDefaultExists(): void
    {
        if (! WhatsappNumber::where('is_default', true)->where('is_active', true)->exists()) {
            WhatsappNumber::where('is_active', true)->orderBy('sort_order')->orderBy('id')->first()?->update(['is_default' => true]);
        }
    }
}
