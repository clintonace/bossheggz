<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Enums\ListingType;
use App\Enums\PropertyStatus;
use App\Enums\RentPeriod;
use App\Enums\VerificationStatus;
use App\Models\Engagement;
use App\Models\Property;
use App\Models\User;
use App\Notifications\PropertyUpdated;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PropertyControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
    }

    /**
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        return [
            'title' => 'Sunset Villa',
            'listing_type' => 'rent',
            'property_type' => 'house',
            'status' => 'published',
            'price' => '2,500,000',
            'rent_period' => 'year',
            'address' => '1 Palm Avenue',
            'city' => 'Lekki',
            'state' => 'Lagos',
            'bedrooms' => 4,
            'bathrooms' => 3,
            'area_sqm' => 300,
            'description' => 'A lovely home.',
            'features_text' => "Pool\nGym, Garden\n\nPool",
            'is_featured' => '1',
            ...$overrides,
        ];
    }

    public function test_index_lists_all_properties_including_drafts(): void
    {
        Property::factory()->draft()->create(['title' => 'Draft Listing']);

        $this->actingAs($this->admin)->get(route('admin.properties.index'))
            ->assertOk()
            ->assertSeeText('Draft Listing');
    }

    public function test_create_form_renders(): void
    {
        $this->actingAs($this->admin)->get(route('admin.properties.create'))->assertOk();
    }

    public function test_admin_creates_property_with_photos(): void
    {
        Storage::fake('public');

        $response = $this->actingAs($this->admin)->post(route('admin.properties.store'), $this->validPayload([
            'images' => [UploadedFile::fake()->image('front.jpg'), UploadedFile::fake()->image('kitchen.png')],
        ]));

        $property = Property::sole();
        $response->assertRedirect(route('admin.properties.edit', $property));
        $this->assertSame('sunset-villa', $property->slug);
        $this->assertSame(2_500_000, $property->price);
        $this->assertSame(ListingType::Rent, $property->listing_type);
        $this->assertSame(RentPeriod::Year, $property->rent_period);
        $this->assertSame(['Pool', 'Gym', 'Garden'], $property->features);
        $this->assertTrue($property->is_featured);
        $this->assertNotNull($property->published_at);
        $this->assertCount(2, $property->images);
        $property->images->each(fn ($image) => Storage::disk('public')->assertExists($image->path));
    }

    public function test_rent_listing_requires_rent_period(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.properties.store'), $this->validPayload(['rent_period' => '']))
            ->assertSessionHasErrors(['rent_period' => 'Choose how often rent is paid.']);
    }

    public function test_required_fields_are_validated(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.properties.store'), [])
            ->assertSessionHasErrors(['title', 'listing_type', 'property_type', 'status', 'price', 'address', 'city', 'description']);
    }

    public function test_non_image_upload_is_rejected(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin)
            ->post(route('admin.properties.store'), $this->validPayload([
                'images' => [UploadedFile::fake()->create('notes.pdf', 10, 'application/pdf')],
            ]))
            ->assertSessionHasErrors(['images.0' => 'Only image files can be uploaded.']);
    }

    public function test_regular_user_cannot_create_property(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('admin.properties.store'), $this->validPayload())
            ->assertForbidden();

        $this->assertDatabaseCount('properties', 0);
    }

    public function test_price_change_notifies_registered_engagers_but_not_guests(): void
    {
        Notification::fake();
        $property = Property::factory()->create(['price' => 100_000_000]);
        $member = User::factory()->create();
        Engagement::factory()->forUser($member)->for($property)->create();
        Engagement::factory()->for($property)->create();

        $response = $this->actingAs($this->admin)->put(route('admin.properties.update', $property), [
            ...$property->only(['title', 'address', 'city', 'description']),
            'listing_type' => 'sale',
            'property_type' => $property->property_type->value,
            'status' => 'published',
            'price' => 90_000_000,
        ]);

        $response->assertSessionHas('success', 'Property updated. 1 engaged user notified.');
        Notification::assertSentTo($member, PropertyUpdated::class, fn (PropertyUpdated $notification) => str_contains($notification->changes[0], 'Price changed'));
        Notification::assertSentTimes(PropertyUpdated::class, 1);
    }

    public function test_update_without_price_or_status_change_sends_no_notification(): void
    {
        Notification::fake();
        $property = Property::factory()->create();
        Engagement::factory()->forUser()->for($property)->create();

        $this->actingAs($this->admin)->put(route('admin.properties.update', $property), [
            ...$property->only(['address', 'city', 'description', 'price']),
            'title' => 'A Brand New Title',
            'listing_type' => 'sale',
            'property_type' => $property->property_type->value,
            'status' => $property->status->value,
        ])->assertSessionHas('success', 'Property updated.');

        Notification::assertNothingSent();
        $this->assertSame('A Brand New Title', $property->fresh()->title);
    }

    public function test_status_change_to_sold_notifies_engagers(): void
    {
        Notification::fake();
        $property = Property::factory()->create();
        $member = User::factory()->create();
        Engagement::factory()->forUser($member)->for($property)->create();

        $this->actingAs($this->admin)->put(route('admin.properties.update', $property), [
            ...$property->only(['title', 'address', 'city', 'description', 'price']),
            'listing_type' => 'sale',
            'property_type' => $property->property_type->value,
            'status' => PropertyStatus::Sold->value,
        ]);

        Notification::assertSentTo($member, PropertyUpdated::class, fn (PropertyUpdated $notification) => $notification->changes === ['Status changed from Available to Sold']);
    }

    public function test_admin_deletes_property_and_its_photos(): void
    {
        Storage::fake('public');
        $property = Property::factory()->create();
        $path = UploadedFile::fake()->image('a.jpg')->store('properties', 'public');
        $property->images()->create(['path' => $path]);

        $this->actingAs($this->admin)->delete(route('admin.properties.destroy', $property))
            ->assertRedirect(route('admin.properties.index'));

        $this->assertModelMissing($property);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_store_shortlet_defaults_to_nightly_pricing_and_saves_verification(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.properties.store'), $this->validPayload(['listing_type' => 'shortlet', 'rent_period' => 'night', 'verification_status' => 'verified']))
            ->assertSessionHasNoErrors();

        $property = Property::sole();
        $this->assertSame(ListingType::Shortlet, $property->listing_type);
        $this->assertSame(RentPeriod::Night, $property->rent_period);
        $this->assertSame(VerificationStatus::Verified, $property->verification_status);
    }

    public function test_lease_requires_a_price_period(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.properties.store'), $this->validPayload(['listing_type' => 'lease', 'rent_period' => '']))
            ->assertSessionHasErrors('rent_period');
    }
}
