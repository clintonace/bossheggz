<?php

namespace App\Http\Requests\Admin;

use App\Enums\InvestmentCategory;
use App\Enums\LandUse;
use App\Enums\OpportunityStatus;
use App\Enums\VerificationStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InvestmentOpportunityRequest extends FormRequest
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
            'category' => ['required', Rule::enum(InvestmentCategory::class)],
            'status' => ['required', Rule::enum(OpportunityStatus::class)],
            'location' => ['required', 'string', 'max:160'],
            'state' => ['nullable', 'string', 'max:120'],
            'summary' => ['required', 'string', 'max:300'],
            'description' => ['required', 'string', 'max:10000'],
            'required_investment' => ['nullable', 'integer', 'min:0'],
            'minimum_participation' => ['nullable', 'integer', 'min:0'],
            'timeline' => ['nullable', 'string', 'max:120'],
            'structure' => ['nullable', 'string', 'max:5000'],
            'risks' => ['nullable', 'string', 'max:5000'],
            'documentation' => ['nullable', 'string', 'max:160'],
            'verification_status' => ['required', Rule::enum(VerificationStatus::class)],
            'is_featured' => ['boolean'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'remove_image' => ['boolean'],
            'land_use' => ['nullable', Rule::enum(LandUse::class)],
            'land_sizes' => ['nullable', 'string', 'max:160'],
            'land_size_sqm' => ['nullable', 'integer', 'min:0'],
            'acquisition_price' => ['nullable', 'integer', 'min:0'],
            'access' => ['nullable', 'string', 'max:160'],
            'estate_status' => ['nullable', 'string', 'max:160'],
            'rationale' => ['nullable', 'string', 'max:300'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'image.max' => 'The image must be 5MB or smaller.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $digits = fn (string $key) => is_string($this->input($key)) ? (preg_replace('/[^\d]/', '', $this->input($key)) ?: null) : $this->input($key);

        $this->merge([
            'required_investment' => $digits('required_investment'),
            'minimum_participation' => $digits('minimum_participation'),
            'acquisition_price' => $digits('acquisition_price'),
            'land_size_sqm' => $digits('land_size_sqm'),
            'is_featured' => $this->boolean('is_featured'),
            'remove_image' => $this->boolean('remove_image'),
        ]);
    }

    /**
     * Attributes ready to be saved on the opportunity.
     *
     * @return array<string, mixed>
     */
    public function opportunityAttributes(): array
    {
        return $this->safe()->except(['image', 'remove_image']);
    }
}
