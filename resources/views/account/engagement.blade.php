@php($property = $engagement->property)

<x-layouts.client title="Engagement progress" :subtitle="$property->title">
    <x-slot:actions>
        <a href="{{ route('account.engagements.index') }}" class="btn btn-ghost hidden sm:inline-flex"><x-icon name="arrow-left" class="size-4" /> My engagements</a>
    </x-slot:actions>

    <section class="max-w-6xl">
        @if ($isComplete)
            <div class="mb-6 flex animate-fade-up items-center gap-4 rounded-3xl bg-gradient-to-r from-emerald-50 to-ivory p-5 ring-1 ring-emerald-100">
                <span class="grid size-12 shrink-0 place-items-center rounded-2xl bg-emerald-500 text-white"><x-icon name="key" /></span>
                <div>
                    <p class="font-display text-xl text-ink-900">Congratulations — this deal is complete!</p>
                    <p class="text-sm text-ink-500">Thank you for choosing us. We hope you love your new place.</p>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 animate-fade-up gap-8 rounded-[2rem] bg-white p-5 shadow-soft ring-1 ring-ink-100 [animation-delay:60ms] sm:p-6 md:grid-cols-[18rem_1fr]">
            <x-property-image :src="$property->cover_url" :alt="$property->title" class="aspect-[4/3] rounded-2xl" />
            <div class="flex flex-col py-1">
                <div class="flex flex-wrap items-center gap-2">
                    <x-status-badge :status="$engagement->status" />
                    <span class="text-xs text-ink-400">Engaged {{ $engagement->created_at->format('F j, Y') }}</span>
                </div>
                <h1 class="mt-3 font-display text-3xl tracking-tight text-ink-950 sm:text-4xl">{{ $property->title }}</h1>
                <p class="mt-2 flex items-center gap-2 text-sm text-ink-500"><x-icon name="map-pin" class="size-4 text-brass-500" /> {{ $property->address }}, {{ $property->location }}</p>
                <div class="mt-auto flex flex-wrap items-center justify-between gap-4 pt-6">
                    <p class="font-display text-2xl text-ink-900">{{ $property->formatted_price }}</p>
                    @if ($property->status->isPublic())
                        <a href="{{ route('properties.show', $property) }}" class="btn btn-outline btn-sm">View listing <x-icon name="arrow-right" class="size-3.5" /></a>
                    @endif
                </div>
            </div>
        </div>

        <div class="mt-8 card animate-fade-up p-6 [animation-delay:120ms] sm:p-8">
            <h2 class="font-display text-2xl text-ink-900">Progress</h2>
            <p class="mt-1 text-sm text-ink-500">{{ $engagement->status->description() }}</p>
            <div class="mt-8"><x-progress-tracker :status="$engagement->status" /></div>
        </div>

        <div class="mt-8 grid grid-cols-1 gap-8 lg:grid-cols-3">
            <div class="card p-6 sm:p-8 lg:col-span-2" data-reveal>
                <h2 class="font-display text-2xl text-ink-900">Timeline</h2>
                <ol class="relative mt-8 space-y-8 border-l border-ink-100 pl-8">
                    @foreach ($engagement->updates as $update)
                        <li class="relative">
                            <span class="absolute top-0.5 -left-[41px] grid size-5 place-items-center rounded-full bg-white ring-4 ring-white">
                                <span @class(['size-2.5 rounded-full', 'bg-brass-500' => $loop->first, 'bg-ink-200' => ! $loop->first])></span>
                            </span>
                            <div class="flex flex-wrap items-center gap-2">
                                <x-status-badge :status="$update->status" />
                                <time class="text-xs text-ink-400" datetime="{{ $update->created_at->toIso8601String() }}">{{ $update->created_at->format('M j, Y · g:i A') }}</time>
                            </div>
                            @if ($update->note)
                                <p class="mt-3 rounded-2xl bg-ivory px-4 py-3 text-sm leading-relaxed text-ink-700 ring-1 ring-ink-100">{{ $update->note }}</p>
                            @else
                                <p class="mt-2 text-sm text-ink-500">{{ $update->status->description() }}</p>
                            @endif
                        </li>
                    @endforeach
                    <li class="relative">
                        <span class="absolute top-0.5 -left-[41px] grid size-5 place-items-center rounded-full bg-white ring-4 ring-white">
                            <span @class(['size-2.5 rounded-full', 'bg-brass-500' => $engagement->updates->isEmpty(), 'bg-ink-200' => $engagement->updates->isNotEmpty()])></span>
                        </span>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="pill bg-sky-50 text-sky-700 ring-sky-200">You engaged</span>
                            <time class="text-xs text-ink-400">{{ $engagement->created_at->format('M j, Y · g:i A') }}</time>
                        </div>
                        @if ($engagement->message)
                            <p class="mt-3 rounded-2xl bg-ivory px-4 py-3 text-sm text-ink-600 italic ring-1 ring-ink-100">“{{ $engagement->message }}”</p>
                        @endif
                    </li>
                </ol>
            </div>

            <aside class="space-y-6" data-reveal data-reveal-delay="100">
                <div class="card p-6">
                    <h3 class="font-display text-lg text-ink-900">Your contact details</h3>
                    <dl class="mt-4 space-y-3 text-sm">
                        <div class="flex items-center gap-3 text-ink-600"><x-icon name="user" class="size-4 text-ink-300" /> {{ $engagement->name }}</div>
                        <div class="flex items-center gap-3 text-ink-600"><x-icon name="mail" class="size-4 text-ink-300" /> {{ $engagement->email }}</div>
                        @if ($engagement->phone)
                            <div class="flex items-center gap-3 text-ink-600"><x-icon name="phone" class="size-4 text-ink-300" /> {{ $engagement->phone }}</div>
                        @endif
                    </dl>
                </div>
                <div class="rounded-2xl bg-gradient-to-br from-brass-50 to-ivory p-6 ring-1 ring-brass-100">
                    <x-icon name="bell" class="text-brass-500" />
                    <p class="mt-3 text-sm font-semibold text-ink-900">Updates on the way</p>
                    <p class="mt-1 text-xs leading-relaxed text-ink-500">We'll notify you here and by email at {{ auth()->user()->email }} whenever there's progress or the property changes.</p>
                </div>
            </aside>
        </div>
    </section>
</x-layouts.client>
