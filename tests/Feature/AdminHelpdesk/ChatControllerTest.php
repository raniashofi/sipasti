<?php

namespace Tests\Feature\AdminHelpdesk;

use App\Models\ChatRoom;
use App\Models\ChatRoomUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Helpers\CreatesTestModels;
use Tests\TestCase;

class ChatControllerTest extends TestCase
{
    use RefreshDatabase, CreatesTestModels;

    private function createAdminChatSetup(): array
    {
        $bidang  = $this->createBidang();
        $user    = $this->createUser('admin_helpdesk');
        $adminAh = $this->createAdminHelpdesk($user, $bidang);

        $opdUser = $this->createUser('opd');
        $opd     = $this->createOpd($opdUser);
        $tiket   = $this->createTiket($opd, ['admin_id' => $adminAh->id]);
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

        return compact('user', 'adminAh', 'tiket', 'chatRoom', 'opd', 'opdUser');
    }

    // ─── Auth & Role Guard ────────────────────────────────────────

    public function test_tamu_tidak_dapat_mengakses_chat_admin_helpdesk(): void
    {
        $tiketId = 'TKT-' . strtoupper(Str::random(10));
        $this->get("/admin-helpdesk/tiket/{$tiketId}/chat")->assertRedirect('/login');
    }

    public function test_opd_tidak_dapat_mengakses_chat_admin_helpdesk(): void
    {
        $user = $this->createUser('opd');
        $this->createOpd($user);

        $tiketId = 'TKT-TEST';
        $this->actingAs($user)
            ->get("/admin-helpdesk/tiket/{$tiketId}/chat")
            ->assertForbidden();
    }

    // ─── Show ─────────────────────────────────────────────────────

    public function test_admin_helpdesk_dapat_mengakses_halaman_chat(): void
    {
        ['user' => $user, 'tiket' => $tiket] = $this->createAdminChatSetup();

        $response = $this->actingAs($user)
            ->get("/admin-helpdesk/tiket/{$tiket->id}/chat");

        $response->assertOk();
    }

    // ─── Send ─────────────────────────────────────────────────────

    public function test_admin_helpdesk_dapat_mengirim_pesan(): void
    {
        ['user' => $user, 'tiket' => $tiket] = $this->createAdminChatSetup();

        $response = $this->actingAs($user)
            ->postJson("/admin-helpdesk/tiket/{$tiket->id}/chat/send", [
                'konten' => 'Silakan coba restart router Anda',
            ]);

        $response->assertOk();
    }

    public function test_send_validasi_gagal_jika_pesan_kosong(): void
    {
        ['user' => $user, 'tiket' => $tiket] = $this->createAdminChatSetup();

        $response = $this->actingAs($user)
            ->postJson("/admin-helpdesk/tiket/{$tiket->id}/chat/send", [
                'konten' => '',
            ]);

        $response->assertUnprocessable();
    }
}
