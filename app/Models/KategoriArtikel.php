<?php

namespace App\Models;

use App\Models\Concerns\HasPrefixedId;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string      $id
 * @property string|null $nama_kategori
 * @property string|null $deskripsi
 */
class KategoriArtikel extends Model
{
    use HasPrefixedId;

    protected $table = 'kategori_artikel';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = true;
    protected string $idPrefix = 'KTA';

    protected $fillable = ['id', 'nama_kategori', 'deskripsi'];

    public function knowledgeBases()
    {
        return $this->hasMany(ArtikelOpd::class, 'kategori_artikel_id');
    }

    public function artikelOpd()
    {
        return $this->hasMany(ArtikelOpd::class, 'kategori_artikel_id');
    }
}
