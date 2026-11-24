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
     * and every demo dataset from the admin Data manager.
     */
    public function run(): void
    {
        if (! User::where('is_admin', true)->exists()) {
            User::forceCreate([
                'name' => 'Site Admin',
                'email' => 'admin@example.com',
                'password' => Hash::make('password'),
                'is_admin' => true,
                'email_verified_at' => now(),
            ]);
        }

        foreach (DataSet::seedOrder() as $dataSet) {
            $dataSet->seed();
        }
    }
}
