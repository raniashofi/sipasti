<?php

namespace App\Http\Controllers\AdminHelpdesk;

use App\Events\NewChatMessage;
use App\Http\Controllers\Controller;
use App\Models\AdminHelpdesk;
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
     * Tampilkan halaman chat admin dengan OPD untuk tiket panduan_remote.
     */
    public function show(string $tiketId)
    {
        $admin = $this->findAdminProfile();
        $tiket = $this->findTiketForAdmin($tiketId, $admin?->id);

        $latest = $tiket->statusTiket->sortByDesc('created_at')->first();
        $currentStatus = $latest?->status_tiket ?? 'verifikasi_admin';
        $chatIsActive = $currentStatus === 'panduan_remote';

        $room = $chatIsActive
            ? $this->firstOrCreateRoom($tiket->id)
            : $this->findExistingRoom($tiket->id);

        $userId = $this->authenticatedUserId();
        $this->ensureRoomUser($room->id, $userId);
        $this->markRoomAsRead($room->id, $userId);

        $opdUserId = $tiket->opd?->user_id;
        if ($opdUserId) {
            $this->ensureRoomUser($room->id, $opdUserId);
        }

        $messages = $this->loadRoomMessages($room->id);

        return $this->renderView('admin_helpdesk.chat', compact('tiket', 'room', 'messages', 'admin', 'chatIsActive'));
    }

    /**
     * Kirim pesan (AJAX).
     */
    public function send(Request $request, string $tiketId)
    {
        $admin = $this->findAdminProfile();
        $tiket = $this->findTiketForAdmin($tiketId, $admin?->id);

        $latest = $tiket->statusTiket->sortByDesc('created_at')->first();
        $currentStatus = $latest?->status_tiket ?? 'verifikasi_admin';
        $chatIsActive = $currentStatus === 'panduan_remote';

        if (!$chatIsActive) {
            return $this->jsonResponse([
                'error' => 'Chat ini sudah menjadi riwayat dan tidak dapat menerima pesan baru.',
                'message' => 'Chat history - no new messages allowed'
            ], 403);
        }

        $room = $this->findExistingRoom($tiket->id);

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

        $message = $this->createMessage([
            'id'          => IdGenerator::make('MSG'),
            'room_id'     => $room->id,
            'sender_id'   => $this->authenticatedUserId(),
            'konten'      => $request->input('konten'),
            'file_url'    => $fileUrl,
            'tipe_konten' => $tipeKonten,
        ]);

        $senderName = $this->senderName();
        $this->broadcastMessage($message, $senderName);

        return $this->jsonResponse([
            'id'          => $message->id,
            'sender_id'   => $message->sender_id,
            'konten'      => $message->konten,
            'file_url'    => $fileUrl ? Storage::url($fileUrl) : null,
            'tipe_konten' => $message->tipe_konten,
            'created_at'  => Carbon::parse($message->created_at)->format('H:i'),
            'sender_name' => $senderName,
        ]);
    }

    // ── Protected methods (overridable for unit testing) ─────

    protected function authenticatedUserId(): ?string
    {
        return Auth::id();
    }

    protected function findAdminProfile()
    {
        return AdminHelpdesk::with('bidang')->where('user_id', $this->authenticatedUserId())->first();
    }

    protected function findTiketForAdmin(string $tiketId, ?string $adminId)
    {
        return Tiket::with(['opd', 'kategori', 'kb.kategori', 'latestStatus', 'statusTiket', 'buktiFoto'])
            ->where('admin_id', $adminId)->findOrFail($tiketId);
    }

    protected function firstOrCreateRoom(string $tiketId)
    {
        return ChatRoom::firstOrCreate(
            ['tiket_id' => $tiketId, 'nama_roomchat' => 'admin'],
            []
        );
    }

    protected function findExistingRoom(string $tiketId)
    {
        return ChatRoom::where('tiket_id', $tiketId)->where('nama_roomchat', 'admin')->firstOrFail();
    }

    protected function ensureRoomUser(string $roomId, string $userId): void
    {
        ChatRoomUser::firstOrCreate(
            ['room_id' => $roomId, 'user_id' => $userId]
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

    protected function createMessage(array $attributes)
    {
        return ChatMessage::create($attributes);
    }

    protected function senderName(): string
    {
        return Auth::user()->adminHelpdesk?->nama_lengkap ?? Auth::user()->email;
    }

    protected function broadcastMessage($message, string $senderName): void
    {
        broadcast(new NewChatMessage($message, $senderName));
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
