<x-layouts.client title="Profile & security" subtitle="Keep your contact details up to date so our team can reach you.">
    <div class="grid max-w-5xl grid-cols-1 gap-6 xl:grid-cols-3">
        <section class="card animate-fade-up p-6 sm:p-8 xl:col-span-2">
            <h2 class="font-display text-xl text-ink-900">Personal details</h2>
            <p class="mt-1 text-sm text-ink-400">Updates and emails about your engaged properties are sent to this address.</p>
            <form method="POST" action="{{ route('account.profile.update') }}" class="mt-6 space-y-5">
                @csrf
                @method('PUT')
                <x-field name="name" label="Full name" :value="$user->name" bag="profile" required autocomplete="name" />
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <x-field name="email" label="Email" type="email" :value="$user->email" bag="profile" required autocomplete="email" />
                    <x-field name="phone" label="Phone" type="tel" :value="$user->phone" bag="profile" autocomplete="tel" placeholder="Best number to reach you" />
                </div>
                <div class="flex justify-end">
                    <button class="btn btn-primary" data-loading-text="Saving…"><x-icon name="check" class="size-4" /> Save details</button>
                </div>
            </form>
        </section>

        <aside class="card animate-fade-up p-6 [animation-delay:60ms]">
            <span class="grid size-14 place-items-center rounded-2xl bg-ink-900 font-display text-2xl text-brass-200">{{ str($user->name)->substr(0, 1)->upper() }}</span>
            <p class="mt-4 font-display text-xl text-ink-900">{{ $user->name }}</p>
            <p class="text-sm text-ink-400">Member since {{ $user->created_at->format('F Y') }}</p>
            <ul class="mt-6 space-y-3 text-sm text-ink-600">
                <li class="flex items-center gap-3"><x-icon name="mail" class="size-4 text-ink-300" /> Email updates on</li>
                <li class="flex items-center gap-3"><x-icon name="bell" class="size-4 text-ink-300" /> In-app notifications on</li>
            </ul>
        </aside>

        <section class="card animate-fade-up p-6 [animation-delay:120ms] sm:p-8 xl:col-span-2">
            <h2 class="font-display text-xl text-ink-900">Change password</h2>
            <p class="mt-1 text-sm text-ink-400">Use at least 8 characters.</p>
            <form method="POST" action="{{ route('account.profile.password') }}" class="mt-6 space-y-5">
                @csrf
                @method('PUT')
                <x-field name="current_password" label="Current password" type="password" bag="password" required autocomplete="current-password" />
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <x-field name="password" label="New password" type="password" bag="password" required autocomplete="new-password" />
                    <x-field name="password_confirmation" label="Confirm new password" type="password" bag="password" required autocomplete="new-password" />
                </div>
                <div class="flex justify-end">
                    <button class="btn btn-primary" data-loading-text="Updating…"><x-icon name="lock" class="size-4" /> Update password</button>
                </div>
            </form>
        </section>
    </div>
</x-layouts.client>
