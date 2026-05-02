<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property string $tiket_id
 * @property string $status_tiket
 * @property string|null $spesifikasi_perangkat_rusak
 * @property string|null $rekomendasi
 * @property string|null $file_rekomendasi
 * @property string|null $catatan
 */
class StatusTiket extends Model
{
    protected $table    = 'status_tiket';
    public $incrementing = false;
    protected $keyType  = 'string';
    public $timestamps  = false;

    protected $casts = [
        'created_at' => 'datetime',
    ];

    protected $fillable = [
        'id', 'tiket_id', 'status_tiket',
        'spesifikasi_perangkat_rusak',
        'rekomendasi', 'file_rekomendasi', 'catatan', 'file_bukti',
        'created_at',
    ];

    public function tiket()
    {
        return $this->belongsTo(Tiket::class);
    }
}
