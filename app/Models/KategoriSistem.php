<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string      $id
 * @property string|null $nama_kategori
 * @property string|null $deskripsi
 */
class KategoriSistem extends Model
{
    protected $table = 'kategori_sistem';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = ['id', 'bidang_id', 'nama_kategori', 'deskripsi', 'icon'];

    public function bidang()
    {
        return $this->belongsTo(Bidang::class, 'bidang_id');
    }

    public function nodes()
    {
        return $this->hasMany(NodeDiagnosis::class, 'kategori_id');
    }
}
