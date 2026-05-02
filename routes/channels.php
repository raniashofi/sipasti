<?php

use App\Models\ChatRoom;
use App\Models\ChatRoomUser;
use App\Models\TimTeknis;
use App\Models\TiketTeknisi;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
*/

/*
 * Private notification channel — hanya pemilik akun yang boleh subscribe
 * Channel: notifications.{userId}
 */
Broadcast::channel('notifications.{userId}', function (User $user, string $userId) {
    return $user->id === $userId;
});

Broadcast::channel('chat.{roomId}', function (User $user, string $roomId) {
    $room = ChatRoom::with('tiket.opd')->find($roomId);
    if (!$room) return false;

    // OPD yang memiliki tiket ini
    if ($user->role === 'opd' && $room->tiket->opd?->user_id === $user->id) {
        return ['id' => $user->id, 'role' => 'opd'];
    }

    // Tim Teknis boleh subscribe ke room 'admin' (riwayat panduan remote) jika punya TiketTeknisi aktif
    if ($user->role === 'tim_teknis' && $room->nama_roomchat === 'admin') {
        $timTeknis = TimTeknis::where('user_id', $user->id)->first();
        if ($timTeknis && TiketTeknisi::where('tiket_id', $room->tiket_id)
                ->where('teknis_id', $timTeknis->id)
                ->where('status_tugas', 'aktif')
                ->exists()) {
            return ['id' => $user->id, 'role' => 'tim_teknis'];
        }
    }

    // Admin/Teknis yang sudah ditambahkan ke room
    $member = ChatRoomUser::where('room_id', $roomId)
                          ->where('user_id', $user->id)
                          ->first();

    if ($member) {
        return ['id' => $user->id, 'role' => $user->role];
    }

    return false;
});
