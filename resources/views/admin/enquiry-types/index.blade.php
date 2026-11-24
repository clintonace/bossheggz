<x-layouts.admin title="Enquiry types" subtitle="The options visitors choose from on the Speak to an Adviser form.">
    <x-slot:actions>
        <a href="{{ route('admin.enquiries.index') }}" class="btn btn-ghost hidden sm:inline-flex"><x-icon name="arrow-left" class="size-4" /> Enquiries</a>
    </x-slot:actions>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <section class="card animate-fade-up overflow-hidden xl:col-span-2">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-ink-100 bg-ivory/60 text-xs tracking-wider text-ink-400 uppercase">
                        <tr>
                            <th class="px-6 py-4 font-semibold">Type</th>
                            <th class="px-4 py-4 font-semibold">WhatsApp line</th>
                            <th class="px-4 py-4 text-center font-semibold">Enquiries</th>
                            <th class="px-4 py-4 font-semibold">Status</th>
                            <th class="px-6 py-4"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-50">
                        @foreach ($types as $type)
                            <tr class="transition hover:bg-ivory/60">
                                <td class="px-6 py-4">
                                    <a href="{{ route('admin.enquiry-types.edit', $type) }}" class="flex items-center gap-2 font-semibold text-ink-900 hover:text-brass-700">
                                        {{ $type->name }}
                                        @if ($type->is_system)
                                            <span class="rounded-md bg-ink-50 px-1.5 py-0.5 text-[10px] font-semibold tracking-wide text-ink-500 uppercase" title="Used by the website's own forms">Built-in</span>
                                        @endif
                                    </a>
                                    @if ($type->description)
                                        <p class="mt-0.5 max-w-sm truncate text-xs text-ink-400">{{ $type->description }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-xs whitespace-nowrap text-ink-500">
                                    @if ($type->whatsappNumber)
                                        {{ $type->whatsappNumber->label }}<span class="block text-ink-400">{{ $type->whatsappNumber->display }}</span>
                                    @else
                                        Default number
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <a href="{{ route('admin.enquiries.index', ['type' => $type->slug]) }}" class="inline-flex min-w-8 justify-center rounded-full bg-ink-50 px-2.5 py-1 text-xs font-semibold text-ink-700 hover:bg-ink-100">{{ $type->enquiries_count }}</a>
                                </td>
                                <td class="px-4 py-4">
                                    <span @class(['pill', 'bg-emerald-50 text-emerald-700 ring-emerald-200' => $type->is_active, 'bg-stone-100 text-stone-600 ring-stone-200' => ! $type->is_active])>
                                        <span class="size-1.5 rounded-full bg-current opacity-70"></span> {{ $type->is_active ? 'On' : 'Off' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex justify-end gap-1">
                                        <a href="{{ route('admin.enquiry-types.edit', $type) }}" class="grid size-9 place-items-center rounded-lg text-ink-400 hover:bg-ink-50 hover:text-ink-900" title="Edit"><x-icon name="pencil" class="size-4" /></a>
                                        @unless ($type->is_system || $type->enquiries_count > 0)
                                            <form method="POST" action="{{ route('admin.enquiry-types.destroy', $type) }}" data-confirm="Delete “{{ $type->name }}”?">
                                                @csrf @method('DELETE')
                                                <button class="grid size-9 place-items-center rounded-lg text-ink-400 hover:bg-rose-50 hover:text-rose-600" title="Delete"><x-icon name="trash" class="size-4" /></button>
                                            </form>
                                        @endunless
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <section class="card animate-fade-up p-6 [animation-delay:80ms]">
            <h2 class="font-display text-xl text-ink-900">Add an enquiry type</h2>
            <p class="mt-1 text-sm text-ink-400">It appears in the “Enquiry type” dropdown straight away.</p>
            <form method="POST" action="{{ route('admin.enquiry-types.store') }}" class="mt-6">
                @csrf
                @include('admin.enquiry-types._form', ['type' => $newType])
                <button class="btn btn-primary mt-6 w-full" data-loading-text="Adding…"><x-icon name="plus" class="size-4" /> Add type</button>
            </form>
        </section>
    </div>
</x-layouts.admin>
