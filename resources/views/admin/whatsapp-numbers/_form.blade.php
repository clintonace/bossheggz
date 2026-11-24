<div class="space-y-5">
    <x-field name="label" label="Label" :value="$whatsappNumber->label" required maxlength="60" placeholder="e.g. Land banking desk" hint="Visitors see this when choosing where to send their enquiry." />
    <x-field name="number" label="WhatsApp number" type="tel" :value="$whatsappNumber->number ? $whatsappNumber->display : null" required placeholder="+234 811 170 8068" hint="International format including the country code." />
    <x-field name="sort_order" label="Position in the list" type="number" min="0" max="999" :value="$whatsappNumber->sort_order ?? 0" />
    <label class="flex cursor-pointer items-start gap-3 rounded-2xl bg-ivory p-4 ring-1 ring-ink-100">
        <input type="hidden" name="is_default" value="0">
        <input type="checkbox" name="is_default" value="1" class="mt-0.5 size-4 accent-brass-500" @checked(old('is_default', $whatsappNumber->is_default))>
        <span>
            <span class="block text-sm font-semibold text-ink-900">Default number</span>
            <span class="block text-xs text-ink-400">Used by every WhatsApp button on the website and by enquiry types without their own line.</span>
        </span>
    </label>
    <label class="flex cursor-pointer items-start gap-3 rounded-2xl bg-ivory p-4 ring-1 ring-ink-100">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" name="is_active" value="1" class="mt-0.5 size-4 accent-brass-500" @checked(old('is_active', $whatsappNumber->is_active ?? true))>
        <span>
            <span class="block text-sm font-semibold text-ink-900">Offer on the website</span>
            <span class="block text-xs text-ink-400">Turned-off numbers are hidden from visitors.</span>
        </span>
    </label>
</div>
