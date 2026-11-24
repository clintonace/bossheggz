<?php

namespace App\Http\Requests\Admin;

use App\Enums\DataSet;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DeleteDataRequest extends FormRequest
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
            'datasets' => ['required', 'array', 'min:1'],
            'datasets.*' => ['string', Rule::enum(DataSet::class)],
            'confirmation' => ['required', 'string', 'in:DELETE'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'datasets.required' => 'Choose at least one kind of data to delete.',
            'confirmation.required' => 'Type DELETE to confirm.',
            'confirmation.in' => 'Type DELETE in capital letters to confirm.',
        ];
    }

    /**
     * The confirmation field shows capitals whatever is typed, so accept any case.
     */
    protected function prepareForValidation(): void
    {
        if (is_string($this->input('confirmation'))) {
            $this->merge(['confirmation' => strtoupper(trim($this->input('confirmation')))]);
        }
    }

    /**
     * @return list<DataSet>
     */
    public function dataSets(): array
    {
        return array_map(DataSet::from(...), array_values(array_unique($this->validated('datasets'))));
    }
}
