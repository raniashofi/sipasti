<?php

namespace App\Http\Controllers\AdminHelpdesk;

use App\Events\NewChatMessage;
use App\Http\Controllers\Controller;
use App\Models\AdminHelpdesk;
use App\Models\ChatMessage;
use App\Models\ChatRoom;
use App\Models\ChatRoomUser;
use App\Models\Tiket;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    private function adminProfile(): ?AdminHelpdesk
    {
        return AdminHelpdesk::with('bidang')->where('user_id', Auth::id())->first();
    }

    /**
     * Tampilkan halaman chat admin dengan OPD untuk tiket panduan_remote.
     */
    public function show(string $tiketId)
    {
        $admin = $this->adminProfile();

        $tiket = Tiket::with(['opd', 'kategori', 'kb.kategori', 'latestStatus'])
            ->where('admin_id', $admin?->id)
            ->whereHas('latestStatus', fn($q) => $q->where('status_tiket', 'panduan_remote'))
            ->findOrFail($tiketId);

        // Buat atau temukan room dengan tipe 'admin'
        $room = ChatRoom::firstOrCreate(
            ['tiket_id' => $tiket->id, 'nama_roomchat' => 'admin'],
            ['id' => 'ROOM-' . strtoupper(Str::random(10))]
        );

        // Tambahkan admin ke room jika belum
        ChatRoomUser::firstOrCreate(
            ['room_id' => $room->id, 'user_id' => Auth::id()],
            ['role_di_room' => 'admin_helpdesk']
        );

        // Tambahkan OPD ke room jika belum
        $opdUserId = $tiket->opd?->user_id;
        if ($opdUserId) {
            ChatRoomUser::firstOrCreate(
                ['room_id' => $room->id, 'user_id' => $opdUserId],
                ['role_di_room' => 'opd']
            );
        }

        $messages = ChatMessage::where('room_id', $room->id)
            ->with(['sender.opd', 'sender.adminHelpdesk', 'sender.timTeknis'])
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(fn($msg) => [
                'id'          => $msg->id,
                'sender_id'   => $msg->sender_id,
                'konten'      => $msg->konten,
                'file_url'    => $msg->file_url ? Storage::url($msg->file_url) : null,
                'tipe_konten' => $msg->tipe_konten,
                'created_at'  => Carbon::parse($msg->created_at)->format('H:i'),
                'sender_name' => $msg->sender->opd?->nama_opd
                    ?? $msg->sender->adminHelpdesk?->nama_lengkap
                    ?? $msg->sender->timTeknis?->nama_lengkap
                    ?? 'Pengguna',
            ])
            ->values();

        return view('admin_helpdesk.chat', compact('tiket', 'room', 'messages', 'admin'));
    }

    /**
     * Kirim pesan (AJAX).
     */
    public function send(Request $request, string $tiketId)
    {
        $admin = $this->adminProfile();

        $tiket = Tiket::where('admin_id', $admin?->id)->findOrFail($tiketId);

        $room = ChatRoom::where('tiket_id', $tiket->id)
                        ->where('nama_roomchat', 'admin')
                        ->firstOrFail();

        $request->validate([
            'konten' => 'required_without:file|nullable|string|max:2000',
            'file'   => 'nullable|file|mimes:jpg,jpeg,png|max:10240',
        ]);

        $fileUrl    = null;
        $tipeKonten = 'text';

        if ($request->hasFile('file')) {
            $fileUrl    = $request->file('file')->store('chat/files', 'public');
            $tipeKonten = 'image';
        }

        $message = ChatMessage::create([
            'id'          => 'MSG-' . strtoupper(Str::random(10)),
            'room_id'     => $room->id,
            'sender_id'   => Auth::id(),
            'konten'      => $request->input('konten'),
            'file_url'    => $fileUrl,
            'tipe_konten' => $tipeKonten,
        ]);

        $senderName = Auth::user()->adminHelpdesk?->nama_lengkap
            ?? Auth::user()->email;

        broadcast(new NewChatMessage($message, $senderName));

        return response()->json([
            'id'          => $message->id,
            'sender_id'   => $message->sender_id,
            'konten'      => $message->konten,
            'file_url'    => $fileUrl ? Storage::url($fileUrl) : null,
            'tipe_konten' => $message->tipe_konten,
            'created_at'  => Carbon::parse($message->created_at)->format('H:i'),
            'sender_name' => $senderName,
        ]);
    }
}
