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
use App\Support\IdGenerator;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ChatController extends Controller
{
    private function teknisProfile(): ?TimTeknis
    {
        return TimTeknis::where('user_id', Auth::id())->first();
    }

    private function aktifkanUlangPenugasan(string $tiketId, ?string $teknisId): void
    {
        if (! $teknisId) {
            return;
        }

        if (TiketTeknisi::where('tiket_id', $tiketId)->where('teknis_id', $teknisId)->where('status_tugas', 'aktif')->exists()) {
            return;
        }

        $penugasanTerakhir = TiketTeknisi::where('tiket_id', $tiketId)
            ->where('teknis_id', $teknisId)
            ->where('status_tugas', 'selesai')
            ->latest('waktu_ditugaskan')
            ->first();

        if ($penugasanTerakhir) {
            TiketTeknisi::where('tiket_id', $tiketId)
                ->where('teknis_id', $teknisId)
                ->update([
                    'peran_teknisi'       => $penugasanTerakhir->peran_teknisi,
                    'waktu_ditugaskan'    => now(),
                    'status_tugas'        => 'aktif',
                    'alasan_dikembalikan' => null,
                ]);
        }
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
            $this->aktifkanUlangPenugasan($tiketId, $teknis?->id);
        }

        // Izinkan teknisi utama maupun pendamping, baik sesi aktif maupun riwayat.
        $assignment = TiketTeknisi::where('tiket_id', $tiketId)
            ->where('teknis_id', $teknis?->id)
            ->whereIn('status_tugas', ['aktif', 'selesai'])
            ->orderByRaw("CASE WHEN status_tugas = 'aktif' THEN 0 ELSE 1 END")
            ->latest('waktu_ditugaskan')
            ->first();

        abort_if(!$assignment, 403);

        $myPeran = $assignment->peran_teknisi;

        $tiket = Tiket::with(['opd', 'kategori', 'kb.kategori', 'latestStatus', 'statusTiket', 'buktiFoto'])
            ->findOrFail($tiketId);

        // ── Room Teknis ──
        $room = ChatRoom::firstOrCreate(
            ['tiket_id' => $tiket->id, 'nama_roomchat' => 'teknis'],
            []
        );

        // Tambahkan teknisi (utama & pendamping) ke room agar bisa subscribe channel
        ChatRoomUser::firstOrCreate(
            ['room_id' => $room->id, 'user_id' => Auth::id()],
            ['last_read_at' => now()]
        );
        // Update last_read_at setiap kali mengakses
        DB::table('chat_room_users')
            ->where('room_id', $room->id)
            ->where('user_id', Auth::id())
            ->update(['last_read_at' => now()]);

        // Tambahkan OPD ke room jika belum
        $opdUserId = $tiket->opd?->user_id;
        if ($opdUserId) {
            ChatRoomUser::firstOrCreate(
                ['room_id' => $room->id, 'user_id' => $opdUserId]
            );
        }

        $messages = $this->loadMessages($room->id);

        // ── Room Admin (riwayat panduan remote, hanya lihat) ──
        $adminRoom     = ChatRoom::where('tiket_id', $tiket->id)->where('nama_roomchat', 'admin')->first();
        if ($adminRoom) {
            ChatRoomUser::firstOrCreate(
                ['room_id' => $adminRoom->id, 'user_id' => Auth::id()],
                ['last_read_at' => now()]
            );
        }
        $adminMessages = $adminRoom ? $this->loadMessages($adminRoom->id) : collect();

        $bukaKembaliStatus = $tiket->statusTiket->where('status_tiket', 'dibuka_kembali')->last();

        // Tentukan apakah chat masih aktif (status harus 'perbaikan_teknis' atau 'dibuka_kembali')
        $latest = $tiket->statusTiket->sortByDesc('created_at')->first();
        $currentStatus = $latest?->status_tiket ?? 'verifikasi_admin';
        $chatIsActive = $currentStatus === 'perbaikan_teknis' || $currentStatus === 'dibuka_kembali';
        $canSend = $assignment->status_tugas === 'aktif' && $myPeran === 'teknisi_utama' && $chatIsActive;

        return view('tim_teknis.chat', compact(
            'tiket', 'room', 'messages',
            'adminRoom', 'adminMessages',
            'teknis', 'bukaKembaliStatus',
            'myPeran', 'canSend', 'chatIsActive'
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

        $tiket = Tiket::with('statusTiket')->whereHas('tiketTeknisi', fn($q) => $q
            ->where('teknis_id', $teknis?->id)
            ->where('status_tugas', 'aktif')
            ->where('peran_teknisi', 'teknisi_utama'))
            ->findOrFail($tiketId);

        // Periksa apakah chat masih aktif (status harus 'perbaikan_teknis' atau 'dibuka_kembali')
        $latest = $tiket->statusTiket->sortByDesc('created_at')->first();
        $currentStatus = $latest?->status_tiket ?? 'verifikasi_admin';
        $chatIsActive = $currentStatus === 'perbaikan_teknis' || $currentStatus === 'dibuka_kembali';

        // Jika chat sudah menjadi riwayat (tidak aktif), tolak permintaan
        if (!$chatIsActive) {
            return response()->json([
                'error' => 'Chat ini sudah menjadi riwayat dan tidak dapat menerima pesan baru.',
                'message' => 'Chat history - no new messages allowed'
            ], 403);
        }

        $room = ChatRoom::where('tiket_id', $tiket->id)
            ->where('nama_roomchat', 'teknis')
            ->firstOrFail();

        $request->validate([
            'konten' => 'required_without:file|nullable|string|max:2000',
            'file'   => 'nullable|file|mimes:jpg,jpeg,png|max:5120',
        ], [
            'file.max' => 'Gambar yang diupload terlalu besar. Maksimal 5 MB.',
        ]);

        $fileUrl    = null;
        $tipeKonten = 'text';

        if ($request->hasFile('file')) {
            $fileUrl    = $request->file('file')->store('chat/files', 'public');
            $tipeKonten = 'image';
        }

        $message = ChatMessage::create([
            'id'          => IdGenerator::make('MSG'),
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
