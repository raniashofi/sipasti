<?php

namespace Tests\Feature\SuperAdmin;

use App\Models\AdminHelpdesk;
use App\Models\Opd;
use App\Models\Pimpinan;
use App\Models\TimTeknis;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Helpers\CreatesTestModels;
use Tests\TestCase;

class ManajemenPenggunaControllerTest extends TestCase
{
    use RefreshDatabase, CreatesTestModels;

    // ─── Auth & Role Guard ────────────────────────────────────────

    public function test_tamu_tidak_dapat_mengakses_manajemen_pengguna(): void
    {
        $this->get('/super-admin/pengguna/opd')->assertRedirect('/login');
    }

    public function test_opd_tidak_dapat_mengakses_manajemen_pengguna(): void
    {
        $user = $this->createUser('opd');
        $this->createOpd($user);

        $this->actingAs($user)->get('/super-admin/pengguna/opd')->assertForbidden();
    }

    // ─── OPD Management ───────────────────────────────────────────

    public function test_super_admin_dapat_mengakses_halaman_manajemen_opd(): void
    {
        $user = $this->createUser('super_admin');

        $response = $this->actingAs($user)->get('/super-admin/pengguna/opd');

        $response->assertOk();
        $response->assertViewIs('super_admin.manajemen-pengguna.opd');
    }

