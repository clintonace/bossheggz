@php
    $listingType = old('listing_type', $property->listing_type?->value ?? 'sale');
    $selectClasses = 'field-input appearance-none';
@endphp

<div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
    <div class="space-y-6 xl:col-span-2">
        <section class="card p-6 sm:p-8">
            <h2 class="font-display text-xl text-ink-900">Listing details</h2>
            <p class="mt-1 text-sm text-ink-400">The basics visitors see first.</p>

            <div class="mt-6 space-y-5">
                <x-field name="title" label="Title" :value="$property->title" required placeholder="e.g. The Palms — Contemporary 5 Bedroom Villa" />

                <div>
                    <span class="field-label">Listing type</span>
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4" data-rent-toggle="#rent-period-field">
                        @foreach (\App\Enums\ListingType::cases() as $type)
                            <label class="cursor-pointer">
                                <input type="radio" name="listing_type" value="{{ $type->value }}" class="peer sr-only" @checked($listingType === $type->value)>
                                <span class="flex items-center gap-3 rounded-2xl bg-white px-4 py-3.5 text-sm font-semibold text-ink-600 ring-1 ring-ink-200 transition peer-checked:bg-ink-900 peer-checked:text-white peer-checked:ring-ink-900">
                                    <x-icon :name="match ($type) { \App\Enums\ListingType::Sale => 'tag', \App\Enums\ListingType::Shortlet => 'calendar', \App\Enums\ListingType::Lease => 'building', default => 'key' }" class="size-4" /> {{ $type->label() }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                    @error('listing_type') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div>
                        <label for="price" class="field-label">Price ({{ config('listings.currency') }})</label>
                        <input type="text" inputmode="numeric" name="price" id="price" value="{{ old('price', $property->price) }}" required class="field-input" placeholder="250000000">
                        @error('price') <p class="field-error">{{ $message }}</p> @enderror
                    </div>
                    <div id="rent-period-field">
                        <label for="rent_period" class="field-label">Price per</label>
                        <select name="rent_period" id="rent_period" class="{{ $selectClasses }}">
                            @foreach (\App\Enums\RentPeriod::cases() as $period)
                                <option value="{{ $period->value }}" @selected(old('rent_period', $property->rent_period?->value ?? 'year') === $period->value)>{{ ucfirst($period->label()) }}</option>
                            @endforeach
                        </select>
                        @error('rent_period') <p class="field-error">{{ $message }}</p> @enderror
                    </div>
                </div>

                <x-field name="description" label="Description" textarea rows="8" :value="$property->description" required placeholder="Describe the property, neighbourhood and what makes it special. Leave a blank line between paragraphs." />
            </div>
        </section>

        <section class="card p-6 sm:p-8">
            <h2 class="font-display text-xl text-ink-900">Location</h2>
            <div class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-2">
                <x-field name="address" label="Street address" :value="$property->address" required class="sm:col-span-2" />
                <x-field name="city" label="City / Area" :value="$property->city" required />
                <x-field name="state" label="State / Region" :value="$property->state" />
            </div>
        </section>

        <section class="card p-6 sm:p-8">
            <h2 class="font-display text-xl text-ink-900">Specifications</h2>
            <div class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-3">
                <x-field name="bedrooms" label="Bedrooms" type="number" min="0" :value="$property->bedrooms" />
                <x-field name="bathrooms" label="Bathrooms" type="number" min="0" :value="$property->bathrooms" />
                <x-field name="area_sqm" label="Area (m²)" type="number" min="0" :value="$property->area_sqm" />
            </div>
            <x-field class="mt-5" name="features_text" label="Features & amenities" textarea rows="4" :value="implode(PHP_EOL, $property->features ?? [])" hint="One feature per line, e.g. Swimming pool" />
        </section>

        <section class="card p-6 sm:p-8">
            <h2 class="font-display text-xl text-ink-900">Photos</h2>
            <p class="mt-1 text-sm text-ink-400">JPG, PNG or WebP up to 5MB each. The first photo is used as the cover.</p>

            @if ($property->exists && $property->images->isNotEmpty())
                <div class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-3">
                    @foreach ($property->images as $image)
                        <figure class="group relative aspect-[4/3] overflow-hidden rounded-2xl bg-ink-100 ring-1 ring-ink-100">
                            <img src="{{ $image->url }}" alt="" class="size-full object-cover">
                            @if ($loop->first)
                                <span class="absolute top-3 left-3 rounded-full bg-brass-500 px-2.5 py-1 text-[10px] font-bold tracking-wider text-white uppercase">Cover</span>
                            @endif
                            <div class="absolute inset-0 flex items-end justify-end gap-2 bg-gradient-to-t from-ink-950/60 to-transparent p-3 opacity-0 transition group-hover:opacity-100">
                                @unless ($loop->first)
                                    <button type="submit" form="cover-image-{{ $image->id }}" class="rounded-full bg-white/95 px-3 py-1.5 text-xs font-semibold text-ink-900 hover:bg-white">Make cover</button>
                                @endunless
                                <button type="submit" form="delete-image-{{ $image->id }}" class="grid size-8 place-items-center rounded-full bg-rose-600 text-white hover:bg-rose-700" aria-label="Remove photo"><x-icon name="trash" class="size-4" /></button>
                            </div>
                        </figure>
                    @endforeach
                </div>
            @endif

            <label class="mt-6 flex cursor-pointer flex-col items-center justify-center rounded-2xl border-2 border-dashed border-ink-200 bg-ivory/60 px-6 py-10 text-center transition hover:border-brass-300 hover:bg-brass-50/40">
                <span class="grid size-12 place-items-center rounded-2xl bg-white text-brass-500 shadow-soft ring-1 ring-ink-100"><x-icon name="upload" /></span>
                <span class="mt-4 text-sm font-semibold text-ink-900">Click to upload photos</span>
                <span class="mt-1 text-xs text-ink-400">You can select several at once</span>
                <input type="file" name="images[]" multiple accept="image/jpeg,image/png,image/webp" class="sr-only" data-image-preview="#image-previews">
            </label>
            <div id="image-previews" hidden class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-4"></div>
            @error('images') <p class="field-error">{{ $message }}</p> @enderror
            @foreach ($errors->get('images.*') as $messages)
                <p class="field-error">{{ $messages[0] }}</p>
            @endforeach
        </section>
    </div>

    <div class="space-y-6">
        <section class="card p-6 xl:sticky xl:top-24">
            <h2 class="font-display text-xl text-ink-900">Publishing</h2>
            <div class="mt-6 space-y-5">
                <div>
                    <label for="status" class="field-label">Status</label>
                    <select name="status" id="status" class="{{ $selectClasses }}">
                        @foreach (\App\Enums\PropertyStatus::cases() as $status)
                            <option value="{{ $status->value }}" @selected(old('status', $property->status?->value) === $status->value)>{{ $status->label() }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1.5 text-xs text-ink-400">Drafts are hidden from the website.</p>
                    @error('status') <p class="field-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="property_type" class="field-label">Property type</label>
                    <select name="property_type" id="property_type" class="{{ $selectClasses }}">
                        @foreach (\App\Enums\PropertyType::cases() as $type)
                            <option value="{{ $type->value }}" @selected(old('property_type', $property->property_type?->value) === $type->value)>{{ $type->label() }}</option>
                        @endforeach
                    </select>
                    @error('property_type') <p class="field-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="verification_status" class="field-label">Verification</label>
                    <select name="verification_status" id="verification_status" class="{{ $selectClasses }}">
                        @foreach (\App\Enums\VerificationStatus::cases() as $verification)
                            <option value="{{ $verification->value }}" @selected(old('verification_status', $property->verification_status?->value ?? 'pending') === $verification->value)>{{ $verification->label() }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1.5 text-xs text-ink-400">Only mark “Title verified” once the process is complete.</p>
                    @error('verification_status') <p class="field-error">{{ $message }}</p> @enderror
                </div>
                <label class="flex cursor-pointer items-start gap-3 rounded-2xl bg-ivory p-4 ring-1 ring-ink-100">
                    <input type="hidden" name="is_featured" value="0">
                    <input type="checkbox" name="is_featured" value="1" class="mt-0.5 size-4 accent-brass-500" @checked(old('is_featured', $property->is_featured))>
                    <span>
                        <span class="block text-sm font-semibold text-ink-900">Feature on homepage</span>
                        <span class="block text-xs text-ink-400">Shown in the “Featured properties” section.</span>
                    </span>
                </label>

                @if ($property->exists && $property->engagements_count > 0)
                    <p class="flex gap-2 rounded-2xl bg-brass-50 p-4 text-xs leading-relaxed text-brass-700 ring-1 ring-brass-100">
                        <x-icon name="bell" class="size-4 shrink-0" />
                        Changing the price or status will notify registered engagers by email and in-app.
                    </p>
                @endif

                <button class="btn btn-primary btn-lg w-full" data-loading-text="Saving…">
                    <x-icon name="check" class="size-4" /> {{ $property->exists ? 'Save changes' : 'Publish property' }}
                </button>
            </div>
        </section>
    </div>
</div>
