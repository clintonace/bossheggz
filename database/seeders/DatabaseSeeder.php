<?php

namespace Database\Seeders;

use App\Enums\DataSet;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database: one admin account (only when there is none yet)
     * and, when listings.seed_demo_data is on, every demo dataset from the admin Data manager.
     */
    public function run(): void
    {
        $this->createAdmin();

        if (! config('listings.seed_demo_data')) {
            $this->command?->info('Demo data skipped. Add it any time from Admin → Data manager.');

            return;
        }

        foreach (DataSet::seedOrder() as $dataSet) {
            $dataSet->seed();
        }
    }

    /**
     * The single admin account. Locally it falls back to admin@example.com / password;
     * anywhere else ADMIN_EMAIL and ADMIN_PASSWORD must be set.
     */
    private function createAdmin(): void
    {
        if (User::where('is_admin', true)->exists()) {
            return;
        }

        $isLocal = app()->environment(['local', 'testing']);
        $email = config('listings.admin.email') ?: ($isLocal ? 'admin@example.com' : null);
        $password = config('listings.admin.password') ?: ($isLocal ? 'password' : null);

        if (! $email || ! $password) {
            $this->command?->warn('No admin created: set ADMIN_EMAIL and ADMIN_PASSWORD, or run php artisan app:make-admin you@example.com');

            return;
        }

        User::forceCreate([
            'name' => config('listings.admin.name'),
            'email' => $email,
            'password' => Hash::make($password),
            'is_admin' => true,
            'email_verified_at' => now(),
        ]);
    }
}
