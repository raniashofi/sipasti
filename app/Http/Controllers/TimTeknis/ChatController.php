<?php

namespace App\Http\Controllers\TimTeknis;

use App\Events\NewChatMessage;
use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Models\ChatRoom;
use App\Models\ChatRoomUser;
use App\Models\StatusTiket;
use App\Models\Tiket;
use App\Models\TiketTeknisi;
use App\Models\TimTeknis;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    private function teknisProfile(): ?TimTeknis
    {
        return TimTeknis::where('user_id', Auth::id())->first();
    }

    /**
     * Tampilkan halaman chat tim teknis dengan OPD.
     * Teknisi utama: bisa kirim pesan di room teknis.
     * Teknisi pendamping: hanya lihat room teknis + riwayat chat admin (panduan remote).
     */
    public function show(string $tiketId)
    {
        $teknis = $this->teknisProfile();

        // Self-heal: dibuka_kembali tickets whose TiketTeknisi wasn't flipped back to aktif
        $latestStatusTiket = StatusTiket::where('tiket_id', $tiketId)
            ->orderByDesc('created_at')
            ->value('status_tiket');
        if ($latestStatusTiket === 'dibuka_kembali') {
            TiketTeknisi::where('tiket_id', $tiketId)
                ->where('teknis_id', $teknis?->id)
                ->where('status_tugas', 'selesai')
                ->update(['status_tugas' => 'aktif']);
        }

        // Izinkan teknisi utama maupun pendamping
        $assignment = TiketTeknisi::where('tiket_id', $tiketId)
            ->where('teknis_id', $teknis?->id)
            ->where('status_tugas', 'aktif')
            ->first();

        abort_if(!$assignment, 403);

        $myPeran = $assignment->peran_teknisi;
        $canSend = $myPeran === 'teknisi_utama';

        $tiket = Tiket::with(['opd', 'kategori', 'kb.kategori', 'latestStatus', 'statusTiket'])
            ->findOrFail($tiketId);

        // ── Room Teknis ──
        $room = ChatRoom::firstOrCreate(
            ['tiket_id' => $tiket->id, 'nama_roomchat' => 'teknis'],
            ['id' => 'ROOM-' . strtoupper(Str::random(10))]
        );

        // Tambahkan teknisi (utama & pendamping) ke room agar bisa subscribe channel
        $roomUser = ChatRoomUser::firstOrCreate(
            ['room_id' => $room->id, 'user_id' => Auth::id()],
            ['role_di_room' => 'tim_teknis']
        );
        $roomUser->update(['last_read_at' => now()]);

        // Tambahkan OPD ke room jika belum
        $opdUserId = $tiket->opd?->user_id;
        if ($opdUserId) {
            ChatRoomUser::firstOrCreate(
                ['room_id' => $room->id, 'user_id' => $opdUserId],
                ['role_di_room' => 'opd']
            );
        }

        $messages = $this->loadMessages($room->id);

        // ── Room Admin (riwayat panduan remote, hanya lihat) ──
        $adminRoom     = ChatRoom::where('tiket_id', $tiket->id)->where('nama_roomchat', 'admin')->first();
        $adminMessages = $adminRoom ? $this->loadMessages($adminRoom->id) : collect();

        $bukaKembaliStatus = $tiket->statusTiket->where('status_tiket', 'dibuka_kembali')->last();

        return view('tim_teknis.chat', compact(
            'tiket', 'room', 'messages',
            'adminRoom', 'adminMessages',
            'teknis', 'bukaKembaliStatus',
            'myPeran', 'canSend'
        ));
    }

    private function loadMessages(string $roomId): \Illuminate\Support\Collection
    {
        return ChatMessage::where('room_id', $roomId)
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
                    ?? $msg->sender->timTeknis?->nama_lengkap
                    ?? $msg->sender->adminHelpdesk?->nama_lengkap
                    ?? 'Pengguna',
            ])
            ->values();
    }

    /**
     * Kirim pesan (AJAX).
     */
    public function send(Request $request, string $tiketId)
    {
        $teknis = $this->teknisProfile();

        $tiket = Tiket::whereHas('tiketTeknisi', fn($q) => $q
            ->where('teknis_id', $teknis?->id)
            ->where('status_tugas', 'aktif')
            ->where('peran_teknisi', 'teknisi_utama'))
            ->findOrFail($tiketId);

        $room = ChatRoom::where('tiket_id', $tiket->id)
            ->where('nama_roomchat', 'teknis')
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

        $senderName = $teknis?->nama_lengkap ?? Auth::user()->email;

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
