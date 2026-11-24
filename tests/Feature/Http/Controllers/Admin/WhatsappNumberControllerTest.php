<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Models\User;
use App\Models\WhatsappNumber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WhatsappNumberControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private WhatsappNumber $main;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        WhatsappNumber::query()->delete();
        $this->main = WhatsappNumber::factory()->default()->create(['label' => 'Main line', 'number' => '2348111708068']);
    }

    public function test_non_admins_cannot_manage_numbers(): void
    {
        $this->actingAs(User::factory()->create())->get(route('admin.whatsapp-numbers.index'))->assertForbidden();
    }

    public function test_index_lists_numbers_with_the_default_marked(): void
    {
        $this->actingAs($this->admin)->get(route('admin.whatsapp-numbers.index'))
            ->assertOk()
            ->assertSeeText('Main line')
            ->assertSeeText('+2348111708068')
            ->assertSeeText('Default');
    }

    public function test_store_cleans_the_number_and_can_become_the_new_default(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.whatsapp-numbers.store'), ['label' => 'Sales desk', 'number' => '+234 (0) 803-000-0000', 'is_default' => '1', 'is_active' => '1'])
            ->assertSessionHasNoErrors();

        $sales = WhatsappNumber::where('label', 'Sales desk')->sole();
        $this->assertSame('2348030000000', $sales->number);
        $this->assertTrue($sales->is_default);
        $this->assertFalse($this->main->refresh()->is_default);

        $this->get(route('home'))->assertSee('https://wa.me/'.$sales->number, false);
    }

    public function test_numbers_must_be_valid_and_unique(): void
    {
        $this->actingAs($this->admin)->post(route('admin.whatsapp-numbers.store'), ['label' => 'Bad', 'number' => '123'])->assertSessionHasErrors('number');
        $this->actingAs($this->admin)->post(route('admin.whatsapp-numbers.store'), ['label' => 'Dup', 'number' => '+234 811 170 8068'])->assertSessionHasErrors('number');
    }

    public function test_changing_the_number_updates_every_whatsapp_button(): void
    {
        $this->actingAs($this->admin)
            ->put(route('admin.whatsapp-numbers.update', $this->main), ['label' => 'Main line', 'number' => '+234 909 000 0000', 'is_default' => '1', 'is_active' => '1'])
            ->assertSessionHasNoErrors();

        $this->get(route('contact'))
            ->assertSee('https://wa.me/2349090000000', false)
            ->assertDontSee('https://wa.me/2348111708068', false);
    }

    public function test_turning_off_the_default_promotes_another_active_number(): void
    {
        $backup = WhatsappNumber::factory()->create(['label' => 'Backup']);

        $this->actingAs($this->admin)
            ->put(route('admin.whatsapp-numbers.update', $this->main), ['label' => 'Main line', 'number' => $this->main->number, 'is_default' => '1', 'is_active' => '0'])
            ->assertSessionHasNoErrors();

        $this->assertFalse($this->main->refresh()->is_default);
        $this->assertTrue($backup->refresh()->is_default);
    }

    public function test_the_last_number_cannot_be_removed_but_others_can(): void
    {
        $this->actingAs($this->admin)->delete(route('admin.whatsapp-numbers.destroy', $this->main))->assertSessionHas('error');
        $this->assertModelExists($this->main);

        $backup = WhatsappNumber::factory()->create();
        $this->actingAs($this->admin)->delete(route('admin.whatsapp-numbers.destroy', $this->main))->assertRedirect(route('admin.whatsapp-numbers.index'));

        $this->assertModelMissing($this->main);
        $this->assertTrue($backup->refresh()->is_default);
    }
}
