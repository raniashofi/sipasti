<?php

namespace App\Models;

use App\Models\Concerns\HasPrefixedId;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property string|null $user_id
 * @property string $role_pelaku
 * @property string $jenis_aktivitas
 * @property string|null $detail_tindakan
 * @property string|null $ip_address
 * @property string|null $session_id
 * @property \Carbon\Carbon|null $waktu_eksekusi
 * @property string|null $nama_tabel
 * @property string|null $id_record
 * @property array|null $data_before
 * @property array|null $data_after
 */
class ActivityLog extends Model
{
    use HasPrefixedId;

    protected $table = 'activity_log';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = true;
    protected string $idPrefix = 'LOG';

    protected $fillable = [
        'id',
        'user_id',
        'role_pelaku',
        'jenis_aktivitas',
        'detail_tindakan',
        'ip_address',
        'session_id',
        'waktu_eksekusi',
        'nama_tabel',
        'id_record',
        'data_before',
        'data_after',
    ];

    protected $casts = [
        'waktu_eksekusi' => 'datetime',
        'data_before'    => 'array',
        'data_after'     => 'array',
    ];

    public function getJenisAktivitasAttribute($value)
    {
        $detail = $this->attributes['detail_tindakan'] ?? '';

        if ($value === 'reject' && $this->isLogRusakBeratLama($detail)) {
            return 'approve';
        }

        return $value;
    }

    public function getDetailTindakanAttribute($value)
    {
        if ($this->isLogRusakBeratLama($value)) {
            return str_ireplace(
                'gagal diperbaiki (rusak berat)',
                'selesai dianalisis: aset rusak berat dan memerlukan pengadaan/pergantian',
                $value
            );
        }

        return $value;
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    private function isLogRusakBeratLama($detail): bool
    {
        return is_string($detail)
            && str_contains(strtolower($detail), 'gagal diperbaiki (rusak berat)');
    }
}
