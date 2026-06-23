<?php

namespace App\Models;

use App\Models\Concerns\HasPrefixedId;
use Illuminate\Database\Eloquent\Model;

class TiketBuktiFoto extends Model
{
    use HasPrefixedId;

    protected $table = 'tiket_bukti_foto';
    public $incrementing = false;
    protected $keyType = 'string';
    protected string $idPrefix = 'TBFO';

    protected $fillable = ['id', 'tiket_id', 'foto_path'];

    public function tiket()
    {
        return $this->belongsTo(Tiket::class, 'tiket_id');
    }
}
