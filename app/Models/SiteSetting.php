<?php

namespace App\Models;

use App\Enums\SiteArea;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Once;

#[Fillable(['area', 'settings'])]
class SiteSetting extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'area' => SiteArea::class,
            'settings' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::saved(fn () => Once::flush());
        static::deleted(fn () => Once::flush());
    }

    /**
     * The settings for an area: saved values merged over the area's defaults.
     */
    public static function for(SiteArea $area): self
    {
        $stored = static::stored()->get($area->value);

        $setting = $stored ?? new static(['area' => $area]);
        $setting->settings = [...$area->defaults(), ...($stored?->settings ?? [])];

        return $setting;
    }

    /**
     * All saved rows, loaded once per request.
     *
     * @return Collection<string, self>
     */
    protected static function stored(): Collection
    {
        return once(fn () => static::all()->keyBy(fn (self $setting) => $setting->area->value));
    }

    public function value(string $key): mixed
    {
        return $this->settings[$key] ?? $this->area->defaults()[$key] ?? null;
    }

    public function mode(): string
    {
        return $this->value('mode');
    }

    /**
     * The colour mode for a visitor: their own choice wins over the site default.
     */
    public function modeFor(?User $user): string
    {
        return $user?->theme_mode ?? $this->mode();
    }

    /**
     * Sidebars are designed light. "auto" follows the page's light/dark mode,
     * "light" pins the light palette, "dark" pins the inverted (dark) palette.
     */
    public function sidebarPalette(): ?string
    {
        return match ($this->value('sidebar')) {
            'light' => 'normal',
            'dark' => 'inverted',
            default => null,
        };
    }

    public function footerIsDark(): bool
    {
        return $this->value('footer') === 'dark';
    }

    public function animationsEnabled(): bool
    {
        return (bool) $this->value('animations');
    }

    /**
     * Bunny Fonts stylesheet for any chosen font that isn't bundled with the app.
     */
    public function fontStylesheetUrl(): ?string
    {
        $families = collect([$this->value('heading_font'), $this->value('body_font')])
            ->unique()
            ->map(fn (string $key) => config("appearance.fonts.{$key}"))
            ->filter(fn (?array $font) => filled($font['bunny'] ?? null))
            ->map(fn (array $font) => "{$font['bunny']}:{$font['weights']}");

        return $families->isEmpty() ? null : 'https://fonts.bunny.net/css?family='.$families->implode('|').'&display=swap';
    }

    /**
     * The theme stylesheet injected after the app CSS.
     */
    public function css(): string
    {
        $fonts = config('appearance.fonts');
        $recipes = static::recipes();
        $fill = fn (array $recipe): string => collect($recipe)
            ->map(fn (string $template, string $variable) => "{$variable}:".strtr($template, [
                '{A}' => $this->value('accent'),
                '{P}' => $this->value('primary'),
                '{B}' => $this->value('background'),
            ]))
            ->implode(';');

        $light = $fill($recipes['light']);
        $dark = $fill($recipes['dark']).';color-scheme:dark';

        return implode('', [
            ':root{',
            '--font-display:'.($fonts[$this->value('heading_font')]['stack'] ?? $fonts['fraunces']['stack']).';',
            '--font-sans:'.($fonts[$this->value('body_font')]['stack'] ?? $fonts['instrument-sans']['stack']).';',
            'font-size:'.(int) $this->value('font_size').'px}',
            ":root,[data-palette=normal]{{$light}}",
            '[data-palette=normal]{color-scheme:light}',
            "[data-palette=inverted]{{$fill($recipes['dark'])}}",
            ":root[data-mode=dark]{{$dark}}",
            "@media (prefers-color-scheme:dark){:root[data-mode=system]{{$dark}}}",
        ]);
    }

    /**
     * How each theme colour is derived from the accent ({A}), primary ({P}) and background ({B}) colours.
     * Shared with the live preview in the site manager.
     *
     * @return array{light: array<string, string>, dark: array<string, string>}
     */
    public static function recipes(): array
    {
        $mix = fn (string $color, int $percent, string $with) => "color-mix(in oklab, {$color} {$percent}%, {$with})";
        $deep = $mix('{P}', 70, 'black');

        $light = [
            '--color-white' => '#ffffff',
            '--color-ivory' => '{B}',
            '--color-sand' => $mix('{B}', 93, '{A}'),
        ];
        foreach ([50 => 8, 100 => 18, 200 => 35, 300 => 55, 400 => 78] as $step => $percent) {
            $light["--color-brass-{$step}"] = $mix('{A}', $percent, 'white');
        }
        $light['--color-brass-500'] = '{A}';
        $light['--color-brass-600'] = $mix('{A}', 84, 'black');
        $light['--color-brass-700'] = $mix('{A}', 67, 'black');
        foreach ([50 => 4, 100 => 10, 200 => 22, 300 => 38, 400 => 58, 500 => 72, 600 => 82, 700 => 90, 800 => 95] as $step => $percent) {
            $light["--color-ink-{$step}"] = $mix('{P}', $percent, 'white');
        }
        $light['--color-ink-900'] = '{P}';
        $light['--color-ink-950'] = $deep;

        $dark = [
            '--color-white' => $mix('white', 5, $deep),
            '--color-ivory' => $deep,
            '--color-sand' => $mix('white', 9, $deep),
        ];
        foreach ([50 => 12, 100 => 20, 200 => 34, 300 => 55] as $step => $percent) {
            $dark["--color-brass-{$step}"] = $mix('{A}', $percent, $deep);
        }
        $dark['--color-brass-400'] = $mix('{A}', 85, 'white');
        $dark['--color-brass-500'] = '{A}';
        $dark['--color-brass-600'] = $mix('{A}', 80, 'white');
        $dark['--color-brass-700'] = $mix('{A}', 62, 'white');
        foreach ([50 => 8, 100 => 13, 200 => 20, 300 => 30, 400 => 45, 500 => 55, 600 => 66, 700 => 77, 800 => 87, 900 => 93, 950 => 97] as $step => $percent) {
            $dark["--color-ink-{$step}"] = $mix('white', $percent, $deep);
        }

        return ['light' => $light, 'dark' => $dark];
    }
}
