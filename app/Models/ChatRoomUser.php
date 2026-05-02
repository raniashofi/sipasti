<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatRoomUser extends Model
{
    protected $table = 'chat_room_users';

    protected $fillable = [
        'room_id',
        'user_id',
        'role_di_room',
        'last_read_at',
    ];

    protected $casts = [
        'last_read_at' => 'datetime',
    ];

    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function room()
    {
        return $this->belongsTo(ChatRoom::class, 'room_id');
    }
}
