<?php

namespace Tests\Feature\Opd;

use App\Models\ChatRoom;
use App\Models\ChatRoomUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Helpers\CreatesTestModels;
use Tests\TestCase;

class ChatControllerTest extends TestCase
{
    use RefreshDatabase, CreatesTestModels;

    private function createChatSetup(): array
    {
        $user  = $this->createUser('opd');
        $opd   = $this->createOpd($user);
        $tiket = $this->createTiket($opd);
        $this->createStatusTiket($tiket, 'panduan_remote');

        $chatRoom = ChatRoom::create([
            'id'            => (string) Str::uuid(),
            'tiket_id'      => $tiket->id,
            'nama_roomchat' => 'admin',
        ]);

        ChatRoomUser::create([
            'room_id' => $chatRoom->id,
            'user_id' => $user->id,
        ]);

        return compact('user', 'opd', 'tiket', 'chatRoom');
    }

    // ─── Auth & Role Guard ────────────────────────────────────────

    public function test_tamu_tidak_dapat_mengakses_chat_tiket(): void
    {
        $tiketId = 'TKT-' . strtoupper(Str::random(10));
        $this->get("/opd/pengaduan-saya/{$tiketId}/chat")->assertRedirect('/login');
    }

    public function test_admin_helpdesk_tidak_dapat_mengakses_chat_opd(): void
    {
        $bidang = $this->createBidang();
        $user   = $this->createUser('admin_helpdesk');
        $this->createAdminHelpdesk($user, $bidang);

        $tiketId = 'TKT-' . strtoupper(Str::random(10));
        $this->actingAs($user)
            ->get("/opd/pengaduan-saya/{$tiketId}/chat")
            ->assertForbidden();
    }

    // ─── Show Chat ────────────────────────────────────────────────

    public function test_opd_dapat_mengakses_halaman_chat_tiket_miliknya(): void
    {
        ['user' => $user, 'tiket' => $tiket] = $this->createChatSetup();

        $response = $this->actingAs($user)
            ->get("/opd/pengaduan-saya/{$tiket->id}/chat");

        $response->assertOk();
        $response->assertViewIs('opd.pengaduan-saya.chat');
        $response->assertViewHas('tiket');
    }

    public function test_opd_tidak_dapat_mengakses_chat_tiket_milik_opd_lain(): void
    {
        $user2 = $this->createUser('opd');
        $opd2  = $this->createOpd($user2);
        $tiket = $this->createTiket($opd2);

        $user1 = $this->createUser('opd');
        $this->createOpd($user1);

        $response = $this->actingAs($user1)
            ->get("/opd/pengaduan-saya/{$tiket->id}/chat");

        $response->assertNotFound();
    }

    // ─── Send Message ─────────────────────────────────────────────

    public function test_opd_dapat_mengirim_pesan_di_chat_tiket(): void
    {
        ['user' => $user, 'tiket' => $tiket, 'chatRoom' => $chatRoom] = $this->createChatSetup();

        $response = $this->actingAs($user)
            ->postJson("/opd/pengaduan-saya/{$tiket->id}/chat/send", [
                'konten' => 'Halo, masalah belum terselesaikan',
            ]);

        $response->assertOk();
    }

    public function test_send_validasi_gagal_jika_pesan_kosong(): void
    {
        ['user' => $user, 'tiket' => $tiket] = $this->createChatSetup();

        $response = $this->actingAs($user)
            ->postJson("/opd/pengaduan-saya/{$tiket->id}/chat/send", [
                'konten' => '',
            ]);

        $response->assertUnprocessable();
    }

    public function test_opd_tidak_dapat_mengirim_pesan_ke_tiket_milik_opd_lain(): void
    {
        $user2 = $this->createUser('opd');
        $opd2  = $this->createOpd($user2);
        $tiket = $this->createTiket($opd2);

        $user1 = $this->createUser('opd');
        $this->createOpd($user1);

        $response = $this->actingAs($user1)
            ->postJson("/opd/pengaduan-saya/{$tiket->id}/chat/send", [
                'pesan' => 'Test',
            ]);

        $response->assertNotFound();
    }
}
