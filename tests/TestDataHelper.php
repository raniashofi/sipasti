<?php

namespace Tests;

use App\Models\AdminHelpdesk;
use App\Models\Bidang;
use App\Models\KategoriSistem;
use App\Models\NodeDiagnosis;
use App\Models\Opd;
use App\Models\Pimpinan;
use App\Models\StatusTiket;
use App\Models\Tiket;
use App\Models\TiketTeknisi;
use App\Models\TimTeknis;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * Trait helper untuk membuat data mock yang sering digunakan di unit test.
 * Memastikan semua field NOT NULL terisi agar tidak error di SQLite in-memory.
 */
trait TestDataHelper
{
    protected function createBidang(array $overrides = []): Bidang
    {
        return Bidang::create(array_merge([
            'id' => 'BDG-' . uniqid(),
            'nama_bidang' => 'Bidang Test',
            'batas_hari_pengerjaan' => 3,
        ], $overrides));
    }

    protected function createUser(string $role = 'opd', array $overrides = []): User
    {
        return User::create(array_merge([
            'id'       => 'USR-' . uniqid(),
            'email'    => $role . '-' . uniqid() . '@test.com',
            'password' => bcrypt('password'),
            'role'     => $role,
        ], $overrides));
    }

    protected function createOpd(?User $user = null, array $overrides = []): Opd
    {
        $user = $user ?? $this->createUser('opd');
        return Opd::create(array_merge([
            'id'       => 'OPD-' . uniqid(),
            'user_id'  => $user->id,
            'kode_opd' => 'KD-' . uniqid(),
            'nama_opd' => 'Dinas Test',
        ], $overrides));
    }

    protected function createAdmin(?Bidang $bidang = null, array $overrides = []): array
    {
        $bidang = $bidang ?? $this->createBidang();
        $user = $this->createUser('admin_helpdesk');
        $admin = AdminHelpdesk::create(array_merge([
            'id'            => 'AH-' . uniqid(),
            'user_id'       => $user->id,
            'nama_lengkap'  => 'Admin Test',
            'bidang_id'     => $bidang->id,
        ], $overrides));
        return [$user, $admin, $bidang];
    }

    protected function createTeknisi(?Bidang $bidang = null, array $overrides = []): array
    {
        $bidang = $bidang ?? $this->createBidang();
        $user = $this->createUser('tim_teknis');
        $teknis = TimTeknis::create(array_merge([
            'id'            => 'TT-' . uniqid(),
            'user_id'       => $user->id,
            'nama_lengkap'  => 'Teknisi Test',
            'bidang_id'     => $bidang->id,
        ], $overrides));
        return [$user, $teknis, $bidang];
    }

    protected function createPimpinanUser(array $overrides = []): User
    {
        $user = $this->createUser('pimpinan');
        Pimpinan::create(array_merge([
            'id'            => 'PMP-' . uniqid(),
            'user_id'       => $user->id,
            'nama_lengkap'  => 'Pimpinan Test',
        ], $overrides));
        return $user;
    }

    /**
     * Membuat Tiket beserta KategoriSistem dan NodeDiagnosis yang diperlukan.
     *
     * PENTING: Tabel `tiket` TIDAK memiliki kolom `bidang_id` maupun `kategori_id`.
     * Kedua field itu adalah accessor yang di-derive dari relasi `node_diagnosis`.
     * Jadi bidang_id di-set pada NodeDiagnosis, bukan pada Tiket.
     */
    protected function createTiket(string $opdId, string $bidangId, string $status = 'verifikasi_admin', array $overrides = []): Tiket
    {
        // Buat KategoriSistem
        $kategori = KategoriSistem::first() ?? KategoriSistem::create([
            'id'             => (string) Str::uuid(),
            'nama_kategori'  => 'Kategori Test',
        ]);

        // Buat NodeDiagnosis — bidang_id di sini, BUKAN di tiket
        $node = NodeDiagnosis::create([
            'id'          => (string) Str::uuid(),
            'kategori_id' => $kategori->id,
            'bidang_id'   => $bidangId,
            'tipe_node'   => 'solusi',
            'judul_solusi' => 'Solusi Test',
        ]);

        $tiketId = 'TKT-' . strtoupper(Str::random(10));
        $tiket = Tiket::create(array_merge([
            'id'                => $tiketId,
            'opd_id'            => $opdId,
            'subjek_masalah'    => 'Test Problem',
            'detail_masalah'    => 'Detail test',
            'node_diagnosis_id' => $node->id,
        ], $overrides));

        StatusTiket::create([
            'id'           => 'STS-' . strtoupper(Str::random(10)),
            'tiket_id'     => $tiketId,
            'status_tiket' => $status,
            'created_at'   => now(),
        ]);

        return $tiket;
    }
}
