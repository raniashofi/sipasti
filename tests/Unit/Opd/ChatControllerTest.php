<?php

namespace Tests\Unit\Opd;

use App\Http\Controllers\Opd\ChatController;
use Illuminate\Http\Request;
use Tests\TestCase;

class ChatControllerTest extends TestCase
{

    public function testShowTolakNonOpd()
    {
        $this->actingAs(new \App\Models\User(['id' => 999, 'role' => 'admin_helpdesk']));
        $this->assertTrue(
            in_array($this->get('/opd/pengaduan-saya/FAKE/chat')->status(), [302, 403, 404]),
            'show() — role non-opd ditolak akses chat'
        );
    }
    public function testShowChatAdminAktif(): void
    {
        $controller = new FakeOpdChatController();
        $controller->mockUser = FakeChatUser::opd();
        $controller->mockTiket = FakeChatTiket::withStatuses(['panduan_remote']);
        $controller->mockRoom = (object) ['id' => 'ROOM-001', 'nama_roomchat' => 'admin'];
        $controller->mockMessages = collect([['id' => 'MSG-001', 'konten' => 'Halo']]);

        $result = $controller->show('TKT-001', Request::create('/chat', 'GET', ['type' => 'admin']));

        $this->assertSame('opd.pengaduan-saya.chat', $result['view']);
        $this->assertSame('admin', $result['data']['type']);
        $this->assertTrue($result['data']['chatIsActive']);
        $this->assertTrue($controller->roomUserCreated);
        $this->assertTrue($controller->roomMarkedAsRead);
        $this->assertSame('OPD-001', $controller->requestedOpdId);
        $this->assertSame('TKT-001', $controller->requestedTiketId);
    }

    public function testShowDefaultTypeAdmin(): void
    {
        $controller = new FakeOpdChatController();
        $controller->mockUser = FakeChatUser::opd();
        $controller->mockTiket = FakeChatTiket::withStatuses(['panduan_remote']);
        $controller->mockRoom = (object) ['id' => 'ROOM-001', 'nama_roomchat' => 'admin'];

        $result = $controller->show('TKT-001', Request::create('/chat', 'GET', ['type' => 'asal']));

        $this->assertSame('admin', $result['data']['type']);
    }

    public function testSendTolakNonaktif(): void
    {
        $controller = new FakeOpdChatController();
        $controller->mockUser = FakeChatUser::opd();
        $controller->mockTiket = FakeChatTiket::withStatuses(['selesai']);

        $result = $controller->send(Request::create('/chat', 'POST', [
            'type' => 'admin',
            'konten' => 'Pesan baru',
        ]), 'TKT-001');

        $this->assertSame(403, $result['status']);
        $this->assertSame('Chat history - no new messages allowed', $result['data']['message']);
    }

    public function testSendPesanTextAdmin(): void
    {
        $controller = new FakeOpdChatController();
        $controller->mockUser = FakeChatUser::opd(namaOpd: 'Dinas Kominfo');
        $controller->mockTiket = FakeChatTiket::withStatuses(['panduan_remote']);
        $controller->mockRoom = (object) ['id' => 'ROOM-001'];

        $result = $controller->send(Request::create('/chat', 'POST', [
            'type' => 'admin',
            'konten' => 'Mohon bantuan',
        ]), 'TKT-001');

        $this->assertSame('ROOM-001', $controller->createdMessage['room_id']);
        $this->assertSame('USR-001', $controller->createdMessage['sender_id']);
        $this->assertSame('Mohon bantuan', $controller->createdMessage['konten']);
        $this->assertSame('text', $controller->createdMessage['tipe_konten']);
        $this->assertTrue($controller->messageBroadcasted);
        $this->assertSame('Dinas Kominfo', $result['data']['sender_name']);
        $this->assertSame(200, $result['status']);
    }

    public function testSendPesanTeknis(): void
    {
        $controller = new FakeOpdChatController();
        $controller->mockUser = FakeChatUser::opd();
        $controller->mockTiket = FakeChatTiket::withStatuses(['perbaikan_teknis']);
        $controller->mockRoom = (object) ['id' => 'ROOM-TEKNIS'];

        $result = $controller->send(Request::create('/chat', 'POST', [
            'type' => 'teknis',
            'konten' => 'Baik, siap dicek',
        ]), 'TKT-001');

        $this->assertSame('ROOM-TEKNIS', $controller->createdMessage['room_id']);
        $this->assertSame('Baik, siap dicek', $result['data']['konten']);
        $this->assertSame(200, $result['status']);
    }
}

class FakeOpdChatController extends ChatController
{
    public $mockUser;
    public $mockTiket;
    public $mockRoom;
    public $mockMessages;
    public ?string $requestedOpdId = null;
    public ?string $requestedTiketId = null;
    public bool $roomUserCreated = false;
    public bool $roomMarkedAsRead = false;
    public ?array $createdMessage = null;
    public bool $messageBroadcasted = false;

    protected function authenticatedUser()
    {
        return $this->mockUser;
    }

    protected function findTiketForOpd(string $opdId, string $tiketId, array $with)
    {
        $this->requestedOpdId = $opdId;
        $this->requestedTiketId = $tiketId;
        return $this->mockTiket;
    }

    protected function firstOrCreateRoom(string $tiketId, string $type)
    {
        return $this->mockRoom;
    }

    protected function firstOrCreateRoomUser(string $roomId, string $userId, ?string $bidangId): void
    {
        $this->roomUserCreated = true;
    }

    protected function markRoomAsRead(string $roomId, string $userId): void
    {
        $this->roomMarkedAsRead = true;
    }

    protected function loadRoomMessages(string $roomId)
    {
        return $this->mockMessages ?? collect();
    }

    protected function findRoomOrFail(string $tiketId, string $type)
    {
        return $this->mockRoom;
    }

    protected function createMessage(array $attributes)
    {
        $this->createdMessage = $attributes;
        return (object) array_merge($attributes, ['created_at' => '2026-05-17 20:00:00']);
    }

    protected function broadcastMessage($message, string $senderName): void
    {
        $this->messageBroadcasted = true;
    }

    protected function renderView(string $view, array $data)
    {
        return ['view' => $view, 'data' => $data];
    }

    protected function jsonResponse(array $data, int $status = 200)
    {
        return ['data' => $data, 'status' => $status];
    }
}

class FakeChatUser
{
    public function __construct(
        public string $id,
        public object $opd,
        public $adminHelpdesk = null,
        public $timTeknis = null,
    ) {
    }

    public static function opd(string $namaOpd = 'OPD Test'): self
    {
        return new self('USR-001', (object) ['id' => 'OPD-001', 'nama_opd' => $namaOpd]);
    }
}

class FakeChatTiket
{
    public string $id = 'TKT-001';
    public string $bidang_id = 'BID-001';
    public $statusTiket;

    public static function withStatuses(array $statuses): self
    {
        $tiket = new self();
        $tiket->statusTiket = collect($statuses)->map(
            fn($status, $index) => (object) ['status_tiket' => $status, 'created_at' => now()->addSeconds($index)]
        );

        return $tiket;
    }
}
