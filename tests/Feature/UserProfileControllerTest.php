<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\Helpers\CreatesTestModels;
use Tests\TestCase;

class UserProfileControllerTest extends TestCase
{
    use RefreshDatabase, CreatesTestModels;

    // ─── Unauthenticated ──────────────────────────────────────────

    public function test_tamu_tidak_dapat_mengakses_profil_opd(): void
    {
        $this->get('/opd/profil')->assertRedirect('/login');
    }

    public function test_tamu_tidak_dapat_mengakses_profil_admin_helpdesk(): void
    {
        $this->get('/admin-helpdesk/profil')->assertRedirect('/login');
    }

    // ─── Show Profil OPD ──────────────────────────────────────────

    public function test_opd_dapat_melihat_halaman_profil(): void
    {
        $user = $this->createUser('opd');
        $this->createOpd($user);

        $response = $this->actingAs($user)->get('/opd/profil');

        $response->assertOk();
        $response->assertViewIs('opd.profil');
        $response->assertViewHas('user');
        $response->assertViewHas('profil');
    }

    public function test_admin_helpdesk_tidak_dapat_mengakses_profil_opd(): void
    {
        $bidang = $this->createBidang();
        $user   = $this->createUser('admin_helpdesk');
        $this->createAdminHelpdesk($user, $bidang);

        $response = $this->actingAs($user)->get('/opd/profil');

        $response->assertForbidden();
    }

    // ─── Show Profil Admin Helpdesk ───────────────────────────────

    public function test_admin_helpdesk_dapat_melihat_halaman_profil(): void
    {
        $bidang = $this->createBidang();
        $user   = $this->createUser('admin_helpdesk');
        $this->createAdminHelpdesk($user, $bidang);

        $response = $this->actingAs($user)->get('/admin-helpdesk/profil');

        $response->assertOk();
        $response->assertViewIs('admin_helpdesk.profil');
    }

    // ─── Show Profil Tim Teknis ───────────────────────────────────

    public function test_tim_teknis_dapat_melihat_halaman_profil(): void
    {
        $bidang = $this->createBidang();
        $user   = $this->createUser('tim_teknis');
        $this->createTimTeknis($user, $bidang);

        $response = $this->actingAs($user)->get('/tim-teknis/profil');

        $response->assertOk();
        $response->assertViewIs('tim_teknis.profil');
        $response->assertViewHas('tiketAktifCount');
    }

    // ─── Show Profil Pimpinan ─────────────────────────────────────

    public function test_pimpinan_dapat_melihat_halaman_profil(): void
    {
        $user = $this->createUser('pimpinan');
        $this->createPimpinan($user);

        $response = $this->actingAs($user)->get('/pimpinan/profil');

        $response->assertOk();
        $response->assertViewIs('pimpinan.profil');
    }

    // ─── Update Status Tim Teknis ─────────────────────────────────

