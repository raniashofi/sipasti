<?php

namespace App\Models;

use App\Models\Concerns\HasPrefixedId;
use Illuminate\Database\Eloquent\Model;

class ChatRoom extends Model
{
    use HasPrefixedId;

    protected $table = 'chat_room';
    public $incrementing = false;
    protected $keyType = 'string';
    protected string $idPrefix = 'ROOM';

    protected $fillable = ['id', 'tiket_id', 'nama_roomchat', 'is_active', 'transferred_at'];

    protected $casts = [
        'is_active' => 'boolean',
        'transferred_at' => 'datetime',
    ];

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

    // Get active admin (dari pivot, role = admin_helpdesk dan is_active = true)
    public function getActiveAdmin()
    {
        return $this->users()
            ->wherePivot('role_di_room', 'admin_helpdesk')
            ->wherePivot('is_active', true)
            ->first();
    }

    // Get all admin history (dari pivot, role = admin_helpdesk dan is_active = false)
    public function getAdminHistory()
    {
        return $this->users()
            ->wherePivot('role_di_room', 'admin_helpdesk')
            ->wherePivot('is_active', false)
            ->orderByPivot('sequence_number', 'desc')
            ->get();
    }

    // Get all admins (active + history)
    public function getAllAdmins()
    {
        return $this->users()
            ->wherePivot('role_di_room', 'admin_helpdesk')
            ->orderByPivot('sequence_number')
            ->get();
    }
}
