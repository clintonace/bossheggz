<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Models\Engagement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_sees_dashboard_with_recent_engagements(): void
    {
        $engagement = Engagement::factory()->create(['name' => 'Recent Prospect']);

        $response = $this->actingAs(User::factory()->admin()->create())->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSeeText('Recent Prospect');
        $response->assertSeeText($engagement->property->title);
    }

    public function test_regular_user_is_forbidden(): void
    {
        $this->actingAs(User::factory()->create())->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
    }
}
