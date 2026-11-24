<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Engagement;
use App\Models\InvestmentOpportunity;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SidebarNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_sidebar_has_a_dropdown_for_every_managed_section(): void
    {
        $response = $this->actingAs(User::factory()->admin()->create())->get(route('admin.dashboard'))->assertOk();

        foreach (['nav-properties', 'nav-engagements', 'nav-enquiries', 'nav-investments', 'nav-land', 'nav-insights', 'nav-adverts', 'nav-site'] as $group) {
            $response->assertSee('data-toggle="#'.$group.'"', false);
        }

        $response->assertSee(route('admin.properties.create'), false)
            ->assertSee(route('admin.opportunities.create', ['category' => 'land_banking']), false)
            ->assertSee(route('admin.articles.index', ['status' => 'scheduled']), false);
    }

    public function test_the_current_section_dropdown_opens_with_the_current_view_highlighted(): void
    {
        $response = $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.properties.index', ['status' => 'sold']))
            ->assertOk();

        $response->assertSee('id="nav-properties" class="nav-group-panel is-open"', false)
            ->assertSee('id="nav-insights" class="nav-group-panel"', false);

        $this->assertMatchesRegularExpression(
            '#<a href="'.preg_quote(route('admin.properties.index', ['status' => 'sold']), '#').'"[^>]*aria-current="page"#',
            $response->getContent(),
        );
    }

    public function test_editing_a_land_opportunity_opens_the_land_banking_dropdown(): void
    {
        $opportunity = InvestmentOpportunity::factory()->land()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.opportunities.edit', $opportunity))
            ->assertOk()
            ->assertSee('id="nav-land" class="nav-group-panel is-open"', false)
            ->assertSee('id="nav-investments" class="nav-group-panel"', false);
    }

    public function test_client_sidebar_groups_engagements_notifications_and_browsing(): void
    {
        $user = User::factory()->create();
        Engagement::factory()->forUser($user)->for(Property::factory())->create();

        $this->actingAs($user)
            ->get(route('account.engagements.index', ['filter' => 'open']))
            ->assertOk()
            ->assertSee('id="nav-my-engagements" class="nav-group-panel is-open"', false)
            ->assertSee('data-toggle="#nav-my-notifications"', false)
            ->assertSee('data-toggle="#nav-explore"', false)
            ->assertSee(route('properties.index', ['listing' => 'shortlet']), false);
    }

    public function test_notifications_can_be_filtered_to_unread(): void
    {
        $user = User::factory()->create();
        $user->notifications()->create(['id' => fake()->uuid(), 'type' => 'test', 'data' => ['title' => 'Already seen'], 'read_at' => now()]);
        $user->notifications()->create(['id' => fake()->uuid(), 'type' => 'test', 'data' => ['title' => 'Brand new update']]);

        $content = $this->actingAs($user)
            ->get(route('account.notifications.index', ['filter' => 'unread']))
            ->assertOk()
            ->getContent();

        // The bell menu in the top bar lists both; the page list only shows the unread one.
        $this->assertSame(2, substr_count($content, 'Brand new update'));
        $this->assertSame(1, substr_count($content, 'Already seen'));
    }
}
