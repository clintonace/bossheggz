<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(Request $request): View
    {
        $this->rememberReturnPath($request);

        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'These credentials do not match our records.',
            ]);
        }

        $request->session()->regenerate();

        $home = $request->user()->isAdmin() ? route('admin.dashboard') : route('account.index');

        return redirect()->intended($home)->with('success', "Welcome back, {$request->user()->name}!");
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return to_route('home');
    }

    /**
     * Send the user back to the page they came from (e.g. a property) after signing in.
     */
    public static function rememberReturnPath(Request $request): void
    {
        $return = $request->query('return');

        if (is_string($return) && str_starts_with($return, '/') && ! str_starts_with($return, '//')) {
            $request->session()->put('url.intended', url($return));
        }
    }
}
