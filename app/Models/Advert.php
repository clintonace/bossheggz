<?php

namespace App\Models;

use App\Enums\AdvertFormat;
use App\Enums\AdvertPage;
use App\Enums\AdvertPosition;
use App\Enums\AdvertWidth;
use Database\Factories\AdvertFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

#[Fillable([
    'title', 'sponsor', 'body', 'cta_label', 'link_url', 'is_active', 'starts_at', 'ends_at',
    'format', 'page', 'section', 'position', 'width', 'display_seconds', 'sort_order',
])]
class Advert extends Model
{
    /** @use HasFactory<AdvertFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'format' => AdvertFormat::class,
            'position' => AdvertPosition::class,
            'width' => AdvertWidth::class,
            'is_active' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'clicks' => 'integer',
            'display_seconds' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::deleted(fn (Advert $advert) => $advert->deleteStoredImage());
    }

    /**
     * Adverts that should currently be shown on the site.
     */
    #[Scope]
    protected function live(Builder $query): void
    {
        $query->where('is_active', true)
            ->where(fn (Builder $query) => $query->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn (Builder $query) => $query->whereNull('ends_at')->orWhere('ends_at', '>', now()));
    }

    #[Scope]
    protected function ordered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Live in-page adverts for a page, grouped by slot key ("section:position").
     *
     * @return Collection<string, Collection<int, self>>
     */
    public static function slotsFor(AdvertPage $page): Collection
    {
        return static::query()->live()->ordered()
            ->where('format', AdvertFormat::Inline)
            ->where('page', $page->value)
            ->get()
            ->groupBy(fn (self $advert) => $advert->slotKey());
    }

    /**
     * A live pop-up for the page (or one targeting every page), chosen at random.
     */
    public static function popupFor(?AdvertPage $page): ?self
    {
        if (! $page) {
            return null;
        }

        return static::query()->live()
            ->where('format', AdvertFormat::Popup)
            ->whereIn('page', ['all', $page->value])
            ->inRandomOrder()
            ->first();
    }

    public function slotKey(): string
    {
        return "{$this->section}:{$this->position?->value}";
    }

    public function isPopup(): bool
    {
        return $this->format === AdvertFormat::Popup;
    }

    public function pageEnum(): ?AdvertPage
    {
        return AdvertPage::tryFrom((string) $this->page);
    }

    /**
     * Human readable placement, e.g. "Home page · After Latest on the market · Half width".
     */
    public function locationLabel(): string
    {
        if ($this->isPopup()) {
            return 'Pop-up · '.(AdvertPage::popupTargets()[$this->page] ?? 'All public pages')." · {$this->display_seconds}s";
        }

        $page = $this->pageEnum();
        $where = $this->position === AdvertPosition::Inside ? 'Inside' : $this->position?->label();
        $parts = [$page?->label(), $where.' '.$page?->sectionLabel($this->section)];

        if ($this->position !== AdvertPosition::Inside) {
            $parts[] = $this->width === AdvertWidth::Half ? 'Half width' : 'Full width';
        }

        return implode(' · ', array_filter($parts));
    }

    /**
     * One of: live, scheduled, expired, paused.
     */
    public function state(): string
    {
        return match (true) {
            ! $this->is_active => 'paused',
            $this->starts_at?->isFuture() ?? false => 'scheduled',
            $this->ends_at?->isPast() ?? false => 'expired',
            default => 'live',
        };
    }

    public function stateBadgeClasses(): string
    {
        return match ($this->state()) {
            'live' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
            'scheduled' => 'bg-sky-50 text-sky-700 ring-sky-200',
            'expired' => 'bg-rose-50 text-rose-700 ring-rose-200',
            default => 'bg-ink-50 text-ink-500 ring-ink-200',
        };
    }

    public function hasRemoteImage(): bool
    {
        return Str::startsWith((string) $this->image_path, ['http://', 'https://']);
    }

    public function deleteStoredImage(): void
    {
        if ($this->image_path && ! $this->hasRemoteImage()) {
            Storage::disk('public')->delete($this->image_path);
        }
    }

    protected function imageUrl(): Attribute
    {
        return Attribute::get(fn (): ?string => match (true) {
            ! $this->image_path => null,
            $this->hasRemoteImage() => $this->image_path,
            default => Storage::disk('public')->url($this->image_path),
        });
    }

    /**
     * Host shown under the call to action, e.g. "example.com".
     */
    protected function displayDomain(): Attribute
    {
        return Attribute::get(fn (): string => Str::after((string) parse_url($this->link_url, PHP_URL_HOST), 'www.'));
    }
}
