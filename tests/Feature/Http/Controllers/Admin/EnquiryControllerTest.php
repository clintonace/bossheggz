<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Enums\EnquiryStatus;
use App\Models\Enquiry;
use App\Models\InvestmentOpportunity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnquiryControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
    }

    public function test_non_admins_cannot_see_enquiries(): void
    {
        $this->actingAs(User::factory()->create())->get(route('admin.enquiries.index'))->assertForbidden();
    }

    public function test_index_filters_by_status_type_and_opportunity_title(): void
    {
        $opportunity = InvestmentOpportunity::factory()->create(['title' => 'Harbour Flats']);
        $linked = Enquiry::factory()->ofType('investment')->create(['name' => 'Linked Person']);
        $linked->enquirable()->associate($opportunity)->save();
        Enquiry::factory()->status(EnquiryStatus::Contacted)->create(['name' => 'Contacted Person']);

        $this->actingAs($this->admin)->get(route('admin.enquiries.index', ['status' => 'contacted']))
            ->assertOk()
            ->assertSeeText('Contacted Person')
            ->assertDontSeeText('Linked Person');

        $this->actingAs($this->admin)->get(route('admin.enquiries.index', ['q' => 'Harbour']))
            ->assertSeeText('Linked Person')
            ->assertDontSeeText('Contacted Person');

        $this->actingAs($this->admin)->get(route('admin.enquiries.index', ['type' => 'investment']))
            ->assertSeeText('Linked Person')
            ->assertDontSeeText('Contacted Person');
    }

    public function test_show_offers_email_call_and_whatsapp(): void
    {
        $enquiry = Enquiry::factory()->create(['email' => 'ada@example.com', 'phone' => '+234 803 000 0000']);

        $this->actingAs($this->admin)->get(route('admin.enquiries.show', $enquiry))
            ->assertOk()
            ->assertSee('mailto:ada@example.com', false)
            ->assertSee('https://wa.me/2348030000000', false);
    }

    public function test_update_changes_status_and_saves_internal_notes(): void
    {
        $enquiry = Enquiry::factory()->create();

        $this->actingAs($this->admin)
            ->put(route('admin.enquiries.update', $enquiry), ['status' => 'follow_up', 'admin_notes' => 'Call back Friday.'])
            ->assertSessionHasNoErrors();

        $enquiry->refresh();
        $this->assertSame(EnquiryStatus::FollowUp, $enquiry->status);
        $this->assertSame('Call back Friday.', $enquiry->admin_notes);
    }

    public function test_export_downloads_filtered_csv_and_neutralises_formulas(): void
    {
        Enquiry::factory()->create(['name' => '=HYPERLINK("bad")', 'status' => EnquiryStatus::New]);
        Enquiry::factory()->status(EnquiryStatus::Closed)->create(['name' => 'Closed Person']);

        $response = $this->actingAs($this->admin)->get(route('admin.enquiries.export', ['status' => 'new']));

        $response->assertOk()->assertDownload('enquiries-'.now()->format('Y-m-d').'.csv');
        $csv = $response->streamedContent();
        $this->assertStringContainsString("'=HYPERLINK", $csv);
        $this->assertStringNotContainsString('Closed Person', $csv);
    }

    public function test_destroy_deletes_the_enquiry(): void
    {
        $enquiry = Enquiry::factory()->create();

        $this->actingAs($this->admin)->delete(route('admin.enquiries.destroy', $enquiry))->assertRedirect(route('admin.enquiries.index'));

        $this->assertModelMissing($enquiry);
    }
}
