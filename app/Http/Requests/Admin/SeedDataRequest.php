<?php

namespace App\Http\Requests\Admin;

use App\Enums\DataSet;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SeedDataRequest extends FormRequest
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
            'datasets.*' => ['string', Rule::in(collect(DataSet::cases())->filter->isSeedable()->map->value->all())],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return ['datasets.required' => 'Choose at least one kind of demo data to add.'];
    }

    /**
     * @return list<DataSet>
     */
    public function dataSets(): array
    {
        return array_map(DataSet::from(...), array_values(array_unique($this->validated('datasets'))));
    }
}
