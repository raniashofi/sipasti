<?php

namespace Tests\Helpers;

use App\Models\AdminHelpdesk;
use App\Models\Bidang;
use App\Models\KategoriArtikel;
use App\Models\KategoriSistem;
use App\Models\KnowledgeBase;
use App\Models\NodeDiagnosis;
use App\Models\Opd;
use App\Models\Pimpinan;
use App\Models\StatusTiket;
use App\Models\Tiket;
use App\Models\TiketTeknisi;
use App\Models\TimTeknis;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

trait CreatesTestModels
{
    private int $statusTimestampOffset = 0;
    protected function createUser(string $role): User
    {
        return User::create([
            'id'       => (string) Str::uuid(),
            'email'    => fake()->unique()->safeEmail(),
            'password' => Hash::make('password'),
            'role'     => $role,
        ]);
    }

    protected function createBidang(array $attrs = []): Bidang
    {
        return Bidang::create(array_merge([
            'id'          => (string) Str::uuid(),
            'nama_bidang' => fake()->word() . '_' . Str::random(4),
        ], $attrs));
    }

    protected function createOpd(User $user, array $attrs = []): Opd
    {
        return Opd::create(array_merge([
            'id'       => (string) Str::uuid(),
            'user_id'  => $user->id,
            'kode_opd' => 'OPD-' . strtoupper(Str::random(6)),
            'nama_opd' => fake()->company(),
        ], $attrs));
    }

    protected function createAdminHelpdesk(User $user, Bidang $bidang, array $attrs = []): AdminHelpdesk
    {
        return AdminHelpdesk::create(array_merge([
            'id'           => (string) Str::uuid(),
            'user_id'      => $user->id,
            'bidang_id'    => $bidang->id,
            'nama_lengkap' => fake()->name(),
        ], $attrs));
    }

    protected function createTimTeknis(User $user, Bidang $bidang, array $attrs = []): TimTeknis
    {
        return TimTeknis::create(array_merge([
            'id'             => (string) Str::uuid(),
            'user_id'        => $user->id,
            'bidang_id'      => $bidang->id,
            'nama_lengkap'   => fake()->name(),
            'status_teknisi' => 'online',
        ], $attrs));
    }

    protected function createPimpinan(User $user, array $attrs = []): Pimpinan
    {
        return Pimpinan::create(array_merge([
            'id'           => (string) Str::uuid(),
            'user_id'      => $user->id,
            'nama_lengkap' => fake()->name(),
        ], $attrs));
    }

    protected function createKategoriSistem(array $attrs = []): KategoriSistem
    {
        return KategoriSistem::create(array_merge([
            'id'            => (string) Str::uuid(),
            'nama_kategori' => fake()->words(3, true),
            'icon'          => 'default',
        ], $attrs));
    }

    protected function createKategoriArtikel(array $attrs = []): KategoriArtikel
    {
        return KategoriArtikel::create(array_merge([
            'id'            => (string) Str::uuid(),
            'nama_kategori' => fake()->words(3, true),
        ], $attrs));
    }

    protected function createKnowledgeBase(array $attrs = []): KnowledgeBase
    {
        return KnowledgeBase::create(array_merge([
            'id'                => (string) Str::uuid(),
            'nama_artikel_sop'  => fake()->sentence(),
            'isi_konten'        => '<p>' . fake()->paragraph() . '</p>',
            'deskripsi_singkat' => fake()->sentence(),
            'status_publikasi'  => 'published',
            'visibilitas_akses' => 'opd',
            'total_views'       => 0,
            'rating'            => 0,
            'rating_count'      => 0,
        ], $attrs));
    }

    protected function createTiket(Opd $opd, array $attrs = []): Tiket
    {
        return Tiket::create(array_merge([
            'id'             => 'TKT-' . strtoupper(Str::random(10)),
            'opd_id'         => $opd->id,
            'subjek_masalah' => fake()->sentence(),
            'detail_masalah' => fake()->paragraph(),
        ], $attrs));
    }

    protected function createStatusTiket(Tiket $tiket, string $status, array $attrs = []): StatusTiket
    {
        $defaults = [
            'id'           => 'STS-' . strtoupper(Str::random(10)),
            'tiket_id'     => $tiket->id,
            'status_tiket' => $status,
            'catatan'      => null,
            'created_at'   => now()->addSeconds($this->statusTimestampOffset++),
        ];
        return StatusTiket::create(array_merge($defaults, $attrs));
    }

    protected function createTiketTeknisi(Tiket $tiket, TimTeknis $teknisi, array $attrs = []): TiketTeknisi
    {
        return TiketTeknisi::create(array_merge([
            'tiket_id'         => $tiket->id,
            'teknis_id'        => $teknisi->id,
            'peran_teknisi'    => 'teknisi_utama',
            'waktu_ditugaskan' => now(),
            'status_tugas'     => 'aktif',
        ], $attrs));
    }
}
