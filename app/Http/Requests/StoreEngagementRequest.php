<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEngagementRequest extends FormRequest
{
    /**
     * Anyone may engage with a property.
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
            'name' => [Rule::requiredIf($isGuest), 'nullable', 'string', 'max:120'],
            'email' => [Rule::requiredIf($isGuest), 'nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'message' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.required' => 'Please enter your email so our team can reach you.',
        ];
    }
}
