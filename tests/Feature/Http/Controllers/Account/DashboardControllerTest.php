<?php

namespace Tests\Feature\Http\Controllers\Account;

use App\Enums\EngagementStatus;
use App\Models\Engagement;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_shows_stats_active_engagements_and_latest_progress_note(): void
    {
        $user = User::factory()->create(['name' => 'Bola Buyer']);
        $engagement = Engagement::factory()->forUser($user)->status(EngagementStatus::ViewingScheduled)
            ->for(Property::factory()->create(['title' => 'Harbour View Flat']))->create();
        $engagement->updates()->create(['status' => EngagementStatus::ViewingScheduled, 'note' => 'Viewing on Saturday at noon']);
        Engagement::factory()->forUser($user)->status(EngagementStatus::Closed)->create();

        $response = $this->actingAs($user)->get(route('account.index'));

        $response->assertOk();
        $response->assertSeeText('You have 1 home in progress.');
        $response->assertSeeText('1 viewing scheduled.');
        $response->assertSeeText('Harbour View Flat');
        $response->assertSeeText('Viewing on Saturday at noon');
    }

    public function test_recommends_similar_available_properties_not_already_engaged(): void
    {
        $user = User::factory()->create();
        $engaged = Property::factory()->create(['city' => 'Lekki', 'title' => 'Already Engaged Home']);
        Engagement::factory()->forUser($user)->for($engaged)->create();
        Property::factory()->create(['city' => 'Lekki', 'title' => 'Lekki Neighbour Home']);
        Property::factory()->sold()->create(['city' => 'Lekki', 'title' => 'Sold Lekki Home']);

        $response = $this->actingAs($user)->get(route('account.index'));

        $response->assertSeeTextInOrder(['You might also like', 'Lekki Neighbour Home']);
        $response->assertDontSeeText('Sold Lekki Home');
        $recommendations = str($response->getContent())->after('You might also like')->toString();
        $this->assertStringNotContainsString('Already Engaged Home', $recommendations);
    }

    public function test_new_user_without_engagements_gets_a_welcome_and_starter_homes(): void
    {
        Property::factory()->create(['title' => 'Starter Pick']);

        $response = $this->actingAs(User::factory()->create(['name' => 'Ada Lovelace']))->get(route('account.index'));

        $response->assertSeeText("Let's find a place you'll love, Ada.");
        $response->assertSeeTextInOrder(['Start with these homes', 'Starter Pick']);
    }

    public function test_does_not_show_other_users_engagements(): void
    {
        $theirs = Engagement::factory()->forUser()->for(Property::factory()->sold()->create(['title' => 'Someone Elses Home']))->create();

        $this->actingAs(User::factory()->create())->get(route('account.index'))
            ->assertOk()
            ->assertDontSeeText($theirs->property->title);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('account.index'))->assertRedirect(route('login'));
    }
}
