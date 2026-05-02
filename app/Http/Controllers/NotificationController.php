<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Menangani request HTTP untuk fitur notifikasi.
 *
 * Pengiriman notifikasi TIDAK dilakukan di sini — gunakan Notification class:
 *   $user->notify(new TiketMasukNotification(...));
 *   $user->notify(new StatusTiketNotification(...));
 *   $user->notify(new TugasBaruNotification(...));
 */
class NotificationController extends Controller
{
    /**
     * GET /notif
     * JSON (Accept: application/json) → data untuk notification bell.
     * HTML → halaman riwayat notifikasi lengkap.
     */
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $notifications = $user->notifications()
            ->latest()
            ->take(20)
            ->get();

        $unreadCount = $user->unreadNotifications()->count();

        if ($request->expectsJson()) {
            return response()->json([
                'notifications' => $notifications->map(fn ($n) => [
                    'id'         => $n->id,
                    'icon'       => $n->data['icon']  ?? 'default',
                    'title'      => $n->data['title'] ?? '',
                    'body'       => $n->data['body']  ?? '',
                    'url'        => $n->data['url']   ?? null,
                    'read'       => $n->read_at !== null,
                    'created_at' => $n->created_at?->diffForHumans() ?? '',
                ]),
                'unread_count' => $unreadCount,
            ]);
        }

        return view('notifikasi.index', compact('notifications', 'unreadCount'));
    }

    /**
     * POST /notif/{id}/read
     * Tandai satu notifikasi sebagai sudah dibaca.
     */
    public function markRead(string $id): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        /** @var \Illuminate\Notifications\DatabaseNotification $notification */
        $notification = $user->notifications()
            ->where('id', $id)
            ->firstOrFail();

        $notification->markAsRead();

        return response()->json(['ok' => true]);
    }

    /**
     * POST /notif/read-all
     * Tandai semua notifikasi yang belum dibaca sebagai sudah dibaca.
     */
    public function markAllRead(): JsonResponse
    {
        Auth::user()->unreadNotifications->markAsRead();

        return response()->json(['ok' => true]);
    }
}
