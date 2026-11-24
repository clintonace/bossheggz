<?php

namespace App\Models;

use App\Enums\SiteArea;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'phone', 'password', 'theme_mode'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The model's default values for attributes.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'is_admin' => false,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'completed_tours' => 'array',
        ];
    }

    /**
     * @return HasMany<Engagement, $this>
     */
    public function engagements(): HasMany
    {
        return $this->hasMany(Engagement::class);
    }

    /**
     * Enquiries the user sent while signed in (Speak to an Adviser, land banking, investments…).
     *
     * @return HasMany<Enquiry, $this>
     */
    public function enquiries(): HasMany
    {
        return $this->hasMany(Enquiry::class);
    }

    public function isAdmin(): bool
    {
        return (bool) $this->is_admin;
    }

    public function hasSeenTour(SiteArea $area): bool
    {
        return array_key_exists($area->value, $this->completed_tours ?? []);
    }

    /**
     * Remember that the user finished (or skipped) an area's tour, so it doesn't start again.
     */
    public function markTourSeen(SiteArea $area): void
    {
        $this->forceFill(['completed_tours' => [...($this->completed_tours ?? []), $area->value => now()->toIso8601String()]])->save();
    }
}
