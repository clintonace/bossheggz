<?php

namespace App\Models;

use App\Enums\ListingType;
use App\Enums\PropertyStatus;
use App\Enums\PropertyType;
use App\Enums\RentPeriod;
use Database\Factories\PropertyFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\RouteKey;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

#[Fillable([
    'title', 'listing_type', 'property_type', 'status', 'price', 'rent_period',
    'address', 'city', 'state', 'bedrooms', 'bathrooms', 'area_sqm',
    'description', 'features', 'is_featured', 'published_at',
])]
#[RouteKey('slug')]
class Property extends Model
{
    /** @use HasFactory<PropertyFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'listing_type' => ListingType::class,
            'property_type' => PropertyType::class,
            'status' => PropertyStatus::class,
            'rent_period' => RentPeriod::class,
            'features' => 'array',
            'is_featured' => 'boolean',
            'published_at' => 'datetime',
            'price' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Property $property): void {
            if (blank($property->slug)) {
                $property->slug = static::uniqueSlug($property->title);
            }
        });

        static::saving(function (Property $property): void {
            if ($property->status?->isPublic() && $property->published_at === null) {
                $property->published_at = now();
            }

            if ($property->listing_type === ListingType::Sale) {
                $property->rent_period = null;
            }
        });
    }

    public static function uniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'property';
        $slug = $base;
        $suffix = 2;

        while (static::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    /**
     * @return HasMany<PropertyImage, $this>
     */
    public function images(): HasMany
    {
        return $this->hasMany(PropertyImage::class)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * @return HasOne<PropertyImage, $this>
     */
    public function coverImage(): HasOne
    {
        return $this->hasOne(PropertyImage::class)->ofMany(['sort_order' => 'min', 'id' => 'min']);
    }

    /**
     * @return HasMany<Engagement, $this>
     */
    public function engagements(): HasMany
    {
        return $this->hasMany(Engagement::class);
    }

    /**
     * Only properties visible to the public.
     */
    #[Scope]
    protected function visible(Builder $query): void
    {
        $query->whereIn('status', array_map(fn (PropertyStatus $status) => $status->value, PropertyStatus::publicCases()));
    }

    /**
     * Apply the public search filters.
     *
     * @param  array{q?: ?string, listing?: ?string, type?: ?string, city?: ?string, min_price?: mixed, max_price?: mixed, beds?: mixed}  $filters
     */
    #[Scope]
    protected function filter(Builder $query, array $filters): void
    {
        $query
            ->when($filters['q'] ?? null, function (Builder $query, string $term): void {
                $query->where(function (Builder $query) use ($term): void {
                    $query->where('title', 'like', "%{$term}%")
                        ->orWhere('address', 'like', "%{$term}%")
                        ->orWhere('city', 'like', "%{$term}%")
                        ->orWhere('state', 'like', "%{$term}%");
                });
            })
            ->when(ListingType::tryFrom((string) ($filters['listing'] ?? '')), fn (Builder $query, ListingType $type) => $query->where('listing_type', $type))
            ->when(PropertyType::tryFrom((string) ($filters['type'] ?? '')), fn (Builder $query, PropertyType $type) => $query->where('property_type', $type))
            ->when($filters['city'] ?? null, fn (Builder $query, string $city) => $query->where('city', $city))
            ->when(is_numeric($filters['min_price'] ?? null), fn (Builder $query) => $query->where('price', '>=', (int) $filters['min_price']))
            ->when(is_numeric($filters['max_price'] ?? null), fn (Builder $query) => $query->where('price', '<=', (int) $filters['max_price']))
            ->when(is_numeric($filters['beds'] ?? null), fn (Builder $query) => $query->where('bedrooms', '>=', (int) $filters['beds']));
    }

    /**
     * Price formatted with the configured currency, e.g. "₦250,000 /yr".
     */
    protected function formattedPrice(): Attribute
    {
        return Attribute::get(function (): string {
            $price = config('listings.currency').number_format($this->price);

            return $this->listing_type === ListingType::Rent && $this->rent_period
                ? "{$price} {$this->rent_period->shortLabel()}"
                : $price;
        });
    }

    protected function location(): Attribute
    {
        return Attribute::get(fn (): string => collect([$this->city, $this->state])->filter()->implode(', '));
    }

    protected function coverUrl(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->coverImage?->url);
    }

    public function acceptsEngagements(): bool
    {
        return $this->status->acceptsEngagements();
    }
}
