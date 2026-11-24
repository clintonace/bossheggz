<?php

namespace App\Models;

use App\Enums\ArticleCategory;
use Database\Factories\ArticleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\RouteKey;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

#[Fillable(['title', 'category', 'author_name', 'excerpt', 'body', 'image_path', 'is_featured', 'published_at'])]
#[RouteKey('slug')]
class Article extends Model
{
    /** @use HasFactory<ArticleFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'category' => ArticleCategory::class,
            'is_featured' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Article $article): void {
            if (blank($article->slug)) {
                $article->slug = static::uniqueSlug($article->title);
            }
        });

        static::deleted(function (Article $article): void {
            if ($article->image_path && ! $article->hasRemoteImage()) {
                Storage::disk('public')->delete($article->image_path);
            }
        });
    }

    public static function uniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'article';
        $slug = $base;
        $suffix = 2;

        while (static::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    /**
     * Articles whose publication date has arrived.
     */
    #[Scope]
    protected function published(Builder $query): void
    {
        $query->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    public function isPublished(): bool
    {
        return $this->published_at !== null && $this->published_at->isPast();
    }

    public function isScheduled(): bool
    {
        return $this->published_at !== null && $this->published_at->isFuture();
    }

    public function statusLabel(): string
    {
        return match (true) {
            $this->isScheduled() => 'Scheduled',
            $this->isPublished() => 'Published',
            default => 'Draft',
        };
    }

    public function hasRemoteImage(): bool
    {
        return Str::startsWith((string) $this->image_path, ['http://', 'https://']);
    }

    protected function imageUrl(): Attribute
    {
        return Attribute::get(fn (): ?string => match (true) {
            blank($this->image_path) => null,
            $this->hasRemoteImage() => $this->image_path,
            default => Storage::disk('public')->url($this->image_path),
        });
    }

    /**
     * Estimated reading time in minutes.
     */
    protected function readingMinutes(): Attribute
    {
        return Attribute::get(fn (): int => max(1, (int) ceil(str_word_count(strip_tags($this->body)) / 220)));
    }
}
