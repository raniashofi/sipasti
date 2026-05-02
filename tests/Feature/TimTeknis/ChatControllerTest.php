<?php

namespace Tests\Feature\TimTeknis;

use App\Models\ChatRoom;
use App\Models\ChatRoomUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Helpers\CreatesTestModels;
use Tests\TestCase;

class ChatControllerTest extends TestCase
{
    use RefreshDatabase, CreatesTestModels;

    private function createTeknisiChatSetup(): array
    {
        $bidang  = $this->createBidang();
        $user    = $this->createUser('tim_teknis');
        $teknisi = $this->createTimTeknis($user, $bidang);

        $opdUser = $this->createUser('opd');
        $opd     = $this->createOpd($opdUser);
        $tiket   = $this->createTiket($opd);
        $this->createStatusTiket($tiket, 'perbaikan_teknis');
        $this->createTiketTeknisi($tiket, $teknisi, [
            'peran_teknisi' => 'teknisi_utama',
            'status_tugas'  => 'aktif',
        ]);

        $chatRoom = ChatRoom::create([
            'id'            => (string) Str::uuid(),
            'tiket_id'      => $tiket->id,
            'nama_roomchat' => 'teknis',
        ]);

        ChatRoomUser::create([
            'room_id' => $chatRoom->id,
            'user_id' => $user->id,
        ]);

        return compact('user', 'teknisi', 'tiket', 'chatRoom');
    }

    // ─── Auth & Role Guard ────────────────────────────────────────

    public function test_tamu_tidak_dapat_mengakses_chat_tim_teknis(): void
    {
        $tiketId = 'TKT-' . strtoupper(Str::random(10));
        $this->get("/tim-teknis/tiket/{$tiketId}/chat")->assertRedirect('/login');
    }

    public function test_opd_tidak_dapat_mengakses_chat_tim_teknis(): void
    {
        $user = $this->createUser('opd');
        $this->createOpd($user);

        $tiketId = 'TKT-TEST';
        $this->actingAs($user)
            ->get("/tim-teknis/tiket/{$tiketId}/chat")
            ->assertForbidden();
    }

    // ─── Show ─────────────────────────────────────────────────────

    public function test_tim_teknis_dapat_mengakses_halaman_chat(): void
    {
        ['user' => $user, 'tiket' => $tiket] = $this->createTeknisiChatSetup();

        $response = $this->actingAs($user)
            ->get("/tim-teknis/tiket/{$tiket->id}/chat");

        $response->assertOk();
    }

    // ─── Send ─────────────────────────────────────────────────────

    public function test_teknisi_utama_dapat_mengirim_pesan(): void
    {
        ['user' => $user, 'tiket' => $tiket] = $this->createTeknisiChatSetup();

        $response = $this->actingAs($user)
            ->postJson("/tim-teknis/tiket/{$tiket->id}/chat/send", [
                'konten' => 'Sedang dalam penanganan',
            ]);

        $response->assertOk();
    }

    public function test_send_validasi_gagal_jika_pesan_kosong(): void
    {
        ['user' => $user, 'tiket' => $tiket] = $this->createTeknisiChatSetup();

        $response = $this->actingAs($user)
            ->postJson("/tim-teknis/tiket/{$tiket->id}/chat/send", [
                'konten' => '',
            ]);

        $response->assertUnprocessable();
    }
}
