<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TiketTeknisi extends Model
{
    protected $table = 'tiket_teknisi';
    public $timestamps = false;

    protected $fillable = ['tiket_id', 'teknis_id', 'peran_teknisi', 'waktu_ditugaskan', 'status_tugas', 'alasan_dibatalkan'];

    protected $casts = [
        'waktu_ditugaskan' => 'datetime',
    ];

    public function tiket()
    {
        return $this->belongsTo(Tiket::class, 'tiket_id');
    }

    public function timTeknis()
    {
        return $this->belongsTo(TimTeknis::class, 'teknis_id');
    }
}
