<x-layouts.auth title="Create account" heading="Create your account" subheading="Track every property you engage with and get updates by email.">
    <form method="POST" action="{{ route('register') }}" class="space-y-5">
        @csrf
        <x-field name="name" label="Full name" required autofocus autocomplete="name" placeholder="Jane Doe" />
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
            <x-field name="email" label="Email" type="email" required autocomplete="email" placeholder="you@example.com" />
            <x-field name="phone" label="Phone (optional)" type="tel" autocomplete="tel" placeholder="+234…" />
        </div>
        <x-field name="password" label="Password" type="password" required autocomplete="new-password" hint="At least 8 characters." />
        <x-field name="password_confirmation" label="Confirm password" type="password" required autocomplete="new-password" />

        <button class="btn btn-primary btn-lg w-full" data-loading-text="Creating account…">Create account <x-icon name="arrow-right" class="size-4" /></button>
    </form>

    <p class="mt-8 text-center text-sm text-ink-500">
        Already have an account? <a href="{{ route('login', array_filter(['return' => request('return')])) }}" class="font-semibold text-brass-600 hover:text-brass-700">Sign in</a>
    </p>
</x-layouts.auth>
