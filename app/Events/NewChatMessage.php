<?php

namespace App\Events;

use App\Models\ChatMessage;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Support\Facades\Storage;

class NewChatMessage implements ShouldBroadcastNow
{
    public function __construct(
        public readonly ChatMessage $message,
        public readonly string $senderName,
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('chat.' . $this->message->room_id)];
    }

    public function broadcastAs(): string
    {
        return 'NewChatMessage';
    }

    public function broadcastWith(): array
    {
        return [
            'id'          => $this->message->id,
            'sender_id'   => $this->message->sender_id,
            'konten'      => $this->message->konten,
            'file_url'    => $this->message->file_url
                                ? Storage::url($this->message->file_url)
                                : null,
            'tipe_konten' => $this->message->tipe_konten,
            'created_at'  => \Carbon\Carbon::parse($this->message->created_at)->format('H:i'),
            'sender_name' => $this->senderName,
        ];
    }
}
