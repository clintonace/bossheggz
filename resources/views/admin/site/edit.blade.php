@php
    $value = fn (string $key) => old($key, $setting->value($key));
    $previewUrl = match ($area) {
        \App\Enums\SiteArea::Landing => route('home'),
        \App\Enums\SiteArea::Client => route('account.index'),
        \App\Enums\SiteArea::Admin => route('admin.dashboard'),
    };
    $fontGroups = collect($fonts)->groupBy('type', preserveKeys: true);
    $sizes = config('appearance.font_size');
    $chrome = $area->hasSidebar() ? 'sidebar' : 'footer';
    $previewFonts = collect($fonts)->map(fn ($font) => ['stack' => $font['stack'], 'bunny' => $font['bunny'], 'weights' => $font['weights']]);
@endphp

<x-layouts.admin title="Site manager" :subtitle="$area->description()">
    <x-slot:actions>
        <a href="{{ $previewUrl }}" target="_blank" class="btn btn-outline hidden sm:inline-flex"><x-icon name="eye" class="size-4" /> View {{ strtolower($area->label()) }}</a>
    </x-slot:actions>

    <div class="flex animate-fade-up flex-wrap items-center gap-3">
        <label for="site-area" class="text-sm font-medium text-ink-500">Editing</label>
        <div class="relative">
            <select id="site-area" onchange="window.PageLoader?.start(); window.location.href = this.value" class="appearance-none rounded-full border-0 bg-white py-2.5 pr-10 pl-4 text-sm font-semibold text-ink-900 shadow-soft ring-1 ring-ink-100 focus:ring-brass-300 focus:outline-none">
                @foreach (\App\Enums\SiteArea::cases() as $siteArea)
                    <option value="{{ route('admin.site.edit', $siteArea) }}" @selected($siteArea === $area)>{{ $siteArea->label() }}</option>
                @endforeach
            </select>
            <x-icon name="chevron-down" class="pointer-events-none absolute top-1/2 right-3.5 size-4 -translate-y-1/2 text-ink-400" />
        </div>
        @if ($setting->exists)
            <span class="pill bg-brass-50 text-brass-700 ring-brass-200">Customised</span>
        @else
            <span class="pill bg-ink-50 text-ink-500 ring-ink-200">Default look</span>
        @endif
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-5"
        data-site-manager
        data-area="{{ $area->value }}"
        data-recipes='@json($recipes)'
        data-fonts='@json($previewFonts)'>

        <form method="POST" action="{{ route('admin.site.update', $area) }}" id="site-form" class="space-y-6 xl:col-span-3">
            @csrf
            @method('PUT')

            {{-- Colours --}}
            <section class="card animate-fade-up p-6 sm:p-8">
                <h2 class="font-display text-xl text-ink-900">Colours</h2>
                <p class="mt-1 text-sm text-ink-400">Start from a preset or pick your own. Lighter and darker shades are generated automatically.</p>

                <div class="mt-5 grid grid-cols-2 gap-2 sm:grid-cols-3">
                    @foreach ($presets as $key => $preset)
                        <button type="button" data-preset='@json($preset)' class="flex items-center gap-3 rounded-xl bg-white px-3 py-2.5 text-left text-sm font-medium text-ink-700 ring-1 ring-ink-200 transition hover:ring-brass-400">
                            <span class="flex -space-x-1.5">
                                @foreach (['primary', 'accent', 'background'] as $swatch)
                                    <span class="size-5 rounded-full ring-2 ring-white" style="background: {{ $preset[$swatch] }}"></span>
                                @endforeach
                            </span>
                            {{ $preset['label'] }}
                        </button>
                    @endforeach
                </div>

                <div class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-3">
                    @foreach (['accent' => ['Accent', 'Buttons, highlights, badges'], 'primary' => ['Primary', 'Text, dark surfaces, sidebar'], 'background' => ['Background', 'Page background']] as $key => [$label, $hint])
                        <div>
                            <label for="{{ $key }}" class="field-label">{{ $label }}</label>
                            <div class="flex items-center gap-2 rounded-xl bg-white p-1.5 ring-1 ring-ink-200 focus-within:ring-2 focus-within:ring-brass-400">
                                <input type="color" data-color-for="{{ $key }}" value="{{ $value($key) }}" class="size-9 shrink-0 cursor-pointer rounded-lg border-0 bg-transparent p-0" aria-label="{{ $label }} colour picker">
                                <input type="text" name="{{ $key }}" id="{{ $key }}" value="{{ $value($key) }}" maxlength="7" class="w-full bg-transparent font-mono text-sm text-ink-900 uppercase focus:outline-none" spellcheck="false">
                            </div>
                            @error($key)
                                <p class="field-error">{{ $message }}</p>
                            @else
                                <p class="mt-1.5 text-xs text-ink-400">{{ $hint }}</p>
                            @enderror
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- Typography --}}
            <section class="card animate-fade-up p-6 [animation-delay:60ms] sm:p-8">
                <h2 class="font-display text-xl text-ink-900">Fonts &amp; size</h2>
                <div class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-2">
                    @foreach (['heading_font' => ['Heading font', 'The quick brown fox'], 'body_font' => ['Body font', 'Clear, comfortable reading for every page.']] as $key => [$label, $sample])
                        <div>
                            <label for="{{ $key }}" class="field-label">{{ $label }}</label>
                            <select name="{{ $key }}" id="{{ $key }}" class="field-input appearance-none">
                                @foreach ($fontGroups as $type => $group)
                                    <optgroup label="{{ $type === 'serif' ? 'Serif — classic' : 'Sans-serif — modern' }}">
                                        @foreach ($group as $fontKey => $font)
                                            <option value="{{ $fontKey }}" @selected($value($key) === $fontKey)>{{ $font['label'] }}</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                            <p data-font-sample="{{ $key }}" @class(['mt-3 truncate text-ink-800', 'text-2xl' => $key === 'heading_font', 'text-base' => $key === 'body_font']) style="font-family: {{ $fonts[$value($key)]['stack'] ?? 'inherit' }}">{{ $sample }}</p>
                            @error($key) <p class="field-error">{{ $message }}</p> @enderror
                        </div>
                    @endforeach
                </div>

                <div class="mt-6">
                    <div class="flex items-center justify-between">
                        <label for="font_size" class="field-label mb-0">Base font size</label>
                        <span class="rounded-full bg-ink-50 px-2.5 py-0.5 text-xs font-semibold text-ink-700"><span data-font-size-value>{{ $value('font_size') }}</span>px</span>
                    </div>
                    <input type="range" name="font_size" id="font_size" min="{{ $sizes['min'] }}" max="{{ $sizes['max'] }}" step="1" value="{{ $value('font_size') }}" class="mt-3 w-full accent-brass-500">
                    <div class="mt-1 flex justify-between text-[11px] text-ink-400"><span>Compact</span><span>Default ({{ $sizes['default'] }}px)</span><span>Large</span></div>
                    <p class="mt-2 text-xs text-ink-400">Scales text and spacing together, so layouts stay balanced.</p>
                    @error('font_size') <p class="field-error">{{ $message }}</p> @enderror
                </div>
            </section>

            {{-- Layout & mode --}}
            <section class="card animate-fade-up p-6 [animation-delay:120ms] sm:p-8">
                <h2 class="font-display text-xl text-ink-900">{{ $area->hasSidebar() ? 'Sidebar' : 'Footer' }} &amp; mode</h2>

                <div class="mt-6">
                    <span class="field-label">{{ $area->hasSidebar() ? 'Sidebar style' : 'Footer style' }}</span>
                    @if ($area->hasSidebar())
                        <p class="-mt-0.5 mb-3 text-xs text-ink-400">“Match mode” turns the sidebar dark in dark mode and light in light mode — including when visitors switch it from their profile menu.</p>
                    @endif
                    <div @class(['grid gap-3', 'grid-cols-3' => $area->hasSidebar(), 'grid-cols-2' => ! $area->hasSidebar()])>
                        @foreach ($area->hasSidebar() ? ['auto' => 'Match mode', 'light' => 'Always light', 'dark' => 'Always dark'] : ['light' => 'Light', 'dark' => 'Dark'] as $option => $label)
                            <label class="cursor-pointer">
                                <input type="radio" name="{{ $chrome }}" value="{{ $option }}" class="peer sr-only" @checked($value($chrome) === $option)>
                                <span class="block rounded-2xl bg-white p-3 ring-1 ring-ink-200 transition peer-checked:ring-2 peer-checked:ring-brass-500">
                                    <span class="flex h-16 overflow-hidden rounded-lg ring-1 ring-ink-100" aria-hidden="true">
                                        @if ($option === 'auto')
                                            <span class="w-1/3 space-y-1.5 bg-gradient-to-b from-white from-50% to-ink-900 to-50% p-2">
                                                <span class="block h-1.5 w-3/4 rounded-full bg-ink-200"></span>
                                                <span class="block h-1.5 w-1/2 rounded-full bg-brass-400"></span>
                                            </span>
                                            <span class="flex-1 bg-gradient-to-b from-ivory from-50% to-ink-800 to-50%"></span>
                                        @elseif ($area->hasSidebar())
                                            <span @class(['w-1/3 space-y-1.5 p-2', 'bg-ink-900' => $option === 'dark', 'border-r border-ink-100 bg-white' => $option === 'light'])>
                                                <span @class(['block h-1.5 w-3/4 rounded-full', 'bg-white/40' => $option === 'dark', 'bg-ink-200' => $option === 'light'])></span>
                                                <span class="block h-1.5 w-1/2 rounded-full bg-brass-400"></span>
                                                <span @class(['block h-1.5 w-2/3 rounded-full', 'bg-white/25' => $option === 'dark', 'bg-ink-100' => $option === 'light'])></span>
                                            </span>
                                            <span class="flex-1 bg-ivory"></span>
                                        @else
                                            <span class="flex w-full flex-col">
                                                <span class="flex-1 bg-ivory"></span>
                                                <span @class(['h-6', 'bg-ink-900' => $option === 'dark', 'border-t border-ink-100 bg-white' => $option === 'light'])></span>
                                            </span>
                                        @endif
                                    </span>
                                    <span class="mt-2 block text-sm font-semibold text-ink-900">{{ $label }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="mt-6">
                    <span class="field-label">Colour mode</span>
                    <div class="grid grid-cols-3 gap-3">
                        @foreach (['light' => ['Light', 'home'], 'dark' => ['Dark', 'lock'], 'system' => ['Match device', 'globe']] as $option => [$label, $icon])
                            <label class="cursor-pointer">
                                <input type="radio" name="mode" value="{{ $option }}" class="peer sr-only" @checked($value('mode') === $option)>
                                <span class="flex flex-col items-center gap-2 rounded-2xl bg-white p-4 text-center ring-1 ring-ink-200 transition peer-checked:bg-ink-900 peer-checked:text-white peer-checked:ring-ink-900">
                                    <span @class(['grid size-9 place-items-center rounded-full', 'bg-amber-100 text-amber-600' => $option === 'light', 'bg-ink-800 text-brass-200' => $option === 'dark', 'bg-ink-100 text-ink-600' => $option === 'system'])>
                                        @if ($option === 'light')
                                            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg>
                                        @elseif ($option === 'dark')
                                            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></svg>
                                        @else
                                            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="13" rx="2"/><path d="M8 21h8M12 17v4"/></svg>
                                        @endif
                                    </span>
                                    <span class="text-sm font-semibold">{{ $label }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                    @error('mode') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <label class="mt-6 flex cursor-pointer items-start gap-3 rounded-2xl bg-ivory p-4 ring-1 ring-ink-100">
                    <input type="hidden" name="animations" value="0">
                    <input type="checkbox" name="animations" value="1" class="mt-0.5 size-4 accent-brass-500" @checked($value('animations'))>
                    <span>
                        <span class="block text-sm font-semibold text-ink-900">Animations</span>
                        <span class="block text-xs text-ink-400">Fade-ins, hover lifts and transitions. Turn off for a calmer, faster feel.</span>
                    </span>
                </label>
            </section>

            <div class="flex flex-wrap items-center justify-between gap-3">
                <button type="submit" form="site-reset-form" class="btn btn-ghost"><x-icon name="trash" class="size-4" /> Reset to defaults</button>
                <button class="btn btn-primary btn-lg" data-loading-text="Saving…"><x-icon name="check" class="size-4" /> Save {{ strtolower($area->label()) }}</button>
            </div>
        </form>

        {{-- Live preview --}}
        <aside class="xl:col-span-2">
            <div class="xl:sticky xl:top-24">
                <p class="mb-3 flex items-center gap-2 text-xs font-semibold tracking-[0.15em] text-ink-400 uppercase"><span class="size-2 animate-pulse rounded-full bg-emerald-500"></span> Live preview · {{ $area->label() }}</p>
                <div class="overflow-hidden rounded-3xl shadow-lift ring-1 ring-ink-200">
                    <div class="flex items-center gap-1.5 bg-ink-100 px-4 py-2.5" aria-hidden="true">
                        <span class="size-2.5 rounded-full bg-rose-400"></span><span class="size-2.5 rounded-full bg-amber-400"></span><span class="size-2.5 rounded-full bg-emerald-400"></span>
                    </div>
                    <div data-preview-root class="bg-ivory font-sans text-ink-800">
                        <div data-preview-zoom class="flex min-h-[26rem]">
                            @if ($area->hasSidebar())
                                <div data-preview-chrome class="flex w-36 shrink-0 flex-col gap-1.5 border-r border-ink-100 bg-white p-3 text-[11px] text-ink-600">
                                    <span class="mb-2 flex items-center gap-1.5 font-display text-sm font-semibold text-ink-900"><span class="grid size-5 place-items-center rounded-md bg-ink-900 text-[10px] text-brass-300">S</span>{{ config('listings.company.name') }}</span>
                                    <span class="rounded-lg bg-ink-900 px-2 py-1.5 font-medium text-white">Overview</span>
                                    <span class="px-2 py-1">{{ $area === \App\Enums\SiteArea::Admin ? 'Properties' : 'Engagements' }}</span>
                                    <span class="px-2 py-1">{{ $area === \App\Enums\SiteArea::Admin ? 'Engagements' : 'Notifications' }}</span>
                                    <span class="px-2 py-1">{{ $area === \App\Enums\SiteArea::Admin ? 'Adverts' : 'Profile' }}</span>
                                </div>
                            @endif
                            <div class="flex min-w-0 flex-1 flex-col">
                                @unless ($area->hasSidebar())
                                    <div class="flex items-center justify-between border-b border-ink-100 px-4 py-3 text-[11px]">
                                        <span class="flex items-center gap-1.5 font-display text-sm font-semibold text-ink-900"><span class="grid size-5 place-items-center rounded-md bg-ink-900 text-[10px] text-brass-300">S</span>{{ config('listings.company.name') }}</span>
                                        <span class="flex gap-2 text-ink-500"><span>Home</span><span>For sale</span><span>For rent</span></span>
                                    </div>
                                @endunless
                                <div class="flex-1 space-y-3 p-4">
                                    <p class="text-[10px] font-semibold tracking-[0.2em] text-brass-600 uppercase">{{ $area === \App\Enums\SiteArea::Landing ? 'Curated homes' : 'Overview' }}</p>
                                    <p class="font-display text-2xl leading-tight text-ink-950">{{ $area === \App\Enums\SiteArea::Landing ? 'Find a place that feels like home.' : 'Good morning.' }}</p>
                                    <p class="text-xs leading-relaxed text-ink-500">Explore beautiful properties and follow every step, from first call to the keys in your hand.</p>
                                    <div class="flex flex-wrap gap-2">
                                        <span class="btn btn-sm btn-primary">Primary</span>
                                        <span class="btn btn-sm btn-accent">Accent</span>
                                        <span class="btn btn-sm btn-outline">Outline</span>
                                    </div>
                                    <div class="grid grid-cols-2 gap-2">
                                        <div class="card p-3">
                                            <p class="text-[10px] text-ink-400">Engaged</p>
                                            <p class="font-display text-xl text-ink-950">12</p>
                                            <span class="pill mt-1 bg-brass-50 text-[10px] text-brass-700 ring-brass-200">In progress</span>
                                        </div>
                                        <div class="card p-3">
                                            <p class="text-[10px] text-ink-400">Viewings</p>
                                            <p class="font-display text-xl text-ink-950">3</p>
                                            <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-ink-100"><div class="h-full w-2/3 rounded-full bg-brass-500"></div></div>
                                        </div>
                                    </div>
                                </div>
                                @unless ($area->hasSidebar())
                                    <div data-preview-chrome class="bg-ink-950 px-4 py-3 text-[10px] text-ink-300">
                                        <span class="font-display text-xs text-white">{{ config('listings.company.name') }}</span> · Properties for sale · For rent · Contact
                                    </div>
                                @endunless
                            </div>
                        </div>
                    </div>
                </div>
                <p class="mt-3 text-xs text-ink-400">Changes preview instantly. Click <span class="font-semibold text-ink-600">Save</span> to apply them to the {{ strtolower($area->label()) }}.</p>
            </div>
        </aside>
    </div>

    <form id="site-reset-form" method="POST" action="{{ route('admin.site.reset', $area) }}" data-confirm="Reset the {{ strtolower($area->label()) }} to the default look?" hidden>
        @csrf
        @method('DELETE')
    </form>
</x-layouts.admin>
