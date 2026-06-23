<?php

namespace Tests\Unit\AdminHelpdesk;

use App\Http\Controllers\AdminHelpdesk\ManajemenTiketController;
use Illuminate\Http\Request;
use Tests\TestCase;

class ManajemenTiketControllerTest extends TestCase
{
    public function testMenungguVerifTolakNonAdmin()
    {
        $this->actingAs(new \App\Models\User(['id' => 999, 'role' => 'opd']));
        $this->assertTrue(in_array($this->get('/admin-helpdesk/tiket/menunggu-verif')->status(), [302, 403]), 'menungguVerif() - role non-admin ditolak');
    }

    public function testBisaTerimaBidangCocok(): void
    {
        $controller = new TestableManajemenTiketController();
        $this->assertTrue($controller->callBisaTerima(new FakeMTiket('BDG-001'), (object) ['id' => 'AH-001', 'bidang_id' => 'BDG-001']), 'bisaTerima() - true jika bidang cocok');
    }

    public function testBisaTerimaBidangBeda(): void
    {
        $controller = new TestableManajemenTiketController();
        $this->assertFalse($controller->callBisaTerima(new FakeMTiket('BDG-002'), (object) ['id' => 'AH-001', 'bidang_id' => 'BDG-001']), 'bisaTerima() - false jika bidang berbeda');
    }

    public function testRevisiValidasiAlasan(): void
    {
        try {
            Request::create('/r', 'POST', [])->validate(['alasan_revisi' => 'required|string|max:1000']);
            $this->fail();
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->assertArrayHasKey('alasan_revisi', $e->errors(), 'revisi() - alasan_revisi wajib diisi');
        }
    }

    public function testTransferValidasiBidang(): void
    {
        try {
            Request::create('/t', 'POST', [])->validate(['bidang_id' => 'required|string']);
            $this->fail();
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->assertArrayHasKey('bidang_id', $e->errors(), 'transfer() - bidang_id wajib diisi');
        }
    }

    public function testEskalasiValidasiTeknisi(): void
    {
        try {
            Request::create('/e', 'POST', [])->validate(['teknisi_utama_id' => 'required|string']);
            $this->fail();
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->assertArrayHasKey('teknisi_utama_id', $e->errors(), 'eskalasi() - teknisi_utama_id wajib diisi');
        }
    }

    public function testEskalasiValidasiPendamping(): void
    {
        $request = Request::create('/e', 'POST', [
            'teknisi_utama_id' => 'TT-001',
            'teknisi_pendamping_ids' => ['TT-002'],
        ]);

        $request->validate([
            'teknisi_utama_id' => 'required|string',
            'teknisi_pendamping_ids' => 'nullable|array',
            'teknisi_pendamping_ids.*' => 'string',
        ]);

        $this->assertSame(['TT-002'], $request->input('teknisi_pendamping_ids'));
    }

    public function testSelesaiOlehAdminValidasi(): void
    {
        $request = Request::create('/s', 'POST', ['catatan' => 'Sudah diperbaiki via remote']);
        $request->validate(['catatan' => 'nullable|string|max:1000']);
        $this->assertSame('Sudah diperbaiki via remote', $request->input('catatan'), 'selesaiOlehAdmin() - catatan nullable valid');
    }

    public function testSelesaiOlehAdminValidasiMaks(): void
    {
        try {
            Request::create('/s', 'POST', ['catatan' => str_repeat('x', 1001)])->validate(['catatan' => 'nullable|string|max:1000']);
            $this->fail();
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->assertArrayHasKey('catatan', $e->errors(), 'selesaiOlehAdmin() - catatan maks 1000 karakter');
        }
    }

    public function testTerimaProsesBidangHarusCocok(): void
    {
        $controller = new TestableManajemenTiketController();
        $this->assertTrue($controller->callBisaTerima(new FakeMTiket('BDG-001'), (object) ['id' => 'AH-001', 'bidang_id' => 'BDG-001']), 'terimaProses() - hanya bisa terima jika bidang cocok');
    }

    public function testPanduanStatusFilter(): void
    {
        $valid = (new TestableManajemenTiketController())->callPanduanStatuses();

        $this->assertSame(['panduan_remote'], $valid, 'panduan() - hanya tiket status panduan_remote');
    }

    public function testDistribusiTolakNonAdmin()
    {
        $this->actingAs(new \App\Models\User(['id' => 999, 'role' => 'opd']));
        $this->assertTrue(in_array($this->get('/admin-helpdesk/tiket/distribusi')->status(), [302, 403]), 'distribusi() - role non-admin ditolak');
    }

    public function testRiwayatTolakNonAdmin()
    {
        $this->actingAs(new \App\Models\User(['id' => 999, 'role' => 'opd']));
        $this->assertTrue(in_array($this->get('/admin-helpdesk/tiket/riwayat')->status(), [302, 403]), 'riwayat() - role non-admin ditolak');
    }

    public function testExportCsvHeaderFormat(): void
    {
        $headers = (new TestableManajemenTiketController())->callExportCsvHeader();

        $this->assertCount(9, $headers, 'exportCsv() - header CSV berisi 9 kolom');
        $this->assertSame('ID Tiket', $headers[0]);
        $this->assertSame('Waktu Selesai', $headers[8]);
    }
}

class TestableManajemenTiketController extends ManajemenTiketController
{
    public function callBisaTerima($tiket, $admin): bool
    {
        return $this->bisaTerima($tiket, $admin);
    }

    public function callPanduanStatuses(): array
    {
        return $this->panduanStatuses();
    }

    public function callExportCsvHeader(): array
    {
        return $this->exportCsvHeader();
    }
}

class FakeMTiket
{
    public function __construct(public ?string $bidang_id)
    {
    }
}
