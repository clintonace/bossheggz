<?php

namespace App\Models;

use App\Enums\InvestmentCategory;
use App\Enums\LandUse;
use App\Enums\OpportunityStatus;
use App\Enums\VerificationStatus;
use Database\Factories\InvestmentOpportunityFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\RouteKey;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

#[Fillable([
    'title', 'category', 'status', 'location', 'state', 'summary', 'description',
    'required_investment', 'minimum_participation', 'timeline', 'structure', 'risks',
    'documentation', 'verification_status', 'image_path', 'is_featured',
    'land_use', 'land_sizes', 'land_size_sqm', 'acquisition_price', 'access', 'estate_status', 'rationale',
])]
#[RouteKey('slug')]
class InvestmentOpportunity extends Model
{
    /** @use HasFactory<InvestmentOpportunityFactory> */
    use HasFactory;

    /**
     * The model's default values for attributes.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => 'draft',
        'verification_status' => 'pending',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'category' => InvestmentCategory::class,
            'status' => OpportunityStatus::class,
            'verification_status' => VerificationStatus::class,
            'land_use' => LandUse::class,
            'required_investment' => 'integer',
            'minimum_participation' => 'integer',
            'acquisition_price' => 'integer',
            'land_size_sqm' => 'integer',
            'is_featured' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (InvestmentOpportunity $opportunity): void {
            if (blank($opportunity->slug)) {
                $opportunity->slug = static::uniqueSlug($opportunity->title);
            }
        });

        static::saving(function (InvestmentOpportunity $opportunity): void {
            if ($opportunity->status?->isPublic() && $opportunity->published_at === null) {
                $opportunity->published_at = now();
            }
        });

        static::deleted(function (InvestmentOpportunity $opportunity): void {
            if ($opportunity->image_path && ! $opportunity->hasRemoteImage()) {
                Storage::disk('public')->delete($opportunity->image_path);
            }
        });
    }

    public static function uniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'opportunity';
        $slug = $base;
        $suffix = 2;

        while (static::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    /**
     * @return MorphMany<Enquiry, $this>
     */
    public function enquiries(): MorphMany
    {
        return $this->morphMany(Enquiry::class, 'enquirable');
    }

    /**
     * Only opportunities visible to the public.
     */
    #[Scope]
    protected function visible(Builder $query): void
    {
        $query->where('status', '!=', OpportunityStatus::Draft);
    }

    /**
     * Land banking opportunities (shown on the Land Banking page).
     */
    #[Scope]
    protected function land(Builder $query): void
    {
        $query->where('category', InvestmentCategory::LandBanking);
    }

    /**
     * Apply the public land banking filters.
     *
     * @param  array{state?: ?string, location?: ?string, size?: ?string, price?: ?string}  $filters
     */
    #[Scope]
    protected function filterLand(Builder $query, array $filters): void
    {
        $query
            ->when($filters['state'] ?? null, fn (Builder $query, string $state) => $query->where('state', $state))
            ->when($filters['location'] ?? null, fn (Builder $query, string $location) => $query->where(
                fn (Builder $query) => $query->where('location', 'like', "%{$location}%")->orWhere('state', 'like', "%{$location}%")
            ))
            ->when(static::landSizeRanges()[$filters['size'] ?? ''] ?? null, function (Builder $query, array $range): void {
                $query->where('land_size_sqm', '>=', $range['min'])->when($range['max'], fn (Builder $query, int $max) => $query->where('land_size_sqm', '<=', $max));
            })
            ->when(static::priceRanges()[$filters['price'] ?? ''] ?? null, function (Builder $query, array $range): void {
                $query->where('acquisition_price', '>=', $range['min'])->when($range['max'], fn (Builder $query, int $max) => $query->where('acquisition_price', '<=', $max));
            });
    }

    /**
     * Land size bands for the land banking filter (smallest plot, m²).
     *
     * @return array<string, array{label: string, min: int, max: ?int}>
     */
    public static function landSizeRanges(): array
    {
        return [
            'up-to-500' => ['label' => 'Up to 500 m²', 'min' => 0, 'max' => 500],
            '500-1000' => ['label' => '500 – 1,000 m²', 'min' => 501, 'max' => 1000],
            '1000-5000' => ['label' => '1,000 – 5,000 m²', 'min' => 1001, 'max' => 5000],
            'over-5000' => ['label' => 'Over 5,000 m² (incl. hectares)', 'min' => 5001, 'max' => null],
        ];
    }

    /**
     * Acquisition price bands for the land banking filter.
     *
     * @return array<string, array{label: string, min: int, max: ?int}>
     */
    public static function priceRanges(): array
    {
        $currency = config('listings.currency');

        return [
            'under-5m' => ['label' => "Under {$currency}5m", 'min' => 0, 'max' => 4_999_999],
            '5m-20m' => ['label' => "{$currency}5m – {$currency}20m", 'min' => 5_000_000, 'max' => 20_000_000],
            '20m-50m' => ['label' => "{$currency}20m – {$currency}50m", 'min' => 20_000_001, 'max' => 50_000_000],
            'over-50m' => ['label' => "Over {$currency}50m", 'min' => 50_000_001, 'max' => null],
        ];
    }

    public function isLand(): bool
    {
        return $this->category === InvestmentCategory::LandBanking;
    }

    public function acceptsEnquiries(): bool
    {
        return $this->status === OpportunityStatus::Open;
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
     * Format an amount with the configured currency, or null when not set.
     */
    public static function money(?int $amount): ?string
    {
        return $amount === null ? null : config('listings.currency').number_format($amount);
    }

    protected function fullLocation(): Attribute
    {
        return Attribute::get(fn (): string => collect([$this->location, $this->state])->filter()->unique()->implode(', '));
    }

    public function url(): string
    {
        return $this->isLand() ? route('land-banking.show', $this) : route('investments.show', $this);
    }
}
