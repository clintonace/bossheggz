<div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
    <div class="space-y-6 xl:col-span-2">
        <section class="card p-6 sm:p-8">
            <h2 class="font-display text-xl text-ink-900">Advert content</h2>
            <p class="mt-1 text-sm text-ink-400">Advertise anything — a partner business, product, event or offer. Keep it short: one clear headline and one action.</p>
            <div class="mt-6 space-y-5">
                <x-field name="title" label="Headline" :value="$advert->title" required maxlength="120" placeholder="e.g. Beach weekends from ₦85,000" />
                <x-field name="sponsor" label="Advertiser / sponsor (optional)" :value="$advert->sponsor" maxlength="120" placeholder="e.g. Coastline Getaways" />
                <x-field name="body" label="Short description (optional)" textarea rows="3" maxlength="300" :value="$advert->body" hint="Up to 300 characters." />
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-[1fr_2fr]">
                    <x-field name="cta_label" label="Button text" :value="$advert->cta_label" required maxlength="40" />
                    <x-field name="link_url" label="Link (where the advert goes)" type="url" :value="$advert->link_url" required placeholder="https://" />
                </div>
            </div>
        </section>

        <section class="card p-6 sm:p-8">
            <h2 class="font-display text-xl text-ink-900">Image</h2>
            <p class="mt-1 text-sm text-ink-400">JPG, PNG or WebP up to 4MB. Landscape images look best.</p>
            @if ($advert->image_url)
                <div class="mt-5 flex items-center gap-4">
                    <img src="{{ $advert->image_url }}" alt="" class="h-20 w-32 rounded-xl object-cover ring-1 ring-ink-100">
                    <label class="flex items-center gap-2 text-sm text-ink-600">
                        <input type="hidden" name="remove_image" value="0">
                        <input type="checkbox" name="remove_image" value="1" class="size-4 accent-rose-600"> Remove current image
                    </label>
                </div>
            @endif
            <label class="mt-5 flex cursor-pointer flex-col items-center justify-center rounded-2xl border-2 border-dashed border-ink-200 bg-ivory/60 px-6 py-8 text-center transition hover:border-brass-300 hover:bg-brass-50/40">
                <span class="grid size-11 place-items-center rounded-2xl bg-white text-brass-500 shadow-soft ring-1 ring-ink-100"><x-icon name="upload" /></span>
                <span class="mt-3 text-sm font-semibold text-ink-900">{{ $advert->image_url ? 'Replace image' : 'Upload an image' }}</span>
                <input type="file" name="image" accept="image/jpeg,image/png,image/webp" class="sr-only" data-image-preview="#advert-image-preview">
            </label>
            <div id="advert-image-preview" hidden class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-4"></div>
            @error('image') <p class="field-error">{{ $message }}</p> @enderror
        </section>

        <section class="card p-6 sm:p-8">
            <h2 class="font-display text-xl text-ink-900">Type &amp; placement</h2>
            <p class="mt-1 text-sm text-ink-400">Choose how the advert is shown and exactly where it goes.</p>

            @php
                $selectedFormat = old('format', $advert->format?->value ?? 'inline');
                $selectedPage = old('page', $advert->page ?? 'home');
                $selectedSection = old('section', $advert->section);
                $selectedPosition = old('position', $advert->position?->value ?? 'after');
                $selectedWidth = old('width', $advert->width?->value ?? 'full');
            @endphp

            <div class="mt-6 grid grid-cols-1 gap-3 sm:grid-cols-2">
                @foreach (\App\Enums\AdvertFormat::cases() as $format)
                    <label class="cursor-pointer">
                        <input type="radio" name="format" value="{{ $format->value }}" class="peer sr-only" @checked($selectedFormat === $format->value)>
                        <span class="flex h-full gap-3 rounded-2xl bg-white p-4 ring-1 ring-ink-200 transition peer-checked:bg-ink-900 peer-checked:ring-ink-900 [&_*]:transition peer-checked:[&_.desc]:text-ink-300 peer-checked:[&_.name]:text-white peer-checked:[&_.ico]:bg-brass-500 peer-checked:[&_.ico]:text-white">
                            <span class="ico grid size-10 shrink-0 place-items-center rounded-xl bg-ink-50 text-ink-500"><x-icon :name="$format === \App\Enums\AdvertFormat::Popup ? 'sparkles' : 'grid'" class="size-5" /></span>
                            <span>
                                <span class="name block text-sm font-semibold text-ink-900">{{ $format->label() }}</span>
                                <span class="desc mt-0.5 block text-xs leading-relaxed text-ink-500">{{ $format->description() }}</span>
                            </span>
                        </span>
                    </label>
                @endforeach
            </div>
            @error('format') <p class="field-error">{{ $message }}</p> @enderror

            <div class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-3">
                <div>
                    <label for="page" class="field-label">Page</label>
                    <select name="page" id="page" class="field-input appearance-none">
                        @foreach (\App\Enums\AdvertPage::popupTargets() as $value => $label)
                            <option value="{{ $value }}" @selected($selectedPage === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('page') <p class="field-error">{{ $message }}</p> @enderror
                </div>
                <div data-format-only="inline">
                    <label for="section" class="field-label">Section</label>
                    <select name="section" id="section" class="field-input appearance-none">
                        @foreach (\App\Enums\AdvertPage::cases() as $advertPage)
                            @foreach ($advertPage->sections() as $sectionKey => $section)
                                <option value="{{ $sectionKey }}" data-page="{{ $advertPage->value }}" data-positions="{{ collect($section['positions'])->pluck('value')->implode(',') }}" @selected($selectedSection === $sectionKey && $selectedPage === $advertPage->value)>{{ $section['label'] }}</option>
                            @endforeach
                        @endforeach
                    </select>
                    @error('section') <p class="field-error">{{ $message }}</p> @enderror
                </div>
                <div data-format-only="inline">
                    <label for="position" class="field-label">Position</label>
                    <select name="position" id="position" class="field-input appearance-none">
                        @foreach (\App\Enums\AdvertPosition::cases() as $position)
                            <option value="{{ $position->value }}" @selected($selectedPosition === $position->value)>{{ $position === \App\Enums\AdvertPosition::Inside ? 'Inside (card)' : $position->label().' the section' }}</option>
                        @endforeach
                    </select>
                    @error('position') <p class="field-error">{{ $message }}</p> @enderror
                </div>
            </div>

            <div data-format-only="inline" class="mt-6">
                <div data-advert-width>
                    <span class="field-label">Width</span>
                    <div class="grid grid-cols-2 gap-3">
                        @foreach (\App\Enums\AdvertWidth::cases() as $width)
                            <label class="cursor-pointer">
                                <input type="radio" name="width" value="{{ $width->value }}" class="peer sr-only" @checked($selectedWidth === $width->value)>
                                <span class="block rounded-2xl bg-white p-4 ring-1 ring-ink-200 transition peer-checked:ring-2 peer-checked:ring-brass-500">
                                    <span class="grid grid-cols-12 gap-1" aria-hidden="true">
                                        <span @class(['h-7 rounded-md bg-brass-400', 'col-span-6' => $width === \App\Enums\AdvertWidth::Half, 'col-span-12' => $width === \App\Enums\AdvertWidth::Full])></span>
                                        @if ($width === \App\Enums\AdvertWidth::Half)
                                            <span class="col-span-6 h-7 rounded-md border border-dashed border-ink-200"></span>
                                        @endif
                                    </span>
                                    <span class="mt-3 block text-sm font-semibold text-ink-900">{{ $width->label() }}</span>
                                    <span class="block text-xs text-ink-400">{{ $width === \App\Enums\AdvertWidth::Half ? 'Two half adverts in the same spot sit side by side.' : 'Spans the whole row.' }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                    @error('width') <p class="field-error">{{ $message }}</p> @enderror
                </div>
                <x-field class="mt-5 max-w-xs" name="sort_order" label="Order in this spot" type="number" min="0" max="999" :value="$advert->sort_order ?? 0" hint="Lower numbers show first." />
            </div>

            <div data-format-only="popup" class="mt-6">
                <x-field class="max-w-xs" name="display_seconds" label="Show for (seconds)" type="number" min="3" max="60" :value="$advert->display_seconds ?? 8" hint="Closes automatically after this time. Shown once per visit." />
            </div>
        </section>
    </div>

    <div class="space-y-6">
        <section class="card p-6 xl:sticky xl:top-24">
            <h2 class="font-display text-xl text-ink-900">Publishing</h2>
            <div class="mt-6 space-y-5">
                <label class="flex cursor-pointer items-start gap-3 rounded-2xl bg-ivory p-4 ring-1 ring-ink-100">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" class="mt-0.5 size-4 accent-brass-500" @checked(old('is_active', $advert->is_active))>
                    <span>
                        <span class="block text-sm font-semibold text-ink-900">Active</span>
                        <span class="block text-xs text-ink-400">Untick to pause the advert without deleting it.</span>
                    </span>
                </label>
                <x-field name="starts_at" label="Start showing (optional)" type="datetime-local" :value="$advert->starts_at?->format('Y-m-d\TH:i')" />
                <x-field name="ends_at" label="Stop showing (optional)" type="datetime-local" :value="$advert->ends_at?->format('Y-m-d\TH:i')" hint="Leave both empty to run continuously." />
                <button class="btn btn-primary btn-lg w-full" data-loading-text="Saving…"><x-icon name="check" class="size-4" /> {{ $advert->exists ? 'Save changes' : 'Create advert' }}</button>
            </div>
        </section>
    </div>
</div>
