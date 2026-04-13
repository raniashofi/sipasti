<?php

use App\Models\ChatRoom;
use App\Models\ChatRoomUser;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
*/

Broadcast::channel('chat.{roomId}', function (User $user, string $roomId) {
    $room = ChatRoom::with('tiket.opd')->find($roomId);
    if (!$room) return false;

    // OPD yang memiliki tiket ini
    if ($user->role === 'opd' && $room->tiket->opd?->user_id === $user->id) {
        return ['id' => $user->id, 'role' => 'opd'];
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
