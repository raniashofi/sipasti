<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiwayatTransferTiket extends Model
{
    protected $table = 'riwayat_transfer_tiket';
    public $timestamps = false;

    protected $fillable = [
        'tiket_id',
        'pengirim_admin_id',
        'penerima_admin_id',
        'penerima_bidang_id',
        'alasan_transfer',
        'waktu_transfer',
    ];

    public function tiket()
    {
        return $this->belongsTo(Tiket::class);
    }

    public function pengirim()
    {
        return $this->belongsTo(AdminHelpdesk::class, 'pengirim_admin_id');
    }

    public function penerima()
    {
        return $this->belongsTo(AdminHelpdesk::class, 'penerima_admin_id');
    }

    public function penerimaBidang()
    {
        return $this->belongsTo(Bidang::class, 'penerima_bidang_id');
    }
}
