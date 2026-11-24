<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ArticleCategory;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ArticleRequest;
use App\Models\Article;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ArticleController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();

        $articles = Article::query()
            ->when(ArticleCategory::tryFrom($request->string('category')->toString()), fn ($query, ArticleCategory $category) => $query->where('category', $category))
            ->when($status === 'draft', fn ($query) => $query->whereNull('published_at'))
            ->when($status === 'scheduled', fn ($query) => $query->where('published_at', '>', now()))
            ->when($status === 'published', fn ($query) => $query->published())
            ->when($request->string('q')->trim()->toString(), fn ($query, string $term) => $query->where(
                fn ($query) => $query->where('title', 'like', "%{$term}%")->orWhere('author_name', 'like', "%{$term}%")
            ))
            ->orderByRaw('published_at is null desc')
            ->latest('published_at')
            ->paginate(12)
            ->withQueryString();

        return view('admin.articles.index', ['articles' => $articles]);
    }

    public function create(Request $request): View
    {
        return view('admin.articles.create', ['article' => new Article([
            'author_name' => $request->user()->name,
            'category' => ArticleCategory::RealEstateInvestment,
        ])]);
    }

    public function store(ArticleRequest $request): RedirectResponse
    {
        $article = new Article($request->articleAttributes());

        if ($request->hasFile('image')) {
            $article->image_path = $request->file('image')->store('articles', 'public');
        }

        $article->save();

        return to_route('admin.articles.edit', $article)->with('success', match ($article->statusLabel()) {
            'Published' => 'Article published.',
            'Scheduled' => 'Article scheduled for '.$article->published_at->format('M j, Y g:ia').'.',
            default => 'Draft saved.',
        });
    }

    public function edit(Article $article): View
    {
        return view('admin.articles.edit', ['article' => $article]);
    }

    public function update(ArticleRequest $request, Article $article): RedirectResponse
    {
        $article->fill($request->articleAttributes($article));

        if ($request->hasFile('image') || $request->validated('remove_image')) {
            if ($article->image_path && ! $article->hasRemoteImage()) {
                Storage::disk('public')->delete($article->image_path);
            }

            $article->image_path = $request->hasFile('image') ? $request->file('image')->store('articles', 'public') : null;
        }

        $article->save();

        return back()->with('success', 'Article saved.');
    }

    public function destroy(Article $article): RedirectResponse
    {
        $article->delete();

        return to_route('admin.articles.index')->with('success', 'Article deleted.');
    }
}
