<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bidang extends Model
{
    protected $table = 'bidang';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    public function kategori()
    {
        return $this->hasMany(Kategori::class);
    }
}
