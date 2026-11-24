<?php

namespace App\Models;

use App\Enums\EnquiryStatus;
use Database\Factories\EnquiryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable(['user_id', 'enquiry_type_id', 'status', 'name', 'email', 'phone', 'interest', 'message', 'admin_notes'])]
class Enquiry extends Model
{
    /** @use HasFactory<EnquiryFactory> */
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
            'status' => EnquiryStatus::class,
        ];
    }

    /**
     * The opportunity or property the enquiry is about, if any.
     *
     * @return MorphTo<Model, $this>
     */
    public function enquirable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return BelongsTo<EnquiryType, $this>
     */
    public function enquiryType(): BelongsTo
    {
        return $this->belongsTo(EnquiryType::class);
    }

    public function typeLabel(): string
    {
        return $this->enquiryType?->name ?? 'Enquiry';
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * What the enquirer is interested in, for display.
     */
    public function subject(): string
    {
        return collect([$this->enquirable?->title, $this->interest])->filter()->implode(' — ') ?: $this->typeLabel();
    }
}
