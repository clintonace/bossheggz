@php
    $selectClasses = 'field-input appearance-none';
    $money = fn ($value) => $value === null ? null : number_format((int) $value);
@endphp

<div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
    <div class="space-y-6 xl:col-span-2">
        <section class="card p-6 sm:p-8">
            <h2 class="font-display text-xl text-ink-900">Opportunity details</h2>
            <p class="mt-1 text-sm text-ink-400">What investors see on the listing card and detail page.</p>

            <div class="mt-6 space-y-5">
                <x-field name="title" label="Title" :value="$opportunity->title" required placeholder="e.g. Asaba Gardens Estate — Phase 2" />

                <div>
                    <label for="category" class="field-label">Investment category</label>
                    <select name="category" id="category" class="{{ $selectClasses }}">
                        @foreach (\App\Enums\InvestmentCategory::cases() as $category)
                            <option value="{{ $category->value }}" @selected(old('category', $opportunity->category?->value) === $category->value)>{{ $category->label() }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1.5 text-xs text-ink-400">“Land Banking” opportunities also appear on the Land Banking page.</p>
                    @error('category') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <x-field name="summary" label="Short summary" textarea rows="2" :value="$opportunity->summary" required maxlength="300" hint="One or two sentences, shown at the top of the page (max 300 characters)." />
                <x-field name="description" label="Description" textarea rows="7" :value="$opportunity->description" required hint="Leave a blank line between paragraphs." />
            </div>
        </section>

        <section class="card p-6 sm:p-8">
            <h2 class="font-display text-xl text-ink-900">Location</h2>
            <div class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-2">
                <x-field name="location" label="Location / area" :value="$opportunity->location" required placeholder="e.g. Ibusa Road, Asaba" />
                <x-field name="state" label="State" :value="$opportunity->state" placeholder="e.g. Delta State" />
            </div>
        </section>

        <section class="card p-6 sm:p-8" data-show-for="category" data-show-values="land_banking">
            <h2 class="font-display text-xl text-ink-900">Land details</h2>
            <p class="mt-1 text-sm text-ink-400">Shown on Land Banking cards and pages.</p>
            <div class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <label for="land_use" class="field-label">Land type</label>
                    <select name="land_use" id="land_use" class="{{ $selectClasses }}">
                        <option value="">—</option>
                        @foreach (\App\Enums\LandUse::cases() as $landUse)
                            <option value="{{ $landUse->value }}" @selected(old('land_use', $opportunity->land_use?->value) === $landUse->value)>{{ $landUse->label() }}</option>
                        @endforeach
                    </select>
                    @error('land_use') <p class="field-error">{{ $message }}</p> @enderror
                </div>
                <x-field name="acquisition_price" :label="'Acquisition price from ('.config('listings.currency').')'" inputmode="numeric" :value="$money($opportunity->acquisition_price)" placeholder="5,500,000" />
                <x-field name="land_sizes" label="Plot sizes offered" :value="$opportunity->land_sizes" placeholder="e.g. 300 m², 500 m², 1 plot" />
                <x-field name="land_size_sqm" label="Smallest plot (m²)" inputmode="numeric" :value="$opportunity->land_size_sqm" hint="Used by the land size filter." />
                <x-field name="access" label="Access road / distance" :value="$opportunity->access" placeholder="e.g. 10 minutes from Asaba airport" />
                <x-field name="estate_status" label="Estate / development status" :value="$opportunity->estate_status" placeholder="e.g. Perimeter fencing complete" />
                <x-field class="sm:col-span-2" name="rationale" label="One-line investment rationale" :value="$opportunity->rationale" maxlength="300" />
            </div>
        </section>

        <section class="card p-6 sm:p-8">
            <h2 class="font-display text-xl text-ink-900">Investment information</h2>
            <p class="mt-1 text-sm text-ink-400">Leave amounts empty to show “On request”. Present any figures as projections, never as guaranteed returns.</p>
            <div class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-3">
                <x-field name="required_investment" :label="'Required investment ('.config('listings.currency').')'" inputmode="numeric" :value="$money($opportunity->required_investment)" />
                <x-field name="minimum_participation" :label="'Minimum participation ('.config('listings.currency').')'" inputmode="numeric" :value="$money($opportunity->minimum_participation)" />
                <x-field name="timeline" label="Expected timeline" :value="$opportunity->timeline" placeholder="e.g. 24 – 36 months" />
            </div>
            <x-field class="mt-5" name="structure" label="Investment structure" textarea rows="4" :value="$opportunity->structure" />
            <x-field class="mt-5" name="risks" label="Relevant risks" textarea rows="4" :value="$opportunity->risks" />
        </section>
    </div>

    <div class="space-y-6">
        <section class="card p-6 xl:sticky xl:top-24">
            <h2 class="font-display text-xl text-ink-900">Publishing</h2>
            <div class="mt-6 space-y-5">
                <div>
                    <label for="status" class="field-label">Status</label>
                    <select name="status" id="status" class="{{ $selectClasses }}">
                        @foreach (\App\Enums\OpportunityStatus::cases() as $status)
                            <option value="{{ $status->value }}" @selected(old('status', $opportunity->status?->value) === $status->value)>{{ $status->label() }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1.5 text-xs text-ink-400">Drafts are hidden. Closed opportunities stay visible but stop taking enquiries.</p>
                    @error('status') <p class="field-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="verification_status" class="field-label">Verification</label>
                    <select name="verification_status" id="verification_status" class="{{ $selectClasses }}">
                        @foreach (\App\Enums\VerificationStatus::cases() as $verification)
                            <option value="{{ $verification->value }}" @selected(old('verification_status', $opportunity->verification_status?->value) === $verification->value)>{{ $verification->label() }}</option>
                        @endforeach
                    </select>
                    @error('verification_status') <p class="field-error">{{ $message }}</p> @enderror
                </div>
                <x-field name="documentation" label="Documentation" :value="$opportunity->documentation" placeholder="e.g. Certificate of Occupancy" />
                <label class="flex cursor-pointer items-start gap-3 rounded-2xl bg-ivory p-4 ring-1 ring-ink-100">
                    <input type="hidden" name="is_featured" value="0">
                    <input type="checkbox" name="is_featured" value="1" class="mt-0.5 size-4 accent-brass-500" @checked(old('is_featured', $opportunity->is_featured))>
                    <span>
                        <span class="block text-sm font-semibold text-ink-900">Feature this opportunity</span>
                        <span class="block text-xs text-ink-400">Listed first and shown on the homepage.</span>
                    </span>
                </label>

                <div>
                    <span class="field-label">Photo</span>
                    @if ($opportunity->image_url)
                        <figure class="relative aspect-[16/10] overflow-hidden rounded-2xl bg-ink-100 ring-1 ring-ink-100">
                            <img src="{{ $opportunity->image_url }}" alt="" class="size-full object-cover">
                        </figure>
                        <label class="mt-2 flex items-center gap-2 text-xs text-ink-500">
                            <input type="checkbox" name="remove_image" value="1" class="size-4 accent-rose-600"> Remove photo
                        </label>
                    @endif
                    <label class="mt-3 flex cursor-pointer items-center justify-center gap-2 rounded-2xl border-2 border-dashed border-ink-200 bg-ivory/60 px-4 py-5 text-sm font-semibold text-ink-700 transition hover:border-brass-300">
                        <x-icon name="upload" class="size-4 text-brass-500" /> {{ $opportunity->image_url ? 'Replace photo' : 'Upload photo' }}
                        <input type="file" name="image" accept="image/jpeg,image/png,image/webp" class="sr-only" data-image-preview="#opportunity-image-preview">
                    </label>
                    <div id="opportunity-image-preview" hidden class="mt-3 grid grid-cols-1"></div>
                    @error('image') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <button class="btn btn-primary btn-lg w-full" data-loading-text="Saving…">
                    <x-icon name="check" class="size-4" /> {{ $opportunity->exists ? 'Save changes' : 'Create opportunity' }}
                </button>
            </div>
        </section>
    </div>
</div>
