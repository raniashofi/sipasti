<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bidang extends Model
{
    protected $table = 'bidang';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = ['id', 'nama_bidang'];

    public function kategori()
    {
        return $this->hasMany(KategoriSistem::class);
    }

    public function knowledgeBases()
    {
        return $this->hasMany(KnowledgeBase::class, 'bidang_id');
    }
}
