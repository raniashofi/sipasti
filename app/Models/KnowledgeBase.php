<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string      $id
 * @property string|null $kategori_id
 * @property string|null $nama_artikel_sop
 * @property string|null $isi_konten
 * @property string      $status_publikasi
 * @property string      $visibilitas_akses
 */
class KnowledgeBase extends Model
{
    protected $table = 'knowledge_base';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    public function kategori()
    {
        return $this->belongsTo(Kategori::class);
    }

    public function tags()
    {
        return $this->hasMany(Tag::class, 'kb_id');
    }

    public function nodes()
    {
        return $this->hasMany(NodeDiagnosis::class, 'kb_id');
    }
}
