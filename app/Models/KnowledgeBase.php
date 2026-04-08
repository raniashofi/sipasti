<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string      $id
 * @property string|null $kategori_id
 * @property string      $nama_artikel_sop
 * @property string|null $deskripsi_singkat
 * @property string      $isi_konten
 * @property string|null $header_image
 * @property string      $status_publikasi
 * @property string      $visibilitas_akses
 * @property int         $total_views
 * @property float|null  $rating
 */
class KnowledgeBase extends Model
{
    use SoftDeletes;

    protected $table = 'knowledge_base';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = true;

    protected $fillable = [
        'id', 'kategori_id', 'nama_artikel_sop', 'deskripsi_singkat',
        'isi_konten', 'status_publikasi', 'visibilitas_akses',
        'header_image', 'lampiran_file',
        'total_views', 'rating',
    ];

    protected $dates = ['deleted_at'];

    /**
     * Relationship: Kategori
     */
    public function kategori()
    {
        return $this->belongsTo(Kategori::class);
    }

    /**
     * Relationship: Tags (Many-to-Many)
     */
    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'knowledge_base_tag');
    }

    /**
     * Relationship: Lampiran Files
     */
    public function lampirans()
    {
        return $this->hasMany(LampiranArtikel::class, 'knowledge_base_id');
    }

    /**
     * Relationship: Nodes Diagnosis
     */
    public function nodes()
    {
        return $this->hasMany(NodeDiagnosis::class, 'kb_id');
    }
}
