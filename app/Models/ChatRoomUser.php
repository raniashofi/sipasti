<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatRoomUser extends Model
{
    protected $table = 'chat_room_users';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = ['room_id', 'user_id'];
    public $timestamps = false;

    protected $fillable = [
        'room_id',
        'user_id',
        'role_di_room',
        'bidang_id',
        'last_read_at',
        'sequence_number',
        'started_at',
        'ended_at',
        'is_active',
    ];

    protected $casts = [
        'last_read_at' => 'datetime',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function room()
    {
        return $this->belongsTo(ChatRoom::class, 'room_id');
    }
}
