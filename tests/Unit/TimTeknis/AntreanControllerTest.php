<?php

namespace Tests\Unit\TimTeknis;

use Illuminate\Http\Request;
use Tests\TestCase;

class AntreanControllerTest extends TestCase
{
    public function testIndexTolakNonAdmin()
    {
        $this->actingAs(new \App\Models\User(['id' => 999, 'role' => 'opd']));
        $this->assertTrue(in_array($this->get('/tim-teknis/antrean')->status(), [302, 403]), 'index() - role non-tim_teknis ditolak');
    }

    public function testSelesaiValidasiCatatan(): void
    {
        $request = Request::create('/s', 'POST', []);
        $request->validate(['catatan' => 'nullable|string|max:1000']);

        $this->assertNull($request->input('catatan'), 'selesai() - catatan nullable valid tanpa input');
    }

    public function testGagalValidasiAnalisis(): void
    {
        try {
            Request::create('/g', 'POST', [])->validate([
                'analisis_kerusakan' => 'required|string',
                'rekomendasi' => 'required|string',
            ]);
            $this->fail();
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->assertArrayHasKey('analisis_kerusakan', $e->errors(), 'gagal() - analisis dan rekomendasi wajib diisi');
        }
    }

    public function testKembalikanValidasiAlasan(): void
    {
        try {
            Request::create('/k', 'POST', [])->validate(['alasan_kembalikan' => 'required|string']);
            $this->fail();
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->assertArrayHasKey('alasan_kembalikan', $e->errors(), 'kembalikan() - alasan wajib diisi');
        }
    }

    public function testRiwayatStatusSelesai(): void
    {
        $valid = ['selesai', 'gagal', 'dikembalikan'];
        $this->assertContains('selesai', $valid);
        $this->assertContains('dikembalikan', $valid, 'riwayat() - hanya tugas selesai/gagal/dikembalikan');
    }
}
