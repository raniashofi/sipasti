<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Helpers\CreatesTestModels;
use Tests\TestCase;

class ActivityLogControllerTest extends TestCase
{
    use RefreshDatabase, CreatesTestModels;

    // ─── Audit Log (Super Admin) ───────────────────────────────────

    public function test_super_admin_dapat_melihat_halaman_audit(): void
    {
        $user = $this->createUser('super_admin');

        $response = $this->actingAs($user)->get('/super-admin/audit');

        $response->assertOk();
        $response->assertViewIs('super_admin.audit');
    }

    public function test_tamu_tidak_dapat_mengakses_audit(): void
    {
        $response = $this->get('/super-admin/audit');

        $response->assertRedirect('/login');
    }

    public function test_role_selain_super_admin_tidak_dapat_mengakses_audit(): void
    {
        $user = $this->createUser('opd');

        $response = $this->actingAs($user)->get('/super-admin/audit');

        $response->assertForbidden();
    }

    public function test_super_admin_dapat_filter_audit_berdasarkan_role(): void
    {
        $user   = $this->createUser('super_admin');
        $bidang = $this->createBidang();
        $ah     = $this->createUser('admin_helpdesk');
        $this->createAdminHelpdesk($ah, $bidang);

        ActivityLog::create([
            'user_id'         => $ah->id,
            'role_pelaku'     => 'admin_helpdesk',
            'jenis_aktivitas' => 'login',
            'ip_address'      => '127.0.0.1',
            'session_id'      => 'test-session',
            'waktu_eksekusi'  => now(),
        ]);

        $response = $this->actingAs($user)
            ->get('/super-admin/audit?role_pelaku=admin_helpdesk');

        $response->assertOk();
        $response->assertViewHas('logs');
    }

    public function test_super_admin_dapat_filter_audit_berdasarkan_jenis_aktivitas(): void
    {
        $user = $this->createUser('super_admin');

        $response = $this->actingAs($user)
            ->get('/super-admin/audit?jenis_aktivitas=login');

        $response->assertOk();
    }

    public function test_super_admin_dapat_filter_audit_berdasarkan_tanggal(): void
    {
        $user = $this->createUser('super_admin');

        $response = $this->actingAs($user)
            ->get('/super-admin/audit?tanggal=' . now()->format('Y-m-d'));

        $response->assertOk();
    }

