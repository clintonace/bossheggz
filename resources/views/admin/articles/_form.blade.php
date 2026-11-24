@php
    $visibility = old('visibility', match (true) {
        $article->isScheduled() => 'scheduled',
        $article->isPublished() => 'published',
        default => $article->exists ? 'draft' : 'published',
    });
@endphp

<div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
    <div class="space-y-6 xl:col-span-2">
        <section class="card p-6 sm:p-8">
            <div class="space-y-5">
                <x-field name="title" label="Title" :value="$article->title" required placeholder="e.g. Five checks before you buy land in Lagos" />
                <x-field name="excerpt" label="Summary" textarea rows="2" :value="$article->excerpt" required maxlength="300" hint="Shown on article cards and at the top of the article (max 300 characters)." />
                <x-field name="body" label="Article" textarea rows="18" :value="$article->body" required hint="Leave a blank line between paragraphs. Start a line with “## ” for a sub-heading." />
            </div>
        </section>
    </div>

    <div class="space-y-6">
        <section class="card p-6 xl:sticky xl:top-24">
            <h2 class="font-display text-xl text-ink-900">Publishing</h2>
            <div class="mt-6 space-y-5">
                <div>
                    <span class="field-label">Visibility</span>
                    <div class="grid grid-cols-3 gap-2">
                        @foreach (['draft' => 'Draft', 'published' => 'Publish', 'scheduled' => 'Schedule'] as $value => $label)
                            <label class="cursor-pointer">
                                <input type="radio" name="visibility" value="{{ $value }}" class="peer sr-only" @checked($visibility === $value)>
                                <span class="block rounded-xl bg-white px-3 py-2.5 text-center text-sm font-medium text-ink-600 ring-1 ring-ink-200 transition peer-checked:bg-ink-900 peer-checked:text-white peer-checked:ring-ink-900">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('visibility') <p class="field-error">{{ $message }}</p> @enderror
                </div>
                <div data-show-for="visibility" data-show-values="scheduled">
                    <x-field name="publish_at" label="Go live on" type="datetime-local" :value="$article->isScheduled() ? $article->published_at->format('Y-m-d\TH:i') : null" />
                </div>
                @if ($article->isPublished())
                    <p class="text-xs text-ink-400">Published {{ $article->published_at->format('M j, Y g:ia') }}</p>
                @endif

                <div>
                    <label for="category" class="field-label">Category</label>
                    <select name="category" id="category" class="field-input appearance-none">
                        @foreach (\App\Enums\ArticleCategory::cases() as $category)
                            <option value="{{ $category->value }}" @selected(old('category', $article->category?->value) === $category->value)>{{ $category->label() }}</option>
                        @endforeach
                    </select>
                    @error('category') <p class="field-error">{{ $message }}</p> @enderror
                </div>
                <x-field name="author_name" label="Author" :value="$article->author_name" required />

                <label class="flex cursor-pointer items-start gap-3 rounded-2xl bg-ivory p-4 ring-1 ring-ink-100">
                    <input type="hidden" name="is_featured" value="0">
                    <input type="checkbox" name="is_featured" value="1" class="mt-0.5 size-4 accent-brass-500" @checked(old('is_featured', $article->is_featured))>
                    <span>
                        <span class="block text-sm font-semibold text-ink-900">Featured article</span>
                        <span class="block text-xs text-ink-400">Shown large at the top of the Insights page.</span>
                    </span>
                </label>

                <div>
                    <span class="field-label">Featured image</span>
                    @if ($article->image_url)
                        <figure class="relative aspect-[16/10] overflow-hidden rounded-2xl bg-ink-100 ring-1 ring-ink-100">
                            <img src="{{ $article->image_url }}" alt="" class="size-full object-cover">
                        </figure>
                        <label class="mt-2 flex items-center gap-2 text-xs text-ink-500">
                            <input type="checkbox" name="remove_image" value="1" class="size-4 accent-rose-600"> Remove image
                        </label>
                    @endif
                    <label class="mt-3 flex cursor-pointer items-center justify-center gap-2 rounded-2xl border-2 border-dashed border-ink-200 bg-ivory/60 px-4 py-5 text-sm font-semibold text-ink-700 transition hover:border-brass-300">
                        <x-icon name="upload" class="size-4 text-brass-500" /> {{ $article->image_url ? 'Replace image' : 'Upload image' }}
                        <input type="file" name="image" accept="image/jpeg,image/png,image/webp" class="sr-only" data-image-preview="#article-image-preview">
                    </label>
                    <div id="article-image-preview" hidden class="mt-3 grid grid-cols-1"></div>
                    @error('image') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <button class="btn btn-primary btn-lg w-full" data-loading-text="Saving…"><x-icon name="check" class="size-4" /> Save article</button>
            </div>
        </section>
    </div>
</div>
