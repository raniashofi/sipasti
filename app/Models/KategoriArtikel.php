<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string      $id
 * @property string|null $nama_kategori
 * @property string|null $deskripsi
 */
class KategoriArtikel extends Model
{
    protected $table = 'kategori_artikel';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = true;

    protected $fillable = ['id', 'nama_kategori', 'deskripsi'];

    public function knowledgeBases()
    {
        return $this->hasMany(KnowledgeBase::class, 'kategori_artikel_id');
    }
}
