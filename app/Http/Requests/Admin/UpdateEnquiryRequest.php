<?php

namespace App\Http\Requests\Admin;

use App\Enums\EnquiryStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEnquiryRequest extends FormRequest
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
            'status' => ['required', Rule::enum(EnquiryStatus::class)],
            'admin_notes' => ['nullable', 'string', 'max:5000'],
            'client_message' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
