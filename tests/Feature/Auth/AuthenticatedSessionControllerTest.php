<?php

namespace Tests\Feature\Auth;

use App\Models\Opd;
use App\Models\StatusTiket;
use App\Models\Tiket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\Helpers\CreatesTestModels;
use Tests\TestCase;

class AuthenticatedSessionControllerTest extends TestCase
{
    use RefreshDatabase, CreatesTestModels;

    // ─── Halaman Login ────────────────────────────────────────────

    public function test_halaman_login_dapat_diakses_oleh_tamu(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_halaman_login_tidak_dapat_diakses_user_yang_sudah_login(): void
    {
        $user = $this->createUser('opd');

        $response = $this->actingAs($user)->get('/login');

        $response->assertRedirect();
    }

    // ─── Login ────────────────────────────────────────────────────

    public function test_login_berhasil_dengan_kredensial_valid(): void
    {
        $user = $this->createUser('opd');

        $response = $this->post('/login', [
            'email'    => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
    }

    public function test_login_gagal_dengan_password_salah(): void
    {
        $user = $this->createUser('opd');

        $this->post('/login', [
            'email'    => $user->email,
            'password' => 'password-salah',
        ]);

        $this->assertGuest();
    }

    public function test_login_gagal_dengan_email_tidak_terdaftar(): void
    {
        $this->post('/login', [
            'email'    => 'tidakada@example.com',
            'password' => 'password',
        ]);

        $this->assertGuest();
    }

    public function test_login_gagal_jika_email_tidak_diisi(): void
    {
        $response = $this->post('/login', [
            'email'    => '',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_login_gagal_jika_password_tidak_diisi(): void
    {
        $response = $this->post('/login', [
            'email'    => 'test@example.com',
            'password' => '',
        ]);

        $response->assertSessionHasErrors('password');
    }

    // ─── Redirect Berdasarkan Role ─────────────────────────────────

    public function test_super_admin_diarahkan_ke_dashboard_super_admin_setelah_login(): void
    {
        $user = $this->createUser('super_admin');

        $response = $this->post('/login', [
            'email'    => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('super_admin.dashboard'));
    }

    public function test_admin_helpdesk_diarahkan_ke_dashboard_admin_helpdesk_setelah_login(): void
    {
        $user = $this->createUser('admin_helpdesk');

        $response = $this->post('/login', [
            'email'    => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin_helpdesk.dashboard'));
    }

    public function test_tim_teknis_diarahkan_ke_antrean_setelah_login(): void
    {
        $user = $this->createUser('tim_teknis');

        $response = $this->post('/login', [
            'email'    => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('tim_teknis.antrean'));
    }

    public function test_opd_diarahkan_ke_dashboard_opd_setelah_login(): void
    {
        $user = $this->createUser('opd');
        $this->createOpd($user);

        $response = $this->post('/login', [
            'email'    => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('opd.dashboard'));
    }

    public function test_pimpinan_diarahkan_ke_dashboard_pimpinan_setelah_login(): void
    {
        $user = $this->createUser('pimpinan');

        $response = $this->post('/login', [
            'email'    => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('pimpinan.dashboard'));
    }

    // ─── Update last_login_at ─────────────────────────────────────

    public function test_last_login_at_diperbarui_setelah_login(): void
    {
        $user = $this->createUser('opd');

        $this->post('/login', [
            'email'    => $user->email,
            'password' => 'password',
        ]);

        $this->assertNotNull($user->fresh()->last_login_at);
    }

    // ─── Logout ───────────────────────────────────────────────────

    public function test_logout_berhasil_dan_session_dihapus(): void
    {
        $user = $this->createUser('opd');

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }

    public function test_logout_memerlukan_autentikasi(): void
    {
        $response = $this->post('/logout');

        $response->assertRedirect('/login');
    }

    // ─── Auto-close Tiket OPD ─────────────────────────────────────

    public function test_tiket_opd_ditutup_otomatis_jika_lebih_7_hari_selesai_tanpa_konfirmasi(): void
    {
        $user = $this->createUser('opd');
        $opd  = $this->createOpd($user);

        $tiket = $this->createTiket($opd);
        StatusTiket::create([
            'id'           => 'STS-' . strtoupper(Str::random(10)),
            'tiket_id'     => $tiket->id,
            'status_tiket' => 'selesai',
            'created_at'   => now()->subDays(8),
        ]);

        $this->post('/login', [
            'email'    => $user->email,
            'password' => 'password',
        ]);

        $statusTerbaru = StatusTiket::where('tiket_id', $tiket->id)
            ->orderByDesc('created_at')
            ->value('status_tiket');

        $this->assertEquals('tiket_ditutup', $statusTerbaru);
    }

    public function test_tiket_opd_tidak_ditutup_jika_kurang_dari_7_hari(): void
    {
        $user = $this->createUser('opd');
        $opd  = $this->createOpd($user);

        $tiket = $this->createTiket($opd);
        StatusTiket::create([
            'id'           => 'STS-' . strtoupper(Str::random(10)),
            'tiket_id'     => $tiket->id,
            'status_tiket' => 'selesai',
            'created_at'   => now()->subDays(3),
        ]);

        $this->post('/login', [
            'email'    => $user->email,
            'password' => 'password',
        ]);

        $countTiketDitutup = StatusTiket::where('tiket_id', $tiket->id)
            ->where('status_tiket', 'tiket_ditutup')
            ->count();

        $this->assertEquals(0, $countTiketDitutup);
    }

    public function test_tiket_yang_sudah_ada_penilaian_tidak_ditutup_otomatis(): void
    {
        $user = $this->createUser('opd');
        $opd  = $this->createOpd($user);

        $tiket = $this->createTiket($opd, ['penilaian' => 4]);
        StatusTiket::create([
            'id'           => 'STS-' . strtoupper(Str::random(10)),
            'tiket_id'     => $tiket->id,
            'status_tiket' => 'selesai',
            'created_at'   => now()->subDays(10),
        ]);

        $this->post('/login', [
            'email'    => $user->email,
            'password' => 'password',
        ]);

        $countTiketDitutup = StatusTiket::where('tiket_id', $tiket->id)
            ->where('status_tiket', 'tiket_ditutup')
            ->count();

        $this->assertEquals(0, $countTiketDitutup);
    }
}
