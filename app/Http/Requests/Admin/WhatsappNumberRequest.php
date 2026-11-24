<?php

namespace App\Http\Requests\Admin;

use App\Models\WhatsappNumber;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WhatsappNumberRequest extends FormRequest
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
            'label' => ['required', 'string', 'max:60'],
            'number' => ['required', 'string', 'regex:/^\d{8,15}$/', Rule::unique('whatsapp_numbers', 'number')->ignore($this->route('whatsapp_number'))],
            'is_default' => ['boolean'],
            'is_active' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'number.regex' => 'Enter the number in international format, e.g. +234 811 170 8068 (8–15 digits).',
            'number.unique' => 'This WhatsApp number has already been added.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'number' => WhatsappNumber::digits($this->input('number')),
            'is_default' => $this->boolean('is_default'),
            'is_active' => $this->boolean('is_active'),
            'sort_order' => $this->input('sort_order') ?: 0,
        ]);
    }
}
