<?php

namespace App\Http\Requests\Admin;

use App\Enums\AdvertFormat;
use App\Enums\AdvertPage;
use App\Enums\AdvertPosition;
use App\Enums\AdvertWidth;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdvertRequest extends FormRequest
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
        $isPopup = $this->input('format') === AdvertFormat::Popup->value;
        $sections = AdvertPage::tryFrom((string) $this->input('page'))?->sections() ?? [];
        $positions = array_map(fn (AdvertPosition $position) => $position->value, $sections[$this->input('section')]['positions'] ?? []);

        return [
            'title' => ['required', 'string', 'max:120'],
            'sponsor' => ['nullable', 'string', 'max:120'],
            'body' => ['nullable', 'string', 'max:300'],
            'cta_label' => ['required', 'string', 'max:40'],
            'link_url' => ['required', 'url:http,https', 'max:2048'],
            'format' => ['required', Rule::enum(AdvertFormat::class)],
            'page' => ['required', Rule::in($isPopup ? array_keys(AdvertPage::popupTargets()) : array_column(AdvertPage::cases(), 'value'))],
            'section' => ['exclude_if:format,popup', 'required', Rule::in(array_keys($sections))],
            'position' => ['exclude_if:format,popup', 'required', Rule::in($positions)],
            'width' => ['exclude_if:format,popup', 'required', Rule::enum(AdvertWidth::class)],
            'display_seconds' => ['exclude_unless:format,popup', 'required', 'integer', 'min:3', 'max:60'],
            'sort_order' => ['integer', 'min:0', 'max:999'],
            'is_active' => ['boolean'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => array_filter(['nullable', 'date', $this->filled('starts_at') ? 'after:starts_at' : null]),
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'remove_image' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'link_url.url' => 'Enter a full web address starting with http:// or https://.',
            'ends_at.after' => 'The end date must be after the start date.',
            'image.max' => 'The image must be 4MB or smaller.',
            'section.in' => 'Choose a section that exists on the selected page.',
            'position.in' => 'That position isn’t available for the selected section.',
            'display_seconds.min' => 'Show the pop-up for at least 3 seconds.',
            'display_seconds.max' => 'Show the pop-up for at most 60 seconds.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'remove_image' => $this->boolean('remove_image'),
            'sort_order' => $this->filled('sort_order') ? $this->input('sort_order') : 0,
        ]);
    }

    /**
     * Attributes ready to save; pop-ups have no section/position.
     *
     * @return array<string, mixed>
     */
    public function advertAttributes(): array
    {
        $attributes = $this->safe()->except(['image', 'remove_image']);

        if ($attributes['format'] === AdvertFormat::Popup->value) {
            return [...$attributes, 'section' => null, 'position' => null];
        }

        return $attributes;
    }
}
