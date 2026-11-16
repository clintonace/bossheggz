<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

#[Signature('app:make-admin {email : Email address of the admin} {--name= : Display name for a new user} {--password= : Password for a new user (generated when omitted)}')]
#[Description('Create a site administrator, or promote an existing user to administrator')]
class MakeAdmin extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = (string) $this->argument('email');

        $user = User::where('email', $email)->first();

        if ($user) {
            $user->forceFill(['is_admin' => true])->save();
            $this->components->info("{$email} is now an administrator.");

            return self::SUCCESS;
        }

        $password = $this->option('password') ?: Str::password(16, symbols: false);

        $user = User::create([
            'name' => $this->option('name') ?: Str::before($email, '@'),
            'email' => $email,
            'password' => $password,
        ]);
        $user->forceFill(['is_admin' => true, 'email_verified_at' => now()])->save();

        $this->components->info("Administrator {$email} created.");

        if (! $this->option('password')) {
            $this->components->twoColumnDetail('Generated password', $password);
        }

        return self::SUCCESS;
    }
}
