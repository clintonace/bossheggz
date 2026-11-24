<?php

namespace App\Models;

use Database\Factories\WhatsappNumberFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Once;

#[Fillable(['label', 'number', 'is_default', 'is_active', 'sort_order'])]
class WhatsappNumber extends Model
{
    /** @use HasFactory<WhatsappNumberFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (WhatsappNumber $whatsappNumber): void {
            $whatsappNumber->number = static::digits($whatsappNumber->number);

            if (! $whatsappNumber->is_active) {
                $whatsappNumber->is_default = false;
            }
        });

        static::saved(function (WhatsappNumber $whatsappNumber): void {
            if ($whatsappNumber->is_default) {
                static::whereKeyNot($whatsappNumber->id)->where('is_default', true)->update(['is_default' => false]);
            }

            Once::flush();
        });

        static::deleted(fn () => Once::flush());
    }

    /**
     * Keep only the digits of a phone number (international format, no "+"),
     * dropping a "(0)" trunk prefix such as "+234 (0) 803…".
     */
    public static function digits(?string $number): string
    {
        return preg_replace('/\D+/', '', str_replace('(0)', '', (string) $number));
    }

    /**
     * @return HasMany<EnquiryType, $this>
     */
    public function enquiryTypes(): HasMany
    {
        return $this->hasMany(EnquiryType::class);
    }

    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('is_active', true)->orderByDesc('is_default')->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Active numbers for the public site, default first (loaded once per request).
     *
     * @return Collection<int, self>
     */
    public static function options(): Collection
    {
        return once(fn () => static::query()->active()->get());
    }

    /**
     * The number used by WhatsApp buttons when no other number applies.
     */
    public static function primaryNumber(): string
    {
        return static::options()->first()?->number ?? static::digits(config('listings.company.whatsapp'));
    }

    /**
     * A wa.me link to a number, optionally with a pre-filled message.
     */
    public static function linkTo(string $number, ?string $text = null): string
    {
        return 'https://wa.me/'.static::digits($number).($text ? '?text='.rawurlencode($text) : '');
    }

    public function link(?string $text = null): string
    {
        return static::linkTo($this->number, $text);
    }

    /**
     * The number as shown to visitors, e.g. "+2348111708068".
     */
    protected function display(): Attribute
    {
        return Attribute::get(fn (): string => '+'.$this->number);
    }
}
