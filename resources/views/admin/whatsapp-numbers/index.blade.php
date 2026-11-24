<x-layouts.admin title="WhatsApp numbers" subtitle="Where visitors can send their enquiries on WhatsApp.">
    <x-slot:actions>
        <a href="{{ route('admin.enquiries.index') }}" class="btn btn-ghost hidden sm:inline-flex"><x-icon name="arrow-left" class="size-4" /> Enquiries</a>
    </x-slot:actions>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <div class="space-y-4 xl:col-span-2">
            @forelse ($numbers as $number)
                <article class="card flex animate-fade-up flex-wrap items-center gap-4 p-5">
                    <span @class(['grid size-12 shrink-0 place-items-center rounded-2xl', 'bg-emerald-50 text-emerald-600' => $number->is_active, 'bg-ink-50 text-ink-300' => ! $number->is_active])><x-icon name="whatsapp" /></span>
                    <div class="min-w-0 flex-1">
                        <p class="flex flex-wrap items-center gap-2 font-semibold text-ink-900">
                            {{ $number->label }}
                            @if ($number->is_default)
                                <span class="rounded-md bg-brass-50 px-1.5 py-0.5 text-[10px] font-bold tracking-wide text-brass-700 uppercase ring-1 ring-brass-100">Default</span>
                            @endif
                            @unless ($number->is_active)
                                <span class="rounded-md bg-ink-50 px-1.5 py-0.5 text-[10px] font-semibold tracking-wide text-ink-500 uppercase">Off</span>
                            @endunless
                        </p>
                        <p class="font-display text-lg text-ink-700">{{ $number->display }}</p>
                        <p class="text-xs text-ink-400">{{ $number->enquiry_types_count }} enquiry {{ str('type')->plural($number->enquiry_types_count) }} routed here</p>
                    </div>
                    <div class="flex items-center gap-1">
                        <a href="{{ $number->link('Test message from the website admin.') }}" target="_blank" rel="noopener" data-no-loader class="btn btn-outline btn-sm">Test</a>
                        <a href="{{ route('admin.whatsapp-numbers.edit', $number) }}" class="grid size-9 place-items-center rounded-lg text-ink-400 hover:bg-ink-50 hover:text-ink-900" title="Edit"><x-icon name="pencil" class="size-4" /></a>
                        @if ($numbers->count() > 1)
                            <form method="POST" action="{{ route('admin.whatsapp-numbers.destroy', $number) }}" data-confirm="Remove {{ $number->display }}?">
                                @csrf @method('DELETE')
                                <button class="grid size-9 place-items-center rounded-lg text-ink-400 hover:bg-rose-50 hover:text-rose-600" title="Remove"><x-icon name="trash" class="size-4" /></button>
                            </form>
                        @endif
                    </div>
                </article>
            @empty
                <x-empty-state icon="phone" title="No WhatsApp numbers yet" description="Add a number so visitors can send enquiries on WhatsApp." />
            @endforelse
        </div>

        <section class="card animate-fade-up p-6 [animation-delay:80ms]">
            <h2 class="font-display text-xl text-ink-900">Add a number</h2>
            <p class="mt-1 text-sm text-ink-400">With more than one number, visitors choose where to send their enquiry.</p>
            <form method="POST" action="{{ route('admin.whatsapp-numbers.store') }}" class="mt-6">
                @csrf
                @include('admin.whatsapp-numbers._form', ['whatsappNumber' => $newNumber])
                <button class="btn btn-primary mt-6 w-full" data-loading-text="Adding…"><x-icon name="plus" class="size-4" /> Add number</button>
            </form>
        </section>
    </div>
</x-layouts.admin>
