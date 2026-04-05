<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property string $tiket_id
 * @property string $status_tiket
 * @property string|null $analisis_kerusakan
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

    protected $fillable = [
        'id', 'tiket_id', 'status_tiket',
        'analisis_kerusakan', 'spesifikasi_perangkat_rusak',
        'rekomendasi', 'file_rekomendasi', 'catatan',
    ];

    public function tiket()
    {
        return $this->belongsTo(Tiket::class);
    }
}