    public function test_super_admin_dapat_menambah_opd_baru(): void
    {
        $user = $this->createUser('super_admin');

        $response = $this->actingAs($user)
            ->post('/super-admin/pengguna/opd', [
                'kode_opd'  => 'OPD-TEST001',
                'nama_opd'  => 'Dinas Uji Coba',
                'email'     => 'opdtest@example.com',
                'password'  => 'password123',
            ]);

        $response->assertRedirect(route('super_admin.pengguna.opd'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('opd', ['kode_opd' => 'OPD-TEST001', 'nama_opd' => 'Dinas Uji Coba']);
        $this->assertDatabaseHas('users', ['email' => 'opdtest@example.com', 'role' => 'opd']);
    }

    public function test_storeOpd_validasi_gagal_jika_email_sudah_digunakan(): void
    {
        $user = $this->createUser('super_admin');
        $existing = $this->createUser('opd');

        $response = $this->actingAs($user)
            ->post('/super-admin/pengguna/opd', [
                'kode_opd'  => 'OPD-TEST002',
                'nama_opd'  => 'Dinas Test',
                'email'     => $existing->email,
                'password'  => 'password123',
            ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_storeOpd_validasi_gagal_jika_kode_opd_sudah_ada(): void
    {
        $user    = $this->createUser('super_admin');
        $opdUser = $this->createUser('opd');
        $this->createOpd($opdUser, ['kode_opd' => 'OPD-EXISTING']);

        $response = $this->actingAs($user)
            ->post('/super-admin/pengguna/opd', [
                'kode_opd'  => 'OPD-EXISTING',
                'nama_opd'  => 'Dinas Baru',
                'email'     => 'baru@example.com',
                'password'  => 'password123',
            ]);

        $response->assertSessionHasErrors('kode_opd');
    }

    public function test_storeOpd_validasi_gagal_jika_password_terlalu_pendek(): void
    {
        $user = $this->createUser('super_admin');

        $response = $this->actingAs($user)
            ->post('/super-admin/pengguna/opd', [
                'kode_opd'  => 'OPD-TEST003',
                'nama_opd'  => 'Dinas Test',
                'email'     => 'test@example.com',
                'password'  => '123',
            ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_super_admin_dapat_memperbarui_data_opd(): void
    {
        $user    = $this->createUser('super_admin');
        $opdUser = $this->createUser('opd');
        $opd     = $this->createOpd($opdUser, ['kode_opd' => 'OPD-OLD', 'nama_opd' => 'Nama Lama']);

        $response = $this->actingAs($user)
            ->put("/super-admin/pengguna/opd/{$opd->id}", [
                'kode_opd'  => 'OPD-UPDATED',
                'nama_opd'  => 'Nama Baru',
                'email'     => $opdUser->email,
            ]);

        $response->assertRedirect(route('super_admin.pengguna.opd'));
        $response->assertSessionHas('success');

        $this->assertEquals('Nama Baru', $opd->fresh()->nama_opd);
    }

    public function test_super_admin_dapat_menghapus_opd(): void
    {
        $user    = $this->createUser('super_admin');
        $opdUser = $this->createUser('opd');
        $opd     = $this->createOpd($opdUser);

        $response = $this->actingAs($user)
            ->delete("/super-admin/pengguna/opd/{$opd->id}");

        $response->assertRedirect(route('super_admin.pengguna.opd'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('opd', ['id' => $opd->id]);
        $this->assertDatabaseMissing('users', ['id' => $opdUser->id]);
    }

    public function test_destroyOpd_mengembalikan_404_jika_opd_tidak_ada(): void
    {
        $user = $this->createUser('super_admin');

        $response = $this->actingAs($user)
            ->delete('/super-admin/pengguna/opd/' . Str::uuid());

        $response->assertNotFound();
    }

    // ─── Internal Management (Tim Teknis) ─────────────────────────

    public function test_super_admin_dapat_mengakses_manajemen_internal(): void
    {
        $user = $this->createUser('super_admin');

        $response = $this->actingAs($user)->get('/super-admin/pengguna/internal');

        $response->assertOk();
        $response->assertViewIs('super_admin.manajemen-pengguna.internal');
    }

    public function test_super_admin_dapat_menambah_tim_teknis(): void
    {
        $user   = $this->createUser('super_admin');
        $bidang = $this->createBidang();

        $response = $this->actingAs($user)
            ->post('/super-admin/pengguna/internal/tim-teknis', [
                'nama_lengkap'   => 'Teknisi Baru',
                'email'          => 'teknisi@example.com',
                'password'       => 'password123',
                'bidang_id'      => $bidang->id,
                'status_teknisi' => 'online',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('tim_teknis', ['nama_lengkap' => 'Teknisi Baru']);
        $this->assertDatabaseHas('users', ['email' => 'teknisi@example.com', 'role' => 'tim_teknis']);
    }

    public function test_storeTimTeknis_validasi_gagal_jika_bidang_tidak_valid(): void
    {
        $user = $this->createUser('super_admin');

        $response = $this->actingAs($user)
            ->post('/super-admin/pengguna/internal/tim-teknis', [
                'nama_lengkap' => 'Teknisi Test',
                'email'        => 'teknisi2@example.com',
                'password'     => 'password123',
                'bidang_id'    => Str::uuid(),
            ]);

        $response->assertSessionHasErrors('bidang_id');
    }

    public function test_super_admin_dapat_memperbarui_tim_teknis(): void
    {
        $user   = $this->createUser('super_admin');
        $bidang = $this->createBidang();
        $ttUser = $this->createUser('tim_teknis');
        $tt     = $this->createTimTeknis($ttUser, $bidang, ['nama_lengkap' => 'Nama Lama']);

        $response = $this->actingAs($user)
            ->put("/super-admin/pengguna/internal/tim-teknis/{$tt->id}", [
                'nama_lengkap' => 'Nama Baru',
                'email'        => $ttUser->email,
                'bidang_id'    => $bidang->id,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertEquals('Nama Baru', $tt->fresh()->nama_lengkap);
    }

    public function test_super_admin_dapat_menghapus_tim_teknis(): void
    {
        $user   = $this->createUser('super_admin');
        $bidang = $this->createBidang();
        $ttUser = $this->createUser('tim_teknis');
        $tt     = $this->createTimTeknis($ttUser, $bidang);

        $response = $this->actingAs($user)
            ->delete("/super-admin/pengguna/internal/tim-teknis/{$tt->id}");

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('tim_teknis', ['id' => $tt->id]);
    }

    // ─── Internal Management (Admin Helpdesk) ─────────────────────

    public function test_super_admin_dapat_menambah_admin_helpdesk(): void
    {
        $user   = $this->createUser('super_admin');
        $bidang = $this->createBidang();

        $response = $this->actingAs($user)
            ->post('/super-admin/pengguna/internal/admin-helpdesk', [
                'nama_lengkap' => 'Admin Baru',
                'email'        => 'adminnew@example.com',
                'password'     => 'password123',
                'bidang_id'    => $bidang->id,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('admin_helpdesk', ['nama_lengkap' => 'Admin Baru']);
        $this->assertDatabaseHas('users', ['email' => 'adminnew@example.com', 'role' => 'admin_helpdesk']);
    }

    public function test_storeAdminHelpdesk_validasi_gagal_jika_email_duplikat(): void
    {
        $user      = $this->createUser('super_admin');
        $bidang    = $this->createBidang();
        $existing  = $this->createUser('admin_helpdesk');

        $response = $this->actingAs($user)
            ->post('/super-admin/pengguna/internal/admin-helpdesk', [
                'nama_lengkap' => 'Admin Test',
                'email'        => $existing->email,
                'password'     => 'password123',
                'bidang_id'    => $bidang->id,
            ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_super_admin_dapat_memperbarui_admin_helpdesk(): void
    {
        $user   = $this->createUser('super_admin');
        $bidang = $this->createBidang();
        $ahUser = $this->createUser('admin_helpdesk');
        $ah     = $this->createAdminHelpdesk($ahUser, $bidang, ['nama_lengkap' => 'Nama Lama']);

        $response = $this->actingAs($user)
            ->put("/super-admin/pengguna/internal/admin-helpdesk/{$ah->id}", [
                'nama_lengkap' => 'Nama Diperbarui',
                'email'        => $ahUser->email,
                'bidang_id'    => $bidang->id,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertEquals('Nama Diperbarui', $ah->fresh()->nama_lengkap);
    }

    public function test_super_admin_dapat_menghapus_admin_helpdesk(): void
    {
        $user   = $this->createUser('super_admin');
        $bidang = $this->createBidang();
        $ahUser = $this->createUser('admin_helpdesk');
        $ah     = $this->createAdminHelpdesk($ahUser, $bidang);

        $response = $this->actingAs($user)
            ->delete("/super-admin/pengguna/internal/admin-helpdesk/{$ah->id}");

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('admin_helpdesk', ['id' => $ah->id]);
    }

    // ─── Pimpinan Management ──────────────────────────────────────

    public function test_super_admin_dapat_menambah_pimpinan(): void
    {
        $user = $this->createUser('super_admin');

        $response = $this->actingAs($user)
            ->post('/super-admin/pengguna/internal/pimpinan', [
                'nama_lengkap' => 'Pak Direktur',
                'email'        => 'direktur@example.com',
                'password'     => 'password123',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('pimpinan', ['nama_lengkap' => 'Pak Direktur']);
        $this->assertDatabaseHas('users', ['email' => 'direktur@example.com', 'role' => 'pimpinan']);
    }

    public function test_storePimpinan_validasi_gagal_jika_nama_lengkap_kosong(): void
    {
        $user = $this->createUser('super_admin');

        $response = $this->actingAs($user)
            ->post('/super-admin/pengguna/internal/pimpinan', [
                'nama_lengkap' => '',
                'email'        => 'direktur2@example.com',
                'password'     => 'password123',
            ]);

        $response->assertSessionHasErrors('nama_lengkap');
    }

    public function test_super_admin_dapat_memperbarui_pimpinan(): void
    {
        $user     = $this->createUser('super_admin');
        $pimUser  = $this->createUser('pimpinan');
        $pimpinan = $this->createPimpinan($pimUser, ['nama_lengkap' => 'Nama Lama']);

        $response = $this->actingAs($user)
            ->put("/super-admin/pengguna/internal/pimpinan/{$pimpinan->id}", [
                'nama_lengkap' => 'Nama Baru',
                'email'        => $pimUser->email,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertEquals('Nama Baru', $pimpinan->fresh()->nama_lengkap);
    }

    public function test_super_admin_dapat_menghapus_pimpinan(): void
    {
        $user     = $this->createUser('super_admin');
        $pimUser  = $this->createUser('pimpinan');
        $pimpinan = $this->createPimpinan($pimUser);

        $response = $this->actingAs($user)
            ->delete("/super-admin/pengguna/internal/pimpinan/{$pimpinan->id}");

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('pimpinan', ['id' => $pimpinan->id]);
    }
}
