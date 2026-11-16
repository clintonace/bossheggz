<?php

namespace Database\Seeders;

use App\Enums\EngagementStatus;
use App\Models\Engagement;
use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::factory()->admin()->create([
            'name' => 'Site Admin',
            'email' => 'admin@example.com',
        ]);

        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->call([PropertySeeder::class, AdvertSeeder::class]);

        $properties = Property::visible()->take(3)->get();

        $progressed = Engagement::factory()->forUser($user)->for($properties[0])->status(EngagementStatus::ViewingScheduled)->create([
            'message' => 'I would love to arrange a viewing this weekend if possible.',
            'created_at' => now()->subDays(4),
        ]);
        $progressed->updates()->create(['author_id' => $admin->id, 'status' => EngagementStatus::Contacted, 'note' => 'Called to confirm interest and budget.', 'created_at' => now()->subDays(3)]);
        $progressed->updates()->create(['author_id' => $admin->id, 'status' => EngagementStatus::ViewingScheduled, 'note' => 'Viewing booked for Saturday at 11am.', 'created_at' => now()->subDay()]);

        Engagement::factory()->forUser($user)->for($properties[2])->create(['created_at' => now()->subHours(5)]);

        foreach ([12, 9, 7, 6, 2, 1, 0] as $daysAgo) {
            Engagement::factory()->for(Property::visible()->inRandomOrder()->first())->create(['created_at' => now()->subDays($daysAgo)->subHours(rand(0, 8))]);
        }

        Engagement::factory()->for($properties[1])->status(EngagementStatus::Negotiating)->create(['created_at' => now()->subDays(10)]);
        Engagement::factory()->for($properties[1])->status(EngagementStatus::Contacted)->create(['created_at' => now()->subDays(5)]);
    }
}
