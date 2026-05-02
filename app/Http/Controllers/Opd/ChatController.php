<?php

namespace App\Http\Controllers\Opd;

use App\Events\NewChatMessage;
use App\Http\Controllers\Controller;
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
    /**
     * Tampilkan halaman chat (get/create room, load messages).
     */
    public function show(string $tiketId, Request $request)
    {
        $user  = Auth::user();
        $opd   = $user->opd;
        $tiket = Tiket::with('statusTiket')->where('opd_id', $opd->id)->findOrFail($tiketId);

        $type = in_array($request->query('type'), ['admin', 'teknis'])
            ? $request->query('type')
            : 'admin';

        // Buat atau temukan room sesuai tipe
        $room = ChatRoom::firstOrCreate(
            ['tiket_id' => $tiket->id, 'nama_roomchat' => $type],
            ['id' => 'ROOM-' . strtoupper(Str::random(10))]
        );

        // Tambahkan OPD ke room jika belum ada
        ChatRoomUser::firstOrCreate(
            ['room_id' => $room->id, 'user_id' => $user->id],
            ['role_di_room' => 'opd']
        );

        // Load pesan dengan info pengirim
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

        $bukaKembaliStatus = $tiket->statusTiket->where('status_tiket', 'dibuka_kembali')->last();

        return view('opd.pengaduan-saya.chat', compact('tiket', 'room', 'messages', 'type', 'bukaKembaliStatus'));
    }

    /**
     * Kirim pesan baru (AJAX).
     */
    public function send(Request $request, string $tiketId)
    {
        $user  = Auth::user();
        $opd   = $user->opd;
        $tiket = Tiket::where('opd_id', $opd->id)->findOrFail($tiketId);

        $type = in_array($request->input('type'), ['admin', 'teknis'])
            ? $request->input('type')
            : 'admin';

        $room = ChatRoom::where('tiket_id', $tiket->id)
                        ->where('nama_roomchat', $type)
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
            'sender_id'   => $user->id,
            'konten'      => $request->input('konten'),
            'file_url'    => $fileUrl,
            'tipe_konten' => $tipeKonten,
        ]);

        $senderName = $user->opd?->nama_opd
            ?? $user->adminHelpdesk?->nama_lengkap
            ?? $user->timTeknis?->nama_lengkap
            ?? 'Pengguna';

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
