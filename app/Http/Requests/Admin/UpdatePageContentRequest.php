<?php

namespace App\Http\Requests\Admin;

use App\Enums\LandingPage;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates a whole landing page edit. Rules are built from the page's section
 * definitions in config/page_content.php, so new fields are validated automatically.
 */
class UpdatePageContentRequest extends FormRequest
{
    /** Links may be absolute, site-relative, an on-page anchor, email or phone. */
    public const LinkPattern = '/^(https?:\/\/|\/|#|mailto:|tel:)/i';

    /** Image values are a web link or a file previously uploaded through the CMS. */
    public const ImagePattern = '/^(https?:\/\/|\/|page-content\/)/i';

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
        $rules = [];

        foreach ($this->page()->sections() as $section => $definition) {
            $rules["visibility.{$section}"] = ['nullable', 'boolean'];

            foreach ($definition['fields'] as $name => $field) {
                $key = "sections.{$section}.{$name}";

                if ($field['type'] === 'items') {
                    $rules[$key] = ['nullable', 'array', 'max:'.($field['max_items'] ?? 12)];
                    $rules["{$key}.*"] = ['array'];

                    foreach ($field['fields'] as $subName => $subField) {
                        $rules["{$key}.*.{$subName}"] = $this->fieldRules($subField);
                    }

                    continue;
                }

                $rules[$key] = $this->fieldRules($field);

                if ($field['type'] === 'image') {
                    $rules["uploads.{$section}.{$name}"] = ['nullable', 'image', 'max:5120'];
                }
            }
        }

        return $rules;
    }

    /**
     * Friendly names such as "Hero banner → Heading" for error messages.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        $attributes = [];

        foreach ($this->page()->sections() as $section => $definition) {
            foreach ($definition['fields'] as $name => $field) {
                $attributes["sections.{$section}.{$name}"] = "{$definition['label']} → {$field['label']}";
                $attributes["uploads.{$section}.{$name}"] = "{$definition['label']} → {$field['label']} upload";

                foreach ($field['fields'] ?? [] as $subName => $subField) {
                    $attributes["sections.{$section}.{$name}.*.{$subName}"] = "{$field['label']} → {$subField['label']}";
                }
            }
        }

        return $attributes;
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'sections.*.*.regex' => 'The :attribute must be a full web address (https://…), a page path starting with “/”, or an anchor starting with “#”.',
        ];
    }

    /**
     * Drop repeatable rows the admin left completely empty before validating them.
     */
    protected function prepareForValidation(): void
    {
        $sections = $this->input('sections', []);

        if (! is_array($sections)) {
            return;
        }

        foreach ($this->page()->sections() as $section => $definition) {
            foreach ($definition['fields'] as $name => $field) {
                $rows = $sections[$section][$name] ?? null;

                if ($field['type'] !== 'items' || ! is_array($rows)) {
                    continue;
                }

                $sections[$section][$name] = array_values(array_filter(
                    $rows,
                    fn (mixed $row) => is_array($row) && collect($row)
                        ->except(array_keys(array_filter($field['fields'], fn (array $subField) => $subField['type'] === 'icon')))
                        ->contains(fn (mixed $value) => filled($value)),
                ));
            }
        }

        $this->merge(['sections' => $sections]);
    }

    /**
     * @param  array<string, mixed>  $field
     * @return array<int, mixed>
     */
    protected function fieldRules(array $field): array
    {
        $presence = ($field['required'] ?? false) ? 'required' : 'nullable';

        return match ($field['type']) {
            'textarea' => [$presence, 'string', 'max:'.($field['max'] ?? 2000)],
            'lines' => [$presence, 'string', 'max:'.($field['max'] ?? 3000)],
            'url' => [$presence, 'string', 'max:500', 'regex:'.self::LinkPattern],
            'image' => [$presence, 'string', 'max:1000', 'regex:'.self::ImagePattern],
            'icon' => [$presence, 'string', Rule::in(config('page_content.icons'))],
            default => [$presence, 'string', 'max:'.($field['max'] ?? 255)],
        };
    }

    protected function page(): LandingPage
    {
        return $this->route('page');
    }
}
