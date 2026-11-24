<x-layouts.admin :title="$whatsappNumber->label" :subtitle="$whatsappNumber->display.' · '.$whatsappNumber->enquiry_types_count.' enquiry '.str('type')->plural($whatsappNumber->enquiry_types_count).' routed here'">
    <x-slot:actions>
        <a href="{{ route('admin.whatsapp-numbers.index') }}" class="btn btn-ghost"><x-icon name="arrow-left" class="size-4" /> WhatsApp numbers</a>
    </x-slot:actions>

    <form method="POST" action="{{ route('admin.whatsapp-numbers.update', $whatsappNumber) }}" class="card max-w-2xl animate-fade-up p-6 sm:p-8">
        @csrf
        @method('PUT')
        @include('admin.whatsapp-numbers._form')
        <div class="mt-6 flex justify-end">
            <button class="btn btn-primary" data-loading-text="Saving…"><x-icon name="check" class="size-4" /> Save changes</button>
        </div>
    </form>
</x-layouts.admin>
