<?php

namespace App\Http\Controllers;

use App\Notifications\NewDirectMessageNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        return view('notifications.index', [
            'notifications' => $request->user()
                ->notifications()
                ->where('type', '!=', NewDirectMessageNotification::class)
                ->latest()
                ->paginate(25),
            'unreadCount' => $this->visibleUnreadNotifications($request)->count(),
        ]);
    }

    public function open(Request $request, DatabaseNotification $notification): RedirectResponse
    {
        abort_unless(
            $notification->notifiable_type === $request->user()::class
            && (int) $notification->notifiable_id === $request->user()->id,
            404
        );
        abort_if($notification->type === NewDirectMessageNotification::class, 404);

        $notification->markAsRead();
        $url = $notification->data['url'] ?? null;

        if (! is_string($url) || ! str_starts_with($url, url('/'))) {
            $url = route('notifications.index');
        }

        return redirect()->to($url);
    }

    public function readAll(Request $request): RedirectResponse
    {
        $this->visibleUnreadNotifications($request)->update(['read_at' => now()]);

        return back()->with('success', 'Todas las notificaciones se marcaron como leídas.');
    }

    public function destroyAll(Request $request): RedirectResponse
    {
        $request->user()
            ->notifications()
            ->where('type', '!=', NewDirectMessageNotification::class)
            ->delete();

        return back()->with('success', 'Todas las notificaciones se eliminaron.');
    }

    private function visibleUnreadNotifications(Request $request): mixed
    {
        return $request->user()
            ->unreadNotifications()
            ->where('type', '!=', NewDirectMessageNotification::class);
    }
}
