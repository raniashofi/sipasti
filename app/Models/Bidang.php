<?php

namespace App\Models;

use App\Models\Concerns\HasPrefixedId;
use Illuminate\Database\Eloquent\Model;

class Bidang extends Model
{
    use HasPrefixedId;

    protected $table = 'bidang';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    protected string $idPrefix = 'BDG';

    protected $fillable = ['id', 'nama_bidang', 'batas_hari_pengerjaan'];

    public function knowledgeBases()
    {
        return $this->hasMany(SopInternal::class, 'bidang_id');
    }

    public function sopInternal()
    {
        return $this->hasMany(SopInternal::class, 'bidang_id');
    }
}
