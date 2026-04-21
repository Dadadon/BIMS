<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(): View
    {
        $notifications = auth()->user()
            ->notifications()
            ->paginate(30);

        return view('admin.notifications.index', compact('notifications'));
    }

    public function markRead(string $id): RedirectResponse
    {
        auth()->user()
            ->notifications()
            ->where('id', $id)
            ->first()
            ?->markAsRead();

        return back();
    }

    public function markAllRead(): RedirectResponse
    {
        auth()->user()->unreadNotifications->markAsRead();

        return back()->with('success', 'All notifications marked as read.');
    }

    public function destroy(string $id): RedirectResponse
    {
        auth()->user()
            ->notifications()
            ->where('id', $id)
            ->delete();

        return back()->with('success', 'Notification removed.');
    }

    public function recent(): JsonResponse
    {
        $user  = auth()->user();
        $items = $user->notifications()->latest()->limit(6)->get()->map(fn($n) => [
            'id'         => $n->id,
            'icon'       => $n->data['icon'] ?? 'bell',
            'message'    => $n->data['message'] ?? '',
            'url'        => $n->data['url'] ?? null,
            'read'       => ! is_null($n->read_at),
            'time'       => $n->created_at->diffForHumans(),
        ]);

        return response()->json([
            'items'        => $items,
            'unread_count' => $user->unreadNotifications()->count(),
        ]);
    }
}
