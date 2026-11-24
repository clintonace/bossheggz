<?php

namespace App\Models;

use App\Enums\LandingPage;
use Database\Factories\PageSectionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Once;
use Illuminate\Support\Str;

/**
 * One section of a landing page. Only the fields an admin changed are stored in
 * `content`; everything else falls back to the defaults in config/page_content.php.
 * A section with no changes that is visible has no row at all.
 */
#[Fillable(['page', 'section', 'content', 'is_visible'])]
class PageSection extends Model
{
    /** @use HasFactory<PageSectionFactory> */
    use HasFactory;

    /** Folder on the public disk for images uploaded through the CMS. */
    public const ImageDirectory = 'page-content';

    protected $attributes = [
        'is_visible' => true,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'page' => LandingPage::class,
            'content' => 'array',
            'is_visible' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saved(fn () => Once::flush());
        static::deleted(fn () => Once::flush());
    }

    /**
     * Every section of a page, top to bottom, with saved changes merged over the defaults.
     *
     * @return Collection<string, self>
     */
    public static function forPage(LandingPage $page): Collection
    {
        $stored = static::stored()->get($page->value, collect());

        return collect($page->sections())->map(
            fn (array $definition, string $key) => $stored->get($key) ?? new static(['page' => $page, 'section' => $key, 'content' => []]),
        );
    }

    /**
     * All saved rows grouped by page and keyed by section, loaded once per request.
     *
     * @return Collection<string, Collection<string, self>>
     */
    protected static function stored(): Collection
    {
        return once(fn () => static::all()
            ->groupBy(fn (self $section) => $section->page->value)
            ->map(fn (Collection $sections) => $sections->keyBy('section')));
    }

    /**
     * Save an admin's edit of a section. Only values that differ from the defaults are kept,
     * and the row is removed when nothing differs, so later default changes still show.
     *
     * @param  array<string, mixed>  $values
     */
    public static function updateSection(LandingPage $page, string $key, array $values, bool $isVisible): void
    {
        $section = static::forPage($page)->get($key);
        $previousImages = $section->imagePaths();

        $overrides = collect($section->fields())
            ->filter(fn (array $field, string $name) => array_key_exists($name, $values))
            ->map(fn (array $field, string $name) => static::normalise($field, $values[$name]))
            ->reject(fn (mixed $value, string $name) => $value === static::normalise($section->fields()[$name], $section->fields()[$name]['default'] ?? null))
            ->all();

        $isVisible = $section->isHideable() ? $isVisible : true;

        if ($overrides === [] && $isVisible) {
            if ($section->exists) {
                $section->delete();
            }
        } else {
            $section->fill(['content' => $overrides, 'is_visible' => $isVisible])->save();
        }

        static::deleteUnusedImages(array_diff($previousImages, $section->exists ? $section->imagePaths() : []));
    }

    /**
     * Put a section back to its default content (and visible).
     */
    public function resetToDefault(): void
    {
        $images = $this->imagePaths();

        if ($this->exists) {
            $this->delete();
        }

        static::deleteUnusedImages($images);
    }

    /**
     * The section's definition from config/page_content.php.
     *
     * @return array{label: string, description?: string, hideable?: bool, fields: array<string, array<string, mixed>>}
     */
    public function definition(): array
    {
        return $this->page->sections()[$this->section] ?? ['label' => Str::headline($this->section), 'fields' => []];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function fields(): array
    {
        return $this->definition()['fields'];
    }

    public function label(): string
    {
        return $this->definition()['label'];
    }

    public function isHideable(): bool
    {
        return $this->definition()['hideable'] ?? true;
    }

    /**
     * Whether the section should be shown on the public page.
     */
    public function isShown(): bool
    {
        return ! $this->isHideable() || $this->is_visible;
    }

    public function isCustomised(): bool
    {
        return $this->exists && $this->content !== [] && $this->content !== null;
    }

    /**
     * The stored value of a field (or its default), exactly as the admin typed it.
     */
    public function raw(string $field): mixed
    {
        $content = $this->content ?? [];

        return array_key_exists($field, $content) ? $content[$field] : ($this->fields()[$field]['default'] ?? null);
    }

    /**
     * A field's value ready for the page, with {company}-style tokens filled in.
     */
    public function value(string $field): mixed
    {
        return static::fillTokens($this->raw($field));
    }

    /**
     * Collection helper for repeatable "items" and "lines" fields.
     *
     * @return Collection<int, mixed>
     */
    public function list(string $field): Collection
    {
        return collect($this->value($field) ?? []);
    }

    /**
     * Paragraphs of a long text field, split on blank lines.
     *
     * @return Collection<int, string>
     */
    public function paragraphs(string $field): Collection
    {
        return Str::of((string) $this->value($field))
            ->explode("\n\n")
            ->map(fn (string $paragraph) => trim($paragraph))
            ->filter()
            ->values();
    }

    /**
     * Public URL of an image field: uploads come from the public disk, links are used as-is.
     */
    public function imageUrl(string $field): ?string
    {
        return static::resolveImageUrl($this->raw($field));
    }

    public static function resolveImageUrl(?string $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        return Str::startsWith($value, ['http://', 'https://', '/']) ? $value : Storage::disk('public')->url($value);
    }

    /**
     * Uploaded image paths currently stored on this section.
     *
     * @return list<string>
     */
    public function imagePaths(): array
    {
        return collect($this->content ?? [])
            ->filter(fn (mixed $value, string $name) => ($this->fields()[$name]['type'] ?? null) === 'image')
            ->filter(fn (mixed $value) => is_string($value) && Str::startsWith($value, self::ImageDirectory.'/'))
            ->values()
            ->all();
    }

    /**
     * @param  array<int, string>  $paths
     */
    protected static function deleteUnusedImages(array $paths): void
    {
        foreach ($paths as $path) {
            Storage::disk('public')->delete($path);
        }
    }

    /**
     * Bring a submitted (or default) value into the stored shape for its field type.
     *
     * @param  array<string, mixed>  $field
     */
    protected static function normalise(array $field, mixed $value): mixed
    {
        return match ($field['type']) {
            'lines' => collect(is_array($value) ? $value : preg_split('/\R/', (string) $value))
                ->map(fn (mixed $line) => trim((string) $line))
                ->filter(fn (string $line) => $line !== '')
                ->values()
                ->all(),
            'items' => collect(is_array($value) ? $value : [])
                ->map(fn (mixed $item) => collect($field['fields'])
                    ->map(fn (array $subField, string $name) => static::normalise($subField, is_array($item) ? ($item[$name] ?? null) : null))
                    ->all())
                ->reject(fn (array $item) => collect($item)->every(fn (mixed $value, string $name) => blank($value) || ($field['fields'][$name]['type'] === 'icon')))
                ->values()
                ->all(),
            'textarea' => filled($value) ? str_replace("\r\n", "\n", trim((string) $value)) : null,
            default => filled($value) ? trim((string) $value) : null,
        };
    }

    /**
     * Replace {company}, {company_short} and {tagline} in text (recursively for lists).
     */
    public static function fillTokens(mixed $value): mixed
    {
        if (is_array($value)) {
            return array_map(static::fillTokens(...), $value);
        }

        if (! is_string($value)) {
            return $value;
        }

        $company = (string) config('listings.company.name');

        return strtr($value, [
            '{company}' => $company,
            '{company_short}' => Str::before($company, ' '),
            '{tagline}' => (string) config('listings.company.tagline'),
        ]);
    }
}
