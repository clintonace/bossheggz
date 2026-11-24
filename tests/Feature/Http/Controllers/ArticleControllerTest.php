<?php

namespace Tests\Feature\Http\Controllers;

use App\Enums\ArticleCategory;
use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticleControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_lists_only_published_articles(): void
    {
        Article::factory()->create(['title' => 'Published Guide']);
        Article::factory()->draft()->create(['title' => 'Draft Thoughts']);
        Article::factory()->scheduled()->create(['title' => 'Coming Next Week']);

        $this->get(route('insights.index'))
            ->assertOk()
            ->assertSeeText('Published Guide')
            ->assertDontSeeText('Draft Thoughts')
            ->assertDontSeeText('Coming Next Week');
    }

    public function test_index_highlights_the_featured_article_and_filters_by_category(): void
    {
        Article::factory()->featured()->create(['title' => 'Big Featured Piece', 'category' => ArticleCategory::LandBanking]);
        Article::factory()->create(['title' => 'Due Diligence Checklist', 'category' => ArticleCategory::DueDiligence]);

        $this->get(route('insights.index'))->assertSeeTextInOrder(['Big Featured Piece', 'Read Article', 'Due Diligence Checklist']);

        $this->get(route('insights.index', ['category' => 'due_diligence']))
            ->assertSeeText('Due Diligence Checklist')
            ->assertDontSeeText('Big Featured Piece');
    }

    public function test_show_renders_article_with_sub_headings(): void
    {
        $article = Article::factory()->create(['title' => 'Buying Land Safely', 'body' => "Intro paragraph.\n\n## Check the title\n\nAsk for the C of O."]);

        $this->get(route('insights.show', $article))
            ->assertOk()
            ->assertSeeText('Buying Land Safely')
            ->assertSee('<h2 class="pt-4 font-display text-2xl text-ink-950">Check the title</h2>', false);
    }

    public function test_show_returns_404_for_drafts_and_scheduled_articles(): void
    {
        $this->get(route('insights.show', Article::factory()->draft()->create()))->assertNotFound();
        $this->get(route('insights.show', Article::factory()->scheduled()->create()))->assertNotFound();
    }
}
