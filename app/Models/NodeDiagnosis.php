<?php

namespace App\Models;

use App\Models\Concerns\HasPrefixedId;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string      $id
 * @property string|null $bidang_id
 * @property string|null $artikel_opd_id
 * @property string|null $sop_internal_id
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
    use HasPrefixedId;

    protected $table = 'node_diagnosis';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = true;
    protected string $idPrefix = 'NDG';

    protected $fillable = [
        'id', 'kategori_id', 'bidang_id', 'artikel_opd_id', 'sop_internal_id', 'tipe_node',
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

    public function artikelOpd()
    {
        return $this->belongsTo(ArtikelOpd::class, 'artikel_opd_id');
    }

    public function sopInternal()
    {
        return $this->belongsTo(SopInternal::class, 'sop_internal_id');
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
