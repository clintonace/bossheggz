<x-layouts.auth title="Sign in" heading="Welcome back" subheading="Sign in to see your engaged properties and their progress.">
    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf
        <x-field name="email" label="Email" type="email" required autofocus autocomplete="email" placeholder="you@example.com" />
        <x-field name="password" label="Password" type="password" required autocomplete="current-password" placeholder="••••••••" />

        <label class="flex items-center gap-2.5 text-sm text-ink-600">
            <input type="checkbox" name="remember" class="size-4 rounded border-ink-300 accent-ink-900">
            Keep me signed in
        </label>

        <button class="btn btn-primary btn-lg w-full" data-loading-text="Signing in…">Sign in <x-icon name="arrow-right" class="size-4" /></button>
    </form>

    <p class="mt-8 text-center text-sm text-ink-500">
        New here? <a href="{{ route('register', array_filter(['return' => request('return')])) }}" class="font-semibold text-brass-600 hover:text-brass-700">Create a free account</a>
    </p>
</x-layouts.auth>
