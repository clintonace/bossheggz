<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DevAccountSwitchController extends Controller
{
    /**
     * Sign in as the given user without a password (local testing only).
     */
    public function __invoke(Request $request, User $user): RedirectResponse
    {
        abort_unless(config('listings.dev_account_switcher'), 404);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect($user->isAdmin() ? route('admin.dashboard') : route('account.index'))
            ->with('success', "Signed in as {$user->name} (".($user->isAdmin() ? 'admin' : 'client').').');
    }
}
