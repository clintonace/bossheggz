<?php

namespace App\Models;

use App\Enums\EngagementStatus;
use Database\Factories\EngagementFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['property_id', 'user_id', 'name', 'email', 'phone', 'message', 'status'])]
class Engagement extends Model
{
    /** @use HasFactory<EngagementFactory> */
    use HasFactory;

    /**
     * The model's default values for attributes.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => 'new',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => EngagementStatus::class,
        ];
    }

    /**
     * @return BelongsTo<Property, $this>
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<EngagementUpdate, $this>
     */
    public function updates(): HasMany
    {
        return $this->hasMany(EngagementUpdate::class)->latest()->latest('id');
    }

    #[Scope]
    protected function open(Builder $query): void
    {
        $query->whereIn('status', array_map(fn (EngagementStatus $status) => $status->value, EngagementStatus::openCases()));
    }

    public function isGuest(): bool
    {
        return $this->user_id === null;
    }
}
