<?php

namespace App\Http\Controllers;

use App\Enums\ArticleCategory;
use App\Enums\LandingPage;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ArticleController extends Controller
{
    public function index(Request $request): View
    {
        $category = ArticleCategory::tryFrom($request->string('category')->toString());

        $featured = $category || $request->integer('page', 1) > 1
            ? null
            : Article::published()->where('is_featured', true)->latest('published_at')->first();

        $articles = Article::published()
            ->when($category, fn ($query, ArticleCategory $category) => $query->where('category', $category))
            ->when($featured, fn ($query, Article $featured) => $query->whereKeyNot($featured->id))
            ->latest('published_at')
            ->paginate(9)
            ->withQueryString();

        return view('insights.index', [
            'content' => LandingPage::Insights->content(),
            'featured' => $featured,
            'articles' => $articles,
            'category' => $category,
        ]);
    }

    public function show(Article $article): View
    {
        abort_unless($article->isPublished(), 404);

        return view('insights.show', [
            'article' => $article,
            'related' => Article::published()
                ->whereKeyNot($article->id)
                ->orderByRaw('category = ? desc', [$article->category->value])
                ->latest('published_at')
                ->take(3)
                ->get(),
        ]);
    }
}
