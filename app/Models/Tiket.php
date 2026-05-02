<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\KategoriSistem;
use App\Models\KnowledgeBase;
use App\Models\NodeDiagnosis;

class Tiket extends Model
{
    protected $table = 'tiket';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id', 'opd_id', 'admin_id', 'kb_id', 'sop_internal_id', 'bidang_id', 'rekomendasi_penanganan', 'kategori_id',
        'subjek_masalah', 'detail_masalah', 'lokasi',
        'foto_bukti', 'spesifikasi_perangkat',
        'penilaian', 'komentar_penutupan',
    ];

    protected $casts = [
        'foto_bukti' => 'array',
    ];

    public function opd()
    {
        return $this->belongsTo(Opd::class);
    }

    public function bidang()
    {
        return $this->belongsTo(Bidang::class, 'bidang_id');
    }

    public function kb()
    {
        return $this->belongsTo(KnowledgeBase::class, 'kb_id');
    }

    public function sopInternal()
    {
        return $this->belongsTo(KnowledgeBase::class, 'sop_internal_id');
    }

    public function kategori()
    {
        return $this->belongsTo(KategoriSistem::class, 'kategori_id');
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
        return $this->hasOne(TiketTeknisi::class)->where('peran_teknisi', 'teknisi_utama');
    }

    public function chatRooms()
    {
        return $this->hasMany(ChatRoom::class);
    }

    public function solutionNode()
    {
        return $this->hasOne(NodeDiagnosis::class, 'kb_id', 'kb_id')
                    ->where('tipe_node', 'solusi');
    }
}
