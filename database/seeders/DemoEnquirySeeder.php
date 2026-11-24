<?php

namespace Database\Seeders;

use App\Enums\EnquiryStatus;
use App\Models\Enquiry;
use App\Models\EnquiryType;
use App\Models\InvestmentOpportunity;
use Illuminate\Database\Seeder;

/**
 * Demo enquiries: a few from guests (Speak to an Adviser, land banking, investments, list a property)
 * plus the demo client's tracked enquiries. Safe to run more than once.
 */
class DemoEnquirySeeder extends Seeder
{
    public function run(): void
    {
        $land = InvestmentOpportunity::visible()->land()->first();

        $guests = [
            ['Bisi Ogunleye', 'bisi.ogunleye@example.com', EnquiryType::Consultation, 'Investment advisory', EnquiryStatus::New, 0, null],
            ['Segun Alade', 'segun.alade@example.com', EnquiryType::LandBanking, 'Plots under ₦10m', EnquiryStatus::Contacted, 2, $land],
            ['Halima Yusuf', 'halima.yusuf@example.com', EnquiryType::Investment, 'Rental income', EnquiryStatus::FollowUp, 4, null],
            ['Obinna Eke', 'obinna.eke@example.com', EnquiryType::ListProperty, '3 bedroom flat in Yaba', EnquiryStatus::Interested, 6, null],
        ];

        foreach ($guests as $index => [$name, $email, $typeSlug, $interest, $status, $daysAgo, $subject]) {
            if (Enquiry::where('email', $email)->exists()) {
                continue;
            }

            $enquiry = new Enquiry([
                'enquiry_type_id' => EnquiryType::where('slug', $typeSlug)->value('id'),
                'status' => $status,
                'name' => $name,
                'email' => $email,
                'phone' => sprintf('+234 805 000 %04d', 300 + $index),
                'interest' => $interest,
                'message' => 'Please get in touch with the available options and next steps.',
            ]);
            $enquiry->enquirable()->associate($subject);
            $enquiry->created_at = now()->subDays($daysAgo)->subHours(3);
            $enquiry->save();
        }

        app(ClientEnquirySeeder::class)->run();
    }
}
