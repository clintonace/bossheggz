<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticleControllerTest extends TestCase
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
            'title' => 'Five checks before buying land',
            'category' => 'due_diligence',
            'author_name' => 'Advisory Team',
            'excerpt' => 'What to check first.',
            'body' => "Paragraph one.\n\n## Heading\n\nParagraph two.",
            'visibility' => 'published',
            ...$overrides,
        ];
    }

    public function test_non_admins_cannot_manage_articles(): void
    {
        $this->actingAs(User::factory()->create())->get(route('admin.articles.index'))->assertForbidden();
    }

    public function test_index_lists_drafts_scheduled_and_published_articles(): void
    {
        Article::factory()->draft()->create(['title' => 'A Draft']);
        Article::factory()->scheduled()->create(['title' => 'A Scheduled One']);

        $this->actingAs($this->admin)->get(route('admin.articles.index'))
            ->assertOk()
            ->assertSeeText('A Draft')
            ->assertSeeText('A Scheduled One')
            ->assertSeeText('Scheduled');
    }

    public function test_store_publishes_immediately(): void
    {
        $this->actingAs($this->admin)->post(route('admin.articles.store'), $this->validPayload())->assertSessionHasNoErrors();

        $article = Article::sole();
        $this->assertTrue($article->isPublished());
        $this->assertSame('five-checks-before-buying-land', $article->slug);
    }

    public function test_store_can_schedule_or_save_a_draft(): void
    {
        $this->actingAs($this->admin)->post(route('admin.articles.store'), $this->validPayload(['visibility' => 'scheduled', 'publish_at' => now()->addDays(3)->format('Y-m-d\TH:i')]))->assertSessionHasNoErrors();
        $this->actingAs($this->admin)->post(route('admin.articles.store'), $this->validPayload(['title' => 'Draft piece', 'visibility' => 'draft']))->assertSessionHasNoErrors();

        $this->assertTrue(Article::where('slug', 'five-checks-before-buying-land')->sole()->isScheduled());
        $this->assertNull(Article::where('slug', 'draft-piece')->sole()->published_at);
    }

    public function test_scheduling_needs_a_future_date(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.articles.store'), $this->validPayload(['visibility' => 'scheduled', 'publish_at' => now()->subDay()->format('Y-m-d\TH:i')]))
            ->assertSessionHasErrors('publish_at');
    }

    public function test_update_keeps_the_original_publication_date(): void
    {
        $article = Article::factory()->create(['published_at' => now()->subMonth()]);
        $originalDate = $article->published_at->toDateTimeString();

        $this->actingAs($this->admin)->put(route('admin.articles.update', $article), $this->validPayload(['title' => 'New title']))->assertSessionHasNoErrors();

        $article->refresh();
        $this->assertSame('New title', $article->title);
        $this->assertSame($originalDate, $article->published_at->toDateTimeString());
    }

    public function test_destroy_deletes_the_article(): void
    {
        $article = Article::factory()->create();

        $this->actingAs($this->admin)->delete(route('admin.articles.destroy', $article))->assertRedirect(route('admin.articles.index'));

        $this->assertModelMissing($article);
    }
}
