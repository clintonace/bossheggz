<div class="space-y-5">
    <x-field name="name" label="Name" :value="$type->name" required maxlength="80" placeholder="e.g. Mortgage advice" />
    <x-field name="description" label="Description (optional)" :value="$type->description" maxlength="200" hint="For your team — not shown on the website." />
    <div>
        <label for="whatsapp_number_id-{{ $type->id ?? 'new' }}" class="field-label">WhatsApp line</label>
        <select name="whatsapp_number_id" id="whatsapp_number_id-{{ $type->id ?? 'new' }}" class="field-input appearance-none">
            <option value="">Default number</option>
            @foreach ($whatsappNumbers as $whatsappNumber)
                <option value="{{ $whatsappNumber->id }}" @selected((string) old('whatsapp_number_id', $type->whatsapp_number_id) === (string) $whatsappNumber->id)>{{ $whatsappNumber->label }} · {{ $whatsappNumber->display }}{{ $whatsappNumber->is_active ? '' : ' (off)' }}</option>
            @endforeach
        </select>
        <p class="mt-1.5 text-xs text-ink-400">Visitors who pick this type are offered this number when sending on WhatsApp.</p>
        @error('whatsapp_number_id') <p class="field-error">{{ $message }}</p> @enderror
    </div>
    <x-field name="sort_order" label="Position in the list" type="number" min="0" max="999" :value="$type->sort_order ?? 0" hint="Lower numbers appear first." />
    @if ($type->is_system)
        <p class="flex gap-2 rounded-2xl bg-ivory p-4 text-xs leading-relaxed text-ink-500 ring-1 ring-ink-100">
            <x-icon name="lock" class="size-4 shrink-0 text-ink-400" />
            Built-in type used by the website's own forms — it can be renamed but is always on.
        </p>
        <input type="hidden" name="is_active" value="1">
    @else
        <label class="flex cursor-pointer items-start gap-3 rounded-2xl bg-ivory p-4 ring-1 ring-ink-100">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" class="mt-0.5 size-4 accent-brass-500" @checked(old('is_active', $type->is_active ?? true))>
            <span>
                <span class="block text-sm font-semibold text-ink-900">Show on the contact form</span>
                <span class="block text-xs text-ink-400">Turn off to hide it without losing past enquiries.</span>
            </span>
        </label>
    @endif
</div>
