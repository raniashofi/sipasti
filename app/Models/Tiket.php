<?php

namespace App\Models;

use App\Models\Concerns\HasPrefixedId;
use App\Models\NodeDiagnosis;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string      $id
 * @property string      $opd_id
 * @property string|null $admin_id
 * @property string      $node_diagnosis_id
 * @property string|null $rekomendasi_penanganan
 * @property string      $subjek_masalah
 * @property string      $detail_masalah
 * @property string|null $lokasi
 * @property string|null $spesifikasi_perangkat
 */
class Tiket extends Model
{
    use HasPrefixedId;

    protected $table = 'tiket';
    public $incrementing = false;
    protected $keyType = 'string';
    protected string $idPrefix = 'TKT';
    protected bool $idUsesDate = true;

    protected $fillable = [
        'id', 'opd_id', 'admin_id', 'bidang_id', 'node_diagnosis_id', 'rekomendasi_penanganan',
        'subjek_masalah', 'detail_masalah', 'lokasi',
        'spesifikasi_perangkat',
        'penilaian', 'komentar_penutupan',
        'reopened_count', 'last_reopened_at',
    ];

    protected $casts = [
    ];

    protected static function booted()
    {
        static::creating(function ($tiket) {
            if (empty($tiket->bidang_id) && $tiket->node_diagnosis_id) {
                $node = NodeDiagnosis::find($tiket->node_diagnosis_id);
                if ($node) {
                    $tiket->bidang_id = $node->bidang_id;
                }
            }
        });
    }

    public function opd()
    {
        return $this->belongsTo(Opd::class);
    }

    public function buktiFoto()
    {
        return $this->hasMany(TiketBuktiFoto::class, 'tiket_id')->orderBy('created_at');
    }

    public function bidang()
    {
        return $this->belongsTo(Bidang::class);
    }

    public function kb()
    {
        return $this->hasOneThrough(
            ArtikelOpd::class,
            NodeDiagnosis::class,
            'id',
            'id',
            'node_diagnosis_id',
            'artikel_opd_id'
        );
    }

    public function sopInternal()
    {
        return $this->hasOneThrough(
            SopInternal::class,
            NodeDiagnosis::class,
            'id',
            'id',
            'node_diagnosis_id',
            'sop_internal_id'
        );
    }

    public function kategori()
    {
        return $this->hasOneThrough(
            KategoriSistem::class,
            NodeDiagnosis::class,
            'id',
            'id',
            'node_diagnosis_id',
            'kategori_id'
        );
    }

    public function admin()
    {
        return $this->belongsTo(AdminHelpdesk::class, 'admin_id');
    }

    public function statusTiket()
    {
        return $this->hasMany(StatusTiket::class);
    }

    public function latestStatus()
    {
        return $this->hasOne(StatusTiket::class)->latestOfMany('created_at');
    }

    public function tiketTeknisi()
    {
        return $this->hasMany(TiketTeknisi::class);
    }

    public function teknisiUtama()
    {
        return $this->hasOne(TiketTeknisi::class)
            ->where('peran_teknisi', 'teknisi_utama')
            ->orderByDesc('waktu_ditugaskan');
    }

    public function chatRooms()
    {
        return $this->hasMany(ChatRoom::class);
    }

    public function chatRoom()
    {
        return $this->hasOne(ChatRoom::class);
    }

    // Helper method untuk check apakah tiket ini ditransfer
    public function isTransferred()
    {
        $chatRoom = $this->chatRoom;
        if (!$chatRoom) {
            return false;
        }
        // Check jika ada admin di history (is_active = false)
        return $chatRoom->users()
            ->where('users.role', 'admin_helpdesk')
            ->wherePivot('is_active', false)
            ->exists();
    }

    // Get transferred from admin (admin terakhir yang non-active)
    public function getTransferredFromAdmin()
    {
        $chatRoom = $this->chatRoom;
        if (!$chatRoom) {
            return null;
        }
        // Get admin dengan is_active = false, urutan terbaru
        return $chatRoom->users()
            ->where('users.role', 'admin_helpdesk')
            ->wherePivot('is_active', false)
            ->orderByPivot('sequence_number', 'desc')
            ->first();
    }

    public function solutionNode()
    {
        return $this->belongsTo(NodeDiagnosis::class, 'node_diagnosis_id');
    }

    public function getKategoriIdAttribute(): ?string
    {
        return $this->solutionNode?->kategori_id;
    }

    /**
     * Mendapatkan waktu mulai perhitungan SLA
     * Jika tiket pernah dibuka kembali, gunakan last_reopened_at
     * Jika belum, gunakan created_at
     */
    public function getSlaPeriodStartDate()
    {
        return $this->last_reopened_at ?? $this->created_at;
    }

    /**
     * Check apakah tiket masih bisa dibuka kembali
     * Max 3x: 1x pengajuan awal + 2x pembukaan ulang
     */
    public function canBeReopened(): bool
    {
        return $this->reopened_count < 3;
    }

    /**
     * Mark tiket sebagai dibuka kembali
     * Increment reopened_count dan set last_reopened_at
     */
    public function markAsReopened(): self
    {
        if (!$this->canBeReopened()) {
            throw new \Exception('Tiket tidak bisa dibuka kembali lagi. Sudah mencapai batas maksimal pembukaan (3x)');
        }

        $this->increment('reopened_count');
        $this->update([
            'last_reopened_at' => now(),
            'penilaian'        => null,
        ]);

        return $this;
    }

    /**
     * Get all foto paths for this ticket
     */
    public function getFotoPaths(): array
    {
        if ($this->relationLoaded('buktiFoto')) {
            return $this->buktiFoto
                ->pluck('foto_path')
                ->filter()
                ->values()
                ->toArray();
        }

        return $this->buktiFoto()->pluck('foto_path')->filter()->values()->toArray();
    }

    /**
     * Cek apakah tiket selesai tepat waktu berdasarkan SLA yang di-reset
     * Jika ada last_reopened_at, SLA dihitung dari sana
     */
    public function isCompletedOnTime($completedAtTimestamp = null, $batasHariPengerjaan = null): bool
    {
        if (!$batasHariPengerjaan && $this->bidang) {
            $batasHariPengerjaan = $this->bidang->batas_hari_pengerjaan;
        }

        if (!$batasHariPengerjaan) {
            return true; // Jika tidak ada SLA, dianggap tepat waktu
        }

        $slaPeriodStart = $this->getSlaPeriodStartDate();
        $completedAt = $completedAtTimestamp ?? now();
        $deadline = \Carbon\Carbon::parse($slaPeriodStart)->addDays($batasHariPengerjaan);

        return \Carbon\Carbon::parse($completedAt)->lte($deadline);
    }
}
