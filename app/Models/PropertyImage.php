<?php

namespace App\Models;

use Database\Factories\PropertyImageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

#[Fillable(['path', 'sort_order'])]
class PropertyImage extends Model
{
    /** @use HasFactory<PropertyImageFactory> */
    use HasFactory;

    protected static function booted(): void
    {
        static::deleted(function (PropertyImage $image): void {
            if (! $image->isRemote()) {
                Storage::disk('public')->delete($image->path);
            }
        });
    }

    /**
     * @return BelongsTo<Property, $this>
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function isRemote(): bool
    {
        return Str::startsWith($this->path, ['http://', 'https://']);
    }

    protected function url(): Attribute
    {
        return Attribute::get(fn (): string => $this->isRemote() ? $this->path : Storage::disk('public')->url($this->path));
    }
}
