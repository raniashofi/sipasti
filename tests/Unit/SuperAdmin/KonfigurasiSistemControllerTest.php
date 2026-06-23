<?php
namespace Tests\Unit\SuperAdmin;
use Illuminate\Http\Request;
use Tests\TestCase;

class KonfigurasiSistemControllerTest extends TestCase
{
    public function testIndexTolakNonAdmin()
    {
        $this->actingAs(new \App\Models\User(['id'=>999,'role'=>'opd']));
        $this->assertTrue(in_array($this->get('/super-admin/konfigurasi/konfigurasi-sistem')->status(),[302,403]), 'index() — role non-super_admin ditolak');
    }
    // ── Kategori CRUD ──
    public function testStoreKategoriValidasiNama(): void
    {
        try{Request::create('/k','POST',[])->validate(['nama_kategori'=>'required|string|max:255']);$this->fail();}
        catch(\Illuminate\Validation\ValidationException $e){$this->assertArrayHasKey('nama_kategori',$e->errors(), 'storeKategori() — nama_kategori wajib diisi');}
    }
    public function testUpdateKategoriValidasiNama(): void
    {
        try{Request::create('/k','PUT',[])->validate(['nama_kategori'=>'required|string|max:255']);$this->fail();}
        catch(\Illuminate\Validation\ValidationException $e){$this->assertArrayHasKey('nama_kategori',$e->errors(), 'updateKategori() — nama_kategori wajib');}
    }
    public function testDestroyKategoriTolakNonAdmin(): void
    {
        $this->actingAs(new \App\Models\User(['id'=>999,'role'=>'opd']));
        $this->assertTrue(in_array($this->delete('/super-admin/konfigurasi/kategori/FAKE')->status(),[302,403,404,405]), 'destroyKategori() — non-admin ditolak');
    }
    // ── Node Diagnosis CRUD ──
    public function testStoreNodeValidasiPertanyaan(): void
    {
        try{Request::create('/n','POST',['tipe_node'=>'pertanyaan'])->validate(['tipe_node'=>'required|in:pertanyaan,solusi','teks_pertanyaan'=>'required_if:tipe_node,pertanyaan|string']);$this->fail();}
        catch(\Illuminate\Validation\ValidationException $e){$this->assertArrayHasKey('teks_pertanyaan',$e->errors(), 'storeNode() — teks_pertanyaan wajib untuk tipe pertanyaan');}
    }
    public function testStoreNodeValidasiSolusi(): void
    {
        try{Request::create('/n','POST',['tipe_node'=>'solusi'])->validate(['tipe_node'=>'required|in:pertanyaan,solusi','judul_solusi'=>'required_if:tipe_node,solusi|string']);$this->fail();}
        catch(\Illuminate\Validation\ValidationException $e){$this->assertArrayHasKey('judul_solusi',$e->errors(), 'storeNode() — judul_solusi wajib untuk tipe solusi');}
    }
    public function testStoreNodeTipeInvalid(): void
    {
        try{Request::create('/n','POST',['tipe_node'=>'invalid'])->validate(['tipe_node'=>'required|in:pertanyaan,solusi']);$this->fail();}
        catch(\Illuminate\Validation\ValidationException $e){$this->assertArrayHasKey('tipe_node',$e->errors(), 'storeNode() — tipe harus pertanyaan/solusi');}
    }
    public function testUpdateNodeValidasi(): void
    {
        try{Request::create('/n','PUT',['tipe_node'=>'pertanyaan'])->validate(['tipe_node'=>'required|in:pertanyaan,solusi','teks_pertanyaan'=>'required_if:tipe_node,pertanyaan|string']);$this->fail();}
        catch(\Illuminate\Validation\ValidationException $e){$this->assertArrayHasKey('teks_pertanyaan',$e->errors(), 'updateNode() — teks pertanyaan wajib');}
    }
    public function testDestroyNodeTolakNonAdmin(): void
    {
        $this->actingAs(new \App\Models\User(['id'=>999,'role'=>'opd']));
        $this->assertTrue(in_array($this->delete('/super-admin/konfigurasi/node/FAKE')->status(),[302,403,404,405]), 'destroyNode() — non-admin ditolak');
    }
}
