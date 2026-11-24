<?php

namespace App\Http\Requests\Admin;

use App\Enums\ListingType;
use App\Enums\PropertyStatus;
use App\Enums\PropertyType;
use App\Enums\RentPeriod;
use App\Enums\VerificationStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PropertyRequest extends FormRequest
{
    /**
     * Admin routes are already guarded by the admin middleware.
     */
    public function authorize(): bool
    {
        return (bool) $this->user()?->isAdmin();
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:160'],
            'listing_type' => ['required', Rule::enum(ListingType::class)],
            'property_type' => ['required', Rule::enum(PropertyType::class)],
            'status' => ['required', Rule::enum(PropertyStatus::class)],
            'price' => ['required', 'integer', 'min:0'],
            'verification_status' => ['nullable', Rule::enum(VerificationStatus::class)],
            'rent_period' => ['nullable', Rule::requiredIf(fn (): bool => in_array($this->input('listing_type'), ListingType::periodicValues(), true)), Rule::enum(RentPeriod::class)],
            'address' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'max:120'],
            'bedrooms' => ['nullable', 'integer', 'min:0', 'max:50'],
            'bathrooms' => ['nullable', 'integer', 'min:0', 'max:50'],
            'area_sqm' => ['nullable', 'integer', 'min:0'],
            'description' => ['required', 'string', 'max:10000'],
            'features_text' => ['nullable', 'string', 'max:2000'],
            'is_featured' => ['boolean'],
            'images' => ['nullable', 'array', 'max:12'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'rent_period.required' => 'Choose how often rent is paid.',
            'images.*.max' => 'Each image must be 5MB or smaller.',
            'images.*.image' => 'Only image files can be uploaded.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'price' => is_string($this->price) ? preg_replace('/[^\d]/', '', $this->price) : $this->price,
            'is_featured' => $this->boolean('is_featured'),
        ]);
    }

    /**
     * Attributes ready to be saved on the property.
     *
     * @return array<string, mixed>
     */
    public function propertyAttributes(): array
    {
        return [
            ...$this->safe()->except(['images', 'features_text']),
            'features' => $this->features(),
        ];
    }

    /**
     * Features entered one per line (or comma separated).
     *
     * @return array<int, string>
     */
    public function features(): array
    {
        return collect(preg_split('/[\r\n,]+/', (string) $this->input('features_text')))
            ->map(fn (string $feature): string => trim($feature))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
