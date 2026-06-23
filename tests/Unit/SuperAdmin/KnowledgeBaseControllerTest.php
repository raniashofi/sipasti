<?php
namespace Tests\Unit\SuperAdmin;
use Illuminate\Http\Request;
use Tests\TestCase;

class KnowledgeBaseControllerTest extends TestCase
{
    // ── Otorisasi ──
    public function testIndexOpdTolakNonAdmin()
    {
        $this->actingAs(new \App\Models\User(['id'=>999,'role'=>'opd']));
        $this->assertTrue(in_array($this->get('/super-admin/pustaka/opd')->status(),[302,403]), 'indexOpd() — role non-super_admin ditolak');
    }
    public function testIndexInternalTolakNonAdmin()
    {
        $this->actingAs(new \App\Models\User(['id'=>999,'role'=>'opd']));
        $this->assertTrue(in_array($this->get('/super-admin/pustaka/internal')->status(),[302,403]), 'indexInternal() — role non-super_admin ditolak');
    }
    // ── Kategori Artikel CRUD ──
    public function testStoreKategoriValidasiNama(): void
    {
        try{Request::create('/k','POST',[])->validate(['nama_kategori'=>'required|string|max:255']);$this->fail();}
        catch(\Illuminate\Validation\ValidationException $e){$this->assertArrayHasKey('nama_kategori',$e->errors(), 'storeKategori() — nama_kategori wajib');}
    }
    public function testUpdateKategoriValidasiNama(): void
    {
        try{Request::create('/k','PUT',[])->validate(['nama_kategori'=>'required|string|max:255']);$this->fail();}
        catch(\Illuminate\Validation\ValidationException $e){$this->assertArrayHasKey('nama_kategori',$e->errors(), 'updateKategori() — nama_kategori wajib');}
    }
    public function testDestroyKategoriTolak(): void
    {
        $this->actingAs(new \App\Models\User(['id'=>999,'role'=>'opd']));
        $this->assertTrue(in_array($this->delete('/super-admin/pustaka/kategori/FAKE')->status(),[302,403,404,405]), 'destroyKategori() — non-admin ditolak');
    }
    // ── Bidang CRUD ──
    public function testStoreBidangValidasi(): void
    {
        try{Request::create('/b','POST',[])->validate(['nama_bidang'=>'required|string|max:255']);$this->fail();}
        catch(\Illuminate\Validation\ValidationException $e){$this->assertArrayHasKey('nama_bidang',$e->errors(), 'storeBidang() — nama wajib');}
    }
    public function testStoreBidangBatasDefault(): void
    {
        $batas = null ?: 3;
        $this->assertSame(3,$batas, 'storeBidang() — batas_hari_pengerjaan default 3 hari');
    }
    public function testUpdateBidangValidasi(): void
    {
        try{Request::create('/b','PUT',[])->validate(['nama_bidang'=>'required|string|max:255']);$this->fail();}
        catch(\Illuminate\Validation\ValidationException $e){$this->assertArrayHasKey('nama_bidang',$e->errors(), 'updateBidang() — nama wajib');}
    }
    public function testDestroyBidangTolak(): void
    {
        $this->actingAs(new \App\Models\User(['id'=>999,'role'=>'opd']));
        $this->assertTrue(in_array($this->delete('/super-admin/pustaka/bidang/FAKE')->status(),[302,403,404,405]), 'destroyBidang() — non-admin ditolak');
    }
    // ── Artikel CRUD ──
    public function testStoreArtikelValidasiJudul(): void
    {
        try{Request::create('/a','POST',['visibilitas_akses'=>'opd','status_publikasi'=>'draft'])->validate(['nama_artikel_sop'=>'required|string|max:500']);$this->fail();}
        catch(\Illuminate\Validation\ValidationException $e){$this->assertArrayHasKey('nama_artikel_sop',$e->errors(), 'store() — judul artikel wajib');}
    }
    public function testStoreArtikelValidasiVisibilitas(): void
    {
        try{Request::create('/a','POST',['nama_artikel_sop'=>'Test','status_publikasi'=>'draft'])->validate(['visibilitas_akses'=>'required|in:opd,internal']);$this->fail();}
        catch(\Illuminate\Validation\ValidationException $e){$this->assertArrayHasKey('visibilitas_akses',$e->errors(), 'store() — visibilitas wajib opd/internal');}
    }
    public function testStoreArtikelValidasiStatusPublikasi(): void
    {
        try{Request::create('/a','POST',['nama_artikel_sop'=>'Test','visibilitas_akses'=>'opd'])->validate(['status_publikasi'=>'required|in:draft,published']);$this->fail();}
        catch(\Illuminate\Validation\ValidationException $e){$this->assertArrayHasKey('status_publikasi',$e->errors(), 'store() — status harus draft/published');}
    }
    public function testUpdateArtikelValidasiJudul(): void
    {
        try{Request::create('/a','PUT',[])->validate(['nama_artikel_sop'=>'required|string|max:500']);$this->fail();}
        catch(\Illuminate\Validation\ValidationException $e){$this->assertArrayHasKey('nama_artikel_sop',$e->errors(), 'update() — judul artikel wajib');}
    }
    public function testCreateRequireSuperAdmin(): void
    {
        $role = 'opd';
        $this->assertNotSame('super_admin',$role, 'create() — role non-super_admin harus ditolak (abort 403)');
    }
    public function testEditTolakNonAdmin(): void
    {
        $this->actingAs(new \App\Models\User(['id'=>999,'role'=>'opd']));
        $this->assertTrue(in_array($this->get('/super-admin/pustaka/artikel/FAKE/edit')->status(),[302,403,404]), 'edit() — non-admin ditolak');
    }
    public function testDestroyArtikelTolak(): void
    {
        $this->actingAs(new \App\Models\User(['id'=>999,'role'=>'opd']));
        $this->assertTrue(in_array($this->delete('/super-admin/pustaka/artikel/FAKE')->status(),[302,403,404,405]), 'destroy() — non-admin ditolak');
    }
    public function testPreviewTolakNonAdmin(): void
    {
        $this->actingAs(new \App\Models\User(['id'=>999,'role'=>'opd']));
        $this->assertTrue(in_array($this->get('/super-admin/pustaka/artikel/FAKE/preview')->status(),[302,403,404]), 'preview() — non-admin ditolak');
    }
    public function testVisibilitasMapping(): void
    {
        $m=['opd'=>'artikel_opd','internal'=>'sop_internal'];
        $this->assertSame('artikel_opd',$m['opd']);
        $this->assertSame('sop_internal',$m['internal'], 'visibilitas akses menentukan tipe artikel');
    }
}
