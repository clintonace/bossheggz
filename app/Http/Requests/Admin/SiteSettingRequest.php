<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SiteSettingRequest extends FormRequest
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
        $hex = ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'];
        $fonts = array_keys(config('appearance.fonts'));

        return [
            'accent' => $hex,
            'primary' => $hex,
            'background' => $hex,
            'heading_font' => ['required', Rule::in($fonts)],
            'body_font' => ['required', Rule::in($fonts)],
            'font_size' => ['required', 'integer', 'between:'.config('appearance.font_size.min').','.config('appearance.font_size.max')],
            'mode' => ['required', Rule::in(['light', 'dark', 'system'])],
            'sidebar' => ['sometimes', Rule::in(['auto', 'light', 'dark'])],
            'footer' => ['sometimes', Rule::in(['light', 'dark'])],
            'animations' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'accent.regex' => 'Use a 6-digit hex colour like #b88a41.',
            'primary.regex' => 'Use a 6-digit hex colour like #121825.',
            'background.regex' => 'Use a 6-digit hex colour like #faf7f2.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'animations' => $this->boolean('animations'),
            'font_size' => is_numeric($this->input('font_size')) ? (int) $this->input('font_size') : $this->input('font_size'),
            ...collect(['accent', 'primary', 'background'])
                ->filter(fn (string $key) => is_string($this->input($key)))
                ->mapWithKeys(fn (string $key) => [$key => strtolower(trim($this->input($key)))])
                ->all(),
        ]);
    }
}
