<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function create(Request $request): View
    {
        LoginController::rememberReturnPath($request);

        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:40'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = User::create($validated);

        Auth::login($user);

        $request->session()->regenerate();

        return redirect()->intended(route('account.index'))
            ->with('success', 'Your account is ready. Engage with any property to track its progress here.');
    }
}