    public function test_super_admin_dapat_export_audit_csv(): void
    {
        $user = $this->createUser('super_admin');

        $response = $this->actingAs($user)->get('/super-admin/audit/export-csv');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    // ─── Log Admin Helpdesk ────────────────────────────────────────

    public function test_admin_helpdesk_dapat_melihat_log_aktivitas(): void
    {
        $bidang = $this->createBidang();
        $user   = $this->createUser('admin_helpdesk');
        $this->createAdminHelpdesk($user, $bidang);

        $response = $this->actingAs($user)->get('/admin-helpdesk/log');

        $response->assertOk();
        $response->assertViewIs('admin_helpdesk.log');
    }

    public function test_tamu_tidak_dapat_mengakses_log_admin_helpdesk(): void
    {
        $response = $this->get('/admin-helpdesk/log');

        $response->assertRedirect('/login');
    }

    public function test_opd_tidak_dapat_mengakses_log_admin_helpdesk(): void
    {
        $user = $this->createUser('opd');

        $response = $this->actingAs($user)->get('/admin-helpdesk/log');

        $response->assertForbidden();
    }

    public function test_admin_helpdesk_dapat_filter_log_berdasarkan_pencarian(): void
    {
        $bidang = $this->createBidang();
        $user   = $this->createUser('admin_helpdesk');
        $this->createAdminHelpdesk($user, $bidang);

        $response = $this->actingAs($user)
            ->get('/admin-helpdesk/log?search=login');

        $response->assertOk();
        $response->assertViewHas('logs');
    }

    public function test_admin_helpdesk_dapat_export_log_csv(): void
    {
        $bidang = $this->createBidang();
        $user   = $this->createUser('admin_helpdesk');
        $this->createAdminHelpdesk($user, $bidang);

        $response = $this->actingAs($user)->get('/admin-helpdesk/log/export-csv');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    // ─── Log Pimpinan ─────────────────────────────────────────────

    public function test_pimpinan_dapat_melihat_log_aktivitas(): void
    {
        $user = $this->createUser('pimpinan');
        $this->createPimpinan($user);

        $response = $this->actingAs($user)->get('/pimpinan/log');

        $response->assertOk();
        $response->assertViewIs('pimpinan.log');
    }

    public function test_tamu_tidak_dapat_mengakses_log_pimpinan(): void
    {
        $response = $this->get('/pimpinan/log');

        $response->assertRedirect('/login');
    }

    public function test_opd_tidak_dapat_mengakses_log_pimpinan(): void
    {
        $user = $this->createUser('opd');

        $response = $this->actingAs($user)->get('/pimpinan/log');

        $response->assertForbidden();
    }

    public function test_pimpinan_dapat_filter_log_berdasarkan_tanggal(): void
    {
        $user = $this->createUser('pimpinan');
        $this->createPimpinan($user);

        $response = $this->actingAs($user)
            ->get('/pimpinan/log?tanggal=' . now()->format('Y-m-d'));

        $response->assertOk();
    }

    public function test_pimpinan_dapat_export_log_csv(): void
    {
        $user = $this->createUser('pimpinan');
        $this->createPimpinan($user);

        $response = $this->actingAs($user)->get('/pimpinan/log/export-csv');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    // ─── Static Log Methods ────────────────────────────────────────

    public function test_logLogin_mencatat_aktivitas_login_ke_database(): void
    {
        $user = $this->createUser('opd');

        \App\Http\Controllers\ActivityLogController::logLogin($user);

        $this->assertDatabaseHas('activity_log', [
            'user_id'         => $user->id,
            'role_pelaku'     => 'opd',
            'jenis_aktivitas' => 'login',
        ]);
    }

    public function test_logLogout_mencatat_aktivitas_logout_ke_database(): void
    {
        $user = $this->createUser('opd');

        \App\Http\Controllers\ActivityLogController::logLogout($user);

        $this->assertDatabaseHas('activity_log', [
            'user_id'         => $user->id,
            'role_pelaku'     => 'opd',
            'jenis_aktivitas' => 'logout',
        ]);
    }

    public function test_logCreate_mencatat_pembuatan_record(): void
    {
        $user = $this->createUser('super_admin');

        \App\Http\Controllers\ActivityLogController::logCreate(
            userId: $user->id,
            rolePelaku: 'super_admin',
            namaTabel: 'opd',
            idRecord: 'test-id',
            dataAfter: ['nama_opd' => 'Test OPD']
        );

        $this->assertDatabaseHas('activity_log', [
            'user_id'         => $user->id,
            'jenis_aktivitas' => 'create',
            'nama_tabel'      => 'opd',
            'id_record'       => 'test-id',
        ]);
    }

    public function test_logUpdate_mencatat_pembaruan_record(): void
    {
        $user = $this->createUser('super_admin');

        \App\Http\Controllers\ActivityLogController::logUpdate(
            userId: $user->id,
            rolePelaku: 'super_admin',
            namaTabel: 'opd',
            idRecord: 'test-id'
        );

        $this->assertDatabaseHas('activity_log', [
            'user_id'         => $user->id,
            'jenis_aktivitas' => 'update',
            'nama_tabel'      => 'opd',
        ]);
    }

    public function test_logDelete_mencatat_penghapusan_record(): void
    {
        $user = $this->createUser('super_admin');

        \App\Http\Controllers\ActivityLogController::logDelete(
            userId: $user->id,
            rolePelaku: 'super_admin',
            namaTabel: 'opd',
            idRecord: 'test-id'
        );

        $this->assertDatabaseHas('activity_log', [
            'user_id'         => $user->id,
            'jenis_aktivitas' => 'delete',
        ]);
    }

    public function test_logEscalate_mencatat_eskalasi_tiket(): void
    {
        $user = $this->createUser('admin_helpdesk');

        \App\Http\Controllers\ActivityLogController::logEscalate(
            userId: $user->id,
            rolePelaku: 'admin_helpdesk',
            idTicket: 'TKT-ABC123',
            detail: 'Eskalasi ke tim teknis'
        );

        $this->assertDatabaseHas('activity_log', [
            'user_id'         => $user->id,
            'jenis_aktivitas' => 'escalate',
            'nama_tabel'      => 'tiket',
            'id_record'       => 'TKT-ABC123',
        ]);
    }
}
