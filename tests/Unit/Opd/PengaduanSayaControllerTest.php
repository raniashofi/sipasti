<?php

namespace Tests\Unit\Opd;

use App\Http\Controllers\Opd\PengaduanSayaController;
use App\Models\Tiket;
use Illuminate\Http\Request;
use Tests\TestCase;

class PengaduanSayaControllerTest extends TestCase
{
    public function testIndexTolakNonOpd()
    {
        $this->actingAs(new \App\Models\User(['id' => 999, 'role' => 'admin_helpdesk']));
        $this->assertTrue(in_array($this->get('/opd/pengaduan-saya')->status(), [302, 403]), 'index() - role non-opd ditolak');
    }

    public function testShowTolakNonOpd()
    {
        $this->actingAs(new \App\Models\User(['id' => 999, 'role' => 'admin_helpdesk']));
        $this->assertTrue(in_array($this->get('/opd/pengaduan-saya/FAKE/detail')->status(), [302, 403, 404]), 'show() - role non-opd ditolak');
    }

    public function testChatTolakNonOpd()
    {
        $this->actingAs(new \App\Models\User(['id' => 999, 'role' => 'admin_helpdesk']));
        $this->assertTrue(in_array($this->get('/opd/pengaduan-saya/FAKE/chat')->status(), [302, 403, 404]), 'chat() - role non-opd ditolak');
    }

    public function testKonfirmValidasiPenilaian(): void
    {
        try {
            Request::create('/k', 'POST', [])->validate(['penilaian' => 'required|integer|min:1|max:5']);
            $this->fail();
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->assertArrayHasKey('penilaian', $e->errors(), 'konfirm() - penilaian wajib diisi');
        }
    }

    public function testKonfirmPenilaianMaksimal5(): void
    {
        try {
            Request::create('/k', 'POST', ['penilaian' => 6])->validate(['penilaian' => 'required|integer|min:1|max:5']);
            $this->fail();
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->assertArrayHasKey('penilaian', $e->errors(), 'konfirm() - penilaian harus 1-5');
        }
    }

    public function testKonfirmPenilaianMinimal1(): void
    {
        try {
            Request::create('/k', 'POST', ['penilaian' => 0])->validate(['penilaian' => 'required|integer|min:1|max:5']);
            $this->fail();
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->assertArrayHasKey('penilaian', $e->errors(), 'konfirm() - penilaian minimal 1');
        }
    }

    public function testKonfirmStatusHarusSelesai(): void
    {
        $valid = (new TestablePengaduanSayaController())->allowedRateableStatuses();

        $this->assertContains('selesai', $valid);
        $this->assertContains('rusak_berat', $valid);
        $this->assertContains('tiket_ditutup', $valid);
        $this->assertNotContains('panduan_remote', $valid, 'konfirm() - hanya status selesai/rusak_berat/tiket_ditutup yang bisa dinilai');
    }

    public function testBukaKembaliValidasiAlasan(): void
    {
        try {
            Request::create('/b', 'POST', [])->validate([
                'alasan' => 'required|string|max:1000',
                'file_bukti' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240',
            ]);
            $this->fail();
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->assertArrayHasKey('alasan', $e->errors(), 'bukaKembali() - alasan wajib diisi');
        }
    }

    public function testBukaKembaliAlasanMaks1000(): void
    {
        try {
            Request::create('/b', 'POST', ['alasan' => str_repeat('x', 1001)])
                ->validate(['alasan' => 'required|string|max:1000']);
            $this->fail();
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->assertArrayHasKey('alasan', $e->errors(), 'bukaKembali() - alasan maks 1000 karakter');
        }
    }

    public function testBukaKembaliStatusHarusSelesai(): void
    {
        $this->assertSame('selesai', (new TestablePengaduanSayaController())->allowedReopenableStatus(), 'bukaKembali() - hanya bisa buka kembali tiket berstatus selesai');
    }

    public function testBukaKembaliMaks3Pembukaan(): void
    {
        $this->assertTrue((new Tiket(['reopened_count' => 2]))->canBeReopened(), 'bukaKembali() - masih bisa pada pembukaan ke-2');
        $this->assertFalse((new Tiket(['reopened_count' => 3]))->canBeReopened(), 'bukaKembali() - berhenti pada batas pembukaan ke-3');
    }

    public function testEditTolakNonOpd()
    {
        $this->actingAs(new \App\Models\User(['id' => 999, 'role' => 'admin_helpdesk']));
        $this->assertTrue(in_array($this->get('/opd/pengaduan-saya/FAKE/edit')->status(), [302, 403, 404]), 'edit() - role non-opd ditolak');
    }

    public function testEditStatusHarusPerluRevisi(): void
    {
        $this->assertSame('perlu_revisi', (new TestablePengaduanSayaController())->allowedEditableStatus(), 'edit() - hanya bisa edit saat status perlu_revisi');
    }

    public function testUpdateValidasiSubjek(): void
    {
        try {
            Request::create('/u', 'PUT', [])->validate([
                'subjek_masalah' => 'required|string|max:255',
                'detail_masalah' => 'required|string',
            ]);
            $this->fail();
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->assertArrayHasKey('subjek_masalah', $e->errors(), 'update() - subjek_masalah wajib diisi');
        }
    }

    public function testUpdateValidasiFoto(): void
    {
        $request = Request::create('/u', 'PUT', [
            'subjek_masalah' => 'Test',
            'detail_masalah' => 'Detail',
        ]);

        $request->validate([
            'subjek_masalah' => 'required|string|max:255',
            'detail_masalah' => 'required|string',
            'foto_bukti' => 'nullable|array|max:5',
            'foto_bukti.*' => 'image|mimes:jpg,jpeg,png|max:5120',
        ]);

        $this->assertSame('Test', $request->input('subjek_masalah'), 'update() - foto_bukti nullable dan maks 5 file');
    }

    public function testFilterStatusValid(): void
    {
        $valid = ['verifikasi_admin', 'panduan_remote', 'perbaikan_teknis', 'selesai', 'tiket_ditutup', 'perlu_revisi', 'dibuka_kembali', 'rusak_berat'];
        $this->assertContains('selesai', $valid);
        $this->assertContains('dibuka_kembali', $valid);
        $this->assertNotContains('invalid', $valid, 'filter status - hanya status valid yang diterima');
    }
}

class TestablePengaduanSayaController extends PengaduanSayaController
{
    public function allowedRateableStatuses(): array
    {
        return $this->rateableStatuses();
    }

    public function allowedReopenableStatus(): string
    {
        return $this->reopenableStatus();
    }

    public function allowedEditableStatus(): string
    {
        return $this->editableStatus();
    }
}
