<?php

namespace App\Http\Requests\Admin;

use App\Enums\ArticleCategory;
use App\Models\Article;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class ArticleRequest extends FormRequest
{
    /**
     * Admin routes are already guarded by the admin middleware.
     */
    public function authorize(): bool
    {
        return (bool) $this->user()?->isAdmin();
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:180'],
            'category' => ['required', Rule::enum(ArticleCategory::class)],
            'author_name' => ['required', 'string', 'max:120'],
            'excerpt' => ['required', 'string', 'max:300'],
            'body' => ['required', 'string', 'max:60000'],
            'visibility' => ['required', Rule::in(['draft', 'published', 'scheduled'])],
            'publish_at' => ['exclude_unless:visibility,scheduled', 'required', 'date', 'after:now'],
            'is_featured' => ['boolean'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'remove_image' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'publish_at.required' => 'Choose when the article should go live.',
            'publish_at.after' => 'A scheduled date must be in the future.',
            'image.max' => 'The image must be 5MB or smaller.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_featured' => $this->boolean('is_featured'),
            'remove_image' => $this->boolean('remove_image'),
        ]);
    }

    /**
     * Attributes ready to be saved on the article.
     *
     * @return array<string, mixed>
     */
    public function articleAttributes(?Article $article = null): array
    {
        return [
            ...$this->safe()->only(['title', 'category', 'author_name', 'excerpt', 'body', 'is_featured']),
            'published_at' => match ($this->validated('visibility')) {
                'draft' => null,
                'scheduled' => Carbon::parse($this->validated('publish_at')),
                default => $article?->isPublished() ? $article->published_at : now(),
            },
        ];
    }
}
