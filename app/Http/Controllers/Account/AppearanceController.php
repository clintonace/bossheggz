<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class AppearanceController extends Controller
{
    /**
     * Save the signed-in user's light / dark / system preference.
     */
    public function update(Request $request): RedirectResponse|Response
    {
        $validated = $request->validate([
            'mode' => ['required', Rule::in(['light', 'dark', 'system'])],
        ]);

        $request->user()->update(['theme_mode' => $validated['mode']]);

        if ($request->expectsJson()) {
            return response()->noContent();
        }

        return back()->with('success', 'Appearance updated.');
    }
}
