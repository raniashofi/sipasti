<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Str;
use Tests\Helpers\CreatesTestModels;
use Tests\TestCase;

class NotificationControllerTest extends TestCase
{
    use RefreshDatabase, CreatesTestModels;

    // ─── Unauthenticated ──────────────────────────────────────────

    public function test_tamu_tidak_dapat_mengakses_notifikasi(): void
    {
        $response = $this->get('/notif');

        $response->assertRedirect('/login');
    }

    public function test_tamu_tidak_dapat_menandai_notifikasi_dibaca(): void
    {
        $response = $this->post('/notif/some-id/read');

        $response->assertRedirect('/login');
    }

    public function test_tamu_tidak_dapat_menandai_semua_dibaca(): void
    {
        $response = $this->post('/notif/read-all');

        $response->assertRedirect('/login');
    }

    // ─── Index HTML ───────────────────────────────────────────────

    public function test_halaman_notifikasi_dapat_diakses_user_terautentikasi(): void
    {
        $user = $this->createUser('opd');

        $response = $this->actingAs($user)->get('/notif');

        $response->assertOk();
        $response->assertViewIs('notifikasi.index');
    }

    public function test_halaman_notifikasi_menampilkan_data_yang_benar(): void
    {
        $user = $this->createUser('opd');

        $response = $this->actingAs($user)
            ->get('/notif');

        $response->assertViewHas('notifications');
        $response->assertViewHas('unreadCount');
    }

    // ─── Index JSON ───────────────────────────────────────────────

    public function test_notifikasi_json_dapat_diakses_dengan_header_accept_json(): void
    {
        $user = $this->createUser('opd');

        $response = $this->actingAs($user)
            ->getJson('/notif');

        $response->assertOk();
        $response->assertJsonStructure([
            'notifications',
            'unread_count',
        ]);
    }

    public function test_json_notifikasi_berisi_field_yang_benar(): void
    {
        $user = $this->createUser('opd');

        DatabaseNotification::create([
            'id'              => (string) Str::uuid(),
            'type'            => 'App\Notifications\StatusTiketNotification',
            'notifiable_type' => 'App\Models\User',
            'notifiable_id'   => $user->id,
            'data'            => json_encode([
                'icon'  => 'info',
                'title' => 'Tiket Diperbarui',
                'body'  => 'Status tiket Anda telah berubah',
                'url'   => '/opd/pengaduan-saya/TKT-001',
            ]),
        ]);

        $response = $this->actingAs($user)->getJson('/notif');

        $response->assertOk();
        $response->assertJsonPath('unread_count', 1);
        $response->assertJsonStructure([
            'notifications' => [
                '*' => ['id', 'icon', 'title', 'body', 'url', 'read', 'created_at'],
            ],
        ]);
    }

    public function test_unread_count_sesuai_dengan_notifikasi_belum_dibaca(): void
    {
        $user = $this->createUser('opd');

        for ($i = 0; $i < 3; $i++) {
            DatabaseNotification::create([
                'id'              => (string) Str::uuid(),
                'type'            => 'App\Notifications\TiketMasukNotification',
                'notifiable_type' => 'App\Models\User',
                'notifiable_id'   => $user->id,
                'data'            => json_encode(['title' => 'Test ' . $i]),
            ]);
        }

        $response = $this->actingAs($user)->getJson('/notif');

        $response->assertJsonPath('unread_count', 3);
    }

    // ─── Mark Read ────────────────────────────────────────────────

    public function test_menandai_satu_notifikasi_sebagai_dibaca(): void
    {
        $user = $this->createUser('opd');

        $notifId = (string) Str::uuid();
        DatabaseNotification::create([
            'id'              => $notifId,
            'type'            => 'App\Notifications\TiketMasukNotification',
            'notifiable_type' => 'App\Models\User',
            'notifiable_id'   => $user->id,
            'data'            => json_encode(['title' => 'Test']),
        ]);

        $response = $this->actingAs($user)->postJson("/notif/{$notifId}/read");

        $response->assertOk();
        $response->assertJson(['ok' => true]);
        $this->assertNotNull(
            DatabaseNotification::find($notifId)->read_at
        );
    }

    public function test_markRead_mengembalikan_404_jika_notifikasi_tidak_ditemukan(): void
    {
        $user = $this->createUser('opd');

        $response = $this->actingAs($user)
            ->postJson('/notif/' . Str::uuid() . '/read');

        $response->assertNotFound();
    }

    public function test_user_tidak_dapat_menandai_notifikasi_milik_user_lain(): void
    {
        $user1 = $this->createUser('opd');
        $user2 = $this->createUser('opd');

        $notifId = (string) Str::uuid();
        DatabaseNotification::create([
            'id'              => $notifId,
            'type'            => 'App\Notifications\TiketMasukNotification',
            'notifiable_type' => 'App\Models\User',
            'notifiable_id'   => $user2->id,
            'data'            => json_encode(['title' => 'Test']),
        ]);

        $response = $this->actingAs($user1)
            ->postJson("/notif/{$notifId}/read");

        $response->assertNotFound();
    }

    // ─── Mark All Read ────────────────────────────────────────────

    public function test_menandai_semua_notifikasi_sebagai_dibaca(): void
    {
        $user = $this->createUser('opd');

        for ($i = 0; $i < 2; $i++) {
            DatabaseNotification::create([
                'id'              => (string) Str::uuid(),
                'type'            => 'App\Notifications\TiketMasukNotification',
                'notifiable_type' => 'App\Models\User',
                'notifiable_id'   => $user->id,
                'data'            => json_encode(['title' => 'Test']),
            ]);
        }

        $response = $this->actingAs($user)->postJson('/notif/read-all');

        $response->assertOk();
        $response->assertJson(['ok' => true]);

        $unread = $user->unreadNotifications()->count();
        $this->assertEquals(0, $unread);
    }

    public function test_markAllRead_tidak_mempengaruhi_notifikasi_user_lain(): void
    {
        $user1 = $this->createUser('opd');
        $user2 = $this->createUser('opd');

        DatabaseNotification::create([
            'id'              => (string) Str::uuid(),
            'type'            => 'App\Notifications\TiketMasukNotification',
            'notifiable_type' => 'App\Models\User',
            'notifiable_id'   => $user2->id,
            'data'            => json_encode(['title' => 'Test']),
        ]);

        $this->actingAs($user1)->postJson('/notif/read-all');

        $this->assertEquals(1, $user2->unreadNotifications()->count());
    }
}
