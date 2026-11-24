<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Demo client (non-admin) accounts. Never creates admins. Safe to run more than once.
 * Passwords are random: sign in as them with the local "Test accounts" switcher, or reset the password.
 */
class DemoClientSeeder extends Seeder
{
    /** @var list<array{name: string, email: string, phone: string}> */
    public const Clients = [
        ['name' => 'Test User', 'email' => 'test@example.com', 'phone' => '+234 803 000 0100'],
        ['name' => 'Ada Nwankwo', 'email' => 'ada.nwankwo@example.com', 'phone' => '+234 803 000 0101'],
        ['name' => 'Kunle Adebayo', 'email' => 'kunle.adebayo@example.com', 'phone' => '+234 803 000 0102'],
    ];

    public function run(): void
    {
        foreach (self::Clients as $client) {
            User::firstOrCreate(['email' => $client['email']], [
                'name' => $client['name'],
                'phone' => $client['phone'],
                'password' => Hash::make(Str::random(40)),
            ]);
        }
    }
}
