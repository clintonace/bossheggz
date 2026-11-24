<?php

namespace Database\Seeders;

use App\Enums\EnquiryStatus;
use App\Models\Enquiry;
use App\Models\EnquiryType;
use App\Models\InvestmentOpportunity;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Gives the demo client (test@example.com) a land banking and an investment enquiry with
 * adviser updates, so the client dashboard's "My enquiries" area has something to show.
 * Safe to run more than once.
 */
class ClientEnquirySeeder extends Seeder
{
    public function run(): void
    {
        $client = User::where('email', 'test@example.com')->first();
        $admin = User::where('is_admin', true)->orderBy('id')->first();

        if (! $client) {
            return;
        }

        $land = InvestmentOpportunity::visible()->land()->first();
        $investment = InvestmentOpportunity::visible()->whereNot(fn ($query) => $query->land())->first();

        $demos = [
            [$land, EnquiryType::LandBanking, 'Plots for long-term holding', EnquiryStatus::Interested, [
                [EnquiryStatus::Contacted, 'Thanks for your interest! I have emailed the survey plan and the estate layout.', 3],
                [EnquiryStatus::Interested, 'Site inspection is booked for Saturday at 10am — I will meet you at the estate gate.', 1],
            ]],
            [$investment, EnquiryType::Investment, 'Minimum participation and timeline', EnquiryStatus::Contacted, [
                [EnquiryStatus::Contacted, null, 2],
            ]],
        ];

        foreach ($demos as [$opportunity, $typeSlug, $interest, $status, $updates]) {
            if (! $opportunity || Enquiry::where('user_id', $client->id)->where('interest', $interest)->exists()) {
                continue;
            }

            $enquiry = Enquiry::create([
                'user_id' => $client->id,
                'enquiry_type_id' => EnquiryType::where('slug', $typeSlug)->value('id'),
                'status' => $status,
                'name' => $client->name,
                'email' => $client->email,
                'interest' => $interest,
                'message' => 'Please share the documentation and next steps.',
            ]);
            $enquiry->enquirable()->associate($opportunity);
            $enquiry->created_at = now()->subDays(5);
            $enquiry->save();

            foreach ($updates as [$updateStatus, $message, $daysAgo]) {
                $update = $enquiry->updates()->create(['status' => $updateStatus, 'message' => $message, 'user_id' => $admin?->id]);
                $update->forceFill(['created_at' => now()->subDays($daysAgo), 'updated_at' => now()->subDays($daysAgo)])->save();
            }
        }
    }
}
