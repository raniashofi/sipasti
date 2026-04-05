<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string      $id
 * @property string|null $bidang_id
 * @property string|null $nama_kategori
 * @property string|null $deskripsi
 */
class Kategori extends Model
{
    protected $table = 'kategori';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    public function bidang()
    {
        return $this->belongsTo(Bidang::class);
    }

    public function knowledgeBase()
    {
        return $this->hasMany(KnowledgeBase::class);
    }
}
