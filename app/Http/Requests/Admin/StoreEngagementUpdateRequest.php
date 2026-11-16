<?php

namespace App\Http\Requests\Admin;

use App\Enums\EngagementStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEngagementUpdateRequest extends FormRequest
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
            'status' => ['required', Rule::enum(EngagementStatus::class)],
            'note' => ['nullable', 'string', 'max:2000'],
            'notify' => ['boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['notify' => $this->boolean('notify')]);
    }
}
