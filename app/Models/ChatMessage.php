<?php

namespace App\Models;

use App\Models\Concerns\HasPrefixedId;
use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    use HasPrefixedId;

    protected $table = 'chat_messages';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    protected string $idPrefix = 'MSG';

    protected $fillable = [
        'id','room_id','sender_id','konten','file_url','tipe_konten'
    ];

    public function room()
    {
        return $this->belongsTo(ChatRoom::class, 'room_id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
