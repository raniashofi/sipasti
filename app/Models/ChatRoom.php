<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatRoom extends Model
{
    protected $table = 'chat_room';
    public $incrementing = false;
    protected $keyType = 'string';

    public function tiket()
    {
        return $this->belongsTo(Tiket::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'chat_room_users', 'room_id', 'user_id');
    }

    public function messages()
    {
        return $this->hasMany(ChatMessage::class, 'room_id');
    }
}
