<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        return view('account.notifications', [
            'notifications' => $request->user()->notifications()->paginate(15),
            'unreadCount' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    /**
     * Mark a notification as read and follow its link.
     */
    public function show(Request $request, string $notification): RedirectResponse
    {
        $notification = $request->user()->notifications()->findOrFail($notification);

        $notification->markAsRead();

        return redirect($notification->data['url'] ?? route('account.index'));
    }

    public function readAll(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return back()->with('success', 'All notifications marked as read.');
    }
}
