<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Enums\DataSet;
use App\Enums\LandingPage;
use App\Enums\SiteArea;
use App\Models\Advert;
use App\Models\Article;
use App\Models\Engagement;
use App\Models\Enquiry;
use App\Models\InvestmentOpportunity;
use App\Models\PageSection;
use App\Models\Property;
use App\Models\PropertyImage;
use App\Models\SiteSetting;
use App\Models\User;
use App\Notifications\EnquiryProgressUpdated;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DataManagerControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
    }

    public function test_data_manager_is_admin_only(): void
    {
        $this->get(route('admin.data.index'))->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create())->get(route('admin.data.index'))->assertForbidden();
        $this->actingAs(User::factory()->create())->post(route('admin.data.seed.store'), ['datasets' => ['properties']])->assertForbidden();
        $this->actingAs(User::factory()->create())->delete(route('admin.data.destroy'), ['datasets' => ['properties'], 'confirmation' => 'DELETE'])->assertForbidden();

        $this->assertSame(0, Property::count());
    }

    public function test_pages_render_with_counts_and_quick_select(): void
    {
        Property::factory()->count(2)->create();

        $this->actingAs($this->admin)->get(route('admin.data.index'))
            ->assertOk()
            ->assertSee('Property listings')
            ->assertSee('You are the only admin')
            ->assertSee('id="nav-data"', false);

        $this->actingAs($this->admin)->get(route('admin.data.seed', ['only' => 'land_banking']))
            ->assertOk()
            ->assertSee('data-check-preset', false)
            ->assertSee('value="land_banking" data-group="listings-content" class="peer sr-only" checked', false)
            ->assertDontSee('value="notifications"', false);

        $this->actingAs($this->admin)->get(route('admin.data.delete'))
            ->assertOk()
            ->assertSee('Type DELETE to confirm')
            ->assertSee('value="admins"', false);
    }

    public function test_admin_seeds_only_what_they_choose(): void
    {
        $this->actingAs($this->admin)->post(route('admin.data.seed.store'), ['datasets' => ['properties', 'land_banking']])
            ->assertRedirect(route('admin.data.index'))
            ->assertSessionHas('success', fn (string $message) => str_contains($message, '9 property listings') && str_contains($message, '3 land opportunities'));

        $this->assertSame(9, Property::count());
        $this->assertSame(3, InvestmentOpportunity::query()->land()->count());
        $this->assertSame(0, InvestmentOpportunity::whereNot(fn ($query) => $query->land())->count());
        $this->assertSame(0, Article::count());
        $this->assertSame(0, Advert::count());
    }

    public function test_seeding_again_adds_no_duplicates(): void
    {
        $this->actingAs($this->admin)->post(route('admin.data.seed.store'), ['datasets' => ['properties', 'articles']]);
        $this->actingAs($this->admin)->post(route('admin.data.seed.store'), ['datasets' => ['properties', 'articles']])
            ->assertSessionHas('success', 'That demo data is already in place — nothing new was added.');

        $this->assertSame(9, Property::count());
        $this->assertSame(6, Article::count());
    }

    public function test_seeding_activity_brings_in_what_it_needs_but_never_admins(): void
    {
        $this->actingAs($this->admin)->post(route('admin.data.seed.store'), ['datasets' => ['engagements', 'enquiries']])
            ->assertSessionHasNoErrors();

        $this->assertSame(9, Property::count());
        $this->assertSame(3, User::where('is_admin', false)->count());
        $this->assertGreaterThan(0, Engagement::count());
        $this->assertGreaterThan(0, Enquiry::whereNotNull('user_id')->count());
        $this->assertSame(1, User::where('is_admin', true)->count());
    }

    public function test_unknown_or_unseedable_datasets_are_rejected(): void
    {
        $this->actingAs($this->admin)->post(route('admin.data.seed.store'), ['datasets' => ['notifications']])->assertSessionHasErrors('datasets.0');
        $this->actingAs($this->admin)->post(route('admin.data.seed.store'), ['datasets' => []])->assertSessionHasErrors('datasets');
        $this->actingAs($this->admin)->delete(route('admin.data.destroy'), ['datasets' => ['everything'], 'confirmation' => 'DELETE'])->assertSessionHasErrors('datasets.0');
    }

    public function test_deleting_requires_typing_delete(): void
    {
        Property::factory()->create();

        $this->actingAs($this->admin)->delete(route('admin.data.destroy'), ['datasets' => ['properties'], 'confirmation' => 'remove'])
            ->assertSessionHasErrors('confirmation');
        $this->actingAs($this->admin)->delete(route('admin.data.destroy'), ['datasets' => ['properties']])
            ->assertSessionHasErrors('confirmation');

        $this->assertSame(1, Property::count());
    }

    public function test_confirmation_word_is_not_case_sensitive(): void
    {
        Property::factory()->create();

        $this->actingAs($this->admin)->delete(route('admin.data.destroy'), ['datasets' => ['properties'], 'confirmation' => ' delete '])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.data.index'));

        $this->assertSame(0, Property::count());
    }

    public function test_delete_page_explains_why_the_button_is_locked(): void
    {
        $this->actingAs($this->admin)->get(route('admin.data.delete'))
            ->assertSee('data-check-hint', false)
            ->assertSee('Tick at least one item above to delete.');
    }

    public function test_deleting_properties_removes_photos_and_engagements_only(): void
    {
        $property = Property::factory()->create();
        PropertyImage::factory()->for($property)->create();
        Engagement::factory()->for($property)->create();
        Article::factory()->create();

        $this->actingAs($this->admin)->delete(route('admin.data.destroy'), ['datasets' => ['properties'], 'confirmation' => 'DELETE'])
            ->assertRedirect(route('admin.data.index'))
            ->assertSessionHas('success', fn (string $message) => str_contains($message, '1 property listing') && str_contains($message, '1 engagement'));

        $this->assertSame(0, Property::count());
        $this->assertSame(0, PropertyImage::count());
        $this->assertSame(0, Engagement::count());
        $this->assertSame(1, Article::count());
    }

    public function test_deleting_everything_keeps_only_the_signed_in_admin(): void
    {
        $this->actingAs($this->admin)->post(route('admin.data.seed.store'), ['datasets' => ['engagements', 'enquiries', 'articles', 'adverts']]);
        $otherAdmin = User::factory()->admin()->create();
        PageSection::factory()->section(LandingPage::Home, 'hero')->create();
        SiteSetting::create(['area' => SiteArea::Landing, 'settings' => ['mode' => 'dark']]);
        $this->admin->notify(new EnquiryProgressUpdated(Enquiry::first()->updates()->create(['status' => 'contacted'])));

        $this->actingAs($this->admin)->delete(route('admin.data.destroy'), [
            'datasets' => collect(DataSet::cases())->map->value->all(),
            'confirmation' => 'DELETE',
        ])->assertSessionHas('success', fn (string $message) => str_contains($message, 'Your admin account was kept'));

        $this->assertSame([$this->admin->id], User::pluck('id')->all());
        $this->assertModelMissing($otherAdmin);
        foreach ([Property::class, InvestmentOpportunity::class, Article::class, Advert::class, Engagement::class, Enquiry::class, PageSection::class, SiteSetting::class] as $model) {
            $this->assertSame(0, $model::count(), $model);
        }
        $this->assertSame(0, $this->admin->notifications()->count());
    }

    public function test_deleting_clients_keeps_admins_and_guest_records(): void
    {
        $client = User::factory()->create();
        $enquiry = Enquiry::factory()->create(['user_id' => $client->id]);

        $this->actingAs($this->admin)->delete(route('admin.data.destroy'), ['datasets' => ['clients'], 'confirmation' => 'DELETE']);

        $this->assertModelMissing($client);
        $this->assertModelExists($this->admin);
        $this->assertNull($enquiry->fresh()->user_id);
    }

    public function test_full_database_seeder_never_adds_a_second_admin_or_duplicates(): void
    {
        config(['listings.seed_demo_data' => true]);

        $this->seed();
        $this->seed();

        $this->assertSame([$this->admin->id], User::where('is_admin', true)->pluck('id')->all());
        $this->assertSame(9, Property::count());
        $this->assertSame(6, Article::count());
    }

    public function test_full_database_seeder_creates_one_admin_on_an_empty_database(): void
    {
        config(['listings.seed_demo_data' => true]);
        $this->admin->delete();

        $this->seed();

        $this->assertSame(['admin@example.com'], User::where('is_admin', true)->pluck('email')->all());
    }

    public function test_without_demo_data_only_the_admin_is_seeded(): void
    {
        config(['listings.seed_demo_data' => false, 'listings.admin.email' => 'owner@bosshegz.test', 'listings.admin.password' => 'a-long-password']);
        $this->admin->delete();

        $this->seed();

        $this->assertSame(['owner@bosshegz.test'], User::pluck('email')->all());
        foreach ([Property::class, InvestmentOpportunity::class, Article::class, Advert::class, Engagement::class, Enquiry::class] as $model) {
            $this->assertSame(0, $model::count(), $model);
        }

        $this->actingAs(User::sole())->post(route('admin.data.seed.store'), ['datasets' => ['properties']])->assertSessionHasNoErrors();
        $this->assertSame(9, Property::count());
    }

    public function test_live_seeding_never_creates_a_default_admin(): void
    {
        config(['listings.seed_demo_data' => false, 'listings.admin.email' => null, 'listings.admin.password' => null]);
        $this->admin->delete();
        $this->app['env'] = 'production';

        $this->seed();

        $this->assertSame(0, User::count());
    }
}
