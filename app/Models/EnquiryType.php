<?php

namespace App\Models;

use Database\Factories\EnquiryTypeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable(['name', 'description', 'whatsapp_number_id', 'is_active', 'sort_order'])]
class EnquiryType extends Model
{
    /** @use HasFactory<EnquiryTypeFactory> */
    use HasFactory;

    /**
     * Built-in types the website's own forms and links rely on.
     */
    public const string General = 'general';

    public const string Property = 'property';

    public const string Investment = 'investment';

    public const string LandBanking = 'land_banking';

    public const string Consultation = 'consultation';

    public const string ListProperty = 'list_property';

    public const string ShortletBooking = 'shortlet_booking';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_system' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (EnquiryType $enquiryType): void {
            if (blank($enquiryType->slug)) {
                $enquiryType->slug = static::uniqueSlug($enquiryType->name);
            }
        });

        static::saving(function (EnquiryType $enquiryType): void {
            if ($enquiryType->is_system) {
                $enquiryType->is_active = true;
            }
        });
    }

    public static function uniqueSlug(string $name): string
    {
        $base = Str::slug($name, '_') ?: 'enquiry';
        $slug = $base;
        $suffix = 2;

        while (static::where('slug', $slug)->exists()) {
            $slug = "{$base}_{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    /**
     * @return BelongsTo<WhatsappNumber, $this>
     */
    public function whatsappNumber(): BelongsTo
    {
        return $this->belongsTo(WhatsappNumber::class);
    }

    /**
     * @return HasMany<Enquiry, $this>
     */
    public function enquiries(): HasMany
    {
        return $this->hasMany(Enquiry::class);
    }

    #[Scope]
    protected function ordered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('name');
    }

    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('is_active', true);
    }

    public static function findBySlug(?string $slug): ?self
    {
        return $slug ? static::where('slug', $slug)->first() : null;
    }

    /**
     * The WhatsApp number enquiries of this type go to (its own, or the site default).
     */
    public function whatsappDigits(): string
    {
        return $this->whatsappNumber?->is_active ? $this->whatsappNumber->number : WhatsappNumber::primaryNumber();
    }

    public function isDeletable(): bool
    {
        return ! $this->is_system && ! $this->enquiries()->exists();
    }
}
