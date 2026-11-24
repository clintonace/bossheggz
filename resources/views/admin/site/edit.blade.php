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

            {{-- Glass & effects --}}
            <section class="card animate-fade-up p-6 [animation-delay:180ms] sm:p-8">
                <h2 class="font-display text-xl text-ink-900">Glass &amp; effects</h2>
                <p class="mt-1 text-sm text-ink-400">The frosted-glass look used on cards, the header, menus and dropdowns.</p>

                <div class="mt-6">
                    <span class="field-label">Glass style</span>
                    <div class="grid grid-cols-3 gap-3">
                        @foreach (['frosted' => ['Frosted', 'Full blur — the signature look'], 'subtle' => ['Subtle', 'A lighter, crisper blur'], 'solid' => ['Solid', 'No blur — plain surfaces']] as $option => [$label, $hint])
                            <label class="cursor-pointer">
                                <input type="radio" name="glass" value="{{ $option }}" class="peer sr-only" @checked($value('glass') === $option)>
                                <span class="block rounded-2xl bg-white p-3 ring-1 ring-ink-200 transition peer-checked:ring-2 peer-checked:ring-brass-500">
                                    <span class="relative isolate block h-16 overflow-hidden rounded-lg bg-gradient-to-br from-brass-200 via-ivory to-ink-200 ring-1 ring-ink-100" aria-hidden="true">
                                        <span class="absolute top-2 left-3 size-8 rounded-full bg-brass-400"></span>
                                        <span class="absolute right-3 bottom-1 size-10 rounded-full bg-ink-400"></span>
                                        <span @class([
                                            'absolute inset-x-3 top-1/2 h-8 -translate-y-1/2 rounded-md ring-1',
                                            'bg-white/50 ring-white/80' => $option === 'frosted',
                                            'bg-white/60 ring-white/80' => $option === 'subtle',
                                            'bg-white ring-ink-100' => $option === 'solid',
                                        ]) style="backdrop-filter: {{ ['frosted' => 'blur(12px)', 'subtle' => 'blur(2px)', 'solid' => 'none'][$option] }}"></span>
                                    </span>
                                    <span class="mt-2 block text-sm font-semibold text-ink-900">{{ $label }}</span>
                                    <span class="block text-[11px] leading-snug text-ink-400">{{ $hint }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                    @error('glass') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div @class(['mt-6 grid grid-cols-1 gap-3', 'sm:grid-cols-2' => ! $area->hasSidebar()])>
                    <label class="flex cursor-pointer items-start gap-3 rounded-2xl bg-ivory p-4 ring-1 ring-ink-100">
                        <input type="hidden" name="ambient" value="0">
                        <input type="checkbox" name="ambient" value="1" class="mt-0.5 size-4 accent-brass-500" @checked($value('ambient'))>
                        <span>
                            <span class="block text-sm font-semibold text-ink-900">Background glow</span>
                            <span class="block text-xs text-ink-400">Soft accent-coloured light behind the glass{{ $area->hasSidebar() ? ' in the sidebar' : '' }}.</span>
                        </span>
                    </label>
                    @unless ($area->hasSidebar())
                        <label class="flex cursor-pointer items-start gap-3 rounded-2xl bg-ivory p-4 ring-1 ring-ink-100">
                            <input type="hidden" name="contact_strip" value="0">
                            <input type="checkbox" name="contact_strip" value="1" class="mt-0.5 size-4 accent-brass-500" @checked($value('contact_strip'))>
                            <span>
                                <span class="block text-sm font-semibold text-ink-900">Contact strip</span>
                                <span class="block text-xs text-ink-400">The dark bar above the header with phone, email and “List Your Property”.</span>
                            </span>
                        </label>
                        <label class="flex cursor-pointer items-start gap-3 rounded-2xl bg-ivory p-4 ring-1 ring-ink-100 sm:col-span-2">
                            <input type="hidden" name="preloader" value="0">
                            <input type="checkbox" name="preloader" value="1" class="mt-0.5 size-4 accent-brass-500" @checked($value('preloader'))>
                            <span class="flex-1">
                                <span class="block text-sm font-semibold text-ink-900">Branded preloader</span>
                                <span class="block text-xs text-ink-400">An animated {{ config('listings.company.name') }} intro on the first page a visitor opens, once per visit. Hidden when animations are off.</span>
                            </span>
                            <a href="{{ route('home') }}?preloader=1" target="_blank" class="btn btn-ghost btn-sm shrink-0" onclick="try { sessionStorage.removeItem('site-preloader-seen') } catch (e) {}"><x-icon name="play" class="size-3.5" /> Preview</a>
                        </label>
                    @endunless
                </div>
            </section>

            {{-- Guided tour --}}
            <section class="card animate-fade-up p-6 [animation-delay:240ms] sm:p-8">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <h2 class="font-display text-xl text-ink-900">Guided tour</h2>
                        <p class="mt-1 max-w-md text-sm text-ink-400">A short spotlight walkthrough of the {{ strtolower($area->label()) }}. Anyone can replay it from “Take a tour” in their profile menu{{ $area->hasSidebar() ? '' : ' or the footer' }}.</p>
                    </div>
                    <a href="{{ $previewUrl }}?tour=1" target="_blank" class="btn btn-outline btn-sm"><x-icon name="play" class="size-3.5" /> Preview tour</a>
                </div>

                <label class="mt-5 flex cursor-pointer items-start gap-3 rounded-2xl bg-ivory p-4 ring-1 ring-ink-100">
                    <input type="hidden" name="tour" value="0">
                    <input type="checkbox" name="tour" value="1" class="mt-0.5 size-4 accent-brass-500" @checked($value('tour'))>
                    <span>
                        <span class="block text-sm font-semibold text-ink-900">Show the tour on first visit</span>
                        <span class="block text-xs text-ink-400">Starts automatically the first time someone opens the {{ strtolower($area->label()) }}. Signed-in people see it once per account; guests once per browser.</span>
                    </span>
                </label>

                <ol class="mt-5 grid grid-cols-1 gap-2 sm:grid-cols-2">
                    @foreach ($area->tourSteps() as $step)
                        <li class="flex items-start gap-3 rounded-xl px-3 py-2.5 ring-1 ring-ink-100">
                            <span class="grid size-6 shrink-0 place-items-center rounded-full bg-brass-50 text-[11px] font-bold text-brass-700 ring-1 ring-brass-100">{{ $loop->iteration }}</span>
                            <span class="min-w-0">
                                <span class="block truncate text-sm font-semibold text-ink-900">{{ $step['title'] }}</span>
                                <span class="line-clamp-2 block text-xs text-ink-400">{{ $step['body'] }}</span>
                            </span>
                        </li>
                    @endforeach
                </ol>
            </section>

            @if ($area === \App\Enums\SiteArea::Landing)
                <a href="{{ route('admin.pages.index') }}" class="group flex animate-fade-up items-center gap-4 rounded-3xl bg-gradient-to-br from-ink-900 to-ink-800 p-5 text-white shadow-soft transition hover:shadow-lift [animation-delay:300ms]" data-palette="normal">
                    <span class="grid size-11 shrink-0 place-items-center rounded-2xl bg-white/10 text-brass-300 ring-1 ring-white/15"><x-icon name="file" /></span>
                    <span class="min-w-0 flex-1">
                        <span class="block font-semibold">Looking to change the wording or images?</span>
                        <span class="block text-sm text-ink-300">Headings, text, buttons and sections of every page live in Pages.</span>
                    </span>
                    <x-icon name="arrow-right" class="size-4 text-brass-300 transition group-hover:translate-x-1" />
                </a>
            @endif

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
                        <span class="ml-3 flex-1 truncate rounded-full bg-white/70 px-3 py-0.5 text-[10px] text-ink-400">{{ str($previewUrl)->after('://') }}</span>
                    </div>
                    <div data-preview-root data-glass="{{ $value('glass') }}" class="bg-ivory font-sans text-ink-800">
                        <div data-preview-zoom class="relative isolate flex min-h-[30rem] overflow-hidden">
                            {{-- Background glow --}}
                            <div data-preview-ambient @if (! $value('ambient')) hidden @endif class="pointer-events-none absolute inset-0 -z-10" aria-hidden="true">
                                <div class="absolute -top-16 -left-12 size-48 rounded-full bg-brass-200/60 blur-3xl"></div>
                                <div class="absolute top-1/3 -right-16 size-44 rounded-full bg-ink-200/70 blur-3xl"></div>
                                <div class="absolute -bottom-16 left-1/3 size-40 rounded-full bg-brass-100/80 blur-3xl"></div>
                            </div>

                            @if ($area->hasSidebar())
                                @php($isAdminPreview = $area === \App\Enums\SiteArea::Admin)
                                <div data-preview-chrome class="flex w-40 shrink-0 flex-col gap-1 border-r border-ink-100 bg-white/80 p-3 text-[10.5px] text-ink-600 backdrop-blur-xl">
                                    <span class="mb-2 flex items-center gap-1.5 font-display text-[13px] font-semibold text-ink-900"><span class="grid size-5 place-items-center rounded-md bg-ink-900 text-[10px] text-brass-300">{{ str(config('listings.company.name'))->substr(0, 1) }}</span><span class="truncate">{{ str(config('listings.company.name'))->before(' ') }}</span></span>
                                    <span class="px-2 pt-1 text-[8px] font-semibold tracking-[0.2em] text-ink-300 uppercase">{{ $isAdminPreview ? 'Manage' : 'Account' }}</span>
                                    <span class="rounded-lg bg-ink-900 px-2 py-1.5 font-medium text-white">{{ $isAdminPreview ? 'Dashboard' : 'Overview' }}</span>
                                    <span class="flex items-center justify-between px-2 py-1">{{ $isAdminPreview ? 'Properties' : 'Notifications' }} <x-icon name="chevron-down" class="size-3 text-ink-300" /></span>
                                    <span class="flex items-center justify-between rounded-lg bg-white px-2 py-1 font-medium text-ink-900 shadow-soft ring-1 ring-ink-100"><span class="flex items-center gap-1">{{ $isAdminPreview ? 'Enquiries' : 'My engagements' }} <span class="size-1 rounded-full bg-brass-500"></span></span> <x-icon name="chevron-down" class="size-3 rotate-180 text-ink-400" /></span>
                                    <span class="relative ml-2 overflow-hidden rounded-xl bg-gradient-to-b from-white/80 via-white/55 to-white/35 p-1 ring-1 ring-ink-100/70 backdrop-blur-xl">
                                        <span class="absolute top-2 bottom-2 left-2.5 w-px bg-gradient-to-b from-brass-300 to-ink-100"></span>
                                        @foreach ($isAdminPreview ? ['All enquiries', 'Enquiry types', 'WhatsApp numbers'] : ['All', 'In progress', 'Completed'] as $subLink)
                                            <span @class(['relative flex items-center gap-1.5 rounded-md py-1 pr-1 pl-4', 'bg-ink-900 text-white' => $loop->first])>{{ $subLink }}</span>
                                        @endforeach
                                    </span>
                                    @if ($isAdminPreview)
                                        <span class="px-2 pt-2 text-[8px] font-semibold tracking-[0.2em] text-ink-300 uppercase">Website</span>
                                        <span class="flex items-center justify-between px-2 py-1">Pages <x-icon name="chevron-down" class="size-3 text-ink-300" /></span>
                                        <span class="flex items-center justify-between px-2 py-1">Site manager <x-icon name="chevron-down" class="size-3 text-ink-300" /></span>
                                    @else
                                        <span class="flex items-center justify-between px-2 py-1">Browse <x-icon name="chevron-down" class="size-3 text-ink-300" /></span>
                                    @endif
                                </div>
                                <div class="flex min-w-0 flex-1 flex-col">
                                    <div class="flex items-center justify-between border-b border-ink-100/70 bg-ivory/60 px-4 py-2.5 backdrop-blur-xl">
                                        <span class="font-display text-sm text-ink-900">{{ $isAdminPreview ? 'Dashboard' : 'Overview' }}</span>
                                        <span class="flex items-center gap-2"><x-icon name="bell" class="size-3.5 text-ink-400" /><span class="grid size-5 place-items-center rounded-full bg-ink-900 text-[9px] text-brass-200">A</span></span>
                                    </div>
                                    <div class="flex-1 space-y-3 p-4">
                                        @if ($isAdminPreview)
                                            <div class="grid grid-cols-2 gap-2">
                                                @foreach ([['New enquiries', '4', 'In progress'], ['Engagements', '12', 'This week']] as [$statLabel, $statValue, $statPill])
                                                    <div class="card p-3">
                                                        <p class="text-[10px] text-ink-400">{{ $statLabel }}</p>
                                                        <p class="font-display text-xl text-ink-950">{{ $statValue }}</p>
                                                        <span class="pill mt-1 bg-brass-50 text-[10px] text-brass-700 ring-brass-200">{{ $statPill }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div data-palette="normal" class="relative overflow-hidden rounded-2xl bg-ink-950 p-4 text-white">
                                                <div class="absolute -top-8 -right-6 size-24 rounded-full bg-brass-500/30 blur-2xl"></div>
                                                <p class="relative text-[9px] font-semibold tracking-[0.2em] text-brass-300 uppercase">Welcome back</p>
                                                <p class="relative mt-1 font-display text-lg">Good morning, Ada.</p>
                                            </div>
                                        @endif
                                        <div class="glass rounded-2xl p-3">
                                            <div class="flex items-center justify-between">
                                                <p class="text-[11px] font-semibold text-ink-900">{{ $isAdminPreview ? 'Recent enquiries' : 'Your engagements' }}</p>
                                                <span class="text-[10px] text-brass-600">View all</span>
                                            </div>
                                            <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-ink-100"><div class="h-full w-2/3 rounded-full bg-brass-500"></div></div>
                                            <div class="mt-2 flex flex-wrap gap-1.5">
                                                <span class="btn btn-sm btn-primary">Primary</span>
                                                <span class="btn btn-sm btn-accent">Accent</span>
                                                <span class="btn btn-sm btn-outline">Outline</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="flex min-w-0 flex-1 flex-col">
                                    <div data-preview-strip data-palette="normal" @if (! $value('contact_strip')) hidden @endif class="flex items-center justify-between bg-ink-950 px-4 py-1.5 text-[8.5px] text-ink-300">
                                        <span>{{ config('listings.company.phone') }} · {{ strtolower(config('listings.company.email')) }}</span>
                                        <span class="font-semibold text-brass-300">+ List Your Property</span>
                                    </div>
                                    <div class="flex items-center justify-between gap-2 border-b border-white/40 bg-ivory/55 px-4 py-2.5 text-[10px] backdrop-blur-2xl">
                                        <span class="flex shrink-0 items-center gap-1.5 font-display text-[13px] font-semibold text-ink-900"><span class="grid size-5 place-items-center rounded-md bg-ink-900 text-[10px] text-brass-300">{{ str(config('listings.company.name'))->substr(0, 1) }}</span>{{ str(config('listings.company.name'))->before(' ') }}</span>
                                        <span class="hidden items-center gap-2 whitespace-nowrap text-ink-500 sm:flex"><span class="rounded-full bg-white/80 px-2 py-0.5 text-ink-900 shadow-soft">Home</span><span>Properties</span><span>Investments</span></span>
                                        <span class="shrink-0 rounded-full bg-ink-900 px-2.5 py-1 text-[9px] font-semibold whitespace-nowrap text-white">Speak to an Adviser</span>
                                    </div>
                                    <div class="flex-1 space-y-3 p-4">
                                        <p class="text-[9px] font-semibold tracking-[0.2em] text-brass-600 uppercase">Verified property · Investment advisory</p>
                                        <p class="font-display text-[26px] leading-[1.05] text-ink-950">Strategic property.<br><em class="text-brass-500">Lasting wealth.</em></p>
                                        <div class="glass-strong rounded-2xl p-2">
                                            <div class="flex gap-1 text-[9px] font-medium text-ink-500">
                                                <span class="rounded-full bg-ink-900 px-2 py-0.5 text-white">All</span><span class="px-2 py-0.5">Buy</span><span class="px-2 py-0.5">Rent</span><span class="px-2 py-0.5">Shortlet</span>
                                            </div>
                                            <div class="mt-1.5 grid grid-cols-[1fr_1fr_auto] gap-1.5">
                                                <span class="rounded-xl bg-white/70 px-2 py-1.5 text-[9px] text-ink-300 ring-1 ring-white/80">State or city</span>
                                                <span class="rounded-xl bg-white/70 px-2 py-1.5 text-[9px] text-ink-300 ring-1 ring-white/80">All categories</span>
                                                <span class="btn btn-sm btn-accent rounded-xl">Search</span>
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-2 gap-2">
                                            @foreach ([['Lekki Phase 1 duplex', '₦185m'], ['Ikoyi serviced flat', '₦9.5m / yr']] as [$cardTitle, $cardPrice])
                                                <div class="glass-card overflow-hidden rounded-2xl">
                                                    <div class="h-12 bg-gradient-to-br {{ $loop->first ? 'from-ink-300 to-brass-200' : 'from-brass-200 to-ink-200' }}"></div>
                                                    <div class="p-2">
                                                        <p class="truncate text-[10px] font-semibold text-ink-900">{{ $cardTitle }}</p>
                                                        <span class="pill mt-1 bg-brass-50 text-[9px] text-brass-700 ring-brass-200">{{ $cardPrice }}</span>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div data-preview-chrome class="bg-ink-950 px-4 py-3 text-[10px] text-ink-300">
                                        <span class="font-display text-xs text-white">{{ config('listings.company.name') }}</span> · Properties · Investments · Contact
                                    </div>
                                </div>
                            @endif
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
