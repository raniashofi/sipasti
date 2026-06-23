<?php

namespace App\Models;

use App\Models\Concerns\HasPrefixedId;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string      $id
 * @property string|null $nama_kategori
 * @property string|null $deskripsi
 */
class KategoriSistem extends Model
{
    use HasPrefixedId;

    protected $table = 'kategori_sistem';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    protected string $idPrefix = 'KTS';

    protected $fillable = ['id', 'nama_kategori', 'deskripsi', 'icon'];


    public function nodes()
    {
        return $this->hasMany(NodeDiagnosis::class, 'kategori_id');
    }
}
