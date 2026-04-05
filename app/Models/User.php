<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * @property string $id
 * @property string $email
 * @property string $password
 * @property string|null $gambar
 * @property string $role
 */
class User extends Authenticatable
{
    protected $table = 'users';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'id','email','password','gambar','role'
    ];

    // RELASI
    public function opd()
    {
        return $this->hasOne(Opd::class);
    }

    public function adminHelpdesk()
    {
        return $this->hasOne(AdminHelpdesk::class);
    }

    public function timTeknis()
    {
        return $this->hasOne(TimTeknis::class);
    }

    public function messages()
    {
        return $this->hasMany(ChatMessage::class, 'sender_id');
    }

    public function chatRooms()
    {
        return $this->belongsToMany(ChatRoom::class, 'chat_room_users', 'user_id', 'room_id');
    }
}