    public function test_tim_teknis_dapat_mengubah_status_ke_offline_jika_tidak_ada_tiket_aktif(): void
    {
        $bidang  = $this->createBidang();
        $user    = $this->createUser('tim_teknis');
        $teknisi = $this->createTimTeknis($user, $bidang, ['status_teknisi' => 'online']);

        $response = $this->actingAs($user)
            ->post('/tim-teknis/profil/ubah-status', [
                'status_teknisi' => 'offline',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertEquals('offline', $teknisi->fresh()->status_teknisi);
    }

    public function test_tim_teknis_tidak_dapat_offline_jika_ada_tiket_aktif(): void
    {
        $bidang  = $this->createBidang();
        $user    = $this->createUser('tim_teknis');
        $teknisi = $this->createTimTeknis($user, $bidang, ['status_teknisi' => 'online']);

        $opdUser = $this->createUser('opd');
        $opd     = $this->createOpd($opdUser);
        $tiket   = $this->createTiket($opd);
        $this->createTiketTeknisi($tiket, $teknisi, ['status_tugas' => 'aktif']);

        $response = $this->actingAs($user)
            ->post('/tim-teknis/profil/ubah-status', [
                'status_teknisi' => 'offline',
            ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('status_teknisi');
    }

    public function test_tim_teknis_dapat_mengubah_status_ke_online(): void
    {
        $bidang  = $this->createBidang();
        $user    = $this->createUser('tim_teknis');
        $teknisi = $this->createTimTeknis($user, $bidang, ['status_teknisi' => 'offline']);

        $response = $this->actingAs($user)
            ->post('/tim-teknis/profil/ubah-status', [
                'status_teknisi' => 'online',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertEquals('online', $teknisi->fresh()->status_teknisi);
    }

    public function test_updateStatus_validasi_gagal_jika_status_tidak_valid(): void
    {
        $bidang = $this->createBidang();
        $user   = $this->createUser('tim_teknis');
        $this->createTimTeknis($user, $bidang);

        $response = $this->actingAs($user)
            ->post('/tim-teknis/profil/ubah-status', [
                'status_teknisi' => 'tidak_valid',
            ]);

        $response->assertSessionHasErrors('status_teknisi');
    }

    // ─── Update Password ──────────────────────────────────────────

    public function test_user_dapat_mengubah_password_dengan_data_valid(): void
    {
        $user = $this->createUser('opd');
        $this->createOpd($user);

        $response = $this->actingAs($user)
            ->post('/opd/profil/ubah-password', [
                'password_lama'              => 'password',
                'password_baru'              => 'newpassword123',
                'password_baru_confirmation' => 'newpassword123',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertTrue(Hash::check('newpassword123', $user->fresh()->password));
    }

    public function test_gagal_ubah_password_jika_password_lama_salah(): void
    {
        $user = $this->createUser('opd');
        $this->createOpd($user);

        $response = $this->actingAs($user)
            ->post('/opd/profil/ubah-password', [
                'password_lama'              => 'salah',
                'password_baru'              => 'newpassword123',
                'password_baru_confirmation' => 'newpassword123',
            ]);

        $response->assertSessionHasErrors('password_lama');
    }

    public function test_gagal_ubah_password_jika_password_baru_sama_dengan_lama(): void
    {
        $user = $this->createUser('opd');
        $this->createOpd($user);

        $response = $this->actingAs($user)
            ->post('/opd/profil/ubah-password', [
                'password_lama'              => 'password',
                'password_baru'              => 'password',
                'password_baru_confirmation' => 'password',
            ]);

        $response->assertSessionHasErrors('password_baru');
    }

    public function test_gagal_ubah_password_jika_konfirmasi_tidak_cocok(): void
    {
        $user = $this->createUser('opd');
        $this->createOpd($user);

        $response = $this->actingAs($user)
            ->post('/opd/profil/ubah-password', [
                'password_lama'              => 'password',
                'password_baru'              => 'newpassword123',
                'password_baru_confirmation' => 'tidakcocok',
            ]);

        $response->assertSessionHasErrors('password_baru_confirmation');
    }

    public function test_gagal_ubah_password_jika_password_baru_kurang_dari_8_karakter(): void
    {
        $user = $this->createUser('opd');
        $this->createOpd($user);

        $response = $this->actingAs($user)
            ->post('/opd/profil/ubah-password', [
                'password_lama'              => 'password',
                'password_baru'              => 'short',
                'password_baru_confirmation' => 'short',
            ]);

        $response->assertSessionHasErrors('password_baru');
    }

    public function test_tamu_tidak_dapat_mengubah_password(): void
    {
        $response = $this->post('/opd/profil/ubah-password', [
            'password_lama'              => 'password',
            'password_baru'              => 'newpassword123',
            'password_baru_confirmation' => 'newpassword123',
        ]);

        $response->assertRedirect('/login');
    }

    public function test_admin_helpdesk_dapat_mengubah_password(): void
    {
        $bidang = $this->createBidang();
        $user   = $this->createUser('admin_helpdesk');
        $this->createAdminHelpdesk($user, $bidang);

        $response = $this->actingAs($user)
            ->post('/admin-helpdesk/profil/ubah-password', [
                'password_lama'              => 'password',
                'password_baru'              => 'newpassword123',
                'password_baru_confirmation' => 'newpassword123',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }
}
