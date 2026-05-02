<?php

namespace Tests\Feature\TimTeknis;

use App\Models\StatusTiket;
use App\Models\TiketTeknisi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Helpers\CreatesTestModels;
use Tests\TestCase;

class AntreanControllerTest extends TestCase
{
    use RefreshDatabase, CreatesTestModels;

    private function createTeknisiAndTiket(): array
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

        return compact('user', 'teknisi', 'tiket', 'opd', 'bidang');
    }

    // ─── Auth & Role Guard ────────────────────────────────────────

    public function test_tamu_tidak_dapat_mengakses_antrean(): void
    {
        $this->get('/tim-teknis/antrean')->assertRedirect('/login');
    }

    public function test_opd_tidak_dapat_mengakses_antrean(): void
    {
        $user = $this->createUser('opd');
        $this->createOpd($user);

        $this->actingAs($user)->get('/tim-teknis/antrean')->assertForbidden();
    }

    // ─── Index Antrean ────────────────────────────────────────────

    public function test_tim_teknis_dapat_mengakses_halaman_antrean(): void
    {
        $bidang = $this->createBidang();
        $user   = $this->createUser('tim_teknis');
        $this->createTimTeknis($user, $bidang);

        $response = $this->actingAs($user)->get('/tim-teknis/antrean');

        $response->assertOk();
        $response->assertViewIs('tim_teknis.antrean');
        $response->assertViewHas('tikets');
    }

    public function test_antrean_menampilkan_tiket_aktif_teknisi(): void
    {
        ['user' => $user, 'tiket' => $tiket] = $this->createTeknisiAndTiket();

        $response = $this->actingAs($user)->get('/tim-teknis/antrean');

        $response->assertOk();
    }

    public function test_antrean_mendukung_filter_pencarian(): void
    {
        ['user' => $user, 'tiket' => $tiket] = $this->createTeknisiAndTiket();

        $response = $this->actingAs($user)
            ->get('/tim-teknis/antrean?search=' . urlencode($tiket->subjek_masalah));

        $response->assertOk();
    }

    // ─── Selesai ──────────────────────────────────────────────────

    public function test_teknisi_utama_dapat_menyelesaikan_tiket(): void
    {
        ['user' => $user, 'tiket' => $tiket] = $this->createTeknisiAndTiket();

        $response = $this->actingAs($user)
            ->post("/tim-teknis/tiket/{$tiket->id}/selesai", [
                'catatan' => 'Komputer telah diperbaiki',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('status_tiket', [
            'tiket_id'     => $tiket->id,
            'status_tiket' => 'selesai',
        ]);
    }

    public function test_selesai_mengubah_status_tugas_menjadi_selesai(): void
    {
        ['user' => $user, 'tiket' => $tiket, 'teknisi' => $teknisi] = $this->createTeknisiAndTiket();

        $this->actingAs($user)
            ->post("/tim-teknis/tiket/{$tiket->id}/selesai", [
                'catatan' => 'Sudah selesai',
            ]);

        $this->assertDatabaseHas('tiket_teknisi', [
            'tiket_id'     => $tiket->id,
            'teknis_id'    => $teknisi->id,
            'status_tugas' => 'selesai',
        ]);
    }

    public function test_selesai_langsung_tutup_tiket_jika_pernah_dibuka_kembali(): void
    {
        ['user' => $user, 'tiket' => $tiket] = $this->createTeknisiAndTiket();

        StatusTiket::create([
            'id'           => 'STS-KEMBALI001',
            'tiket_id'     => $tiket->id,
            'status_tiket' => 'dibuka_kembali',
            'catatan'      => 'Masalah belum selesai',
        ]);

        $this->actingAs($user)
            ->post("/tim-teknis/tiket/{$tiket->id}/selesai");

        $this->assertDatabaseHas('status_tiket', [
            'tiket_id'     => $tiket->id,
            'status_tiket' => 'tiket_ditutup',
        ]);
    }

    public function test_teknisi_pendamping_tidak_dapat_menyelesaikan_tiket(): void
    {
        $bidang     = $this->createBidang();
        $user       = $this->createUser('tim_teknis');
        $teknisiPnd = $this->createTimTeknis($user, $bidang);

        $opdUser = $this->createUser('opd');
        $opd     = $this->createOpd($opdUser);
        $tiket   = $this->createTiket($opd);
        $this->createStatusTiket($tiket, 'perbaikan_teknis');
        $this->createTiketTeknisi($tiket, $teknisiPnd, [
            'peran_teknisi' => 'teknisi_pendamping',
            'status_tugas'  => 'aktif',
        ]);

        $response = $this->actingAs($user)
            ->post("/tim-teknis/tiket/{$tiket->id}/selesai");

        $response->assertNotFound();
    }

    // ─── Gagal / Rusak Berat ─────────────────────────────────────

    public function test_teknisi_dapat_melaporkan_tiket_sebagai_rusak_berat(): void
    {
        ['user' => $user, 'tiket' => $tiket] = $this->createTeknisiAndTiket();

        $response = $this->actingAs($user)
            ->post("/tim-teknis/tiket/{$tiket->id}/gagal", [
                'analisis_kerusakan'          => 'Motherboard terbakar',
                'spesifikasi_perangkat_rusak' => 'PC Dell Optiplex 7060',
                'rekomendasi'                 => 'Perlu penggantian motherboard',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('status_tiket', [
            'tiket_id'     => $tiket->id,
            'status_tiket' => 'rusak_berat',
        ]);
    }

    public function test_gagal_validasi_jika_analisis_kerusakan_kosong(): void
    {
        ['user' => $user, 'tiket' => $tiket] = $this->createTeknisiAndTiket();

        $response = $this->actingAs($user)
            ->post("/tim-teknis/tiket/{$tiket->id}/gagal", [
                'analisis_kerusakan' => '',
                'rekomendasi'        => 'Penggantian perangkat',
            ]);

        $response->assertSessionHasErrors('analisis_kerusakan');
    }

    public function test_gagal_validasi_jika_rekomendasi_kosong(): void
    {
        ['user' => $user, 'tiket' => $tiket] = $this->createTeknisiAndTiket();

        $response = $this->actingAs($user)
            ->post("/tim-teknis/tiket/{$tiket->id}/gagal", [
                'analisis_kerusakan' => 'Kerusakan hardware',
                'rekomendasi'        => '',
            ]);

        $response->assertSessionHasErrors('rekomendasi');
    }

    // ─── Riwayat ──────────────────────────────────────────────────

    public function test_tim_teknis_dapat_mengakses_riwayat_tugas(): void
    {
        $bidang = $this->createBidang();
        $user   = $this->createUser('tim_teknis');
        $this->createTimTeknis($user, $bidang);

        $response = $this->actingAs($user)->get('/tim-teknis/riwayat');

        $response->assertOk();
        $response->assertViewIs('tim_teknis.riwayat');
    }

    public function test_riwayat_menampilkan_tugas_yang_selesai(): void
    {
        ['user' => $user, 'tiket' => $tiket, 'teknisi' => $teknisi] = $this->createTeknisiAndTiket();

        TiketTeknisi::where('tiket_id', $tiket->id)
            ->where('teknis_id', $teknisi->id)
            ->update(['status_tugas' => 'selesai']);

        StatusTiket::create([
            'id'           => 'STS-DONE',
            'tiket_id'     => $tiket->id,
            'status_tiket' => 'selesai',
        ]);

        $response = $this->actingAs($user)->get('/tim-teknis/riwayat');

        $response->assertOk();
        $response->assertViewHas('riwayats');
    }

    // ─── Kembalikan ke Admin ──────────────────────────────────────

    public function test_teknisi_dapat_mengembalikan_tiket_ke_admin_helpdesk(): void
    {
        $bidang  = $this->createBidang();
        $ahUser  = $this->createUser('admin_helpdesk');
        $adminAh = $this->createAdminHelpdesk($ahUser, $bidang);

        $user    = $this->createUser('tim_teknis');
        $teknisi = $this->createTimTeknis($user, $bidang);

        $opdUser = $this->createUser('opd');
        $opd     = $this->createOpd($opdUser);
        $tiket   = $this->createTiket($opd, ['admin_id' => $adminAh->id]);
        $this->createStatusTiket($tiket, 'perbaikan_teknis');
        $this->createTiketTeknisi($tiket, $teknisi, [
            'peran_teknisi' => 'teknisi_utama',
            'status_tugas'  => 'aktif',
        ]);

        $response = $this->actingAs($user)
            ->post("/tim-teknis/tiket/{$tiket->id}/kembalikan", [
                'alasan_kembalikan' => 'Masalah memerlukan penanganan khusus admin',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('status_tiket', [
            'tiket_id'     => $tiket->id,
            'status_tiket' => 'verifikasi_admin',
        ]);
    }

    public function test_kembalikan_validasi_gagal_jika_alasan_kosong(): void
    {
        ['user' => $user, 'tiket' => $tiket] = $this->createTeknisiAndTiket();

        $response = $this->actingAs($user)
            ->post("/tim-teknis/tiket/{$tiket->id}/kembalikan", [
                'alasan_kembalikan' => '',
            ]);

        $response->assertSessionHasErrors('alasan_kembalikan');
    }
}
