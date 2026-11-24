<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EnquiryStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateEnquiryRequest;
use App\Models\Enquiry;
use App\Models\EnquiryType;
use App\Models\InvestmentOpportunity;
use App\Models\Property;
use App\Notifications\EnquiryProgressUpdated;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EnquiryController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.enquiries.index', [
            'enquiries' => $this->filtered($request)->with(['enquirable', 'enquiryType'])->latest()->paginate(15)->withQueryString(),
            'types' => EnquiryType::ordered()->get(),
            'statusCounts' => Enquiry::toBase()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
        ]);
    }

    public function show(Enquiry $enquiry): View
    {
        $enquiry->load(['enquirable', 'user', 'enquiryType', 'updates.author']);

        return view('admin.enquiries.show', [
            'enquiry' => $enquiry,
            'previousEnquiries' => Enquiry::where('email', $enquiry->email)->whereKeyNot($enquiry->id)->latest()->take(5)->get(),
        ]);
    }

    public function update(UpdateEnquiryRequest $request, Enquiry $enquiry): RedirectResponse
    {
        $previousStatus = $enquiry->status;
        $enquiry->update($request->safe()->only(['status', 'admin_notes']));
        $message = $request->validated('client_message');

        if ($enquiry->status === $previousStatus && blank($message)) {
            return back()->with('success', 'Enquiry updated.');
        }

        $update = $enquiry->updates()->create([
            'status' => $enquiry->status,
            'message' => $message,
            'user_id' => $request->user()->id,
        ]);

        if (! $enquiry->user) {
            return back()->with('success', 'Enquiry updated. This is a guest enquiry, so let them know by email, phone or WhatsApp.');
        }

        $enquiry->user->notify(new EnquiryProgressUpdated($update));

        return back()->with('success', "Enquiry updated and {$enquiry->name} has been notified.");
    }

    public function destroy(Enquiry $enquiry): RedirectResponse
    {
        $enquiry->delete();

        return to_route('admin.enquiries.index')->with('success', 'Enquiry deleted.');
    }

    /**
     * Download the (filtered) enquiries as a CSV file.
     */
    public function export(Request $request): StreamedResponse
    {
        $enquiries = $this->filtered($request)->with(['enquirable', 'enquiryType'])->latest();

        return response()->streamDownload(function () use ($enquiries): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Date', 'Name', 'Email', 'Phone', 'Type', 'Interest', 'Status', 'Message', 'Internal notes']);

            $enquiries->lazy()->each(function (Enquiry $enquiry) use ($handle): void {
                fputcsv($handle, array_map(fn (?string $value) => $this->safeCell($value), [
                    $enquiry->created_at->format('Y-m-d H:i'),
                    $enquiry->name,
                    $enquiry->email,
                    $enquiry->phone,
                    $enquiry->typeLabel(),
                    $enquiry->subject(),
                    $enquiry->status->label(),
                    $enquiry->message,
                    $enquiry->admin_notes,
                ]));
            });

            fclose($handle);
        }, 'enquiries-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv']);
    }

    /**
     * @return Builder<Enquiry>
     */
    private function filtered(Request $request): Builder
    {
        return Enquiry::query()
            ->when(EnquiryStatus::tryFrom($request->string('status')->toString()), fn ($query, EnquiryStatus $status) => $query->where('status', $status))
            ->when(EnquiryType::findBySlug($request->string('type')->toString()), fn ($query, EnquiryType $type) => $query->whereBelongsTo($type))
            ->when($request->string('q')->trim()->toString(), fn ($query, string $term) => $query->where(
                fn ($query) => $query->where('name', 'like', "%{$term}%")->orWhere('email', 'like', "%{$term}%")->orWhere('interest', 'like', "%{$term}%")
                    ->orWhereHasMorph('enquirable', [InvestmentOpportunity::class, Property::class], fn ($query) => $query->where('title', 'like', "%{$term}%"))
            ));
    }

    /**
     * Stop spreadsheet apps from treating visitor input as a formula.
     */
    private function safeCell(?string $value): string
    {
        $value = (string) $value;

        return preg_match('/^[=+\-@\t\r]/', $value) ? "'{$value}" : $value;
    }
}
