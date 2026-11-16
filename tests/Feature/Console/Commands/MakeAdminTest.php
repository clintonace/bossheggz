<?php

namespace Tests\Feature\Console\Commands;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MakeAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_new_admin_with_given_password(): void
    {
        $this->artisan('app:make-admin', ['email' => 'boss@example.com', '--password' => 'super-secret'])
            ->assertSuccessful();

        $user = User::where('email', 'boss@example.com')->sole();
        $this->assertTrue($user->isAdmin());
        $this->assertTrue(Hash::check('super-secret', $user->password));
    }

    public function test_promotes_existing_user(): void
    {
        $user = User::factory()->create();

        $this->artisan('app:make-admin', ['email' => $user->email])->assertSuccessful();

        $this->assertTrue($user->fresh()->isAdmin());
    }
}
