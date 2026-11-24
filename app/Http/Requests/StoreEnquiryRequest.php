<?php

namespace App\Http\Requests;

use App\Enums\PropertyStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEnquiryRequest extends FormRequest
{
    /**
     * Anyone may send an enquiry.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Guests must tell us who they are; signed-in users are already known.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $isGuest = $this->user() === null;

        return [
            'type' => ['required', 'string', Rule::exists('enquiry_types', 'slug')->where('is_active', true)],
            'property' => ['nullable', 'string', Rule::exists('properties', 'slug')->where('status', PropertyStatus::Published->value)],
            'name' => [Rule::requiredIf($isGuest), 'nullable', 'string', 'max:120'],
            'email' => [Rule::requiredIf($isGuest), 'nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'interest' => ['nullable', 'string', 'max:200'],
            'opportunity' => ['nullable', 'string', Rule::exists('investment_opportunities', 'slug')->where('status', 'open')],
            'message' => ['nullable', 'string', 'max:3000'],
        ];
    }

    /**
     * Send visitors back to the form itself when validation fails.
     */
    protected function getRedirectUrl(): string
    {
        return strtok(parent::getRedirectUrl(), '#').'#enquire';
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.required' => 'Please enter your email so our team can reach you.',
            'opportunity.exists' => 'That opportunity is no longer open to enquiries.',
            'property.exists' => 'That property is no longer available — choose another or describe what you need.',
            'type.exists' => 'Please choose an enquiry type from the list.',
        ];
    }
}
