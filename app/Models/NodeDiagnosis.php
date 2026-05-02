<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string      $id
 * @property string|null $kb_id
 * @property string|null $bidang_id
 * @property string      $tipe_node
 * @property string|null $teks_pertanyaan
 * @property string|null $hint_konteks
 * @property string|null $judul_solusi
 * @property string|null $penjelasan_solusi
 * @property string|null $rekomendasi_penanganan
 * @property string|null $id_next_ya
 * @property string|null $id_next_tidak
 */
class NodeDiagnosis extends Model
{
    protected $table = 'node_diagnosis';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = true;

    protected $fillable = [
        'id', 'kategori_id', 'kb_id', 'sop_internal_id', 'bidang_id', 'tipe_node',
        'teks_pertanyaan', 'hint_konteks',
        'judul_solusi', 'penjelasan_solusi', 'rekomendasi_penanganan',
        'id_next_ya', 'id_next_tidak',
    ];

    public function kategori()
    {
        return $this->belongsTo(KategoriSistem::class, 'kategori_id');
    }

    public function bidang()
    {
        return $this->belongsTo(Bidang::class, 'bidang_id');
    }

    public function knowledgeBase()
    {
        return $this->belongsTo(KnowledgeBase::class, 'kb_id');
    }

    public function sopInternal()
    {
        return $this->belongsTo(KnowledgeBase::class, 'sop_internal_id');
    }

    public function nextYa()
    {
        return $this->belongsTo(NodeDiagnosis::class, 'id_next_ya');
    }

    public function nextTidak()
    {
        return $this->belongsTo(NodeDiagnosis::class, 'id_next_tidak');
    }
}
