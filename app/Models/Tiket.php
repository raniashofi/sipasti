<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Kategori;
use App\Models\KnowledgeBase;
use App\Models\RiwayatTransferTiket;

class Tiket extends Model
{
    protected $table = 'tiket';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id', 'opd_id', 'admin_id', 'kb_id', 'kategori_id', 'prioritas',
        'subjek_masalah', 'detail_masalah', 'lokasi',
        'foto_bukti', 'spesifikasi_perangkat',
        'penilaian', 'komentar_penutupan',
    ];

    public function opd()
    {
        return $this->belongsTo(Opd::class);
    }

    public function kb()
    {
        return $this->belongsTo(KnowledgeBase::class, 'kb_id');
    }

    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'kategori_id');
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
        return $this->hasOne(StatusTiket::class)->latestOfMany('id');
    }

    public function riwayatTransfer()
    {
        return $this->hasMany(RiwayatTransferTiket::class);
    }

    public function tiketTeknisi()
    {
        return $this->hasMany(TiketTeknisi::class);
    }

    public function teknisiUtama()
    {
        return $this->hasOne(TiketTeknisi::class)->where('peran_teknisi', 'utama');
    }

    public function chatRooms()
    {
        return $this->hasMany(ChatRoom::class);
    }
}
