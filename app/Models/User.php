<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * @property string $id
 * @property string $email
 * @property string $password
 * @property string|null $gambar
 * @property string $role
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection $notifications
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection $unreadNotifications
 */
class User extends Authenticatable
{
    use Notifiable;
    protected $table = 'users';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'id','email','password','gambar','role','last_login_at'
    ];

    protected $casts = [
        'last_login_at' => 'datetime',
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

    /**
     * Channel broadcast untuk notifikasi real-time via Reverb.
     * Frontend subscribe ke: private-notifications.{userId}
     */
    public function receivesBroadcastNotificationsOn(): string
    {
        return 'notifications.' . $this->id;
    }
}
