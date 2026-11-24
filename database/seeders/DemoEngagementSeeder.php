<?php

namespace Database\Seeders;

use App\Enums\EngagementStatus;
use App\Models\Engagement;
use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Demo engagements: two for the demo client (one with progress updates) and a handful from guests.
 * Needs properties and demo clients. Safe to run more than once.
 */
class DemoEngagementSeeder extends Seeder
{
    /** @var list<array{0: string, 1: string, 2: EngagementStatus, 3: int}> name, email, status, days ago */
    private const Guests = [
        ['Chidi Okafor', 'chidi.okafor@example.com', EngagementStatus::New, 0],
        ['Funke Adeyemi', 'funke.adeyemi@example.com', EngagementStatus::New, 1],
        ['Ibrahim Musa', 'ibrahim.musa@example.com', EngagementStatus::Contacted, 2],
        ['Ngozi Eze', 'ngozi.eze@example.com', EngagementStatus::New, 6],
        ['Tunde Bakare', 'tunde.bakare@example.com', EngagementStatus::Contacted, 5],
        ['Aisha Bello', 'aisha.bello@example.com', EngagementStatus::Negotiating, 10],
        ['Emeka Nwosu', 'emeka.nwosu@example.com', EngagementStatus::New, 12],
    ];

    public function run(): void
    {
        $properties = Property::visible()->orderBy('id')->get();

        if ($properties->isEmpty()) {
            return;
        }

        $admin = User::where('is_admin', true)->orderBy('id')->first();
        $client = User::where('email', 'test@example.com')->first();

        if ($client && ! Engagement::where('user_id', $client->id)->exists()) {
            $progressed = $this->engagement($properties[0], $client->name, $client->email, $client->phone, EngagementStatus::ViewingScheduled, 4, $client->id, 'I would love to arrange a viewing this weekend if possible.');
            $progressed->updates()->create(['author_id' => $admin?->id, 'status' => EngagementStatus::Contacted, 'note' => 'Called to confirm interest and budget.', 'created_at' => now()->subDays(3)]);
            $progressed->updates()->create(['author_id' => $admin?->id, 'status' => EngagementStatus::ViewingScheduled, 'note' => 'Viewing booked for Saturday at 11am.', 'created_at' => now()->subDay()]);

            $this->engagement($properties[min(2, $properties->count() - 1)], $client->name, $client->email, $client->phone, EngagementStatus::New, 0, $client->id, 'Is this still available? I would like more photos.');
        }

        foreach (self::Guests as $index => [$name, $email, $status, $daysAgo]) {
            if (Engagement::where('email', $email)->exists()) {
                continue;
            }

            $this->engagement(
                $properties[$index % $properties->count()],
                $name,
                $email,
                sprintf('+234 803 000 %04d', 200 + $index),
                $status,
                $daysAgo,
                null,
                'Please call me with more details about this property.',
            );
        }
    }

    private function engagement(Property $property, string $name, string $email, ?string $phone, EngagementStatus $status, int $daysAgo, ?int $userId, string $message): Engagement
    {
        $engagement = Engagement::create([
            'property_id' => $property->id,
            'user_id' => $userId,
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'message' => $message,
            'status' => $status,
        ]);

        $engagement->forceFill(['created_at' => now()->subDays($daysAgo)->subHours(2), 'updated_at' => now()->subDays($daysAgo)])->save();

        return $engagement;
    }
}
