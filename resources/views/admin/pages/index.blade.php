<x-layouts.admin title="Pages" subtitle="Edit the wording, images and sections of every public page. Changes go live as soon as you save.">
    <div class="grid grid-cols-1 gap-5 md:grid-cols-2 2xl:grid-cols-4">
        @foreach ($pages as $summary)
            @php($page = $summary['page'])
            <article class="group relative isolate flex animate-fade-up flex-col overflow-hidden rounded-3xl bg-white/70 p-6 shadow-soft ring-1 ring-ink-100 backdrop-blur-xl transition duration-300 hover:-translate-y-0.5 hover:shadow-lift" style="animation-delay: {{ $loop->index * 40 }}ms">
                <div class="pointer-events-none absolute -top-16 -right-16 -z-10 size-40 rounded-full bg-brass-200/40 blur-3xl transition duration-500 group-hover:bg-brass-300/50"></div>

                <div class="flex items-start justify-between gap-3">
                    <span class="grid size-12 place-items-center rounded-2xl bg-ink-900 text-brass-300 shadow-soft"><x-icon :name="$page->icon()" /></span>
                    @if ($summary['customised'] > 0)
                        <span class="pill bg-brass-50 text-brass-700 ring-brass-200">{{ $summary['customised'] }} edited</span>
                    @else
                        <span class="pill bg-ink-50 text-ink-500 ring-ink-200">Default content</span>
                    @endif
                </div>

                <h2 class="mt-5 font-display text-xl text-ink-900">
                    <a href="{{ route('admin.pages.edit', $page) }}" class="after:absolute after:inset-0">{{ $page->label() }}</a>
                </h2>
                <p class="mt-1.5 flex-1 text-sm text-ink-500">{{ $page->description() }}</p>

                <dl class="mt-5 flex items-center gap-4 border-t border-ink-100/80 pt-4 text-xs text-ink-400">
                    <div><dt class="sr-only">Sections</dt><dd><span class="font-semibold text-ink-700">{{ $summary['sections'] }}</span> sections</dd></div>
                    @if ($summary['hidden'] > 0)
                        <div><dt class="sr-only">Hidden</dt><dd><span class="font-semibold text-ink-700">{{ $summary['hidden'] }}</span> hidden</dd></div>
                    @endif
                    <div class="ml-auto"><dt class="sr-only">Last edited</dt><dd>{{ $summary['updated_at'] ? 'Edited '.$summary['updated_at']->diffForHumans() : 'Never edited' }}</dd></div>
                </dl>

                <div class="relative z-10 mt-4 flex gap-2">
                    <a href="{{ route('admin.pages.edit', $page) }}" class="btn btn-primary btn-sm"><x-icon name="pencil" class="size-3.5" /> Edit page</a>
                    <a href="{{ $page->url() }}" target="_blank" class="btn btn-ghost btn-sm"><x-icon name="eye" class="size-3.5" /> View</a>
                </div>
            </article>
        @endforeach
    </div>

    <p class="mt-8 flex items-start gap-2 text-xs text-ink-400">
        <x-icon name="info" class="mt-px size-4 shrink-0" />
        Phone, email, Instagram and address come from the company settings; WhatsApp lines, enquiry types, listings, opportunities and articles each have their own manager in the sidebar.
    </p>
</x-layouts.admin>
