<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Models\Enquiry;
use App\Models\EnquiryType;
use App\Models\User;
use App\Models\WhatsappNumber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnquiryTypeControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
    }

    public function test_non_admins_cannot_manage_enquiry_types(): void
    {
        $this->actingAs(User::factory()->create())->get(route('admin.enquiry-types.index'))->assertForbidden();
    }

    public function test_index_lists_built_in_types(): void
    {
        $this->actingAs($this->admin)->get(route('admin.enquiry-types.index'))
            ->assertOk()
            ->assertSeeText('Consultation request')
            ->assertSeeText('Built-in')
            ->assertSeeText('Add an enquiry type');
    }

    public function test_store_adds_a_type_routed_to_a_whatsapp_number(): void
    {
        $whatsappNumber = WhatsappNumber::factory()->create();

        $this->actingAs($this->admin)
            ->post(route('admin.enquiry-types.store'), ['name' => 'Mortgage advice', 'whatsapp_number_id' => $whatsappNumber->id, 'is_active' => '1', 'sort_order' => '10'])
            ->assertRedirect(route('admin.enquiry-types.index'));

        $type = EnquiryType::where('name', 'Mortgage advice')->sole();
        $this->assertSame('mortgage_advice', $type->slug);
        $this->assertTrue($type->whatsappNumber->is($whatsappNumber));
        $this->assertFalse($type->is_system);
    }

    public function test_names_must_be_unique(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.enquiry-types.store'), ['name' => 'Consultation request'])
            ->assertSessionHasErrors('name');
    }

    public function test_built_in_types_can_be_renamed_but_stay_on_and_cannot_be_deleted(): void
    {
        $consultation = EnquiryType::findBySlug('consultation');

        $this->actingAs($this->admin)
            ->put(route('admin.enquiry-types.update', $consultation), ['name' => 'Book a consultation', 'is_active' => '0'])
            ->assertSessionHasNoErrors();

        $consultation->refresh();
        $this->assertSame('Book a consultation', $consultation->name);
        $this->assertSame('consultation', $consultation->slug);
        $this->assertTrue($consultation->is_active);

        $this->actingAs($this->admin)->delete(route('admin.enquiry-types.destroy', $consultation))->assertSessionHas('error');
        $this->assertModelExists($consultation);
    }

    public function test_custom_types_can_be_switched_off_and_deleted_only_when_unused(): void
    {
        $used = EnquiryType::factory()->create(['name' => 'Valuation request']);
        Enquiry::factory()->create(['enquiry_type_id' => $used->id]);
        $unused = EnquiryType::factory()->create(['name' => 'Site inspection']);

        $this->actingAs($this->admin)->put(route('admin.enquiry-types.update', $used), ['name' => 'Valuation request', 'is_active' => '0']);
        $this->assertFalse($used->refresh()->is_active);

        $this->actingAs($this->admin)->delete(route('admin.enquiry-types.destroy', $used))->assertSessionHas('error');
        $this->assertModelExists($used);

        $this->actingAs($this->admin)->delete(route('admin.enquiry-types.destroy', $unused))->assertRedirect(route('admin.enquiry-types.index'));
        $this->assertModelMissing($unused);
    }
}
