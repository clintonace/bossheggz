<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PropertyImageControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_make_cover_moves_image_to_the_front(): void
    {
        $property = Property::factory()->create();
        $first = $property->images()->create(['path' => 'https://example.com/1.jpg', 'sort_order' => 0]);
        $second = $property->images()->create(['path' => 'https://example.com/2.jpg', 'sort_order' => 1]);

        $this->actingAs(User::factory()->admin()->create())
            ->patch(route('admin.properties.images.cover', [$property, $second]))
            ->assertRedirect();

        $this->assertSame([$second->id, $first->id], $property->images()->pluck('id')->all());
    }

    public function test_image_of_another_property_returns_404(): void
    {
        $property = Property::factory()->create();
        $otherImage = Property::factory()->create()->images()->create(['path' => 'https://example.com/x.jpg']);

        $this->actingAs(User::factory()->admin()->create())
            ->delete(route('admin.properties.images.destroy', [$property, $otherImage]))
            ->assertNotFound();

        $this->assertModelExists($otherImage);
    }

    public function test_admin_removes_image(): void
    {
        $property = Property::factory()->create();
        $image = $property->images()->create(['path' => 'https://example.com/1.jpg']);

        $this->actingAs(User::factory()->admin()->create())
            ->delete(route('admin.properties.images.destroy', [$property, $image]))
            ->assertRedirect();

        $this->assertModelMissing($image);
    }
}
