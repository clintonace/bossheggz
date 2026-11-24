@php
    $subject = 'Re: '.$enquiry->subject();
    $whatsappNumber = preg_replace('/\D+/', '', (string) $enquiry->phone);
@endphp

<x-layouts.admin :title="$enquiry->name" :subtitle="$enquiry->typeLabel().' · '.$enquiry->created_at->format('M j, Y g:i A')">
    <x-slot:actions>
        <a href="{{ route('admin.enquiries.index') }}" class="btn btn-ghost hidden sm:inline-flex"><x-icon name="arrow-left" class="size-4" /> All enquiries</a>
    </x-slot:actions>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <div class="space-y-6 xl:col-span-2">
            <section class="card animate-fade-up p-6 sm:p-8">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h2 class="font-display text-xl text-ink-900">Enquiry</h2>
                    <x-status-badge :status="$enquiry->status" />
                </div>
                <dl class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="rounded-2xl bg-ivory p-4 ring-1 ring-ink-100">
                        <dt class="text-xs font-semibold tracking-wider text-ink-400 uppercase">Type</dt>
                        <dd class="mt-1 font-medium text-ink-900">{{ $enquiry->typeLabel() }}</dd>
                    </div>
                    <div class="rounded-2xl bg-ivory p-4 ring-1 ring-ink-100">
                        <dt class="text-xs font-semibold tracking-wider text-ink-400 uppercase">Interest</dt>
                        <dd class="mt-1 font-medium text-ink-900">
                            @if ($enquiry->enquirable instanceof \App\Models\InvestmentOpportunity)
                                <a href="{{ route('admin.opportunities.edit', $enquiry->enquirable) }}" class="hover:text-brass-700">{{ $enquiry->enquirable->title }}</a>
                            @elseif ($enquiry->enquirable instanceof \App\Models\Property)
                                <a href="{{ route('admin.properties.edit', $enquiry->enquirable) }}" class="hover:text-brass-700">{{ $enquiry->enquirable->title }}</a>
                                <span class="block text-xs font-normal text-ink-400">{{ $enquiry->enquirable->location }} · {{ $enquiry->enquirable->formatted_price }}</span>
                            @endif
                            @if ($enquiry->interest)
                                <span @class(['block', 'mt-2 text-sm font-normal text-ink-600' => $enquiry->enquirable])>{{ $enquiry->enquirable ? 'Also described: ' : '' }}{{ $enquiry->interest }}</span>
                            @elseif (! $enquiry->enquirable)
                                Not specified
                            @endif
                        </dd>
                    </div>
                </dl>
                <div class="mt-6">
                    <p class="text-xs font-semibold tracking-wider text-ink-400 uppercase">Message</p>
                    @if ($enquiry->message)
                        <p class="mt-2 rounded-2xl bg-ivory px-5 py-4 text-sm leading-relaxed whitespace-pre-line text-ink-700 ring-1 ring-ink-100">{{ $enquiry->message }}</p>
                    @else
                        <p class="mt-2 text-sm text-ink-400">No message was included.</p>
                    @endif
                </div>
            </section>

            <section class="card animate-fade-up p-6 [animation-delay:60ms] sm:p-8">
                <h2 class="font-display text-xl text-ink-900">Status and internal notes</h2>
                <p class="mt-1 text-sm text-ink-400">Notes are only visible to admins.</p>

                <form method="POST" action="{{ route('admin.enquiries.update', $enquiry) }}" class="mt-6 space-y-5">
                    @csrf
                    @method('PUT')
                    <div>
                        <span class="field-label">Status</span>
                        <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                            @foreach (\App\Enums\EnquiryStatus::cases() as $case)
                                <label class="cursor-pointer">
                                    <input type="radio" name="status" value="{{ $case->value }}" class="peer sr-only" @checked(old('status', $enquiry->status->value) === $case->value)>
                                    <span class="flex items-center gap-2 rounded-xl bg-white px-3 py-2.5 text-sm font-medium text-ink-600 ring-1 ring-ink-200 transition peer-checked:bg-ink-900 peer-checked:text-white peer-checked:ring-ink-900 hover:ring-ink-300">
                                        <span class="size-2 rounded-full bg-current opacity-50"></span> {{ $case->label() }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        @error('status') <p class="field-error">{{ $message }}</p> @enderror
                    </div>
                    <x-field name="admin_notes" label="Internal notes" textarea rows="5" :value="$enquiry->admin_notes" placeholder="e.g. Called on Monday — wants a site visit to Asaba next week." />
                    <div class="flex justify-end">
                        <button class="btn btn-primary" data-loading-text="Saving…"><x-icon name="check" class="size-4" /> Save</button>
                    </div>
                </form>
            </section>
        </div>

        <aside class="space-y-6">
            <section class="card animate-fade-up p-6 [animation-delay:80ms]">
                <div class="flex items-center gap-4">
                    <span class="grid size-14 place-items-center rounded-2xl bg-ink-900 font-display text-xl text-brass-200">{{ str($enquiry->name)->substr(0, 1)->upper() }}</span>
                    <div class="min-w-0">
                        <p class="truncate font-semibold text-ink-900">{{ $enquiry->name }}</p>
                        <p class="text-xs text-ink-400">{{ $enquiry->user ? 'Member since '.$enquiry->user->created_at->format('M Y') : 'Guest · manual follow-up' }}</p>
                    </div>
                </div>
                <dl class="mt-6 space-y-3 text-sm">
                    <div class="flex items-center gap-3">
                        <x-icon name="mail" class="size-4 text-ink-300" />
                        <a href="mailto:{{ $enquiry->email }}?subject={{ rawurlencode($subject) }}" class="truncate text-ink-700 hover:text-brass-700">{{ $enquiry->email }}</a>
                        <button type="button" data-copy="{{ $enquiry->email }}" class="ml-auto text-ink-300 hover:text-ink-700" title="Copy email"><x-icon name="copy" class="size-4" /></button>
                    </div>
                    @if ($enquiry->phone)
                        <div class="flex items-center gap-3">
                            <x-icon name="phone" class="size-4 text-ink-300" />
                            <a href="tel:{{ $enquiry->phone }}" class="text-ink-700 hover:text-brass-700">{{ $enquiry->phone }}</a>
                        </div>
                    @endif
                </dl>
                <div class="mt-6 grid grid-cols-2 gap-2">
                    <a href="mailto:{{ $enquiry->email }}?subject={{ rawurlencode($subject) }}" class="btn btn-accent btn-sm"><x-icon name="mail" class="size-4" /> Email</a>
                    @if ($enquiry->phone)
                        <a href="tel:{{ $enquiry->phone }}" class="btn btn-outline btn-sm"><x-icon name="phone" class="size-4" /> Call</a>
                        @if ($whatsappNumber)
                            <a href="https://wa.me/{{ $whatsappNumber }}" target="_blank" rel="noopener" data-no-loader class="btn btn-outline btn-sm col-span-2"><x-icon name="whatsapp" class="size-4" /> WhatsApp</a>
                        @endif
                    @endif
                </div>
            </section>

            @if ($previousEnquiries->isNotEmpty())
                <section class="card animate-fade-up p-6 [animation-delay:120ms]">
                    <h2 class="font-display text-lg text-ink-900">Earlier enquiries</h2>
                    <ul class="mt-4 divide-y divide-ink-50">
                        @foreach ($previousEnquiries as $previous)
                            <li>
                                <a href="{{ route('admin.enquiries.show', $previous) }}" class="flex items-center justify-between gap-3 py-3 text-sm hover:text-brass-700">
                                    <span class="min-w-0">
                                        <span class="block truncate font-medium text-ink-900">{{ $previous->subject() }}</span>
                                        <span class="block text-xs text-ink-400">{{ $previous->created_at->format('M j, Y') }}</span>
                                    </span>
                                    <x-status-badge :status="$previous->status" />
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            <form method="POST" action="{{ route('admin.enquiries.destroy', $enquiry) }}" data-confirm="Delete this enquiry from {{ $enquiry->name }}?" class="text-center">
                @csrf @method('DELETE')
                <button class="text-xs font-semibold text-rose-600 hover:text-rose-700"><x-icon name="trash" class="inline size-3.5" /> Delete enquiry</button>
            </form>
        </aside>
    </div>
</x-layouts.admin>
