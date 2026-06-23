<?php

namespace App\Http\Controllers\Opd;

use App\Events\NewChatMessage;
use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Models\ChatRoom;
use App\Models\ChatRoomUser;
use App\Models\Tiket;
use App\Support\IdGenerator;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ChatController extends Controller
{
    /**
     * Tampilkan halaman chat (get/create room, load messages).
     */
    public function show(string $tiketId, Request $request)
    {
        $user  = $this->authenticatedUser();
        $opd   = $user->opd;
        $tiket = $this->findTiketForOpd($opd->id, $tiketId, ['statusTiket', 'buktiFoto']);

        $type = $this->validRoomType($request->query('type'));
        $roomBidangId = $tiket->bidang_id;

        // Buat atau temukan room sesuai tipe
        $room = $this->firstOrCreateRoom($tiket->id, $type);

        // Tambahkan OPD ke room jika belum ada
        $this->firstOrCreateRoomUser($room->id, $user->id, $roomBidangId);
        $this->markRoomAsRead($room->id, $user->id);

        // Load pesan dengan info pengirim
        $messages = $this->loadRoomMessages($room->id);

        $bukaKembaliStatus = $tiket->statusTiket->where('status_tiket', 'dibuka_kembali')->last();

        // Tentukan apakah chat masih aktif atau sudah menjadi riwayat (history)
        $allStatuses    = $tiket->statusTiket;
        $latest         = $tiket->statusTiket->sortByDesc('created_at')->first();
        $currentStatus  = $latest?->status_tiket ?? 'verifikasi_admin';
        $statusList     = $allStatuses->pluck('status_tiket')->toArray();

        // Admin chat aktif hanya jika status saat ini adalah 'panduan_remote'
        $adminChatActive = $currentStatus === 'panduan_remote';

        // Teknis chat aktif jika status adalah 'perbaikan_teknis' atau 'dibuka_kembali'
        $teknisChatActive = $currentStatus === 'perbaikan_teknis' || $currentStatus === 'dibuka_kembali';

        // Tentukan apakah chat saat ini aktif atau tidak berdasarkan tipe
        $chatIsActive = $type === 'admin' ? $adminChatActive : $teknisChatActive;

        return $this->renderView('opd.pengaduan-saya.chat', compact('tiket', 'room', 'messages', 'type', 'bukaKembaliStatus', 'chatIsActive'));
    }

    /**
     * Kirim pesan baru (AJAX).
     */
    public function send(Request $request, string $tiketId)
    {
        $user  = $this->authenticatedUser();
        $opd   = $user->opd;
        $tiket = $this->findTiketForOpd($opd->id, $tiketId, ['statusTiket']);

        $type = $this->validRoomType($request->input('type'));

        // Periksa apakah chat masih aktif
        $allStatuses    = $tiket->statusTiket;
        $latest         = $tiket->statusTiket->sortByDesc('created_at')->first();
        $currentStatus  = $latest?->status_tiket ?? 'verifikasi_admin';

        // Admin chat aktif hanya jika status saat ini adalah 'panduan_remote'
        $adminChatActive = $currentStatus === 'panduan_remote';

        // Teknis chat aktif jika status adalah 'perbaikan_teknis' atau 'dibuka_kembali'
        $teknisChatActive = $currentStatus === 'perbaikan_teknis' || $currentStatus === 'dibuka_kembali';

        // Tentukan apakah chat saat ini aktif atau tidak berdasarkan tipe
        $chatIsActive = $type === 'admin' ? $adminChatActive : $teknisChatActive;

        // Jika chat sudah menjadi riwayat (tidak aktif), tolak permintaan
        if (!$chatIsActive) {
            return $this->jsonResponse([
                'error' => 'Chat ini sudah menjadi riwayat dan tidak dapat menerima pesan baru.',
                'message' => 'Chat history - no new messages allowed'
            ], 403);
        }

        $room = $this->findRoomOrFail($tiket->id, $type);

        $request->validate([
            'konten' => 'required_without:file|nullable|string|max:2000',
            'file'   => 'nullable|file|mimes:jpg,jpeg,png|max:5120',
        ], [
            'file.max' => 'Gambar yang diupload terlalu besar. Maksimal 5 MB.',
        ]);

        $fileUrl    = null;
        $tipeKonten = 'text';

        if ($request->hasFile('file')) {
            $fileUrl    = $this->storeChatFile($request->file('file'));
            $tipeKonten = 'image';
        }

        $message = $this->createMessage([
            'id'          => IdGenerator::make('MSG'),
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

        $this->broadcastMessage($message, $senderName);

        return $this->jsonResponse([
            'id'          => $message->id,
            'sender_id'   => $message->sender_id,
            'konten'      => $message->konten,
            'file_url'    => $fileUrl ? $this->storageUrl($fileUrl) : null,
            'tipe_konten' => $message->tipe_konten,
            'created_at'  => Carbon::parse($message->created_at)->format('H:i'),
            'sender_name' => $senderName,
        ]);
    }

    protected function authenticatedUser()
    {
        return Auth::user();
    }

    protected function validRoomType(?string $type): string
    {
        return in_array($type, ['admin', 'teknis']) ? $type : 'admin';
    }

    protected function findTiketForOpd(string $opdId, string $tiketId, array $with)
    {
        return Tiket::with($with)->where('opd_id', $opdId)->findOrFail($tiketId);
    }

    protected function firstOrCreateRoom(string $tiketId, string $type)
    {
        return ChatRoom::firstOrCreate(
            ['tiket_id' => $tiketId, 'nama_roomchat' => $type],
            []
        );
    }

    protected function firstOrCreateRoomUser(string $roomId, string $userId, ?string $bidangId): void
    {
        ChatRoomUser::firstOrCreate(
            ['room_id' => $roomId, 'user_id' => $userId],
            ['role_di_room' => 'opd', 'bidang_id' => $bidangId]
        );
    }

    protected function markRoomAsRead(string $roomId, string $userId): void
    {
        DB::table('chat_room_users')
            ->where('room_id', $roomId)
            ->where('user_id', $userId)
            ->update(['last_read_at' => now()]);
    }

    protected function loadRoomMessages(string $roomId)
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
                    ?? $msg->sender->adminHelpdesk?->nama_lengkap
                    ?? $msg->sender->timTeknis?->nama_lengkap
                    ?? 'Pengguna',
            ])
            ->values();
    }

    protected function findRoomOrFail(string $tiketId, string $type)
    {
        return ChatRoom::where('tiket_id', $tiketId)
                       ->where('nama_roomchat', $type)
                       ->firstOrFail();
    }

    protected function storeChatFile($file): string
    {
        return $file->store('chat/files', 'public');
    }

    protected function createMessage(array $attributes)
    {
        return ChatMessage::create($attributes);
    }

    protected function broadcastMessage($message, string $senderName): void
    {
        broadcast(new NewChatMessage($message, $senderName));
    }

    protected function storageUrl(string $path): string
    {
        return Storage::url($path);
    }

    protected function renderView(string $view, array $data)
    {
        return view($view, $data);
    }

    protected function jsonResponse(array $data, int $status = 200)
    {
        return response()->json($data, $status);
    }
}
