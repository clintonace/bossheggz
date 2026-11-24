<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Models\Engagement;
use App\Models\Enquiry;
use App\Models\InvestmentOpportunity;
use App\Models\Property;
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

    public function test_shows_website_overview_recent_enquiries_and_activity(): void
    {
        Property::factory()->sold()->create();
        InvestmentOpportunity::factory()->create(['title' => 'Harbour Flats']);
        Enquiry::factory()->create(['name' => 'Ngozi Enquirer']);

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSeeText('Total enquiries')
            ->assertSeeText('Investment opportunities')
            ->assertSeeText('Ngozi Enquirer')
            ->assertSeeText('Recent website activity')
            ->assertSeeText('Investment opportunity published');
    }
}
