@php
    $customisedCount = $sections->filter->isCustomised()->count();
    $hiddenCount = $sections->reject->isShown()->count();
@endphp

<x-layouts.admin :title="$page->label().' page'" :subtitle="$page->description()">
    <x-slot:actions>
        <a href="{{ route('admin.pages.index') }}" class="btn btn-ghost hidden sm:inline-flex"><x-icon name="arrow-left" class="size-4" /> All pages</a>
        <a href="{{ $page->url() }}" target="_blank" class="btn btn-outline"><x-icon name="eye" class="size-4" /> View page</a>
    </x-slot:actions>

    <div class="flex animate-fade-up flex-wrap items-center gap-3">
        <label for="landing-page" class="text-sm font-medium text-ink-500">Editing</label>
        <div class="relative">
            <select id="landing-page" onchange="window.PageLoader?.start(); window.location.href = this.value" class="appearance-none rounded-full border-0 bg-white py-2.5 pr-10 pl-4 text-sm font-semibold text-ink-900 shadow-soft ring-1 ring-ink-100 focus:ring-brass-300 focus:outline-none">
                @foreach (\App\Enums\LandingPage::cases() as $landingPage)
                    <option value="{{ route('admin.pages.edit', $landingPage) }}" @selected($landingPage === $page)>{{ $landingPage->label() }}</option>
                @endforeach
            </select>
            <x-icon name="chevron-down" class="pointer-events-none absolute top-1/2 right-3.5 size-4 -translate-y-1/2 text-ink-400" />
        </div>
        @if ($customisedCount > 0)
            <span class="pill bg-brass-50 text-brass-700 ring-brass-200">{{ $customisedCount }} {{ str('section')->plural($customisedCount) }} edited</span>
        @else
            <span class="pill bg-ink-50 text-ink-500 ring-ink-200">Default content</span>
        @endif
        @if ($hiddenCount > 0)
            <span class="pill bg-ink-50 text-ink-500 ring-ink-200">{{ $hiddenCount }} hidden</span>
        @endif
    </div>

    @if ($errors->any())
        <div class="mt-6 flex animate-fade-up items-start gap-3 rounded-2xl bg-rose-50 p-4 text-sm text-rose-700 ring-1 ring-rose-100">
            <x-icon name="alert" class="mt-px size-5 shrink-0" />
            <div>
                <p class="font-semibold">Nothing was saved — please fix {{ $errors->count() === 1 ? 'the highlighted field' : 'the '.$errors->count().' highlighted fields' }}.</p>
                <ul class="mt-1 list-inside list-disc text-rose-600">
                    @foreach (array_slice($errors->all(), 0, 4) as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-[15rem_minmax(0,1fr)]">
        {{-- Section index --}}
        <nav aria-label="Sections" class="hidden xl:block">
            <div class="sticky top-24 overflow-hidden rounded-3xl bg-white/70 p-3 shadow-soft ring-1 ring-ink-100 backdrop-blur-xl">
                <p class="px-3 pt-2 pb-3 text-[11px] font-semibold tracking-[0.2em] text-ink-300 uppercase">Sections</p>
                <ol class="space-y-0.5">
                    @foreach ($sections as $key => $section)
                        <li>
                            <a href="#section-{{ $key }}" class="group flex items-center gap-3 rounded-xl px-3 py-2 text-sm text-ink-600 transition hover:bg-ivory hover:text-ink-900">
                                <span class="w-5 text-right font-display text-xs text-ink-300">{{ $loop->iteration }}</span>
                                <span @class(['flex-1 truncate', 'text-ink-400 line-through decoration-ink-200' => ! $section->isShown()])>{{ $section->label() }}</span>
                                @if ($section->isCustomised())
                                    <span class="size-1.5 shrink-0 rounded-full bg-brass-500" title="Edited"></span>
                                @endif
                            </a>
                        </li>
                    @endforeach
                </ol>
                <p class="mt-3 flex items-center gap-2 border-t border-ink-100 px-3 pt-3 text-[11px] text-ink-400"><span class="size-1.5 rounded-full bg-brass-500"></span> Edited from default</p>
            </div>
        </nav>

        <form method="POST" action="{{ route('admin.pages.update', $page) }}" enctype="multipart/form-data" data-dirty-form class="min-w-0 space-y-6">
            @csrf
            @method('PUT')

            @foreach ($sections as $key => $section)
                @php($definition = $section->definition())
                <section id="section-{{ $key }}" class="card scroll-mt-24 animate-fade-up overflow-hidden" style="animation-delay: {{ min($loop->index, 6) * 40 }}ms">
                    <header class="flex flex-wrap items-start justify-between gap-4 border-b border-ink-100/80 bg-gradient-to-br from-ivory/80 to-white px-6 py-5 sm:px-8">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-display text-sm text-ink-300">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                <h2 class="font-display text-xl text-ink-900">{{ $section->label() }}</h2>
                                @if ($section->isCustomised())
                                    <span class="pill bg-brass-50 text-brass-700 ring-brass-200">Edited</span>
                                @endif
                                @unless ($section->isShown())
                                    <span class="pill bg-ink-50 text-ink-500 ring-ink-200">Hidden</span>
                                @endunless
                            </div>
                            @if ($definition['description'] ?? null)
                                <p class="mt-1 text-sm text-ink-400">{{ $definition['description'] }}</p>
                            @endif
                        </div>

                        <div class="flex items-center gap-2">
                            @if ($section->isHideable())
                                <label class="inline-flex cursor-pointer items-center gap-2.5 rounded-full bg-white py-1.5 pr-3 pl-1.5 text-xs font-semibold text-ink-600 ring-1 ring-ink-100">
                                    <input type="hidden" name="visibility[{{ $key }}]" value="0">
                                    <input type="checkbox" name="visibility[{{ $key }}]" value="1" class="peer sr-only" @checked(old("visibility.{$key}", $section->is_visible))>
                                    <span class="relative h-5 w-9 rounded-full bg-ink-200 transition after:absolute after:top-0.5 after:left-0.5 after:size-4 after:rounded-full after:bg-white after:shadow-sm after:transition peer-checked:bg-emerald-500 peer-checked:after:translate-x-4 peer-focus-visible:ring-2 peer-focus-visible:ring-brass-400"></span>
                                    <span class="peer-checked:hidden">Hidden</span>
                                    <span class="hidden peer-checked:inline">Shown</span>
                                </label>
                            @endif
                            @if ($section->exists)
                                <button type="submit" form="reset-{{ $key }}" class="btn btn-ghost btn-sm" title="Put this section back to its original wording">
                                    <x-icon name="clock" class="size-3.5" /> Reset
                                </button>
                            @endif
                        </div>
                    </header>

                    <div class="grid grid-cols-1 gap-5 px-6 py-6 sm:grid-cols-2 sm:px-8 sm:py-7">
                        @foreach ($section->fields() as $name => $field)
                            @include('admin.pages._field', [
                                'field' => $field,
                                'inputName' => "sections[{$key}][{$name}]",
                                'errorKey' => "sections.{$key}.{$name}",
                                'id' => "f-{$key}-{$name}",
                                'value' => old("sections.{$key}.{$name}", $section->raw($name)),
                            ])
                        @endforeach
                    </div>
                </section>
            @endforeach

            {{-- Save bar --}}
            <div class="sticky bottom-4 z-20">
                <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl bg-white/75 px-5 py-3.5 shadow-lift ring-1 ring-white backdrop-blur-xl backdrop-saturate-150">
                    <p class="flex items-center gap-2 text-sm text-ink-500">
                        <span data-dirty-indicator hidden class="inline-flex items-center gap-2 font-semibold text-brass-700"><span class="size-2 animate-pulse rounded-full bg-brass-500"></span> Unsaved changes</span>
                        <span data-clean-indicator>Use <code class="rounded bg-ink-50 px-1 text-xs">{company}</code>, <code class="rounded bg-ink-50 px-1 text-xs">{company_short}</code> or <code class="rounded bg-ink-50 px-1 text-xs">{tagline}</code> to insert company details.</span>
                    </p>
                    <div class="flex items-center gap-2">
                        <a href="{{ $page->url() }}" target="_blank" class="btn btn-ghost btn-sm hidden sm:inline-flex">Preview</a>
                        <button type="submit" class="btn btn-primary"><x-icon name="check" class="size-4" /> Save {{ strtolower($page->label()) }} page</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- Icon artwork for the icon pickers' live preview. --}}
    <div data-icon-sprite hidden>
        @foreach ($icons as $iconName)
            <span data-icon="{{ $iconName }}"><x-icon :name="$iconName" class="size-5" /></span>
        @endforeach
    </div>

    @foreach ($sections->filter->exists as $key => $section)
        <form id="reset-{{ $key }}" method="POST" action="{{ route('admin.pages.reset', [$page, $key]) }}" data-confirm="Reset “{{ $section->label() }}” to its original content? Your edits to this section will be lost." hidden>
            @csrf
            @method('DELETE')
        </form>
    @endforeach
</x-layouts.admin>
