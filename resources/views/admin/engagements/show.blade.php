@php($property = $engagement->property)

<x-layouts.admin :title="$engagement->name" :subtitle="'Engaged with '.$property->title.' · '.$engagement->created_at->format('M j, Y g:i A')">
    <x-slot:actions>
        <a href="{{ route('admin.engagements.index') }}" class="btn btn-ghost hidden sm:inline-flex"><x-icon name="arrow-left" class="size-4" /> All engagements</a>
    </x-slot:actions>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <div class="space-y-6 xl:col-span-2">
            {{-- Progress --}}
            <section class="card animate-fade-up p-6 sm:p-8">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h2 class="font-display text-xl text-ink-900">Progress</h2>
                    <x-status-badge :status="$engagement->status" />
                </div>
                <div class="mt-8"><x-progress-tracker :status="$engagement->status" /></div>
            </section>

            {{-- Update form --}}
            <section class="card animate-fade-up p-6 [animation-delay:60ms] sm:p-8">
                <h2 class="font-display text-xl text-ink-900">Post an update</h2>
                <p class="mt-1 text-sm text-ink-400">Move this engagement forward and leave a note.</p>

                @if ($engagement->isGuest())
                    <div class="mt-5 flex gap-3 rounded-2xl bg-amber-50 p-4 text-sm text-amber-800 ring-1 ring-amber-100">
                        <x-icon name="info" class="size-5 shrink-0" />
                        <p><span class="font-semibold">Guest engagement.</span> They don't have an account, so updates are not sent automatically — reach out manually by email{{ $engagement->phone ? ' or phone' : '' }}.</p>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.engagements.updates.store', $engagement) }}" class="mt-6 space-y-5">
                    @csrf
                    <div>
                        <span class="field-label">New stage</span>
                        <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                            @foreach (\App\Enums\EngagementStatus::cases() as $case)
                                <label class="cursor-pointer">
                                    <input type="radio" name="status" value="{{ $case->value }}" class="peer sr-only" @checked(old('status', $engagement->status->value) === $case->value)>
                                    <span class="flex items-center gap-2 rounded-xl bg-white px-3 py-2.5 text-sm font-medium text-ink-600 ring-1 ring-ink-200 transition peer-checked:bg-ink-900 peer-checked:text-white peer-checked:ring-ink-900 hover:ring-ink-300">
                                        <span class="size-2 rounded-full bg-current opacity-50"></span> {{ $case->label() }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        @error('status') <p class="field-error">{{ $message }}</p> @enderror
                    </div>

                    <x-field name="note" label="Note (optional)" textarea rows="3" :placeholder="$engagement->isGuest() ? 'Internal note, e.g. Called and left a voicemail.' : 'Shared with the engager, e.g. Viewing booked for Saturday at 11am.'" />

                    <div class="flex flex-wrap items-center justify-between gap-4">
                        @unless ($engagement->isGuest())
                            <label class="flex items-center gap-2.5 text-sm text-ink-600">
                                <input type="hidden" name="notify" value="0">
                                <input type="checkbox" name="notify" value="1" checked class="size-4 accent-brass-500">
                                Notify {{ str($engagement->name)->before(' ') }} by email &amp; in-app
                            </label>
                        @else
                            <span></span>
                        @endunless
                        <button class="btn btn-primary" data-loading-text="Saving…"><x-icon name="check" class="size-4" /> Save update</button>
                    </div>
                </form>
            </section>

            {{-- Timeline --}}
            <section class="card animate-fade-up p-6 [animation-delay:120ms] sm:p-8">
                <h2 class="font-display text-xl text-ink-900">Activity</h2>
                <ol class="relative mt-8 space-y-7 border-l border-ink-100 pl-8">
                    @foreach ($engagement->updates as $update)
                        <li class="relative">
                            <span class="absolute top-1 -left-[37px] size-2.5 rounded-full bg-brass-500 ring-4 ring-white"></span>
                            <div class="flex flex-wrap items-center gap-2 text-xs text-ink-400">
                                <x-status-badge :status="$update->status" />
                                <span>by {{ $update->author?->name ?? 'System' }}</span>
                                <span>·</span>
                                <time>{{ $update->created_at->format('M j, Y g:i A') }}</time>
                            </div>
                            @if ($update->note)
                                <p class="mt-2.5 rounded-2xl bg-ivory px-4 py-3 text-sm text-ink-700 ring-1 ring-ink-100">{{ $update->note }}</p>
                            @endif
                        </li>
                    @endforeach
                    <li class="relative">
                        <span class="absolute top-1 -left-[37px] size-2.5 rounded-full bg-ink-200 ring-4 ring-white"></span>
                        <p class="text-xs text-ink-400"><span class="font-semibold text-ink-700">{{ $engagement->name }}</span> engaged · {{ $engagement->created_at->format('M j, Y g:i A') }}</p>
                        @if ($engagement->message)
                            <p class="mt-2.5 rounded-2xl bg-ivory px-4 py-3 text-sm text-ink-600 italic ring-1 ring-ink-100">“{{ $engagement->message }}”</p>
                        @endif
                    </li>
                </ol>
            </section>
        </div>

        <aside class="space-y-6">
            {{-- Contact --}}
            <section class="card animate-fade-up p-6 [animation-delay:80ms]">
                <div class="flex items-center gap-4">
                    <span @class(['grid size-14 place-items-center rounded-2xl font-display text-xl', 'bg-ink-100 text-ink-600' => $engagement->isGuest(), 'bg-ink-900 text-brass-200' => ! $engagement->isGuest()])>{{ str($engagement->name)->substr(0, 1)->upper() }}</span>
                    <div class="min-w-0">
                        <p class="truncate font-semibold text-ink-900">{{ $engagement->name }}</p>
                        <p class="text-xs text-ink-400">{{ $engagement->isGuest() ? 'Guest · manual follow-up' : 'Member since '.$engagement->user->created_at->format('M Y') }}</p>
                    </div>
                </div>
                <dl class="mt-6 space-y-3 text-sm">
                    <div class="flex items-center gap-3">
                        <x-icon name="mail" class="size-4 text-ink-300" />
                        <a href="mailto:{{ $engagement->email }}?subject={{ rawurlencode('Re: '.$property->title) }}" class="truncate text-ink-700 hover:text-brass-700">{{ $engagement->email }}</a>
                        <button type="button" data-copy="{{ $engagement->email }}" class="ml-auto text-ink-300 hover:text-ink-700" title="Copy email"><x-icon name="copy" class="size-4" /></button>
                    </div>
                    @if ($engagement->phone)
                        <div class="flex items-center gap-3">
                            <x-icon name="phone" class="size-4 text-ink-300" />
                            <a href="tel:{{ $engagement->phone }}" class="text-ink-700 hover:text-brass-700">{{ $engagement->phone }}</a>
                        </div>
                    @endif
                </dl>
                <div class="mt-6 grid grid-cols-2 gap-2">
                    <a href="mailto:{{ $engagement->email }}?subject={{ rawurlencode('Re: '.$property->title) }}" class="btn btn-accent btn-sm"><x-icon name="mail" class="size-4" /> Email</a>
                    @if ($engagement->phone)
                        <a href="tel:{{ $engagement->phone }}" class="btn btn-outline btn-sm"><x-icon name="phone" class="size-4" /> Call</a>
                    @endif
                </div>
            </section>

            {{-- Property --}}
            <section class="card animate-fade-up overflow-hidden [animation-delay:140ms]">
                <x-property-image :src="$property->cover_url" class="aspect-[16/10]" />
                <div class="p-6">
                    <p class="text-xs text-ink-400">{{ $property->listing_type->label() }} · {{ $property->location }}</p>
                    <p class="mt-1 font-display text-lg text-ink-900">{{ $property->title }}</p>
                    <p class="mt-1 text-sm font-semibold text-ink-700">{{ $property->formatted_price }}</p>
                    <div class="mt-4 flex gap-2">
                        <a href="{{ route('admin.properties.edit', $property) }}" class="btn btn-outline btn-sm"><x-icon name="pencil" class="size-3.5" /> Edit</a>
                        @if ($property->status->isPublic())
                            <a href="{{ route('properties.show', $property) }}" target="_blank" class="btn btn-ghost btn-sm"><x-icon name="eye" class="size-3.5" /> View</a>
                        @endif
                    </div>
                </div>
            </section>

            @if ($otherEngagements->isNotEmpty())
                <section class="card animate-fade-up p-6 [animation-delay:200ms]">
                    <h3 class="font-display text-lg text-ink-900">Also engaged with</h3>
                    <ul class="mt-4 space-y-2">
                        @foreach ($otherEngagements as $other)
                            <li>
                                <a href="{{ route('admin.engagements.show', $other) }}" class="flex items-center justify-between gap-3 rounded-xl px-3 py-2 text-sm hover:bg-ivory">
                                    <span class="truncate text-ink-700">{{ $other->property->title }}</span>
                                    <x-status-badge :status="$other->status" />
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif
        </aside>
    </div>
</x-layouts.admin>
