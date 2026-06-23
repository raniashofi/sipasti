<?php

namespace App\Models;

use App\Models\Concerns\HasPrefixedId;
use Illuminate\Database\Eloquent\Model;

class TimTeknis extends Model
{
    use HasPrefixedId;

    protected $table = 'tim_teknis';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    protected string $idPrefix = 'USR-TIM';

    protected $fillable = ['id','user_id','bidang_id','nama_lengkap','status_teknisi'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tiketTeknisi()
    {
        return $this->hasMany(TiketTeknisi::class, 'teknis_id');
    }

    public function bidang()
    {
        return $this->belongsTo(Bidang::class, 'bidang_id', 'id');
    }
}
